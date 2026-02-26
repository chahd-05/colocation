<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use App\Models\Membership;
use App\Models\Payment;
use App\Support\ColocationBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ColocationController extends Controller
{
    public function create()
    {
        return view('colocations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $hasActive = Membership::where('user_id', Auth::id())
            ->whereNull('left_at')
            ->whereHas('colocation', function ($q) {
                $q->where('status', 'active');
            })
            ->exists();

        if ($hasActive) {
            return back()->withErrors([
                'name' => 'You already have an active colocation.',
            ]);
        }

        $colocation = null;

        DB::transaction(function () use (&$colocation, $request): void {
            $colocation = Colocation::create([
                'name' => $request->name,
                'owner_id' => Auth::id(),
                'status' => 'active',
            ]);

            Membership::create([
                'user_id' => Auth::id(),
                'colocation_id' => $colocation->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            foreach (['Loyer', 'Nourriture', 'Internet', 'Transport', 'Autre'] as $name) {
                Category::create([
                    'name' => $name,
                    'colocation_id' => $colocation->id,
                ]);
            }
        });

        return redirect()->route('colocations.show', $colocation);
    }

    public function show(Request $request, Colocation $colocation)
    {
        $this->authorizeMemberOrAdmin($colocation);

        $month = $request->query('month');
        if ($month && ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = null;
        }
        $expensesQuery = $colocation->expenses()->with(['payer', 'category'])->latest('expenses_date');

        if ($month) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            $expensesQuery->whereBetween('expenses_date', [$start, $end]);
        }

        $expenses = $expensesQuery->get();
        $categories = $colocation->categories()->orderBy('name')->get();
        $memberships = $colocation->activeMemberships()->with('user')->get();
        $allExpenses = $colocation->expenses()->with('category')->get();

        $balanceData = ColocationBalance::compute($colocation);

        $categoryStats = $allExpenses
            ->groupBy('category.name')
            ->map(fn ($items) => round($items->sum('amount'), 2))
            ->sortDesc();

        $monthlyStats = $allExpenses
            ->groupBy(fn ($expense) => optional($expense->expenses_date)->format('Y-m') ?: 'unknown')
            ->map(fn ($items) => round($items->sum('amount'), 2))
            ->sortKeysDesc();

        return view('colocations.show', [
            'colocation' => $colocation,
            'expenses' => $expenses,
            'categories' => $categories,
            'memberships' => $memberships,
            'balanceData' => $balanceData,
            'categoryStats' => $categoryStats,
            'monthlyStats' => $monthlyStats,
            'month' => $month,
            'isOwner' => $this->isOwner($colocation),
        ]);
    }

    public function update(Request $request, Colocation $colocation)
    {
        $this->authorizeOwner($colocation);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $colocation->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Colocation updated.');
    }

    public function leave(Colocation $colocation)
    {
        $membership = $this->getActiveMembership($colocation, Auth::id());
        if (! $membership) {
            abort(403);
        }

        if ($membership->role === 'owner') {
            return back()->withErrors(['leave' => 'Owner cannot leave. Cancel colocation or remove members first.']);
        }

        $this->applyReputationOnExit($colocation, Auth::id());

        $membership->update([
            'left_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'You left the colocation.');
    }

    public function removeMember(Colocation $colocation, Membership $membership)
    {
        $this->authorizeOwner($colocation);

        if ($membership->colocation_id !== $colocation->id || $membership->left_at) {
            abort(404);
        }

        if ($membership->role === 'owner') {
            return back()->withErrors(['member' => 'Owner cannot be removed.']);
        }

        $userId = $membership->user_id;
        $balanceData = ColocationBalance::compute($colocation);
        $memberBalance = $balanceData['members'][$userId]['balance'] ?? 0;

        if ($memberBalance < 0) {
            $memberDebtSettlements = collect($balanceData['settlements'])->filter(function ($settlement) use ($userId) {
                return $settlement['from_user']->id === $userId;
            });
            foreach ($memberDebtSettlements as $settlement) {
                Payment::create([
                    'from_user_id' => $colocation->owner_id,
                    'to_user_id' => $settlement['to_user']->id,
                    'amount' => $settlement['amount'],
                    'colocation_id' => $colocation->id,
                    'paid_at' => now(),
                ]);
            }
        }

        $this->applyReputationOnExit($colocation, $userId);

        $membership->update([
            'left_at' => now(),
        ]);

        return back()->with('success', 'Member removed.');
    }

    public function cancel(Colocation $colocation)
    {
        $this->authorizeOwner($colocation);

        $balanceData = ColocationBalance::compute($colocation);

        foreach ($colocation->activeMemberships()->get() as $membership) {
            $balance = $balanceData['members'][$membership->user_id]['balance'] ?? 0;
            $membership->user->increment('reputation', $balance < 0 ? -1 : 1);
            $membership->update(['left_at' => now()]);
        }

        $colocation->update(['status' => 'cancelled']);

        return redirect()->route('dashboard')->with('success', 'Colocation cancelled.');
    }

    private function authorizeMemberOrAdmin(Colocation $colocation): void
    {
        if (Auth::user()->role === 'admin') {
            return;
        }

        $membership = $this->getActiveMembership($colocation, Auth::id());
        if (! $membership) {
            abort(403);
        }
    }

    private function authorizeOwner(Colocation $colocation): void
    {
        if ($colocation->owner_id !== Auth::id()) {
            abort(403);
        }
    }

    private function getActiveMembership(Colocation $colocation, int $userId): ?Membership
    {
        return $colocation->activeMemberships()->where('user_id', $userId)->first();
    }

    private function isOwner(Colocation $colocation): bool
    {
        return $colocation->owner_id === Auth::id();
    }

    private function applyReputationOnExit(Colocation $colocation, int $userId): void
    {
        $balanceData = ColocationBalance::compute($colocation);
        $balance = $balanceData['members'][$userId]['balance'] ?? 0;

        $delta = $balance < 0 ? -1 : 1;
        Membership::where('colocation_id', $colocation->id)
            ->where('user_id', $userId)
            ->with('user')
            ->first()?->user
            ?->increment('reputation', $delta);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
        $this->authorizeMember($colocation);

        $memberIds = $colocation->activeMemberships()->pluck('user_id')->toArray();
        $categoryIds = $colocation->categories()->pluck('id')->toArray();

        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expenses_date' => 'required|date',
            'payer_id' => 'required|integer|in:'.implode(',', $memberIds),
            'category_id' => 'required|integer|in:'.implode(',', $categoryIds),
        ]);

        Expense::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'expenses_date' => $request->expenses_date,
            'colocation_id' => $colocation->id,
            'payer_id' => $request->payer_id,
            'category_id' => $request->category_id,
        ]);

        return back()->with('success', 'Expense added.');
    }

    public function destroy(Colocation $colocation, Expense $expense)
    {
        $this->authorizeMember($colocation);

        if ($expense->colocation_id !== $colocation->id) {
            abort(404);
        }

        $isOwner = $colocation->owner_id === Auth::id();
        $isPayer = $expense->payer_id === Auth::id();

        if (! $isOwner && ! $isPayer) {
            abort(403);
        }

        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }

    private function authorizeMember(Colocation $colocation): void
    {
        $exists = $colocation->activeMemberships()
            ->where('user_id', Auth::id())
            ->exists();

        if (! $exists) {
            abort(403);
        }
    }
}

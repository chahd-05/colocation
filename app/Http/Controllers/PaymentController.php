<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
        $this->authorizeMember($colocation);

        $memberIds = $colocation->activeMemberships()->pluck('user_id')->toArray();

        $request->validate([
            'from_user_id' => 'required|integer|in:'.implode(',', $memberIds),
            'to_user_id' => 'required|integer|different:from_user_id|in:'.implode(',', $memberIds),
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ((int) $request->from_user_id !== Auth::id() && $colocation->owner_id !== Auth::id()) {
            abort(403);
        }

        Payment::create([
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'amount' => $request->amount,
            'colocation_id' => $colocation->id,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payment recorded.');
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

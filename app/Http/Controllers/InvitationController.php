<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
        $this->authorizeOwner($colocation);

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'created_by' => Auth::id(),
            'token' => Str::random(20),
            'email' => $request->email,
            'status' => 'pendding',
        ]);

        $url = url('/invitations/' . $invitation->token);

        return redirect()->back()->with('link', $url);
    }

    public function accept(string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->accepted_at || $invitation->refused_at) {
            return redirect()->route('dashboard')->withErrors(['invitation' => 'Invitation already used.']);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return redirect()->route('dashboard')->withErrors(['invitation' => 'Invitation expired.']);
        }

        if (strtolower($invitation->email) !== strtolower(Auth::user()->email)) {
            return redirect()->route('dashboard')->withErrors(['invitation' => 'This invitation is not for your email.']);
        }

        $hasActive = Membership::where('user_id', Auth::id())
            ->whereNull('left_at')
            ->whereHas('colocation', function ($q) {
                $q->where('status', 'active');
            })
            ->exists();

        if ($hasActive) {
            return redirect()->route('dashboard')->withErrors(['invitation' => 'You already have an active colocation.']);
        }

        Membership::create([
            'user_id' => Auth::id(),
            'colocation_id' => $invitation->colocation_id,
            'role' => 'user',
            'joined_at' => now(),
        ]);

        $invitation->update([
            'accepted_at' => now(),
            'status' => 'accepted',
        ]);

        return redirect()->route('colocations.show', $invitation->colocation_id);
    }

    public function refuse(string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->accepted_at && ! $invitation->refused_at) {
            $invitation->update([
                'refused_at' => now(),
                'status' => 'refused',
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Invitation refused.');
    }

    private function authorizeOwner(Colocation $colocation): void
    {
        if ($colocation->owner_id !== Auth::id()) {
            abort(403);
        }
    }
}

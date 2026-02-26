<?php

namespace App\Http\Controllers;

use App\Mail\ColocationInvitationMail;
use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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
            'email' => $request->email,
            'colocation_id' => $colocation->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(3),
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitations.accept',
            $invitation->expires_at,
            ['token' => $invitation->token]
        );

        $refuseUrl = URL::temporarySignedRoute(
            'invitations.refuse',
            $invitation->expires_at,
            ['token' => $invitation->token]
        );

        Mail::to($request->email)->queue(
            new ColocationInvitationMail($colocation, $acceptUrl, $refuseUrl, $invitation->expires_at)
        );

        return back()->with('success', 'Invitation sent.');
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
        ]);

        return redirect()->route('colocations.show', $invitation->colocation_id);
    }

    public function refuse(string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->accepted_at && ! $invitation->refused_at) {
            $invitation->update([
                'refused_at' => now(),
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

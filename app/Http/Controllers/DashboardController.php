<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $activeMembership = Membership::with('colocation')
            ->where('user_id', Auth::id())
            ->whereNull('left_at')
            ->whereHas('colocation', function ($q) {
                $q->where('status', 'active');
            })
            ->first();

        return view('dashboard', [
            'activeMembership' => $activeMembership,
        ]);
    }
}

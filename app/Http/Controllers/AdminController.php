<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'usersCount' => User::count(),
            'colocationsCount' => Colocation::count(),
            'expensesCount' => Expense::count(),
            'bannedCount' => User::where('is_banned', true)->count(),
            'users' => User::latest()->get(),
        ]);
    }

    public function ban(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['admin' => 'You cannot ban yourself.']);
        }

        $user->update([
            'is_banned' => true,
        ]);

        return back()->with('success', 'User banned.');
    }

    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
        ]);

        return back()->with('success', 'User unbanned.');
    }
}

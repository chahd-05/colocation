<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColocationController extends Controller
{
    public function create() {
        return view('colocations.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name'=>'required|string|max:255'
        ]);

        $hasActive = Membership::where('user_id', Auth::id())->whereNull('left_at')->exists();
        if($hasActive) {
            return back()->withErrors('you already have an active colocation');
        }

        $colocation = Colocation::create([
            'name'=>$request->name,
            'status'=>'active'
        ]);

        Membership::create([
            'user_id'=>Auth::id(),
            'colocation_id'=>$colocation->id,
            'role'=>'owner'
        ]);

        return redirect()->route('colocations.show', $colocation);
    }
}

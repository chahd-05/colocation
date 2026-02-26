<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
        $this->authorizeOwner($colocation);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'name' => $request->name,
            'colocation_id' => $colocation->id,
        ]);

        return back()->with('success', 'Category added.');
    }

    public function destroy(Colocation $colocation, Category $category)
    {
        $this->authorizeOwner($colocation);

        if ($category->colocation_id !== $colocation->id) {
            abort(404);
        }

        if ($category->expenses()->exists()) {
            return back()->withErrors(['category' => 'Category has expenses and cannot be deleted.']);
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function authorizeOwner(Colocation $colocation): void
    {
        if ($colocation->owner_id !== Auth::id()) {
            abort(403);
        }
    }
}

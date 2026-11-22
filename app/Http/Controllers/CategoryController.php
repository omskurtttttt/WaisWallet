<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $request->user()->categories()->create($request->validated());

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);

        if ($category->transactions()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete a category that has associated transactions. Please reassign or delete the transactions first.',
            ]);
        }

        if ($category->budgets()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete a category that has active budgets.',
            ]);
        }

        if ($category->recurringTransactions()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete a category linked to recurring transactions.',
            ]);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}

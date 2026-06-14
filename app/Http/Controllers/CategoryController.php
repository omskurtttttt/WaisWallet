<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;

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
}

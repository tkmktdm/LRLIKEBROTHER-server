<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = json_encode(Category::orderBy("updated_at", "desc")->where("user_id", auth()->id())->get());
        return $categories;
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        $categories = Category::create($validated);
        return response()->json($categories);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        if ($category->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $result = $category->delete();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}
    /**
     * Display the specified resource.
     */
    public function show(string $id) {}
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}
}

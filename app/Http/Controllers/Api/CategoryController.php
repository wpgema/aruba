<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection($categories->items()),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'from' => $categories->firstItem(),
                'to' => $categories->lastItem(),
            ]
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $category = Category::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function show($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $category->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}

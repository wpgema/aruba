<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        
        $query = Product::with(['category', 'supplier']);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }
        
        $products = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }
        
        $product = Product::create($data);
        $product->load(['category', 'supplier']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'supplier'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        
        // Only handle image if new image is uploaded
        if ($request->hasFile('image')) {
            // Delete old image if exists before storing new one
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            
            // Store new image
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }
        // If no new image uploaded, keep the existing image (don't include image in $data)
        
        $product->update($data);
        $product->load(['category', 'supplier']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete associated image file if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}

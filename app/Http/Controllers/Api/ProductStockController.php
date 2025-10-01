<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductStockRequest;
use App\Http\Requests\UpdateProductStockRequest;
use App\Http\Resources\ProductStockResource;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        
        // Get product_id from URL parameter or query parameter
        $productId = $request->route('product_id') ?? $request->get('product_id', '');
        
        $query = ProductStock::with(['product']);
        
        // Filter by product_id if provided
        if (!empty($productId)) {
            $query->where('product_id', $productId);
        }
        
        // Search functionality
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('stock', 'like', "%{$search}%")
                    ->orWhereHas('product', function($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }
        
        $productStocks = $query->orderBy('date', 'desc')->paginate($perPage);
        
        // Customize message based on whether filtering by product_id
        $message = !empty($productId) 
            ? "Product stock history for product ID {$productId} retrieved successfully"
            : 'Product stocks retrieved successfully';
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => ProductStockResource::collection($productStocks->items()),
            'pagination' => [
                'current_page' => $productStocks->currentPage(),
                'last_page' => $productStocks->lastPage(),
                'per_page' => $productStocks->perPage(),
                'total' => $productStocks->total(),
                'from' => $productStocks->firstItem(),
                'to' => $productStocks->lastItem(),
            ],
            'filters' => [
                'product_id' => $productId,
                'search' => $search
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductStockRequest $request)
    {
        $data = $request->validated();
        
        try {
            DB::beginTransaction();
            
            // Find the product to update its stock
            $product = Product::findOrFail($data['product_id']);
            
            // Create the product stock record
            $productStock = ProductStock::create($data);
            
            // Update product stock: current stock + new stock
            $product->update([
                'stock' => $product->stock + $data['stock']
            ]);
            
            DB::commit();
            
            $productStock->load(['product']);
            
            return response()->json([
                'success' => true,
                'message' => 'Product stock created and product inventory updated successfully',
                'data' => new ProductStockResource($productStock)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product stock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productStock = ProductStock::with(['product'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Product stock retrieved successfully',
            'data' => new ProductStockResource($productStock)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductStockRequest $request, ProductStock $productStock)
    {
        $data = $request->validated();
        
        try {
            DB::beginTransaction();
            
            // Find the product to update its stock
            $product = Product::findOrFail($productStock->product_id);
            
            // Store the old stock amount before update
            $oldStock = $productStock->stock;
            
            // Update the product stock record
            $productStock->update($data);
            
            // Calculate the difference between new and old stock
            $newStock = $data['stock'] ?? $oldStock;
            $stockDifference = $newStock - $oldStock;
            
            // Update product stock: current stock + difference
            // If difference is positive, add to stock
            // If difference is negative, subtract from stock
            $updatedProductStock = max(0, $product->stock + $stockDifference);
            $product->update([
                'stock' => $updatedProductStock
            ]);
            
            DB::commit();
            
            $productStock->load(['product']);
            
            $message = $stockDifference >= 0 
                ? "Product stock updated successfully. Product inventory increased by " . abs($stockDifference) . " units."
                : "Product stock updated successfully. Product inventory reduced by " . abs($stockDifference) . " units.";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new ProductStockResource($productStock)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product stock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductStock $productStock)
    {
        try {
            DB::beginTransaction();
            
            // Find the product to update its stock
            $product = Product::findOrFail($productStock->product_id);
            
            // Store the stock amount before deletion
            $stockToReduce = $productStock->stock;
            
            // Delete the product stock record
            $productStock->delete();
            
            // Update product stock: current stock - deleted stock amount
            // Ensure stock doesn't go below 0
            $newStock = max(0, $product->stock - $stockToReduce);
            $product->update([
                'stock' => $newStock
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Product stock deleted successfully. Product inventory reduced by {$stockToReduce} units."
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product stock: ' . $e->getMessage(),
            ], 500);
        }
    }
}

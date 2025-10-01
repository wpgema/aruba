<?php

namespace App\Http\Controllers\Api;

use App\Models\SaleDetail;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\SaleDetailResource;
use App\Http\Requests\StoreSaleDetailRequest;
use App\Http\Requests\UpdateSaleDetailRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SaleDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleDetail::with(['sale.user', 'product.category']);

        // Filter by sale_id
        if ($request->has('sale_id')) {
            $query->where('sale_id', $request->sale_id);
        }

        // Filter by product_id
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $saleDetails = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Sale details retrieved successfully',
            'data' => SaleDetailResource::collection($saleDetails->items()),
            'pagination' => [
                'current_page' => $saleDetails->currentPage(),
                'last_page' => $saleDetails->lastPage(),
                'per_page' => $saleDetails->perPage(),
                'total' => $saleDetails->total(),
                'from' => $saleDetails->firstItem(),
                'to' => $saleDetails->lastItem(),
            ]
        ]);
    }

    public function store(StoreSaleDetailRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Calculate subtotal
            $data['subtotal'] = $data['quantity'] * $data['price'];
            
            // Create sale detail
            $saleDetail = SaleDetail::create($data);
            
            // Update product stock
            $product = Product::find($data['product_id']);
            if ($product) {
                $product->decrement('stock', $data['quantity']);
            }
            
            // Update sale totals
            $sale = $saleDetail->sale;
            $sale->total = $sale->saleDetails()->sum('subtotal');
            $sale->grand_total = $sale->total - $sale->discount;
            $sale->change_amount = $sale->paid_amount - $sale->grand_total;
            $sale->save();
            
            DB::commit();
            
            $saleDetail->load(['sale.user', 'product.category']);
            
            return response()->json([
                'success' => true,
                'message' => 'Sale detail created successfully',
                'data' => new SaleDetailResource($saleDetail)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(SaleDetail $saleDetail)
    {
        $saleDetail->load(['sale.user', 'product.category']);
        
        return response()->json([
            'success' => true,
            'message' => 'Sale detail retrieved successfully',
            'data' => new SaleDetailResource($saleDetail)
        ]);
    }

    public function update(UpdateSaleDetailRequest $request, SaleDetail $saleDetail)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Restore previous stock
            $product = Product::find($saleDetail->product_id);
            if ($product) {
                $product->increment('stock', $saleDetail->quantity);
            }
            
            // Update sale detail
            if (isset($data['quantity']) || isset($data['price'])) {
                $data['subtotal'] = ($data['quantity'] ?? $saleDetail->quantity) * ($data['price'] ?? $saleDetail->price);
            }
            
            $saleDetail->update($data);
            
            // Update new stock
            if ($product && isset($data['quantity'])) {
                $product->decrement('stock', $data['quantity']);
            } elseif ($product) {
                $product->decrement('stock', $saleDetail->quantity);
            }
            
            // Update sale totals
            $sale = $saleDetail->sale;
            $sale->total = $sale->saleDetails()->sum('subtotal');
            $sale->grand_total = $sale->total - $sale->discount;
            $sale->change_amount = $sale->paid_amount - $sale->grand_total;
            $sale->save();
            
            DB::commit();
            
            $saleDetail->load(['sale.user', 'product.category']);
            
            return response()->json([
                'success' => true,
                'message' => 'Sale detail updated successfully',
                'data' => new SaleDetailResource($saleDetail)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sale detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(SaleDetail $saleDetail)
    {
        try {
            DB::beginTransaction();
            
            // Restore product stock
            $product = Product::find($saleDetail->product_id);
            if ($product) {
                $product->increment('stock', $saleDetail->quantity);
            }
            
            $sale = $saleDetail->sale;
            
            $saleDetail->delete();
            
            // Update sale totals
            $sale->total = $sale->saleDetails()->sum('subtotal');
            $sale->grand_total = $sale->total - $sale->discount;
            $sale->change_amount = $sale->paid_amount - $sale->grand_total;
            $sale->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale detail deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sale detail: ' . $e->getMessage()
            ], 500);
        }
    }
}
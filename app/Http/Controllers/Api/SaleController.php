<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['employee', 'saleDetails.product'])
            ->withCount('saleDetails');

        // Filter by date range
        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by invoice number or user name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->get('per_page', 10);
        $sales = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Sales retrieved successfully',
            'data' => SaleResource::collection($sales->items()),
            'pagination' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
                'from' => $sales->firstItem(),
                'to' => $sales->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'payment_method' => 'required|string|in:cash,card,qris',
            'status' => 'required|string|in:paid,unpaid,cancelled',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|integer|min:0',
            'paid_amount' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();
            $data = $request->all();
            $total = 0;
            foreach ($data['products'] as $product) {
                $total += $product['quantity'] * $product['price'];
            }
            $data['total'] = $total;
            $data['grand_total'] = $total - ($data['discount'] ?? 0);
            $data['change_amount'] = $data['paid_amount'] - $data['grand_total'];
            $data['invoice_number'] = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $data['user_id'] = Auth::id();
            $sale = Sale::create($data);
            foreach ($data['products'] as $productData) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'subtotal' => $productData['quantity'] * $productData['price'],
                    'note' => $productData['note'] ?? null,
                ]);
                $product = Product::find($productData['product_id']);
                if ($product) {
                    $product->decrement('stock', $productData['quantity']);
                }
            }
            DB::commit();
            $sale->load(['employee', 'saleDetails.product']);
            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully',
                'data' => $sale
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['employee', 'saleDetails.product.category'])
            ->withCount('saleDetails')
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Sale retrieved successfully',
            'data' => new SaleResource($sale)
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // If products are updated, recalculate totals
            if (isset($data['products'])) {
                // Restore stock from previous sale details
                foreach ($sale->saleDetails as $saleDetail) {
                    $product = Product::find($saleDetail->product_id);
                    if ($product) {
                        $product->increment('stock', $saleDetail->quantity);
                    }
                }
                
                // Delete old sale details
                $sale->saleDetails()->delete();
                
                // Calculate new totals
                $total = 0;
                foreach ($data['products'] as $product) {
                    $total += $product['quantity'] * $product['price'];
                }
                
                $data['total'] = $total;
                $data['grand_total'] = $total - ($data['discount'] ?? $sale->discount);
                $data['change_amount'] = ($data['paid_amount'] ?? $sale->paid_amount) - $data['grand_total'];
                
                // Create new sale details
                foreach ($data['products'] as $productData) {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'price' => $productData['price'],
                        'subtotal' => $productData['quantity'] * $productData['price'],
                        'note' => $productData['note'] ?? null,
                    ]);

                    // Update product stock
                    $product = Product::find($productData['product_id']);
                    if ($product) {
                        $product->decrement('stock', $productData['quantity']);
                    }
                }
            }
            
            $sale->update($data);
            
            DB::commit();
            
            $sale->load(['employee', 'saleDetails.product']);
            
            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully',
                'data' => new SaleResource($sale)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();
            
            // Restore product stock
            foreach ($sale->saleDetails as $saleDetail) {
                $product = Product::find($saleDetail->product_id);
                if ($product) {
                    $product->increment('stock', $saleDetail->quantity);
                }
            }
            
            $sale->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSalesReport(Request $request)
    {
        $query = Sale::query();

        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $totalSales = $query->sum('grand_total');
        $totalTransactions = $query->count();
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        $salesByMethod = $query->groupBy('payment_method')
            ->selectRaw('payment_method, count(*) as count, sum(grand_total) as total')
            ->get();

        $salesByStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as count, sum(grand_total) as total')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Sales report retrieved successfully',
            'data' => [
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'average_transaction' => $avgTransaction,
                'sales_by_payment_method' => $salesByMethod,
                'sales_by_status' => $salesByStatus,
            ]
        ]);
    }
}

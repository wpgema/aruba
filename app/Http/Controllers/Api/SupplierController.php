<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('products')->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Suppliers retrieved successfully',
            'data' => SupplierResource::collection($suppliers->items()),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'from' => $suppliers->firstItem(),
                'to' => $suppliers->lastItem(),
            ]
        ]);
    }

    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();
        $supplier = Supplier::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'data' => new SupplierResource($supplier)
        ], 201);
    }

    public function show($id)
    {
        $supplier = Supplier::withCount('products')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Supplier retrieved successfully',
            'data' => new SupplierResource($supplier)
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        $supplier->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'data' => new SupplierResource($supplier)
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully'
        ]);
    }
}

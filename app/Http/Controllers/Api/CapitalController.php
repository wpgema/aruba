<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Capital;
use App\Http\Resources\CapitalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search', '');
        $query = Capital::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $wantsPagination = $request->has('page') || $request->has('per_page') || $request->boolean('paginate', false);

        if ($wantsPagination) {
            $paginator = $query->orderByDesc('date')->paginate($perPage);
            return response()->json([
                'success' => true,
                'message' => 'Capitals retrieved successfully',
                'data' => CapitalResource::collection($paginator->items()),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ]
            ]);
        }

        $items = $query->orderByDesc('date')->get();
        return response()->json([
            'success' => true,
            'message' => 'Capitals retrieved successfully',
            'data' => CapitalResource::collection($items),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:harian,bulanan,tahunan',
            'date' => 'required|date',
            'amount' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $capital = Capital::create($validated);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Capital created successfully',
                'data' => new CapitalResource($capital),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create capital: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Capital $capital)
    {
        return response()->json([
            'success' => true,
            'message' => 'Capital retrieved successfully',
            'data' => new CapitalResource($capital),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Capital $capital)
    {
        $validated = $request->validate([
            'type' => 'sometimes|required|in:harian,bulanan,tahunan',
            'date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $capital->update($validated);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Capital updated successfully',
                'data' => new CapitalResource($capital),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update capital: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Capital $capital)
    {
        try {
            DB::beginTransaction();
            $capital->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Capital deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete capital: ' . $e->getMessage(),
            ], 500);
        }
    }



}


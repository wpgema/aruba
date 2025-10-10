<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Employees retrieved successfully',
            'data' => EmployeeResource::collection($employees),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = Employee::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => new EmployeeResource($employee)
        ], 201);
    }
 
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Employee retrieved successfully',
            'data' => new EmployeeResource($employee)
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        
        if (empty($data['password'])) {
            unset($data['password']);
            unset($data['password_confirmation']);
        }
        
        $employee->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => new EmployeeResource($employee)
        ]);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }
}

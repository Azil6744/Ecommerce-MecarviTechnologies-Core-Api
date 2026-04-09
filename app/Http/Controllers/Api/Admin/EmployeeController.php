<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Get all employees
     */
    public function index(Request $request)
    {
        $employees = collect([
            [
                'id' => 1,
                'name' => 'John Doe',
                'company' => 'Mecarvi',
                'title' => 'Senior Developer',
                'shift' => 'Morning (9 AM - 5 PM)',
                'phone' => '+1-555-0123',
                'email' => 'john@mecarvi.com',
                'projects' => 3,
                'tasks' => 12,
                'attendancePercent' => 95,
                'leaveBalance' => [
                    'maternity' => 0,
                    'bereavement' => 2,
                    'sick' => 3,
                    'vacation' => 8,
                ],
            ],
        ]);

        if ($request->has('search')) {
            $employees = $employees->filter(function($item) use ($request) {
                return stripos($item['name'], $request->search) !== false;
            });
        }

        return response()->json($employees->values()->all());
    }

    /**
     * Create employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'title' => 'required|string',
            'phone' => 'required|string|max:20',
            'shift' => 'nullable|string',
            'department' => 'nullable|string',
        ]);

        return response()->json(['message' => 'Employee created successfully'], 201);
    }

    /**
     * Get employee details
     */
    public function show($id)
    {
        return response()->json(['id' => $id]);
    }

    /**
     * Update employee
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'title' => 'string',
            'status' => 'string',
        ]);

        return response()->json(['message' => 'Employee updated successfully']);
    }

    /**
     * Delete employee
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomeService;
use Illuminate\Http\Request;

class CoustomServiceController extends Controller
{
    public function index()
    {
        $customServices = CustomeService::first();
        return response()->json([
            'data' => $customServices
        ], 200);
    }

    // // Create a new custom service
     public function store(Request $request)
    {
        $validatedData = $request->validate([
            'desc' => 'nullable|string',
            'watsapp' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $customService = CustomeService::create($validatedData);

        return response()->json([
            'message' => 'Custom service created successfully!',
            'data' => $customService
        ], 201);
    }

    // Show a specific custom service
    public function show($id)
    {
        $customService = CustomeService::find($id);

        if (!$customService) {
            return response()->json(['message' => 'Custom service not found'], 404);
        }

        return response()->json($customService, 200);
    }

    // Update an existing custom service
    public function update(Request $request)
    {
        $customService = CustomeService::first();
        if ($customService) {
            $customService->update($request->all());
        } else {
            $customService = CustomeService::create($request->all());
        }
        if (!$customService) {
            return response()->json(['message' => 'Custom service not found'], 404);
        }

        return response()->json([
            'message' => 'Custom service updated successfully!',
            'data' => $customService
        ], 200);
    }

    // Delete a custom service
    public function destroy($id)
    {
        $customService = CustomeService::find($id);

        if (!$customService) {
            return response()->json(['message' => 'Custom service not found'], 404);
        }

        $customService->delete();

        return response()->json(['message' => 'Custom service deleted successfully!'], 200);
    }
}
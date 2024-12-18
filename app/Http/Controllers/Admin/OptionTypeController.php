<?php

namespace App\Http\Controllers\Admin;

use App\Models\OptionType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Service;

class OptionTypeController extends Controller
{
    public function index()
    {
        $optionTypes = OptionType::with('service')->get();
        return response()->json(['data'=> $optionTypes], 200);

    }
    public function getOptionTypedService($id)
    {
        $optionTypes = OptionType::with('Options')->where('service_id', $id)->get();

        if ($optionTypes->isEmpty()) {
            return response()->json([
                'message' => 'No Option Types found for the given service ID.',
                'data' => []
            ], 404); // Returning a 404 Not Found status code
        }
        return response()->json(['data'=> $optionTypes], 200);

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
             'type' => 'required|in:number,option,text,meter,chekbox',
            // 'value' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
        ]);

        $optionType = OptionType::create($validated);

        return response()->json(['data'=>$optionType->load('service')], 201);
    }

    public function show($id)
    {
        $optionType = OptionType::with('service')->findOrFail($id);
        return response()->json($optionType);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'type' => 'required|in:number,option,text,meter,chekbox',
            // 'value' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
        ]);

        $optionType = OptionType::findOrFail($id);
        $optionType->update($validated);

        return response()->json($optionType->load('service'));
    }

    public function destroy($id)
    {
        $optionType = OptionType::findOrFail($id);
        $optionType->delete();
        return response()->json(null, 204);
    }
}

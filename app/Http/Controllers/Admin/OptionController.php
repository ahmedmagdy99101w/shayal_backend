<?php

namespace App\Http\Controllers\Admin;
use App\Models\Options;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OptionController extends Controller
{
    public function index()
    {
        $options = Options::all();
        return response()->json($options);
    }

    public function create()
    {
        // Typically not used for API, form handled on frontend
    }

    public function store(Request $request)
    {
        $data = $request->all(); 
        $createdOrUpdatedItems = [];
    
        foreach ($data as $da) {
            // Validate each item
       
            $validated = Validator::make($da, [
                'key' => 'required|string|max:255',
                'price' => 'nullable|string|max:255',
                'option_type_id' => 'required|exists:option_types,id',
            ])->validate();

            // Use updateOrCreate to either update an existing record or create a new one
            $createdOrUpdatedItem = Options::updateOrCreate(
                ['key' => $validated['key'], 'option_type_id' => $validated['option_type_id']], // Criteria to check for existing record
                ['price' => $validated['price']] // Fields to update or create with
            );

            $createdOrUpdatedItems[] = $createdOrUpdatedItem;
        }

        // Return all the processed items in the response
        return response()->json(['data' => $createdOrUpdatedItems], 200);
    }


    public function show($id)
    {
        $option = Options::findOrFail($id);
        return response()->json($option);
    }

    public function edit($id)
    {
        // Typically not used for API, form handled on frontend
    }

    public function update(Request $request, $id)
    {
        $data = $request->all(); // Assuming $data is an array of items
        $createdOrUpdatedItems = [];

        foreach ($data as $da) {
            // Validate each item
            $validated = Validator::make($da, [
                'key' => 'required|string|max:255',
                'price' => 'nullable|string|max:255',
                'option_type_id' => 'required|exists:option_types,id',
            ])->validate();

            // Use updateOrCreate to either update an existing record or create a new one
            $createdOrUpdatedItem = Options::updateOrCreate(
                ['key' => $validated['key'], 'option_type_id' => $validated['option_type_id']], // Criteria to check for existing record
                ['price' => $validated['price']] // Fields to update or create with
            );

            $createdOrUpdatedItems[] = $createdOrUpdatedItem;
        }

        return response()->json($option);
    }

    public function destroy($id)
    {
        $option = Options::findOrFail($id);
        $option->delete();
        return response()->json(null, 204);
    }
}

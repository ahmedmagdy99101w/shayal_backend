<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $service=Service::paginate($request->get('per_page', 50));
        return ServiceResource::collection($service);


    }
    public function getServiceDetails($serviceId)
    {

        $service = Service::with('optionTypes.options')->find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Format the data to include option types and options

            $option_types = [];


        foreach ($service->optionTypes as $optionType) {
            $options = $optionType->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'key' => $option->key,
                    'price' => $option->price,

                ];
            });

            $option_types [] = [
                'id' => $optionType->id,
                'key' => $optionType->key,
                'options' => $options,
            ];
        }

        return response()->json($option_types);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'photo' => 'required|image|mimes:jpeg,webp,png,jpg,gif,pdf|max:2048',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }
        if ($request->file('photo')) {
            $avatar = $request->file('photo');
            $photo  =  upload($avatar, public_path('uploads/service_photo'));

        } else {
            $photo = null;
        }
        $service=Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'photo' => $photo,
            'status' => $request->has('status') ? $request->status : 1,
            'duration' => $request->duration,
            'is_square_meters' => $request->has('is_square_meters') ? $request->is_square_meters : true, // Add the new column value
        ]);
        // return response()->json(['message' => 'service created successfully', 'data' => $service], 200);
        return (new ServiceResource($service ))
        ->response()
        ->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return new ServiceResource($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        // Check if a new photo is provided

            // Delete the existing photo if it exists
        if ($request->file('photo')) {
            $avatar = $request->file('photo');
            $photo  =  upload($avatar, public_path('uploads/service_photo'));

        } else {
            $photo = $service->photo;
        }

        $service->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'photo' => $photo,
            'status' => $request->has('status') ? $request->status : 1,
            'duration' => $request->duration,
        ]);

        return (new ServiceResource($service))->response()->setStatusCode(200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        // Check if the service exists
        if (!$service) {
        return response()->json(['message' => 'Service not found'], 404);
        }
        if ($service->photo) {
            // Assuming 'personal_photo' is the attribute storing the file name
            $photoPath = 'uploads/service_photo/' . $service->photo;

            // Delete photo from storage
            Storage::delete($photoPath);
        }

        // Delete the user
        $service->delete();

        return response()->json(['message' => 'العملية تمت بنجاح']);

    }
    public function getServiceCount()
    {
        $count = Service::count();

        return response()->json([
            "successful" => true,
            "message" => "عملية العرض تمت بنجاح",
            'data' => $count
        ]);
    }

}

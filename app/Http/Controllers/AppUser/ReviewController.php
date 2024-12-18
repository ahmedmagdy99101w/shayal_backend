<?php

namespace App\Http\Controllers\AppUser;

use App\Models\Review;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ApartmentResource;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:providers,id',
            'descriptions' => 'nullable|string',
            'rating' => 'required|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $reviews = Review::create([
            'user_id' => Auth::guard('app_users')->user()->id,
            'provider_id' => $request->provider_id,
            'descriptions' => $request->descriptions,
            'rating' => $request->rating,
        ]);
        return response()->json(['isSuccess' => true, 'data' =>  $reviews], 200);
    }

    /**
     * Display the specified resource.
     */



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:providers,id',
            'descriptions' => 'nullable|string',
            'rating' => 'required|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $review->update([
            'user_id' => Auth::guard('app_users')->user()->id,
            'provider_id' => $request->provider_id,
            'descriptions' => $request->descriptions,
            'rating' => $request->rating,

        ]);
        return response()->json(['isSuccess' => true, 'data' =>   $review], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json(['isSuccess' => true], 200);
    }
}

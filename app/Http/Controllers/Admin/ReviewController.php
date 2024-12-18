<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewsResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Reviews retrieved successfully',
            'data' => ReviewsResource::collection($reviews),
        ], Response::HTTP_OK);
    }

    public function show($service_id)
    {
        $review = Review::where('service_id', $service_id)->get();

        if (!$review) {
            return response()->json(['message' => 'Review not found for the given Apartment ID'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Review retrieved successfully',
            'data' => new ReviewsResource($review),
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        $review->delete();
            return response()->json(['message' => 'Review deleted successfully'], Response::HTTP_OK);
    }
}

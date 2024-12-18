<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppUsers;
use Illuminate\Http\Request;
use App\Models\Point;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    public function index()
    {
        // Retrieve points with user information
        $points = Point::with('user')
                       ->select('user_id', DB::raw('SUM(point) as total_points'))
                       ->groupBy('user_id')
                       ->get();

        // Calculate riyals for each user
        $pointsPerRiyal = 5000;
        $amountPerRiyal = 100;

        $userData = [];

        foreach ($points as $point) {
            $riyals = ($point->total_points / $pointsPerRiyal) * $amountPerRiyal;
            $userData[] = [
                'user_id' => $point->user_id,
                'user_name' => $point->user->name,
                'points' => $point->total_points,
                'riyals' => $riyals
            ];
        }

        // Return the data as JSON response
        return response()->json($userData);
    }


}

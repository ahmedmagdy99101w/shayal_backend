<?php

namespace App\Http\Controllers\AppUser;

use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ApartmentResource;
use App\Models\AppUsers;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function myApartments()
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $apartments =  Apartment::where('owner_id', $user->id)->get();
        // dd($apartments);
        if ( $apartments->count() > 0 ) {
            return response()->json(['data'=> ApartmentResource::collection( $apartments) ], 200);
        }
        return response()->json(['error' => 'User not has investment apartments'], 422);

    }

    public function SolidApartments()
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $apartments = Apartment::whereHas('BookedApartments', function ($query) {
            $query->where('paid', 1);
        })->where('owner_id', $user->id)->get();

        if ( $apartments->count() > 0 ) {
            return response()->json(['data'=> ApartmentResource::collection( $apartments) ], 200);

        }
        return response()->json(['error' => ' not exist  apartments solid'], 422);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::guard('app_users')->user();
        if(!empty($user->image)){
            $user->image = asset('uploads/user/' .  $user->image)  ;
        }
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        return response()->json(['data'=>  $user ], 200);
    }
    public function updateProfile(Request $request)
{
    $user = Auth::guard('app_users')->user();
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    $user->name = $request->input('name');
    $user->email = $request->input('email');
    if ($request->has('phone')) {
        $user->phone = "009665" . $request->phone;
    }
    if (request()->has('image') &&  request('image') != '') {
        $avatar = request()->file('image');
        if ($avatar->isValid()) {
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/uploads/user');
            $avatar->move($avatarPath, $avatarName);
            $image  = $avatarName;
        }
    } else {
        $image = $user->image;
    }
    $user->image =$image;
    $user->save();

    return response()->json(['message' => 'Profile updated successfully', 'data' => $user]);
}
    public  function deactive_account(Request $request)
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if ($user) {
            $user->status = 0;
            $user->save();
            return response()->json(['success' => "true"], 200);

        } else {
            return response()->json([ 'error' => "you do not have access"], 200);
        }
    }

}

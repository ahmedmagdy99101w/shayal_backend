<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Notification;
use App\Notifications\UserRegisteredNotification;

class AuthController extends Controller
{


public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid email or password',
        ], 401);
    }
    $user = auth()->user();

    $roles = $user->roles;
    $permissions = $roles->flatMap(function ($role) {
        return $role->permissions->pluck('name');
    });
    return response()->json([
        'access_token' => $token,
        'user' => new UserResource($user),
        "roles" => $roles,
        'permissions' => $permissions,
        'expires_in' => JWTAuth::factory()->getTTL() * 60,
    ]);
}


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
         // send notification to admin new user
         $admins = User::all();
         Notification::send($admins, new UserRegisteredNotification($user));

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'عملية تسجيل الخروج تمت بنجاح']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\AppUsers;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\AppUserResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class UserController extends Controller
{
    protected $user;
    function __construct(User $user)
    {

        $this->user = $user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $user = auth()->user();

        $roles = $user->roles;
        $permissions = $roles->flatMap(function ($role) {
            return $role->permissions->pluck('name');
        });
        return response()->json([

            'user' => new UserResource($user),
            "roles" => $roles,
            'permissions' => $permissions,

        ]);
    }
    public function index(Request $request)
    {
        $users = User::with('permissions')->paginate($request->get('per_page', 50));

        return response()->json([
            'successful' => true,
            'message' => 'Operation retrieved successfully',
            'data' => UserResource::collection($users)
        ], 200);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'national_id' => 'required|string|max:255',
            'photo' => 'nullable',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        if ($request->file('photo')) {
            $avatar = $request->file('photo');
            $photo = upload($avatar,public_path('uploads/personal_photo'));
        } else {
            $photo =null;
        }
        $user = User::create([
            'name' => $request->name,
            'national_id' => $request->national_id,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'photo' => $photo,
            'number' => $request->number,
            'email' => $request->email,
            'roles_name' => $request->roles_name,
            'password' => Hash::make($request->password),

        ]);
        // $user->assignRole([$role->id]);
        $user->assignRole([$request->input('roles_name')]);

        return (new UserResource($user))
        ->response()
        ->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        try {
            // Laravel's route model binding should automatically fetch the user by ID
            // If not found, it will throw a ModelNotFoundException
            $user = User::findOrFail($user->id);

            $userRole = $user->roles->pluck('name', 'name')->all();

            return response()->json([
                'successful' => true,
                'message' => 'Operation retrieved successfully',
                'data' => new UserResource($user)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function update(Request $request, User $user )
    {
        $validator = Validator::make($request->all(), [

            'name' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'photo' => 'nullable',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'email' => 'required|unique:users,email,'. $user->id,
            'password' => 'required|string|min:8',
            'roles_name' => 'required',
        ]);

        if ($validator->fails()) {
            // Debug statements
           // dd($request->all(), $validator->errors()->all());

            return response()->json(['errors' => $validator->errors()]);
        }

        if ($request->file('photo')) {
            $avatar = $request->file('photo');
            $photo = upload($avatar,public_path('uploads/personal_photo'));
        } else {
            $photo = $user->photo;
        }

        // Update user details
        $user->update([
            'name' => $request->name,
            'national_id' => $request->national_id,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'photo' => $photo,
            'number' => $request->number,
            'email' =>$request->email,
            'roles_name' => $request->roles_name,
            'password' => Hash::make($request->password),
        ]);

        // Delete existing roles
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();

        // Assign new roles
        $user->assignRole([$request->input('roles_name')]);

        return (new UserResource($user))
        ->response()
        ->setStatusCode(200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {

        // Delete personal photo if it exists
        if ($user->photo) {
            // Assuming 'personal_photo' is the attribute storing the file name
            $photoPath = 'uploads/personal_photo/' . $user->photo;

            // Delete photo from storage
            Storage::delete($photoPath);
        }

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
    public function getUserCount()
    {
        $count = User::count();
        return response()->json([
            "successful" => true,
            "message" => "عملية العرض تمت بنجاح",
            'data' => $count
        ], 200);
    }

    public function app_user()
    {
        $users =  AppUsers::all();
        return response()->json(['data' =>AppUserResource::collection($users)], 200);
    }
    public function getAppUserCount(){
        $count = AppUsers::count();

        return response()->json([
            "successful" => true,
            "message" => "عملية العرض تمت بنجاح",
            'data' => $count
        ], 200);
    }
}

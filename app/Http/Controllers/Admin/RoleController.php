<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->paginate($request->get('per_page', 50));
        return response()->json($roles);
    }




    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'permission' => 'required',
    //     ]);
    //     $role = Role::create(['name' => $request->input('name')]);
    //     $role->syncPermissions($request->input('permission'));
    //     return response()->json([
    //         'message' => 'true',
    //         'data' => $role,

    //     ], 200);
    // }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permissions' => 'required',
        ]);
        
        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permissions'));
        return response()->json([
            'message' => 'true',
            'data' => $role,
           
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show(Role $role)
    // {
    //     $role =  Role::with('permissions')->where('id', $role->id)->first();

    //     return response()->json($role);


    // }
    public function show(Role $role)
    {
        // Fetch the role with its permissions
        $role = Role::with('permissions')->where('id', $role->id)->first();
    
        // Extract permissions from the role
        $permissions = $role->permissions->pluck('name');
    
        // Return the role ID, name, and permissions as JSON
        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $permissions->toArray(),
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permissions' => 'required',
        ]);


        $role->syncPermissions($request->input('permissions'));
        $role->update(['name' => $request->name]);

        return response()->json([
            'data' => $role,
            'message' => 'Role update successfully',
        ], 200);
    }


    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json([
            'message' => 'Role deleted successfully',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\AppUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Events\ManalNotificationAppUsersEvent;
use App\Notifications\ManalNotificationAppUsers;
use App\Models\ManualNotification;

class ManualNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

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
        $request->validate([
        'user_ids' => 'required|array',
        'title' => 'required|string',
        'message' => 'required|string',
    ]);

    // Retrieve users based on user IDs
    $users = AppUsers::whereIn('id', $request->user_ids)->get();

    // Initialize an array to hold the manual notifications
    $manualNotifications = [];

    // Loop through users to create notifications
    foreach ($users as $user) {
        $manualNotifications[] = ManualNotification::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'message' => $request->message,
        ]);
    }
 
        $firebaseToken = AppUsers::whereIn('id', $request->user_ids)->whereNotNull('device_token')
        ->pluck('device_token')->all();
        sendFirebase($firebaseToken,$request->title,$request->message);

         return response()->json(['message'=>'sent successfuly'], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

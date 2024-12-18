<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
   public function NotificationRead($type)
{
    if (!empty(Auth::guard('users')->user()->notifications)) {
        if ($type == 'member') {
            $notifications = Auth::guard('users')->user()->notifications->where('type', 'App\Notifications\MembershipNotification');
        } elseif ($type == 'booking') {
            $notifications = Auth::guard('users')->user()->notifications->where('type', 'App\Notifications\BookingNotification');
        } elseif ($type == 'register') {

            $notifications = Auth::guard('users')->user()->notifications->where('type', 'App\Notifications\UserRegisteredNotification');

        }
    } else {
        $notifications = [];
    }

    $data = [];
    foreach ($notifications as $notification) {

        $data[] = $notification;
    }

    return response()->json(['isSuccess' => true, 'data' => $data], 200);
}
    public function MarkASRead($type){

        if(!empty(Auth::guard('users')->user()->notifications)){
            if($type =='member'){

                    $notifications  =   Auth::guard('users')->user()->notifications->where('type','App\Notifications\MembershipNotification')->markAsRead();
            }elseif($type =='booking'){
                    $notifications  =   Auth::guard('users')->user()->notifications->where('type','App\Notifications\BookingNotification')->markAsRead();
            }elseif($type =='register'){
                    $notifications  =   Auth::guard('users')->user()->notifications->where('type','App\Notifications\UserRegisteredNotification')->markAsRead();
            }

            return response()->json(['isSuccess' => true], 200);
        }
        return response()->json(['isSuccess' => false], 422);

    }
    public function Clear($type){

        if(!empty(Auth::guard('users')->user()->notifications)){
            if($type =='member'){
                $notifications  =   Auth::guard('users')->user()->notifications()->where('type','App\Notifications\MembershipNotification')->delete();
            }elseif($type =='booking'){
                $notifications  =   Auth::guard('users')->user()->notifications()->where('type','App\Notifications\BookingNotification')->delete();
            }elseif($type =='register'){
                $notifications  =   Auth::guard('users')->user()->notifications()->where('type','App\Notifications\UserRegisteredNotification')->delete();
            }
            return response()->json(['isSuccess' => true,'data'=> $notifications ], 200);
        }

        return response()->json(['isSuccess' => false,'error' => 'user it has no notification'], 401);
    }
}

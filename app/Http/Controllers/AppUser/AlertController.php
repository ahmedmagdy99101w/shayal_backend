<?php

namespace App\Http\Controllers\AppUser;

use App\Http\Controllers\Controller;
use App\Jobs\SendReminderNotification;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'nullable|string',
            'date' => 'required|date',
            // 'time' => 'required|date_format:H:i',
            'service_id' => 'required|exists:sub_services,id',
        ]);
        $user = Auth::guard('app_users')->user();
        $reminder = Alert::create([
            'text' => $request->text,
            'date' => $request->date,
            'time' => $request->time,
            'user_id' => $user->id,
            'service_id' => $request->service_id,
        ]);

        // جدولة التذكير بعد إنشائه
        $this->scheduleReminderNotification($reminder);

        return response()->json(['message' => 'Reminder created successfully']);
    }

    private function scheduleReminderNotification($reminder)
    {
        $reminderDateTime = $reminder->date . ' ' . $reminder->time;

        // Schedule the job to be executed at the reminder time
        SendReminderNotification::dispatch($reminder)->delay(now()->parse($reminderDateTime));
    }
 
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')
        ->toArray();
        if (isset($settings['site_logo_single'])) {
            $settings['site_logo_single'] = asset('uploads/settings/' . $settings['site_logo_single']);
        }
        if (isset($settings['site_logo_full'])) {
            $settings['site_logo_full'] = asset('uploads/settings/' . $settings['site_logo_full']);
        }
        if (isset($settings['site_logo_dark'])) {
            $settings['site_logo_dark'] = asset('uploads/settings/' . $settings['site_logo_dark']);
        }

    return  $settings;
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
        $validator = Validator::make($request->all(), [
          'site_logo_single '=>'',
          "site_logo_single" => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
          "site_logo_full" => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
          "site_logo_dark" => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
          "site_name_ar" => '',
          "site_name_en" => '',
          "info_email" => '',
          "mobile" => '',
          "tax_added_value" => '',
          "tiktok" => '',
          "instagram" => '',
          "snapchat" => '',
          "twitter" => '',
          "siteMaintenanceMsg" => '',
          "maintenance_mode" => '',
            'available_bookings' => 'integer',
           'link_app_ios' => '',
            'link_app_android' => '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }


        foreach ($validator->validated() as $key => $input) {
            if (($key === 'site_logo_single' || $key === 'site_logo_full' || $key === 'site_logo_dark') && $request->hasFile($key) && $request->file($key)->isValid()) {
                $avatar = $request->file($key);
                $avatarName = time() . '_' . $key . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path('uploads/settings'), $avatarName);
                $input = $avatarName;
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $input]);
        }

        // Fetch the stored settings after the update
        $storedSettings = Setting::pluck('value', 'key')->toArray();

        // Update the site_logo URLs in the settings array if they exist
        if (isset($storedSettings['site_logo_single'])) {
            $storedSettings['site_logo_single'] = asset('uploads/settings/' . $storedSettings['site_logo_single']);
        }
        if (isset($storedSettings['site_logo_full'])) {
            $storedSettings['site_logo_full'] = asset('uploads/settings/' . $storedSettings['site_logo_full']);
        }
        if (isset($storedSettings['site_logo_dark'])) {
            $storedSettings['site_logo_dark'] = asset('uploads/settings/' . $storedSettings['site_logo_dark']);
        }

        return response()->json(['isSuccess' => true, 'data' => $storedSettings], 200);
    }


}

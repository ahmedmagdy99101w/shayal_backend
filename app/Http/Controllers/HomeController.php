<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function contactUs(Request $request)
    {
        $data =$request->all();
         ContactUs::create($data);
         return response()->json(['isSuccess' => true], 200);
    }
    public function Settings()
    {
        $settings = Setting::pluck('value', 'key')
        ->toArray();
        $image1 = asset('uploads/settings/' .  $settings['site_logo_single']);
        $settings['site_logo_single'] =    $image1;
        $image2 = asset('uploads/settings/' .  $settings['site_logo_full']);
        $settings['site_logo_full'] =    $image2;
        $image3 = asset('uploads/settings/' .  $settings['site_logo_dark']);
        $settings['site_logo_dark'] =    $image3;
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
        //
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

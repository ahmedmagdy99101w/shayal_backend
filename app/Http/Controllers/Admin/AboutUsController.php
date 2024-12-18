<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function index()
    {
        $aboutUs = AboutUs::first();

        return response()->json(['data'=>  $aboutUs]);
    }

    public function update(Request $request)
    {
        $aboutUs = AboutUs::first();
        if ($aboutUs) {
            $aboutUs->update($request->all());
        } else {
            $aboutUs = AboutUs::create($request->all());
        }
        return response()->json(['data'=>  $aboutUs]);
    }
}

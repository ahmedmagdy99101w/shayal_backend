<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Privacy;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    public function index()
    {
        $privacy = Privacy::first();
        return response()->json(['data'=> $privacy], 200);

    }
    public function update(Request $request)
    {
        $privacy = Privacy::first();
        if ($privacy) {
            $privacy->update($request->all());
        } else {
            $privacy = Privacy::create($request->all());
        }
        return response()->json(['data'=> $privacy], 200);

    }
}

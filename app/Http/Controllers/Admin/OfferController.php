<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::first();
        return response()->json(['data'=> $offers], 200);

    }
    public function update(Request $request)
    {
        $offers = Offer::first();
        if ($offers) {
            $offers->update($request->all());
        } else {
            $offers = Offer::create($request->all());
        }
        return response()->json(['data'=> $offers], 200);

    }
}

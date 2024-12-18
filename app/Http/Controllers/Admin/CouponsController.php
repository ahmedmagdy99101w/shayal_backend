<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;

class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::get();

        return response()->json(['data'=> $coupons], 200);
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
    public function store(CouponRequest $request)
    {
        $coupons = new Coupon();
        $coupons->discount_code = $request->discount_code;
        $coupons->type = $request->type;
        $coupons->start_date = $request->start_date;
        $coupons->end_date = $request->end_date;
        $coupons->discount =  $request->discount;
        $coupons->discount_percentage =  $request->discount_percentage;
        $coupons->max_usage = $request->max_usage;
        $coupons->max_discount_value = $request->max_discount_value;
        $coupons->save();
        return response()->json(['data'=> $coupons], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        return response()->json(['data'=> $coupon], 200);
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
    public function update(CouponRequest $request,Coupon $coupon)
    {

        $coupon->discount_code = $request->discount_code;
        $coupon->type = $request->type;
        $coupon->start_date = $request->start_date;
        $coupon->end_date = $request->end_date;
        $coupon->discount =   $request->discount  ;
        $coupon->discount_percentage = $request->discount_percentage;
        $coupon->max_usage = $request->max_usage;
        $coupon->max_discount_value = $request->max_discount_value;
        $coupon->save();

        return response()->json(['data'=> $coupon], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(null, 204);
    }
}

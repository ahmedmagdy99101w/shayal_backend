<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ControlBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ControlBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $control_bookings = ControlBooking::all();
        return response()->json($control_bookings, 201);

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
            'date' => 'required',
            'max_number' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $control_bookings = ControlBooking::create($request->all());

        return response()->json($control_bookings, 201);
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'max_number' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        $control_booking = ControlBooking::find($id);
        $control_booking = $control_booking->update($request->all());

        return response()->json($control_booking, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $control_booking = ControlBooking::find($id);

        if (!$control_booking) {
            return response()->json(['isSuccess' => false, 'message' => 'Item not found'], 404);
        }

        $control_booking->delete();
        return response()->json(['isSuccess' => true], 200);
    }

}
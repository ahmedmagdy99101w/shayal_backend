<?php

namespace App\Http\Controllers\Provider;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Service;
use App\Events\BookedEvent;
use Illuminate\Http\Request;
use App\Services\TabbyPayment;
use App\Services\paylinkPayment;
use App\Services\TammaraPayment;
use App\Services\WatsapIntegration;
use App\Http\Controllers\Controller;
use App\Jobs\SendReminderNotification;
use App\Models\ControlBooking;
use App\Models\Coupon;
use App\Models\OfferSubmitted;
use App\Models\Point;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppUserBooking;
use Illuminate\Support\Facades\Validator;
use App\Notifications\BookingNotification;
use App\Services\WatsapIntegrationCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use League\CommonMark\Extension\TableOfContents\TableOfContentsBuilder;

class BookingProviderController extends Controller
{
   
    public function Bookings()
    {

       
        $bookings = Booking::with('service')->get();

        return response()->json(['bookings' => $bookings], 200);
    }

    public function submitBooking($id, Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'time' => 'required',
            'date' => 'required',
            'price' => 'required|numeric',
            'note' => 'nullable|string',
        ]);
    
        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = Auth::guard('providers')->user();
        if (!$user) {
            return response()->json(['error' => 'سجل الدخول اولا'], 401);
        }
        // Check if the booking exists
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'الحجز غير موجود'], 404);
        }
    
        try {
            // Create a new offer in the `offer_submitteds` table
            $offerSubmitted = OfferSubmitted::create([
                'provider_id'=> $user->id,
                'booking_id' => $booking->id,
                'date' => $request->date,
                'time' => $request->time,
                'price' => $request->price,
                'note' => $request->note,
            ]);
    
            // Return success response
            return response()->json([
                'message' => 'تم تقديم الطلب',
                'offer' => $offerSubmitted,
            ], 201);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json(['error' => 'حدث خطأ أثناء تقديم الطلب: ' . $e->getMessage()], 500);
        }
    }
    
    public function change_status_bookings(Request $request, $id)
    {
        // Authenticate user
        $user = Auth::guard('providers')->user();
        if (!$user) {
            return response()->json(['error' => 'Provider not authenticated'], 401);
        }
    
        // Validate the input
        $validated = $request->validate([
            'status' => 'required|in:prepared,executed,canceld', // Ensure status is valid
        ]);
    
        try {
            // Find the booking and ensure it belongs to the authenticated user
            $offer = OfferSubmitted::with('booking')->where('id', $id)->first();
            if (!$offer) {
                return response()->json(['error' => 'offer not found or does not belong to the user'], 404);
            }
    
            $booking = $offer->booking; // Retrieve the related booking
            $booking->status = $validated['status'];
            $booking->save(); // Save the updated booking
    
            return response()->json(['message' => 'Booking status updated successfully', 'booking' =>  $offer], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
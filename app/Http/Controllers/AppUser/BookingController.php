<?php

namespace App\Http\Controllers\AppUser;

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

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $paylink;
    public $tabby;
    public $tammara;
    public function __construct()
    {
        $this->paylink = new paylinkPayment();
        $this->tabby = new TabbyPayment();
        $this->tammara = new TammaraPayment();
    }
    public function userBookings()
    {

        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $bookings = Booking::with('service')->where('user_id', $user->id)->get();

        return response()->json(['bookings' => $bookings], 200);
    }
    public function offer_submitteds()
{
    // Get the authenticated user
    $user = Auth::guard('app_users')->user();
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    try {
        // Get offers submitted for the user's bookings
        $offers = OfferSubmitted::whereHas('booking', function ($query) use ($user) {
            $query->where('user_id', $user->id); // Filter bookings belonging to the user
        })->with('booking')->get();

        return response()->json(['offers' => $offers], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

public function change_status_bookings(Request $request, $id)
{
    // Authenticate user
    $user = Auth::guard('app_users')->user();
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    // Validate the input
    $validated = $request->validate([
        'status' => 'required|in:accepted,canceld', // Ensure status is valid
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
    public function getServiceDetails($serviceId)
    {

        $service = Service::with('optionTypes.options')->find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Format the data to include option types and options
        $serviceDetails = [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => $service->price,
            'option_types' => []
        ];

        foreach ($service->optionTypes as $optionType) {
            $options = $optionType->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'key' => $option->key,

                    'price' => $option->price,

                ];
            });

            $serviceDetails['option_types'][] = [
                'id' => $optionType->id,
                'key' => $optionType->key,
                'type' => $optionType->type,
                'options' => $options,
            ];
        }

        return response()->json($serviceDetails);
    }

    public function bookService(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'from_city_id' => 'required|exists:cities,id',
            'to_city_id' => 'required|exists:cities,id',
            'time' => 'required',
            'date' => 'required',
            'item_moveds' => 'array',
            'item_moveds.*' => 'exists:item_moveds,id', // Ensure each item exists
            'images' => 'array',
            'images.*' => 'file|mimes:jpg,jpeg,png|max:2048', // Validate uploaded files
            'note' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'سجل الدخول اولا'], 401);
        }
    
        try {
            // Create the booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'service_id' => $request->service_id,
                'from_city_id' => $request->from_city_id,
                'to_city_id' => $request->to_city_id,
                'date' => $request->date,
                'time' => $request->time,
                'note' => $request->note,
            ]);
    
            // Attach item_moveds
            if ($request->has('item_moveds') && is_array($request->item_moveds)) {
                $booking->item_moveds()->attach($request->item_moveds);
            }
    
            // Handle uploaded images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filePath = $file->store('uploads/booking_images', 'public');
                    $booking->images()->create(['image' => $filePath]);
                }
            }
    
            return response()->json([
                'message' => 'تم تقديم الطلب',
                'booking' => $booking->load('item_moveds', 'images', 'service'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()], 500);
        }
    }
    
    // if ($request->payment == 'Tabby') {
    //     $items = collect([]);
    //     $items->push([
    //         'title' => 'title',
    //         'quantity' => 2,
    //         'unit_price' => 20,
    //         'category' => 'Clothes',
    //     ]);;

    //     $order_data = [
    //         'amount' =>  $total_price,
    //         'currency' => 'SAR',
    //         'description' => 'description',
    //         'full_name' => $booking->user->name ?? 'user_name',
    //         'buyer_phone' => $booking->user->phone ?? '9665252123',
    //         // 'buyer_email' => 'card.success@tabby.ai',//this test
    //         'buyer_email' =>  $booking->user->email ?? 'user@gmail.com',
    //         'address' => 'Saudi Riyadh',
    //         'city' => 'Riyadh',
    //         'zip' => '1234',
    //         'order_id' => "$booking->id",
    //         'registered_since' => $booking->created_at,
    //         'loyalty_level' => 0,
    //         'success-url' => route('success-ur'),
    //         'cancel-url' => route('cancel-ur'),
    //         'failure-url' => route('failure-ur'),
    //         'items' =>  $items,
    //     ];

    //     $payment = $this->tabby->createSession($order_data);

    //     $id = $payment->id;

    //     $redirect_url = $payment->configuration->available_products->installments[0]->web_url;
    //     return  $redirect_url;
    // } elseif ($request->payment == 'Paylink') {

    //     $data = [
    //         'amount' => $total_price,
    //         'callBackUrl' => route('paylink-result'),
    //         'clientEmail' => $booking->user->email ?? 'test@gmail.com',
    //         'clientMobile' => $booking->user->phone ?? '9665252123',
    //         'clientName' => $booking->user->name ?? 'user_name',
    //         'note' => 'This invoice is for VIP client.',
    //         'orderNumber' => $booking->id,
    //         'products' => [
    //             [
    //                 'description' => $booking->service->description ?? 'description',
    //                 'imageSrc' =>  $booking->service->photo,
    //                 'price' => $total_price ?? 1,
    //                 'qty' => 1,
    //                 'title' => $booking->service->name ?? 'title',
    //             ],
    //         ],
    //     ];


    //     return $this->paylink->paymentProcess($data);
    // } elseif ($request->payment == 'Tammara') {
    //     $consumer = [
    //         'first_name' =>  $booking->user->name,
    //         'last_name' => $booking->user->name,
    //         'phone' => $booking->user->phone,
    //         'email' => $booking->user->email ?? 'test@test.com',
    //     ];

    //     $billing_address = [
    //         'first_name' => $booking->user->name,
    //         'last_name' =>  $booking->user->name,
    //         'line1' =>  $request->address ?? 'Riyadh',
    //         'city' =>  $request->address ?? 'Riyadh',
    //         'phone' => $booking->user->phone,
    //     ];

    //     $shipping_address = $billing_address;
    //     $order = [
    //         'order_num' =>$booking->id,
    //         'total' => $booking->total_price,
    //         'notes' => 'notes',
    //         'discount_name' => 'discount coupon',
    //         'discount_amount' => 0,
    //         'vat_amount' => 0,
    //         'shipping_amount' => 0,
    //     ];
    //     $products[] = [
    //         'id' => $booking->service_id,
    //         'type' => 'حجز خدمة',
    //         'name' =>  $booking->service->name,
    //         'sku' => 'SA-12436',
    //         'image_url' => $booking->service->photo,
    //         'quantity' => 1,
    //         'unit_price' => $booking->service->price,
    //         'discount_amount' => 0,
    //         'tax_amount' => 0,
    //         'total' => $booking->service->price,
    //     ];

    //     dd($this->tammara->paymentProcess($order, $products, $consumer, $billing_address, $shipping_address));
    // } else {
    //     return response()->json(['message' => 'choose payment ',], 422);
    // }


    // }


}
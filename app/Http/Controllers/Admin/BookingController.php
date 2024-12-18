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

    public function bookMultipleServices(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'date'           => 'required|date_format:m-d-Y',
            'time'           => 'required',
            'area_id' => 'required|exists:areas,id',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'Please login first'], 401);
        }

        // $requestedDate = Carbon::createFromFormat('m-d-Y', $request->date);
        // if ($requestedDate->isPast()) {
        //     return response()->json(['error' => 'The entered date is in the past'], 422);
        // }
        $requestedDate = Carbon::createFromFormat('m-d-Y', $request->date);
        if ($requestedDate->isBefore(Carbon::today())) {
            return response()->json(['error' => 'The entered date is in the past'], 422);
        }

        //     /////////////////////////
        $convertedDate = Carbon::createFromFormat('m-d-Y', $request->date)->format('Y-m-d');
        $startTime = Carbon::createFromFormat('h:i A', $request->time)->format('H:i:s');
        $carts = Cart::where('user_id', $user->id)->get();
        $items = [];
        $items_subscriptions = [];
        $filteredItems = [];
        $error = false;
        $subscription_flag = false;
        if ($carts->isEmpty()) {
            return response()->json([
                'error' => 'cart is empty'
            ], 422);
        }
        $existingDate = ControlBooking::where('date', $request->date)->first();
        if ($existingDate) {
            $existingOrders = Order::where('date', $request->date)->get();
            foreach ($existingOrders as $existingOrder) {
                if ($existingDate->max_number >= $existingOrder->count_booking) {
                    dd($carts);
                     foreach($carts as $cart){
                             $cart->delete();
                     }
                    return response()->json(['message' => 'Booking is closed for this date']);
                }
            }
        }
        foreach ($carts as $cart) {
            $service = Service::where('id', $cart->service_id)->first();
            $existingBookings = Booking::where('service_id', $service->id)
                ->where('date', $convertedDate)
                ->where('paid', 1)
                ->get();

            if ($existingBookings->isNotEmpty()) {
                foreach ($existingBookings as $existingBooking) {
                    $existingEndTime = Carbon::createFromFormat('H:i:s', $existingBooking->time)
                        ->addHours(4)
                        ->format('H:i:s');
                    $bookingStart = Carbon::createFromFormat('H:i:s', $existingBooking->time);
                    $bookingEnd = Carbon::createFromFormat('H:i:s', $existingEndTime);
                    $desiredStart = Carbon::createFromFormat('H:i:s', $startTime)->addMinutes(1);
                    $desiredEnd = $desiredStart->copy()->addHours(4);

                    if ($desiredStart->between($bookingStart, $bookingEnd, true)) {
                        $error = true;
                        break;
                    }
                }

                if ($error) {
                    return response()->json([
                        'error' => 'تم حجز هذه الخدمة بالفعل. يرجى اختيار وقت آخر وبعد 4 ساعات'
                    ], 422);
                }
            }

            $cost = $cart->price;
            $booking = Booking::create([
                'user_id'     => $user->id,
                'service_id'  => $service->id,
                'date'        => $convertedDate,
                'time'        => $startTime,
                'name'        => $request->name ?? $user->name,
                'phone'       => $request->phone ?? $user->phone,
                'total_price' => $cost,
            ]);

            if ($error) {
                return response()->json([
                    'error' => 'تم حجز هذه الخدمة بالفعل. يرجى اختيار وقت آخر وبعد 4 ساعات'
                ], 422);
            }

            $items[] = [
                'id'    => $service->id,
                'booked_id' => $booking->id,
                'total' => $cost,
            ];
        }


        $totalCost = collect($items)->sum('total') ?? 0.0;
        if ($request->has('coupon_code') && !empty($request->coupon_code)) {
            $coupon_data = checkCoupon($request->coupon_code, $totalCost);
            if ($coupon_data && $coupon_data['status'] == true) {
                $totalCost = $coupon_data['price_after_discount'];
            } else {
                return response()->json(['status' => false, 'message' => $coupon_data['message']], 310);
            }
        }
        if ($request->points == true) {
            if ($riyals = calculateRiyalsFromPoints($user->id) > 0) {
                $totalCost -= $riyals;
            }
        }
        $order = Order::create([
            'user_id'     => $user->id,
            'total_price' => $totalCost,
            'date'        => $convertedDate,
            'time'        => $startTime,
            'area_id' => $request->area_id,
            'coupon_id' => $request->has('coupon_code') ? $coupon_data['id'] : 0,
            'discount_price' => $request->has('coupon_code') ? $coupon_data['discount'] : 0,
            'price_befor_discount' => collect($items)->sum('total') ?? 0.0,
            'points' => $request->points == true ? 1 : 0
        ]);
        $bookings = [];
        foreach ($items as $item) {
            $booking = Booking::where('id', $item['booked_id'])->first();
            $booking->order_id = $order->id;
            $booking->save();
        }
        if ($request->payment == 'cash') {
            return $this->CashMethod($order);
        } elseif ($request->payment == 'Tabby') {
            $items = collect([]);
            $items->push([
                'title' => 'title',
                'quantity' => 1,
                'unit_price' => 20,
                'category' => 'Clothes',
            ]);

            $order_data = [
                'amount' => $totalCost,
                'currency' => 'SAR',
                'description' => 'description',
                'full_name' =>  $order->user->name ?? 'user_name',
                'buyer_phone' =>  $order->user->phone ?? '9665252123',
                'buyer_email' => 'card.success@tabby.ai', //this test
                // 'buyer_email' =>   $order->user->email ?? 'user@gmail.com',
                'address' => 'Saudi Riyadh',
                'city' => 'Riyadh',
                'zip' => '1234',
                'order_id' => " $order->id",
                'registered_since' =>  $order->created_at,
                'loyalty_level' => 0,
                'success-url' => route('success-ur'),
                'cancel-url' => route('cancel-ur'),
                'failure-url' => route('failure-ur'),
                'items' =>  $items,
            ];

            $payment = $this->tabby->createSession($order_data);

            $id = $payment->id;

            $redirect_url = $payment->configuration->available_products->installments[0]->web_url;
            return  $redirect_url;
        } elseif ($request->payment == 'Paylink') {

            $data = [
                'amount' => $totalCost,
                'callBackUrl' => route('paylink-result'),
                'clientEmail' =>  $order->user->email ?? 'test@gmail.com',
                'clientMobile' =>  $order->user->phone ?? '9665252123',
                'clientName' =>  $order->user->name ?? 'user_name',
                'note' => 'This invoice is for VIP client.',
                'orderNumber' =>  $order->id,
                'products' => [
                    [
                        'description' => 'description',
                        'imageSrc' =>  'image',
                        'price' =>  1,
                        'qty' => 1,
                        'title' =>  'title',
                    ],
                ],
            ];


            return $this->paylink->paymentProcess($data);
        }
        //  elseif ($request->payment == 'Tammara') {
        //     $consumer = [
        //         'first_name' =>  $order->user->name,
        //         'last_name' => $order->user->name,
        //         'phone' => $order->user->phone,
        //         'email' => $order->user->email ?? 'test@test.com',
        //     ];

        //     $billing_address = [
        //         'first_name' => $order->user->name,
        //         'last_name' =>  $order->user->name,
        //         'line1' =>  $request->address ?? 'Riyadh',
        //         'city' =>  $request->address ?? 'Riyadh',
        //         'phone' => $order->user->phone,
        //     ];

        //     $shipping_address = $billing_address;
        //     $order = [
        //         'order_num' =>$order->id,
        //         'total' =>  $totalCost,
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
        // }
        else {
            return response()->json(['message' => 'choose payment ',], 422);
        }

        return response()->json(['message' => 'عملية الحجز تمت بنجاح'], 201);
    }


    public function show(string $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        return response()->json(['booking' => $booking], 200);
    }



    public function cancelBooking($id)
    {

        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        $user = Auth::guard('app_users')->user();
        if (!$user || $booking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $booking->status = 'canceld';
        $booking->save();
        $order =Order::find($booking->order_id);
        $data =  [
            'name' =>$user->name,
            'date'=> $booking->date,
            'time'=> $booking->time,
            'area' => $order->area->name,
            'city' => $order->area->city->name,
            'message' => 'تم الغاء الحجز  ',
        ];
        $watsap =   new WatsapIntegration( $data);
        $watsap->Process();
      //////to customer
      $data =  [
        'phone' =>$user->phone,
        'message' => 'تم تأكيد الغاء الحجز  ',
    ];
    $watsap1 =   new WatsapIntegrationCustomer( $data);
    $watsap1->Process();
        return response()->json(['message' => 'Booking canceled successfully'], 200);
    }
    public function checkCoupon(Request $request)
    {
        return checkCoupon($request->couponCode, $request->totalAmount);
    }
    public function sucess(Request $request)
    {
        return   $this->tabby->calbackPayment($request);
    }
    public function cancel(Request $request)
    {
        return response()->json(["error" => 'error', 'Data' => 'payment canceld'], 404);
    }
    public function failure(Request $request)
    {
        return response()->json(["error" => 'error', 'Data' => 'payment failure'], 404);
    }
    public function paylinkResult(Request $request)
    {

        return   $this->paylink->calbackPayment($request);
    }
       public function scheduleReminderNotification($reminder)
    {
        dd($reminder);
        $reminderDateTime = $reminder->date . ' ' . $reminder->time;

        // Schedule the job to be executed at the reminder time
        SendReminderNotification::dispatch($reminder)->delay(now()->parse($reminderDateTime));
    }

    public function CashMethod($order)
    {
        try {
            DB::beginTransaction();
            $existingOrder = Order::where('date', $order->date)->get();
            if ($existingOrder->isNotEmpty()) {
                $order->count_booking++;
                $order->save();
            }
            $bookeds = Booking::where('order_id', $order->id)->get();
            Cart::where('user_id',  $order->user->id)->delete();
            if ($order->points != 0) {
                Point::where('user_id', $order->user->id)->delete();
            }
            Point::create([
                'order_id' => $order->id,
                'user_id' => $order->user->id,
                'point' => 25
            ]);
            if ($order->coupon_id != 0) {
                Coupon::where('id', $order->coupon_id)->decrement('max_usage');
            }
            $data =  [
                'name' => $order->user->name,
                'date' => $order->date,
                'time' => $order->time,
                'area' => $order->area->name,
                'city' => $order->area->city->name,
                'message' => 'لديك حجز جديد ',
            ];
            $watsap = new WatsapIntegration($data);
            $watsap->Process();
            $data = [
                'phone' => $order->user->phone,
                'message' => 'تم تأكيد الحجز',
            ];
            $watsap1 = new WatsapIntegrationCustomer($data);
            $watsap1->Process();
         return   $this->scheduleReminderNotification($order);
            DB::commit();
            return response()->json(['message' => 'payment created successfully'], 201);
        } catch (\Throwable $th) {
            dd($th->getMessage(), $th->getLine());
            DB::rollBack();
            return response()->json(["error" => 'error', 'Data' => 'payment failed'], 404);
        }
    }
}
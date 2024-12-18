<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Point;
use App\Models\Coupon;
use App\Models\Booking;
use App\Models\Membership;
use App\Events\BookedEvent;
use App\Models\OrderPayment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\PaymentGetway;
use App\Models\PaymentGeteway;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Notifications\AppUserBooking;
use Illuminate\Support\Facades\Config;
use App\Notifications\BookingNotification;
use App\Services\contracts\PaymentInterface;
use Illuminate\Support\Facades\Notification;
use App\Services\WatsapIntegrationSubscription;


class TabbyPayment
{
    public function __construct()
    {

        // $tabby = PaymentGetway::where([
        //     ['keyword', 'Tabby'],
        // ])->first();
        // $tabbyConf = json_decode($tabby->information, true);
        // dd($tabbyConf);
        // Config::set('services.tabby.pk_test ',$tabbyConf["pk_test"]);
        // Config::set('services.tabby.sk_test  ',$tabbyConf["sk_test"]);
        // Config::set('services.tabby.base_url','https://api.tabby.ai/api/v2/');

        Config::set('services.tabby.pk_test', 'pk_test_7f9a63b4-4c7e-4289-8ab3-df58fb021fe8');
        Config::set('services.tabby.sk_test', 'sk_test_e5734624-8b6c-466c-ba73-445447105365');
        Config::set('services.tabby.base_url', 'https://api.tabby.ai/api/v2/');
    }






    public function createSession($data)
    {

        $body = $this->getConfig($data);

        $http = Http::withToken(Config::get('services.tabby.pk_test'))->baseUrl(Config::get('services.tabby.base_url'))->withoutVerifying();

        $response = $http->post('checkout', $body);

        return $response->object();
    }
    public function getSession($payment_id)
    {

        $http = Http::withToken(Config::get('services.tabby.sk_test'))->baseUrl(Config::get('services.tabby.base_url'))->withoutVerifying();

        $url = 'payments/' . $payment_id;

        $response = $http->get($url);

        return $response->object();
    }

    public function getConfig($data)
    {
        $body = [];

        $body = [
            "payment" => [
                // "is_test"=>true,
                "amount" => $data['amount'],
                "currency" => $data['currency'],
                "description" =>  $data['description'],
                "buyer" => [
                    "phone" => $data['buyer_phone'],
                    "email" => $data['buyer_email'],
                    "name" => $data['full_name'],
                    "dob" => "2019-08-24"
                ],
                "shipping_address" => [
                    "city" => $data['city'],
                    "address" =>  $data['address'],
                    "zip" => $data['zip'],
                ],
                "order" => [
                    "tax_amount" => "0.00",
                    "shipping_amount" => "0.00",
                    "discount_amount" => "0.00",
                    "updated_at" =>  "2019-08-24T14:15:22Z",
                    "reference_id" => $data['order_id'],
                    "items" =>
                    $data['items'],
                ],
                "buyer_history" => [
                    "registered_since" => $data['registered_since'],
                    "loyalty_level" => $data['loyalty_level'],
                ],
            ],
            "lang" => app()->getLocale(),
            "merchant_code" => "شركة التنظيف الاعمقsau",
            "merchant_urls" => [
                "success" => $data['success-url'],
                "cancel" => $data['cancel-url'],
                "failure" => $data['failure-url'],
            ]
        ];

        return $body;
    }

    public function calbackPayment(Request $request)
    {
        $response = $this->getSession($request->payment_id);

        if ($response->status == "CLOSED") {
            try {
                DB::beginTransaction();
                $order = Order::where('id', $response->order->reference_id)->first();
                $existingOrder = Order::where('date', $order->date)->get();
                 if($existingOrder->isNotEmpty() ){
                 $order->count_booking++;
                 $order->save();
                 }
                $bookeds= Booking::where('order_id', $order->id)->get();
                  foreach ($bookeds as $booked) {
                    $booked->paid = 1;
                    $booked->save();
                $adminUsers = User::where('roles_name', 'Admin')->get();
                foreach ($adminUsers as $adminUser) {
                    Notification::send($adminUser, new BookingNotification($booked));
                }
                $order->user->notify(new AppUserBooking($booked->service));

                BookedEvent::dispatch($booked->service);
                  }
                  Cart::where('user_id',  $order->user->id)->delete();

                $order_payment =  OrderPayment::create([
                    'payment_type' => 'Tabby',
                    'customer_name' => $response->buyer->name,
                    'transaction_id' => $request->payment_id,
                    'order_id' => $order->id,
                    'price' =>   $response->amount,
                    'transaction_status' => $response->status,
                    'is_success' => true,
                ]);
                if ($order->points != 0) {
                    Point::where('user_id', $order->user->id)->delete();
                }
                    Point::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user->id,
                        'point' => 25
                    ]);
                    ///////////
                    if ($order->coupon_id != 0) {
                        Coupon::where('id', $order->coupon_id)->decrement('max_usage');
                    }
                $data =  [
                    'name' => $order->user->name,
                    'date'=> $order->date,
                    'time'=> $order->time,
                    'area' => $order->area->name,
                    'city' => $order->area->city->name,
                    'message' => 'لديك حجز جديد ',
                ];
                $watsap =   new WatsapIntegration( $data);
                $watsap->Process();
                $data =  [
                    'phone' =>$order->user->phone,
                    'message' => 'تم تأكيدالحجز  ',
                ];
                $watsap1 =   new WatsapIntegrationCustomer( $data);
                $watsap1->Process();
                DB::commit();
                return response()->json(['message' => 'payment created successfully'], 201);
            } catch (\Throwable $th) {
              dd($th->getMessage(),$th->getLine());
                DB::rollBack();
                return response()->json(["error" => 'error', 'Data' => 'payment failed'], 404);
            }
        }
    }

    public function calbackPaymentSubscription(Request $request)
    {
        $response = $this->getSession($request->payment_id);

        if ($response->status == "CLOSED") {
            try {
                DB::beginTransaction();
                $booked = Membership::where('id', $response->order->reference_id)->first();
                $booked->paid = 1;
                $booked->visit_count += 1;
                $booked->save();
                $order =  SubscriptionPayment::create([
                    'payment_type' => 'Tabby',
                    'customer_name' => $response->buyer->name,
                    'transaction_id' => $request->payment_id,
                    'membership_id' => $booked->id,
                    'price' =>   $response->amount,
                    'transaction_status' => $response->status,
                    'is_success' => true,
                ]);
                $data =  [

                    'name' => $booked->user->name,
                    'subscription' => $booked->subscription->name,
                    'message' => 'لديك اشتراك جديد ',
                ];
                $watsap =   new WatsapIntegrationSubscription($data);
                $watsap->Process();
                $adminUsers = User::where('roles_name', 'Admin')->get();
                foreach ($adminUsers as $adminUser) {
                    Notification::send($adminUser, new BookingNotification($booked));
                }

                $booked->user->notify(new AppUserBooking($booked->subscription));
                BookedEvent::dispatch($booked->subscription);

                DB::commit();
                return response()->json(['message' => 'payment created successfully'], 201);
            } catch (\Throwable $th) {
                dd($th->getMessage(), $th->getLine());
                DB::rollBack();
                return response()->json(["error" => 'error', 'Data' => 'payment failed'], 404);
            }
        }
    }
}

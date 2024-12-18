<?php

namespace App\Services;

use Maree\Tamara\Tamara;
use Illuminate\Http\Request;
use App\Models\PaymentGetway;
use App\Models\PaymentGeteway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use App\Services\contracts\PaymentInterface;


class TammaraPayment
{

    public function __construct()
    {

        // $tammara = PaymentGetway::where([
        //     ['keyword', 'Tammara'],
        // ])->first();
        // $tammaraConf = json_decode($tammara->information, true);
        // Config::set('services.tammara.api_token', $tammaraConf["token"]);

    }

    public function paymentProcess($order,$products,$consumer,$billing_address,$shipping_address)
    {

        $urls = ['success' => route('tammara-result'), 'failure' =>  route('tammara-result'), 'cancel' => route('tammara-result'), 'notification' => 'tammara-result'];
        $respone = (new Tamara())->createCheckoutSession($order, $products, $consumer, $billing_address, $shipping_address, $urls);
     dd($respone);
        return $respone['checkout_url'];
    }
    public function calbackPayment(Request $request)
    {
        $response = (new Tamara())->getOrderDetails($request->orderId);
          dd($request->paymentStatus,$response);

        // if ($request->paymentStatus == 'approved') {

        // 	//update order payment status
        // $payment = new OrderPayment();
        // $payment->order_id = $response['order_id'];
        // $payment->order_number = $response['order_number'];
        // $payment->consumer = $response['consumer'];
        // $payment->status = $response['status'];
        // $payment->billing_address =$response['billing_address'];
        // $payment->shipping_address =$response['shipping_address'];
        // $payment->items = $response['items'];
        // $payment->transactions = $response['transactions'];
        // $payment->save();

        //   $order =  Orders::where('order_number',$response['order_number'])->first();
        //   $order->paid = 1;
        //   $order->save();
        //   return response()->json([
        //                 'success' => true,
        //                 'data'=>'العمليه تمت بنجاج',
        //             ]);
        // } else {
        //    return response()->json([
        //                 'success' => false,
        //                  'data'=>'فشلت العمليه',
        //             ]);
        // }
    }
}

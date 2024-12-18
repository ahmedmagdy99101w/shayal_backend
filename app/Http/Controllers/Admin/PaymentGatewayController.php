<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PaymentGetway;
use App\Http\Controllers\Controller;
class PaymentGatewayController extends Controller
{
    public function index()
    {
        $data = [];

        $paymentGateway = PaymentGetway::where('keyword', 'Tammara')->first();
        if ($paymentGateway) {
            $information = json_decode($paymentGateway->information, true);
            $paymentGateway->information = $information;
            $data[] = $paymentGateway;
        }

        $paymentGateway = PaymentGetway::where('keyword', 'Tabby')->first();
        if ($paymentGateway) {
            $information = json_decode($paymentGateway->information, true);
            $paymentGateway->information = $information;
            $data[] = $paymentGateway;
        }

        return response()->json([
            "isSuccess" => true,
            'data' => $data
        ], 200);
    }


    public function TammaraUpdate(Request $request)
    {
        $tammara =PaymentGetway::where([
            ['keyword', 'Tammara'],
        ])->first();
      //  dd($myfatoorah);
        $tammara->status = $request->status;
        $information = [];
        $information['api_token'] = $request->api_token;
        $tammara->information = json_encode($information);
        if (request()->has('image') &&  request('image') != '') {
            $avatar = request()->file('image');
            if ($avatar->isValid()) {
                $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/uploads/tammara');
                $avatar->move($avatarPath, $avatarName);
                $image  = $avatarName;
            }
        } else {
            $image = $tammara->image;
        }
        $tammara->image = $image;
        $tammara->save();

        return response()->json([
            "isSuccess" => true,
            'data' => $tammara
        ], 200);
    }

    public function TabbyUpdate(Request $request)
    {
        $tabby = PaymentGetway::where([
            ['keyword', 'Tabby'],
        ])->first();
      //  dd($myfatoorah);
        $tabby->status = $request->status;
        $information = [];
        $information['api_token'] = $request->api_token;
        $tabby->information = json_encode($information);
        if (request()->has('image') &&  request('image') != '') {
            $avatar = request()->file('image');
            if ($avatar->isValid()) {
                $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = public_path('/uploads/tabby');
                $avatar->move($avatarPath, $avatarName);
                $image  = $avatarName;
            }
        } else {
            $image = $tabby->image;
        }
        $tabby->image = $image;
        $tabby->save();

        return response()->json([
            "isSuccess" => true,
            'data' => $tabby
        ], 200);
    }
}

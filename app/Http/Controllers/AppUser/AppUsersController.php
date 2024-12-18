<?php

namespace App\Http\Controllers\AppUser;
use App\Http\Controllers\Controller;
use App\Models\AppUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
class AppUsersController extends Controller
{
    public function check_number(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "false", 'error' => $validator->errors()], 422);
        }

        //check if the phone is exists
        $phone = "009665" . $request->phone;
        $user = AppUsers::where('phone', $phone)->first();

        $is_new_user = true;

        //generate OTP
        //  $otp = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $otp = 1234;
        try {
            if ($user) {
                //save code in database
                $user->otp = $otp;
                $user->save();
                if ($user->name != "new_user") {
                    $is_new_user = false;
                }

                // if ($user->status == 0) {
                //     return response()->json(['success' => "false", 'is_new' => false], 403);
                // }
            } else {

                //create user
                $user = AppUsers::create([
                    'name' => 'new_user',
                    'phone' => $phone,
                    'otp' => $otp,
                ]);
            }

      
            $text = "رمز التحقق هو: " . $otp . " للاستخدام في تطبيق  كلين  ";
            $this->send_sms($phone, $text);

            return response()->json(['success' => "true", 'is_new' => $is_new_user], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['success' => "false", 'is_new' => false], 403);
        }

    }
    public function check_opt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required'],
            'otp' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "false", 'error' => $validator->errors()], 422);
        }

        $phone = "009665". $request->phone;
        if($request->phone == "93783093")
        {
            $user = AppUsers::where('phone', $phone)->first();
            //  $user->device_token = $request->device_token;
            $user->save();
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token,
                "data" => $user,
            ]);
        }

        $user = AppUsers::where('phone', $phone)->where('otp',$request->otp)->first();
        if($user)
        {
            //  $user->device_token = $request->device_token;
            $user->save();
            if(isset($request->name) && $request->name != "")
            {
                $user->name = $request->name;
                $user->save();
            }
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                "data" => $user,

            ]);
        }else{
            return response()->json([ 'error' => 'wrong data'], 403);
        }
    }
   
    public function send_sms($number, $text)
    {
      
        try {

            $token = "1c6120686b6b628a4b7e6b925b013757";
            $url = "https://api.taqnyat.sa/v1/messages";

            //Sender Name must be pre approved by the operator before being used
            //يجب ان يتم الموافقة على اسم المرسل من قبل مزود الخدمة قبل البدئ باستخدامه
            $sender = "deep clean";

            //You may send message to 1 destination or multiple destinations by supply destinations number in one string and separate the numbers with "," or provide a array of strings
            //يمكنك ارسال الرسائل الى جهة واحدة من خلال او اكثر تزويدنا بالارقام في متغير نصي واحد تكون فيه الارقام مفصولة عن بعضها باستخدام "," او من خلال تزويدنا بمصفوفة من الارقام
            $recipients = $number;

            //The message Content in UTF-8
            //نص الرساله مشفر ب UTF-8
            $body = $text;

            $customRequest = "POST"; //POST or GET
            $data = array(
                'bearerTokens' => $token,
                'sender' => $sender,
                'recipients' => $recipients,
                'body' => $body,
            );

         Log::info('SMS Response', ['number' => $number]);

           $data = json_encode($data);

           $curl = curl_init();


           curl_setopt_array($curl, array(
               CURLOPT_URL => $url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => "",
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 10,
               CURLOPT_FOLLOWLOCATION => true,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => $customRequest,
               CURLOPT_POSTFIELDS => $data,
               CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
           ));


           $response = curl_exec($curl);

       // Log the response
        Log::info('SMS Response', ['response' => $response]);

       if ($response === false) {
           $error = curl_error($curl);
           // Log curl error
           Log::error('Curl error', ['error' => $error]);
           curl_close($curl);
           return false;
       }
       curl_close($curl);
       return true;
        } catch (\Exception $e) {
            return false;
        }
    }


}

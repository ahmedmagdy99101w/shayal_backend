<?php

use App\Models\Point;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
if (!function_exists('upload')) {
function upload($avatar, $directory)
{
        $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
        $avatar->move($directory, $avatarName);
        return $avatarName;

}

// function isServiceInUserSubscription( $serviceId)
// {
//     $user = Auth::guard('app_users')->user();

//     if (!$user) {
//         return false;
//     }
//     $subscriptions = $user->subscription;
//     $subscriptionData = $user->subscription()->first(['expire_date', 'visit_count']);

//     if (!$subscriptions) {
//         return false;
//     }

//     if (!$subscriptions ||  $subscriptionData->expire_date < now()) {
//         return false;
//     }
//     foreach($subscriptions as $subscription){
//         if ( $subscriptionData->visit_count >= $subscription->visits) {
//             return response()->json(['error' => 'Visit count limit exceeded'], 422);
//         }
//         $subscriptionServices = $subscription->services;

//         foreach ($subscriptionServices as $service) {
//             // dd($serviceId);
//             if ($service->id == $serviceId) {
//                 return true;
//             }
//         }
//     }
//     return false;
// }
function isServiceInUserSubscription($serviceId)
{
    $user = Auth::guard('app_users')->user();

    if (!$user) {
        return false;
    }

    $subscriptions = $user->subscription()->where('expire_date', '>', now())->get();

    if ($subscriptions->isEmpty()) {
        return false;
    }

    foreach ($subscriptions as $subscription) {
        $pivotData = $subscription->pivot;

        if ($pivotData->visit_count == $subscription->visits) {
            return false;
            return response()->json(['error' => 'Visit count limit exceeded'], 422);
        }

        $subscriptionServices = $subscription->services;

        foreach ($subscriptionServices as $service) {
            if ($service->id == $serviceId) {
                return true;
            }
        }
    }

    return false;
}

}
  /////////
  if (!function_exists('checkCoupon')) {
    function checkCoupon($couponCode, $totalAmount)
    {
        $coupon = App\Models\Coupon::where('discount_code', $couponCode)->first();

        if (!$coupon) {
            return ['status' => false, 'message' => 'not exist'];
        }

        $currentDate = date('Y-m-d');
        if ($currentDate < $coupon->start_date || $currentDate > $coupon->end_date) {
            return ['status' => false, 'message' => 'date expired'];
        }

        if ($coupon->max_usage !== null && $coupon->max_usage <= 0) {
            return ['status' => false, 'message' => 'max usage reached'];
        }

        if ($coupon->max_discount_value !== null && $totalAmount > $coupon->max_discount_value) {
            return ['status' => false, 'message' => 'totalAmount greater than max discount value'];
        }

        // Decrement max_usage in the database
        if ($coupon->type == 'percentage') {
            $discount = (float) $coupon->discount_percentage;
            $priceAfterDiscount = $totalAmount - ($totalAmount * $discount);
        } else {
            $discount = (int) $coupon->discount;
            $priceAfterDiscount = $totalAmount - $discount;
        }

        return [
            'status' => true,
            'discount' => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'id' => $coupon->id,
        ];
    }
}
// دالة للحصول على Google Access Token
if (!function_exists('getGoogleAccessToken')) {
    function getGoogleAccessToken()
    {
        $credentialsFilePath = base_path('firebase-cloud-messaging.json');
        // dd(  $credentialsFilePath);
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        
        try {
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();
         // Inspect the token details
            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());
            return null;
        }
    }
}

// دالة لإنشاء مجموعة
if (!function_exists('makeGroup')) {
    function makeGroup(array $registrationIds, string $notificationKeyName, $accessToken, string $operation = 'create')
    {
        $url = 'https://fcm.googleapis.com/fcm/notification';
        $projectId = "6ee1cf483da00985387bac18ce0314ff85da8a62";

        if (empty($registrationIds)) return;

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'project_id: ' . $projectId,
        ];

        $payload = [
            'operation' => $operation,
            'notification_key_name' => $notificationKeyName,
            'registration_ids' => $registrationIds,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode == 200) {
            $response = json_decode($response);
            return $response->notification_key ?? null;
        }

        return null;
    }
}


if (!function_exists('sendFirebase')) {
    function sendFirebase($tokens, $title = null, $body = null, $Url = null, $imageUrl = null)
    {
        
        if (empty($tokens)) {
            return false;
        }

        $apiAccessToken = getGoogleAccessToken();
        $isGroup = false;
        $key = time();

        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        $tokens = array_values(array_filter(array_unique($tokens)));

        // Notification object for FCM
        $notification = [
            'title' => $title ?: config('app.name') . ' Notification',
            'body' => $body,
        ];

        // Data object to include additional information like URL
        $data = [
            'url' => $Url,
        ];

       

        if (count($tokens) === 1) {
            $token = $tokens[0];
        } else {
            if ($tokens instanceof \Illuminate\Support\Collection) {
                $tokens = $tokens->toArray();
            }
            $token = makeGroup($tokens, $key, $apiAccessToken);
            $isGroup = true;
        }

        // Payload structure for FCM
        $payload = [
            'token' => $token,
            'notification' => $notification,
            'data' => $data, // Include the additional data here
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $apiAccessToken,
            'Content-Type' => 'application/json',
        ];

        try {
            // Use GuzzleHttp Client for the request
            $client = new \GuzzleHttp\Client(['verify' => false]);

            $response = $client->post('https://fcm.googleapis.com/v1/projects/deep-clean-e4268/messages:send', [
                'headers' => $headers,
                'json' => ['message' => $payload],
            ]);

            $result = json_decode($response->getBody()->getContents());

            if (isset($result->error)) {
                throw new \Exception("Notification Error: " . json_encode($result) . " Tokens: " . json_encode($tokens));
            }

            if ($isGroup) {
                makeGroup($tokens, $key, $apiAccessToken, 'remove');
            }

            return $result;
        } catch (\Exception $ex) {
            \Log::error('Firebase Notification Error: ' . $ex->getMessage());
            return false;
        }
    }
}

if (!function_exists('calculateRiyalsFromPoints')) {
    function calculateRiyalsFromPoints($userId)
{
    $points = Point::where('user_id', $userId)->sum('point');
    $pointsPerRiyal = 5000;
    $amountPerRiyal = 100;

    if ($points > 0) {
        $riyals = ($points / $pointsPerRiyal) * $amountPerRiyal;
        return $riyals;
    }

    return 0;
}
    }

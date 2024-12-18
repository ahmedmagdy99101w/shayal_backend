<?php

namespace App\Http\Controllers\AppUser;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AppUsers;
use App\Models\Membership;
use App\Events\BookedEvent;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\TabbyPayment;
use App\Services\paylinkPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppUserBooking;
use App\Notifications\MembershipNotification;

class SubscriptionController extends Controller
{
    public $paylink;
    public $tabby;
    public function __construct()
    {
        $this->paylink = new paylinkPayment();
        $this->tabby = new TabbyPayment();
    }
    public function index()
    {

        $subscriptions = Subscription::all();
        return response()->json(['data' => SubscriptionResource::collection($subscriptions)], 200);

    }
    public function subscriptionsHasOffer()
    {
        $subscriptions = Subscription::whereNotNull('offer') 
            ->where('offer', '!=', '') 
            ->get();
            return response()->json(['data' => SubscriptionResource::collection($subscriptions)], 200);

    }
    
    public function show($id)
    {
        $subscription = Subscription::with('services')->find($id);

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        return response()->json(['data' => $subscription], 200);
    }
    public function booking(Request $request)
    {
        $validatedData = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'payment' => 'required'
        ]);
    
        // Get the authenticated user
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Get the subscription
        $subscription = Subscription::find($request->subscription_id);
        $duration = $subscription->duration; // Duration in days
        $visitsAllowed = $subscription->visits; // Number of visits allowed
    
        // Check if the user already has an active subscription for the same plan
        $existing = Membership::where('subscription_id', $subscription->id)
        ->where('user_id', $user->id)
        ->where('paid', 1)
        ->first();
    
        if ($existing) {
            // Check if the subscription has expired
            // if (Carbon::now()->greaterThan($existing->expire_date)) {
            //     return response()->json(['message' => 'Your subscription has expired.'], 422);
            // }
    
            // // Check if the user has exhausted all visits
            // if ($existing->visit_count >= $visitsAllowed) {
            //     return response()->json(['message' => 'You have exhausted all your visits.'], 422);
            // }
    
            return response()->json(['message' => 'You are already subscribed and your subscription is active.'], 422);
        }
    
        // Create new membership with calculated expiration date
        $membership = new Membership();
        $membership->user_id = $user->id;
        $membership->subscription_id = $request->subscription_id;
        $membership->expire_date = Carbon::now()->addDays($duration); // Expiration date calculation
        $membership->save();
    
        // Notify admins and user
        $adminUsers = User::where('roles_name', 'admin')->get();
        foreach ($adminUsers as $adminUser) {
            $adminUser->notify(new MembershipNotification($membership));
        }
        $user->notify(new AppUserBooking($subscription));
        BookedEvent::dispatch($subscription);

        ////////payment
        if ($request->payment == 'Tabby') {
            $items = collect([]);
            $items->push([
                'title' => 'title',
                'quantity' => 2,
                'unit_price' => 20,
                'category' => 'Clothes',
            ]);;

            $order_data = [
                'amount' =>  $subscription->price,
                'currency' => 'SAR',
                'description' => 'description',
                'full_name' => $user->name ?? 'user_name',
                'buyer_phone' => $user->phone ?? '9665252123',
                  'buyer_email' => 'card.success@tabby.ai',//this test
                //   'buyer_email' =>  $user->email ?? 'user@gmail.com',
                'address' => 'Saudi Riyadh',
                'city' => 'Riyadh',
                'zip' => '1234',
                'order_id' => "$membership->id",
                'registered_since' => $membership->created_at,
                'loyalty_level' => 0,
                'success-url' => route('success-ur-subscription'),
                'cancel-url' => route('cancel-ur-subscription'),
                'failure-url' => route('failure-ur-subscription'),
                'items' =>  $items,
            ];

            $payment = $this->tabby->createSession($order_data);

            $id = $payment->id;

            $redirect_url = $payment->configuration->available_products->installments[0]->web_url;

            return  $redirect_url;
        }
        elseif ($request->payment == 'Paylink') {

            $data = [
                'amount' =>  $subscription->price,
                'callBackUrl' => route('paylink-result-subscription'),
                'clientEmail' => $user->email ?? 'test@gmail.com',
                'clientMobile' => $user->phone ?? '9665252123',
                'clientName' => $user->name ?? 'user_name',
                'note' => 'This invoice is for VIP client.',
                'orderNumber' => $membership->id,
                'products' => [
                    [
                        'description' =>  $subscription->description ?? 'description',
                        'imageSrc' =>  null,
                        'price' =>  $subscription->price ?? 1,
                        'qty' => 1,
                        'title' =>  $subscription->name ?? 'title',
                    ],
                ],
            ];


            return $this->paylink->paymentProcess($data);
        }
        else{
            return response()->json(['message' => 'you need payment method'], 422);
        }

        // return response()->json(['message' => 'you subscripe successfully.'], 200);
    }
    public function requestVisit(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        // Retrieve the subscription
        $subscription = Subscription::findOrFail($validatedData['subscription_id']);

        // Check if the subscription is expired
        if ($subscription->isExpired()) {
            return response()->json(['message' => 'Subscription has expired.'], 400);
        }

        // Check if the visit limit is reached
        if ($subscription->isVisitLimitReached()) {
            return response()->json(['message' => 'Visit limit has been reached for this subscription.'], 400);
        }

        // Increment the visit count for the subscription
        $subscription->visits++;
        $subscription->save();

        // Return a success message
        return response()->json(['message' => 'Visit requested successfully.'], 200);
    }
    public function userSuscriptions()
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $suscriptions = $user->subscription;

        return response()->json(['data' => $suscriptions], 200);
    }

    public function sucess(Request $request)
    {
        return   $this->tabby->calbackPaymentSubscription($request);
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

        return   $this->paylink->calbackPaymentSubscription($request);
    }
}

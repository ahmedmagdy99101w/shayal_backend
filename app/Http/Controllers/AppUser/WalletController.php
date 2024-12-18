<?php

namespace App\Http\Controllers\AppUser;

use App\Http\Controllers\Controller;
use App\Models\AppUsers;
use App\Models\Offer;
use App\Models\OfferSubmitted;
use App\Models\Provider;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
class WalletController extends Controller
{
    public function userBalance(Request $request)
    {
        $user = Auth::guard('app_users')->user();
        return response()->json([
            'user_balance' => $user->wallet_balance,
        ], 200);
    }

    public function addBalance(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required'
        ]);
        // Get the authenticated user
        $user = Auth::guard('app_users')->user();

        if ($request->payment_method == 'mamopay') {
            try {
                $client = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('POST', 'https://sandbox.dev.business.mamopay.com/manage_api/v1/links', [
                    'body' => json_encode([
                        'name' => 'Order Payment',
                        'description' => 'Payment for your order',
                        'capacity' => 1,
                        'active' => true,
                        'return_url' => route('wallet.callback'),
                        'failure_return_url' => route('wallet.failuer'),
                        'processing_fee_percentage' => 3,
                        'amount' => $request->amount,
                        'amount_currency' => 'AED',
                        'link_type' => 'standalone',
                        'enable_tabby' => false,
                        'enable_message' => false,
                        'enable_tips' => false,
                        'save_card' => 'off',
                        'enable_customer_details' => false,
                        'enable_quantity' => false,
                        'enable_qr_code' => false,
                        'send_customer_receipt' => false,
                        'hold_and_charge_later' => false,
                        'title' => 'Order Payment'
                    ]),
                    'headers' => [
                        'Authorization' => 'Bearer sk-7611402f-ca7d-40cb-891f-02d652f7a348',
                        'Content-Type' => 'application/json',
                        'accept' => 'application/json',
                    ],
                ]);
                $data = json_decode($response->getBody(), true);
                // Update the order with the transaction_id
                $user->update(['paymentLink_id' => $data['id']]); 
                
                return response()->json([
                    'status'=>"true",
                    'message' => '',
                    'payment_url' => $data['payment_url']
                ], 200);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                return response()->json([
                    'error' => 'Client error occurred',
                    'status_code' => $e->getResponse()->getStatusCode(),
                    'message' => $e->getResponse()->getBody()->getContents()
                ], $e->getResponse()->getStatusCode());
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'An unexpected error occurred',
                    'status_code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }
    }
    public function paymentCallback(Request $request)
    {

        try {
            $paymentStatus = $request->input('status');
            $checkoutId = $request->input('chargeUID');
            $paymentLinkId = $request->input('paymentLinkId');
            $mamopayResponse = Http::withoutVerifying()->get("https://sandbox.dev.business.mamopay.com/manage_api/v1/links/{$paymentLinkId}");

            if ($paymentStatus == 'captured') {
                DB::beginTransaction();
                $mamopayData = $mamopayResponse->json();

                $user = AppUsers::where('paymentLink_id',  $paymentLinkId)->first();

                if ($user && $paymentStatus == 'captured') {
                    $user->wallet_balance += $request->input('amount');
                    $user->save();
                }
                // Handle the fetched data (e.g., store it in your payment table)
                WalletTransaction::create([
                    'app_user_id' => $user->id,
                    'credit' => $request->input('amount'),
                    'debit' => null,
                    'balance' => $user->wallet_balance,
                    'transaction_type' => 'add',
                    'process'=>'add_balance' 
                ]);
                DB::commit();
            } else {
                // Handle the error if the request to Mamo Pay fails
                return response()->json(['status' => 'Failed to fetch Mamo Pay data'], 500);
            }

            return response()->json(['status' => 'Callback processed successfully']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Return the error message from the exception
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function paymentCallbackFailuer(Request $request)
    {
        return response()->json(['status' => 'Failed to fetch Mamo Pay data'], 500);
    }

    public function transferBalance(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'offer_submitted_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all(); 
        
            return response()->json([
                'success' => false,
                'message' => $errors[0] 
            ], 401);
        }
        $validated = $validator->validated();

        // Get the authenticated user (sender)
        $sender = Auth::guard('app_users')->user();

        // Check if the sender has enough balance to transfer
        if ($sender->wallet_balance < $validated['amount']) {
            return response()->json([
                'message' => 'Insufficient balance',
            ], 400);
        }
       $offer =  OfferSubmitted::where('id', $validated['offer_submitted_id']);
        // Find the recipient by account number and phone
        $recipient = Provider::where('id', $offer->provider_id)->first();

        // If the recipient is not found
        if (!$recipient) {
            return response()->json([
                'message' => 'Recipient not found',
            ], 404);
        }

        // Begin transaction to ensure both updates succeed or fail together
        DB::transaction(function () use ($sender, $recipient, $offer) {
            $amount = $offer->price;

            // Deduct from sender's wallet balance
            $sender->wallet_balance -= $amount;
            $sender->save();
            
            // Create a transaction record for sender (debit)
            WalletTransaction::create([
                'app_user_id' => $sender->id,
                'credit' => null,  // No credit for sender, it's a debit
                'debit' => $amount,
                'balance' => $sender->wallet_balance,  // Updated balance
                'transaction_type' => 'deduction',    // Deduction for the sender
                'process'=>'payment' ,
                 'offer_submitted_id'=> $offer->id
            ]);

            // Add to recipient's wallet balance
            $recipient->wallet_balance += $amount;
            $recipient->save();

            // Create a transaction record for recipient (credit)
            WalletTransaction::create([
                'provider_id' => $recipient->id,
                'credit' => $amount,  // Credit for recipient
                'debit' => null,  // No debit for recipient
                'balance' => $recipient->wallet_balance,  // Updated balance
                'transaction_type' => 'add', // Addition for the recipient
                'process'=>'payment' ,
                'offer_submitted_id'=> $offer->id
            ]);
        });
        // Return success response
        return response()->json([
            'message' => 'Balance transferred successfully',
            'sender_balance' => $sender->wallet_balance,
        ], 200);
    }
}

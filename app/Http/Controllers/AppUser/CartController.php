<?php

namespace App\Http\Controllers\AppUser;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderOption;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function addItemToCart(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0', 
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
    
        // Get authenticated user
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $user_id = $user->id;
    
        // Check if the cart already exists for the user and service
        $cart = Cart::where('user_id', $user_id)
            ->where('service_id', $request->service_id)
            ->first();
    
        // If cart exists, update it with new options and price
      if ($cart) {
            $cart->price = $request->price; // Update the price
            $cart->save();
    
            if (!empty($request->options)) {
                foreach ($request->options as $option) {
                    $orderOption = OrderOption::where('cart_id', $cart->id)
                        ->where('option_type_id', $option['id'])
                        ->first();
                    if ($orderOption) {
                        // Update existing option
                        $orderOption->update([
                            'value' => $option['value'] ?? null,
                        ]);
                    } else {
                        // Create new option
                        OrderOption::create([
                            'cart_id' => $cart->id,
                            'option_type_id' => $option['id'],
                            'value' => $option['value'] ?? null,
                        ]);
                    }
                }
            }
        } 
         else {
            // Create a new cart entry if it doesn't exist, including price
            $cart = Cart::create([
                'user_id' => $user_id,
                'service_id' => $request->service_id,
                'price' => $request->price, // Store the price
            ]);
    
            // Add options to the newly created cart
            if (!empty($request->options)) {
                foreach ($request->options as $option) {
                    OrderOption::create([
                        'cart_id' => $cart->id,
                        'option_type_id' => $option['id'],
                        'value' => $option['value'] ?? null,
                    ]);
                }
            }
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Item added to cart successfully',
        ], 200);
    }
    
    
    public function removeItemFromCart(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'service_id' => 'required|exists:services,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $user_id = Auth::guard('app_users')->user()->id;

        $cart = Cart::where('user_id', $user_id)->where('service_id', $request->service_id)->first();


            $cart->delete();
            return response()->json([
                'status' => true,
                'message' => 'Item removed from cart successfully',
            ], 200);

    }

    public function getCartItems(Request $request)
    {

        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $user_id = Auth::guard('app_users')->user()->id;
        $subtotal = 0.0;
        $deliveryFees = 0.0;

        $carts = Cart::where('user_id', $user_id)->get();

        $items = $carts->map(function ($cart) {
            $service = Service::where('id', $cart->service_id)->first();
    
            return [
                'id' => $service->id,
                'name' => $service->name ,
                'photo' => $service->photo,
                'total' => $cart->price,
            ];
        });
        $totalCost =  $items->sum('total') ?? 0.0;

        return response()->json([
            'status' => true,
            'totalCost' => $totalCost ?? 0.0,
            'items' => $items,
        ], 200);
    }

    public function getUserCart()
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $user_id = Auth::guard('app_users')->user()->id;
        $count_cart = Cart::where('user_id', $user_id)
            ->count();
        return response()->json([
            'status' => true,
            'count' => $count_cart,
        ], 200);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\AppUser;

use App\Models\Area;
use App\Models\City;
use App\Models\Term;
use App\Models\AboutUs;
use App\Models\Contact;
use App\Models\Privacy;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\ServiceResource;
use App\Models\CustomeService;
use App\Models\ItemMoved;
use App\Models\Offer;
use App\Models\ManualNotification;
use Illuminate\Support\Facades\Auth;
class GeneralController extends Controller
{
    public function getAllServices()
    {
        $services = Service::all();
        return response()->json(['data' => ServiceResource::collection($services)], 200);
    }
    public function item_moved()
    {
        $items = ItemMoved::all();
        return response()->json(['data' =>  $items], 200);
    }
    public function getOffers()
    {
        $offers = Offer::first();
        return response()->json(['data' => $offers], 200);
    }
    public function getAllTerm()
    {
        $term = Term::all();
        return response()->json(['data' => $term], 200);
    }
    public function getAllprivacy()
    {
        $privacy = Privacy::all();
        return response()->json(['data' => $privacy], 200);
    }
    public function getAllsetting()
    {
          $settings = Setting::pluck('value', 'key')
        ->toArray();
        if (isset($settings['site_logo_single'])) {
            $settings['site_logo_single'] = asset('uploads/settings/' . $settings['site_logo_single']);
        }
        if (isset($settings['site_logo_full'])) {
            $settings['site_logo_full'] = asset('uploads/settings/' . $settings['site_logo_full']);
        }
        if (isset($settings['site_logo_dark'])) {
            $settings['site_logo_dark'] = asset('uploads/settings/' . $settings['site_logo_dark']);
        }

    return  $settings;
       
    }

    public function customer_service()
    {
        $customers = CustomeService::all();
        return  $customers;
    }

    public function getContactUs()
    {
        $contactUs = Contact::all();
        return response()->json(['data' => $contactUs], 200);
    }

    public function getAboutUs()
    {
        $aboutUs = AboutUs::all();
        return response()->json(['data' => $aboutUs], 200);
    }
    public function getQuestion()
    {
        $question = Question::all();
        return response()->json(['data' => $question], 200);
    }
    public function cities()
    {
        $terms = City::get();
        return CityResource::collection($terms);
    }

    public function cityArea($id){
        $areas =  Area::where('city_id',$id)->get();
        return AreaResource::collection($areas);
    }
      public function notification(Request $request)
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $notifications = ManualNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

      

        // Return notifications as a JSON response
        return response()->json([
            'status' => 'success',
            'notifications' => $notifications,
        ], 200);
    }
    public function SwipNotification(Request $request)
    {
        $user = Auth::guard('app_users')->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $notification = ManualNotification::where('user_id', $user->id)->where('id', $request->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $notification->delete();

        // Return notifications as a JSON response
        return response()->json([
            'status' => 'success',
        ], 200);
    }

}

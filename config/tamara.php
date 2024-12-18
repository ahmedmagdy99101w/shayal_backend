<?php

use App\Models\PaymentGetway;
use Illuminate\Support\Facades\Config;



return [

    /*
    |--------------------------------------------------------------------------
    | Merchant token
    |--------------------------------------------------------------------------
    |
    | This value is the Merchant token
    |
    */

    'token' => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhY2NvdW50SWQiOiJiNzk3MTM5Yi1jMmJlLTQ5Y2MtYmI5OC01MTgxMDM3OTgwNzciLCJ0eXBlIjoibWVyY2hhbnQiLCJzYWx0IjoiN2EyZDNiMjY2NGMxM2M5MjcxMGJjODQzMTEwY2VmY2EiLCJyb2xlcyI6WyJST0xFX01FUkNIQU5UIl0sImlhdCI6MTcwNjAyNTUxNCwiaXNzIjoiVGFtYXJhIFBQIn0.BizJLcn-hz-aaS360efq_eM_eIbaxDEP937GBssrYqODEcMzQqceAdtOu9nMYEw_nK9mRD863TJiqfUeNjqig7XvdNPx_aj1skb8jmL5kHBaz5hG1auF8Z589miJJVUyW7PTObVXlDiKldkzrbDCkMazgsTGVXm2vOMCisLYo83-zpQEhU1Q2U625rTdenftytfB7fj6QL04B6Ms0gVQw_dssFHsBbBnoN_Z-h5oc91H6UCXnN1zWnxIqT5MCu9PyuYXkrifu6WzxDv1eccsHDBm0RYf7wderZV7jYTbo-uIKZxtngGGhEmJ67gYcuvIsVJtqVgpvVZQa-j2B3l2bQ",

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | Mode only values: "test" or "live"
    |
    */

    "mode"     => "test",

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | This value URL .
    |
    */

    'test_url' => "https://api-sandbox.tamara.co",
    'live_url' => "https://api.tamara.co",



    /*
    |--------------------------------------------------------------------------
    | Country
    |--------------------------------------------------------------------------
    |
    | This value Country .
    |
    */

    'country_code' => "SA",
    'currency'     => "SAR",
];

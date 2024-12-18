<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::all();
        return response()->json(['data'=> $contacts], 200);

    }
    public function contact_us()
    {
        $contact_us = ContactUs::all();
        return response()->json(['data'=> $contact_us], 200);

    }
    public function update(Request $request)
    {
        $contact = Contact::first();
        if ($contact) {
            $contact->update($request->all());
        } else {
            $contact = Contact::create($request->all());
        }
        return response()->json(['data'=> $contact], 200);

    }
}

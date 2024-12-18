<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function index()
    {
        $terms = Term::first();
        return response()->json(['data'=> $terms], 200);

    }
    public function update(Request $request)
    {
        $terms = Term::first();
        if ($terms) {
            $terms->update($request->all());
        } else {
            $terms = Term::create($request->all());
        }
        return response()->json(['data'=> $terms], 200);

    }
}

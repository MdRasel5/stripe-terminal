<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function showCheckoutPage()
    {
        return view('checkout');
    }
}

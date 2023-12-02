<?php

namespace App\Http\Controllers;

use App\CustomReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Terminal\Reader;
use Stripe\PaymentIntent;
use Stripe\Service\TestHelpers\Terminal\ReaderService;

class StripeController extends Controller
{
    public function listReaders(Request $request)
    {
        try {
            // Manually set the connected account ID
            $connectedAccountId = $request->input('connected_account_id'); // Replace with your connected account ID

            // Set the Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));

            // Fetch the list of readers for the connected account
            $readers = Reader::all([], ['stripe_account' => $connectedAccountId]);

            // Dump the response to the browser
            // dd($readers);

            // You can continue with your logic here and return a response
            return response()->json(['readersList' => $readers]);
        } catch (\Exception $e) {
            // Log the error if an exception occurs
            Log::error('Error in yourApiEndpoint: ' . $e->getMessage());

            // Return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function processPayment(Request $request)
    {
        try {
            // Set the Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));

            // Get the amount and reader ID from the request
            $amount = $request->input('amount');
            $readerId = $request->input('readerId');

            // Create a payment intent
            $paymentIntent = PaymentIntent::create([
                'currency' => 'usd',
                'amount' => $amount,
                'payment_method_types' => ['card_present'],
                'capture_method' => 'manual',
            ]);

            // Retrieve the Reader by ID
            $reader = Reader::retrieve($readerId);

            // Process payment on the specified reader
            $reader->processPaymentIntent([
                'payment_intent' => $paymentIntent->id,
            ]);

            // Return the response with the reader and payment intent details
            return response()->json(['reader' => $reader, 'paymentIntent' => $paymentIntent]);
        } catch (\Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => ['message' => $e->getMessage()]]);
        }
    }

    public function simulatePayment(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $readerId = $request->input('readerId');

            // Assuming this is the correct way to create a Stripe client instance
            $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
            $readerService = new ReaderService($stripeClient);

            // Simulate a payment on the specified reader
            $reader = $readerService->presentPaymentMethod($readerId);

            return response()->json(['reader' => $reader]);
        } catch (\Exception $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]]);
        }
    }

    public function capturePayment(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntentId = $request->input('paymentIntentId');

            // Capture the payment
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->capture();

            return response()->json(['paymentIntent' => $paymentIntent]);
        } catch (\Exception $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]]);
        }
    }

    public function cancelPayment(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $readerId = $request->input('readerId');

            // Retrieve the Reader by ID
            $reader = Reader::retrieve($readerId);

            // Cancel the action on the specified reader
            $canceledReader = $reader->cancelAction();

            return response()->json(['reader' => $canceledReader]);
        } catch (\Exception $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]]);
        }
    }
}

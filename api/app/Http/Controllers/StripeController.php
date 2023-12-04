<?php

namespace App\Http\Controllers;

use App\CustomReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Terminal\Reader;
use Stripe\PaymentIntent;
use Stripe\Service\TestHelpers\Terminal\ReaderService;
use Stripe\Terminal\ConnectionToken;
use Stripe\Terminal\Location;

class StripeController extends Controller
{
    public function listReaders(Request $request)
    {
        try {
            $connectedAccountId = $request->input('connected_account_id');

            Stripe::setApiKey(config('services.stripe.secret'));

            $readers = Reader::all([], ['stripe_account' => $connectedAccountId]);

            return response()->json(['readersList' => $readers]);
        } catch (\Exception $e) {
            Log::error('Error in listReaders: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processPayment(Request $request)
    {
        try {
            // Stripe::setApiKey(config('services.stripe.secret'));
            Stripe::setApiKey('sk_test_51OCTILLa933p6qD6Ay0jriVzyOdEdV2gaYdT34DGgkNQyz8ow12CxffKP8vXF4ksm6bItVEk25dgWPM7xkqeBBne00QDKg2NHY');

            $amount = $request->input('amount');
            $readerId = $request->input('readerId');
            $commission = $request->input('commission');
            $connectedAccountId = 'acct_1OCTtILx02PcYbJn';

            $reader = Reader::retrieve($readerId, ['stripe_account' => $connectedAccountId]);

            // Create PaymentIntent for direct charges
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'payment_method_types' => ['card_present'],
                'capture_method' => 'manual',
                'application_fee_amount' => $commission,
            ], [
                'stripe_account' => $connectedAccountId,
            ]);

            // Process payment on the specified reader
            $reader->processPaymentIntent(['payment_intent' => $paymentIntent->id]);

            return response()->json([
                'reader' => $reader,
                'paymentIntent' => $paymentIntent,
                'application_fee_amount' => $commission,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in processPayment: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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

<?php

namespace App\Http\Controllers;

use App\CustomReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Terminal\Reader;
use Stripe\PaymentIntent;
use Stripe\Service\TestHelpers\Terminal\ReaderService;
use Stripe\StripeClient;
use Stripe\Terminal\ConnectionToken;
use Stripe\Terminal\Location;
use Stripe\Transfer;

class StripeController extends Controller
{
    public function listReaders(Request $request)
    {
        try {
            $connectedAccountId = $request->input('connected_account_id');

            Stripe::setApiKey(config('services.stripe.connected_account_secret'));

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
            Stripe::setApiKey(config('services.stripe.secret'));

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
            // Stripe::setApiKey(config('services.stripe.secret'));

            $readerId = $request->input('readerId');
            $connectedAccountId = 'acct_1OCTtILx02PcYbJn';

            // Assuming this is the correct way to create a Stripe client instance
            // $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));

            $connectedAccountSecretKey = 'sk_test_51OCTtILx02PcYbJn50y1Ws1iQswvo6DV7cRo20p99EgKZZ9cweFSWRetJsf5pueJkj1k5pHpuLFZf5RUnPnTlIT6009mgszqmD';

            $stripeClient = new \Stripe\StripeClient($connectedAccountSecretKey);

            // Log or output the API key being used
            \Log::info('Stripe API Key: ' . $stripeClient->getApiKey());

            $readerService = new ReaderService($stripeClient);

            // Simulate a payment on the specified reader
            return $reader = $readerService->presentPaymentMethod($readerId, [], ['stripe_account' => $connectedAccountId]);

            return response()->json(['reader' => $reader]);
        } catch (\Exception $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]]);
        }
    }


    public function transferApplicationFee(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntentId = $request->input('paymentIntentId');
            $commission = $request->input('commission');
            $platformAccountId = 'acct_1OCTILLa933p6qD6'; // Replace with your actual platform account ID

            // Retrieve the PaymentIntent to get the connected account ID
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            // Create a Transfer to move the application fee to the platform account
            $transfer = Transfer::create([
                'amount' => $commission,
                'currency' => 'usd',
                'destination' => $platformAccountId,
                'transfer_group' => $paymentIntentId, // Link the transfer to the payment
            ]);

            return response()->json(['transfer' => $transfer]);
        } catch (\Exception $e) {
            Log::error('Error in transferApplicationFee: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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

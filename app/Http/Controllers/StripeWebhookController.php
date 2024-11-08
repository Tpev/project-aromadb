<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\User; // Assuming you have a User model

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Set your Stripe secret key
        Stripe::setApiKey(config('services.stripe.secret'));


        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook');

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;

                // Extract Customer ID
                $customerId = $subscription->customer;

                // Retrieve Customer to get Email
                $customer = \Stripe\Customer::retrieve($customerId);
                $email = $customer->email;
				Log::info("Customer Email: " . $email);
                // Extract Product ID from Subscription Items
                if (isset($subscription->items->data[0])) {
                    $subscriptionItem = $subscription->items->data[0];
                    $productId = $subscriptionItem->price->product;
					Log::info("Customer Email: " . $subscriptionItem);
                    // Retrieve Product Details
                    $product = \Stripe\Product::retrieve($productId);
                    $productName = $product->name; // Or any other product attribute you need
                } else {
                    // Handle cases where subscription items are not present
                    $productName = null;
					 Log::info("Handle cases where subscription items are not present");
                }

                // Example: Log the information
                Log::info("Stripe Webhook: Subscription Event");
                Log::info("Customer Email: " . $email);
                Log::info("Product Selected: " . $productName);

                // Optional: Link to your User model if you have a relation
                $user = User::where('stripe_customer_id', $customerId)->first();
                if ($user) {
                    // Update user license or perform other actions
                    $user->license_product = $productName;
                    $user->save();
                }

                break;

            // Handle other event types as needed

            default:
                Log::warning('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }
}

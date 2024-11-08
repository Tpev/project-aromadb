<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\User;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Set your Stripe secret key
        Stripe::setApiKey('sk_test_51Q9V2qE7cOnJl2vMpei30mAl1T6AKfJpehygPXiDBDdBKyTHQnH4KJhTfyAbGWT85o6hJbtxaAfdTIIFSB27shOO00K8QwYMFv');


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
            Log::error('Stripe Webhook Signature Verification Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe Webhook Invalid Payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
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
                try {
                    $customer = \Stripe\Customer::retrieve($customerId);
                    $email = $customer->email ?? 'No email found';
                } catch (\Exception $e) {
                    Log::error('Stripe Customer Retrieval Failed: ' . $e->getMessage());
                    $email = 'Unknown';
                }

                // Extract Product IDs from Subscription Items
                $productNames = [];
                if (isset($subscription->items->data) && count($subscription->items->data) > 0) {
                    foreach ($subscription->items->data as $subscriptionItem) {
                        $productId = $subscriptionItem->price->product;

                        // Retrieve Product Details
                        try {
                            $product = \Stripe\Product::retrieve($productId);
                            $productNames[] = $product->name ?? 'Unnamed Product';
                        } catch (\Exception $e) {
                            Log::error('Stripe Product Retrieval Failed: ' . $e->getMessage());
                            $productNames[] = 'Unknown Product';
                        }
                    }
                } else {
                    $productNames[] = 'No products found';
                }

                // Convert product names array to a string if needed
                $productName = implode(', ', $productNames);

                // Log the information
                Log::info("Stripe Webhook: {$event->type}");
                Log::info("Customer Email: {$email}");
                Log::info("Product Selected: {$productName}");

                // Link to your User model if you have a relation
                $user = User::where('stripe_customer_id', $customerId)->first();
                if ($user) {
                    // Update user license or perform other actions
                    $user->license_product = $productName;
                    $user->save();
                    Log::info("Updated User ID {$user->id} with license product: {$productName}");
                } else {
                    Log::warning("No user found with Stripe Customer ID: {$customerId}");
                }

                break;

            // Handle other event types as needed

            default:
                Log::warning('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }
}

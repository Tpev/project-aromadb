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
        // Initialize Stripe with the secret key from config
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

                // Attempt to find the user by stripe_customer_id
                $user = User::where('stripe_customer_id', $customerId)->first();

                if (!$user) {
                    // If user not found by stripe_customer_id, attempt to find by email
                    $user = User::where('email', $email)->first();

                    if ($user) {
                        // Associate stripe_customer_id with the user
                        $user->stripe_customer_id = $customerId;
                        $user->save();

                        Log::info("Associated Stripe Customer ID {$customerId} with User ID {$user->id}");
                    } else {
                        // No user found with this email; log and possibly take other actions
                        Log::warning("No user found with email: {$email} to associate with Stripe Customer ID: {$customerId}");
                        // Optionally, you can create a new user or notify admins
                        return response()->json(['error' => 'User not found'], 404);
                    }
                }

                // Now that stripe_customer_id is associated, assign the license product
                if ($user) {
                    // Update user license or perform other actions
                    $user->license_product = $productName;
                    $user->save();
                    Log::info("Updated User ID {$user->id} with license product: {$productName}");
                }

                break;

            // Handle other event types as needed

            default:
                Log::warning('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }
}

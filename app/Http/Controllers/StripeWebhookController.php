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
            Log::error('Stripe Webhook Signature Verification Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe Webhook Invalid Payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Handle the event
        switch ($event->type) {
			  case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object, $connectedAccountId);
                break;
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionCreatedOrUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            // Handle other event types as needed

            default:
                Log::warning('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle subscription creation or update events.
     */
    protected function handleSubscriptionCreatedOrUpdated($subscription)
    {
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
        Log::info("Stripe Webhook: {$subscription->id}");
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
                return;
            }
        }

        // Now that stripe_customer_id is associated, assign the license product
        if ($user) {
            // Update user license or perform other actions
            $user->license_product = $productName;
            $user->license_status = 'active'; // Assuming 'active' is the status for valid licenses
            $user->save();
            Log::info("Updated User ID {$user->id} with license product: {$productName} and status: active");
        }
    }

    /**
     * Handle subscription deletion events.
     */
    protected function handleSubscriptionDeleted($subscription)
    {
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

        // Log the deletion event
        Log::info("Stripe Webhook: {$subscription->id} - Subscription Deleted");
        Log::info("Customer Email: {$email}");

        // Attempt to find the user by stripe_customer_id
        $user = User::where('stripe_customer_id', $customerId)->first();

        if (!$user) {
            // If user not found by stripe_customer_id, attempt to find by email
            $user = User::where('email', $email)->first();

            if ($user) {
                // Associate stripe_customer_id with the user if not already
                if (!$user->stripe_customer_id) {
                    $user->stripe_customer_id = $customerId;
                    $user->save();

                    Log::info("Associated Stripe Customer ID {$customerId} with User ID {$user->id}");
                }
            } else {
                // No user found with this email; log and possibly take other actions
                Log::warning("No user found with email: {$email} to associate with Stripe Customer ID: {$customerId}");
                // Optionally, you can create a new user or notify admins
                return;
            }
        }

        // Now that stripe_customer_id is associated, update the license status
        if ($user) {
            // Update the license status to 'inactive' or a similar value to indicate no valid license
            $user->license_status = 'inactive'; // Assuming 'inactive' indicates no valid license
            // Optionally, you can also clear the license_product
            $user->license_product = null; // Or set to 'No License'
            $user->save();

            Log::info("Updated User ID {$user->id} to have no valid license (status: inactive)");
        }
    }
	
	    protected function handleCheckoutSessionCompleted($session, $connectedAccountId)
    {
        // Ensure that the session has the necessary metadata
        if (!isset($session->metadata->invoice_id)) {
            Log::warning("Checkout Session {$session->id} does not contain an invoice_id in metadata.");
            return;
        }

        $invoiceId = $session->metadata->invoice_id;

        // Retrieve the invoice from your database
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            Log::warning("No invoice found with ID {$invoiceId} associated with Checkout Session {$session->id}.");
            return;
        }

        // Update the invoice status to 'Payée'
        $invoice->status = 'Payée';
        $invoice->save();
 try {
		$therapist->notify(new InvoicePaid($invoice));

 }
        Log::info("Invoice ID {$invoice->id} marked as 'Payée' due to Checkout Session {$session->id}.");

        // Deactivate the Payment Link by deleting it
        // Retrieve the Payment Link ID from the session
        $paymentLinkId = $session->payment_link ?? null;

        if ($paymentLinkId) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));

                $stripe->paymentLinks->delete($paymentLinkId, [], [
                    'stripe_account' => $connectedAccountId, // Use the connected account context
                ]);

                Log::info("Payment Link {$paymentLinkId} has been deleted to prevent reuse.");
            } catch (\Stripe\Exception\ApiErrorException $e) {
                Log::error("Stripe API Error while deleting Payment Link {$paymentLinkId}: " . $e->getMessage());
            } catch (\Exception $e) {
                Log::error("Unexpected error while deleting Payment Link {$paymentLinkId}: " . $e->getMessage());
            }
        } else {
            Log::warning("Checkout Session {$session->id} does not contain a payment_link ID.");
        }
    }
}

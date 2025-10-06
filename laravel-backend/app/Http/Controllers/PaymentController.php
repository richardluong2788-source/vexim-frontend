<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Package;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Webhook;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Webhooks\VerifyWebhookSignature;

class PaymentController extends Controller
{
    /**
     * Get available packages
     */
    public function getPackages()
    {
        $packages = Package::where('is_active', true)
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|in:stripe,paypal,bank_transfer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found'
            ], 404);
        }

        $package = Package::find($request->package_id);

        try {
            // Create payment record
            $payment = Payment::create([
                'company_id' => $company->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'currency' => 'USD',
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            ]);

            // Log action
            AuditLog::log(
                'payment_initiated',
                'Payment',
                $payment->id,
                null,
                $payment->toArray(),
                "Payment initiated for package: {$package->name}"
            );

            // Generate payment URL based on method
            $paymentUrl = $this->generatePaymentUrl($payment, $request->payment_method);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment' => $payment,
                    'payment_url' => $paymentUrl,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback/webhook
     */
    public function handleCallback(Request $request)
    {
        $paymentMethod = $request->header('X-Payment-Method', 'stripe');
        
        $idempotencyKey = $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $existingPayment = Payment::where('idempotency_key', $idempotencyKey)->first();
            if ($existingPayment) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook already processed',
                    'data' => $existingPayment
                ]);
            }
        }
        
        // Verify webhook signature based on payment method
        if (!$this->verifyWebhookSignature($request, $paymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'status' => 'required|in:completed,failed',
            'payment_reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::where('transaction_id', $request->transaction_id)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        try {
            $oldValues = $payment->toArray();

            if ($request->status === 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'payment_reference' => $request->payment_reference,
                    'paid_at' => now(),
                    'idempotency_key' => $idempotencyKey,
                ]);

                // Upgrade company package
                $this->upgradeCompanyPackage($payment);

                // Log action
                AuditLog::log(
                    'payment_completed',
                    'Payment',
                    $payment->id,
                    $oldValues,
                    $payment->fresh()->toArray(),
                    'Payment completed successfully'
                );

            } else {
                $payment->update([
                    'status' => 'failed',
                    'payment_reference' => $request->payment_reference,
                    'idempotency_key' => $idempotencyKey,
                ]);

                // Log action
                AuditLog::log(
                    'payment_failed',
                    'Payment',
                    $payment->id,
                    $oldValues,
                    $payment->fresh()->toArray(),
                    'Payment failed'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated',
                'data' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory()
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found'
            ], 404);
        }

        $payments = $company->payments()
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Admin: Get all payments
     */
    public function getAllPayments(Request $request)
    {
        $query = Payment::with(['company', 'package']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Generate payment URL based on method
     */
    private function generatePaymentUrl($payment, $method)
    {
        // This would integrate with actual payment gateways
        // For now, return placeholder URLs
        
        $baseUrl = config('app.url');
        
        switch ($method) {
            case 'stripe':
                return "{$baseUrl}/payment/stripe/{$payment->transaction_id}";
            case 'paypal':
                return "{$baseUrl}/payment/paypal/{$payment->transaction_id}";
            case 'bank_transfer':
                return "{$baseUrl}/payment/bank-transfer/{$payment->transaction_id}";
            default:
                return "{$baseUrl}/payment/{$payment->transaction_id}";
        }
    }

    /**
     * Upgrade company package after successful payment
     */
    private function upgradeCompanyPackage($payment)
    {
        $company = $payment->company;
        $package = $payment->package;

        $oldValues = $company->toArray();

        // Calculate expiry date
        $expiryDate = now()->addDays($package->duration_days);

        $company->update([
            'package_id' => $package->id,
            'package_expires_at' => $expiryDate,
        ]);

        // Log action
        AuditLog::log(
            'package_upgraded',
            'Company',
            $company->id,
            $oldValues,
            $company->fresh()->toArray(),
            "Package upgraded to: {$package->name}"
        );
    }

    /**
     * Check package status
     */
    public function checkPackageStatus()
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found'
            ], 404);
        }

        $currentPackage = $company->package;
        $isActive = $company->hasActivePackage();
        $daysRemaining = null;

        if ($company->package_expires_at) {
            $daysRemaining = now()->diffInDays($company->package_expires_at, false);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_package' => $currentPackage,
                'is_active' => $isActive,
                'expires_at' => $company->package_expires_at,
                'days_remaining' => $daysRemaining,
            ]
        ]);
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(Request $request, string $method): bool
    {
        try {
            switch ($method) {
                case 'stripe':
                    return $this->verifyStripeSignature($request);
                case 'paypal':
                    return $this->verifyPayPalSignature($request);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            \Log::error('Webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify Stripe webhook signature
     */
    private function verifyStripeSignature(Request $request): bool
    {
        $webhookSecret = config('services.stripe.webhook_secret');
        
        if (!$webhookSecret) {
            \Log::warning('Stripe webhook secret not configured');
            return false;
        }

        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
            return true;
        } catch (\UnexpectedValueException $e) {
            \Log::error('Invalid Stripe payload: ' . $e->getMessage());
            return false;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Log::error('Invalid Stripe signature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify PayPal webhook signature using official SDK
     */
    private function verifyPayPalSignature(Request $request): bool
    {
        $webhookId = config('services.paypal.webhook_id');
        
        if (!$webhookId) {
            \Log::warning('PayPal webhook ID not configured');
            return false;
        }

        try {
            $clientId = config('services.paypal.client_id');
            $clientSecret = config('services.paypal.secret');
            $mode = config('services.paypal.mode', 'sandbox');

            // Create PayPal environment
            if ($mode === 'live') {
                $environment = new ProductionEnvironment($clientId, $clientSecret);
            } else {
                $environment = new SandboxEnvironment($clientId, $clientSecret);
            }

            $client = new PayPalHttpClient($environment);

            // Build verification request
            $verifyRequest = new VerifyWebhookSignature();
            $verifyRequest->body = [
                'auth_algo' => $request->header('Paypal-Auth-Algo'),
                'cert_url' => $request->header('Paypal-Cert-Url'),
                'transmission_id' => $request->header('Paypal-Transmission-Id'),
                'transmission_sig' => $request->header('Paypal-Transmission-Sig'),
                'transmission_time' => $request->header('Paypal-Transmission-Time'),
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($request->getContent(), true),
            ];

            // Execute verification
            $response = $client->execute($verifyRequest);

            if ($response->statusCode === 200 && $response->result->verification_status === 'SUCCESS') {
                return true;
            }

            \Log::error('PayPal webhook verification failed', [
                'status' => $response->result->verification_status ?? 'unknown'
            ]);
            return false;

        } catch (\Exception $e) {
            \Log::error('PayPal webhook verification error: ' . $e->getMessage());
            return false;
        }
    }
}

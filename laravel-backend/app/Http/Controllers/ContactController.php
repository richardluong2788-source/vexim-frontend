<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use App\Mail\ContactRequestReceived;
use App\Mail\ContactRequestNotification;
use App\Mail\ContactResponse;

class ContactController extends Controller
{
    /**
     * Request contact information
     */
    public function requestContact(Request $request)
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'buyer') {
            // Reset counter if needed
            if ($user->shouldResetContactCount()) {
                $user->resetContactCount();
            }

            // Get buyer's package contact limit
            $contactLimit = $user->company && $user->company->package 
                ? $user->company->package->contact_limit 
                : 1; // Default to 1 for free/no package

            // Check if unlimited (0 or -1 means unlimited)
            if ($contactLimit > 0 && $user->weekly_contact_count >= $contactLimit) {
                return response()->json([
                    'success' => false,
                    'message' => "You've reached your weekly contact limit ({$contactLimit} contacts). Please upgrade your plan to continue.",
                    'limit_reached' => true,
                    'current_count' => $user->weekly_contact_count,
                    'limit' => $contactLimit,
                    'resets_at' => $user->contact_count_reset_at
                ], 429);
            }
        }

        $key = 'contact-request:' . ($request->ip() . ':' . $request->input('email'));
        $maxAttempts = config('app.contact_form_rate_limit', 5);
        $decayMinutes = config('app.contact_form_rate_limit_minutes', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many contact requests. Please try again in {$seconds} seconds."
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'message' => 'required|string|max:1000',
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'required|string|max:100',
            'recaptcha_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$this->verifyRecaptcha($request->recaptcha_token, $request->ip())) {
            return response()->json([
                'success' => false,
                'message' => 'reCAPTCHA verification failed. Please try again.'
            ], 422);
        }

        try {
            $company = Company::with('users')->find($request->company_id);

            if (!$company->isVerified()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only contact verified suppliers'
                ], 403);
            }

            $contact = Contact::create([
                'company_id' => $request->company_id,
                'buyer_id' => auth()->check() ? auth()->id() : null,
                'company_name' => $request->company_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => $request->country,
                'message' => $request->message,
                'status' => 'pending',
            ]);

            if ($user && $user->role === 'buyer') {
                $user->increment('weekly_contact_count');
            }

            RateLimiter::hit($key, $decayMinutes * 60);

            AuditLog::log(
                'contact_requested',
                'Contact',
                $contact->id,
                null,
                $contact->toArray(),
                'Buyer requested contact information'
            );

            $supplier = $company->users()->first();
            if ($supplier) {
                Mail::to($supplier->email)->queue(
                    new ContactRequestNotification($contact, $company)
                );
            }

            Mail::to($request->email)->queue(
                new ContactRequestReceived($contact, $company)
            );

            return response()->json([
                'success' => true,
                'message' => 'Contact request sent successfully. The supplier will respond within 24-48 hours.',
                'data' => $contact,
                'remaining_contacts' => $user && $user->role === 'buyer' 
                    ? max(0, ($user->company->package->contact_limit ?? 1) - $user->weekly_contact_count)
                    : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contact requests for supplier
     */
    public function getSupplierContacts()
    {
        $user = auth()->user();
        
        if ($user->role !== 'supplier') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found'
            ], 404);
        }

        $contacts = $company->contacts()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Get contact requests for buyer
     */
    public function getBuyerContacts()
    {
        $contacts = Contact::with('company')
            ->where('buyer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Supplier: Respond to contact request
     */
    public function respondToContact(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response_message' => 'required|string|max:2000',
            'share_full_contact' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = Contact::with('company')->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact request not found'
            ], 404);
        }

        if ($contact->company->users()->where('id', auth()->id())->doesntExist()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $oldValues = $contact->toArray();

            $contact->update([
                'status' => 'responded',
                'response_message' => $request->response_message,
                'responded_at' => now(),
            ]);

            if ($request->share_full_contact) {
                $contact->company->update([
                    'show_contact_info' => true
                ]);
            }

            AuditLog::log(
                'contact_responded',
                'Contact',
                $contact->id,
                $oldValues,
                $contact->fresh()->toArray(),
                'Supplier responded to contact request'
            );

            Mail::to($contact->email)->send(
                new ContactResponse($contact, $request->share_full_contact)
            );

            return response()->json([
                'success' => true,
                'message' => 'Response sent successfully',
                'data' => $contact->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Response failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get masked contact info for company
     */
    public function getMaskedContact($companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $company->masked_email,
                'phone' => $company->masked_phone,
                'show_full_contact' => $company->show_contact_info,
                'can_request_contact' => $company->isVerified(),
            ]
        ]);
    }

    /**
     * Supplier: Toggle contact visibility
     */
    public function toggleContactVisibility(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'supplier') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'show_contact_info' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $oldValues = $company->toArray();

            $company->update([
                'show_contact_info' => $request->show_contact_info
            ]);

            AuditLog::log(
                'contact_visibility_changed',
                'Company',
                $company->id,
                $oldValues,
                $company->fresh()->toArray(),
                'Contact visibility changed to: ' . ($request->show_contact_info ? 'visible' : 'masked')
            );

            return response()->json([
                'success' => true,
                'message' => 'Contact visibility updated',
                'data' => [
                    'show_contact_info' => $company->show_contact_info
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get buyer's contact limit status
     */
    public function getContactLimitStatus()
    {
        $user = auth()->user();
        
        if (!$user || $user->role !== 'buyer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Reset counter if needed
        if ($user->shouldResetContactCount()) {
            $user->resetContactCount();
        }

        $contactLimit = $user->company && $user->company->package 
            ? $user->company->package->contact_limit 
            : 1;

        return response()->json([
            'success' => true,
            'data' => [
                'contact_limit' => $contactLimit,
                'used_contacts' => $user->weekly_contact_count,
                'remaining_contacts' => $contactLimit > 0 ? max(0, $contactLimit - $user->weekly_contact_count) : 'unlimited',
                'resets_at' => $user->contact_count_reset_at,
                'is_unlimited' => $contactLimit <= 0
            ]
        ]);
    }

    /**
     * Verify reCAPTCHA token
     */
    private function verifyRecaptcha(string $token, string $ip): bool
    {
        $secretKey = config('services.recaptcha.secret_key');
        
        if (!$secretKey) {
            \Log::warning('reCAPTCHA secret key not configured');
            return config('app.env') === 'local';
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            $result = $response->json();
            
            return isset($result['success']) && $result['success'] === true && $result['score'] >= 0.5;
        } catch (\Exception $e) {
            \Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            return false;
        }
    }
}

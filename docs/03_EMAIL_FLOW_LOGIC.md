# Vexim Global - Email Flow Logic

## Overview
The admin-mediated contact system ensures privacy and prevents spam by routing all buyer-supplier communication through admin approval.

---

## Email Flow Diagram

\`\`\`
BUYER                    ADMIN                    SUPPLIER
  |                        |                         |
  |--[1] Submit Request-|                         |
  |                        |                         |
  |<--[2] Confirmation-----|                         |
  |                        |                         |
  |                        |--[3] Review Request--|
  |                        |                         |
  |                        |<--[4] Approve/Reject----|
  |                        |                         |
  |                        |--[5] Notify Supplier-|
  |                        |                         |
  |<--[6] Notify Buyer-----|                         |
  |                        |                         |
  |<--[7] Direct Contact (if approved)------------|
\`\`\`

---

## Step-by-Step Email Flow

### Step 1: Buyer Submits Contact Request

**Trigger:** Buyer clicks "Contact Supplier" button and submits form

**Action:** System creates record in `contact_requests` table with status `pending`

**Email Sent:** Confirmation to Buyer

**Email Template:**
\`\`\`
Subject: Contact Request Submitted - Vexim Global

Dear [Buyer Name],

Your contact request has been submitted successfully.

Request Details:
- Supplier: [Company Name]
- Subject: [Subject]
- Submitted: [Date/Time]

Our admin team will review your request within 24 hours and forward it to the supplier if approved.

Request ID: #[Request ID]

Best regards,
Vexim Global Team
\`\`\`

---

### Step 2: Admin Receives Notification

**Trigger:** New contact request created

**Action:** Email sent to admin dashboard

**Email Template:**
\`\`\`
Subject: New Contact Request - Review Required

Admin Dashboard Alert:

A new contact request requires your review.

Buyer: [Buyer Name] ([Buyer Email])
Supplier: [Company Name]
Subject: [Subject]
Message: [Message Preview...]

Review Now: [Link to Admin Dashboard]

Request ID: #[Request ID]
\`\`\`

---

### Step 3: Admin Reviews Request

**Action:** Admin logs into dashboard and reviews:
- Buyer information
- Message content
- Supplier profile
- Request legitimacy

**Decision:** Approve or Reject

---

### Step 4: Admin Approves Request

**Trigger:** Admin clicks "Approve" button

**Action:** 
- Update `contact_requests` status to `approved`
- Record `admin_id` and `reviewed_at` timestamp

**Emails Sent:**
1. To Supplier (with masked buyer info)
2. To Buyer (confirmation)

---

### Step 5: Email to Supplier (Masked Contact)

**Email Template:**
\`\`\`
Subject: New Business Inquiry - Vexim Global

Dear [Supplier Company Name],

You have received a new business inquiry through Vexim Global.

Buyer Information:
- Name: [Buyer Name]
- Email: b***r@example.com (masked)
- Phone: +84***6789 (masked)
- Country: [Country]

Inquiry Details:
- Subject: [Subject]
- Product Interest: [Product Name]
- Message:
  [Full Message Content]

To respond to this inquiry, please reply to this email or contact the buyer directly at:
Email: [Full Buyer Email - revealed after approval]
Phone: [Full Buyer Phone - revealed after approval]

Request ID: #[Request ID]
Date: [Date/Time]

Best regards,
Vexim Global Team

---
This inquiry was verified by our admin team.
\`\`\`

**Note:** Depending on package tier, full contact info may be revealed immediately or require payment to unlock.

---

### Step 6: Email to Buyer (Confirmation)

**Email Template:**
\`\`\`
Subject: Your Contact Request Has Been Approved

Dear [Buyer Name],

Good news! Your contact request has been approved and forwarded to the supplier.

Request Details:
- Supplier: [Company Name]
- Subject: [Subject]
- Approved: [Date/Time]

The supplier has received your inquiry and will contact you directly at:
- Email: [Your Email]
- Phone: [Your Phone]

You should expect a response within 2-3 business days.

View Request Status: [Link to Dashboard]

Best regards,
Vexim Global Team
\`\`\`

---

### Step 7: Admin Rejects Request

**Trigger:** Admin clicks "Reject" button

**Action:**
- Update `contact_requests` status to `rejected`
- Record rejection reason

**Email Sent:** To Buyer only

**Email Template:**
\`\`\`
Subject: Contact Request Update - Vexim Global

Dear [Buyer Name],

Thank you for your interest in [Company Name].

After review, we are unable to forward your contact request at this time.

Reason: [Admin Notes - e.g., "Incomplete information", "Spam detected"]

If you believe this is an error, please contact our support team.

Request ID: #[Request ID]

Best regards,
Vexim Global Team
\`\`\`

---

## Contact Masking Logic

### Free Package Suppliers
- Email: `c***t@company.com` (first and last character visible)
- Phone: `+84***6789` (country code and last 4 digits visible)

### Premium Package Suppliers
- Full contact information revealed immediately after admin approval

### Contact Unlock Feature (Optional Monetization)
- Buyers can pay a fee (e.g., $5) to unlock full contact details
- Payment processed through Stripe
- Transaction recorded in `transactions` table

---

## Laravel Implementation

### ContactRequestService.php

\`\`\`php
<?php

namespace App\Services;

use App\Models\ContactRequest;
use App\Models\User;
use App\Models\Company;
use App\Mail\ContactRequestSubmitted;
use App\Mail\ContactRequestApproved;
use App\Mail\ContactRequestRejected;
use App\Mail\NewContactRequestAdmin;
use Illuminate\Support\Facades\Mail;

class ContactRequestService
{
    /**
     * Submit a new contact request
     */
    public function submitRequest($data)
    {
        // Create contact request
        $request = ContactRequest::create([
            'buyer_id' => auth()->id(),
            'supplier_id' => $data['supplier_id'],
            'company_id' => $data['company_id'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'product_interest' => $data['product_interest'] ?? null,
            'status' => 'pending',
        ]);

        // Send confirmation email to buyer
        $buyer = auth()->user();
        Mail::to($buyer->email)->send(new ContactRequestSubmitted($request));

        // Send notification to admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new NewContactRequestAdmin($request));
        }

        return $request;
    }

    /**
     * Approve contact request
     */
    public function approveRequest($requestId, $adminNotes = null)
    {
        $request = ContactRequest::findOrFail($requestId);
        
        // Update status
        $request->update([
            'status' => 'approved',
            'admin_id' => auth()->id(),
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
        ]);

        // Get buyer and supplier
        $buyer = User::find($request->buyer_id);
        $supplier = User::find($request->supplier_id);
        $company = Company::find($request->company_id);

        // Send email to supplier (with masked or full contact based on package)
        Mail::to($supplier->email)->send(new ContactRequestApproved($request, 'supplier'));

        // Send confirmation to buyer
        Mail::to($buyer->email)->send(new ContactRequestApproved($request, 'buyer'));

        return $request;
    }

    /**
     * Reject contact request
     */
    public function rejectRequest($requestId, $reason)
    {
        $request = ContactRequest::findOrFail($requestId);
        
        // Update status
        $request->update([
            'status' => 'rejected',
            'admin_id' => auth()->id(),
            'admin_notes' => $reason,
            'reviewed_at' => now(),
        ]);

        // Send email to buyer
        $buyer = User::find($request->buyer_id);
        Mail::to($buyer->email)->send(new ContactRequestRejected($request, $reason));

        return $request;
    }

    /**
     * Mask contact information
     */
    public function maskEmail($email)
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1);
        
        return $maskedName . '@' . $domain;
    }

    /**
     * Mask phone number
     */
    public function maskPhone($phone)
    {
        $length = strlen($phone);
        return substr($phone, 0, 3) . str_repeat('*', $length - 7) . substr($phone, -4);
    }
}
\`\`\`

---

## Email Templates Location

Store email templates in: `resources/views/emails/`

Example structure:
\`\`\`
resources/views/emails/
├── contact-request-submitted.blade.php
├── contact-request-approved-buyer.blade.php
├── contact-request-approved-supplier.blade.php
├── contact-request-rejected.blade.php
└── new-contact-request-admin.blade.php
\`\`\`

---

## Configuration

### .env File
\`\`\`env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@vexim.com
MAIL_FROM_NAME="Vexim Global"

ADMIN_EMAIL=admin@vexim.com
\`\`\`

---

## Testing Email Flow

### Using Mailtrap (Development)
\`\`\`env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
\`\`\`

### Test Command
\`\`\`bash
php artisan tinker

# Send test email
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
\`\`\`

---

## Summary

This email flow ensures:
1. ✅ Privacy protection (masked contacts)
2. ✅ Spam prevention (admin review)
3. ✅ Professional communication
4. ✅ Clear audit trail
5. ✅ Monetization opportunity (contact unlock)

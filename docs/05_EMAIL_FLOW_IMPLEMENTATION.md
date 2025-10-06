# Email Flow Implementation Guide

## Overview
This document explains the admin-mediated contact system where all buyer-supplier communication goes through admin approval.

## Email Flow Process

### Step 1: Buyer Sends Contact Request

**Endpoint:** `POST /api/buyer/contact-request`

**Request Body:**
\`\`\`json
{
  "company_id": 5,
  "subject": "Inquiry about Rice Export",
  "message": "I am interested in importing rice to USA. Please provide pricing details."
}
\`\`\`

**What Happens:**
1. System masks buyer's email and phone
   - Email: `john.doe@example.com` → `jo******@example.com`
   - Phone: `+1234567890` → `+12****7890`
2. Creates message record with status `pending`
3. Sets `admin_status` to `pending`
4. Sends notification to all admins

### Step 2: Admin Reviews Request

**Endpoint:** `GET /api/admin/messages/pending`

**Admin sees:**
- Buyer name and masked contact info
- Supplier company name
- Message subject and content
- Timestamp

**Admin Actions:**
- Approve: Forward to supplier with full buyer contact info
- Reject: Decline request (with optional reason)

### Step 3: Admin Approves/Rejects

**Endpoint:** `PUT /api/admin/messages/{id}/review`

**Request Body:**
\`\`\`json
{
  "admin_status": "approved",
  "admin_note": "Verified legitimate inquiry"
}
\`\`\`

**If Approved:**
1. System updates message status to `approved`
2. Sends email to supplier with:
   - Full buyer contact info (unmasked)
   - Original message
   - Buyer company details
3. Sends confirmation email to buyer
4. Supplier can now contact buyer directly

**If Rejected:**
1. System updates message status to `rejected`
2. Sends notification to buyer with reason
3. No contact info shared

## Email Templates

### 1. Admin Notification Email

**To:** Admin
**Subject:** New Contact Request - Review Required

\`\`\`
Dear Admin,

A new contact request has been submitted:

Buyer: John Doe (jo******@example.com)
Supplier: ABC Rice Export Co.
Subject: Inquiry about Rice Export

Message:
"I am interested in importing rice to USA. Please provide pricing details."

Please review and approve/reject this request in the admin dashboard.

[Review Request Button]
\`\`\`

### 2. Supplier Notification Email (After Approval)

**To:** Supplier
**Subject:** New Buyer Inquiry - Contact Details Provided

\`\`\`
Dear ABC Rice Export Co.,

You have received a new inquiry from a verified buyer:

Buyer Information:
- Name: John Doe
- Email: john.doe@example.com
- Phone: +1234567890
- Company: USA Import Corp

Subject: Inquiry about Rice Export

Message:
"I am interested in importing rice to USA. Please provide pricing details."

You can now contact the buyer directly using the information above.

Admin Note: Verified legitimate inquiry

Best regards,
Vexim Global Team
\`\`\`

### 3. Buyer Confirmation Email

**To:** Buyer
**Subject:** Your Contact Request Has Been Approved

\`\`\`
Dear John Doe,

Your contact request to ABC Rice Export Co. has been approved by our admin team.

The supplier has received your full contact information and will reach out to you directly via email or phone.

Request Details:
- Supplier: ABC Rice Export Co.
- Subject: Inquiry about Rice Export
- Date: 2025-01-15

Thank you for using Vexim Global!

Best regards,
Vexim Global Team
\`\`\`

## Laravel Mail Implementation

### Create Mail Classes

\`\`\`bash
php artisan make:mail ContactRequestToAdmin
php artisan make:mail ContactRequestApproved
php artisan make:mail ContactRequestForwarded
\`\`\`

### Example: ContactRequestForwarded.php

\`\`\`php
<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactRequestForwarded extends Mailable
{
    use Queueable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject('New Buyer Inquiry - Contact Details Provided')
                    ->view('emails.contact-forwarded')
                    ->with([
                        'buyerName' => $this->message->sender->name,
                        'buyerEmail' => $this->message->sender->email,
                        'buyerPhone' => $this->message->sender->phone,
                        'subject' => $this->message->subject,
                        'messageContent' => $this->message->message,
                        'companyName' => $this->message->company->company_name,
                        'adminNote' => $this->message->admin_note,
                    ]);
    }
}
\`\`\`

## Email Configuration

### .env Settings

\`\`\`env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@vexim-global.com
MAIL_FROM_NAME="Vexim Global"
\`\`\`

## Privacy & Security

### Contact Masking Rules

**Email Masking:**
- Show first 2 characters
- Mask middle characters with asterisks
- Show domain fully
- Example: `john.doe@example.com` → `jo******@example.com`

**Phone Masking:**
- Show first 3 digits
- Mask middle digits
- Show last 3 digits
- Example: `+1234567890` → `+12****7890`

### Security Measures

1. **Admin Verification Required**
   - All contact requests must be approved by admin
   - Prevents spam and fraudulent inquiries

2. **Rate Limiting**
   - Limit contact requests per buyer (e.g., 5 per day)
   - Prevent abuse of the system

3. **Audit Trail**
   - Log all admin actions
   - Track approval/rejection reasons
   - Monitor suspicious patterns

## Database Records

### Message Table Fields

\`\`\`sql
- sender_id (buyer)
- receiver_id (supplier)
- company_id
- subject
- message
- admin_status (pending/approved/rejected)
- admin_note
- buyer_email_masked
- buyer_phone_masked
- status (pending/approved/rejected)
- created_at
- updated_at
\`\`\`

## Frontend Integration

### Buyer Contact Form

\`\`\`html
<form id="contactForm">
  <input type="hidden" name="company_id" value="5">
  <input type="text" name="subject" placeholder="Subject" required>
  <textarea name="message" placeholder="Your message" required></textarea>
  <button type="submit">Send Contact Request</button>
</form>

<script>
document.getElementById('contactForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData);
  
  const response = await fetch('/api/buyer/contact-request', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
    body: JSON.stringify(data)
  });
  
  const result = await response.json();
  
  if (result.success) {
    alert('Contact request sent! Admin will review and forward to supplier.');
  }
});
</script>
\`\`\`

### Admin Review Interface

\`\`\`html
<div class="pending-messages">
  <div class="message-card">
    <h3>Buyer: John Doe (jo******@example.com)</h3>
    <p><strong>Supplier:</strong> ABC Rice Export Co.</p>
    <p><strong>Subject:</strong> Inquiry about Rice Export</p>
    <p><strong>Message:</strong> I am interested in importing rice...</p>
    
    <button onclick="reviewMessage(123, 'approved')">Approve</button>
    <button onclick="reviewMessage(123, 'rejected')">Reject</button>
  </div>
</div>

<script>
async function reviewMessage(messageId, status) {
  const note = prompt('Add admin note (optional):');
  
  const response = await fetch(`/api/admin/messages/${messageId}/review`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
    body: JSON.stringify({
      admin_status: status,
      admin_note: note
    })
  });
  
  const result = await response.json();
  
  if (result.success) {
    alert('Message reviewed successfully!');
    location.reload();
  }
}
</script>
\`\`\`

## Testing Checklist

- [ ] Buyer can send contact request
- [ ] Admin receives notification
- [ ] Contact info is properly masked
- [ ] Admin can approve request
- [ ] Supplier receives email with full contact info
- [ ] Buyer receives confirmation
- [ ] Admin can reject request
- [ ] Rejected requests don't share contact info
- [ ] Email templates render correctly
- [ ] Rate limiting works
- [ ] Audit trail is recorded

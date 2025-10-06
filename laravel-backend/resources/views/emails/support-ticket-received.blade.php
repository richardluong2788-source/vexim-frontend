<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .ticket-info { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #2563eb; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Support Ticket Received</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>We have received your support ticket and our team will review it shortly.</p>
            
            <div class="ticket-info">
                <p><strong>Ticket ID:</strong> #{{ $ticketId }}</p>
                <p><strong>Subject:</strong> {{ $subject }}</p>
                <p><strong>Submitted:</strong> {{ $createdAt }}</p>
                <p><strong>Status:</strong> Open</p>
            </div>
            
            <p><strong>Your Message:</strong></p>
            <p style="background: white; padding: 15px; border: 1px solid #e5e7eb;">
                {{ $message }}
            </p>
            
            <p>Our support team typically responds within 24 hours. You will receive an email notification when we reply to your ticket.</p>
            
            <p>Thank you for contacting Vexim Global support.</p>
        </div>
        
        <div class="footer">
            <p>Vexim Global - B2B Verified Supplier Directory</p>
            <p>© {{ date('Y') }} Vexim Global. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

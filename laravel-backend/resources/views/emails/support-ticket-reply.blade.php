<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .ticket-info { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #10b981; }
        .reply-box { background: #ecfdf5; padding: 20px; margin: 20px 0; border: 1px solid #10b981; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Support Team Response</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>Our support team has responded to your ticket.</p>
            
            <div class="ticket-info">
                <p><strong>Ticket ID:</strong> #{{ $ticketId }}</p>
                <p><strong>Subject:</strong> {{ $subject }}</p>
                <p><strong>Status:</strong> {{ ucfirst($status) }}</p>
                <p><strong>Replied:</strong> {{ $repliedAt }}</p>
            </div>
            
            <div class="reply-box">
                <p><strong>Admin Response:</strong></p>
                <p>{{ $adminReply }}</p>
            </div>
            
            <p><strong>Your Original Message:</strong></p>
            <p style="background: white; padding: 15px; border: 1px solid #e5e7eb;">
                {{ $originalMessage }}
            </p>
            
            @if($status !== 'resolved' && $status !== 'closed')
            <p>If you need further assistance, please reply to this email or create a new support ticket.</p>
            @else
            <p>This ticket has been marked as {{ $status }}. If you need additional help, please create a new support ticket.</p>
            @endif
            
            <p>Thank you for using Vexim Global.</p>
        </div>
        
        <div class="footer">
            <p>Vexim Global - B2B Verified Supplier Directory</p>
            <p>© {{ date('Y') }} Vexim Global. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

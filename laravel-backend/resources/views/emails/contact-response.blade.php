<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Response</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .success-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .response-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .contact-info {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📧 Supplier Has Responded</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $contact->contact_person }},</p>
        
        <div class="success-box">
            <p><strong>{{ $contact->company->name }} has responded to your inquiry!</strong></p>
        </div>
        
        <div class="response-box">
            <h3>Response from {{ $contact->company->name }}:</h3>
            <p>{{ $contact->response_message }}</p>
        </div>
        
        @if($shareFullContact)
            <div class="contact-info">
                <h3>✓ Full Contact Information Shared:</h3>
                <p>The supplier has agreed to share their full contact information with you:</p>
                <p><strong>Email:</strong> {{ $contact->company->contact_email }}</p>
                <p><strong>Phone:</strong> {{ $contact->company->contact_phone }}</p>
                <p><strong>Website:</strong> {{ $contact->company->website }}</p>
            </div>
            <p>You can now contact them directly using the information above.</p>
        @else
            <p>For further communication, please continue through the VEXIM platform or wait for the supplier to share their direct contact information.</p>
        @endif
        
        <center>
            <a href="{{ config('app.frontend_url') }}/buyer/contacts" class="button">
                View Full Conversation
            </a>
        </center>
        
        <p>Best regards,<br>The VEXIM Team</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} VEXIM. All rights reserved.</p>
    </div>
</body>
</html>

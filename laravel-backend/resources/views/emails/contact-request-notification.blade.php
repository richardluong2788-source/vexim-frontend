<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Request</title>
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
        .alert-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .buyer-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            font-style: italic;
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
        <h1>🔔 New Contact Request</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $company->name }} Team,</p>
        
        <div class="alert-box">
            <p><strong>You have received a new contact request from a potential buyer!</strong></p>
        </div>
        
        <div class="buyer-info">
            <h3>Buyer Information:</h3>
            <p><strong>Company:</strong> {{ $contact->company_name }}</p>
            <p><strong>Contact Person:</strong> {{ $contact->contact_person }}</p>
            <p><strong>Email:</strong> {{ $contact->email }}</p>
            @if($contact->phone)
                <p><strong>Phone:</strong> {{ $contact->phone }}</p>
            @endif
            <p><strong>Country:</strong> {{ $contact->country }}</p>
            <p><strong>Request Date:</strong> {{ $contact->created_at->format('F d, Y H:i') }}</p>
        </div>
        
        <h3>Message from Buyer:</h3>
        <div class="message-box">
            {{ $contact->message }}
        </div>
        
        <p><strong>Action Required:</strong> Please respond to this inquiry within 24-48 hours to maintain good buyer relations.</p>
        
        <center>
            <a href="{{ config('app.frontend_url') }}/supplier/contacts" class="button">
                View & Respond
            </a>
        </center>
        
        <p>Responding promptly to buyer inquiries helps build trust and increases your chances of securing business opportunities.</p>
        
        <p>Best regards,<br>The VEXIM Team</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} VEXIM. All rights reserved.</p>
    </div>
</body>
</html>

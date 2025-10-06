<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Request Received</title>
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
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
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
        <h1>✓ Request Sent Successfully</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $contact->contact_person }},</p>
        
        <div class="success-box">
            <p><strong>Your contact request has been sent to {{ $company->name }}!</strong></p>
        </div>
        
        <p>Thank you for your interest in connecting with {{ $company->name }}. Your request has been forwarded to their team.</p>
        
        <div class="info-box">
            <h3>Request Details:</h3>
            <p><strong>Supplier:</strong> {{ $company->name }}</p>
            <p><strong>Your Company:</strong> {{ $contact->company_name }}</p>
            <p><strong>Contact Person:</strong> {{ $contact->contact_person }}</p>
            <p><strong>Email:</strong> {{ $contact->email }}</p>
            <p><strong>Request Date:</strong> {{ $contact->created_at->format('F d, Y') }}</p>
        </div>
        
        <h3>What Happens Next?</h3>
        <ul>
            <li>The supplier will review your request</li>
            <li>They typically respond within 24-48 hours</li>
            <li>You'll receive an email when they respond</li>
            <li>You can track your request status in your dashboard</li>
        </ul>
        
        <p>We'll notify you as soon as the supplier responds to your inquiry.</p>
        
        <p>Best regards,<br>The VEXIM Team</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} VEXIM. All rights reserved.</p>
    </div>
</body>
</html>

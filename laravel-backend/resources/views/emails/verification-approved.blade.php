<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Approved</title>
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
        .verification-badge {
            background: #10b981;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
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
        <h1>🎉 Congratulations!</h1>
        <p>Your company has been verified</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $company->name }} Team,</p>
        
        <p>We are pleased to inform you that your company has successfully completed our verification process and has been approved as a verified supplier on VEXIM.</p>
        
        <div class="verification-badge">
            ✓ VERIFIED SUPPLIER
        </div>
        
        <div class="info-box">
            <h3>Your Verification Details:</h3>
            <p><strong>Company Name:</strong> {{ $company->name }}</p>
            <p><strong>Verification ID:</strong> {{ $company->verification_id }}</p>
            <p><strong>Verified Date:</strong> {{ $company->verified_at->format('F d, Y') }}</p>
            <p><strong>Package:</strong> {{ $company->package->name ?? 'Free' }}</p>
            <p><strong>Valid Until:</strong> {{ $company->package_expires_at->format('F d, Y') }}</p>
        </div>
        
        <h3>What's Next?</h3>
        <ul>
            <li>Your company profile is now visible to buyers worldwide</li>
            <li>You can start receiving inquiries from potential buyers</li>
            <li>Add products and showcase your capabilities</li>
            <li>Respond to buyer contact requests</li>
            <li>Consider upgrading to a premium package for more visibility</li>
        </ul>
        
        <center>
            <a href="{{ config('app.frontend_url') }}/supplier/dashboard" class="button">
                Go to Dashboard
            </a>
        </center>
        
        <p>If you have any questions, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>The VEXIM Team</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} VEXIM. All rights reserved.</p>
        <p>This is an automated email. Please do not reply directly to this message.</p>
    </div>
</body>
</html>

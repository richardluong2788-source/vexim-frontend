<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Status Update</title>
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
            background: #fef2f2;
            border-left: 4px solid #ef4444;
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
        <h1>Verification Status Update</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $company->name }} Team,</p>
        
        <p>Thank you for submitting your company for verification on VEXIM. After careful review of your application, we regret to inform you that we are unable to approve your verification at this time.</p>
        
        <div class="alert-box">
            <h3>Reason for Rejection:</h3>
            <p>{{ $reason }}</p>
        </div>
        
        <div class="info-box">
            <h3>What You Can Do:</h3>
            <ul>
                <li>Review the rejection reason carefully</li>
                <li>Update your company information and documents</li>
                <li>Ensure all required documents are clear and valid</li>
                <li>Resubmit your application once you've addressed the issues</li>
            </ul>
        </div>
        
        <p>We encourage you to address the concerns mentioned above and resubmit your application. Our team is here to help you through the verification process.</p>
        
        <center>
            <a href="{{ config('app.frontend_url') }}/supplier/verification" class="button">
                Update & Resubmit
            </a>
        </center>
        
        <p>If you have any questions or need clarification, please contact our support team.</p>
        
        <p>Best regards,<br>The VEXIM Team</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} VEXIM. All rights reserved.</p>
    </div>
</body>
</html>

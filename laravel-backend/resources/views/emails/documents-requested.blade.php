<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Additional Documents Required</title>
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
        .info-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .document-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .document-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
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
        <h1>📄 Additional Documents Required</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $company->name }} Team,</p>
        
        <p>Thank you for your verification application. To proceed with your verification, we need some additional documents from you.</p>
        
        <div class="info-box">
            <h3>Message from Verification Team:</h3>
            <p>{{ $message }}</p>
        </div>
        
        <div class="document-list">
            <h3>Required Documents:</h3>
            <ul>
                @foreach($requiredDocuments as $document)
                    <li>{{ $document }}</li>
                @endforeach
            </ul>
        </div>
        
        <p><strong>Important:</strong> Please upload the requested documents within 7 days to avoid delays in your verification process.</p>
        
        <center>
            <a href="{{ config('app.frontend_url') }}/supplier/verification" class="button">
                Upload Documents
            </a>
        </center>
        
        <p>If you have any questions about the required documents, please contact our support team.</p>
        
        <p>Best regards,<br>The VEXIM Verification Team</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} VEXIM. All rights reserved.</p>
    </div>
</body>
</html>

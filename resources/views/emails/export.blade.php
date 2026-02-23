<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export</title>
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
            background-color: #4a5568;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f7fafc;
            padding: 30px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ ucfirst($exportType) }} Export</h1>
    </div>
    <div class="content">
        <p>Hello,</p>
        <p>Your {{ $exportType }} export is ready and attached to this email.</p>
        <p>File: <strong>{{ $fileName }}</strong></p>
        <p>If you have any questions, please don't hesitate to contact us.</p>
        <p>Best regards,<br>{{ config('app.name') }}</p>
    </div>
    <div class="footer">
        <p>This is an automated message, please do not reply.</p>
    </div>
</body>
</html>

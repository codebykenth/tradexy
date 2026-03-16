<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Failed Job: {{ $jobName }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: left;">
        <div style="text-align: center; margin-bottom: 20px;">
            <svg style="width: 50px; height: 50px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h2 style="color: #ef4444; margin-top: 0; text-align: center;">Automated Job Failed: {{ $jobName }}</h2>
        <p style="color: #374151; font-size: 16px;">Hello,</p>
        <p style="color: #374151; font-size: 16px;">An error occurred during the execution of a scheduled task on your Tradexy server. The job was not able to complete successfully.</p>

        <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 0 4px 4px 0;">
            <p style="color: #991b1b; margin: 0; font-family: monospace; word-wrap: break-word;">
                <strong>Job:</strong><br>
                {{ $jobName }}<br><br>
                <strong>Error Details:</strong><br>
                {{ $errorMessage }}
            </p>
        </div>

        <p style="color: #374151; font-size: 16px;">Please check your server logs for more information or investigate the issue on your production server.</p>
        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        <p style="color: #6b7280; font-size: 14px; text-align: center;">Thanks,<br><strong>Tradexy System</strong></p>
    </div>
</body>
</html>

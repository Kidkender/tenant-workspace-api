<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <tle>Vrify Email</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;">

    <div style="max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px;">

        <h2>🚀 Welcome {{ $user->name ?? 'User' }}</h2>

        <p>Please verify your email to activate your account.</p>

        <div style="margin: 20px 0;">
            <a href="{{ $url }}"
               style="padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
                Verify Email
            </a>
        </div>

        <p>If you did not create this account, you can ignore this email.</p>

        <hr>

        <small>This link will expire in 60 minutes.</small>
    </div>

</body>
</html>

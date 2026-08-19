<!DOCTYPE html>
<html>
<head>
    <title>Phone Number Updated</title>
</head>
<body>
    <h2>Hello {{ $user->fullname }},</h2>
    <p>Your phone number has been updated in your account.</p>
    <p><strong>Old Number:</strong> {{ $oldPhone }}</p>
    <p><strong>New Number:</strong> {{ $newPhone }}</p>
    <p>If this wasn’t you, please contact our support immediately.</p>
    <br>
    <p>Thank you,<br>Support Team</p>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Notification</title>
</head>
<body>
    <h2>Hello {{ $recipientType == 'user' ? $booking->serviceUser->user->fullname : $booking->serviceProvider->user->fullname }},</h2>

    @if($recipientType == 'user')
        <p>Your booking has been placed successfully 🎉</p>
    @else
        <p>You have received a new booking request 📩</p>
    @endif

    <p><strong>Booking ID:</strong> {{ $booking->id }}</p>
    <p><strong>Date:</strong> {{ $booking->booking_date }}</p>
    <p><strong>Time:</strong> {{ $booking->booking_time }}</p>
    <p><strong>Total Amount:</strong> {{ $booking->total_amount ?? 0 }}</p>

    <br>
    <p>Thanks,<br>JustBook Team</p>
</body>
</html>

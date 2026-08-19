<!DOCTYPE html>
<html>
<head>
    <title>Booking Status Update</title>
</head>
<body>
    <h2>
        Hello {{ $recipientType == 'user' ? $booking->serviceUser->user->fullname : $booking->serviceProvider->user->fullname }},
    </h2>

    @if($recipientType == 'user')
        <p>Your booking status has been updated ✅</p>
    @else
        <p>A booking assigned to you has been updated 📩</p>
    @endif

    <p><strong>Booking ID:</strong> {{ $booking->id }}</p>
    <p><strong>Old Status:</strong> {{ ucfirst($oldStatus) }}</p>
    <p><strong>New Status:</strong> {{ ucfirst($newStatus) }}</p>
    <p><strong>Date:</strong> {{ $booking->booking_date }}</p>
    <p><strong>Time:</strong> {{ $booking->booking_time }}</p>
    <p><strong>Total Amount:</strong> {{ $booking->total_amount ?? 0 }}</p>

    <br>
    <p>Thanks,<br>JustBook Team</p>
</body>
</html>

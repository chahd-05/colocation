<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colocation Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f7fb; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 24px;">
        <h2 style="margin-top: 0; color: #111827;">Colocation Invitation</h2>

        <p style="color: #374151;">
            You have been invited to join the colocation:
            <strong>{{ $colocation->name }}</strong>
        </p>

        <p style="color: #374151;">
            This invitation expires at:
            <strong>{{ $expiresAt->format('Y-m-d H:i') }}</strong>
        </p>

        <p style="margin: 20px 0;">
            <a href="{{ $acceptUrl }}" style="display: inline-block; background: #16a34a; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 8px; margin-right: 8px;">
                Accept Invitation
            </a>
            <a href="{{ $refuseUrl }}" style="display: inline-block; background: #dc2626; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 8px;">
                Refuse Invitation
            </a>
        </p>

        <p style="font-size: 13px; color: #6b7280;">
            If the buttons do not work, copy and open these links:
        </p>
        <p style="font-size: 12px; color: #6b7280; word-break: break-all;">
            Accept: {{ $acceptUrl }}<br>
            Refuse: {{ $refuseUrl }}
        </p>
    </div>
</body>
</html>

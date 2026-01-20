<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PRIMS Login Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f9fafb; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">

        <h2 style="color:#1f2937;">St. Michael the Archangel Parish</h2>
        <h3 style="color:#374151;">New Google Login Detected</h3>

        <p>Hello <strong>{{ $user->name }}</strong>,</p>

        <p>
            We detected a successful login to your
            <strong>Parish Records Information Management System (PRIMS)</strong>
            account using <strong>Google Sign-In</strong>.
        </p>

        <table style="margin-top:15px;">
            <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td><strong>Login Time:</strong></td>
                <td>{{ now()->toDayDateTimeString() }}</td>
            </tr>
            <tr>
                <td><strong>IP Address:</strong></td>
                <td>{{ request()->ip() }}</td>
            </tr>
        </table>

        <p style="margin-top:20px; color:#6b7280;">
            If this login was not made by you, please contact the parish office
            immediately to secure your account.
        </p>

        <hr>

        <p style="font-size:12px; color:#9ca3af;">
            This is an automated security notification. Please do not reply.
        </p>
    </div>
</body>
</html>

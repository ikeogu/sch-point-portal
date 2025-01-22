<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
        <tr>
            <td align="center" bgcolor="#003366" style="padding: 40px 0 30px 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                Employee Addition Notification
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="color: #003366; font-size: 24px;">
                            Hello {{ $user->first_name }},
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            You are receiving this mail because you have been added as an employee to {{ $farm->name }}, with a role of {{ $role }}.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 10px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            <strong>Your Email Password is:</strong><br>
                            <span style="color: #003366; font-size: 20px;">{{ $password }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            Please login to the system using the following link:
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px 0;">
                            <a href="{{ env('FRONTEND_URL') }}" style="background-color: #003366; color: #ffffff; padding: 15px 25px; text-decoration: none; font-size: 16px; border-radius: 5px;">
                                Login
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            Thanks,<br>
                            {{ config('app.name') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#003366" style="padding: 30px 30px 30px 30px; color: #ffffff; text-align: center; font-size: 14px;">
                &copy; 2024 {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>

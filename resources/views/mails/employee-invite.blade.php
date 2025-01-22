<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .button {
            background-color: #007BFF;
            border: none;
            color: white;
            padding: 15px 25px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
        <tr>
            <td align="center" bgcolor="#007BFF" style="padding: 40px 0 30px 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                Employee Addition Notification
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="color: #007BFF; font-size: 24px;">
                            Hello {{ $user->first_name }},
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            You are receiving this email because you have been added as an employee to {{ $farm->name }} with a role of {{ $role }}.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 10px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            <strong>Your Email Password is:</strong><br>
                            {{ $password }}
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <a href="{{ env('FRONTEND_URL') }}" class="button">Login</a>
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
            <td bgcolor="#007BFF" style="padding: 30px 30px 30px 30px; color: #ffffff; text-align: center; font-size: 14px;">
                &copy; 2024 {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>

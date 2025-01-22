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
                Email Verification
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="color: #003366; font-size: 24px;">
                            Hello {{ $user }},
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            You are receiving this mail because you created an account at {{ config('app.name') }}.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 10px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            <strong>Your Email Verification Code is:</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0 30px 0; color: #003366; font-size: 24px; font-weight: bold; text-align: center; border: 1px solid #003366; background-color: #f0f8ff;">
                            {{ $otp }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            This code expires in 5 minutes.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            Do not share the above code and please ignore this message if you didn't make this request and we won't allow the request.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
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

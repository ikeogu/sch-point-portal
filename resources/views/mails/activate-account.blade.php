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
                Platform Activation
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="color: #003366; font-size: 24px;">
                            Hello,
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            You are receiving this mail because your organization has been activated on our platform. You can now log in and start using the platform.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            <strong>Getting Started</strong><br>
                            To access your platform, please follow these steps:
                            <ol>
                                <li>Log In: Use the credentials provided to log in to your account at <a href="{{env('FRONTEND_URL')}}" style="color: #003366;">Login URL</a>.</li>
                                <li>Explore: Take a tour of the dashboard and explore the various features available to you.</li>
                                <li>Get Support: If you have any questions or need assistance, our support team is here to help. Reach out to us at <a href="mailto:support@yourplatform.com" style="color: #003366;">support@yourplatform.com</a> or visit our <a href="#" style="color: #003366;">Help Center</a>.</li>
                            </ol>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0 30px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            We are excited to have you on board and look forward to helping you achieve your goals with our platform. If you have any feedback or need further assistance, please do not hesitate to reach out.
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

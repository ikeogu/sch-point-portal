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
                Payment Success Notification
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="color: #003366; font-size: 24px;">
                            Hello dear,
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            This is to notify you that your payment for {{$subscribedPlan->subscriptionPlan->title}} plan for {{$organization}} was successful.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            {{$subscribedPlan->amount}} amount was paid for {{$subscribedPlan->no_of_months}} month(s), and the plan starts {{$subscribedPlan->start_date}} and ends on {{$subscribedPlan->end_date}}.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0; color: #333333; font-size: 16px; line-height: 20px;">
                            Thank you for using {{ config('app.name') }}.
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

<!doctype html>
<html lang="ar">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>بشأن طلبك</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;direction:rtl;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
  <tr>
    <td align="center">

      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;">

        <tr>
          <td style="background:#ef4444;padding:24px;text-align:center;color:#fff;">
            <h1 style="margin:0;font-size:22px;">Ehgezly ⚽</h1>
            <p style="margin:5px 0 0;font-size:14px;">منصة حجز الملاعب</p>
          </td>
        </tr>

        <tr>
          <td style="padding:30px;color:#111827;">
            <h2 style="margin-top:0;color:#ef4444;">مرحباً {{ $name }}</h2>

            <p style="font-size:15px;line-height:1.8;">
              نأسف لإبلاغك بأنه تم رفض طلبك كمالك ملعب.
            </p>

            <p style="font-size:15px;line-height:1.8;">
              السبب: {{ $rejection_reason }}
            </p>

            <p style="font-size:14px;color:#374151;">
              للاستفسار يرجى التواصل مع الإدارة.
            </p>
          </td>
        </tr>

        <tr>
          <td style="background:#f9fafb;padding:15px;text-align:center;font-size:12px;color:#6b7280;">
            © {{ date('Y') }} Ehgezly
          </td>
        </tr>

      </table>

    </td>
  </tr>
</table>

</body>
</html>

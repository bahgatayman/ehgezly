<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Reset Password</title>
</head>

<body style="margin:0;padding:0;background-color:#f3fdf6;font-family:Arial, sans-serif;color:#111827;">

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3fdf6;padding:30px 0;">
		<tr>
			<td align="center">

				<!-- Container -->
				<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

					<!-- Header -->
					<tr>
						<td style="padding:26px 30px;background:linear-gradient(135deg,#16a34a,#22c55e);color:#ffffff;text-align:center;">
							<h1 style="margin:0;font-size:22px;letter-spacing:0.5px;">Ehgezly ⚽</h1>
							<p style="margin:6px 0 0;font-size:14px;opacity:0.9;">Football Court Booking Platform</p>
						</td>
					</tr>

					<!-- Body -->
					<tr>
						<td style="padding:28px 30px;">

							<h2 style="margin:0 0 10px;font-size:18px;color:#111827;">Reset Your Password</h2>

							<p style="margin:0 0 12px;line-height:1.7;color:#374151;">
								We received a request to reset your password for your Ehgezly account.
							</p>

							<p style="margin:0 0 22px;line-height:1.7;color:#374151;">
								Click the button below to create a new secure password and get back to booking your favorite football courts.
							</p>

							<!-- Button -->
							<p style="margin:0 0 22px;text-align:center;">
								<a href="{{ $url }}"
								   style="display:inline-block;background:#16a34a;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:8px;font-weight:bold;">
									Reset Password
								</a>
							</p>


							<p style="margin:0 0 20px;font-size:13px;color:#16a34a;word-break:break-all;">
								{{ $token }}
							</p>

							<!-- Warning -->
							<p style="margin:0;font-size:13px;color:red brown;line-height:1.6;">
								If you didn’t request this, you can safely ignore this email.
							</p>

						</td>
					</tr>

					<!-- Footer -->
					<tr>
						<td style="padding:18px 30px;background:#f0fdf4;color:#6b7280;font-size:12px;text-align:center;">
							<p style="margin:0;">This message was sent to <strong>{{ $email }}</strong></p>
							<p style="margin:6px 0 0;">© {{ date('Y') }} Ehgezly. All rights reserved.</p>
						</td>
					</tr>

				</table>

			</td>
		</tr>
	</table>

</body>
</html>
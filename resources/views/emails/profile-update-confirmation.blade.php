<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Update Confirmation</title>
    <style>
        body { margin: 0; padding: 0; background-color: #0F172A; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; padding: 30px 0; }
        .logo { width: 60px; height: 60px; background-color: #2563EB; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; }
        .logo span { color: #ffffff; font-size: 24px; font-weight: bold; }
        .card { background-color: #1E293B; border-radius: 16px; padding: 40px; border: 1px solid #334155; }
        h1 { color: #F1F5F9; font-size: 24px; margin: 0 0 20px 0; text-align: center; }
        .icon { text-align: center; margin-bottom: 20px; }
        p { color: #94A3B8; line-height: 1.6; font-size: 15px; margin: 0 0 15px 0; }
        .info-box { background-color: #0F172A; border-radius: 12px; padding: 20px; margin: 25px 0; border: 1px solid #334155; }
        .info-box strong { color: #F1F5F9; display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .info-box span { color: #60A5FA; font-size: 15px; }
        .fields-list { list-style: none; padding: 0; margin: 0; }
        .fields-list li { padding: 8px 12px; border-left: 3px solid #2563EB; margin-bottom: 8px; color: #94A3B8; font-size: 14px; background-color: #0F172A; border-radius: 0 8px 8px 0; }
        .footer { text-align: center; padding: 30px 0; color: #64748B; font-size: 12px; }
        @media only screen and (max-width: 480px) {
            .card { padding: 24px; }
            h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><span>EC</span></div>
        </div>
        <div class="card">
            <div class="icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#60A5FA" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1>Profile Updated</h1>
            <p>Hello <strong style="color:#F1F5F9;">{{ $user->resident?->full_name ?? $user->email }}</strong>,</p>
            <p>Your profile information has been successfully updated on {{ $updateDateTime }}.</p>

            <div class="info-box">
                <strong>Modified Fields</strong>
                @if(!empty($changedFields))
                    <ul class="fields-list">
                        @foreach($changedFields as $field)
                            <li>{{ $field }}</li>
                        @endforeach
                    </ul>
                @else
                    <span style="color:#94A3B8;">General profile information</span>
                @endif
            </div>

            <p style="text-align:center; margin-top:25px;">Thank you,<br><strong style="color:#F1F5F9;">E-CiudAgad Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} E-CiudAgad. Barangay Online Document Request System.<br>
            Barangay Ciudad de Strike, Bacoor City, Cavite, Philippines</p>
        </div>
    </div>
</body>
</html>
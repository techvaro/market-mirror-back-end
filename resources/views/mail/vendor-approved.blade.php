<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Approved</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 24px; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
        .header { background: #0f172a; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 32px; color: #1f2937; line-height: 1.6; }
        .btn { display: inline-block; margin-top: 16px; padding: 12px 24px; background: #16a34a; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; }
        .muted { color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Welcome to Market Mirror {{ $vendorName }}!</h1>
        </div>
        <div class="body">
            <p>Great news&nbsp;&mdash; your vendor application has been <strong>approved</strong>.</p>

            @if ($shopName)
                <p>Your shop <strong>{{ $shopName }}</strong> is now live on the marketplace.</p>
            @endif

            <p>You can now sign in to your vendor dashboard and start selling.</p>

            <a href="{{ url('/') }}" class="btn">Go to Vendor Dashboard</a>

            <p class="muted" style="margin-top:24px;">If you have any questions, reply to this email or contact our support team.</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Successful') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #fbfbfb);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .payment-success-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            text-align: center;
        }

        .card {
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            border: none;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(46, 125, 50, 0.12);
            animation: fadeInUp 0.6s ease-in-out;
        }

        .success-icon-wrap {
            width: 90px;
            height: 90px;
            background: #e8f8ed;
            color: #2e7d32;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.2);
        }

        .success-icon-wrap svg {
            width: 48px;
            height: 48px;
        }

        .order-badge {
            background-color: #f1f8e9;
            color: #2e7d32;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            display: inline-block;
            margin: 16px 0;
        }

        .btn-custom {
            background-color: #0b8043;
            color: #ffffff;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-custom:hover {
            background-color: #086333;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .countdown {
            font-size: 15px;
            color: #666;
            margin-top: 20px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="payment-success-page">
        <div class="card">
            <div class="success-icon-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                </svg>
            </div>

            <h3 class="text-success font-weight-bold mb-2">{{ __('Payment Successful!') }}</h3>
            <p class="text-muted mb-3">{{ __('Thank you for your payment. Your order has been placed and is being processed.') }}</p>

            @if(isset($payment))
                <div class="order-badge">
                    <span>{{ __('Payment Reference') }}: #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                    @if($payment->amount > 0)
                        <span class="d-block mt-1">{{ number_format($payment->amount, 2) }} {{ $payment->currency ?? 'SAR' }}</span>
                    @endif
                </div>
            @endif

            <div class="mt-4 d-flex justify-content-center gap-2">
                <a href="/order-history" class="btn-custom">{{ __('View Orders') }}</a>
                <a href="/" class="btn btn-outline-secondary rounded-3 px-4 py-2">{{ __('Home') }}</a>
            </div>

            <p class="countdown">
                {{ __('Redirecting automatically in') }} <span id="countdown" class="fw-bold text-success">4</span> {{ __('seconds') }}...
            </p>
        </div>
    </div>

    <script>
        // If opened as popup, notify parent and close
        if (window.opener && !window.opener.closed) {
            try {
                if (window.opener.basketStore) {
                    window.opener.basketStore.showOrderConfirmModal = true;
                }
            } catch(e) {}
            setTimeout(() => {
                window.close();
            }, 2500);
        }

        // Countdown timer for automatic redirect
        let countdownElement = document.getElementById('countdown');
        let countdownValue = 4;

        const interval = setInterval(() => {
            countdownValue--;
            if (countdownElement) {
                countdownElement.textContent = countdownValue;
            }

            if (countdownValue <= 0) {
                clearInterval(interval);
                if (window.opener && !window.opener.closed) {
                    window.close();
                } else {
                    window.location.href = '/order-history';
                }
            }
        }, 1000);
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Failed') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: linear-gradient(135deg, #fdeded, #fbfbfb);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .payment-failed-page {
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
            box-shadow: 0 15px 35px rgba(211, 47, 47, 0.12);
            animation: fadeInUp 0.6s ease-in-out;
        }

        .fail-icon-wrap {
            width: 90px;
            height: 90px;
            background: #fde8e8;
            color: #d32f2f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 20px rgba(211, 47, 47, 0.2);
        }

        .fail-icon-wrap svg {
            width: 48px;
            height: 48px;
        }

        .highlight-error {
            background-color: #fdf2f2;
            color: #b71c1c;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            margin: 16px 0;
            border: 1px solid #fecaca;
            word-break: break-word;
        }

        .btn-custom-danger {
            background-color: #d32f2f;
            color: #ffffff;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-custom-danger:hover {
            background-color: #b71c1c;
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
    <div class="payment-failed-page">
        <div class="card">
            <div class="fail-icon-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <h3 class="text-danger font-weight-bold mb-2">{{ __('Payment Failed') }}</h3>
            <p class="text-muted mb-3">{{ __('We were unable to process your payment. You can try again or select another payment method.') }}</p>

            @if(isset($request) && $request->error)
                <div class="highlight-error">
                    {{ $request->error }}
                </div>
            @endif

            <div class="mt-4 d-flex justify-content-center gap-2">
                <a href="/checkout" class="btn-custom-danger">{{ __('Try Again') }}</a>
                <a href="/" class="btn btn-outline-secondary rounded-3 px-4 py-2">{{ __('Home') }}</a>
            </div>

            <p class="countdown">
                {{ __('Redirecting automatically in') }} <span id="countdown" class="fw-bold text-danger">5</span> {{ __('seconds') }}...
            </p>
        </div>
    </div>

    <script>
        if (window.opener && !window.opener.closed) {
            try {
                if (window.opener.basketStore) {
                    window.opener.basketStore.orderPaymentCancelModal = true;
                }
            } catch(e) {}
            setTimeout(() => {
                window.close();
            }, 3000);
        }

        let countdownElement = document.getElementById('countdown');
        let countdownValue = 5;

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
                    window.location.href = '/checkout';
                }
            }
        }, 1000);
    </script>
</body>
</html>

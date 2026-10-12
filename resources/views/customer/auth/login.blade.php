<!doctype html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&amp;display=swap" rel="stylesheet">
    <!-- App favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" />

    <!-- App title -->
    <title>{{ $generaleSetting?->title ?? config('app.name', 'Laravel') }} تسجيل الدخول</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<style>
    body {
        font-family: "Almarai", serif;
    }

    .logo_right img {
        height: 90px;
    }

    .logo_right {
        position: absolute;
        top: 15%;
        right: 50%;
        transform: translateX(47%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0;
    }

    .logo_right .site_name {
        font-size: 26px;
        font-weight: 900;
        color: #848589;
        text-transform: capitalize;
        text-align: center;
        letter-spacing: 0.5px;
        font-family: 'Poppins', sans-serif;
    }
    .eye {
        right: unset;
        left: 10px;
    }
</style>

<body style="background: url({{ asset('assets/images/admin-bg.svg') }})">

    <section class="login-section">
        <div class="thumbnail position-relative text-primary">
            <img src="{{ asset('assets/images/login-f.png') }}" alt="thumbnail" width="100%" loading="lazy" />
            <div class="logo_right">
                <img src="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" alt="logo"
                    loading="lazy" />
                <span class="site_name">{{ $generaleSetting?->name ?? config('app.name') }}</span>
            </div>
        </div>

        <!-- Login Card -->
        <div class="card loginCard">
            <div class="card-body" style="direction: rtl;">
                
                <div class="text-center mt-4">
                    <img src="{{ $generaleSetting?->logo ?? asset('assets/logo.png') }}" alt="" height="80" style="max-width: 100%">
                </div>

                <div class="page-content text-center mb-4">
                    <p class="pagePera my-3">
                        مرحبا بكم في
                        <span class="fw-bold text-primary text-capitalize">
                            {{ $generaleSetting?->name ?? config('app.name', 'Laravel') }}
                        </span>
                    </p>
                </div>

                <hr>

                <form action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email">البريد الالكتروني</label>
                        <input type="email" value="{{ old('email') }}" name="phone" id="email"
                            class="form-control" placeholder="Enter Email Address">

                        @error('phone')
                            <span class="text text-danger" role="alert">
                                <span>{{ $message }}</span>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password">كلمة المرور</label>
                        <div class="position-relative passwordInput">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Enter Password">
                            <span class="eye" onclick="showHidePassword()">
                                <i class="fa fa-eye-slash" id="togglePassword"></i>
                            </span>
                        </div>
                        @error('password')
                            <span class="text text-danger" role="alert">
                                <span>{{ $message }}</span>
                            </span>
                        @enderror
                    </div>

                    <button class="btn loginButton" type="submit">تسجيل الدخول</button>
                </form>
            </div>
        </div>
    </section>
    <script src="{{ asset('assets/scripts/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var themeColor = "{{ $generaleSetting?->primary_color ?? '#EE456B' }}";
            document.documentElement.style.setProperty('--theme_color', themeColor);
        });

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        function showHidePassword() {
            const toggle = document.getElementById("togglePassword");
            const password = document.getElementById("password");

            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);
            // toggle the icon
            toggle.classList.toggle("fa-eye");
            toggle.classList.toggle("fa-eye-slash");
        }

    </script>

</body>

</html>

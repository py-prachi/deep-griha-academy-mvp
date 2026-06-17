<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ── Left panel: photo ── */
        .login-photo {
            flex: 0 0 50%;
            background: url('{{ asset('images/login-bg.jpg') }}') center center / cover no-repeat;
            position: relative;
        }
        .login-photo-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(27,58,107,0.72) 0%, rgba(27,58,107,0.45) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 48px;
        }
        .login-photo-tagline {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.3;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }
        .login-photo-tagline span {
            display: block;
            font-size: 1rem;
            font-weight: 400;
            opacity: 0.85;
            margin-top: 6px;
        }

        /* ── Right panel: form ── */
        .login-form-panel {
            flex: 0 0 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            padding: 48px 40px;
        }
        .login-form-inner {
            width: 100%;
            max-width: 380px;
        }

        .login-logo {
            width: 180px;
            height: 180px;
            object-fit: contain;
            display: block;
            margin: 0 auto 16px;
        }

        .login-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1B3A6B;
            line-height: 1.2;
            text-align: center;
        }
        .login-subtitle {
            font-size: 0.92rem;
            color: #6c757d;
            margin-top: 5px;
            margin-bottom: 28px;
            text-align: center;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #344055;
            margin-bottom: 4px;
        }
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #dee2e6;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #1B3A6B;
            box-shadow: 0 0 0 3px rgba(27,58,107,0.12);
        }

        .btn-login {
            background-color: #1B3A6B;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-size: 0.95rem;
            font-weight: 600;
            width: 100%;
            letter-spacing: 0.3px;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background-color: #14305a;
            color: #fff;
        }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 0.83rem;
            color: #1B3A6B;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        .form-check-input:checked {
            background-color: #1B3A6B;
            border-color: #1B3A6B;
        }

        /* ── Responsive: stack on mobile ── */
        @media (max-width: 768px) {
            .login-wrapper { flex-direction: column; }
            .login-photo {
                flex: 0 0 220px;
                min-height: 220px;
            }
            .login-photo-overlay { padding: 24px; }
            .login-photo-tagline { font-size: 1.2rem; }
            .login-form-panel {
                flex: 1;
                padding: 36px 24px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    {{-- ── Left: photo panel ── --}}
    <div class="login-photo">
        <div class="login-photo-overlay">
            <div class="login-photo-tagline">
                Deep Griha Academy
                <span>In Teaching We Learn</span>
            </div>
        </div>
    </div>

    {{-- ── Right: form panel ── --}}
    <div class="login-form-panel">
        <div class="login-form-inner">

            <img src="{{ asset('images/dgs-logo.png') }}" alt="DGS Logo" class="login-logo">

            <div class="login-title">Deep Griha Academy</div>
            <div class="login-subtitle">School Management Portal</div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}"
                           required autocomplete="email" autofocus
                           placeholder="you@deepgriha.org">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password"
                           required autocomplete="current-password"
                           placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember" style="font-size:0.85rem; color:#555;">
                        Keep me signed in
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    Sign In
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>

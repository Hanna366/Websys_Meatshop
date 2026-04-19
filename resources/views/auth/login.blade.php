<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        use App\Helpers\LogoHelper;
        $tenant = $tenant ?? null;
        $businessName = $tenant ? $tenant->business_name : 'MeatShop POS';
        $logoUrl = LogoHelper::getTenantLogo($tenant);
    @endphp

    <title>Sign In - {{ $businessName }}</title>

    @if (($showRecaptcha ?? false) && config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-1: #060202;
            --bg-2: #1a0808;
            --card-border: rgba(255, 255, 255, 0.14);
            --text: #fef7f5;
            --muted: #d5b8b1;
            --input-border: rgba(255, 255, 255, 0.28);
            --rose-300: #f9a8d4;
            --rose-200: #fecdd3;
            --rose-100: #ffe4e6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 18% -10%, rgba(246, 52, 112, 0.28), transparent 38%),
                radial-gradient(circle at 92% 10%, rgba(255, 140, 87, 0.2), transparent 32%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 50%, #2f0b12);
        }

        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            border-radius: 1.5rem;
            border: 1px solid var(--card-border);
            background: rgba(8, 2, 2, 0.74);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(8px);
            padding: 1.5rem;
        }

        .auth-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .logo-wrap {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 14px 26px rgba(246, 52, 112, 0.22);
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .title {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .subtitle {
            margin: 0.35rem 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .alert {
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            border: 1px solid;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .alert-error {
            border-color: rgba(251, 113, 133, 0.45);
            background: rgba(244, 63, 94, 0.1);
            color: #fecdd3;
        }

        .alert-success {
            border-color: rgba(52, 211, 153, 0.45);
            background: rgba(16, 185, 129, 0.1);
            color: #a7f3d0;
        }

        .errors-list {
            margin: 0;
            padding-left: 1.25rem;
        }

        .form-stack {
            display: grid;
            gap: 1rem;
        }

        .field-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #f7dbd4;
            font-size: 0.92rem;
            font-weight: 500;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #c9a29a;
            pointer-events: none;
        }

        .input {
            width: 100%;
            height: 44px;
            border-radius: 0.6rem;
            border: 1px solid var(--input-border);
            background: rgba(255, 255, 255, 0.05);
            color: #ffe4e6;
            font-size: 0.95rem;
            padding: 0 0.8rem 0 2.3rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input::placeholder {
            color: rgba(254, 205, 211, 0.6);
        }

        .input:focus {
            border-color: #fb7185;
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.25);
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: rgba(255, 228, 230, 0.82);
        }

        .remember input {
            margin: 0;
            width: 16px;
            height: 16px;
        }

        .link {
            color: var(--rose-300);
            text-decoration: none;
            font-weight: 500;
        }

        .link:hover {
            color: var(--rose-200);
        }

        .recaptcha-wrap {
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.75rem;
        }

        .btn {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 0.6rem;
            background: linear-gradient(90deg, #9f1239, #f43f5e);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(246, 52, 112, 0.34);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn svg {
            width: 16px;
            height: 16px;
        }

        .auth-footer {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.92rem;
        }

        .auth-footer a {
            color: rgba(255, 228, 230, 0.78);
            text-decoration: none;
        }

        .auth-footer a:hover {
            color: var(--rose-100);
        }

        .or-row {
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .or-row span {
            color: rgba(255, 228, 230, 0.58);
            font-size: 0.74rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .or-row::before,
        .or-row::after {
            content: '';
            height: 1px;
            flex: 1;
            background: rgba(255, 255, 255, 0.15);
        }

        .google-btn {
            width: 100%;
            height: 44px;
            border-radius: 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.04);
            color: var(--rose-100);
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .google-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(249, 168, 212, 0.55);
        }

        @media (min-width: 640px) {
            .auth-shell {
                padding: 1.5rem;
            }

            .auth-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-header">
                <div class="logo-wrap">
                    <img src="{{ $logoUrl }}" alt="{{ $businessName }} Logo">
                </div>
                <h1 class="title">{{ $businessName }}</h1>
                <p class="subtitle">Sign in to manage your meat shop</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul class="errors-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="tenantLoginForm" method="POST" action="/login" class="form-stack">
                @csrf

                <div>
                    <label for="email" class="field-label">Email Address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v16H4z" fill="none" opacity="0"></path><path d="m4 8 8 5 8-5"></path><rect x="3" y="5" width="18" height="14" rx="2"></rect></svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@meatshop.com"
                            class="input"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="field-label">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path></svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            class="input"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <label for="remember" class="remember">
                        <input id="remember" name="remember" type="checkbox" value="1">
                        Remember me
                    </label>
                    <a href="/forgot-password" class="link">Forgot password?</a>
                </div>

                @if (($showRecaptcha ?? false) && config('services.recaptcha.site_key'))
                    <div class="recaptcha-wrap">
                        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                    </div>
                @endif

                <button id="signInButton" type="submit" class="btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 5v14"></path></svg>
                    <span id="signInLabel">Sign In</span>
                </button>
            </form>

            <div class="auth-footer">
                <a href="/">Back to homepage</a>
                @php
                    $centralDomains = (array) config('tenancy.central_domains', []);
                    $centralHost = count($centralDomains) ? $centralDomains[0] : request()->getHost();
                    $scheme = request()->getScheme() ?: 'https';
                    $centralLogin = $scheme . '://' . $centralHost . '/login?force_login=1';
                @endphp
                @if(isset($tenant) || ! in_array(strtolower((string) request()->getHost()), array_map('strtolower', $centralDomains ?? []), true))
                    <div style="margin-top:0.5rem;font-size:0.9rem;color:var(--muted);">
                        Central administrators should sign in on the central site:
                        <a href="{{ $centralLogin }}" class="link">Sign in to Central</a>
                    </div>
                @endif
            </div>

            @if (Route::has('google.redirect'))
                <div class="or-row"><span>or</span></div>

                <a href="{{ route('google.redirect') }}" class="google-btn">Sign in with Google</a>
            @endif
        </section>
    </main>

    <script>
        const loginForm = document.getElementById('tenantLoginForm');
        const signInButton = document.getElementById('signInButton');
        const signInLabel = document.getElementById('signInLabel');

        loginForm?.addEventListener('submit', function () {
            signInButton.disabled = true;
            signInLabel.textContent = 'Signing In...';
        });
    </script>
</body>
</html>

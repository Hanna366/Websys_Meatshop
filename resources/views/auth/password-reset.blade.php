<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Meat Shop POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    @env('local')
        <script src="https://cdn.tailwindcss.com"></script>
    @endenv
    <style>
        :root {
            --bg-1: #060202;
            --bg-2: #1a0808;
            --card-border: rgba(255, 255, 255, 0.14);
            --text: #fef7f5;
            --muted: #d5b8b1;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background:
                radial-gradient(circle at 18% -10%, rgba(246, 52, 112, 0.28), transparent 38%),
                radial-gradient(circle at 92% 10%, rgba(255, 140, 87, 0.2), transparent 32%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 50%, #2f0b12);
        }

        .heading-font {
            font-family: 'Sora', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen text-[color:var(--text)] antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center p-4 sm:p-6">
        <section class="w-full max-w-md rounded-3xl border p-6 shadow-2xl backdrop-blur sm:p-8" style="border-color: var(--card-border); background: rgba(8, 2, 2, 0.74); box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);">
            <div class="mb-6 text-center">
                <h1 class="heading-font text-2xl font-semibold" style="color: var(--text);">Set New Password</h1>
                <p class="mt-1 text-sm" style="color: var(--muted);">Create a secure password for your account.</p>
            </div>

            @php($pageError = $error ?? session('error'))

            @if (!empty($pageError))
                <div class="mb-4 rounded-xl border border-rose-400/45 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ $pageError }}</div>
            @endif

            @if ($token)
                <form method="POST" action="{{ route('password.reset.update') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium" style="color: #f7dbd4;">New Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="h-11 w-full rounded-lg border bg-white/5 px-3 text-sm text-rose-50 outline-none transition placeholder:text-rose-200/55 focus:border-rose-400 focus:ring-2 focus:ring-rose-500/35"
                            placeholder="Enter new password"
                            style="border-color: rgba(255, 255, 255, 0.28);"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium" style="color: #f7dbd4;">Confirm Password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="h-11 w-full rounded-lg border bg-white/5 px-3 text-sm text-rose-50 outline-none transition placeholder:text-rose-200/55 focus:border-rose-400 focus:ring-2 focus:ring-rose-500/35"
                            placeholder="Confirm new password"
                            style="border-color: rgba(255, 255, 255, 0.28);"
                        >
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-400/45 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="rounded-xl border border-white/15 bg-white/[0.03] px-4 py-3 text-sm text-rose-100/90">
                        Password should be at least 8 characters and include mixed character types.
                    </div>

                    <button
                        type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-gradient-to-r from-rose-800 to-rose-500 px-4 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 focus:ring-offset-rose-950/40"
                        style="box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28);"
                    >
                        Reset Password
                    </button>
                </form>
            @else
                <div class="text-center">
                    <div class="mb-6 rounded-xl border border-amber-400/45 bg-amber-500/10 px-4 py-4">
                        <h3 class="mb-2 text-lg font-semibold text-amber-200">Invalid Reset Link</h3>
                        <p class="text-sm text-amber-100/90">This password reset link is invalid or has expired.</p>
                    </div>

                    <div class="space-y-3">
                        <button
                            onclick="history.back()"
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-white/20 bg-white/[0.04] px-4 text-sm font-semibold text-rose-100 transition hover:bg-white/[0.1]"
                        >
                            Go Back
                        </button>

                        <a href="{{ route('password.reset.request') }}" class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-gradient-to-r from-rose-800 to-rose-500 px-4 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:shadow-lg" style="box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28);">
                            Request New Reset Link
                        </a>
                    </div>
                </div>
            @endif

            <div class="mt-4 text-center text-sm">
                <a href="{{ route('login') }}" class="text-rose-100/75 transition hover:text-rose-100">Back to Login</a>
            </div>
        </section>
    </main>
</body>
</html>

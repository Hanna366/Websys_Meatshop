<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Meat Shop POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
                <h1 class="heading-font text-2xl font-semibold" style="color: var(--text);">Reset Your Password</h1>
                <p class="mt-1 text-sm" style="color: var(--muted);">Enter your email address and we will send a reset link.</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-xl border border-emerald-400/45 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-400/45 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.reset.send') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium" style="color: #f7dbd4;">Email Address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="h-11 w-full rounded-lg border bg-white/5 px-3 text-sm text-rose-50 outline-none transition placeholder:text-rose-200/55 focus:border-rose-400 focus:ring-2 focus:ring-rose-500/35"
                        placeholder="you@meatshop.com"
                        style="border-color: rgba(255, 255, 255, 0.28);"
                        value="{{ old('email') }}"
                    >
                </div>

                <div class="rounded-xl border border-white/15 bg-white/[0.03] px-4 py-3 text-sm text-rose-100/90">
                    Enter your email address and we will send you a password reset link.
                </div>

                <button
                    type="submit"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-gradient-to-r from-rose-800 to-rose-500 px-4 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 focus:ring-offset-rose-950/40"
                    style="box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28);"
                >
                    Send Reset Link
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                <a href="{{ route('login') }}" class="text-rose-100/75 transition hover:text-rose-100">Back to Login</a>
            </div>
        </section>
    </main>
</body>
</html>

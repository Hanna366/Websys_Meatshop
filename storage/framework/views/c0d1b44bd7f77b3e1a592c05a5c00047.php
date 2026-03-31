<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <?php
        use App\Helpers\LogoHelper;
        $tenant = $tenant ?? null;
        $businessName = $tenant ? $tenant->business_name : 'MeatShop POS';
        $logoUrl = LogoHelper::getTenantLogo($tenant);
    ?>
    
    <title>Sign In - <?php echo e($businessName); ?></title>

    <?php if(($showRecaptcha ?? false) && config('services.recaptcha.site_key')): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at 15% -8%, rgba(30, 64, 175, 0.14) 0, transparent 32%),
                radial-gradient(circle at 100% 0%, rgba(13, 148, 136, 0.12) 0, transparent 28%),
                linear-gradient(150deg, #f8fafc 0%, #eef2ff 45%, #f8fafc 100%);
        }

        .heading-font {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center p-4 sm:p-6">
        <section class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-xl backdrop-blur sm:p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl overflow-hidden shadow-lg">
                    <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($businessName); ?> Logo" class="h-12 w-12">
                </div>
                <h1 class="heading-font text-2xl font-semibold text-slate-900"><?php echo e($businessName); ?></h1>
                <p class="mt-1 text-sm text-slate-500">Sign in to manage your meat shop</p>
            </div>

            <?php if(session('error')): ?>
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('status')): ?>
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc space-y-1 pl-5">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="tenantLoginForm" method="POST" action="/login" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="mail" class="h-4 w-4"></i>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo e(old('email')); ?>"
                            placeholder="you@meatshop.com"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="lock" class="h-4 w-4"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label for="remember" class="inline-flex items-center gap-2 text-slate-600">
                        <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Remember me
                    </label>
                    <a href="/forgot-password" class="font-medium text-indigo-600 transition hover:text-indigo-700">Forgot password?</a>
                </div>

                <?php if(($showRecaptcha ?? false) && config('services.recaptcha.site_key')): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="g-recaptcha mx-auto" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                    </div>
                <?php endif; ?>

                <button
                    id="signInButton"
                    type="submit"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-700 to-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <i data-lucide="log-in" class="h-4 w-4"></i>
                    <span id="signInLabel">Sign In</span>
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                <a href="/" class="text-slate-500 transition hover:text-slate-700">Back to homepage</a>
            </div>

            <?php if(Route::has('google.redirect')): ?>
                <div class="my-4 flex items-center gap-3">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span class="text-xs uppercase tracking-wide text-slate-400">or</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>

                <a href="<?php echo e(route('google.redirect')); ?>" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <i data-lucide="circle" class="h-3.5 w-3.5 text-rose-500"></i>
                    Sign in with Google
                </a>
            <?php endif; ?>
        </section>
    </main>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

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
<?php /**PATH C:\Users\Rusty\Music\Websys_Meatshop\resources\views/auth/login.blade.php ENDPATH**/ ?>
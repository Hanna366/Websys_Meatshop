<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Create Your MeatShop Account</title>

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
                radial-gradient(circle at 8% -5%, rgba(30, 58, 138, 0.12) 0, transparent 30%),
                radial-gradient(circle at 100% 0%, rgba(13, 148, 136, 0.12) 0, transparent 26%),
                #f8fafc;
        }

        .heading-font {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased">
    <?php ($selectedPlan = old('plan', request('plan', 'basic'))); ?>

    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center p-4 sm:p-6 lg:p-8">
        <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="hidden rounded-2xl border border-slate-200/70 bg-white/80 p-8 shadow-sm backdrop-blur lg:block">
                <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                    <i data-lucide="store" class="h-6 w-6"></i>
                </div>
                <h1 class="heading-font text-3xl font-semibold leading-tight text-slate-900">Create Your MeatShop Account</h1>
                <p class="mt-2 text-sm text-slate-600">Set up your business and start managing your shop with centralized SaaS controls.</p>

                <div class="mt-8 space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </span>
                        <p>Provision tenant database and domain automatically.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </span>
                        <p>Assign initial plan and activate onboarding in minutes.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </span>
                        <p>Use central controls for lifecycle, billing, and notifications.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-md sm:p-8">
                <div class="mb-6 text-center lg:text-left">
                    <div class="mx-auto mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 lg:mx-0">
                        <i data-lucide="building-2" class="h-5 w-5"></i>
                    </div>
                    <h2 class="heading-font text-2xl font-semibold text-slate-900">Create Your MeatShop Account</h2>
                    <p class="mt-1 text-sm text-slate-500">Set up your business and start managing your shop.</p>
                </div>

                <?php if($errors->any()): ?>
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc space-y-1 pl-5">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="tenantSignupForm" action="<?php echo e(route('tenants.store')); ?>" method="POST" class="space-y-6">
                    <?php echo csrf_field(); ?>

                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Personal Info</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                                <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" placeholder="Juan Dela Cruz" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <p class="mt-1 text-xs text-slate-500">Primary contact for this onboarding request.</p>
                            </div>
                            <div>
                                <label for="business_email" class="mb-1 block text-sm font-medium text-slate-700">Business Email</label>
                                <input type="email" id="business_email" name="business_email" value="<?php echo e(old('business_email', old('email'))); ?>" placeholder="owner@yourshop.com" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['business_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Business Info</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="business_name" class="mb-1 block text-sm font-medium text-slate-700">Business Name</label>
                                <input type="text" id="business_name" name="business_name" value="<?php echo e(old('business_name')); ?>" placeholder="MeatShop Downtown" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['business_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="business_phone" class="mb-1 block text-sm font-medium text-slate-700">Business Phone</label>
                                    <input type="text" id="business_phone" name="business_phone" value="<?php echo e(old('business_phone')); ?>" placeholder="0917 000 0000" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['business_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                </div>
                                <div>
                                    <label for="domain" class="mb-1 block text-sm font-medium text-slate-700">Domain / Subdomain</label>
                                    <input type="text" id="domain" name="domain" placeholder="ramcar.localhost" value="<?php echo e(old('domain')); ?>" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['domain'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <p class="mt-1 text-xs text-slate-500">Auto-generated from business name. You can still edit it manually.</p>
                                </div>
                            </div>
                            <div>
                                <label for="business_address" class="mb-1 block text-sm font-medium text-slate-700">Business Address</label>
                                <input type="text" id="business_address" name="business_address" value="<?php echo e(old('business_address')); ?>" placeholder="Street, Barangay, City" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['business_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Admin Account</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="admin_name" class="mb-1 block text-sm font-medium text-slate-700">Administrator Name</label>
                                <input type="text" id="admin_name" name="admin_name" value="<?php echo e(old('admin_name', old('name'))); ?>" placeholder="Store Administrator" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['admin_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            </div>
                            <div>
                                <label for="admin_email" class="mb-1 block text-sm font-medium text-slate-700">Administrator Email</label>
                                <input type="email" id="admin_email" name="admin_email" value="<?php echo e(old('admin_email', old('business_email', old('email')))); ?>" placeholder="admin@yourshop.com" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['admin_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            </div>
                            <div>
                                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                                  <input type="password" id="password" name="password" placeholder="Leave blank to auto-generate" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                  <p class="mt-1 text-xs text-slate-500">Optional: leave blank to auto-generate a secure password and send it via email.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Initial Plan</h3>
                        <select id="plan" name="plan" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 <?php $__errorArgs = ['plan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-300 ring-rose-200 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <option value="basic" <?php echo e($selectedPlan === 'basic' ? 'selected' : ''); ?>>Basic</option>
                            <option value="standard" <?php echo e($selectedPlan === 'standard' ? 'selected' : ''); ?>>Standard</option>
                            <option value="premium" <?php echo e($selectedPlan === 'premium' ? 'selected' : ''); ?>>Premium</option>
                            <option value="enterprise" <?php echo e($selectedPlan === 'enterprise' ? 'selected' : ''); ?>>Enterprise</option>
                        </select>
                    </div>

                    <?php if(($showRecaptcha ?? false) && config('services.recaptcha.site_key')): ?>
                        <div class="flex justify-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                        </div>
                    <?php endif; ?>

                    <button id="submitButton" type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-700 to-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                        <i data-lucide="user-plus" class="h-4 w-4"></i>
                        <span id="submitLabel">Create Tenant</span>
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        const signupForm = document.getElementById('tenantSignupForm');
        const submitButton = document.getElementById('submitButton');
        const submitLabel = document.getElementById('submitLabel');
        const businessNameInput = document.getElementById('business_name');
        const domainInput = document.getElementById('domain');
        const domainRoot = <?php echo json_encode(config('tenancy.fallback_domain', 'localhost'), 512) ?>;

        let domainTouched = Boolean(domainInput?.value?.trim());

        function slugify(value) {
            return (value || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function normalizeRoot(value) {
            const root = (value || 'localhost').trim().toLowerCase();
            return root.length > 0 ? root : 'localhost';
        }

        function updateAutoDomain() {
            if (!businessNameInput || !domainInput || domainTouched) {
                return;
            }

            const slug = slugify(businessNameInput.value);
            domainInput.value = slug ? `${slug}.${normalizeRoot(domainRoot)}` : '';
        }

        domainInput?.addEventListener('input', function () {
            domainTouched = this.value.trim().length > 0;
        });

        domainInput?.addEventListener('blur', function () {
            if (this.value.trim() === '') {
                domainTouched = false;
                updateAutoDomain();
            }
        });

        businessNameInput?.addEventListener('input', updateAutoDomain);

        updateAutoDomain();

        signupForm?.addEventListener('submit', function () {
            submitButton.disabled = true;
            submitLabel.textContent = 'Creating Account...';
        });
    </script>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/account/create.blade.php ENDPATH**/ ?>
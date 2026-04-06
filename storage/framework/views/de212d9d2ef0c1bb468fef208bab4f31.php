<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Meat Shop POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-600 rounded-lg mb-4">
                <i class="fas fa-drumstick-bite text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Meat Shop POS</h1>
            <p class="text-gray-600 mt-2">Reset Your Password</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if(session('status')): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo e(session('status')); ?>

                </div>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo e($errors->first()); ?>

                </div>
            </div>
        <?php endif; ?>

        <!-- Password Reset Form -->
        <form method="POST" action="<?php echo e(route('password.reset.send')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    required 
                    autocomplete="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="Enter your email address"
                    value="<?php echo e(old('email')); ?>"
                >
            </div>

            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Enter your email address and we'll send you a password reset link.
                </p>
            </div>

            <button 
                type="submit" 
                class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 font-medium flex items-center justify-center"
            >
                <i class="fas fa-paper-plane mr-2"></i>
                Send Reset Link
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="<?php echo e(route('login')); ?>" class="text-red-600 hover:text-red-500 text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/auth/password-reset-request.blade.php ENDPATH**/ ?>
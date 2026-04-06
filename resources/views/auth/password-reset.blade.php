<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Meat Shop POS</title>
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
            <p class="text-gray-600 mt-2">Set New Password</p>
        </div>

        @php($pageError = $error ?? session('error'))

        <!-- Error Messages -->
        @if (!empty($pageError))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ $pageError }}
                </div>
            </div>
        @endif

        @if ($token)
            <!-- Password Reset Form -->
            <form method="POST" action="{{ route('password.reset.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>New Password
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        autocomplete="new-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Enter new password"
                    >
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Confirm Password
                    </label>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        required 
                        autocomplete="new-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Confirm new password"
                    >
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center text-red-600 mb-1">
                                <i class="fas fa-exclamation-circle mr-2 text-xs"></i>
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Password Requirements:</strong><br>
                        • At least 8 characters long<br>
                        • Include uppercase and lowercase letters<br>
                        • Include numbers and special characters
                    </p>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 font-medium flex items-center justify-center"
                >
                    <i class="fas fa-key mr-2"></i>
                    Reset Password
                </button>
            </form>
        @else
            <!-- Invalid Token Message -->
            <div class="text-center">
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Invalid Reset Link</h3>
                    <p class="text-yellow-700">
                        This password reset link is invalid or has expired.
                    </p>
                </div>

                <div class="space-y-4">
                    <button 
                        onclick="history.back()" 
                        class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg hover:bg-gray-700 transition duration-200 font-medium"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>
                        Go Back
                    </button>

                    <a href="{{ route('password.reset.request') }}" class="block w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 font-medium text-center">
                        <i class="fas fa-redo mr-2"></i>
                        Request New Reset Link
                    </a>
                </div>
            </div>
        @endif

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-red-600 hover:text-red-500 text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>

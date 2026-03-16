<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <?php if(config('services.recaptcha.site_key')): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            position: relative;
        }
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .bg-layer-1 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9) 0%, rgba(102, 126, 234, 0.8) 50%, rgba(118, 75, 162, 0.9) 100%);
            z-index: 3;
        }
        .bg-layer-2 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1553028826-f4803a7c9f6f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.3;
            z-index: 2;
            mix-blend-mode: multiply;
        }
        .bg-layer-3 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.4;
            z-index: 1;
            mix-blend-mode: screen;
        }
        .main-content {
            padding-top: 100px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }
        .form-container h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #333;
        }
        .form-container label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-container input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            background: linear-gradient(135deg, #dc3545 0%, #667eea 50%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .form-container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="background-container">
        <div class="bg-layer-1"></div>
        <div class="bg-layer-2"></div>
        <div class="bg-layer-3"></div>
    </div>
    <div class="main-content">
        <div class="form-container">
            <h1>Create Your Account</h1>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger text-start" role="alert">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('account.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <?php if(config('services.recaptcha.site_key')): ?>
                    <div class="d-flex justify-content-center mb-3">
                        <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                    </div>
                <?php endif; ?>

                <button type="submit">Sign Up</button>
            </form>
        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/account/create.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Meat Shop POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-box {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 500px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            color: white;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
<div class="card-box">
    <h3 class="mb-2">Forgot Password</h3>
    <p class="text-muted mb-4">Enter your email and we will generate a reset link.</p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('reset_link'))
        <div class="alert alert-info">
            <div class="fw-bold mb-2">Development reset link:</div>
            <a href="{{ session('reset_link') }}" class="small">{{ session('reset_link') }}</a>
        </div>
    @endif

    <form method="POST" action="/forgot-password">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label fw-bold">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <button type="submit" class="btn btn-primary-custom">
            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
        </button>
    </form>

    <div class="mt-3 text-center">
        <a href="/login" class="text-decoration-none">Back to Sign In</a>
    </div>
</div>
</body>
</html>

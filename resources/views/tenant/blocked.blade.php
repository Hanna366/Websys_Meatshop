<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Host Disabled</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(160deg, #fdf1f1 0%, #f8f9fc 40%, #eef3ff 100%);
        }

        .blocked-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 18px 32px rgba(65, 33, 33, 0.12);
            overflow: hidden;
        }

        .blocked-head {
            background: linear-gradient(135deg, #a61f1f, #d94848);
            color: #fff;
            padding: 1.2rem 1.5rem;
        }

        .blocked-body {
            padding: 1.5rem;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="blocked-card mx-auto" style="max-width: 760px;">
            <div class="blocked-head d-flex align-items-center">
                <i class="fas fa-shield-alt me-2"></i>
                <h1 class="h5 mb-0">Tenant Host Disabled</h1>
            </div>
            <div class="blocked-body">
                <p class="mb-2">{{ $message ?? 'Please contact your administrator.' }}</p>
                <p class="mb-0"><strong>Tenant:</strong> {{ $tenant->business_name ?? $tenant->tenant_id }}</p>
            </div>
        </div>
    </div>
</body>
</html>

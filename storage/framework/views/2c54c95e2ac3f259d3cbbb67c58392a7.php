<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-navy: #1d2b53;
            --brand-red: #c43a3a;
            --brand-sky: #eaf2ff;
        }

        body {
            background:
                radial-gradient(circle at 8% 10%, #f7d7d7 0%, transparent 30%),
                radial-gradient(circle at 92% 0%, #dce8ff 0%, transparent 40%),
                #f4f6fa;
            min-height: 100vh;
        }

        .topbar {
            background: linear-gradient(90deg, var(--brand-navy), #243b72);
            color: #fff;
            border-bottom: 4px solid var(--brand-red);
        }

        .topbar .badge {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            font-weight: 500;
        }

        .hero-card {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 35px rgba(22, 31, 54, 0.12);
        }

        .hero-head {
            background: linear-gradient(135deg, #233a71 0%, #2f5ea8 60%, #bb3a3a 100%);
            color: #fff;
            padding: 1.5rem;
        }

        .hero-body {
            background: #fff;
            padding: 1.5rem;
        }

        .meta-chip {
            background: var(--brand-sky);
            border-radius: 12px;
            padding: .8rem 1rem;
            border: 1px solid #d7e7ff;
            height: 100%;
        }

        .meta-chip .label {
            color: #5a6882;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .meta-chip .value {
            color: #1a2741;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <header class="topbar py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-store-alt me-2"></i>
                <strong>Tenant Application</strong>
            </div>
            <span class="badge px-3 py-2"><?php echo e($tenant->domain); ?></span>
        </div>
    </header>

    <main class="container py-4 py-md-5">
        <section class="hero-card">
            <div class="hero-head">
                <h1 class="h4 mb-1"><?php echo e($tenant->business_name ?? $tenant->tenant_id); ?></h1>
                <p class="mb-0 opacity-75">Isolated tenant context is active and connected.</p>
            </div>

            <div class="hero-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="meta-chip">
                            <div class="label">Tenant Domain</div>
                            <div class="value"><?php echo e($tenant->domain); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="meta-chip">
                            <div class="label">Current Plan</div>
                            <div class="value"><?php echo e(ucfirst($tenant->plan ?? 'basic')); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="meta-chip">
                            <div class="label">Tenant ID</div>
                            <div class="value"><?php echo e($tenant->tenant_id); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/tenant/home.blade.php ENDPATH**/ ?>
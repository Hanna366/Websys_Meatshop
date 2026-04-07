<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meat Shop POS - Central Management Platform</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-1: #060202;
            --bg-2: #1a0808;
            --card: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 255, 255, 0.14);
            --text: #fef7f5;
            --muted: #d5b8b1;
            --accent: #f63470;
            --accent-2: #a41245;
            --line: rgba(255, 255, 255, 0.12);
            --success: #47d7a0;
            --warning: #ffc75c;
            --danger: #ff6b6b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "DM Sans", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 18% -10%, rgba(246, 52, 112, 0.28), transparent 38%),
                radial-gradient(circle at 92% 10%, rgba(255, 140, 87, 0.2), transparent 32%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 50%, #2f0b12);
            padding: 0;
        }

        .frame {
            width: 100%;
            max-width: none;
            margin: 0;
            min-height: 100vh;
            border-radius: 0;
            border: 1px solid var(--line);
            background: rgba(8, 2, 2, 0.74);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            position: relative;
        }

        .frame::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent 30%);
            pointer-events: none;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 15;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--line);
            background: rgba(13, 2, 4, 0.85);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .brand {
            font-family: "Sora", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .logo-mark {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(160deg, #ff9b8d, var(--accent));
            color: #220106;
            display: grid;
            place-items: center;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 1.75rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #f8d7cf;
            font-size: 0.92rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .nav-links a:hover {
            opacity: 1;
            color: #fff;
        }

        .auth-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.58rem 1.25rem;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: transform 0.2s ease, border-color 0.2s ease, opacity 0.2s ease;
            display: inline-flex;
            gap: 0.45rem;
            align-items: center;
            justify-content: center;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--accent-2), var(--accent));
            box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28);
        }

        .btn-secondary {
            border-color: rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.02);
        }

        .menu-pill {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--line);
            display: grid;
            place-items: center;
            color: #f6d5cf;
            font-weight: 700;
        }

        .hero {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 1rem;
            align-items: stretch;
            padding: 2rem;
        }

        .hero-left {
            padding: 1rem 0.25rem;
            animation: rise 0.6s ease;
        }

        .eyebrow {
            display: inline-block;
            padding: 0.4rem 0.78rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.04);
            color: #ffd9cf;
            letter-spacing: 0.06em;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        h1 {
            margin: 1rem 0;
            font-family: "Sora", sans-serif;
            font-size: clamp(2rem, 5vw, 3.6rem);
            line-height: 1.04;
            letter-spacing: -0.03em;
            max-width: 660px;
        }

        .accent {
            color: #f84f86;
        }

        .lead {
            margin: 0.6rem 0 1.2rem;
            max-width: 560px;
            color: var(--muted);
            font-size: 1.04rem;
            line-height: 1.6;
        }

        .chip-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .chip {
            color: #ffe9e5;
            font-size: 0.98rem;
            font-weight: 600;
            display: inline-flex;
            gap: 0.4rem;
            align-items: center;
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 4px rgba(246, 52, 112, 0.22);
        }

        .search-wrap {
            margin: 1.1rem 0;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.26);
            background: rgba(255, 255, 255, 0.04);
            padding: 0.35rem 0.45rem 0.35rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            max-width: 560px;
        }

        .search-wrap span {
            color: #f4c7bd;
            font-size: 0.96rem;
        }

        .hero-cta {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin: 1.3rem 0;
        }

        .trust-strip {
            margin-top: 1.1rem;
            color: #e6bfb7;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .avatar-stack {
            display: flex;
        }

        .avatar-stack span {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #2a0609;
            margin-left: -6px;
            background: linear-gradient(145deg, #ffa7a2, #e23e72);
            display: inline-block;
        }

        .avatar-stack span:first-child {
            margin-left: 0;
        }

        .hero-right {
            position: relative;
            border-radius: 22px;
            border: none;
            background: radial-gradient(circle at 70% 20%, rgba(170, 30, 45, 0.24), rgba(40, 4, 8, 0.08) 55%, transparent 100%);
            min-height: 560px;
            overflow: hidden;
            padding: 0;
            animation: rise 0.8s ease;
        }

        .hero-image-placeholder {
            height: 100%;
            width: 100%;
            border-radius: 0;
            border: none;
            display: grid;
            place-items: center;
            padding: 0;
            background: transparent;
        }

        .hero-image-stack {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .hero-image-stack img {
            object-fit: contain;
            object-position: right bottom;
        }

        .hero-image-base,
        .hero-image-glow {
            position: absolute;
            right: 0;
            bottom: 16px;
            width: 102%;
            height: auto;
        }

        .hero-image-base {
            z-index: 1;
            filter: drop-shadow(0 22px 30px rgba(0, 0, 0, 0.5));
        }

        .hero-image-glow {
            z-index: 2;
            filter:
                saturate(1.08)
                contrast(1.03)
                drop-shadow(0 0 5px rgba(255, 180, 140, 0.38))
                drop-shadow(0 0 14px rgba(255, 120, 70, 0.36))
                drop-shadow(0 0 26px rgba(255, 88, 30, 0.3));
            mix-blend-mode: normal;
            -webkit-mask-image: linear-gradient(to bottom, #000 0%, #000 52%, transparent 74%);
            mask-image: linear-gradient(to bottom, #000 0%, #000 52%, transparent 74%);
            pointer-events: none;
        }

        .stat-float {
            position: absolute;
            right: 1rem;
            width: min(210px, 44%);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(8px);
            padding: 0.72rem;
        }

        .stat-float h3 {
            margin: 0;
            color: #ffd9cf;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .stat-float p {
            margin: 0.24rem 0;
            font-size: 1.4rem;
            font-weight: 700;
            font-family: "Sora", sans-serif;
        }

        .stat-float span {
            font-size: 0.76rem;
            color: #ffcfca;
        }

        .stat-live { top: 1.2rem; }
        .stat-sales { top: 8.9rem; }
        .stat-expiry { top: 16.6rem; }

        .status-badge {
            position: absolute;
            right: 1.25rem;
            bottom: 1.25rem;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(32, 6, 10, 0.5);
            backdrop-filter: blur(6px);
            display: grid;
            place-items: center;
            font-size: 0.72rem;
            text-align: center;
            color: #ffe5df;
            font-weight: 700;
            z-index: 8;
        }

        .feature-strip {
            padding: 0 2rem 1.1rem;
        }

        .feature-strip h2,
        .pricing-wrap h2 {
            text-align: center;
            margin: 0.25rem 0 1rem;
            font-family: "Sora", sans-serif;
            font-size: 2rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .feature-card {
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--card);
            padding: 1rem 0.8rem;
            text-align: center;
        }

        .icon-slot {
            width: 42px;
            height: 42px;
            margin: 0 auto 0.65rem;
            border-radius: 50%;
            border: 1px dashed rgba(255, 255, 255, 0.34);
            display: grid;
            place-items: center;
            font-size: 0.66rem;
            color: #ffd5cb;
            font-weight: 700;
        }

        .feature-card h3 {
            margin: 0;
            font-size: 0.92rem;
            font-family: "Sora", sans-serif;
        }

        .feature-card p {
            margin: 0.4rem 0 0;
            font-size: 0.79rem;
            color: #e6b9af;
        }

        .pricing-wrap {
            padding: 0.45rem 2rem 2rem;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .price-card {
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem;
        }

        .price-card h3 {
            margin: 0;
            font-size: 1.12rem;
            font-family: "Sora", sans-serif;
        }

        .price {
            margin: 0.22rem 0 0.45rem;
            font-size: 1.68rem;
            font-weight: 800;
        }

        .price span {
            font-size: 0.95rem;
            font-weight: 500;
            color: #efc1b6;
        }

        .price-sub {
            margin: 0;
            font-size: 0.84rem;
            color: #e6b7ad;
        }

        .price-card ul {
            margin: 0.8rem 0 0;
            padding: 0;
            list-style: none;
        }

        .price-card li {
            margin-bottom: 0.4rem;
            color: #ffd8d0;
            font-size: 0.83rem;
        }

        .price-card li::before {
            content: "• ";
            color: #ff729f;
        }

        .highlight {
            border-color: rgba(67, 168, 255, 0.55);
            box-shadow: inset 0 0 0 1px rgba(67, 168, 255, 0.28);
            position: relative;
            background: linear-gradient(160deg, rgba(27, 91, 206, 0.24), rgba(7, 26, 70, 0.22));
        }

        .highlight::before {
            content: "Most Popular";
            position: absolute;
            top: 0.65rem;
            right: 0.75rem;
            font-size: 0.65rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #dff4ff;
            background: rgba(58, 171, 255, 0.25);
            padding: 0.18rem 0.4rem;
            border-radius: 999px;
        }

        .premium {
            background: linear-gradient(170deg, rgba(80, 26, 140, 0.28), rgba(23, 8, 40, 0.24));
            border-color: rgba(191, 143, 255, 0.38);
        }

        .enterprise {
            background: linear-gradient(170deg, rgba(109, 17, 36, 0.28), rgba(46, 8, 16, 0.28));
            border-color: rgba(255, 130, 164, 0.35);
        }

        .cta-band {
            margin: 0 2rem 2rem;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.04);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.7rem;
            flex-wrap: wrap;
        }

        .cta-band h3 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 1.6rem;
        }

        .cta-band p {
            margin: 0.25rem 0 0;
            color: #ecc1b8;
            font-size: 0.94rem;
        }

        .cta-buttons {
            display: flex;
            gap: 0.7rem;
        }

        .footer {
            padding: 1.4rem 2rem 1.6rem;
            border-top: 1px solid var(--line);
            text-align: center;
            color: #e8b9af;
            font-size: 0.88rem;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1150px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-right {
                min-height: 420px;
            }

            .hero-image-stack img {
                right: 0;
                bottom: 12px;
                width: 102%;
                height: auto;
                object-position: right bottom;
            }

            .feature-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .pricing-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 780px) {
            .topbar {
                flex-wrap: wrap;
                justify-content: center;
                padding: 1rem;
            }

            .nav-links {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 1rem;
                padding-top: 0.2rem;
            }

            .hero,
            .feature-strip,
            .pricing-wrap {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .feature-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .cta-band {
                margin-left: 1rem;
                margin-right: 1rem;
            }

            .cta-buttons,
            .hero-cta {
                width: 100%;
            }

            .cta-buttons .btn,
            .hero-cta .btn {
                flex: 1;
            }

            .stat-float {
                width: 52%;
            }
        }
    </style>
</head>
<body>
    <div class="frame">
        <header class="topbar">
            <a href="/central" class="brand">
                <span class="logo-mark">LOGO</span>
                MEATSHOP
            </a>

            <nav class="nav-links">
                <a href="#about">About System</a>
                <a href="#suppliers">Suppliers</a>
                <a href="#products">Products</a>
                <a href="#sales">Sales</a>
                <a href="#dashboard">Dashboard</a>
            </nav>

            <div class="auth-actions">
                <a href="{{ route('tenants.create') }}" class="btn btn-primary">Get Started</a>
                <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                <div class="menu-pill">≡</div>
            </div>
        </header>

        <section class="hero">
            <div class="hero-left">
                <span class="eyebrow">Built for meat shops. Designed for growth.</span>
                <h1>Smarter Meat Shop Management <span class="accent">Starts Here.</span></h1>
                <p class="lead">
                    All-in-one inventory and POS system designed for meat shops - track stock, prevent spoilage, and boost sales in real time.
                </p>

                <div class="chip-row">
                    <span class="chip"><span class="dot"></span> Reduce waste.</span>
                    <span class="chip"><span class="dot"></span> Increase profit.</span>
                    <span class="chip"><span class="dot"></span> Simplify operations.</span>
                </div>

                <div class="search-wrap">
                    <span>Search cuts, manage inventory, or explore features...</span>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Search</a>
                </div>

                <div class="hero-cta">
                    <a href="{{ route('tenants.create') }}" class="btn btn-primary">Start Managing Now</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">See How It Works</a>
                </div>

                <div class="trust-strip">
                    <div class="avatar-stack">
                        <span></span><span></span><span></span>
                    </div>
                    Trusted by meat shop owners for reliable, real-time operations.
                </div>
            </div>

            <div class="hero-right">
                <div class="hero-image-placeholder">
                    <div class="hero-image-stack">
                        <img class="hero-image-base" src="{{ asset('ribs.png') }}" alt="Premium raw rib cut on board">
                        <img class="hero-image-glow" src="{{ asset('ribs.png') }}" alt="">
                    </div>
                </div>

                <article class="stat-float stat-live">
                    <h3>Live Inventory</h3>
                    <p>1,248 kg</p>
                    <span style="color: var(--success);">+12% from yesterday</span>
                </article>

                <article class="stat-float stat-sales">
                    <h3>Today's Sales</h3>
                    <p>$3,742</p>
                    <span style="color: var(--warning);">+8.5% from yesterday</span>
                </article>

                <article class="stat-float stat-expiry">
                    <h3>Expiring Soon</h3>
                    <p>12 items</p>
                    <span style="color: var(--danger);">View details</span>
                </article>

                <div class="status-badge">Works<br>Offline</div>
            </div>
        </section>

        <section class="feature-strip">
            <h2>Why Choose Our System?</h2>
            <div class="feature-grid">
                <article class="feature-card" id="about">
                    <div class="icon-slot">ICON</div>
                    <h3>Real-Time Inventory Tracking</h3>
                    <p>Never run out or overstock again.</p>
                </article>
                <article class="feature-card" id="suppliers">
                    <div class="icon-slot">ICON</div>
                    <h3>Weight-Based POS System</h3>
                    <p>Accurate pricing for every cut.</p>
                </article>
                <article class="feature-card" id="products">
                    <div class="icon-slot">ICON</div>
                    <h3>Sales and Reports</h3>
                    <p>Know what sells best instantly.</p>
                </article>
                <article class="feature-card" id="sales">
                    <div class="icon-slot">ICON</div>
                    <h3>Works Even Offline</h3>
                    <p>Keep selling even without internet.</p>
                </article>
                <article class="feature-card" id="dashboard">
                    <div class="icon-slot">ICON</div>
                    <h3>Smart Expiry Monitoring</h3>
                    <p>Reduce spoilage and losses.</p>
                </article>
            </div>
        </section>

        <section class="pricing-wrap">
            <h2>Flexible Plans for Every Shop</h2>
            <div class="pricing-grid">
                <article class="price-card">
                    <h3>Basic Plan</h3>
                    <div class="price">$29 <span>/ month</span></div>
                    <p class="price-sub">Great for small shops</p>
                    <ul>
                        <li>Up to 100 products</li>
                        <li>Inventory tracking and stock alerts</li>
                        <li>Single user access</li>
                        <li>No data export</li>
                    </ul>
                </article>

                <article class="price-card highlight">
                    <h3>Standard Plan</h3>
                    <div class="price">$79 <span>/ month</span></div>
                    <p class="price-sub">Best for growing businesses</p>
                    <ul>
                        <li>Unlimited products</li>
                        <li>Full POS system</li>
                        <li>Supplier and customer management</li>
                        <li>Basic reporting and CSV export</li>
                    </ul>
                </article>

                <article class="price-card premium">
                    <h3>Premium Plan</h3>
                    <div class="price">$149 <span>/ month</span></div>
                    <p class="price-sub">For advanced operations</p>
                    <ul>
                        <li>Advanced analytics dashboard</li>
                        <li>API access</li>
                        <li>Batch operations</li>
                        <li>Custom branding and priority support</li>
                    </ul>
                </article>

                <article class="price-card enterprise">
                    <h3>Enterprise Plan</h3>
                    <div class="price">Custom <span>pricing</span></div>
                    <p class="price-sub">Built for large operations</p>
                    <ul>
                        <li>Dedicated database</li>
                        <li>Custom integrations</li>
                        <li>On-premise deployment option</li>
                        <li>Advanced compliance tools</li>
                    </ul>
                </article>
            </div>
        </section>

        <section class="cta-band">
            <div>
                <h3>Start Transforming Your Meat Shop Today.</h3>
                <p>Join meat shop owners who are increasing profits and reducing waste.</p>
            </div>
            <div class="cta-buttons">
                <a href="{{ route('tenants.create') }}" class="btn btn-primary">Start Free Trial</a>
                <a href="{{ route('login') }}" class="btn btn-secondary">Compare Plans</a>
            </div>
        </section>

        <footer class="footer">
            <p>© 2026 Meat Shop POS Central. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

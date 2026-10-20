<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$fullName = isset($_SESSION['full_name']) ? trim($_SESSION['full_name']) : '';
$firstName = $fullName !== '' ? explode(' ', $fullName)[0] : 'there';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loading...</title>
    <noscript><meta http-equiv="refresh" content="3;url=index.php"></noscript>
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', 'Inter', system-ui, -apple-system, sans-serif;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: "";
            position: absolute; inset: 0;
            background:
                radial-gradient(circle at 50% 42%, rgba(47,111,237,0.35), transparent 55%),
                radial-gradient(circle at 85% 90%, rgba(18,70,127,0.5), transparent 45%);
            animation: breathe 4s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes breathe { 0%,100% { opacity: 0.75; } 50% { opacity: 1; } }

        /* floating particles */
        .particles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .particles i {
            position: absolute; bottom: -12px; border-radius: 50%;
            background: rgba(160,195,255,0.7);
            animation: rise linear infinite;
        }
        @keyframes rise {
            0%   { transform: translateY(0) scale(1); opacity: 0; }
            15%  { opacity: 0.9; }
            100% { transform: translateY(-105vh) scale(0.4); opacity: 0; }
        }

        .loading-wrap {
            position: relative; z-index: 2; text-align: center; padding: 20px;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .loading-wrap.leaving { opacity: 0; transform: scale(1.05); }

        /* ----- logo ----- */
        .logo-ring {
            position: relative; width: 130px; height: 130px; margin: 0 auto 26px;
            opacity: 0; transform: scale(0.6);
            animation: popIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.05s forwards;
        }
        @keyframes popIn { to { opacity: 1; transform: scale(1); } }

        .logo-ring .spin {
            position: absolute; inset: -12px; border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0deg, rgba(47,111,237,0) 40deg, #2f6fed 200deg, #8dbaff 330deg, transparent 360deg);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
                    mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
            animation: spin 1.2s linear infinite;
            transition: opacity 0.4s ease;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .logo-ring.done .spin { opacity: 0; }

        .logo-ring .pulse {
            position: absolute; inset: 0; border-radius: 50%;
            animation: pulse 2s ease-out infinite;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(47,111,237,0.5); }
            70%  { box-shadow: 0 0 0 24px rgba(47,111,237,0); }
            100% { box-shadow: 0 0 0 0 rgba(47,111,237,0); }
        }
        .logo-ring img {
            position: relative; width: 100%; height: 100%; border-radius: 50%;
            background: #fff; padding: 6px; object-fit: cover;
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
        }

        /* green check that pops when loading is complete */
        .done-badge {
            position: absolute; right: -4px; bottom: -4px; width: 40px; height: 40px; border-radius: 50%;
            background: #16a34a; border: 3px solid var(--navy);
            display: flex; align-items: center; justify-content: center;
            transform: scale(0); transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .done-badge svg { width: 20px; height: 20px; stroke: #fff; fill: none; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .logo-ring.done .done-badge { transform: scale(1); }

        /* ----- text ----- */
        .welcome {
            color: #fff; font-size: 26px; font-weight: 700; margin: 0;
            opacity: 0; transform: translateY(14px); animation: fadeUp 0.6s ease 0.35s forwards;
        }
        .welcome span { color: #8dbaff; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .loading-text {
            color: #b8c7de; font-size: 14px; margin: 8px 0 0; min-height: 22px;
            opacity: 0; animation: fadeIn 0.6s ease 0.6s forwards;
            transition: opacity 0.25s ease;
        }
        @keyframes fadeIn { to { opacity: 1; } }
        .loading-dots span { display: inline-block; animation: dotBounce 1.4s infinite; }
        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotBounce { 0%, 80%, 100% { opacity: 0.3; } 40% { opacity: 1; } }

        /* ----- progress ----- */
        .loader { width: 260px; max-width: 78vw; margin: 28px auto 0; opacity: 0; animation: fadeIn 0.6s ease 0.7s forwards; }
        .bar { height: 6px; background: rgba(255,255,255,0.12); border-radius: 999px; overflow: hidden; }
        .bar-fill {
            height: 100%; width: 0%; border-radius: 999px;
            background: linear-gradient(90deg, #2f6fed, #8dbaff);
            box-shadow: 0 0 12px rgba(47,111,237,0.7);
            position: relative; overflow: hidden; transition: background 0.4s ease;
        }
        .bar-fill::after {
            content: ""; position: absolute; top: 0; bottom: 0; width: 40px; left: -40px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            animation: shine 1.1s linear infinite;
        }
        @keyframes shine { to { left: 110%; } }
        .bar-fill.done { background: linear-gradient(90deg, #16a34a, #4ade80); box-shadow: 0 0 12px rgba(34,197,94,0.7); }
        .pct { margin-top: 8px; font-size: 12px; color: #9fb1cc; font-weight: 600; font-variant-numeric: tabular-nums; }

        @media (prefers-reduced-motion: reduce) {
            body::before, .particles i, .logo-ring .pulse { animation: none; }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="loading-wrap" id="wrap">
        <div class="logo-ring" id="logoRing">
            <div class="spin"></div>
            <div class="pulse"></div>
            <img src="assets/cms-logo.png" alt="CMS Logo">
            <div class="done-badge"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
        </div>

        <h1 class="welcome">Welcome, <span><?php echo htmlspecialchars($firstName); ?></span>!</h1>
        <p class="loading-text" id="msg">Verifying your account<span class="loading-dots"><span>.</span><span>.</span><span>.</span></span></p>

        <div class="loader">
            <div class="bar"><div class="bar-fill" id="fill"></div></div>
            <div class="pct" id="pct">0%</div>
        </div>
    </div>

    <script>
        // floating particles
        (function () {
            const box = document.getElementById('particles');
            for (let i = 0; i < 16; i++) {
                const p = document.createElement('i');
                const s = 2 + Math.random() * 4;
                p.style.left = Math.random() * 100 + '%';
                p.style.width = p.style.height = s + 'px';
                p.style.animationDuration = (5 + Math.random() * 6) + 's';
                p.style.animationDelay = (-Math.random() * 8) + 's';
                box.appendChild(p);
            }
        })();

        // progress, then go to the dashboard
        (function () {
            const total = 2800;
            const fill = document.getElementById('fill');
            const pct = document.getElementById('pct');
            const msg = document.getElementById('msg');
            const ring = document.getElementById('logoRing');
            const wrap = document.getElementById('wrap');
            const steps = [
                [0,  'Verifying your account'],
                [45, 'Loading your dashboard'],
                [85, 'Almost there']
            ];
            let last = '';
            const dots = '<span class="loading-dots"><span>.</span><span>.</span><span>.</span></span>';
            const start = performance.now();

            function ease(t) { return 1 - Math.pow(1 - t, 2.2); }

            function frame(now) {
                const t = Math.min((now - start) / total, 1);
                const v = Math.round(ease(t) * 100);
                fill.style.width = v + '%';
                pct.textContent = v + '%';

                for (let i = steps.length - 1; i >= 0; i--) {
                    if (v >= steps[i][0]) {
                        if (steps[i][1] !== last) {
                            last = steps[i][1];
                            msg.style.opacity = 0;
                            setTimeout(() => { msg.innerHTML = last + dots; msg.style.opacity = 1; }, 140);
                        }
                        break;
                    }
                }

                if (t < 1) {
                    requestAnimationFrame(frame);
                } else {
                    fill.classList.add('done');
                    ring.classList.add('done');
                    msg.style.opacity = 0;
                    setTimeout(() => { msg.textContent = 'All set!'; msg.style.opacity = 1; }, 140);
                    setTimeout(() => { window.location.href = 'index.php'; }, 700);
                }
            }
            requestAnimationFrame(frame);
        })();
    </script>
</body>
</html>
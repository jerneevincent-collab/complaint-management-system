<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint Management System</title>
    <noscript><meta http-equiv="refresh" content="3;url=login.php"></noscript>
    <style>
        :root { --bg: #121417; --accent: #2f6fed; --navy: #0a2540; }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', 'Inter', system-ui, -apple-system, sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* soft glow behind everything */
        body::before {
            content: "";
            position: absolute; inset: 0;
            background:
                radial-gradient(circle at 50% 42%, rgba(47,111,237,0.28), transparent 52%),
                radial-gradient(circle at 20% 85%, rgba(10,37,64,0.55), transparent 45%);
            animation: breathe 5s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes breathe { 0%,100% { opacity: 0.75; } 50% { opacity: 1; } }

        /* floating particles */
        .particles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .particles i {
            position: absolute; bottom: -12px; width: 4px; height: 4px; border-radius: 50%;
            background: rgba(143,178,255,0.7);
            animation: rise linear infinite;
        }
        @keyframes rise {
            0%   { transform: translateY(0) scale(1); opacity: 0; }
            15%  { opacity: 0.9; }
            100% { transform: translateY(-105vh) scale(0.4); opacity: 0; }
        }

        .splash-wrap {
            position: relative; z-index: 2; text-align: center; padding: 20px;
            transition: opacity 0.7s ease, transform 0.7s ease, filter 0.7s ease;
        }
        .splash-wrap.leaving { opacity: 0; transform: scale(1.06); filter: blur(6px); }

        /* ----- logo with spinning ring ----- */
        .logo-ring {
            position: relative; width: 140px; height: 140px; margin: 0 auto 28px;
            opacity: 0; transform: scale(0.6);
            animation: popIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s forwards;
        }
        @keyframes popIn { to { opacity: 1; transform: scale(1); } }

        .logo-ring .spin {
            position: absolute; inset: -12px; border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0deg, rgba(47,111,237,0) 40deg, #2f6fed 200deg, #8dbaff 330deg, transparent 360deg);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
                    mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
            animation: spin 1.4s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .logo-ring .pulse {
            position: absolute; inset: 0; border-radius: 50%;
            animation: pulse 2s ease-out infinite;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(47,111,237,0.5); }
            70%  { box-shadow: 0 0 0 26px rgba(47,111,237,0); }
            100% { box-shadow: 0 0 0 0 rgba(47,111,237,0); }
        }
        .logo-ring img {
            position: relative; width: 100%; height: 100%; border-radius: 50%;
            background: #fff; padding: 6px; object-fit: cover;
            box-shadow: 0 12px 30px rgba(0,0,0,0.45);
            animation: float 3.2s ease-in-out infinite;
        }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

        /* ----- title typed in letter by letter ----- */
        .splash-title {
            color: #fff; font-size: 28px; font-weight: 700; letter-spacing: 0.5px; min-height: 40px;
        }
        .splash-title span {
            display: inline-block; opacity: 0; transform: translateY(14px);
            animation: letterIn 0.5s ease forwards;
        }
        @keyframes letterIn { to { opacity: 1; transform: translateY(0); } }

        .splash-subtitle {
            color: #94a3b8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.14em;
            margin-top: 8px; opacity: 0; animation: fadeIn 0.8s ease 1.4s forwards;
        }
        @keyframes fadeIn { to { opacity: 1; } }

        /* ----- progress ----- */
        .loader { width: 280px; max-width: 80vw; margin: 36px auto 0; opacity: 0; animation: fadeIn 0.6s ease 1.1s forwards; }
        .bar { height: 6px; background: rgba(255,255,255,0.1); border-radius: 999px; overflow: hidden; position: relative; }
        .bar-fill {
            height: 100%; width: 0%; border-radius: 999px;
            background: linear-gradient(90deg, #2f6fed, #8dbaff);
            box-shadow: 0 0 12px rgba(47,111,237,0.7);
            position: relative; overflow: hidden;
        }
        .bar-fill::after {
            content: ""; position: absolute; top: 0; bottom: 0; width: 40px; left: -40px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            animation: shine 1.2s linear infinite;
        }
        @keyframes shine { to { left: 110%; } }
        .loader-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #8a93a3; }
        .loader-info #pct { color: #cbd5e1; font-weight: 600; font-variant-numeric: tabular-nums; }
        #msg { transition: opacity 0.25s ease; }

        @media (prefers-reduced-motion: reduce) {
            body::before, .particles i, .logo-ring img, .logo-ring .pulse { animation: none; }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="splash-wrap" id="splashWrap">
        <div class="logo-ring">
            <div class="spin"></div>
            <div class="pulse"></div>
            <img src="assets/cms-logo.png" alt="CMS Logo">
        </div>

        <div class="splash-title" id="title" aria-label="Complaint Management System"></div>
        <div class="splash-subtitle">Saint Mary's University</div>

        <div class="loader">
            <div class="bar"><div class="bar-fill" id="fill"></div></div>
            <div class="loader-info"><span id="msg">Starting system...</span><span id="pct">0%</span></div>
        </div>
    </div>

    <script>
        // floating particles
        (function () {
            const box = document.getElementById('particles');
            for (let i = 0; i < 18; i++) {
                const p = document.createElement('i');
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (6 + Math.random() * 7) + 's';
                p.style.animationDelay = (-Math.random() * 10) + 's';
                const s = 2 + Math.random() * 4;
                p.style.width = p.style.height = s + 'px';
                box.appendChild(p);
            }
        })();

        // title typed letter by letter
        (function () {
            const text = 'Complaint Management System';
            const el = document.getElementById('title');
            [...text].forEach((ch, i) => {
                const s = document.createElement('span');
                s.textContent = ch === ' ' ? ' ' : ch;
                s.style.animationDelay = (0.5 + i * 0.035) + 's';
                el.appendChild(s);
            });
        })();

        // progress + redirect to the lamp login
        (function () {
            let seen = false;
            try { seen = sessionStorage.getItem('cmsSplashSeen') === '1'; } catch (e) {}

            const total = seen ? 900 : 4600;   // already saw it this session -> quick version
            const fill = document.getElementById('fill');
            const pct = document.getElementById('pct');
            const msg = document.getElementById('msg');
            const wrap = document.getElementById('splashWrap');
            const messages = [
                [0,  'Starting system...'],
                [30, 'Loading modules...'],
                [60, 'Connecting to database...'],
                [85, 'Preparing login...']
            ];
            let lastMsg = '';
            const start = performance.now();

            function ease(t) { return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; }

            function frame(now) {
                const t = Math.min((now - start) / total, 1);
                const v = Math.round(ease(t) * 100);
                fill.style.width = v + '%';
                pct.textContent = v + '%';
                for (let i = messages.length - 1; i >= 0; i--) {
                    if (v >= messages[i][0]) {
                        if (messages[i][1] !== lastMsg) {
                            lastMsg = messages[i][1];
                            msg.style.opacity = 0;
                            setTimeout(() => { msg.textContent = lastMsg; msg.style.opacity = 1; }, 120);
                        }
                        break;
                    }
                }
                if (t < 1) {
                    requestAnimationFrame(frame);
                } else {
                    try { sessionStorage.setItem('cmsSplashSeen', '1'); } catch (e) {}
                    wrap.classList.add('leaving');
                    setTimeout(() => { window.location.href = 'login.php'; }, 650);
                }
            }
            requestAnimationFrame(frame);
        })();
    </script>
</body>
</html>
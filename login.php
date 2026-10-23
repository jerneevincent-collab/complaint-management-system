<?php
session_start();
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Complaint Management System</title>
    <style>
        :root {
            --bg-color: #121417;
            --lamp-shade: #f5f0e6;
            --glow-color: rgba(255, 214, 110, 0.3);
            --accent-color: #2f6fed;
            --on: 0;
            --transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --form-w: 360px;
            --gap: 8vmin;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: var(--bg-color);
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overflow: hidden;
            transition: background var(--transition);
        }

        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 40%, var(--glow-color), transparent 70%);
            opacity: var(--on);
            transition: opacity var(--transition);
            pointer-events: none;
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--gap);
            z-index: 1;
            flex-wrap: wrap;
            width: 100%;
            max-width: 1000px;
        }

        /* ---------- LAMP ---------- */
        .lamp-wrapper {
            position: relative;
            width: 280px;
            height: 400px;
            display: flex;
            justify-content: center;
            /* centered on screen while the form is hidden, slides left when the lamp is on */
            transform: translateX(calc((var(--form-w) + var(--gap)) / 2));
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        [data-on="true"] .lamp-wrapper { transform: translateX(0); }

        .lamp-svg { width: 100%; height: 100%; overflow: visible; }

        .lamp-shade { fill: url(#shadeGrad); transition: fill var(--transition); }
        [data-on="true"] .lamp-shade {
            fill: #fffdf3;
            filter: drop-shadow(0 0 30px rgba(255, 255, 200, 0.45));
        }

        .lamp-stand { fill: url(#standGrad); }
        .ground-shadow { fill: rgba(0, 0, 0, 0.4); filter: blur(4px); }

        .inner-glow { fill: #ffdb8a; opacity: 0.6; filter: blur(15px); }
        .cone { fill: url(#coneGrad); filter: blur(5px); }
        .floor-glow { fill: url(#floorGrad); }

        .dust { fill: #fff3c4; opacity: 0; animation: dust 6s linear infinite; }
        @keyframes dust {
            0%   { opacity: 0;   transform: translate(0, 12px); }
            30%  { opacity: 0.9; }
            100% { opacity: 0;   transform: translate(8px, -46px); }
        }

        .cord-line { stroke: #666; stroke-width: 2; }
        .cord-bead { fill: var(--accent-color); }
        .cord-hit { cursor: pointer; }
        .cord-ring {
            fill: none; stroke: var(--accent-color); stroke-width: 2;
            transform-box: fill-box; transform-origin: center;
            animation: ring 1.8s ease-out infinite;
        }
        @keyframes ring {
            0%   { transform: scale(1);   opacity: 0.9; }
            100% { transform: scale(3.4); opacity: 0; }
        }
        [data-on="true"] .cord-ring { animation: none; opacity: 0; }

        /* ---------- LOGIN FORM ---------- */
        .login-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 2.5rem;
            border-radius: 30px;
            width: var(--form-w);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transform: translateY(30px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .login-form.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        /* fields appear one by one */
        .stagger { opacity: 0; transform: translateY(14px); transition: opacity 0.5s ease, transform 0.5s ease; }
        .login-form.active .stagger { opacity: 1; transform: translateY(0); }
        .login-form.active .s1 { transition-delay: 0.15s; }
        .login-form.active .s2 { transition-delay: 0.25s; }
        .login-form.active .s3 { transition-delay: 0.35s; }
        .login-form.active .s4 { transition-delay: 0.45s; }
        .login-form.active .s5 { transition-delay: 0.55s; }
        .login-form.active .s6 { transition-delay: 0.65s; }

        .login-form img.logo {
            display: block;
            width: 56px;
            height: 56px;
            margin: 0 auto 10px;
            border-radius: 50%;
        }

        .login-form h2 {
            color: #fff;
            margin: 0 0 6px 0;
            font-weight: 500;
            text-align: center;
            font-size: 1.5rem;
        }

        .login-form .subtitle {
            color: #999;
            text-align: center;
            font-size: 0.8rem;
            margin: 0 0 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            color: #999;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            margin-left: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid transparent;
            border-radius: 15px;
            color: white;
            outline: none;
            transition: 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.22);
        }

        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 64px; }
        .pw-toggle {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #8fb2ff; font-size: 0.78rem; font-weight: 600;
            cursor: pointer; padding: 6px 8px; border-radius: 8px; transition: background 0.2s, color 0.2s;
        }
        .pw-toggle:hover { background: rgba(255,255,255,0.08); color: #fff; }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0a2540, #2f6fed);
            border: none;
            border-radius: 15px;
            font-weight: 600;
            color: #ffffff;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s, box-shadow 0.3s;
            margin-top: 10px;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .login-btn:hover {
            transform: translateY(-2px) scale(1.02);
            background: linear-gradient(135deg, #2f6fed, #0a2540);
            box-shadow: 0 10px 24px rgba(47, 111, 237, 0.35);
        }
        .login-btn:disabled { opacity: 0.85; cursor: wait; transform: none; }
        .login-btn .spinner {
            display: none; width: 16px; height: 16px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff;
            animation: spin 0.7s linear infinite;
        }
        .login-btn.loading .spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .error-msg {
            color: #ff8a8a;
            background: rgba(255, 100, 100, 0.08);
            border: 1px solid rgba(255, 100, 100, 0.2);
            border-radius: 12px;
            padding: 8px 12px;
            text-align: center;
            font-size: 0.85rem;
            margin: 0 0 14px;
            animation: shake 0.45s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        /* ---------- COMPLAINANT BUTTONS ---------- */
        .or-divider {
            display: flex; align-items: center; gap: 12px;
            margin: 22px 0 14px; color: #777; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 0.08em;
        }
        .or-divider::before, .or-divider::after { content: ""; flex: 1; height: 1px; background: rgba(255,255,255,0.12); }
        .complainant-label { text-align: center; color: #888; font-size: 0.8rem; margin: 0 0 12px; }
        .alt-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .alt-btn {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            padding: 12px 8px; border-radius: 14px; text-decoration: none;
            color: #cbd5e1; font-size: 0.78rem; font-weight: 600; text-align: center;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12);
            transition: transform 0.25s ease, background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
        }
        .alt-btn svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        .alt-btn:hover {
            transform: translateY(-3px);
            background: rgba(47,111,237,0.18); border-color: var(--accent-color); color: #fff;
        }

        /* ---------- HINT ---------- */
        .hint {
            color: #8a8a8a;
            text-align: center;
            font-size: 0.8rem;
            margin-top: 20px;
            position: relative;
            z-index: 1;
            transition: opacity 0.4s ease, transform 0.4s ease;
            animation: hintBlink 2.4s ease-in-out infinite;
        }
        .hint::before { content: "\2191"; display: block; font-size: 1rem; animation: arrowBounce 1.2s ease-in-out infinite; }
        .hint.hide { opacity: 0; transform: translateY(10px); animation: none; pointer-events: none; }
        @keyframes hintBlink { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }
        @keyframes arrowBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        @media (max-width: 600px) {
            .container { flex-direction: column; gap: 0; }
            .lamp-wrapper {
                width: 160px;
                height: 200px;
                margin-bottom: -20px;
                transform: none !important;
            }
            .login-form {
                width: 90%;
                max-width: 360px;
                padding: 2rem 1.5rem;
            }
            .hint { font-size: 0.7rem; margin-top: 10px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .dust, .cord-ring, .hint, .hint::before { animation: none; }
        }
    </style>
</head>
<body data-on="false">

<div class="container">
    <div class="lamp-wrapper">
        <svg class="lamp-svg" viewBox="0 0 200 300" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="standGrad" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0" stop-color="#a9a398" />
                    <stop offset="0.45" stop-color="#ebe5db" />
                    <stop offset="1" stop-color="#9d978c" />
                </linearGradient>
                <radialGradient id="shadeGrad" cx="0.5" cy="0.2" r="0.9">
                    <stop offset="0" stop-color="#fbf7ee" />
                    <stop offset="1" stop-color="#e3dccd" />
                </radialGradient>
                <linearGradient id="coneGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#ffe9a8" stop-opacity="0.55" />
                    <stop offset="1" stop-color="#ffe9a8" stop-opacity="0.03" />
                </linearGradient>
                <radialGradient id="floorGrad" cx="0.5" cy="0.5" r="0.5">
                    <stop offset="0" stop-color="#ffe9a8" stop-opacity="0.6" />
                    <stop offset="1" stop-color="#ffe9a8" stop-opacity="0" />
                </radialGradient>
            </defs>

            <!-- shadow on the floor (always visible) -->
            <ellipse class="ground-shadow" cx="100" cy="264" rx="62" ry="5" />

            <!-- light: cone, glow, floor reflection, dust (hidden until the lamp is on) -->
            <g id="light" opacity="0">
                <polygon class="cone" points="42,118 158,118 214,262 -14,262" />
                <ellipse class="floor-glow" cx="100" cy="264" rx="105" ry="14" />
                <ellipse class="inner-glow" cx="100" cy="110" rx="60" ry="30" />
                <circle class="dust" cx="70"  cy="215" r="1.6" style="animation-delay:-0.5s" />
                <circle class="dust" cx="95"  cy="230" r="1.2" style="animation-delay:-2s" />
                <circle class="dust" cx="120" cy="205" r="1.8" style="animation-delay:-3.5s" />
                <circle class="dust" cx="140" cy="235" r="1.3" style="animation-delay:-1.2s" />
                <circle class="dust" cx="82"  cy="190" r="1.4" style="animation-delay:-4.4s" />
                <circle class="dust" cx="110" cy="180" r="1.1" style="animation-delay:-5.2s" />
                <circle class="dust" cx="58"  cy="240" r="1.5" style="animation-delay:-2.8s" />
                <circle class="dust" cx="150" cy="215" r="1.2" style="animation-delay:-0.1s" />
            </g>

            <rect class="lamp-stand" x="92" y="100" width="16" height="160" rx="8" />
            <rect class="lamp-stand" x="60" y="250" width="80" height="12" rx="6" />

            <g class="pull-cord" id="cordGroup">
                <line class="cord-line" x1="130" y1="110" x2="130" y2="180" />
                <g id="beadRing"><circle class="cord-ring" cx="130" cy="190" r="6" /></g>
                <circle class="cord-bead" cx="130" cy="190" r="6" />
                <circle class="cord-hit" cx="130" cy="190" r="25" fill="transparent" />
            </g>

            <path class="lamp-shade" d="M30 110 C 30 50, 170 50, 170 110 C 170 125, 30 125, 30 110 Z" />
        </svg>
    </div>

    <div class="login-form" id="loginForm">
        <div class="stagger s1">
            <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
            <h2>Welcome Back</h2>
            <p class="subtitle">Complaint Management System</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <p class="error-msg">Invalid email or password.</p>
        <?php endif; ?>

        <form action="process_login.php" method="POST" id="loginFormEl">
            <div class="form-group stagger s2">
                <label>Email</label>
                <input type="email" name="email" placeholder="jerneevincent@gmail.com" required />
            </div>
            <div class="form-group stagger s3">
                <label>Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password" id="pwInput" placeholder="pass1234" required />
                    <button type="button" class="pw-toggle" id="pwToggle">Show</button>
                </div>
            </div>
            <div class="stagger s4">
                <button type="submit" class="login-btn" id="loginBtn"><span class="spinner"></span><span class="btn-text">Sign In</span></button>
            </div>
        </form>

        <div class="stagger s5">
            <div class="or-divider">or</div>
            <p class="complainant-label">Are you a complainant?</p>
        </div>
        <div class="alt-actions stagger s6">
            <a class="alt-btn" href="complainant_register.php">
                <svg viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M12 12v6M9 15h6"/></svg>
                File a Complaint
            </a>
            <a class="alt-btn" href="track_complaint.php">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                Track Complaint
            </a>
        </div>
    </div>
</div>

<p class="hint" id="hint">drag the cord to turn on the lamp</p>

<script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
<script src="https://unpkg.com/gsap@3/dist/Draggable.min.js"></script>

<script>
    gsap.registerPlugin(Draggable);

    const root = document.documentElement;
    const body = document.body;
    const loginForm = document.getElementById('loginForm');
    const cordBead = document.querySelector('.cord-bead');
    const cordLine = document.querySelector('.cord-line');
    const hitArea = document.querySelector('.cord-hit');
    const cordGroup = document.getElementById('cordGroup');
    const beadRing = document.getElementById('beadRing');
    const light = document.getElementById('light');
    const hint = document.getElementById('hint');

    let isOn = <?php echo (isset($_GET['error'])) ? 'true' : 'false'; ?>;

    // cord gently swings while the lamp is off
    const swing = gsap.to(cordGroup, {
        rotation: 4, svgOrigin: "130 110", duration: 1.8,
        yoyo: true, repeat: -1, ease: "sine.inOut"
    });

    if (isOn) {
        body.setAttribute('data-on', 'true');
        root.style.setProperty('--on', 1);
        loginForm.classList.add('active');
        body.style.backgroundColor = '#1c1f24';
        gsap.set(light, { opacity: 1 });
        hint.classList.add('hide');
        swing.pause();
        gsap.set(cordGroup, { rotation: 0, svgOrigin: "130 110" });
    }

    hitArea.addEventListener('click', function() {
        toggleLamp();
    });

    Draggable.create(hitArea, {
        type: "y",
        bounds: { minY: 0, maxY: 60 },
        onPress: function() {
            swing.pause();
            gsap.to(cordGroup, { rotation: 0, svgOrigin: "130 110", duration: 0.2 });
        },
        onDrag: function() {
            gsap.set([cordBead, beadRing], { y: this.y });
            gsap.set(cordLine, { attr: { y2: 180 + this.y } });
        },
        onRelease: function() {
            if (this.y > 30) {
                toggleLamp();
            }
            gsap.to([cordBead, beadRing, hitArea], { y: 0, duration: 0.5, ease: "back.out(2.5)" });
            gsap.to(cordLine, { attr: { y2: 180 }, duration: 0.5, ease: "back.out(2.5)" });
            if (!isOn) swing.play();
        }
    });

    function flicker() {
        gsap.killTweensOf(light);
        gsap.timeline()
            .set(light, { opacity: 0 })
            .to(light, { opacity: 1,    duration: 0.06 })
            .to(light, { opacity: 0.15, duration: 0.08 })
            .to(light, { opacity: 1,    duration: 0.06 })
            .to(light, { opacity: 0.4,  duration: 0.10 })
            .to(light, { opacity: 1,    duration: 0.35 });
    }

    function toggleLamp() {
        isOn = !isOn;
        body.setAttribute('data-on', isOn);
        root.style.setProperty('--on', isOn ? 1 : 0);

        if (isOn) {
            swing.pause();
            flicker();
            hint.classList.add('hide');
            gsap.to(body, { backgroundColor: "#1c1f24", duration: 0.6 });
            setTimeout(() => { if (isOn) loginForm.classList.add('active'); }, 450);
        } else {
            loginForm.classList.remove('active');
            gsap.killTweensOf(light);
            gsap.to(light, { opacity: 0, duration: 0.4 });
            hint.classList.remove('hide');
            gsap.to(body, { backgroundColor: "#121417", duration: 0.6 });
            swing.play();
        }
    }

    // show / hide password
    const pwInput = document.getElementById('pwInput');
    document.getElementById('pwToggle').addEventListener('click', function () {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        this.textContent = show ? 'Hide' : 'Show';
    });

    // "Signing in..." state
    document.getElementById('loginFormEl').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('loading');
        btn.querySelector('.btn-text').textContent = 'Signing in...';
        setTimeout(() => { btn.disabled = true; }, 0);
    });
</script>

</body>
</html>
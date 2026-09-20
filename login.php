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
            --lamp-matte: #e8e2d9;
            --lamp-shade: #f5f0e6;
            --lamp-base: #d1ccc2;
            --glow-color: rgba(255, 214, 110, 0.3);
                        --accent-color: #2f6fed;
            --on: 0;
            --transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
            gap: 8vmin;
            z-index: 1;
            flex-wrap: wrap;
            width: 100%;
            max-width: 1000px;
        }

        .lamp-wrapper {
            position: relative;
            width: 280px;
            height: 400px;
            display: flex;
            justify-content: center;
        }

        .lamp-svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .lamp-shade {
            fill: var(--lamp-shade);
            transition: fill var(--transition);
        }
        [data-on="true"] .lamp-shade {
            fill: #fff;
            filter: drop-shadow(0 0 30px rgba(255, 255, 200, 0.4));
        }

        .lamp-base { fill: var(--lamp-base); }

        .inner-glow {
            fill: #ffdb8a;
            opacity: 0;
            transition: opacity var(--transition);
            filter: blur(15px);
        }
        [data-on="true"] .inner-glow { opacity: 0.6; }

        .cord-line { stroke: #555; stroke-width: 2; }
        .cord-bead { fill: var(--accent-color); }
        .cord-hit { cursor: pointer; }

        .login-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 2.5rem;
            border-radius: 30px;
            width: 340px;
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
            margin-bottom: 1.5rem;
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
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0a2540, #2f6fed);
            border: none;
            border-radius: 15px;
            font-weight: 600;
            color: #121417;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            font-size: 1rem;
        }

             .login-btn:hover {
            transform: scale(1.02);
            background: linear-gradient(135deg, #2f6fed, #0a2540);
        }

        .error-msg {
            color: #ff8a8a;
            text-align: center;
            font-size: 0.85rem;
            margin: 0 0 12px;
        }

        .complainant-link {
            text-align: center;
            margin-top: 18px;
            font-size: 0.8rem;
            color: #888;
        }
        .complainant-link a { color: var(--accent-color); }

        .hint {
            color: #666;
            text-align: center;
            font-size: 0.8rem;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 600px) {
            .container {
                flex-direction: column;
                gap: 0;
            }
            .lamp-wrapper {
                width: 160px;
                height: 200px;
                margin-bottom: -20px;
            }
            .login-form {
                width: 90%;
                max-width: 340px;
                padding: 2rem 1.5rem;
            }
            .hint {
                font-size: 0.7rem;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body data-on="false">

<div class="container">
    <div class="lamp-wrapper">
        <svg class="lamp-svg" viewBox="0 0 200 300" xmlns="http://www.w3.org/2000/svg">
            <ellipse class="inner-glow" cx="100" cy="110" rx="60" ry="30" />
            <rect class="lamp-base" x="92" y="100" width="16" height="160" rx="8" />
            <rect class="lamp-base" x="60" y="250" width="80" height="12" rx="6" />
            <g class="pull-cord">
                <line class="cord-line" x1="130" y1="110" x2="130" y2="180" />
                <circle class="cord-bead" cx="130" cy="190" r="6" />
                <circle class="cord-hit" cx="130" cy="190" r="25" fill="transparent" />
            </g>
            <path class="lamp-shade" d="M30 110 C 30 50, 170 50, 170 110 C 170 125, 30 125, 30 110 Z" />
        </svg>
    </div>

    <div class="login-form" id="loginForm">
        <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
        <h2>Welcome Back</h2>
        <p class="subtitle">Complaint Management System</p>

        <?php if (isset($_GET['error'])): ?>
            <p class="error-msg">Invalid email or password.</p>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required />
            </div>
            <button type="submit" class="login-btn">Sign In</button>
        </form>

               <p class="complainant-link">Are you a complainant? <a href="complainant_register.php">File a complaint here</a> or <a href="track_complaint.php">track your complaint</a></p>
    </div>
</div>

<p class="hint">drag the cord to turn on the lamp</p>

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

    let isOn = <?php echo (isset($_GET['error'])) ? 'true' : 'false'; ?>;

    if (isOn) {
        body.setAttribute('data-on', 'true');
        root.style.setProperty('--on', 1);
        loginForm.classList.add('active');
        body.style.backgroundColor = '#1c1f24';
    }

    hitArea.addEventListener('click', function() {
        toggleLamp();
    });

    Draggable.create(hitArea, {
        type: "y",
        bounds: { minY: 0, maxY: 60 },
        onDrag: function() {
            gsap.set(cordBead, { y: this.y });
            gsap.set(cordLine, { attr: { y2: 180 + this.y } });
        },
        onRelease: function() {
            if (this.y > 30) {
                toggleLamp();
            }
            gsap.to([cordBead, hitArea], { y: 0, duration: 0.5, ease: "back.out(2.5)" });
            gsap.to(cordLine, { attr: { y2: 180 }, duration: 0.5, ease: "back.out(2.5)" });
        }
    });

    function toggleLamp() {
        isOn = !isOn;
        body.setAttribute('data-on', isOn);
        root.style.setProperty('--on', isOn ? 1 : 0);

        if (isOn) {
            loginForm.classList.add('active');
            gsap.to(body, { backgroundColor: "#1c1f24", duration: 0.6 });
        } else {
            loginForm.classList.remove('active');
            gsap.to(body, { backgroundColor: "#121417", duration: 0.6 });
        }
    }
</script>

</body>
</html>
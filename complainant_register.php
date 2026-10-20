<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Complainant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --card-bg: #ffffff;
            --navy: #0a2540;
            --accent: #2f6fed;
            --text: #475569;
            --input-bg: #f4f7fb;
            --elastic: cubic-bezier(0.75, -0.5, 0.27, 1.55);
        }
        * { box-sizing: border-box; }

        body.reg-page { font-family: 'Poppins', sans-serif; margin: 0; }

        /* ---------- animated background ---------- */
        .bg {
            position: fixed; inset: 0; z-index: 0; overflow: hidden;
            background: linear-gradient(135deg, #eef2f9 0%, #dbe6f5 100%);
        }
        .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: 0.55; }
        .blob.b1 { width: 380px; height: 380px; background: #9bbcff; top: -120px; left: -100px; animation: drift1 14s ease-in-out infinite; }
        .blob.b2 { width: 320px; height: 320px; background: #b7d4ff; bottom: -110px; right: -80px; animation: drift2 17s ease-in-out infinite; }
        .blob.b3 { width: 240px; height: 240px; background: #cfc6ff; top: 45%; right: 18%; opacity: 0.35; animation: drift1 20s ease-in-out infinite reverse; }
        @keyframes drift1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(60px, 40px); } }
        @keyframes drift2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-50px, -40px); } }

        .page {
            position: relative; z-index: 1;
            min-height: 100vh; width: 100%;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 20px;
        }
        .wrap { width: 100%; max-width: 640px; margin: 0 auto; }

        /* ---------- back pill ---------- */
        .back-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; margin-bottom: 16px;
            background: rgba(255,255,255,0.75); backdrop-filter: blur(6px);
            border: 1px solid #dbe3ef; border-radius: 999px;
            color: var(--navy); font-size: 13px; font-weight: 600; text-decoration: none;
            transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            opacity: 0; animation: fadeIn 0.5s ease 0.1s forwards;
        }
        .back-pill svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; transition: transform 0.2s ease; }
        .back-pill:hover { background: #fff; box-shadow: 0 6px 16px rgba(10,37,64,0.1); text-decoration: none; color: var(--navy); }
        .back-pill:hover svg { transform: translateX(-4px); }

        /* ---------- card ---------- */
        .reg-card {
            position: relative;
            width: 100%;
            background: var(--card-bg);
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(10, 37, 64, 0.15);
            border: 1px solid #e2e8f0;
            padding: 40px 38px;
            overflow: hidden;
            opacity: 0;
            transform: translateY(60px) scale(0.95);
            animation: revealCard 0.9s var(--elastic) 0.1s forwards;
        }
        @keyframes revealCard { to { opacity: 1; transform: translateY(0) scale(1); } }

        .glow {
            position: absolute; top: -40px; right: -40px; width: 180px; height: 180px;
            background: var(--accent); filter: blur(90px); opacity: 0.18; pointer-events: none;
        }

        .reg-card .logo {
            display: block; width: 64px; height: 64px; margin: 0 auto 10px;
            border-radius: 50%; position: relative; z-index: 2;
            animation: logoPulse 3.4s ease-out 1.2s infinite;
        }
        @keyframes logoPulse {
            0%   { box-shadow: 0 0 0 0 rgba(47,111,237,0.35); }
            70%  { box-shadow: 0 0 0 14px rgba(47,111,237,0); }
            100% { box-shadow: 0 0 0 0 rgba(47,111,237,0); }
        }

        .reg-card h2 {
            color: var(--navy); text-align: center; margin: 0 0 4px;
            font-size: 24px; letter-spacing: -0.5px; position: relative; z-index: 2;
        }
        .reg-card .subtitle {
            color: var(--text); opacity: 0.8; text-align: center; font-size: 13px;
            margin: 0 0 24px; position: relative; z-index: 2;
        }

        /* ---------- form ---------- */
        .reg-card form {
            box-shadow: none; padding: 0; max-width: 100%; margin: 0;
            background: transparent; position: relative; z-index: 2;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 16px; }

        .input-group {
            display: flex; flex-direction: column; gap: 6px;
            opacity: 0; transform: translateY(10px);
            animation: fieldIn 0.5s ease forwards;
        }
        .input-group:nth-child(1) { animation-delay: 0.50s; }
        .input-group:nth-child(2) { animation-delay: 0.56s; }
        .input-group:nth-child(3) { animation-delay: 0.62s; }
        .input-group:nth-child(4) { animation-delay: 0.68s; }
        .input-group:nth-child(5) { animation-delay: 0.74s; }
        .input-group:nth-child(6) { animation-delay: 0.80s; }
        @keyframes fieldIn { to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { to { opacity: 1; } }

        .input-group label {
            color: var(--navy); font-size: 11px; text-transform: uppercase;
            letter-spacing: 1.2px; font-weight: 600; margin: 0;
        }

        .field { position: relative; }
        .field .ico {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; stroke: #94a3b8; fill: none; stroke-width: 1.8;
            stroke-linecap: round; stroke-linejoin: round; pointer-events: none;
            transition: stroke 0.25s ease;
        }
        .field:focus-within .ico { stroke: var(--accent); }
        .field .tick {
            position: absolute; right: 12px; top: 50%; width: 20px; height: 20px;
            transform: translateY(-50%) scale(0); transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }
        .field.valid .tick { transform: translateY(-50%) scale(1); }
        .field.valid input { border-color: #86d9a6; }
        .field.invalid input, .field.invalid select { border-color: #f08a8a; background: #fff7f7; }

        .input-group input, .input-group select {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 40px 13px 42px;
            color: var(--navy);
            outline: none;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        .input-group select {
            appearance: none; -webkit-appearance: none; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
        }
        .input-group input:focus, .input-group select:focus {
            border-color: var(--accent);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.12);
        }

        .submit-btn {
            grid-column: 1 / -1;
            background: var(--navy); color: #fff; padding: 14px; border: none;
            border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer;
            margin-top: 4px; width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
            opacity: 0; animation: fadeIn 0.5s ease 0.9s forwards;
        }
        .submit-btn:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 10px 22px rgba(47,111,237,0.3); }
        .submit-btn:disabled { opacity: 0.85; cursor: wait; transform: none; }
        .spinner {
            display: none; width: 16px; height: 16px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff;
            animation: spin 0.7s linear infinite;
        }
        .submit-btn.loading .spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .error-msg, .success-msg {
            position: relative; z-index: 2; text-align: center; font-size: 13px;
            border-radius: 12px; padding: 10px 14px; margin: 0 0 18px;
        }
        .error-msg { color: #b91c1c !important; background: #fef2f2; border: 1px solid #fecaca; animation: shake 0.45s ease; }
        .success-msg { color: #15803d !important; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .error-msg[hidden] { display: none; }
        @keyframes shake {
            0%,100% { transform: translateX(0); } 20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); } 60% { transform: translateX(-4px); } 80% { transform: translateX(4px); }
        }

        /* ---------- success screen ---------- */
        .success-view {
            position: absolute; inset: 0; z-index: 5; background: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 30px;
            opacity: 0; pointer-events: none; transition: opacity 0.4s ease;
        }
        .success-view.show { opacity: 1; pointer-events: all; }
        .check-circle { width: 96px; height: 96px; }
        .check-circle .ring { fill: none; stroke: #16a34a; stroke-width: 4; stroke-dasharray: 289; stroke-dashoffset: 289; }
        .check-circle .mark { fill: none; stroke: #16a34a; stroke-width: 5; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 60; stroke-dashoffset: 60; }
        .success-view.show .ring { animation: draw 0.7s ease 0.1s forwards; }
        .success-view.show .mark { animation: draw 0.4s ease 0.7s forwards; }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        .success-view h3 { color: var(--navy); font-size: 22px; margin: 18px 0 6px; }
        .success-view p { color: var(--text); font-size: 13px; margin: 0 0 22px; max-width: 320px; line-height: 1.6; }
        .go-btn {
            display: inline-block; background: var(--navy); color: #fff; font-weight: 700; font-size: 14px;
            padding: 13px 28px; border-radius: 12px; text-decoration: none;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }
        .go-btn:hover { background: var(--accent); color: #fff; text-decoration: none; transform: translateY(-2px); box-shadow: 0 10px 22px rgba(47,111,237,0.3); }
        .redirect-bar { width: 220px; height: 4px; background: #e2e8f0; border-radius: 999px; margin-top: 18px; overflow: hidden; }
        .redirect-bar span { display: block; height: 100%; width: 0; background: var(--accent); }
        .success-view.show .redirect-bar span { animation: fill 3.5s linear 1.1s forwards; }
        @keyframes fill { to { width: 100%; } }

        @media (max-width: 560px) {
            .form-grid { grid-template-columns: 1fr; }
            .reg-card { padding: 32px 22px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .blob, .reg-card .logo { animation: none; }
        }
    </style>
</head>
<body class="reg-page">
    <div class="bg"><span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span></div>

    <div class="page">
        <div class="wrap">
            <a class="back-pill" href="login.php">
                <svg viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                Back to Login
            </a>

            <div class="reg-card">
                <div class="glow"></div>
                <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
                <h2>Complainant Registration</h2>
                <p class="subtitle">No login required</p>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo '<p class="success-msg">Complainant registered successfully!</p>'; ?>
                <?php if (isset($_GET['status']) && $_GET['status'] == 'error') echo '<p class="error-msg" id="errBox">All required fields must be filled out.</p>'; else echo '<p class="error-msg" id="errBox" hidden></p>'; ?>

                <form action="save_complainant.php" method="POST" id="regForm" novalidate>
                    <div class="form-grid">
                        <div class="input-group">
                            <label>Full Name</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                                <input type="text" name="full_name" required>
                                <svg class="tick" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Contact Number</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>
                                <input type="text" name="contact_number" inputmode="tel" required>
                                <svg class="tick" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Email</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                                <input type="email" name="email" required>
                                <svg class="tick" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Address</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                <input type="text" name="address" required>
                                <svg class="tick" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Organization/Department</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 8h2M13 8h2M9 12h2M13 12h2M10 21v-4h4v4"/></svg>
                                <input type="text" name="organization_department" required>
                                <svg class="tick" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Preferred Contact Method</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M4 5h16v11H9l-5 4z"/></svg>
                                <select name="preferred_contact_method">
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="submit-btn" id="regBtn"><span class="spinner"></span><span class="btn-text">Register</span></button>
                    </div>
                </form>

                <div class="success-view" id="successView">
                    <svg class="check-circle" viewBox="0 0 100 100">
                        <circle class="ring" cx="50" cy="50" r="46" />
                        <path class="mark" d="M30 52l14 14 27-30" />
                    </svg>
                    <h3>Registered successfully!</h3>
                    <p>You can now file your complaint. We will take you there in a moment.</p>
                    <a class="go-btn" id="goBtn" href="complaint_register.php">File a Complaint</a>
                    <div class="redirect-bar"><span></span></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('regForm');
        const btn = document.getElementById('regBtn');
        const errBox = document.getElementById('errBox');
        const successView = document.getElementById('successView');
        const goBtn = document.getElementById('goBtn');

        // live validation: green check when a field looks good
        const rules = {
            full_name: v => v.trim().length >= 2,
            contact_number: v => v.replace(/\D/g, '').length >= 7,
            email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
            address: v => v.trim().length >= 3,
            organization_department: v => v.trim().length >= 2
        };
        function check(input, showInvalid) {
            const rule = rules[input.name];
            if (!rule) return true;
            const field = input.closest('.field');
            const ok = rule(input.value);
            field.classList.toggle('valid', ok);
            field.classList.toggle('invalid', !ok && showInvalid);
            return ok;
        }
        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => check(input, false));
            input.addEventListener('blur', () => { if (input.value !== '') check(input, true); });
        });

        function setLoading(on) {
            btn.disabled = on;
            btn.classList.toggle('loading', on);
            btn.querySelector('.btn-text').textContent = on ? 'Registering...' : 'Register';
        }
        function showError(msg) {
            errBox.textContent = msg;
            errBox.hidden = false;
            errBox.style.animation = 'none'; void errBox.offsetWidth; errBox.style.animation = '';
        }
        function showSuccess(nextUrl) {
            goBtn.href = nextUrl;
            successView.classList.add('show');
            setTimeout(() => { window.location.href = nextUrl; }, 4600);
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            let allOk = true;
            form.querySelectorAll('input').forEach(input => { if (!check(input, true)) allOk = false; });
            if (!allOk) { showError('Please complete all fields with valid details.'); return; }
            errBox.hidden = true;
            setLoading(true);
            try {
                const res = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
                const url = res.url || '';
                if (url.indexOf('new_complainant=') > -1) {
                    showSuccess(url);
                } else if (url.indexOf('status=error') > -1) {
                    setLoading(false);
                    showError('All required fields must be filled out.');
                } else if (url) {
                    window.location.href = url;
                } else {
                    setLoading(false);
                    showError('Something went wrong. Please try again.');
                }
            } catch (err) {
                setLoading(false);
                showError('Could not reach the server. Please try again.');
            }
        });
    </script>
</body>
</html>
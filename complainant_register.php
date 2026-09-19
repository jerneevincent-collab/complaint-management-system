<!DOCTYPE html>
<html>
<head>
    <title>Register Complainant</title>
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

        body.reg-page {
            background: linear-gradient(135deg, #eef2f9 0%, #dbe6f5 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            font-family: 'Poppins', sans-serif;
        }

        .reg-card {
            position: relative;
            width: 100%;
            max-width: 460px;
            background: var(--card-bg);
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(10, 37, 64, 0.15);
            border: 1px solid #e2e8f0;
            padding: 40px 36px;
            overflow: hidden;
            opacity: 0;
            transform: translateY(60px) scale(0.95);
            animation: revealCard 0.9s var(--elastic) 0.1s forwards;
        }
        @keyframes revealCard {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .glow {
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            background: var(--accent);
            filter: blur(90px);
            opacity: 0.18;
            pointer-events: none;
        }

        .reg-card .logo {
            display: block;
            width: 64px;
            height: 64px;
            margin: 0 auto 10px;
            border-radius: 50%;
            position: relative;
            z-index: 2;
        }

        .reg-card h2 {
            color: var(--navy);
            text-align: center;
            margin: 0 0 4px;
            font-size: 24px;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 2;
        }
        .reg-card .subtitle {
            color: var(--text);
            opacity: 0.8;
            text-align: center;
            font-size: 13px;
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
        }

        .reg-card form {
            box-shadow: none;
            padding: 0;
            max-width: 100%;
            margin-top: 0;
            background: transparent;
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: relative;
            z-index: 2;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }
        .input-group:nth-child(1) { animation-delay: 0.5s; }
        .input-group:nth-child(2) { animation-delay: 0.55s; }
        .input-group:nth-child(3) { animation-delay: 0.6s; }
        .input-group:nth-child(4) { animation-delay: 0.65s; }
        .input-group:nth-child(5) { animation-delay: 0.7s; }
        .input-group:nth-child(6) { animation-delay: 0.75s; }
        @keyframes fadeIn { to { opacity: 1; } }

        .input-group label {
            color: var(--navy);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
        }
        .input-group input, .input-group select {
            background: var(--input-bg);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 14px;
            color: var(--navy);
            outline: none;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .input-group input:focus, .input-group select:focus {
            border-color: var(--accent);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.12);
        }

        .reg-card button {
            background: var(--navy);
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            transition: 0.3s;
            width: 100%;
            opacity: 0;
            animation: fadeIn 0.5s ease 0.8s forwards;
        }
        .reg-card button:hover {
            background: var(--accent);
            transform: scale(1.02);
        }

        .success-msg { color: #16a34a !important; text-align: center; font-size: 13px; position: relative; z-index: 2; }
        .error-msg { color: #dc2626 !important; text-align: center; font-size: 13px; position: relative; z-index: 2; }
    </style>
</head>
<body class="reg-page">
    <div class="reg-card">
        <div class="glow"></div>
        <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
        <h2>Complainant Registration</h2>
        <p class="subtitle">No login required</p>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo '<p class="success-msg">Complainant registered successfully!</p>'; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'error') echo '<p class="error-msg">All required fields must be filled out.</p>'; ?>

        <form action="save_complainant.php" method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="input-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Address</label>
                <input type="text" name="address" required>
            </div>
            <div class="input-group">
                <label>Organization/Department</label>
                <input type="text" name="organization_department" required>
            </div>
            <div class="input-group">
                <label>Preferred Contact Method</label>
                <select name="preferred_contact_method">
                    <option value="email">Email</option>
                    <option value="phone">Phone</option>
                    <option value="sms">SMS</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
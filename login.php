<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tetapan Database (Sila ubah ikut nama database anda)
$host = 'localhost';                  
$db   = 'u509001196_Dhia123456780';   
$user = 'u509001196_Dhia123456780';  
$pass = 'A240904j000';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$login_error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Cari pengguna dalam jadual users
        $stmt = $conn->prepare("SELECT id, username, company_name, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Semak katalaluan
            if (password_verify($password, $row['password'])) {
                // Tetapkan sesi untuk pengasingan data syarikat (Tenant)
                $_SESSION['user_id']      = $row['id'];
                $_SESSION['username']     = $row['username'];
                $_SESSION['company_name'] = $row['company_name'];

                // Bawa ke halaman dashboard utama
                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = true;
            }
        } else {
            $login_error = true;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - HE AI Data Centre</title>
    <style>
        :root {
          --ink: #0A120E;
          --ink-2: #101B15;
          --gem: #17A468;
          --gem-bright: #2BD08C;
          --gold: #C6A45C;
          --gold-soft: #E3CD97;
          --text-inv: #EAF2EC;
          --text-inv-soft: #A9BDB1;
          --font-display: "Archivo", "Helvetica Neue", Arial, sans-serif;
          --font-body: "Inter", "Helvetica Neue", Arial, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
          font-family: var(--font-body);
          background: var(--ink);
          color: var(--text-inv);
          min-height: 100vh;
          display: flex;
        }

        .split-layout {
          display: flex;
          width: 100%;
          min-height: 100vh;
        }

        .split-left {
          flex: 1.2;
          position: relative;
          background-image: url('assets/img/campus-aerial.jpg');
          background-size: cover;
          background-position: center;
          padding: 3.5rem;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
        }

        .split-left::before {
          content: '';
          position: absolute;
          top: 0; left: 0; width: 100%; height: 100%;
          background: linear-gradient(135deg, rgba(10, 18, 14, 0.90) 0%, rgba(16, 27, 21, 0.75) 100%);
          z-index: 1;
        }

        .split-left > * {
          position: relative;
          z-index: 2;
        }

        .brand-header {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          text-decoration: none;
          color: var(--text-inv);
          font-weight: 700;
          font-family: var(--font-display);
          font-size: 0.95rem;
        }

        .brand-logo {
          height: 38px;
          width: auto;
        }

        .left-content h1 {
          font-family: var(--font-display);
          font-size: clamp(2.2rem, 3.5vw, 3.2rem);
          font-weight: 800;
          line-height: 1.1;
          margin-bottom: 1.2rem;
          letter-spacing: -0.02em;
        }

        .left-content h1 span {
          color: var(--gold-soft);
        }

        .left-content p {
          color: var(--text-inv-soft);
          font-size: 1rem;
          max-width: 42ch;
          line-height: 1.6;
        }

        .left-footer {
          font-size: 0.75rem;
          color: rgba(234, 242, 236, 0.5);
          letter-spacing: 0.1em;
        }

        .split-right {
          flex: 1;
          background: var(--ink-2);
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 3rem;
          border-left: 1px solid rgba(201, 207, 212, 0.1);
        }

        .form-card {
          width: 100%;
          max-width: 380px;
        }

        .form-card h2 {
          font-family: var(--font-display);
          font-size: 1.8rem;
          font-weight: 700;
          margin-bottom: 0.4rem;
          color: #FFFFFF;
        }

        .form-card p.subtitle {
          color: var(--text-inv-soft);
          font-size: 0.9rem;
          margin-bottom: 2rem;
        }

        .form-group {
          margin-bottom: 1.2rem;
        }

        .form-group label {
          display: block;
          font-size: 0.82rem;
          font-weight: 500;
          color: var(--text-inv-soft);
          margin-bottom: 0.4rem;
        }

        .form-group input {
          width: 100%;
          padding: 0.75rem 1rem;
          background: rgba(255, 255, 255, 0.04);
          border: 1px solid rgba(201, 207, 212, 0.15);
          border-radius: 6px;
          color: var(--text-inv);
          font-size: 0.95rem;
        }

        .form-group input:focus {
          outline: none;
          border-color: var(--gem);
        }

        .error-msg {
          color: #ff6b6b;
          font-size: 0.78rem;
          margin-top: 0.4rem;
          display: block;
        }

        .login-button {
          width: 100%;
          padding: 0.8rem;
          background: var(--gold);
          color: var(--ink);
          border: none;
          border-radius: 6px;
          font-weight: 600;
          font-size: 0.95rem;
          cursor: pointer;
          margin-top: 1rem;
          transition: background 0.2s;
        }

        .login-button:hover {
          background: var(--gold-soft);
        }

        .form-footer-links {
          margin-top: 1.5rem;
          text-align: center;
          font-size: 0.88rem;
          color: var(--text-inv-soft);
        }

        .form-footer-links a {
          color: var(--gem-bright);
          text-decoration: none;
          font-weight: 500;
        }

        .form-footer-links a:hover {
          text-decoration: underline;
        }

        .back-website {
          display: inline-block;
          margin-top: 1.5rem;
          font-size: 0.82rem;
          color: var(--text-inv-soft);
          text-decoration: none;
        }

        .back-website:hover {
          color: var(--text-inv);
        }

        @media (max-width: 768px) {
          .split-left { display: none; }
          .split-right { padding: 2rem; width: 100%; }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <div class="split-left">
        <a href="index.html" class="brand-header">
            <img src="assets/logo.png" alt="HE Logo" class="brand-logo">
            <span>HE AI DATA CENTRE</span>
        </a>

        <div class="left-content">
            <h1>AI infrastructure, <span>hosted in Bentong.</span></h1>
            <p>Deploy and manage high-performance liquid-cooled AI compute instances securely in Pahang's premier data hub.</p>
        </div>

        <div class="left-footer">
            &copy; 2026 HE AI Data Centre Sdn Bhd
        </div>
    </div>

    <div class="split-right">
        <div class="form-card">
            <h2>Log in</h2>
            <p class="subtitle">to your organisation's portal</p>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" placeholder="name@company.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    
                    <?php if ($login_error): ?>
                        <span class="error-msg">Invalid username or password. Please try again.</span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="login-button">Log in</button>
            </form>

            <div class="form-footer-links">
                Don't have an account? <a href="register.php">Register here</a>
            </div>

            <a href="index.html" class="back-website">← Back to website</a>
        </div>
    </div>
</div>

</body>
</html>

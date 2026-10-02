<?php


$host = 'localhost';
$db   = 'u509001196_Dhia123456780';   
$user = 'u509001196_Dhia123456780';  
$pass = 'A240904j000';  
$conn = new mysqli($host, $user, $pass, $db);


if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$username_error = false;

// Process form submission (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name    = trim($_POST['company_name']);
    $company_address = trim($_POST['company_address']);
    $username        = trim($_POST['username']);
    $password        = $_POST['password'];

    if (!empty($company_name) && !empty($company_address) && !empty($username) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database (id and created_at are automatically handled)
        $stmt = $conn->prepare("INSERT INTO users (company_name, company_address, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $company_name, $company_address, $username, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please sign in.'); window.location.href='login.html';</script>";
            exit();
        } else {
            // Check for duplicate entry error code (1062)
            if ($conn->errno == 1062) {
                $username_error = true;
            } else {
                echo "<script>alert('An error occurred: " . $stmt->error . "');</script>";
            }
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
    <title>Register - HE AI Data Centre</title>
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
          padding: 2.5rem 3rem;
          border-left: 1px solid rgba(201, 207, 212, 0.1);
          overflow-y: auto;
        }

        .form-card {
          width: 100%;
          max-width: 380px;
        }

        .form-card h2 {
          font-family: var(--font-display);
          font-size: 1.6rem;
          font-weight: 700;
          margin-bottom: 0.3rem;
          color: #FFFFFF;
        }

        .form-card p.subtitle {
          color: var(--text-inv-soft);
          font-size: 0.85rem;
          margin-bottom: 1.5rem;
        }

        .form-group {
          margin-bottom: 1rem;
        }

        .form-group label {
          display: block;
          font-size: 0.8rem;
          font-weight: 500;
          color: var(--text-inv-soft);
          margin-bottom: 0.3rem;
        }

        .form-group input, .form-group textarea {
          width: 100%;
          padding: 0.65rem 0.9rem;
          background: rgba(255, 255, 255, 0.04);
          border: 1px solid rgba(201, 207, 212, 0.15);
          border-radius: 6px;
          color: var(--text-inv);
          font-size: 0.9rem;
        }

        .form-group textarea {
          resize: vertical;
          height: 60px;
        }

        .form-group input:focus, .form-group textarea:focus {
          outline: none;
          border-color: var(--gem);
        }

        .error-msg {
          color: #ff6b6b;
          font-size: 0.75rem;
          margin-top: 0.2rem;
          display: block;
        }

        .login-button {
          width: 100%;
          padding: 0.75rem;
          background: var(--gold);
          color: var(--ink);
          border: none;
          border-radius: 6px;
          font-weight: 600;
          font-size: 0.9rem;
          cursor: pointer;
          margin-top: 0.8rem;
          transition: background 0.2s;
        }

        .login-button:hover {
          background: var(--gold-soft);
        }

        .form-footer-links {
          margin-top: 1.2rem;
          text-align: center;
          font-size: 0.85rem;
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
          display: block;
          text-align: center;
          margin-top: 1rem;
          font-size: 0.8rem;
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
            <h1>Join the future of <span>AI compute in Pahang.</span></h1>
            <p>Register your account for secure portal access to liquid-cooled hyperscale capacity in the Titiwangsa foothills.</p>
        </div>

        <div class="left-footer">
            &copy; 2026 HE AI Data Centre Sdn Bhd
        </div>
    </div>

    <div class="split-right">
        <div class="form-card">
            <h2>Create account</h2>
            <p class="subtitle">Register company profile & credentials</p>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" placeholder="e.g. Tenaga Nasional Berhad" required>
                </div>

                <div class="form-group">
                    <label for="company_address">Company Address</label>
                    <textarea id="company_address" name="company_address" placeholder="Enter company address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    
                    <?php if ($username_error): ?>
                        <span class="error-msg">⚠️ This username already exists. Please choose another one.</span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-button">Register account</button>
            </form>

            <div class="form-footer-links">
                Already have an account? <a href="login.html">Sign in here</a>
            </div>

            <a href="index.html" class="back-website">← Back to website</a>
        </div>
    </div>
</div>

</body>
</html>
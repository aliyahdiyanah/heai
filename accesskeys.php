<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";

$username = htmlspecialchars(
    $_SESSION["username"] ?? "User",
    ENT_QUOTES,
    "UTF-8"
);
$current_user_id = $_SESSION["user_id"];

$sshKeys = [
    ["name" => "ops-laptop", "type" => "ed25519", "fingerprint" => "SHA256:[FINGERPRINT]"],
    ["name" => "ci-runner", "type" => "ed25519", "fingerprint" => "SHA256:[FINGERPRINT]"],
    ["name" => "research-team", "type" => "rsa-4096", "fingerprint" => "SHA256:[FINGERPRINT]"]
];

$apiTokens = [
    ["name" => "terraform-prod", "scope" => "Instances: read/write", "expires" => "in 62 days"],
    ["name" => "finance-export", "scope" => "Billing: read", "expires" => "in 6 days", "warning" => true]
];

$teamMembers = [
    ["name" => "[Name]", "email" => "[email]", "role" => "Owner", "mfa" => "On", "status" => "active"],
    ["name" => "[Name]", "email" => "[email]", "role" => "Admin", "mfa" => "On", "status" => "active"],
    ["name" => "[Name]", "email" => "[email]", "role" => "Developer", "mfa" => "On", "status" => "active"],
    ["name" => "[Name]", "email" => "[email]", "role" => "Billing only", "mfa" => "Pending", "status" => "pending"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access & keys - HE AI Data Centre</title>
    <link rel="stylesheet" href="css/dashboard.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">

    <!-- SIDEBAR KIRI -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-header-row">
                <a href="dashboard.php" class="sidebar-brand">
                    <img src="assets/logo.png" alt="HE Logo" class="brand-logo" onerror="this.style.display='none'">
                    <span>HE AI FOUNDRY</span>
                </a>
                
            </div>

            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="launch.php">Launch instance</a></li>
                <li><a href="instances.php">Instances</a></li>
                <li><a href="biling.php">Billing & usage</a></li>
                <li><a href="accesskeys.php" class="active">Access & keys</a></li>
                <li><a href="support.php">Support</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="company"><?php echo $username; ?></div>
                <div class="id-label">Tenant ID: #<?php echo $current_user_id; ?></div>
            </div>
            <a href="logout.php" class="logout-btn" title="Logout">Log out</a>
        </div>
    </aside>

    <!-- KANDUNGAN UTAMA KANAN -->
    <main class="main-content">

        <!-- TOP BAR -->
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button id="mobileMenuToggle" class="action-icon-btn" style="display: none; cursor: pointer;" title="Open Menu">☰</button>
                <h1>Access & keys</h1>
            </div>
            <div class="topbar-actions">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" placeholder="Search instances, invoices...">
                </div>
                <button class="action-icon-btn">🔔</button>
                <a href="launch.php" class="launch-btn" style="text-decoration: none; display: inline-flex; align-items: center;">+ Launch GPU</a>
            </div>
        </header>

        <!-- KANDUNGAN UTAMA PAGE ACCESS & KEYS -->
        <div class="access-keys-grid" style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-top: 1.5rem;">
            
            <!-- KOLUM KIRI -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- SSH Keys -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h3 style="color: #ffffff; font-size: 1.1rem; margin-bottom: 4px;">SSH keys</h3>
                            <span style="font-size: 0.8rem; color: var(--text-inv-soft);">Added to new instances at launch</span>
                        </div>
                        <button class="launch-btn" style="padding: 6px 14px; font-size: 0.85rem;">+ Add key</button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($sshKeys as $ssh): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                            <div>
                                <strong style="color: #fff; font-size: 0.95rem; display: block; margin-bottom: 2px;"><?php echo $ssh['name']; ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-inv-soft); font-family: monospace;"><?php echo $ssh['type']; ?> &bull; <?php echo $ssh['fingerprint']; ?></span>
                            </div>
                            <button style="background: rgba(255,255,255,0.08); border: none; color: #fff; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">Remove</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- API Tokens -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h3 style="color: #ffffff; font-size: 1.1rem; margin-bottom: 4px;">API tokens</h3>
                            <span style="font-size: 0.8rem; color: var(--text-inv-soft);">Automate launches and billing exports</span>
                        </div>
                        <button class="launch-btn" style="padding: 6px 14px; font-size: 0.85rem;">+ Create token</button>
                    </div>

                    <div class="table-wrapper">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--text-inv-soft);">
                                    <th style="padding-bottom: 8px;">Name</th>
                                    <th style="padding-bottom: 8px;">Scope</th>
                                    <th style="padding-bottom: 8px;">Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apiTokens as $token): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 10px 0; color: #fff; font-weight: 600;"><?php echo $token['name']; ?></td>
                                    <td style="padding: 10px 0; color: var(--text-inv-soft);"><?php echo $token['scope']; ?></td>
                                    <td style="padding: 10px 0; color: <?php echo isset($token['warning']) ? 'var(--gold-soft, #f39c12)' : 'var(--text-inv-soft)'; ?>;"><?php echo $token['expires']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- KOLUM KANAN -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- Team Members -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h3 style="color: #ffffff; font-size: 1.1rem; margin-bottom: 4px;">Team members</h3>
                            <span style="font-size: 0.8rem; color: var(--text-inv-soft);">4 of [X] seats &bull; MFA required for all</span>
                        </div>
                        <button class="launch-btn" style="padding: 6px 14px; font-size: 0.85rem;">Invite member</button>
                    </div>

                    <div class="table-wrapper">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--text-inv-soft);">
                                    <th style="padding-bottom: 8px;">Member</th>
                                    <th style="padding-bottom: 8px;">Role</th>
                                    <th style="padding-bottom: 8px;">MFA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teamMembers as $member): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #fff; display: block;"><?php echo $member['name']; ?></strong>
                                        <span style="font-size: 0.75rem; color: var(--text-inv-soft);"><?php echo $member['email']; ?></span>
                                    </td>
                                    <td style="padding: 10px 0; color: var(--text-inv);"><?php echo $member['role']; ?></td>
                                    <td style="padding: 10px 0;">
                                        <span style="color: <?php echo $member['mfa'] == 'On' ? '#2ecc71' : '#f39c12'; ?>; font-weight: 600;">
                                            <?php echo $member['mfa']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Roles Info -->
                <div class="metric-card" style="padding: 1.25rem; text-align: left; display: block; background: rgba(0,0,0,0.2);">
                    <h4 style="color: #fff; font-size: 0.95rem; margin-bottom: 8px;">Roles</h4>
                    <div style="font-size: 0.8rem; color: var(--text-inv-soft); display: flex; flex-direction: column; gap: 6px; line-height: 1.4;">
                        <div><strong style="color: #ddd;">Owner:</strong> everything, incl. billing and members</div>
                        <div><strong style="color: #ddd;">Admin:</strong> instances, keys, tokens</div>
                        <div><strong style="color: #ddd;">Developer:</strong> launch and use instances</div>
                        <div><strong style="color: #ddd;">Billing only:</strong> invoices and usage</div>
                    </div>
                </div>

            </div>

        </div>

    </main>

</div>

<!-- SCRIPT -->
<script>
    const appLayout = document.querySelector('.app-layout');
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const mobileMenuToggleBtn = document.getElementById('mobileMenuToggle');

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function () {
            appLayout.classList.toggle('sidebar-collapsed');
        });
    }

    function checkScreenSize() {
        if (window.innerWidth <= 768) {
            if (mobileMenuToggleBtn) mobileMenuToggleBtn.style.display = 'inline-flex';
            appLayout.classList.add('sidebar-collapsed');
        } else {
            if (mobileMenuToggleBtn) mobileMenuToggleBtn.style.display = 'none';
            appLayout.classList.remove('sidebar-collapsed');
        }
    }

    window.addEventListener('resize', checkScreenSize);
    checkScreenSize();

    if (mobileMenuToggleBtn) {
        mobileMenuToggleBtn.addEventListener('click', function () {
            appLayout.classList.toggle('sidebar-collapsed');
        });
    }
</script>
<script src="js/dashboard.js"></script>

</body>
</html>
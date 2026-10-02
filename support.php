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

// Contoh data tiket sokongan sedia ada (simulasi paparan)
$myTickets = [
    ["title" => "Slow NCCL throughput across nodes", "id" => "[TICKET-ID]", "severity" => "P2", "status" => "In progress", "class" => "color: #3498db;"],
    ["title" => "Request 4 more H200 quota", "id" => "[TICKET-ID]", "severity" => "P4", "status" => "Awaiting you", "class" => "color: #f39c12;"],
    ["title" => "Invoice address change", "id" => "[TICKET-ID]", "severity" => "P4", "status" => "Resolved", "class" => "color: var(--text-inv-soft);"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - HE AI Data Centre</title>
    <!-- Guna fail CSS luaran tema HE AI yang sama -->
    <link rel="stylesheet" href="css/dashboard.css?v=3">
    <!-- FontAwesome untuk ikon -->
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
                <li><a href="accesskeys.php">Access & keys</a></li>
                <li><a href="support.php" class="active">Support</a></li>
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
                <h1>Support</h1>
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

        <!-- KANDUNGAN UTAMA PAGE SUPPORT (2 KOLUM GRID) -->
        <div class="support-grid" style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 24px; margin-top: 1.5rem;">
            
            <!-- KOLUM KIRI: Borang Raise a Ticket -->
            <div class="metric-card" style="padding: 1.75rem; text-align: left; display: block;">
                <h3 style="color: #ffffff; font-size: 1.15rem; margin-bottom: 1.25rem;">Raise a ticket</h3>
                
                <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    <!-- Subject -->
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-inv-soft); margin-bottom: 6px;">Subject</label>
                        <input type="text" placeholder="Short summary of the issue" style="width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem;">
                    </div>

                    <!-- Category & Affected instance (2 kolum dalam borang) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-inv-soft); margin-bottom: 6px;">Category</label>
                            <select style="width: 100%; padding: 10px 14px; background: #151922; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem;">
                                <option>Instance / GPU issue</option>
                                <option>Billing & Payment</option>
                                <option>Access & Security</option>
                                <option>General Question</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: var(--text-inv-soft); margin-bottom: 6px;">Affected instance</label>
                            <select style="width: 100%; padding: 10px 14px; background: #151922; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem;">
                                <option>llm-finetune-01</option>
                                <option>gpu-cluster-02</option>
                                <option>None / General</option>
                            </select>
                        </div>
                    </div>

                    <!-- Severity Radio Options -->
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-inv-soft); margin-bottom: 8px;">Severity</label>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                            <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 10px; border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px; color: #fff; font-size: 0.85rem; font-weight: 600;">
                                    <input type="radio" name="severity" value="P1"> P1
                                </div>
                                <span style="font-size: 0.7rem; color: var(--text-inv-soft);">Service down</span>
                            </label>

                            <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 10px; border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px; color: #fff; font-size: 0.85rem; font-weight: 600;">
                                    <input type="radio" name="severity" value="P2"> P2
                                </div>
                                <span style="font-size: 0.7rem; color: var(--text-inv-soft);">Degraded</span>
                            </label>

                            <!-- P3 Selected Default Style -->
                            <label style="background: rgba(255,87,34,0.08); border: 1px solid #ff5722; padding: 10px; border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px; color: #fff; font-size: 0.85rem; font-weight: 600;">
                                    <input type="radio" name="severity" value="P3" checked> P3
                                </div>
                                <span style="font-size: 0.7rem; color: var(--text-inv-soft);">Minor issue</span>
                            </label>

                            <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 10px; border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px; color: #fff; font-size: 0.85rem; font-weight: 600;">
                                    <input type="radio" name="severity" value="P4"> P4
                                </div>
                                <span style="font-size: 0.7rem; color: var(--text-inv-soft);">Question</span>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: var(--text-inv-soft); margin-bottom: 6px;">Description</label>
                        <textarea rows="5" placeholder="What happened, when, and any error messages" style="width: 100%; padding: 12px 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem; resize: vertical;"></textarea>
                    </div>

                    <!-- Attachments & Submit Button -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <button type="button" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-inv-soft); padding: 8px 14px; border-radius: 8px; font-size: 0.8rem; cursor: pointer;">
                            Attach logs or screenshots
                        </button>
                        <button type="submit" class="launch-btn" style="padding: 10px 22px; font-size: 0.9rem;">Submit ticket</button>
                    </div>

                </form>
            </div>

            <!-- KOLUM KANAN: My Tickets, Guides & Urgent Hotline -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <!-- My Tickets Card -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #ffffff; font-size: 1.05rem; margin-bottom: 1rem;">My tickets</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($myTickets as $ticket): ?>
                        <div style="padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <strong style="color: #fff; font-size: 0.9rem; display: block; margin-bottom: 3px;"><?php echo $ticket['title']; ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-inv-soft);"><?php echo $ticket['id']; ?> &bull; <?php echo $ticket['severity']; ?></span>
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 600; <?php echo $ticket['class']; ?>"><?php echo $ticket['status']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Guides Card -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #ffffff; font-size: 1.05rem; margin-bottom: 1rem;">Guides</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem;">
                        <a href="#" style="color: #ff5722; text-decoration: none; display: flex; justify-content: space-between; align-items: center;">Getting started with your first instance <span>→</span></a>
                        <a href="#" style="color: #ff5722; text-decoration: none; display: flex; justify-content: space-between; align-items: center;">Connecting over SSH <span>→</span></a>
                        <a href="#" style="color: #ff5722; text-decoration: none; display: flex; justify-content: space-between; align-items: center;">Service levels and support hours <span>→</span></a>
                    </div>
                </div>

                <!-- Urgent Hotline Card -->
                <div class="metric-card" style="padding: 1.25rem; text-align: left; display: block; background: rgba(0,0,0,0.2);">
                    <div style="font-size: 0.75rem; color: var(--text-inv-soft); letter-spacing: 0.5px; margin-bottom: 6px;">URGENT? CALL 24/7 SUPPORT</div>
                    <div style="color: #fff; font-size: 1.1rem; font-weight: 700; font-family: monospace; margin-bottom: 4px;">[HOTLINE NUMBER]</div>
                    <div style="font-size: 0.75rem; color: var(--text-inv-soft);">[support email]</div>
                </div>

            </div>

        </div>

    </main>

</div>

<!-- SCRIPT SIDEBAR TOGGLE -->
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
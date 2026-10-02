<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";

$username = htmlspecialchars($_SESSION["username"] ?? "User", ENT_QUOTES, "UTF-8");
$current_user_id = $_SESSION["user_id"];

$gpuQuery = "SELECT * FROM gpus WHERE user_id = ? ORDER BY id ASC";
$stmt = $conn->prepare($gpuQuery);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$gpuResult = $stmt->get_result();

$gpus = [];
if ($gpuResult) {
    while ($row = $gpuResult->fetch_assoc()) {
        $gpus[] = $row;
    }
}
$stmt->close();

$totalGPUs = count($gpus);
$totalUtilization = 0;
$totalMemoryUsed = 0;
$totalMemory = 0;
$totalTemperature = 0;

foreach ($gpus as $gpu) {
    $totalUtilization += (float)$gpu["utilization"];
    $totalMemoryUsed += (float)$gpu["memory_used"];
    $totalMemory += (float)$gpu["memory_total"];
    $totalTemperature += (float)$gpu["temperature"];
}

$averageUtilization = $totalGPUs > 0 ? round($totalUtilization / $totalGPUs) : 0;

$usageQuery = "
    SELECT
        gpu_usage.gpu_id,
        gpus.gpu_name,
        gpu_usage.utilization,
        gpu_usage.memory_used,
        gpu_usage.temperature,
        gpu_usage.recorded_at
    FROM gpu_usage
    INNER JOIN gpus
        ON gpu_usage.gpu_id = gpus.id
    WHERE gpus.user_id = ?
    ORDER BY gpu_usage.recorded_at ASC
";

$stmtUsage = $conn->prepare($usageQuery);
$stmtUsage->bind_param("i", $current_user_id);
$stmtUsage->execute();
$usageResult = $stmtUsage->get_result();

$usageData = [];
if ($usageResult) {
    while ($row = $usageResult->fetch_assoc()) {
        $usageData[] = $row;
    }
}
$stmtUsage->close();

$dashboardData = [
    "gpus" => $gpus,
    "usage" => $usageData
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HE AI Data Centre</title>
    <link rel="stylesheet" href="css/dashboard.css?v=4">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- CSS TAMBAHAN UTK PASTIKAN SIDEBAR BOLEH TUTUP (HIDDEN) -->
    <style>
        /* Bila kelas sidebar-collapsed aktif, sidebar akan disembunyikan dan main-content jadi penuh */
        .app-layout.sidebar-collapsed .sidebar {
            display: none !important;
        }
        .app-layout.sidebar-collapsed .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            flex: 1;
        }
        #sidebarToggle {
            cursor: pointer;
            padding: 6px 12px;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: inherit;
            border-radius: 6px;
        }
    </style>
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="launch.php">Launch instance</a></li>
                <li><a href="instances.php">Instances</a></li>
                <li><a href="biling.php">Billing & usage</a></li>
                <li><a href="accesskeys.php">Access & keys</a></li>
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
                <button id="sidebarToggle" title="Toggle Sidebar">☰</button>
                <h1>Dashboard</h1>
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

        <!-- DEMO NOTICE -->
        <div class="demo-notice">
            <strong>Demo Data</strong>
            <span>GPU monitoring data is currently using sample data for system demonstration and testing.</span>
        </div>

        <!-- 4 KOTAK METRIK UTAMA -->
        <section class="metrics-grid">
            <div class="metric-card">
                <div class="title">Running instances</div>
                <div class="value"><?php echo $totalGPUs; ?></div>
                <div class="sub-value">Active nodes</div>
            </div>
            <div class="metric-card">
                <div class="title">GPUs in use</div>
                <div class="value"><?php echo $totalGPUs; ?> <span style="font-size: 1rem; color: var(--text-inv-soft);">/ 8 quota</span></div>
                <div class="sub-value">Allocated compute</div>
            </div>
            <div class="metric-card">
                <div class="title">Month-to-date spend</div>
                <div class="value" style="color: var(--gold-soft);">USD 4,250</div>
                <div class="sub-value">1 – 25 Sep</div>
            </div>
            <div class="metric-card">
                <div class="title">Avg GPU utilisation</div>
                <div class="value"><?php echo $averageUtilization; ?>%</div>
                <div class="sub-value">Last 7 days</div>
            </div>
        </section>

        <!-- BAHAGIAN TENGAH (GRAF & KOMITMEN) -->
        <section class="middle-grid">
            <div class="chart-card">
                <h3>GPU utilisation · last 7 days</h3>
                <div class="chart-container">
                    <canvas id="utilizationChart"></canvas>
                </div>
            </div>

            <div class="commitment-card">
                <h3>Monthly commitment</h3>
                <div class="commitment-value">78% <span>used</span></div>
                <div class="progress-track">
                    <div class="progress-fill-gold" style="width: 78%;"></div>
                </div>
                <div class="commitment-details">
                    <div class="comm-row"><span>Committed</span> <span>USD 9,600</span></div>
                    <div class="comm-row"><span>Used</span> <span>USD 4,250</span></div>
                    <div class="comm-row"><span>Remaining</span> <span>USD 5,350</span></div>
                </div>
                <a href="#" class="view-billing-link">View billing →</a>
            </div>
        </section>

        <!-- BAHAGIAN BAWAH -->
        <section class="bottom-grid-layout">
            <div class="table-card">
                <div class="card-header-flex">
                    <h3>Instances</h3>
                    <a href="#" class="view-all-link">View all →</a>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>GPU</th>
                                <th>Utilization</th>
                                <th>Memory</th>
                                <th>Temp</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($gpus) > 0): ?>
                            <?php foreach ($gpus as $gpu): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($gpu["gpu_name"]); ?></strong></td>
                                    <td><?php echo htmlspecialchars($gpu["model"]); ?></td>
                                    <td>
                                        <div class="metric-cell">
                                            <span><?php echo $gpu["utilization"]; ?>%</span>
                                            <div class="progress-bar-mini">
                                                <div class="progress-fill" style="width: <?php echo $gpu["utilization"]; ?>%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $gpu["memory_used"]; ?> / <?php echo $gpu["memory_total"]; ?> GB</td>
                                    <td><?php echo $gpu["temperature"]; ?>°C</td>
                                    <td>
                                        <span class="status-badge">
                                            <span class="status-dot"></span> 
                                            <?php echo htmlspecialchars($gpu["status"]); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-inv-soft); padding: 2rem;">No GPU instances found for your organization.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kotak Notis Kanan -->
            <div class="notices-card">
                <h3>Notices</h3>
                <div class="notice-item">
                    <span class="notice-tag maintenance">MAINTENANCE</span>
                    <p>IPDC Bentong · 02:00–04:00 MYT</p>
                </div>
                <div class="notice-item">
                    <span class="notice-tag new">NEW</span>
                    <p>B300 instances coming 2027 — register interest</p>
                </div>
                <div class="notice-item">
                    <span class="notice-tag security">SECURITY</span>
                    <p>Rotate API tokens every 90 days</p>
                </div>
            </div>
        </section>

    </main>

</div>

<!-- SCRIPT JAVASCRIPT UNTUK TOGGLE -->
<script>
    window.dashboardData = <?php echo json_encode($dashboardData, JSON_NUMERIC_CHECK); ?>;

    const sidebarToggle = document.getElementById('sidebarToggle');
    const appLayout = document.querySelector('.app-layout');

    if (sidebarToggle && appLayout) {
        sidebarToggle.addEventListener('click', function () {
            appLayout.classList.toggle('sidebar-collapsed');
            
            if (appLayout.classList.contains('sidebar-collapsed')) {
                sidebarToggle.innerText = '❮';
                sidebarToggle.title = "Open Sidebar";
            } else {
                sidebarToggle.innerText = '☰';
                sidebarToggle.title = "Close Sidebar";
            }
        });
    }
</script>
<script src="js/dashboard.js"></script>

</body>
</html>
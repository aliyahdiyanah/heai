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

/*
|--------------------------------------------------------------------------
| Get User Instances (Dari Jadual 'instances')
|--------------------------------------------------------------------------
*/
$gpuQuery = "SELECT * FROM instances WHERE user_id = ? ORDER BY id ASC";
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

// Kira jumlah running & stopped secara dinamik
$totalInstances = count($gpus);
$runningCount = 0;
$stoppedCount = 0;

foreach ($gpus as $gpu) {
    if (strtolower($gpu['status']) === 'running') {
        $runningCount++;
    } else {
        $stoppedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instances - HE AI Data Centre</title>
    <link rel="stylesheet" href="css/dashboard.css?v=4">
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
                <li><a href="instances.php" class="active">Instances</a></li>
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
            <h1>Instances</h1>
            <div class="topbar-actions">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" placeholder="Search instances, invoices...">
                </div>
                <button class="action-icon-btn">🔔</button>
                <a href="launch.php" class="launch-btn" style="text-decoration: none; display: inline-flex; align-items: center; background: var(--gold); color: var(--ink); padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 0.85rem;">+ Launch GPU</a>
            </div>
        </header>

        <!-- INSTANCES LAYOUT GRID -->
        <div class="instances-layout" style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: start;">
            
            <!-- KIRI: SENARAI INSTANCE -->
            <div class="instances-list-card" style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                
                <!-- Filter Badges -->
                <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                    <button class="filter-tab active" style="background: var(--ink-2); border: 1px solid var(--gem-bright); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; cursor: pointer;">All · <?php echo $totalInstances; ?></button>
                    <button class="filter-tab" style="background: var(--ink-2); border: 1px solid var(--border-color); color: var(--text-inv-soft); padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; cursor: pointer;">Running · <?php echo $runningCount; ?></button>
                    <button class="filter-tab" style="background: var(--ink-2); border: 1px solid var(--border-color); color: var(--text-inv-soft); padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; cursor: pointer;">Stopped · <?php echo $stoppedCount; ?></button>
                </div>

                <!-- Table Senarai -->
                <div class="table-wrapper">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                        <thead>
                            <tr style="color: var(--text-inv-soft); border-bottom: 1px solid var(--border-color);">
                                <th style="padding-bottom: 12px;">Name</th>
                                <th style="padding-bottom: 12px;">GPU</th>
                                <th style="padding-bottom: 12px;">Data centre</th>
                                <th style="padding-bottom: 12px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($gpus) > 0): ?>
                            <?php foreach ($gpus as $index => $gpu): ?>
                                <tr class="instance-row" onclick="selectInstance(<?php echo $index; ?>)" style="border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(23,164,104,0.05)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 14px 0;"><strong><?php echo htmlspecialchars($gpu["instance_name"]); ?></strong></td>
                                    <td style="padding: 14px 0; color: var(--text-inv-soft);"><?php echo htmlspecialchars($gpu["gpu_count"]); ?>× <?php echo htmlspecialchars($gpu["gpu_model"]); ?></td>
                                    <td style="padding: 14px 0; color: var(--text-inv-soft);"><?php echo htmlspecialchars($gpu["data_centre"]); ?></td>
                                    <td style="padding: 14px 0;">
                                        <span style="display: inline-flex; align-items: center; gap: 6px; color: <?php echo (strtolower($gpu['status']) === 'running') ? 'var(--gem-bright)' : 'var(--text-inv-soft)'; ?>;">
                                            <span style="width: 7px; height: 7px; border-radius: 50%; background: currentColor;"></span>
                                            <?php echo htmlspecialchars($gpu["status"]); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-inv-soft); padding: 2rem;">No instances found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KANAN: DETAIL PANEL & LIVE UTILIZATION -->
            <div class="instance-detail-card" style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                
                <?php if (count($gpus) > 0): $first = $gpus[0]; ?>
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 id="detailName" style="font-family: var(--font-display); font-size: 1.1rem; color: #fff; margin-bottom: 4px;"><?php echo htmlspecialchars($first["instance_name"]); ?></h3>
                        <span id="detailStatusHeader" style="font-size: 0.78rem; color: var(--gem-bright);">● <?php echo htmlspecialchars($first["status"]); ?></span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button style="background: var(--ink-2); border: 1px solid var(--border-color); color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 0.78rem; cursor: pointer;">Stop</button>
                        <button style="background: var(--ink-2); border: 1px solid var(--border-color); color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 0.78rem; cursor: pointer;">Restart</button>
                        <a href="terminate.php?id=<?php echo $first['id']; ?>" onclick="return confirm('Are you sure you want to delete this instance?');" style="background: rgba(255,107,107,0.15); border: 1px solid rgba(255,107,107,0.3); color: #ff6b6b; padding: 6px 12px; border-radius: 4px; font-size: 0.78rem; text-decoration: none;">Terminate</a>
                    </div>
                </div>

                <!-- SSH Box -->
                <div style="background: var(--ink-2); border: 1px solid var(--border-color); border-radius: 6px; padding: 12px;">
                    <div style="font-size: 0.75rem; color: var(--text-inv-soft); margin-bottom: 6px;">Connect via SSH</div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 0.8rem; color: var(--gold-soft);">
                        <span id="detailSsh">ssh ubuntu@<?php echo htmlspecialchars($first["ssh_key"]); ?></span>
                        <button style="background: var(--ink-card); border: 1px solid var(--border-color); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">Copy</button>
                    </div>
                </div>

                <a href="#" style="color: var(--gem-bright); font-size: 0.82rem; text-decoration: none;">Open web terminal →</a>

                <!-- Specs Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.82rem; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 12px 0;">
                    <div>
                        <div style="color: var(--text-inv-soft); font-size: 0.75rem;">GPU</div>
                        <strong id="detailModel" style="color: #fff;"><?php echo htmlspecialchars($first["gpu_count"]); ?>× <?php echo htmlspecialchars($first["gpu_model"]); ?></strong>
                    </div>
                    <div>
                        <div style="color: var(--text-inv-soft); font-size: 0.75rem;">Data centre</div>
                        <strong id="detailDc" style="color: #fff;"><?php echo htmlspecialchars($first["data_centre"]); ?></strong>
                    </div>
                    <div>
                        <div style="color: var(--text-inv-soft); font-size: 0.75rem;">Software Image</div>
                        <strong id="detailSoftware" style="color: #fff;"><?php echo htmlspecialchars($first["software_image"]); ?></strong>
                    </div>
                    <div>
                        <div style="color: var(--text-inv-soft); font-size: 0.75rem;">Est. Cost</div>
                        <strong id="detailCost" style="color: #fff;">USD <?php echo number_format($first["estimated_cost"], 0); ?>/mo</strong>
                    </div>
                </div>

                <!-- Live Utilization Bars -->
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #fff; margin-bottom: 10px;">GPU utilisation · live</div>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-inv-soft);">GPU 0</span>
                            <div style="flex-grow: 1; margin: 0 12px; background: var(--ink-2); height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="background: var(--gold); width: 91%; height: 100%;"></div>
                            </div>
                            <span style="font-weight: 600;">91%</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-inv-soft);">GPU 1</span>
                            <div style="flex-grow: 1; margin: 0 12px; background: var(--ink-2); height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="background: var(--gold); width: 88%; height: 100%;"></div>
                            </div>
                            <span style="font-weight: 600;">88%</span>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-inv-soft); padding: 2rem;">No instance selected.</div>
                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

<script>
    // Data instance dari PHP untuk kawalan klik interaktif pada panel kanan
    const gpuData = <?php echo json_encode($gpus, JSON_NUMERIC_CHECK); ?>;

    function selectInstance(index) {
        const item = gpuData[index];
        if(item) {
            document.getElementById('detailName').innerText = item.instance_name;
            document.getElementById('detailStatusHeader').innerText = '● ' + item.status;
            document.getElementById('detailSsh').innerText = 'ssh ubuntu@' + item.ssh_key;
            document.getElementById('detailModel').innerText = item.gpu_count + '× ' + item.gpu_model;
            document.getElementById('detailDc').innerText = item.data_centre;
            document.getElementById('detailSoftware').innerText = item.software_image;
            document.getElementById('detailCost').innerText = 'USD ' + Number(item.estimated_cost).toLocaleString() + '/mo';
        }
    }
</script>

</body>
</html>
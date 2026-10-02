<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";

$username = htmlspecialchars($_SESSION["username"] ?? "User", ENT_QUOTES, 'UTF-8');
$current_user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get User Billing / Instances Data
|--------------------------------------------------------------------------
*/
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing & usage - TM AI Foundry</title>
    <link rel="stylesheet" href="css/dashboard.css?v=4">
</head>
<body>

<div class="app-layout">

    <!-- SIDEBAR KIRI -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-header-row">
                <a href="dashboard.php" class="sidebar-brand">
                    <img src="assets/logo.png" alt="Logo" class="brand-logo">
                    <span>HE AI FOUNDRY</span>
                </a>
            </div>

            <ul class="sidebar-menu">
                <li><a href="dashboard.php" >Dashboard</a></li>
                <li><a href="launch.php">Launch instance</a></li>
                <li><a href="instances.php" >Instances</a></li>
                <li><a href="biling.php" class="active">Billing & usage</a></li>
                <li><a href="accesskeys.php">Access & keys</a></li>
                <li><a href="support.php">Support</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="company">aliyah</div>
                <div class="id-label">Tenant ID: #1</div>
            </div>
            <a href="logout.php" class="logout-btn">Log out</a>
        </div>
    </aside>

    <!-- KANDUNGAN UTAMA KANAN -->
    <main class="main-content">

        <!-- TOP BAR -->
        <header class="top-bar">
            <h1>Billing & usage</h1>
            <div class="topbar-actions">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" placeholder="Search instances, invoices...">
                </div>
                <button class="action-icon-btn">🔔</button>
                <a href="launch.php" class="launch-btn" style="text-decoration: none; display: inline-flex; align-items: center; background: var(--gold); color: var(--ink); padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 0.85rem;">+ Launch GPU</a>
            </div>
        </header>

        <!-- TOP SUMMARY CARDS -->
        <div style="display: grid; grid-template-columns: 1.4fr 1.2fr 1.2fr; gap: 20px; margin-bottom: 24px;">
            
            <!-- Kad 1: Month to date -->
            <div style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <div style="font-size: 0.8rem; color: var(--text-inv-soft); margin-bottom: 8px;">Month to date · September</div>
                <div style="font-family: var(--font-display); font-size: 1.8rem; font-weight: 700; color: #fff; margin-bottom: 6px;">USD 7,480</div>
                <div style="font-size: 0.78rem; color: var(--text-inv-soft);">78% of USD 9,600 commitment</div>
            </div>

            <!-- Kad 2: Next invoice -->
            <div style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <div style="font-size: 0.8rem; color: var(--text-inv-soft); margin-bottom: 8px;">Next invoice</div>
                <div style="font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 6px;">1 Oct 2026</div>
                <div style="font-size: 0.78rem; color: var(--text-inv-soft);">Sent to [billing email]</div>
            </div>

            <!-- Kad 3: Payment terms -->
            <div style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-inv-soft); margin-bottom: 8px;">Payment terms</div>
                    <div style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 6px;">Invoice · Net 30</div>
                </div>
                <a href="#" style="color: var(--gem-bright); font-size: 0.8rem; text-decoration: none;">Update billing details →</a>
            </div>

        </div>

        <!-- MAIN LAYOUT (GRID 2 SEKSYEN) -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
            
            <!-- KIRI: USAGE THIS MONTH -->
            <div style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="font-size: 0.95rem; font-weight: 600; color: #fff;">Usage this month</h3>
                    <button style="background: var(--ink-2); border: 1px solid var(--border-color); color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; cursor: pointer;">Export CSV</button>
                </div>

                <div class="table-wrapper">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                        <thead>
                            <tr style="color: var(--text-inv-soft); border-bottom: 1px solid var(--border-color);">
                                <th style="padding-bottom: 12px;">Instance</th>
                                <th style="padding-bottom: 12px;">GPU</th>
                                <th style="padding-bottom: 12px;">Days</th>
                                <th style="padding-bottom: 12px;">Rate</th>
                                <th style="padding-bottom: 12px; text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 0; color: #fff; font-weight: 600;">inference-api</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">2× L40S</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">24</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">USD 1,800 /GPU/mo</td>
                                <td style="padding: 14px 0; text-align: right; color: #fff;">2,880.00</td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 0; color: #fff; font-weight: 600;">dev-sandbox</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">1× L40S</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">24</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">USD 1,800 /GPU/mo</td>
                                <td style="padding: 14px 0; text-align: right; color: #fff;">1,440.00</td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 0; color: #fff; font-weight: 600;">llm-finetune-01</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">4× H200</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">6</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">USD 3,000 /GPU/mo</td>
                                <td style="padding: 14px 0; text-align: right; color: #fff;">2,400.00</td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 0; color: #fff; font-weight: 600;">render-batch</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">1× RTX Pro 6000</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">2</td>
                                <td style="padding: 14px 0; color: var(--text-inv-soft);">[PRICE] /GPU/mo</td>
                                <td style="padding: 14px 0; text-align: right; color: var(--text-inv-soft);">[AMOUNT]</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total Row -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border-color); font-size: 0.88rem;">
                    <span style="font-weight: 600; color: #fff;">Total (excl. SST)</span>
                    <span style="font-weight: 700; color: #fff; font-size: 1rem;">USD 7,480.00</span>
                </div>
            </div>

            <!-- KANAN: INVOICES -->
            <div style="background: var(--ink-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <h3 style="font-size: 0.95rem; font-weight: 600; color: #fff; margin-bottom: 16px;">Invoices</h3>

                <div style="display: flex; flex-direction: column; gap: 14px; font-size: 0.83rem;">
                    <!-- Invois 1 -->
                    <div style="padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-weight: 600; color: #fff; margin-bottom: 2px;">[INV-NO]</div>
                            <div style="font-size: 0.75rem; color: var(--text-inv-soft);">August 2026 · USD [AMOUNT]</div>
                        </div>
                        <div style="text-align: right; display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--gold);">Due</span>
                            <a href="#" style="font-size: 0.75rem; color: var(--gem-bright); text-decoration: none;">PDF</a>
                        </div>
                    </div>

                    <!-- Invois 2 -->
                    <div style="padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-weight: 600; color: #fff; margin-bottom: 2px;">[INV-NO]</div>
                            <div style="font-size: 0.75rem; color: var(--text-inv-soft);">July 2026 · USD [AMOUNT]</div>
                        </div>
                        <div style="text-align: right; display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--gem-bright);">Paid</span>
                            <a href="#" style="font-size: 0.75rem; color: var(--gem-bright); text-decoration: none;">PDF</a>
                        </div>
                    </div>

                    <!-- Invois 3 -->
                    <div style="padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-weight: 600; color: #fff; margin-bottom: 2px;">[INV-NO]</div>
                            <div style="font-size: 0.75rem; color: var(--text-inv-soft);">June 2026 · USD [AMOUNT]</div>
                        </div>
                        <div style="text-align: right; display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--gem-bright);">Paid</span>
                            <a href="#" style="font-size: 0.75rem; color: var(--gem-bright); text-decoration: none;">PDF</a>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 16px;">
                    <a href="#" style="color: var(--gem-bright); font-size: 0.82rem; text-decoration: none;">All invoices →</a>
                </div>
            </div>

        </div>

    </main>

</div>

</body>
</html>
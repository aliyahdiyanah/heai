<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config.php"; // Menggunakan $conn (MySQLi)

$username = htmlspecialchars($_SESSION["username"] ?? "User", ENT_QUOTES, 'UTF-8');
$current_user_id = $_SESSION["user_id"];

// 1. Ambil senarai katalog GPU dari database
$gpuList = [];
$gpuQuery = $conn->query("SELECT * FROM gpu_catalog");
if ($gpuQuery) {
    while ($row = $gpuQuery->fetch_assoc()) {
        $gpuList[] = $row;
    }
}

// 2. Ambil senarai katalog Software Image dari database
$softwareList = [];
$swQuery = $conn->query("SELECT * FROM software_catalog");
if ($swQuery) {
    while ($row = $swQuery->fetch_assoc()) {
        $softwareList[] = $row;
    }
}

// 3. Senarai SSH Keys
$sshKeys = [
    ["name" => "ops-laptop", "type" => "ed25519"],
    ["name" => "ci-runner", "type" => "ed25519"]
];

// 4. Proses apabila form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $instance_name = trim($_POST["instance_name"]);
    $gpu_model = $_POST["gpu_model"];
    $gpu_count = intval($_POST["gpu_count"]);
    $data_centre = $_POST["data_centre"];
    $software_image = $_POST["software_image"];
    $ssh_key = $_POST["ssh_key"];
    
    // Anggaran kos asas dikira berdasarkan pilihan
    $estimated_cost = 3000.00 * $gpu_count; 

    $stmt = $conn->prepare("INSERT INTO instances (user_id, instance_name, gpu_model, gpu_count, data_centre, software_image, ssh_key, estimated_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Running')");
    
    if ($stmt) {
        $stmt->bind_param("ississsd", $current_user_id, $instance_name, $gpu_model, $gpu_count, $data_centre, $software_image, $ssh_key, $estimated_cost);
        
        if ($stmt->execute()) {
            header("Location: instances.php");
            exit();
        } else {
            $error_message = "Ralat menyimpan rekod: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launch instance - HE AI Foundry</title>
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
                <li><a href="launch.php" class="active">Launch instance</a></li>
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

        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button id="mobileMenuToggle" class="action-icon-btn" style="display: none; cursor: pointer;" title="Open Menu">☰</button>
                <h1>Launch instance</h1>
            </div>
            <div class="topbar-actions">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" placeholder="Search instances, invoices...">
                </div>
                <button class="action-icon-btn">🔔</button>
            </div>
        </header>

        <?php if (!empty($error_message)): ?>
            <div style="background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff6b6b; padding: 12px; border-radius: 8px; margin-top: 1.5rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- BORANG UTAMA LAUNCH -->
        <form action="launch.php" method="POST" class="launch-container" style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; margin-top: 1.5rem;">
            
            <!-- BAHAGIAN KIRI (PILIHAN 01 - 05) -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- 01 Choose GPU -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #fff; font-size: 1rem; margin-bottom: 12px;">01 Choose GPU</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        <?php foreach ($gpuList as $index => $gpu): 
                            // Hanya disable jika status mengandungi perkataan "Coming"
                            $isUnavailable = (stripos($gpu['status'], 'Coming') !== false);
                        ?>
                        <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 14px; border-radius: 8px; cursor: <?php echo $isUnavailable ? 'not-allowed' : 'pointer'; ?>; display: flex; flex-direction: column; gap: 6px; <?php echo $isUnavailable ? 'opacity: 0.4;' : ''; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: #fff; font-size: 0.95rem;">
                                    <input type="radio" name="gpu_model" value="<?php echo htmlspecialchars($gpu['gpu_name']); ?>" 
                                        <?php echo $isUnavailable ? 'disabled' : ''; ?> 
                                        <?php echo (!$isUnavailable && $gpu['gpu_name'] === 'RTX Pro 6000') ? 'checked' : ''; ?>> 
                                    <?php echo htmlspecialchars($gpu['gpu_name']); ?>
                                </strong>
                                <span style="font-size: 0.75rem; color: <?php echo $isUnavailable ? '#e74c3c' : '#2ecc71'; ?>;"><?php echo htmlspecialchars($gpu['status']); ?></span>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--text-inv-soft);"><?php echo htmlspecialchars($gpu['specs']); ?></span>
                            <span class="gpu-price" style="font-size: 0.85rem; color: #fff; font-weight: 600;">USD <?php echo number_format($gpu['monthly_price'], 0); ?> / GPU / month</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 02 GPUs per instance -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #fff; font-size: 1rem; margin-bottom: 12px;">02 GPUs per instance</h3>
                    <div style="display: flex; gap: 12px;">
                        <?php foreach ([1, 2, 4, 8] as $qty): ?>
                        <label style="flex: 1; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 10px; border-radius: 8px; text-align: center; cursor: pointer; color: #fff; font-weight: 600;">
                            <input type="radio" name="gpu_count" value="<?php echo $qty; ?>" <?php echo $qty == 2 ? 'checked' : ''; ?>> <?php echo $qty; ?>×
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 03 Data Centre -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #fff; font-size: 1rem; margin-bottom: 12px;">03 Data centre</h3>
                    <div style="display: flex; gap: 12px;">
                        <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 10px 20px; border-radius: 8px; color: #fff; cursor: pointer;">
                            <input type="radio" name="data_centre" value="BFDC" checked> BFDC
                        </label>
                        <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 10px 20px; border-radius: 8px; color: #fff; cursor: pointer;">
                            <input type="radio" name="data_centre" value="KVDC"> KVDC
                        </label>
                    </div>
                </div>

                <!-- 04 Software Image -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #fff; font-size: 1rem; margin-bottom: 12px;">04 Software image</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        <?php foreach ($softwareList as $index => $sw): ?>
                        <label style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; gap: 4px;">
                            <strong style="color: #fff; font-size: 0.9rem;">
                                <input type="radio" name="software_image" value="<?php echo htmlspecialchars($sw['image_name']); ?>" <?php echo $index === 0 ? 'checked' : ''; ?> required> <?php echo htmlspecialchars($sw['image_name']); ?>
                            </strong>
                            <span style="font-size: 0.75rem; color: var(--text-inv-soft);"><?php echo htmlspecialchars($sw['description']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 05 Name & Access -->
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block;">
                    <h3 style="color: #fff; font-size: 1rem; margin-bottom: 12px;">05 Name & access</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; color: var(--text-inv-soft); margin-bottom: 6px;">Instance name</label>
                            <input type="text" name="instance_name" value="llm-finetune-02" required style="width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; color: var(--text-inv-soft); margin-bottom: 6px;">SSH key</label>
                            <select name="ssh_key" style="width: 100%; padding: 10px 14px; background: #151922; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem; cursor: pointer;">
                                <?php foreach ($sshKeys as $key): ?>
                                <option value="<?php echo $key['name']; ?>"><?php echo $key['name']; ?> (<?php echo $key['type']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <!-- BAHAGIAN KANAN (SUMMARY & BUTTON LAUNCH) -->
            <div>
                <div class="metric-card" style="padding: 1.5rem; text-align: left; display: block; position: sticky; top: 20px;">
                    <h3 style="color: #fff; font-size: 1.1rem; margin-bottom: 1rem;">Summary</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-inv-soft);">GPU</span> <strong id="sum-gpu" style="color: #fff;">RTX Pro 6000</strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-inv-soft);">Quantity</span> <strong id="sum-qty" style="color: #fff;">2 GPUs</strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-inv-soft);">Data centre</span> <strong id="sum-dc" style="color: #fff;">BFDC</strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-inv-soft);">Image</span> <strong id="sum-image" style="color: #fff;">Ubuntu 22.04</strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-inv-soft);">Rate</span> <strong id="sum-rate" style="color: #fff;">USD 2,400/mo</strong></div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <span style="font-size: 0.75rem; color: var(--text-inv-soft); display: block; margin-bottom: 4px;">Estimated monthly</span>
                        <div id="sum-total" style="font-size: 1.8rem; font-weight: 700; color: #fff;">USD 4,800</div>
                        <span style="font-size: 0.75rem; color: var(--text-inv-soft);">Billed monthly per GPU. Prorated in the first month.</span>
                    </div>

                    <button type="submit" class="launch-btn" style="width: 100%; padding: 12px; font-size: 1rem; justify-content: center; cursor: pointer;">Launch instance</button>
                    
                    <span style="font-size: 0.75rem; color: var(--text-inv-soft); text-align: center; display: block; margin-top: 10px;">Ready in about [X] minutes. You'll get SSH details on the Instances page.</span>
                </div>
            </div>

        </form>

    </main>

</div>

<!-- SKRIP JAVASCRIPT YANG TELAH DIBAIKI -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");

    function updateSummary() {
        // 1. GPU (Mengambil nilai berdasarkan class .gpu-price dengan tepat)
        const selectedGpu = document.querySelector("input[name='gpu_model']:checked");
        let gpuName = "NVIDIA H200";
        let gpuPrice = 3000;
        if (selectedGpu) {
            gpuName = selectedGpu.value;
            const label = selectedGpu.closest("label");
            const priceSpan = label.querySelector(".gpu-price"); // Menggunakan class khas
            if (priceSpan) {
                const match = priceSpan.innerText.replace(/,/g, "").match(/(\d+)/);
                if (match) gpuPrice = parseInt(match[0]);
            }
        }

        // 2. Quantity
        const selectedQty = document.querySelector("input[name='gpu_count']:checked");
        let qty = selectedQty ? parseInt(selectedQty.value) : 2;

        // 3. Data Centre
        const selectedDc = document.querySelector("input[name='data_centre']:checked");
        let dc = selectedDc ? selectedDc.value : "BFDC";

        // 4. Software Image
        const selectedImage = document.querySelector("input[name='software_image']:checked");
        let imgName = selectedImage ? selectedImage.value : "Ubuntu 22.04";

        // Kira total kos (Harga seunit x Kuantiti)
        let total = gpuPrice * qty;

        // Kemaskini paparan HTML Summary secara live
        document.getElementById("sum-gpu").innerText = gpuName;
        document.getElementById("sum-qty").innerText = qty + (qty > 1 ? " GPUs" : " GPU");
        document.getElementById("sum-dc").innerText = dc;
        document.getElementById("sum-image").innerText = imgName;
        document.getElementById("sum-rate").innerText = "USD " + gpuPrice.toLocaleString() + "/mo";
        document.getElementById("sum-total").innerText = "USD " + total.toLocaleString();
    }

    // Jalankan fungsi setiap kali ada perubahan pada pilihan borang
    form.addEventListener("change", updateSummary);
    
    // Jalankan sekali waktu mula-mula muat turun halaman
    updateSummary();
});
</script>
<script src="js/dashboard.js"></script>
</body>
</html>
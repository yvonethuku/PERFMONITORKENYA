
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"] ?? "User";
$message = "";

$selectedDevices = "
    SELECT MAX(id)
    FROM devices
    WHERE device_name IN (
        'My Laptop',
        'Network Router',
        'Printer',
        'Backup Server'
    )
    GROUP BY device_name
";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $device_id = (int) $_POST["device_id"];
    $alert_message = trim($_POST["message"]);
    $status = $_POST["status"];

    $check = $conn->prepare(
        "SELECT id FROM devices WHERE id = ?"
    );
    $check->bind_param("i", $device_id);
    $check->execute();
    $validDevice = $check->get_result()->num_rows > 0;
    $check->close();

    if ($validDevice && $alert_message !== "") {
        $stmt = $conn->prepare(
            "INSERT INTO alerts (device_id, message, status)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("iss", $device_id, $alert_message, $status);

        if ($stmt->execute()) {
            $message = "Alert created successfully!";
        } else {
            $message = "Unable to create alert.";
        }

        $stmt->close();
    } else {
        $message = "Please select a valid device and enter an alert message.";
    }
}

$devices = $conn->query("
    SELECT id, device_name
    FROM devices
    WHERE id IN ($selectedDevices)
    ORDER BY device_name ASC
");

$alerts = $conn->query("
    SELECT alerts.*, devices.device_name
    FROM alerts
    INNER JOIN devices ON alerts.device_id = devices.id
    WHERE devices.id IN ($selectedDevices)
    ORDER BY alerts.id DESC
");

$pendingAlerts = $conn->query(
    "SELECT COUNT(*) AS total FROM alerts WHERE status = 'Pending'"
)->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Alerts | PERFMONITOR KENYA</title>

<link rel="stylesheet" href="css/style.css">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #081525;
    color: white;
}

.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #0D2036;
    padding: 28px 18px;
    border-right: 1px solid #1B354D;
}

.logo {
    text-align: center;
    color: #35D9F0;
    font-size: 21px;
    font-weight: 900;
    line-height: 1.6;
    margin-bottom: 40px;
    letter-spacing: 1px;
    text-shadow: 0 0 12px rgba(53,217,240,0.45);
}

.logo span {
    display: block;
}

.menu-title {
    color: #71869D;
    font-size: 11px;
    margin: 25px 12px 12px;
    letter-spacing: 2px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 14px;
    margin-bottom: 8px;
    color: #B8C8D9;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
    background: #173B55;
    color: #35D9F0;
}

.sidebar a span {
    font-size: 18px;
}

.logout {
    margin-top: 45px;
    border-top: 1px solid #294057;
    padding-top: 20px;
}

.logout a {
    color: #FF8585;
}

.logout a:hover {
    background: #482B37;
    color: #FFAAAA;
}

.main {
    margin-left: 250px;
    padding: 30px;
}

.header {
    background: #10243A;
    padding: 22px 25px;
    border: 1px solid #1C3A53;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.header h2 {
    color: white;
    margin: 0;
}

.header p {
    color: #91A7BC;
    margin-bottom: 0;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    background: #0B1B2D;
    border: 1px solid #34516B;
    border-radius: 8px;
    overflow: hidden;
}

.search-box input {
    width: 155px;
    background: transparent;
    border: none;
    outline: none;
    padding: 11px;
    color: white;
}

.search-box button {
    background: #35D9F0;
    color: #081525;
    border: none;
    padding: 0 14px;
    cursor: pointer;
    margin: 0;
    border-radius: 0;
}

.notification {
    position: relative;
    color: #35D9F0;
    text-decoration: none;
    font-size: 23px;
}

.notification-count {
    position: absolute;
    top: -8px;
    right: -10px;
    background: #ef5350;
    color: white;
    border-radius: 50%;
    font-size: 10px;
    padding: 4px 6px;
}

.profile {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #D8E6F2;
    font-size: 14px;
}

.profile-icon {
    background: #173C58;
    color: #35D9F0;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.panel {
    background: #10243A;
    padding: 25px;
    border: 1px solid #1C3A53;
    border-radius: 13px;
    margin-bottom: 25px;
}

.panel h3 {
    color: #35D9F0;
    margin-top: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

label {
    display: block;
    color: #B8C8D9;
    font-weight: bold;
    margin-bottom: 9px;
    font-size: 14px;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
    width: 100%;
    padding: 13px;
    background: #0B1B2D;
    color: white;
    border: 1px solid #34516B;
    border-radius: 7px;
    font-family: Arial, sans-serif;
    outline: none;
}

.form-grid input:focus,
.form-grid select:focus,
.form-grid textarea:focus {
    border-color: #35D9F0;
}

textarea {
    resize: vertical;
}

button {
    background: #35D9F0;
    color: #081525;
    border: none;
    padding: 13px 25px;
    border-radius: 7px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 22px;
}

button:hover {
    background: #16BBD5;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 18px;
    min-width: 650px;
}

th {
    background: #173B55;
    color: #35D9F0;
    font-size: 13px;
}

th, td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #294057;
}

td {
    color: #D0DFEC;
    font-size: 14px;
}

tr:hover td {
    background: #142D45;
}

.message {
    background: #123B45;
    color: #65E5D1;
    padding: 14px;
    border: 1px solid #287A78;
    border-radius: 7px;
    margin-bottom: 20px;
}

.footer {
    color: #71869D;
    text-align: center;
    margin-top: 30px;
    font-size: 12px;
}

@media(max-width: 800px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 18px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <div class="logo">
        <span>PERFMONITOR</span>
        <span>KENYA</span>
    </div>

    <div class="menu-title">MAIN MENU</div>

    <a href="dashboard.php"><span>▦</span> Dashboard</a>
    <a href="devices.php"><span>▣</span> Device Monitoring</a>
    <a href="alerts.php" class="active"><span>♧</span> System Alerts</a>
    <a href="reports.php"><span>▤</span> Reports</a>
    <a href="settings.php"><span>⚙</span> Settings</a>

    <div class="logout">
        <a href="logout.php"><span>⇥</span> Logout</a>
    </div>

</div>

<div class="main">

    <div class="header">
        <div>
            <h2>System Alerts</h2>
            <p>Monitor and manage alerts for registered devices.</p>
        </div>

        <div class="topbar-right">

            <form action="search.php" method="GET" class="search-box">
                <input type="text" name="q"
                       placeholder="Search system..." required>
                <button type="submit">⌕</button>
            </form>

            <a href="alerts.php" class="notification" title="System Alerts">
                ♧
                <?php if ($pendingAlerts > 0): ?>
                    <span class="notification-count">
                        <?php echo $pendingAlerts; ?>
                    </span>
                <?php endif; ?>
            </a>

            <div class="profile">
                <div class="profile-icon">♙</div>
                <?php echo htmlspecialchars($username); ?>
            </div>

        </div>
    </div>

    <?php if ($message != ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <h3>Create New Alert</h3>

        <form method="POST">

            <div class="form-grid">

                <div>
                    <label>Select Device</label>

                    <select name="device_id" required>
                        <option value="">Choose a device</option>

                        <?php while ($device = $devices->fetch_assoc()): ?>
                            <option value="<?php echo $device["id"]; ?>">
                                <?php echo htmlspecialchars($device["device_name"]); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div>
                    <label>Alert Status</label>

                    <select name="status" required>
                        <option value="Pending">Pending</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label>Alert Message</label>

                    <textarea name="message" rows="3"
                    placeholder="Describe the system alert..."
                    required></textarea>
                </div>

            </div>

            <button type="submit">CREATE ALERT</button>

        </form>
    </div>

    <div class="panel">
        <h3>System Alerts History</h3>

        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Device</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date Created</th>
                </tr>

                <?php if ($alerts->num_rows > 0): ?>

                    <?php $display_id = 1; ?>

                    <?php while ($alert = $alerts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $display_id++; ?></td>
                            <td><?php echo htmlspecialchars($alert["device_name"]); ?></td>
                            <td><?php echo htmlspecialchars($alert["message"]); ?></td>
                            <td><?php echo htmlspecialchars($alert["status"]); ?></td>
                            <td><?php echo htmlspecialchars($alert["created_at"]); ?></td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5">No system alerts available for these devices.</td>
                    </tr>

                <?php endif; ?>

            </table>
        </div>
    </div>

    <div class="footer">
        PERFMONITOR KENYA | System Alerts
    </div>

</div>

</body>
</html>

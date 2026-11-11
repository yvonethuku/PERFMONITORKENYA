
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"] ?? "User";
$term = trim($_GET["q"] ?? "");
$results = [];

$pendingAlerts = $conn->query(
    "SELECT COUNT(*) AS total FROM alerts WHERE status = 'Pending'"
)->fetch_assoc()["total"];

if ($term !== "") {
    $like = "%" . $term . "%";

    // Search devices
    $stmt = $conn->prepare(
        "SELECT device_name, ip_address, status
         FROM devices
         WHERE device_name LIKE ? OR ip_address LIKE ? OR status LIKE ?"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $data = $stmt->get_result();

    while ($row = $data->fetch_assoc()) {
        $results[] = [
            "type" => "Device",
            "title" => $row["device_name"],
            "details" => "IP: " . $row["ip_address"] .
                         " | Status: " . $row["status"]
        ];
    }
    $stmt->close();

    // Search alerts
    $stmt = $conn->prepare(
        "SELECT devices.device_name, alerts.message, alerts.status
         FROM alerts
         LEFT JOIN devices ON alerts.device_id = devices.id
         WHERE devices.device_name LIKE ?
         OR alerts.message LIKE ?
         OR alerts.status LIKE ?"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $data = $stmt->get_result();

    while ($row = $data->fetch_assoc()) {
        $results[] = [
            "type" => "Alert",
            "title" => $row["device_name"] ?? "Unknown device",
            "details" => $row["message"] . " | Status: " . $row["status"]
        ];
    }
    $stmt->close();

    // Search reports
    $stmt = $conn->prepare(
        "SELECT report_title, report_description
         FROM reports
         WHERE report_title LIKE ? OR report_description LIKE ?"
    );
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $data = $stmt->get_result();

    while ($row = $data->fetch_assoc()) {
        $results[] = [
            "type" => "Report",
            "title" => $row["report_title"],
            "details" => $row["report_description"]
        ];
    }
    $stmt->close();

    // Search settings
    $stmt = $conn->prepare(
        "SELECT setting_name, setting_value
         FROM settings
         WHERE setting_name LIKE ? OR setting_value LIKE ?"
    );
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $data = $stmt->get_result();

    while ($row = $data->fetch_assoc()) {
        $results[] = [
            "type" => "Setting",
            "title" => $row["setting_name"],
            "details" => $row["setting_value"]
        ];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PERFMONITOR KENYA - Search</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #081525;
    color: white;
}

.sidebar {
    width: 260px;
    height: 100vh;
    background: #0D2036;
    position: fixed;
    left: 0;
    top: 0;
    padding: 30px 18px;
    display: flex;
    flex-direction: column;
}

.logo {
    text-align: center;
    color: #35D9F0;
    font-size: 21px;
    font-weight: 900;
    letter-spacing: 1px;
    line-height: 1.7;
    margin-bottom: 45px;
    text-shadow: 0 0 12px rgba(53,217,240,0.45);
}

.logo span {
    display: block;
}

.nav-links a {
    display: block;
    color: #b9c9d9;
    text-decoration: none;
    padding: 15px;
    margin-bottom: 9px;
    border-radius: 8px;
}

.nav-links a:hover,
.nav-links a.active {
    background: #173c58;
    color: #35D9F0;
}

.logout {
    margin-top: auto;
}

.logout a {
    display: block;
    padding: 14px;
    color: #ff8585;
    text-decoration: none;
}

.main {
    margin-left: 260px;
    padding: 30px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 35px;
}

.topbar p {
    color: #9aafc3;
    margin-top: 7px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 18px;
}

.search-box {
    display: flex;
    background: #10243A;
    border: 1px solid #25445d;
    border-radius: 8px;
    overflow: hidden;
}

.search-box input {
    background: transparent;
    border: none;
    outline: none;
    padding: 11px;
    color: white;
    width: 160px;
}

.search-box button {
    background: #35D9F0;
    border: none;
    padding: 0 14px;
    cursor: pointer;
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
    border-radius: 50%;
    font-size: 10px;
    padding: 4px 6px;
}

.profile {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #d8e6f2;
}

.profile-icon {
    background: #173c58;
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
    border: 1px solid #1e405a;
    border-radius: 12px;
    padding: 25px;
}

.panel h3 {
    margin-bottom: 20px;
}

.result {
    padding: 17px;
    border-bottom: 1px solid #25445d;
}

.result:last-child {
    border-bottom: none;
}

.result-type {
    color: #35D9F0;
    font-size: 12px;
    margin-bottom: 7px;
    text-transform: uppercase;
}

.result h4 {
    margin-bottom: 7px;
}

.result p,
.empty {
    color: #b9c9d9;
    line-height: 1.6;
}

@media (max-width: 650px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .topbar-right {
        flex-wrap: wrap;
    }
}
</style>
</head>

<body>

<aside class="sidebar">

    <div class="logo">
        <span>PERFMONITOR</span>
        <span>KENYA</span>
    </div>

    <nav class="nav-links">
        <a href="dashboard.php">▦ Dashboard</a>
        <a href="devices.php">▣ Device Monitoring</a>
        <a href="alerts.php">♧ System Alerts</a>
        <a href="reports.php">▤ Reports</a>
        <a href="settings.php">⚙ Settings</a>
    </nav>

    <div class="logout">
        <a href="logout.php">↪ Logout</a>
    </div>

</aside>

<main class="main">

    <div class="topbar">
        <div>
            <h2>Global Search</h2>
            <p>Search across your monitoring system</p>
        </div>

        <div class="topbar-right">
            <form action="search.php" method="GET" class="search-box">
                <input type="text" name="q"
                       placeholder="Search system..."
                       value="<?php echo htmlspecialchars($term); ?>"
                       required>
                <button type="submit">⌕</button>
            </form>

            <a href="alerts.php" class="notification">
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

    <section class="panel">
        <h3>Search Results</h3>

        <?php if ($term === ""): ?>

            <p class="empty">Enter a keyword in the search bar to find records.</p>

        <?php elseif (count($results) > 0): ?>

            <p class="empty">
                Found <?php echo count($results); ?> matching record(s).
            </p>

            <?php foreach ($results as $result): ?>
                <div class="result">
                    <div class="result-type">
                        <?php echo htmlspecialchars($result["type"]); ?>
                    </div>

                    <h4>
                        <?php echo htmlspecialchars($result["title"]); ?>
                    </h4>

                    <p>
                        <?php echo htmlspecialchars($result["details"]); ?>
                    </p>
                </div>
            <?php endforeach; ?>

        <?php else: ?>

            <p class="empty">
                No records found for
                "<?php echo htmlspecialchars($term); ?>".
            </p>

        <?php endif; ?>

    </section>

</main>

</body>
</html>

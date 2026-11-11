
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"] ?? "User";

// Dashboard statistics
$devices = $conn->query("SELECT COUNT(*) AS total FROM devices")->fetch_assoc()["total"];
$alerts = $conn->query("SELECT COUNT(*) AS total FROM alerts")->fetch_assoc()["total"];
$reports = $conn->query("SELECT COUNT(*) AS total FROM reports")->fetch_assoc()["total"];
$users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()["total"];

$pendingAlerts = $conn->query(
    "SELECT COUNT(*) AS total FROM alerts WHERE status = 'Pending'"
)->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PERFMONITOR KENYA - Dashboard</title>

<link rel="stylesheet" href="css/style.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #081525;
    color: #ffffff;
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
    white-space: nowrap;
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
    transition: 0.3s;
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
    border-radius: 8px;
}

.logout a:hover {
    background: #382532;
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
    margin-bottom: 35px;
    flex-wrap: wrap;
}

.topbar h2 {
    font-size: 25px;
    margin-bottom: 7px;
}

.topbar p {
    color: #9aafc3;
    font-size: 14px;
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
    font-size: 18px;
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

.welcome {
    background: linear-gradient(110deg, #102b43, #123c55);
    border: 1px solid #24516b;
    border-radius: 14px;
    padding: 30px;
    margin-bottom: 30px;
}

.welcome h1 {
    color: #ffffff;
    font-size: 30px;
    margin-bottom: 12px;
}

.welcome p {
    color: #c4d8e8;
    line-height: 1.7;
}

.section-title {
    font-size: 20px;
    margin-bottom: 20px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #10243A;
    border: 1px solid #1e405a;
    border-radius: 12px;
    padding: 23px;
}

.stat-card p {
    color: #9aafc3;
    margin-bottom: 15px;
    font-size: 14px;
}

.stat-card h2 {
    color: #35D9F0;
    font-size: 30px;
}

.overview {
    background: #10243A;
    border: 1px solid #1e405a;
    border-radius: 12px;
    padding: 25px;
}

.overview h3 {
    margin-bottom: 15px;
}

.overview p {
    color: #b9c9d9;
    line-height: 1.8;
}

@media (max-width: 950px) {
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .main {
        padding: 20px;
    }
}

@media (max-width: 650px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .nav-links a {
        display: inline-block;
    }

    .logout {
        margin-top: 10px;
    }

    .main {
        margin-left: 0;
    }

    .stats {
        grid-template-columns: 1fr;
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
        <a href="dashboard.php" class="active">▦ Dashboard</a>
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
            <h2>Dashboard Overview</h2>
            <p>System performance and monitoring summary</p>
        </div>

        <div class="topbar-right">

            <form action="search.php" method="GET" class="search-box">
                <input type="text" name="q" placeholder="Search system..." required>
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

    <section class="welcome">
        <h1>Welcome to PERFMONITOR KENYA</h1>
        <p>
            Monitor your devices, track system performance,
            manage alerts and generate reports from one central dashboard.
        </p>
    </section>

    <h3 class="section-title">System Statistics</h3>

    <section class="stats">

        <div class="stat-card">
            <p>Total Devices</p>
            <h2><?php echo $devices; ?></h2>
        </div>

        <div class="stat-card">
            <p>Total Alerts</p>
            <h2><?php echo $alerts; ?></h2>
        </div>

        <div class="stat-card">
            <p>Generated Reports</p>
            <h2><?php echo $reports; ?></h2>
        </div>

        <div class="stat-card">
            <p>Registered Users</p>
            <h2><?php echo $users; ?></h2>
        </div>

    </section>

    <section class="overview">
        <h3>System Overview</h3>
        <p>
            PERFMONITOR KENYA provides a central platform for monitoring
            connected devices and reviewing system activities.
            Use the navigation menu to manage devices, view alerts,
            generate reports and configure system settings.
        </p>
    </section>

</main>

</body>
</html>

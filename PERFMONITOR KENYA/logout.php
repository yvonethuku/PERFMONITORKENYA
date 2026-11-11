<?php
session_start();

$_SESSION = [];
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Logout | PERFMONITOR KENYA</title>
<link rel="stylesheet" href="css/style.css">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #061426;
    background-image:
        linear-gradient(rgba(0, 210, 230, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 210, 230, 0.04) 1px, transparent 1px);
    background-size: 48px 48px;
    color: white;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.logout-card {
    width: 85%;
    max-width: 850px;
    background: #08192d;
    border: 1px solid #07516b;
    border-radius: 20px;
    padding: 65px 40px;
    text-align: center;
    box-shadow: 0 15px 45px rgba(0,0,0,0.4);
}

.logo {
    color: #00cce5;
    font-size: 27px;
    font-weight: bold;
    letter-spacing: 3px;
    margin-bottom: 35px;
}

.gold-line {
    width: 70px;
    height: 3px;
    background: #e9bd55;
    margin: 0 auto 35px;
}

.logout-icon {
    font-size: 65px;
    color: #00cce5;
    margin-bottom: 20px;
}

h1 {
    font-size: 34px;
    margin-bottom: 15px;
}

h1 span {
    color: #00cce5;
}

.message {
    color: #aabbd0;
    font-size: 17px;
    line-height: 1.7;
}

.login-button {
    display: inline-block;
    margin-top: 30px;
    padding: 16px 45px;
    background: #00cce5;
    color: #061426;
    text-decoration: none;
    font-weight: bold;
    letter-spacing: 2px;
    border-radius: 8px;
}

.login-button:hover {
    background: #00e5f5;
}

footer {
    margin-top: 45px;
    color: #71869c;
    font-size: 12px;
}
</style>
</head>

<body>

<div class="logout-card">

    <div class="logo">PERFMONITOR KENYA</div>

    <div class="gold-line"></div>

    <div class="logout-icon">⇥</div>

    <h1>You Have Been <span>Logged Out!</span></h1>

    <p class="message">
        Thank you for using PerfMonitor Kenya.<br>
        Your session has ended successfully.
    </p>

    <a href="login.php" class="login-button">
        GO TO LOGIN
    </a>

    <footer>
        © 2026 PERFMONITOR KENYA<br><br>
        Smarter Monitoring. Better Performance.
    </footer>

</div>

</body>
</html>
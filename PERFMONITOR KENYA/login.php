
<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, username, password FROM users WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect username or password.";
        }
    } else {
        $error = "Incorrect username or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | PERFMONITOR KENYA</title>
<link rel="stylesheet" href="css/style.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    font-family: "Segoe UI", Arial, sans-serif;
    background:
        radial-gradient(ellipse at 15% 20%, rgba(0,207,232,0.18), transparent 38%),
        radial-gradient(ellipse at 85% 85%, rgba(18,59,93,0.65), transparent 40%),
        linear-gradient(135deg, #030B16, #071B33 55%, #020812);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 35px;
    overflow-x: hidden;
    position: relative;
}

/* Background lighting */

body::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(0,207,232,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,207,232,0.035) 1px, transparent 1px);
    background-size: 45px 45px;
    pointer-events: none;
}

.glow {
    position: fixed;
    width: 350px;
    height: 350px;
    border-radius: 50%;
    background: rgba(0,207,232,0.10);
    filter: blur(100px);
    top: 5%;
    right: 5%;
    pointer-events: none;
}

.container {
    width: 1000px;
    max-width: 100%;
    min-height: 600px;
    display: flex;
    position: relative;
    z-index: 1;
    overflow: hidden;
    border-radius: 20px;
    border: 1px solid rgba(0,207,232,0.25);
    background: rgba(7,27,51,0.88);
    box-shadow:
        0 25px 80px rgba(0,0,0,0.65),
        0 0 35px rgba(0,207,232,0.08);
}

/* LEFT BRANDING */

.left {
    flex: 1.1;
    padding: 65px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: white;
    background:
        linear-gradient(145deg, rgba(7,27,51,0.85), rgba(18,59,93,0.75));
    border-right: 1px solid rgba(0,207,232,0.18);
    position: relative;
}

.brand {
    font-size: 27px;
    font-weight: 800;
    letter-spacing: 3px;
    color: #00CFE8;
    text-shadow: 0 0 18px rgba(0,207,232,0.4);
}

.gold-line {
    width: 65px;
    height: 3px;
    background: #D6B66C;
    margin: 22px 0;
    box-shadow: 0 0 12px rgba(214,182,108,0.5);
}

.left h2 {
    font-size: 43px;
    line-height: 1.2;
    margin-bottom: 22px;
    font-weight: 700;
}

.left h2 span {
    color: #00CFE8;
}

.left p {
    color: #C3D4E3;
    font-size: 15px;
    line-height: 1.9;
    max-width: 400px;
}

.features {
    margin-top: 35px;
}

.features div {
    margin: 18px 0;
    color: #EAF6FA;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.features span {
    color: #D6B66C;
    margin-right: 12px;
    font-size: 18px;
}

.left-footer {
    position: absolute;
    bottom: 25px;
    color: #8CA4B8;
    font-size: 11px;
    letter-spacing: 2px;
}

/* RIGHT LOGIN PANEL */

.right {
    flex: 0.9;
    padding: 65px 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: rgba(3,14,28,0.65);
}

.top-label {
    color: #D6B66C;
    font-size: 11px;
    letter-spacing: 3px;
    font-weight: bold;
    margin-bottom: 15px;
}

.right h2 {
    color: white;
    font-size: 32px;
    margin-bottom: 10px;
}

.subtitle {
    color: #9DB1C3;
    font-size: 14px;
    margin-bottom: 38px;
    line-height: 1.7;
}

label {
    display: block;
    color: #D8E7F2;
    font-size: 13px;
    margin-bottom: 10px;
    font-weight: 600;
}

.input-box {
    margin-bottom: 24px;
}

input {
    width: 100%;
    padding: 16px;
    background: rgba(255,255,255,0.045);
    border: 1px solid rgba(174,204,224,0.2);
    border-radius: 8px;
    color: white;
    font-size: 14px;
    outline: none;
    transition: 0.3s;
}

input::placeholder {
    color: #8298AA;
}

input:focus {
    border-color: #00CFE8;
    box-shadow: 0 0 15px rgba(0,207,232,0.15);
    background: rgba(0,207,232,0.04);
}

button {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(100deg, #00AEC5, #00D8EF);
    color: #031322;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 2px;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 7px 25px rgba(0,207,232,0.2);
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,207,232,0.4);
}

.error {
    background: rgba(198,40,40,0.15);
    border: 1px solid rgba(255,90,90,0.4);
    color: #FFAAAA;
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 22px;
    font-size: 13px;
}

.register {
    text-align: center;
    margin-top: 28px;
    color: #9DB1C3;
    font-size: 13px;
}

.register a {
    color: #00CFE8;
    text-decoration: none;
    font-weight: bold;
}

.register a:hover {
    color: #D6B66C;
}

.footer {
    text-align: center;
    color: #71879B;
    font-size: 11px;
    line-height: 1.8;
    margin-top: 45px;
}

@media(max-width: 750px) {

    body {
        padding: 15px;
    }

    .container {
        flex-direction: column;
    }

    .left {
        padding: 40px 30px;
        min-height: 350px;
    }

    .left h2 {
        font-size: 32px;
    }

    .left-footer {
        display: none;
    }

    .right {
        padding: 45px 30px;
    }
}

</style>
</head>

<body>

<div class="glow"></div>

<div class="container">

    <div class="left">

        <div class="brand">PERFMONITOR KENYA</div>

        <div class="gold-line"></div>

        <h2>
            Intelligent<br>
            <span>Performance</span><br>
            Monitoring.
        </h2>

        <p>
            A centralized monitoring platform designed
            to track device performance, identify system
            issues and provide meaningful performance insights.
        </p>

        <div class="features">

            <div><span>✦</span> Advanced Device Monitoring</div>

            <div><span>✦</span> Intelligent System Alerts</div>

            <div><span>✦</span> Performance Analytics</div>

            <div><span>✦</span> Centralized Reports</div>

        </div>

        <div class="left-footer">
            TECHNOLOGY • VISIBILITY • CONTROL
        </div>

    </div>

    <div class="right">

        <div class="top-label">SECURE SYSTEM ACCESS</div>

        <h2>Welcome Back</h2>

        <p class="subtitle">
            Sign in to your account to continue
            managing your monitoring environment.
        </p>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-box">

                <label>USERNAME</label>

                <input
                    type="text"
                    name="username"
                    placeholder="Enter your username"
                    autocomplete="username"
                    required
                >

            </div>

            <div class="input-box">

                <label>PASSWORD</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button type="submit">
                SIGN IN
            </button>

        </form>

        <div class="register">
            Don't have an account?
            <a href="register.php">Create Account</a>
        </div>

        <div class="footer">
            © 2026 PERFMONITOR KENYA<br>
            Smarter Monitoring. Better Performance.
        </div>

    </div>

</div>

</body>
</html>

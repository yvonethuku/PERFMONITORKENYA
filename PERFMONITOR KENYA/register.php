
<?php
require_once "db.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";

    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";

    } elseif (strlen($password) < 6) {
        $message = "Password must have at least 6 characters.";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ?"
        );

        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "Username already exists.";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users (username, password)
                 VALUES (?, ?)"
            );

            $stmt->bind_param(
                "ss",
                $username,
                $hashed_password
            );

            if ($stmt->execute()) {
                $message = "Account created successfully! You can now login.";
                $success = true;
            } else {
                $message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register | PERFMONITOR KENYA</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #071B33;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.container {
    background: white;
    width: 430px;
    max-width: 100%;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 12px 35px rgba(0,0,0,0.35);
}

h1 {
    color: #071B33;
    text-align: center;
}

h1 span {
    color: #00CFE8;
}

p {
    text-align: center;
    color: #718096;
}

label {
    display: block;
    margin-top: 18px;
    margin-bottom: 8px;
    color: #123B5D;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
}

input:focus {
    outline: none;
    border-color: #00CFE8;
}

button {
    width: 100%;
    margin-top: 25px;
    padding: 14px;
    background: #00CFE8;
    color: #071B33;
    border: none;
    border-radius: 7px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #00aec5;
}

.message {
    background: #eaf6fa;
    color: #071B33;
    padding: 12px;
    border-radius: 6px;
    margin-top: 15px;
    text-align: center;
}

.error {
    background: #ffebee;
    color: #b71c1c;
}

a {
    color: #008da8;
    text-decoration: none;
    font-weight: bold;
}

.footer {
    text-align: center;
    margin-top: 25px;
    font-size: 12px;
    color: #718096;
}
</style>
</head>

<body>

<div class="container">

    <h1>PERFMONITOR <span>KENYA</span></h1>

    <p>Create your account to get started.</p>

    <form method="POST">

        <label>Username</label>
        <input type="text" name="username"
               placeholder="Choose a username" required>

        <label>Password</label>
        <input type="password" name="password"
               placeholder="Create a password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password"
               placeholder="Confirm your password" required>

        <button type="submit">CREATE ACCOUNT</button>

    </form>

    <?php if ($message != ""): ?>
        <div class="message <?php echo $success ? '' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <p>
        Already registered?
        <a href="login.php">Login here</a>
    </p>

    <div class="footer">
        © 2026 PERFMONITOR KENYA
    </div>

</div>

</body>
</html>

<?php
session_start();
require_once "remember_auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT student_id, name, email, password_hash, role
         FROM students WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true);

        $_SESSION["student_id"] = $user["student_id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["last_activity"] = time();

        if (isset($_POST["remember"])) {
            $selector = bin2hex(random_bytes(12));
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash("sha256", $token);
            $expires = time() + (30 * 24 * 60 * 60);

            $stmt = $pdo->prepare(
                "INSERT INTO remember_tokens
                 (student_id, selector, token_hash, expires_at)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $user["student_id"],
                $selector,
                $tokenHash,
                date("Y-m-d H:i:s", $expires)
            ]);

            setcookie("remember_me", $selector . ":" . $token, [
                "expires" => $expires,
                "path" => "/StudentHub/",
                "httponly" => true,
                "secure" => !empty($_SERVER["HTTPS"])
                    && $_SERVER["HTTPS"] !== "off",
                "samesite" => "Lax"
            ]);
        }

        if ($user["role"] === "admin") {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StudentHub Login</title>
</head>
<body>
    <h2>StudentHub Login</h2>

    <p style="color:red;">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <form method="POST" action="">
        <label>Email:</label>
        <input type="email" name="email" required><br><br>

    <label><input type="checkbox" name="remember" value="1">
    Remember Me
    </label><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
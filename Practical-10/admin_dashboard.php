<?php
session_start();
require_once "remember_auth.php";

// Allow only logged-in admins
if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}

// Session timeout: 15 minutes
$timeout = 900;

if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome, Admin <?= htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8") ?>!</h2>

    <p>You have successfully accessed the admin dashboard.</p>

    <a href="logout.php">Logout</a>
</body>
</html>
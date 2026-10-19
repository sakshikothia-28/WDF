<?php
session_start();
require_once "remember_auth.php";

// Redirect users who are not logged in
if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

// Session timeout: 15 minutes
$timeout = 900;

if (isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > $timeout) {

    session_unset();
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
    <title>StudentHub Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION["name"]) ?>!</h2>

    <p>You have successfully logged in.</p>
    <p>Role: <?= htmlspecialchars($_SESSION["role"]) ?></p>

    <a href="logout.php">Logout</a>
</body>
</html>

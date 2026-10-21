<?php
session_start();
require_once "remember_auth.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

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

$name = htmlspecialchars($_SESSION["name"] ?? "", ENT_QUOTES, "UTF-8");
$role = $_SESSION["role"] ?? "student";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= $name ?>!</h2>

    <p>You have successfully logged in.</p>
    <p>Role: <?= htmlspecialchars($role, ENT_QUOTES, "UTF-8") ?></p>

    <h3>Navigation Menu</h3>
    <nav>
        <a href="dashboard.php">Dashboard</a>

        <?php if ($role === "admin"): ?>
            | <a href="admin_dashboard.php">Admin Dashboard</a>
            | <a href="students.php">Manage Students</a>
            | <a href="events.php">Manage Events</a>
        <?php else: ?>
            | <a href="events_ajax.php">View Events</a>
        <?php endif; ?>

        | <a href="logout.php">Logout</a>
    </nav>
</body>
</html>
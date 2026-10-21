<?php
session_start();
require_once "remember_auth.php";

if (
    !isset($_SESSION["student_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
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

require_once __DIR__ . "/db_mysqli.php";

// Dashboard statistics
$studentCount = 0;
$eventCount = 0;
$registrationCount = 0;
$latestRegistrations = [];
$auditLogs = [];

$result = $conn->query("SELECT COUNT(*) AS total FROM students");
if ($result) {
    $studentCount = (int)$result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM events");
if ($result) {
    $eventCount = (int)$result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM registrations");
if ($result) {
    $registrationCount = (int)$result->fetch_assoc()["total"];
}

// Latest registrations: use available columns
$result = $conn->query("SELECT * FROM registrations ORDER BY 1 DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }
}

// Recent audit log entries
$result = $conn->query(
    "SELECT log_id, user_id, action, details, created_at
     FROM audit_logs ORDER BY log_id DESC LIMIT 10"
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $auditLogs[] = $row;
    }
}

// Record dashboard access
$userId = (int)$_SESSION["student_id"];
$action = "Admin dashboard accessed";
$details = "Admin opened the dashboard";
$stmt = $conn->prepare(
    "INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)"
);
if ($stmt) {
    $stmt->bind_param("iss", $userId, $action, $details);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        header, .card, section { background: white; padding: 18px; margin-bottom: 18px; border-radius: 8px; }
        nav a { margin-right: 16px; }
        .stats { display: flex; flex-wrap: wrap; gap: 15px; }
        .card { flex: 1; min-width: 160px; }
        .number { font-size: 28px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { text-align: left; padding: 10px; border: 1px solid #ddd; overflow-wrap: anywhere; }
        th { background: #e9edf3; }
    </style>
</head>
<body>
<header>
    <h2>Welcome, Admin <?= htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8") ?>!</h2>
    <p>StudentHub Administration Panel</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="students.php">Manage Students</a>
        <a href="events.php">Manage Events</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<h3>Dashboard Statistics</h3>
<div class="stats">
    <div class="card">
        <p>Total Students</p>
        <div class="number"><?= $studentCount ?></div>
    </div>
    <div class="card">
        <p>Total Events</p>
        <div class="number"><?= $eventCount ?></div>
    </div>
    <div class="card">
        <p>Total Registrations</p>
        <div class="number"><?= $registrationCount ?></div>
    </div>
</div>

<section>
    <h3>Latest Registrations</h3>
    <?php if ($latestRegistrations): ?>
        <table>
            <tr>
                <?php foreach (array_keys($latestRegistrations[0]) as $column): ?>
                    <th><?= htmlspecialchars((string)$column, ENT_QUOTES, "UTF-8") ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($latestRegistrations as $registration): ?>
                <tr>
                    <?php foreach ($registration as $value): ?>
                        <td><?= htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No registrations found.</p>
    <?php endif; ?>
</section>

<section>
    <h3>Recent Audit Logs</h3>
    <?php if ($auditLogs): ?>
        <table>
            <tr>
                <th>Log ID</th>
                <th>User ID</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date</th>
            </tr>
            <?php foreach ($auditLogs as $log): ?>
                <tr>
                    <td><?= (int)$log["log_id"] ?></td>
                    <td><?= htmlspecialchars((string)($log["user_id"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($log["action"], ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($log["details"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($log["created_at"], ENT_QUOTES, "UTF-8") ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No audit logs yet.</p>
    <?php endif; ?>
</section>
</body>
</html>
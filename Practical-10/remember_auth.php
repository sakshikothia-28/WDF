<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "db.php";

if (isset($_SESSION["student_id"])) {
    return;
}

if (empty($_COOKIE["remember_me"])) {
    return;
}

$parts = explode(":", $_COOKIE["remember_me"], 2);

if (count($parts) !== 2) {
    setcookie("remember_me", "", time() - 3600, "/StudentHub/");
    return;
}

[$selector, $token] = $parts;

if (!ctype_xdigit($selector) || !ctype_xdigit($token)) {
    setcookie("remember_me", "", time() - 3600, "/StudentHub/");
    return;
}

$stmt = $pdo->prepare(
    "SELECT rt.*, s.name, s.email, s.role
     FROM remember_tokens rt
     JOIN students s ON s.student_id = rt.student_id
     WHERE rt.selector = ? AND rt.expires_at > NOW()"
);
$stmt->execute([$selector]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && hash_equals($row["token_hash"], hash("sha256", $token))) {
    session_regenerate_id(true);

    $_SESSION["student_id"] = $row["student_id"];
    $_SESSION["name"] = $row["name"];
    $_SESSION["email"] = $row["email"];
    $_SESSION["role"] = $row["role"];
    $_SESSION["last_activity"] = time();
} else {
    $stmt = $pdo->prepare(
        "DELETE FROM remember_tokens WHERE selector = ?"
    );
    $stmt->execute([$selector]);

    setcookie("remember_me", "", time() - 3600, "/StudentHub/");
}
?>
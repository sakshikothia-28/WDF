<?php
session_start();
require_once "db.php";

// Revoke the Remember Me token
if (!empty($_COOKIE["remember_me"])) {
    $parts = explode(":", $_COOKIE["remember_me"], 2);

    if (count($parts) === 2 && ctype_xdigit($parts[0])) {
        $stmt = $pdo->prepare(
            "DELETE FROM remember_tokens WHERE selector = ?"
        );
        $stmt->execute([$parts[0]]);
    }
}

// Clear session data
$_SESSION = [];

// Remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 42000,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => "Lax"
    ]);
}

// Remove Remember Me cookie
setcookie("remember_me", "", [
    "expires" => time() - 3600,
    "path" => "/StudentHub/",
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"])
        && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_destroy();

header("Location: login.php");
exit;
?>
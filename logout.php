<?php
declare(strict_types=1);

// 🚫 NO SPACES, NO HTML, NO ECHO ABOVE THIS LINE
session_start();

/* ---------- DESTROY SESSION ---------- */
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        false,   // 🔴 IMPORTANT: secure = false (HTTP safe)
        true
    );
}

session_destroy();

/* ---------- DELETE REMEMBER-ME COOKIES ---------- */
setcookie("user_id", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");
setcookie("account_type", "", time() - 3600, "/");

/* ---------- REDIRECT ---------- */
header("Location: index.php");
exit;
?>
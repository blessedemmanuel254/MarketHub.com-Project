<?php
declare(strict_types=1);
session_start();

/* ---------- DESTROY SESSION ---------- */
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], false, true);
}

session_destroy();

/* ---------- DELETE REMEMBER-ME COOKIES ---------- */
setcookie("user_id", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");
setcookie("account_type", "", time() - 3600, "/");

/* ---------- OUTPUT JS TO CLEAR LOCAL STORAGE AND REDIRECT ---------- */
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
    <script>
        // Clear all local storage
        localStorage.clear();
        // Redirect to homepage after clearing
        window.location.href = "index.php";
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>
HTML;

exit;
?>
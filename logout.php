<?php
session_start();
$_SESSION = [];

session_destroy();

// 3️⃣ Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,             // Expire in the past
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

setcookie("user_id", "", time() - 3600, "/", "", true, true);
setcookie("username", "", time() - 3600, "/", "", true, true);
setcookie("account_type", "", time() - 3600, "/", "", true, true);

// 5️⃣ Redirect user to login page
header("Location: index.php");
exit();
?>
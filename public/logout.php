<?php
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session.
session_destroy();

// 4. Prevent the browser from using a cached version of the previous page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// 5. Send the user back to the login page
// Since logout.php and login.php are both in /public, no prefix is needed.
header("Location: login.php");
exit;
?>
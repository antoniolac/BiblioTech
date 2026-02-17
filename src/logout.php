<?php
require 'db_connect.php';

$pdo->prepare("UPDATE sessioni SET data_logout = NOW() WHERE session_id = ?")
    ->execute([session_id()]);

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

header("Location: login.php");
exit;
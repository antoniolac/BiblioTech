<?php require 'db_connect.php';
$pdo->prepare("UPDATE sessioni SET data_logout = NOW() WHERE session_id = ?")->execute([session_id()]);
session_destroy();
header("Location: login.php");
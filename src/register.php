<?php require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO utenti (username, email, password, ruolo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['username'], $_POST['email'], $hash, $_POST['ruolo']]);
    header("Location: login.php");
} ?>
<link rel="stylesheet" href="style.css">
<form method="POST" class="auth-card">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="ruolo"><option value="user">User</option><option value="admin">Admin</option></select>
    <button type="submit">Registrati</button>
</form>
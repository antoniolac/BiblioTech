<?php require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $otp = rand(100000, 999999);
        $pdo->prepare("UPDATE utenti SET otp_code = ? WHERE id = ?")->execute([$otp, $user['id']]);
        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['auth_status'] = 'pending_2fa';
        mail($user['email'], "Codice 2FA BiblioTech", "Il tuo codice: $otp");
        header("Location: verify_2fa.php");
    } else { $error = "Credenziali errate"; }
} ?>
<link rel="stylesheet" href="style.css">
<form method="POST" class="auth-card">
    <h2>Login BiblioTech</h2>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Accedi</button>
    <p>Non hai un account? <a href="register.php">Registrati</a></p>
</form>
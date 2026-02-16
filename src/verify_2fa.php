<?php require 'db_connect.php';
if (!isset($_SESSION['auth_status'])) header("Location: login.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ? AND otp_code = ?");
    $stmt->execute([$_SESSION['temp_user_id'], $_POST['otp']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ruolo'] = $user['ruolo'];
        $pdo->prepare("UPDATE sessioni SET user_id = ? WHERE session_id = ?")->execute([$user['id'], session_id()]);
        unset($_SESSION['auth_status'], $_SESSION['temp_user_id']);
        header("Location: dashboard.php");
    } else { $error = "OTP Errato"; }
} ?>
<link rel="stylesheet" href="style.css">
<form method="POST" class="auth-card">
    <h3>Inserisci codice inviato su Mailpit</h3>
    <input type="text" name="otp" placeholder="Codice 6 cifre" required>
    <button type="submit">Verifica</button>
</form>
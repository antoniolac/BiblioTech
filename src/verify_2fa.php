<?php
require 'db_connect.php';

// Protezione: solo chi ha appena fatto login può arrivare qui
if (!isset($_SESSION['auth_status']) || $_SESSION['auth_status'] !== 'pending_2fa') {
    header("Location: login.php"); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otpInput = trim($_POST['otp'] ?? '');
    $userId   = (int) $_SESSION['temp_user_id'];

    // Cerca un OTP valido: non usato, non scaduto, appartenente all'utente
    $stmt = $pdo->prepare("
        SELECT id FROM otp_sessions
        WHERE user_id  = ?
          AND otp_code = ?
          AND usato    = 0
          AND scadenza > NOW()
        LIMIT 1
    ");
    $stmt->execute([$userId, $otpInput]);
    $otp = $stmt->fetch();

    if ($otp) {
        // Marca OTP come usato
        $pdo->prepare("UPDATE otp_sessions SET usato = 1 WHERE id = ?")
            ->execute([$otp['id']]);

        // Recupera dati utente per la sessione
        $uStmt = $pdo->prepare("SELECT id, ruolo, username FROM utenti WHERE id = ?");
        $uStmt->execute([$userId]);
        $user = $uStmt->fetch();

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['ruolo']    = $user['ruolo'];
        $_SESSION['username'] = $user['username'];

        // Collega la sessione all'utente nel DB
        $pdo->prepare("UPDATE sessioni SET user_id = ? WHERE session_id = ?")
            ->execute([$user['id'], session_id()]);

        // Pulisce i dati temporanei
        unset($_SESSION['auth_status'], $_SESSION['temp_user_id']);

        header("Location: dashboard.php"); exit;
    } else {
        $error = "Codice non valido o scaduto. Riprova o esegui di nuovo il login.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech — Verifica 2FA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-split">
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">🔐</div>
            <h1 class="brand-name">Verifica</h1>
            <p class="brand-tagline">Autenticazione<br>a due fattori.</p>
            <ul class="brand-features">
                <li>Controlla la tua email</li>
                <li>Il codice è valido 10 minuti</li>
                <li>Visualizza il messaggio su Mailpit</li>
            </ul>
        </div>
    </div>
    <div class="auth-form-wrap">
        <form method="POST" class="auth-form">
            <div class="form-header">
                <h2>Codice OTP</h2>
                <p>Ti abbiamo inviato un codice a 6 cifre via email. Inseriscilo qui sotto.</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="field">
                <label for="otp">Codice di verifica</label>
                <input type="text" id="otp" name="otp" placeholder="000000"
                       maxlength="6" pattern="\d{6}" autocomplete="one-time-code"
                       required style="letter-spacing: .4em; font-size: 1.4rem; text-align:center;">
            </div>
            <button type="submit" class="btn-primary">Verifica e Accedi</button>
            <p class="form-footer"><a href="login.php">← Torna al login</a></p>
        </form>
    </div>
</div>
</body>
</html>
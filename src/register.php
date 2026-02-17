<?php
require 'db_connect.php';

if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';
    $confirm  =       $_POST['confirm']  ?? '';

    if (strlen($username) < 3) {
        $error = "Il nome utente deve avere almeno 3 caratteri.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Indirizzo email non valido.";
    } elseif (strlen($password) < 8) {
        $error = "La password deve avere almeno 8 caratteri.";
    } elseif ($password !== $confirm) {
        $error = "Le password non coincidono.";
    } else {

        $chk = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = "Email già registrata.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO utenti (username, email, password, ruolo) VALUES (?, ?, ?, 'user')")
                ->execute([$username, $email, $hash]);
            header("Location: login.php?registered=1"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech — Registrati</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-split">
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">📚</div>
            <h1 class="brand-name">BiblioTech</h1>
            <p class="brand-tagline">Inizia a leggere.<br>Registrati oggi.</p>
            <ul class="brand-features">
                <li>Accesso al catalogo completo</li>
                <li>Gestisci i tuoi prestiti</li>
                <li>Account sicuro con 2FA</li>
            </ul>
        </div>
    </div>
    <div class="auth-form-wrap">
        <form method="POST" class="auth-form">
            <div class="form-header">
                <h2>Crea account</h2>
                <p>Compila i campi per registrarti come lettore.</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="field">
                <label for="username">Nome utente</label>
                <input type="text" id="username" name="username" placeholder="mario_rossi" required
                       minlength="3" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="nome@scuola.it" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 caratteri" required minlength="8">
            </div>
            <div class="field">
                <label for="confirm">Conferma password</label>
                <input type="password" id="confirm" name="confirm" placeholder="Ripeti la password" required>
            </div>
            <button type="submit" class="btn-primary">Registrati</button>
            <p class="form-footer">Hai già un account? <a href="login.php">Accedi</a></p>
        </form>
    </div>
</div>
</body>
</html>
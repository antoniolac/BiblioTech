<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech — Gestione Biblioteca Scolastica</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="landing-body">

<header class="landing-header">
    <div class="landing-header-inner">
        <div class="landing-logo">
            <span class="logo-icon">📚</span>
            <span class="logo-text">BiblioTech</span>
        </div>
        <nav class="landing-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn-primary">Dashboard →</a>
            <?php else: ?>
                <a href="login.php" class="btn-ghost">Accedi</a>
                <a href="register.php" class="btn-primary">Registrati</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="landing-main">
    <section class="hero">
        <div class="hero-text">
            <p class="hero-eyebrow">Biblioteca Scolastica Digitale</p>
            <h1 class="hero-title">Addio al<br><em>registro cartaceo.</em></h1>
            <p class="hero-sub">BiblioTech porta la gestione della biblioteca scolastica nel digitale:
               catalogo in tempo reale, prestiti in un click, restituzioni tracciate e accesso sicuro con 2FA.</p>
            <div class="hero-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn-primary btn-lg">Vai alla Dashboard →</a>
                <?php else: ?>
                    <a href="register.php" class="btn-primary btn-lg">Inizia ora</a>
                    <a href="login.php" class="btn-ghost btn-lg">Accedi</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-visual">
            <div class="book-stack">
                <div class="book b1"></div>
                <div class="book b2"></div>
                <div class="book b3"></div>
                <div class="book b4"></div>
                <div class="book b5"></div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="feature-card">
            <div class="feat-icon">📖</div>
            <h3>Catalogo digitale</h3>
            <p>Tutti i titoli disponibili, con autore, anno, descrizione e copie rimaste. Ricerca istantanea.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">🔄</div>
            <h3>Prestiti e restituzioni</h3>
            <p>Gli utenti prenotano il prestito in un click. I bibliotecari gestiscono le restituzioni con tracciamento completo.</p>
        </div>
        <div class="feature-card">
            <div class="feat-icon">🔐</div>
            <h3>Sicurezza 2FA</h3>
            <p>Ogni accesso è protetto da un codice OTP inviato via email, con scadenza automatica di 10 minuti.</p>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <p>BiblioTech &copy; <?= date('Y') ?> — Sistema di gestione biblioteca scolastica</p>
</footer>

</body>
</html>
<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BiblioTech - Gestione Biblioteca</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>BiblioTech 📚</h1>
            <p>Il futuro digitale della biblioteca scolastica.</p>
        </header>

        <section class="description">
            <h2>Addio al registro cartaceo</h2>
            <p>BiblioTech permette di consultare il catalogo in tempo reale, 
               prenotare prestiti e gestire le restituzioni in pochi click, 
               garantendo la massima sicurezza con l'autenticazione a due fattori.</p>
        </section>

        <nav class="auth-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn">Vai alla Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn">Accedi</a>
                <a href="register.php" class="btn btn-outline">Registrati</a>
            <?php endif; ?>
        </nav>
    </div>
</body>
</html>
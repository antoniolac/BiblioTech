<?php
// Recupero delle variabili d'ambiente impostate nel docker-compose
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'bibliotech';
$user = getenv('DB_USER') ?: 'user';
$pass = getenv('DB_PASS') ?: 'pass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In produzione non mostrare mai $e->getMessage() per sicurezza
    die("Errore di connessione al database. Verifica che i container siano attivi.");
}

// Avvio della sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- GESTIONE SESSIONE SU DATABASE ---
$sid = session_id();
// Impostiamo la scadenza a 1 ora da adesso
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Inseriamo o aggiorniamo l'attività della sessione nel DB
$stmt = $pdo->prepare("INSERT INTO sessioni (session_id, scadenza) 
                       VALUES (?, ?) 
                       ON DUPLICATE KEY UPDATE ultima_attivita = CURRENT_TIMESTAMP");
$stmt->execute([$sid, $expiry]);

// Se l'utente è loggato, aggiorniamo il riferimento user_id nella tabella sessioni
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE sessioni SET user_id = ? WHERE session_id = ?");
    $stmt->execute([$_SESSION['user_id'], $sid]);
}
?>
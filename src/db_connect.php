<?php
$host    = getenv('DB_HOST') ?: 'db';
$db      = getenv('DB_NAME') ?: 'bibliotech';
$user    = getenv('DB_USER') ?: 'user';
$pass    = getenv('DB_PASS') ?: 'pass';
$charset = 'utf8mb4';

$dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Errore di connessione al database. Verifica che i container siano attivi.");
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}


$sid       = session_id();
$scadenza  = date('Y-m-d H:i:s', strtotime('+1 hour'));
$ip        = $_SERVER['REMOTE_ADDR'] ?? null;
$ua        = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

$pdo->prepare("
    INSERT INTO sessioni (session_id, scadenza, ip_address, user_agent)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE ultima_attivita = CURRENT_TIMESTAMP
")->execute([$sid, $scadenza, $ip, $ua]);

if (isset($_SESSION['user_id'])) {
    $chk = $pdo->prepare("SELECT id FROM utenti WHERE id = ?");
    $chk->execute([$_SESSION['user_id']]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE sessioni SET user_id = ? WHERE session_id = ?")
            ->execute([$_SESSION['user_id'], $sid]);
    } else {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>
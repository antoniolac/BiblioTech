<?php require 'db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$uid = $_SESSION['user_id'];
$isAdmin = ($_SESSION['ruolo'] === 'admin');

// Logica Prestito (User)
if (isset($_POST['loan'])) {
    $lid = $_POST['libro_id'];
    $pdo->prepare("UPDATE libri SET quantita_disponibile = quantita_disponibile - 1 WHERE id = ? AND quantita_disponibile > 0")->execute([$lid]);
    $pdo->prepare("INSERT INTO prestiti (id_utente, id_libro) VALUES (?, ?)")->execute([$uid, $lid]);
}

// Logica Restituzione (Admin)
if (isset($_POST['return']) && $isAdmin) {
    $pid = $_POST['prestito_id'];
    $stmt = $pdo->prepare("SELECT id_libro FROM prestiti WHERE id = ?"); $stmt->execute([$pid]);
    $lid = $stmt->fetch()['id_libro'];
    $pdo->prepare("UPDATE prestiti SET data_fine = NOW(), stato = 'restituito' WHERE id = ?")->execute([$pid]);
    $pdo->prepare("UPDATE libri SET quantita_disponibile = quantita_disponibile + 1 WHERE id = ?")->execute([$lid]);
}

$libri = $pdo->query("SELECT * FROM libri")->fetchAll();
$prestiti = $isAdmin ? 
    $pdo->query("SELECT p.id, u.username, l.titolo FROM prestiti p JOIN utenti u ON p.id_utente=u.id JOIN libri l ON p.id_libro=l.id WHERE p.stato='attivo'")->fetchAll() :
    $pdo->prepare("SELECT l.titolo, p.data_inizio, p.stato FROM prestiti p JOIN libri l ON p.id_libro=l.id WHERE p.id_utente=?");
if(!$isAdmin) $prestiti->execute([$uid]);
?>
<link rel="stylesheet" href="style.css">
<div class="container">
    <h1>BiblioTech - Dashboard (<?= $_SESSION['ruolo'] ?>)</h1>
    <a href="logout.php">Logout</a>
    <h2>Catalogo</h2>
    <table>
        <?php foreach($libri as $l): ?>
        <tr>
            <td><?= $l['titolo'] ?> (<?= $l['quantita_disponibile'] ?>)</td>
            <td><?php if(!$isAdmin && $l['quantita_disponibile'] > 0): ?>
                <form method="POST"><input type="hidden" name="libro_id" value="<?= $l['id'] ?>"><button name="loan">Prendi</button></form>
            <?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$uid      = (int)   $_SESSION['user_id'];
$isAdmin  = ($_SESSION['ruolo'] === 'admin');
$username = htmlspecialchars($_SESSION['username'] ?? 'Utente');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['loan']) && !$isAdmin) {
        $lid = (int) $_POST['libro_id'];
        $chk = $pdo->prepare("SELECT id FROM prestiti WHERE id_utente=? AND id_libro=? AND stato='attivo'");
        $chk->execute([$uid, $lid]);
        if (!$chk->fetch()) {
            $pdo->prepare("UPDATE libri SET quantita_disponibile = quantita_disponibile - 1 WHERE id=? AND quantita_disponibile > 0")->execute([$lid]);
            $pdo->prepare("INSERT INTO prestiti (id_utente, id_libro) VALUES (?,?)")->execute([$uid, $lid]);
        }
        header("Location: dashboard.php?tab=catalog&msg=loaned"); exit;
    }

    if (isset($_POST['return']) && $isAdmin) {
        $pid = (int) $_POST['prestito_id'];
        $row = $pdo->prepare("SELECT id_libro FROM prestiti WHERE id=?");
        $row->execute([$pid]);
        $book = $row->fetch();
        if ($book) {
            $pdo->prepare("UPDATE prestiti SET data_fine=NOW(), stato='restituito' WHERE id=?")->execute([$pid]);
            $pdo->prepare("UPDATE libri SET quantita_disponibile = quantita_disponibile + 1 WHERE id=?")->execute([$book['id_libro']]);
        }
        header("Location: dashboard.php?tab=loans&msg=returned"); exit;
    }
}


$libri = $pdo->query("
    SELECT id, titolo, autore, anno_pubblicazione, quantita_totale,
           quantita_disponibile, descrizione
    FROM libri ORDER BY titolo ASC
")->fetchAll();

$mieiLibri = [];
if (!$isAdmin) {
    $s = $pdo->prepare("SELECT id_libro FROM prestiti WHERE id_utente=? AND stato='attivo'");
    $s->execute([$uid]);
    $mieiLibri = array_column($s->fetchAll(), 'id_libro');
}

if ($isAdmin) {
    $prestitiAttivi = $pdo->query("
        SELECT p.id, u.username, u.email,
               l.titolo, l.autore, l.anno_pubblicazione,
               p.data_inizio,
               DATEDIFF(NOW(), p.data_inizio) AS giorni
        FROM prestiti p
        JOIN utenti u ON p.id_utente = u.id
        JOIN libri  l ON p.id_libro  = l.id
        WHERE p.stato = 'attivo'
        ORDER BY p.data_inizio ASC
    ")->fetchAll();

    $statLibri     = $pdo->query("SELECT COUNT(*) FROM libri")->fetchColumn();
    $statAttivi    = $pdo->query("SELECT COUNT(*) FROM prestiti WHERE stato='attivo'")->fetchColumn();
    $statUtenti    = $pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo='user'")->fetchColumn();
    $statRestituiti= $pdo->query("SELECT COUNT(*) FROM prestiti WHERE stato='restituito'")->fetchColumn();
} else {
    $s = $pdo->prepare("
        SELECT l.titolo, l.autore, l.anno_pubblicazione,
               p.data_inizio, p.data_fine, p.stato
        FROM prestiti p
        JOIN libri l ON p.id_libro = l.id
        WHERE p.id_utente = ?
        ORDER BY p.data_inizio DESC
    ");
    $s->execute([$uid]);
    $storico = $s->fetchAll();
}

$tab = $_GET['tab'] ?? ($isAdmin ? 'loans' : 'catalog');
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dash-body">

<header class="dash-header">
    <div class="dash-brand">
        <span class="logo-icon">📚</span>
        <span class="logo-text">BiblioTech</span>
    </div>
    <div class="dash-header-right">
        <span class="dash-welcome">Ciao, <strong><?= $username ?></strong></span>
        <span class="role-badge <?= $isAdmin ? 'role-admin' : 'role-user' ?>">
            <?= $isAdmin ? 'Bibliotecario' : 'Lettore' ?>
        </span>
        <a href="logout.php" class="btn-ghost btn-sm">Esci</a>
    </div>
</header>

<div class="dash-layout">

    <aside class="dash-sidebar">
        <?php if ($isAdmin): ?>
        <div class="sidebar-stats">
            <div class="stat-card"><div class="stat-val"><?= $statAttivi ?></div><div class="stat-label">Prestiti attivi</div></div>
            <div class="stat-card"><div class="stat-val"><?= $statLibri ?></div><div class="stat-label">Titoli</div></div>
            <div class="stat-card"><div class="stat-val"><?= $statUtenti ?></div><div class="stat-label">Lettori</div></div>
            <div class="stat-card"><div class="stat-val"><?= $statRestituiti ?></div><div class="stat-label">Restituiti</div></div>
        </div>
        <?php endif; ?>

        <nav class="sidebar-nav">
            <p class="nav-label">Navigazione</p>
            <?php if ($isAdmin): ?>
            <button class="nav-btn <?= $tab==='loans'?'active':'' ?>"   onclick="goTab('loans',   this)">
                <span class="nav-icon">📋</span> Prestiti attivi
                <?php if ($statAttivi > 0): ?><span class="nav-badge"><?= $statAttivi ?></span><?php endif; ?>
            </button>
            <?php endif; ?>
            <button class="nav-btn <?= $tab==='catalog'?'active':'' ?>" onclick="goTab('catalog', this)">
                <span class="nav-icon">📖</span> Catalogo
            </button>
            <?php if (!$isAdmin): ?>
            <button class="nav-btn <?= $tab==='history'?'active':'' ?>" onclick="goTab('history', this)">
                <span class="nav-icon">🕓</span> I miei prestiti
                <?php $nAttivi = count(array_filter($storico ?? [], fn($p)=>$p['stato']==='attivo'));
                      if ($nAttivi > 0): ?><span class="nav-badge"><?= $nAttivi ?></span><?php endif; ?>
            </button>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="dash-main">

        <?php if ($msg === 'loaned'): ?>
        <div class="toast toast-ok">✅ Prestito registrato con successo.</div>
        <?php elseif ($msg === 'returned'): ?>
        <div class="toast toast-ok">✅ Restituzione registrata. Il libro è di nuovo disponibile.</div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
        <section class="dash-section <?= $tab==='loans'?'active':'' ?>" id="sec-loans">
            <div class="section-head">
                <div>
                    <h2>Prestiti attivi</h2>
                    <p>Gestisci i libri attualmente in prestito e registra le restituzioni.</p>
                </div>
                <input class="search-input" type="text" placeholder="🔍 Cerca…" oninput="filterRows('loans-body', this.value)">
            </div>

            <div class="table-card">
                <table>
                    <thead><tr>
                        <th>Lettore</th>
                        <th>Email</th>
                        <th>Titolo</th>
                        <th>Autore</th>
                        <th>Anno</th>
                        <th>Data prestito</th>
                        <th>Giorni</th>
                        <th>Azione</th>
                    </tr></thead>
                    <tbody id="loans-body">
                    <?php if (!empty($prestitiAttivi)): foreach ($prestitiAttivi as $p):
                        $g = (int) $p['giorni'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['username']) ?></strong></td>
                        <td class="text-muted"><?= htmlspecialchars($p['email']) ?></td>
                        <td><?= htmlspecialchars($p['titolo']) ?></td>
                        <td><?= htmlspecialchars($p['autore']) ?></td>
                        <td><?= htmlspecialchars($p['anno_pubblicazione'] ?? '—') ?></td>
                        <td><?= date('d/m/Y', strtotime($p['data_inizio'])) ?></td>
                        <td>
                            <span class="<?= $g > 14 ? 'text-warn' : 'text-ok' ?>">
                                <?= $g ?> gg<?= $g > 14 ? ' ⚠️' : '' ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Confermare la restituzione di «<?= htmlspecialchars($p['titolo'], ENT_QUOTES) ?>»?')">
                                <input type="hidden" name="prestito_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="return" class="btn-action btn-return">Registra restituzione</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">✅</div><p>Nessun prestito attivo al momento.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <section class="dash-section <?= $tab==='catalog'?'active':'' ?>" id="sec-catalog">
            <div class="section-head">
                <div>
                    <h2>Catalogo Libri</h2>
                    <p><?= $isAdmin ? 'Panoramica di tutti i titoli e le disponibilità.' : 'Sfoglia i libri disponibili e prenota il tuo prestito.' ?></p>
                </div>
                <input class="search-input" type="text" placeholder="🔍 Cerca titolo, autore…" oninput="filterRows('catalog-body', this.value)">
            </div>

            <div class="books-grid" id="catalog-body">
            <?php foreach ($libri as $l):
                $disp = (int) $l['quantita_disponibile'];
                $tot  = (int) ($l['quantita_totale'] ?: 1);
                $pct  = round($disp / $tot * 100);
                $giaInPrestito = in_array($l['id'], $mieiLibri);
            ?>
            <div class="book-card" data-search="<?= strtolower(htmlspecialchars($l['titolo'].' '.$l['autore'].' '.($l['anno_pubblicazione']??''))) ?>">
                <div class="book-spine" style="background: <?= sprintf('hsl(%d,45%%,30%%)', crc32($l['titolo']) % 360) ?>"></div>
                <div class="book-body">
                    <div class="book-meta-top">
                        <span class="book-year"><?= htmlspecialchars($l['anno_pubblicazione'] ?? '—') ?></span>
                        <?php if ($disp === 0): ?>
                            <span class="badge badge-none">Esaurito</span>
                        <?php elseif ($pct < 40): ?>
                            <span class="badge badge-warn">Ultime copie</span>
                        <?php else: ?>
                            <span class="badge badge-ok"><?= $disp ?>/<?= $tot ?> disponibili</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="book-title"><?= htmlspecialchars($l['titolo']) ?></h3>
                    <p class="book-author"><?= htmlspecialchars($l['autore']) ?></p>
                    <?php if (!empty($l['descrizione'])): ?>
                    <p class="book-desc"><?= htmlspecialchars($l['descrizione']) ?></p>
                    <?php endif; ?>

                    <div class="book-footer">
                        <div class="avail-bar">
                            <div class="avail-fill" style="width:<?= $pct ?>%;
                                background: <?= $disp===0?'#c0392b':($pct<40?'#e67e22':'#27ae60') ?>">
                            </div>
                        </div>
                        <?php if (!$isAdmin): ?>
                            <?php if ($disp > 0 && !$giaInPrestito): ?>
                            <form method="POST">
                                <input type="hidden" name="libro_id" value="<?= $l['id'] ?>">
                                <button type="submit" name="loan" class="btn-action btn-loan">Prendi in prestito</button>
                            </form>
                            <?php elseif ($giaInPrestito): ?>
                            <span class="badge badge-info">Già in tuo possesso</span>
                            <?php else: ?>
                            <span class="badge badge-none">Non disponibile</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($libri)): ?>
            <div class="empty-state" style="grid-column:1/-1">
                <div class="empty-icon">📭</div>
                <p>Nessun libro in catalogo.</p>
            </div>
            <?php endif; ?>
            </div>
        </section>

        <?php if (!$isAdmin): ?>
        <section class="dash-section <?= $tab==='history'?'active':'' ?>" id="sec-history">
            <div class="section-head">
                <div>
                    <h2>I miei prestiti</h2>
                    <p>Storico completo dei libri che hai preso in prestito.</p>
                </div>
            </div>

            <div class="table-card">
                <table>
                    <thead><tr>
                        <th>Titolo</th>
                        <th>Autore</th>
                        <th>Anno</th>
                        <th>Data prestito</th>
                        <th>Data restituzione</th>
                        <th>Stato</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($storico)): foreach ($storico as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['titolo']) ?></strong></td>
                        <td><?= htmlspecialchars($p['autore']) ?></td>
                        <td><?= htmlspecialchars($p['anno_pubblicazione'] ?? '—') ?></td>
                        <td><?= date('d/m/Y', strtotime($p['data_inizio'])) ?></td>
                        <td><?= $p['data_fine'] ? date('d/m/Y', strtotime($p['data_fine'])) : '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <?php if ($p['stato'] === 'attivo'): ?>
                                <span class="badge badge-info">In corso</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Restituito</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">📚</div>
                            <p>Non hai ancora effettuato prestiti.<br>Vai al catalogo per iniziare!</p>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

    </main>
</div>

<script>
function goTab(name, btn) {
    document.querySelectorAll('.dash-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    const sec = document.getElementById('sec-' + name);
    if (sec) sec.classList.add('active');
    if (btn) btn.classList.add('active');
    history.replaceState(null, '', '?tab=' + name);
}

//ricercastorico
function filterRows(tbodyId, query) {
    const q = query.toLowerCase();
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    tbody.querySelectorAll('tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

//ricerca catalogo
document.querySelectorAll('[oninput]').forEach(inp => {
    if (inp.placeholder.includes('titolo')) {
        inp.oninput = function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.book-card').forEach(card => {
                card.style.display = (card.dataset.search || '').includes(q) ? '' : 'none';
            });
        };
    }
});
</script>
</body>
</html>
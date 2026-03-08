<?php
require_once("config.php");

// Se l'utente non è loggato, lo rimandiamo al login
if (!isset($_SESSION['utente_loggato'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['utente_loggato'];
$messaggio = $errore = '';

// Gestione aggiunta ed eliminazione indirizzo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['aggiungi_indirizzo'])) {
        try {
            $stmt_ind = $pdo->prepare("INSERT INTO tindirizzo_di_consegna (n_civico, cap, via, citta, username_account, attivo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt_ind->execute([trim($_POST['civico']), trim($_POST['cap']), trim($_POST['via']), trim($_POST['citta']), $username]);
            $messaggio = "Nuovo indirizzo aggiunto con successo!";
        } catch (\PDOException $e) {
            $errore = "Errore durante l'aggiunta: " . $e->getMessage();
        }
    } elseif (isset($_POST['elimina_indirizzo'])) {
        try {
            // "Soft delete": settiamo attivo = 0 invece di eliminare la riga
            $stmt_del = $pdo->prepare("UPDATE tindirizzo_di_consegna SET attivo = 0 WHERE id_indirizzo = ? AND username_account = ?");
            $stmt_del->execute([$_POST['id_indirizzo'], $username]);
            $messaggio = "Indirizzo rimosso dalla rubrica.";
        } catch (\PDOException $e) {
            $errore = "Errore durante l'eliminazione: " . $e->getMessage();
        }
    }
}

// Recupero gli indirizzi attivi dell'utente
$stmt_miei_indirizzi = $pdo->prepare("SELECT * FROM tindirizzo_di_consegna WHERE username_account = ? AND attivo = 1");
$stmt_miei_indirizzi->execute([$username]);
$indirizzi = $stmt_miei_indirizzi->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I Miei Indirizzi - Appane</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header">
        <a href="index.php" class="logo-link"><img src="appane logo.jpg" alt="Logo Appane" style="height: 70px; width: auto;"></a>
        <div class="nav-title" style="color:white; font-size:1.2rem; font-weight:bold; margin-left:20px;">LA MIA RUBRICA</div>
    </header>
    <nav class="sub-nav" style="background: #FFF8E7; padding: 12px 30px; border-bottom: 3px solid #D4A373;">
        <div><a href="index.php" style="color: #5E3A8C; font-weight: bold; text-decoration: none;">← Torna al menù</a></div>
    </nav>
    <main class="content-area">
        <div class="form-container" style="max-width: 800px; margin: 0 auto;">
            <h2 style="color: #8B4513; text-align: center; margin-bottom: 30px;">I Tuoi Indirizzi di Consegna</h2>
            
            <?php if($messaggio) echo "<div class='alert alert-success'>$messaggio</div>"; ?>
            <?php if($errore) echo "<div class='alert alert-error'>$errore</div>"; ?>

            <?php if (empty($indirizzi)): ?>
                <p style="text-align:center; color: #888; padding: 20px 0;">Non hai ancora salvato nessun indirizzo.</p>
            <?php else: ?>
                <div class="grid-layout" style="margin-bottom: 40px;">
                    <?php foreach ($indirizzi as $ind): ?>
                        <div class="card" style="padding: 20px; border-left: 5px solid #5E3A8C; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #8B4513; font-size: 1.1rem;"><?php echo htmlspecialchars($ind['via'] . ', ' . $ind['n_civico']); ?></strong><br>
                                <span style="color: #666;"><?php echo htmlspecialchars($ind['cap'] . ' - ' . $ind['citta']); ?></span>
                            </div>
                            <form method="POST" onsubmit="return confirm('Vuoi davvero rimuovere questo indirizzo?');">
                                <input type="hidden" name="id_indirizzo" value="<?php echo $ind['id_indirizzo']; ?>">
                                <button type="submit" name="elimina_indirizzo" class="btn" style="background: #D6604D; color: white; padding: 8px 12px; font-size: 0.9rem;">Elimina</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <hr style="border: 1px solid #D4A373; margin: 30px 0;">

            <h3 style="color:#5E3A8C; margin-bottom:15px; text-align: center;">Aggiungi un nuovo indirizzo</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;"><label class="form-label">Via / Piazza</label><input type="text" name="via" class="form-control" required></div>
                    <div class="form-col"><label class="form-label">N. Civico</label><input type="text" name="civico" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-col"><label class="form-label">Città</label><input type="text" name="citta" class="form-control" required></div>
                    <div class="form-col"><label class="form-label">CAP</label><input type="text" name="cap" class="form-control" required></div>
                </div>
                <button type="submit" name="aggiungi_indirizzo" class="btn btn-purple" style="width: 100%;">Salva Indirizzo</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
<?php
require_once("config.php");

if (!isset($_SESSION['utente_loggato'])) {
    header("Location: scelta.php");
    exit;
}

$messaggio = '';
$username = $_SESSION['utente_loggato'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (empty($_SESSION['carrello'])) {
            throw new \Exception("Il tuo carrello è vuoto!");
        }

        $pdo->beginTransaction();

        // 1. Inserimento Indirizzo per questo ordine
        $stmt_ind = $pdo->prepare("INSERT INTO tindirizzo_di_consegna (n_civico, cap, via, citta, username_account) VALUES (?, ?, ?, ?, ?)");
        $stmt_ind->execute([$_POST['civico'], $_POST['cap'], $_POST['via'], $_POST['citta'], $username]);
        $id_indirizzo = $pdo->lastInsertId();

        // 2. Calcolo Totale
        $importo_totale = 0;
        foreach ($_SESSION['carrello'] as $item) {
            $importo_totale += $item['prezzo'] * $item['quantita'];
        }

        // 3. Recupero Menù Attivo
        $stmt_menu = $pdo->query("SELECT id_menu FROM tmenu_settimanale ORDER BY id_menu DESC LIMIT 1");
        $menu_attivo = $stmt_menu->fetch();
        if (!$menu_attivo) throw new \Exception("Nessun menù settimanale attivo al momento.");
        $id_menu = $menu_attivo['id_menu'];

        // 4. Inserimento Ordine
        $stmt_ord = $pdo->prepare("INSERT INTO tordine (importo, data, stato, id_indirizzo, username_account, id_menu) VALUES (?, NOW(), 'In attesa', ?, ?, ?)");
        $stmt_ord->execute([$importo_totale, $id_indirizzo, $username, $id_menu]);
        $id_ordine = $pdo->lastInsertId();

        // 5. Inserimento Prodotti Selezionati
        $stmt_sel = $pdo->prepare("INSERT INTO tselezione (id_ordine, nome_prodotto, id_menu, quantita) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['carrello'] as $nome_prod => $dati) {
            $stmt_sel->execute([$id_ordine, $nome_prod, $id_menu, $dati['quantita']]);
        }
        
        $pdo->commit();
        $_SESSION['carrello'] = []; // Svuota il carrello
        $messaggio = "Ordine confermato con successo! Grazie per aver acquistato da Appane.";

    } catch (\PDOException $e) {
        $pdo->rollBack();
        $messaggio = "Errore durante la conferma: " . $e->getMessage();
    } catch (\Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $messaggio = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Checkout - Appane</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a></header>
    <main class="content-area" style="display:flex; justify-content:center; align-items:center;">
        <div class="form-container" style="width: 700px;">
            <h2 style="color: #8B4513; text-align:center; margin-bottom: 20px;">INDIRIZZO DI SPEDIZIONE</h2>
            
            <?php if($messaggio): ?>
                <div class='alert alert-success'><?php echo $messaggio; ?></div>
                <div style="text-align: center; margin-top: 20px;"><a href="index.php" class="btn btn-purple">Torna alla Home</a></div>
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 20px;">Bentornato, <strong><?php echo htmlspecialchars($username); ?></strong>! Dove spediamo il tuo pane?</p>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-col" style="flex: 2;"><label class="form-label">Via / Piazza</label><input type="text" name="via" class="form-control" required></div>
                        <div class="form-col"><label class="form-label">N. Civico</label><input type="text" name="civico" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-col"><label class="form-label">Città</label><input type="text" name="citta" class="form-control" required></div>
                        <div class="form-col"><label class="form-label">CAP</label><input type="text" name="cap" class="form-control" required></div>
                    </div>
                    <button type="submit" class="btn btn-bread" style="width: 100%; margin-top: 20px; font-size: 1.2rem;">Conferma Ordine Definitivo</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>

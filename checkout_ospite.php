<?php
require_once("config.php");

$messaggio = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_username = 'ospite_' . time() . rand(10, 99);
    $email = trim($_POST['email']);
    
    try {
        if (empty($_SESSION['carrello'])) {
            throw new \Exception("Il tuo carrello è vuoto!");
        }

        $pdo->beginTransaction();
        
        // Creazione account ombra
        $stmt_acc = $pdo->prepare("INSERT INTO taccount (username, password) VALUES (?, ?)");
        $stmt_acc->execute([$guest_username, 'guest_pass_123']);
        
        // Inserimento o aggiornamento cliente con campi 'nome' e 'cognome' separati
        $stmt_cli = $pdo->prepare("INSERT INTO tcliente (email, n_telefono, nome, cognome) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE n_telefono = VALUES(n_telefono), nome = VALUES(nome), cognome = VALUES(cognome)");
        $stmt_cli->execute([$email, trim($_POST['telefono']), trim($_POST['nome']), trim($_POST['cognome'])]);
        
        // Registrazione ombra
        $stmt_reg = $pdo->prepare("INSERT INTO tregistrazione (email_cliente, username_account, data) VALUES (?, ?, CURDATE())");
        $stmt_reg->execute([$email, $guest_username]);

        // Inserimento Indirizzo
        $stmt_ind = $pdo->prepare("INSERT INTO tindirizzo_di_consegna (n_civico, cap, via, citta, username_account) VALUES (?, ?, ?, ?, ?)");
        $stmt_ind->execute([$_POST['civico'], $_POST['cap'], $_POST['via'], $_POST['citta'], $guest_username]);
        $id_indirizzo = $pdo->lastInsertId();

        // Calcolo Totale
        $importo_totale = 0;
        foreach ($_SESSION['carrello'] as $item) {
            $importo_totale += $item['prezzo'] * $item['quantita'];
        }

        // Recupero Menù Attivo
        $stmt_menu = $pdo->query("SELECT id_menu FROM tmenu_settimanale ORDER BY id_menu DESC LIMIT 1");
        $menu_attivo = $stmt_menu->fetch();
        if (!$menu_attivo) throw new \Exception("Nessun menù settimanale attivo al momento.");
        $id_menu = $menu_attivo['id_menu'];

        // Inserimento Ordine
        $stmt_ord = $pdo->prepare("INSERT INTO tordine (importo, data, stato, id_indirizzo, username_account, id_menu) VALUES (?, NOW(), 'In attesa', ?, ?, ?)");
        $stmt_ord->execute([$importo_totale, $id_indirizzo, $guest_username, $id_menu]);
        $id_ordine = $pdo->lastInsertId();

        // Inserimento Prodotti Selezionati
        $stmt_sel = $pdo->prepare("INSERT INTO tselezione (id_ordine, nome_prodotto, id_menu, quantita) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['carrello'] as $nome_prod => $dati) {
            $stmt_sel->execute([$id_ordine, $nome_prod, $id_menu, $dati['quantita']]);
        }
        
        $pdo->commit();
        $_SESSION['carrello'] = []; // Svuota il carrello
        $messaggio = "Ordine confermato con successo come ospite! Riceverai a breve una conferma.";

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
<head><meta charset="UTF-8"><title>Dati Ospite - Appane</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a></header>
    <main class="content-area" style="display:flex; justify-content:center; align-items:center;">
        <div class="form-container" style="width: 700px;">
            <h2 style="color: #8B4513; text-align:center; margin-bottom: 20px;">INSERIMENTO DATI (Ospite)</h2>
            
            <?php if($messaggio): ?>
                <div class='alert alert-success'><?php echo $messaggio; ?></div>
                <div style="text-align: center; margin-top: 20px;"><a href="index.php" class="btn btn-purple">Torna alla Home</a></div>
            <?php else: ?>
                <form method="POST">
                    <h3 style="color:#5E3A8C; margin-bottom:10px;">I Tuoi Dati</h3>
                    <div class="form-row">
                        <div class="form-col"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="form-col"><label class="form-label">Cognome</label><input type="text" name="cognome" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-col"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="form-col"><label class="form-label">Telefono</label><input type="text" name="telefono" class="form-control" required></div>
                    </div>
                    
                    <h3 style="color:#5E3A8C; margin: 20px 0 10px 0;">Il Tuo Indirizzo</h3>
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

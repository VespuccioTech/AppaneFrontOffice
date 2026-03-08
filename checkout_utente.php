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

        // 1. Gestione Indirizzo: Controllo se ha scelto un indirizzo esistente o uno nuovo
        $id_indirizzo = null;
        if (isset($_POST['id_indirizzo_scelto']) && $_POST['id_indirizzo_scelto'] !== 'nuovo') {
            $id_indirizzo = $_POST['id_indirizzo_scelto'];
            
            // Verifica di sicurezza (per assicurarci che l'indirizzo sia suo)
            $check = $pdo->prepare("SELECT id_indirizzo FROM tindirizzo_di_consegna WHERE id_indirizzo = ? AND username_account = ?");
            $check->execute([$id_indirizzo, $username]);
            if (!$check->fetch()) throw new \Exception("Indirizzo non valido.");
        } else {
            // Se ha scelto "Nuovo Indirizzo", lo inseriamo e lo leghiamo al suo account
            if(empty($_POST['via']) || empty($_POST['civico']) || empty($_POST['citta']) || empty($_POST['cap'])) {
                throw new \Exception("Devi compilare tutti i campi del nuovo indirizzo.");
            }
            $stmt_ind = $pdo->prepare("INSERT INTO tindirizzo_di_consegna (n_civico, cap, via, citta, username_account, attivo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt_ind->execute([trim($_POST['civico']), trim($_POST['cap']), trim($_POST['via']), trim($_POST['citta']), $username]);
            $id_indirizzo = $pdo->lastInsertId();
        }

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

// Recupero gli indirizzi salvati per mostrarli nel form
$stmt_miei_indirizzi = $pdo->prepare("SELECT * FROM tindirizzo_di_consegna WHERE username_account = ? AND attivo = 1");
$stmt_miei_indirizzi->execute([$username]);
$indirizzi = $stmt_miei_indirizzi->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Appane</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Semplice script per mostrare/nascondere i campi del nuovo indirizzo
        function gestisciFormIndirizzo() {
            var radios = document.getElementsByName('id_indirizzo_scelto');
            var nuovoForm = document.getElementById('form_nuovo_indirizzo');
            var inputs = nuovoForm.getElementsByTagName('input');
            
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    if (radios[i].value === 'nuovo') {
                        nuovoForm.style.display = 'block';
                        for(var j=0; j<inputs.length; j++) inputs[j].required = true;
                    } else {
                        nuovoForm.style.display = 'none';
                        for(var j=0; j<inputs.length; j++) inputs[j].required = false;
                    }
                }
            }
        }

        function controllaCAP(event) {
            var radios = document.getElementsByName('id_indirizzo_scelto');
            var capScelto = '';
            
            // Troviamo quale opzione è selezionata
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    if (radios[i].value === 'nuovo') {
                        // Se è un nuovo indirizzo, prendiamo il valore dalla casella di testo
                        capScelto = document.querySelector('input[name="cap"]').value.trim();
                    } else {
                        // Se è un indirizzo salvato, prendiamo il valore dall'attributo data-cap
                        capScelto = radios[i].getAttribute('data-cap').trim();
                    }
                    break;
                }
            }

            // Se il CAP c'è ma non inizia per "34"
            if (capScelto && !capScelto.startsWith('34')) {
                var procedi = confirm("ATTENZIONE:\nIl CAP indicato (" + capScelto + ") non inizia con 34.\n\nL'ordine potrebbe non arrivare (potrai monitorarlo dallo stato dell'ordine). Vuoi procedere comunque?");
                
                if (!procedi) {
                    return false; // Se l'utente clicca Annulla, ferma l'invio del form
                }
            }
            return true; // Se il CAP è 34... o l'utente ha cliccato OK, invia il form
        }
    </script>
</head>
<body onload="gestisciFormIndirizzo()">
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><img src="appane logo.jpg" alt="Logo Appane" style="height: 70px; width: auto;"></a></header>
    <main class="content-area" style="display:flex; justify-content:center; align-items:center;">
        <div class="form-container" style="width: 700px;">
            <h2 style="color: #8B4513; text-align:center; margin-bottom: 20px;">INDIRIZZO DI SPEDIZIONE</h2>
            
            <?php if($messaggio): ?>
                <div class='alert alert-success'><?php echo $messaggio; ?></div>
                <div style="text-align: center; margin-top: 20px;"><a href="index.php" class="btn btn-purple">Torna alla Home</a></div>
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 20px;">Bentornato, <strong><?php echo htmlspecialchars($username); ?></strong>! Dove spediamo il tuo pane?</p>
                
                <form method="POST" onsubmit="return controllaCAP(event);">
                    
                    <div style="margin-bottom: 20px;">
                        <?php if (!empty($indirizzi)): ?>
                            <h3 style="color:#5E3A8C; margin-bottom:10px; font-size: 1.1rem;">Scegli un indirizzo salvato:</h3>
                            <?php foreach ($indirizzi as $index => $ind): ?>
                                <label style="display: block; padding: 10px; border: 1px solid #D4A373; border-radius: 5px; margin-bottom: 10px; background: #FFFAF4; cursor: pointer;">
                                    <input type="radio" name="id_indirizzo_scelto" value="<?php echo $ind['id_indirizzo']; ?>" data-cap="<?php echo htmlspecialchars($ind['cap']); ?>" onclick="gestisciFormIndirizzo()" <?php echo ($index === 0) ? 'checked' : ''; ?>>
                                    <strong style="color: #8B4513;"><?php echo htmlspecialchars($ind['via'] . ', ' . $ind['n_civico']); ?></strong>
                                    - <?php echo htmlspecialchars($ind['cap'] . ' ' . $ind['citta']); ?>
                                </label>
                            <?php endforeach; ?>
                            
                            <label style="display: block; padding: 10px; border: 1px solid #D4A373; border-radius: 5px; margin-bottom: 10px; background: #eee; cursor: pointer;">
                                <input type="radio" name="id_indirizzo_scelto" value="nuovo" onclick="gestisciFormIndirizzo()">
                                <strong>Oppure inserisci un nuovo indirizzo...</strong>
                            </label>
                        <?php else: ?>
                            <input type="radio" name="id_indirizzo_scelto" value="nuovo" checked style="display:none;">
                            <h3 style="color:#5E3A8C; margin-bottom:10px; font-size: 1.1rem;">Inserisci i dati di spedizione:</h3>
                        <?php endif; ?>
                    </div>

                    <div id="form_nuovo_indirizzo" style="<?php echo !empty($indirizzi) ? 'display: none;' : 'display: block;'; ?>">
                        <div class="form-row">
                            <div class="form-col" style="flex: 2;"><label class="form-label">Via / Piazza</label><input type="text" name="via" class="form-control"></div>
                            <div class="form-col"><label class="form-label">N. Civico</label><input type="text" name="civico" class="form-control"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-col"><label class="form-label">Città</label><input type="text" name="citta" class="form-control"></div>
                            <div class="form-col"><label class="form-label">CAP</label><input type="text" name="cap" class="form-control"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-bread" style="width: 100%; margin-top: 20px; font-size: 1.2rem;">Conferma Ordine Definitivo</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
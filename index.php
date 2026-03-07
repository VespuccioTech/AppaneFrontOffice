<?php
require_once("config.php");

// Inizializza carrello
if (!isset($_SESSION['carrello'])) { $_SESSION['carrello'] = []; }

// Gestione aggiunta e rimozione dal carrello
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_prod = $_POST['nome_prodotto'];
    $prezzo = $_POST['prezzo'];

    if (isset($_POST['aggiungi'])) {
        if (isset($_SESSION['carrello'][$nome_prod])) {
            $_SESSION['carrello'][$nome_prod]['quantita']++;
        } else {
            $_SESSION['carrello'][$nome_prod] = [
                'prezzo' => $prezzo,
                'quantita' => 1
            ];
        }
    } elseif (isset($_POST['rimuovi'])) {
        if (isset($_SESSION['carrello'][$nome_prod])) {
            $_SESSION['carrello'][$nome_prod]['quantita']--;
            if ($_SESSION['carrello'][$nome_prod]['quantita'] <= 0) {
                unset($_SESSION['carrello'][$nome_prod]);
            }
        }
    }
}

// Estrae SOLO i prodotti del menù attivo, con ingredienti e immagini
$sql = "SELECT p.*, 
               GROUP_CONCAT(c.nome_ingrediente SEPARATOR ', ') as ingredienti,
               (SELECT percorso_file FROM timmagine_prodotto ip WHERE ip.nome_prodotto = p.nome LIMIT 1) as immagine
        FROM tprodotto p
        JOIN tproduzione pr ON p.nome = pr.nome_prodotto
        LEFT JOIN tcomposizione c ON p.nome = c.nome_prodotto
        WHERE pr.id_menu = (SELECT id_menu FROM tmenu_settimanale ORDER BY id_menu DESC LIMIT 1)
        GROUP BY p.nome";

$prodotti = $pdo->query($sql)->fetchAll();
$totale_carrello = array_sum(array_column($_SESSION['carrello'], 'quantita'));
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Appane - Il nostro Pane</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header">
        <a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a>
        <div class="header-nav-group">
            <?php if(isset($_SESSION['utente_loggato'])): ?>
                <span style="color: white; font-weight: bold; margin-right: 15px;">
                    Ciao <?php echo htmlspecialchars($_SESSION['utente_loggato']); ?>! 
                    <a href="logout.php" style="color: #E9D5FF; font-size: 0.8rem; margin-left: 10px; text-decoration: none;">(Esci)</a>
                </span>
            <?php else: ?>
                <a href="login.php" style="color: white; font-weight: bold; margin-right: 15px; text-decoration: none;">LOGIN / REGISTRATI</a>
            <?php endif; ?>
            <a href="riepilogo.php" style="color: #E9D5FF; font-weight: bold; font-size: 1.2rem; text-decoration: none;">🛒 (<?php echo $totale_carrello; ?>)</a>
        </div>
    </header>

    <main class="content-area">
        <h2 style="color: #8B4513; text-align: center; margin-bottom: 20px;">IL MENU DELLA SETTIMANA</h2>
        <div class="grid-layout">
            <?php foreach ($prodotti as $p): 
                $qta_in_cart = $_SESSION['carrello'][$p['nome']]['quantita'] ?? 0;
            ?>
                <div class="card" style="text-align: center;">
                    <?php if(!empty($p['immagine'])): ?>
                        <img src="../backoffice/<?php echo htmlspecialchars($p['immagine']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-bottom: 1px solid #D4A373;">
                    <?php else: ?>
                        <div style="width: 100%; height: 200px; background: #eee; display:flex; align-items:center; justify-content:center; border-bottom: 1px solid #D4A373; color: #888;">Nessuna immagine</div>
                    <?php endif; ?>

                    <div class="card-header"><?php echo htmlspecialchars($p['nome']); ?></div>
                    <div style="padding: 15px;">
                        <p style="font-size: 1.2rem; font-weight: bold;">€<?php echo number_format($p['prezzo'], 2); ?></p>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 5px;"><strong>Ingredienti:</strong> <?php echo htmlspecialchars($p['ingredienti'] ?? 'Nessuno'); ?></p>
                        
                        <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="dettagli_pane.php?nome=<?php echo urlencode($p['nome']); ?>" class="btn btn-bread" style="padding: 8px 15px;">Dettagli</a>
                            
                            <form method="POST" style="display:flex; justify-content: center; align-items: center; gap: 10px;">
                                <input type="hidden" name="nome_prodotto" value="<?php echo htmlspecialchars($p['nome']); ?>">
                                <input type="hidden" name="prezzo" value="<?php echo htmlspecialchars($p['prezzo']); ?>">
                                
                                <button type="submit" name="rimuovi" class="btn btn-purple" style="padding: 5px 15px; background: #D6604D;" <?php echo $qta_in_cart > 0 ? '' : 'disabled'; ?>>-</button>
                                <span style="font-weight: bold; font-size: 1.1rem; width: 20px;"><?php echo $qta_in_cart; ?></span>
                                <button type="submit" name="aggiungi" class="btn btn-purple" style="padding: 5px 15px;">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <div class="action-bar"><a href="riepilogo.php" class="btn btn-purple">Vai al Riepilogo ➔</a></div>
</div>
</body>
</html>

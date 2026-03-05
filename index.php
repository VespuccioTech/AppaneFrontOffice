<?php
require_once("config.php");

// Inizializza carrello
if (!isset($_SESSION['carrello'])) { $_SESSION['carrello'] = []; }

// Aggiunta al carrello
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi'])) {
    $nome_prod = $_POST['nome_prodotto'];
    if (isset($_SESSION['carrello'][$nome_prod])) {
        $_SESSION['carrello'][$nome_prod]['quantita']++;
    } else {
        $_SESSION['carrello'][$nome_prod] = [
            'prezzo' => $_POST['prezzo'],
            'quantita' => 1
        ];
    }
}

$prodotti = $pdo->query("SELECT * FROM prodotto")->fetchAll();
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
            <a href="login.php" style="color: white; font-weight: bold; margin-right: 15px;">LOGIN / REGISTRATI</a>
            <a href="riepilogo.php" style="color: #E9D5FF; font-weight: bold; font-size: 1.2rem;">🛒 (<?php echo $totale_carrello; ?>)</a>
        </div>
    </header>

    <main class="content-area">
        <h2 style="color: #8B4513; text-align: center; margin-bottom: 20px;">I NOSTRI PRODOTTI</h2>
        <div class="grid-layout">
            <?php foreach ($prodotti as $p): ?>
                <div class="card" style="text-align: center;">
                    <div class="card-header"><?php echo htmlspecialchars($p['nome']); ?></div>
                    <div style="padding: 15px;">
                        <p>€<?php echo number_format($p['prezzo'], 2); ?></p>
                        <div style="margin-top: 15px; display: flex; justify-content: space-around;">
                            <a href="dettagli_pane.php?nome=<?php echo urlencode($p['nome']); ?>" class="btn btn-bread" style="padding: 8px 15px;">Dettagli</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="nome_prodotto" value="<?php echo htmlspecialchars($p['nome']); ?>">
                                <input type="hidden" name="prezzo" value="<?php echo htmlspecialchars($p['prezzo']); ?>">
                                <button type="submit" name="aggiungi" class="btn btn-purple" style="padding: 8px 15px;">+ Aggiungi</button>
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

<?php
session_start();
$carrello = $_SESSION['carrello'] ?? [];
$totale = 0;
foreach ($carrello as $item) { $totale += $item['prezzo'] * $item['quantita']; }
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Riepilogo Ordine</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a><div class="nav-title">RIEPILOGO ORDINE</div></header>
    <nav class="sub-nav"><div><a href="index.php" style="color: #5E3A8C; font-weight: bold; text-decoration: none;">← Continua gli acquisti</a></div></nav>
    <main class="content-area">
        <div class="form-container">
            <?php if (empty($carrello)): ?>
                <h3 style="text-align:center;">Il tuo carrello è vuoto.</h3>
            <?php else: ?>
                <table class="prod-table" style="width: 100%;">
                    <tr><th>Prodotto</th><th>Quantità</th><th>Prezzo Cad.</th><th>Subtotale</th></tr>
                    <?php foreach ($carrello as $nome => $dati): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nome); ?></td>
                            <td><?php echo $dati['quantita']; ?></td>
                            <td>€<?php echo number_format($dati['prezzo'], 2); ?></td>
                            <td>€<?php echo number_format($dati['prezzo'] * $dati['quantita'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <h2 style="text-align: right; margin-top: 20px; color: #8B4513;">Totale: €<?php echo number_format($totale, 2); ?></h2>
                <div style="display: flex; justify-content: flex-end; margin-top: 30px;">
                    <a href="scelta.php" class="btn btn-purple">Procedi con l'ordine ➔</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>

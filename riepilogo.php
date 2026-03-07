<?php
require_once("config.php"); // Include la connessione al DB (e fa già session_start())

$carrello = $_SESSION['carrello'] ?? [];
$totale = 0;

// Prepariamo la query per recuperare l'immagine del prodotto in modo efficiente
$stmt_img = $pdo->prepare("SELECT percorso_file FROM timmagine_prodotto WHERE nome_prodotto = ? LIMIT 1");

foreach ($carrello as $item) { 
    $totale += $item['prezzo'] * $item['quantita'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Riepilogo Ordine</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header">
        <a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a>
        <div class="nav-title">RIEPILOGO ORDINE</div>
    </header>
    <nav class="sub-nav">
        <div><a href="index.php" style="color: #5E3A8C; font-weight: bold; text-decoration: none;">← Continua gli acquisti</a></div>
    </nav>
    <main class="content-area">
        <div class="form-container" style="max-width: 800px;">
            <?php if (empty($carrello)): ?>
                <h3 style="text-align:center;">Il tuo carrello è vuoto.</h3>
            <?php else: ?>
                <table class="prod-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #D4A373;">
                            <th style="padding: 10px; text-align: left;">Prodotto</th>
                            <th style="padding: 10px; text-align: center;">Quantità</th>
                            <th style="padding: 10px; text-align: right;">Prezzo Cad.</th>
                            <th style="padding: 10px; text-align: right;">Subtotale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrello as $nome => $dati): 
                            // Eseguiamo la query per ottenere il percorso dell'immagine
                            $stmt_img->execute([$nome]);
                            $immagine = $stmt_img->fetchColumn();
                        ?>
                            <tr style="border-bottom: 1px solid #EEE;">
                                <td style="padding: 15px 10px; display: flex; align-items: center; gap: 15px;">
                                    <?php if ($immagine): ?>
                                        <img src="../backoffice/<?php echo htmlspecialchars($immagine); ?>" alt="<?php echo htmlspecialchars($nome); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #D4A373;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #888; border: 1px solid #ccc;">N/A</div>
                                    <?php endif; ?>
                                    <span style="font-weight: bold; font-size: 1.1rem; color: #8B4513;"><?php echo htmlspecialchars($nome); ?></span>
                                </td>
                                <td style="padding: 10px; text-align: center; font-size: 1.1rem; font-weight: bold;">
                                    <?php echo $dati['quantita']; ?>
                                </td>
                                <td style="padding: 10px; text-align: right; font-size: 1.1rem;">
                                    €<?php echo number_format($dati['prezzo'], 2); ?>
                                </td>
                                <td style="padding: 10px; text-align: right; font-weight: bold; color: #5E3A8C; font-size: 1.1rem;">
                                    €<?php echo number_format($dati['prezzo'] * $dati['quantita'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h2 style="text-align: right; margin-top: 30px; padding-top: 20px; border-top: 2px solid #D4A373; color: #8B4513;">
                    Totale: €<?php echo number_format($totale, 2); ?>
                </h2>
                
                <div style="display: flex; justify-content: flex-end; margin-top: 30px;">
                    <a href="scelta.php" class="btn btn-purple" style="font-size: 1.1rem;">Procedi con l'ordine ➔</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
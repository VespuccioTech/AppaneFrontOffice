<?php
require_once("config.php");

// Se l'utente non è loggato, lo rimandiamo al login
if (!isset($_SESSION['utente_loggato'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['utente_loggato'];

// Recupero gli ordini dell'utente
$stmt_ordini = $pdo->prepare("SELECT id_ordine, data, importo, stato FROM tordine WHERE username_account = ? ORDER BY data DESC");
$stmt_ordini->execute([$username]);
$ordini = $stmt_ordini->fetchAll();

// Recupero i prodotti associati agli ordini di questo specifico utente
$stmt_prodotti = $pdo->prepare("SELECT s.id_ordine, s.nome_prodotto, s.quantita 
                                FROM tselezione s 
                                JOIN tordine o ON s.id_ordine = o.id_ordine 
                                WHERE o.username_account = ?");
$stmt_prodotti->execute([$username]);
$tutti_prodotti = $stmt_prodotti->fetchAll();

// Raggruppo i prodotti per id_ordine per stamparli facilmente
$prodotti_per_ordine = [];
foreach ($tutti_prodotti as $p) {
    $prodotti_per_ordine[$p['id_ordine']][] = $p;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I Miei Ordini - Appane</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header">
        <a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a>
        <div class="nav-title" style="color:white; font-size:1.2rem; font-weight:bold; margin-left:20px;">I MIEI ORDINI</div>
    </header>
    <nav class="sub-nav" style="background: #FFF8E7; padding: 12px 30px; border-bottom: 3px solid #D4A373;">
        <div><a href="index.php" style="color: #5E3A8C; font-weight: bold; text-decoration: none;">← Torna al menù</a></div>
    </nav>
    <main class="content-area">
        <div class="form-container" style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; border: 2px solid #D4A373;">
            <h2 style="color: #8B4513; text-align: center; margin-bottom: 30px;">Il tuo Storico Ordini</h2>
            
            <?php if (empty($ordini)): ?>
                <h3 style="text-align:center; color: #888; padding: 40px 0;">Non hai ancora effettuato ordini.</h3>
            <?php else: ?>
                <?php foreach ($ordini as $ordine): 
                    $stato_corrente = $ordine['stato'];
                    
                    // Colori dinamici per i badge di stato
                    $colore_stato = '#4A3320'; // Default
                    if($stato_corrente == 'Consegnato') $colore_stato = '#10B981'; // Verde
                    if($stato_corrente == 'In attesa') $colore_stato = '#D6604D'; // Rosso
                    if($stato_corrente == 'In preparazione') $colore_stato = '#F4A261'; // Arancione
                    if($stato_corrente == 'In fase di consegna') $colore_stato = '#2A9D8F'; // Ottanio
                    if($stato_corrente == 'Annullato') $colore_stato = '#888888'; // Grigio
                ?>
                    <div style="border: 1px solid #D4A373; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #FFFAF4; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #D4A373; padding-bottom: 10px; margin-bottom: 15px;">
                            <div>
                                <span style="font-weight: bold; color: #8B4513; font-size: 1.1rem;">Ordine #<?php echo $ordine['id_ordine']; ?></span><br>
                                <span style="font-size: 0.85rem; color: #666;">Effettuato il <?php echo date('d/m/Y \a\l\l\e H:i', strtotime($ordine['data'])); ?></span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 1.2rem; font-weight: bold; color: #5E3A8C; margin-right: 15px;">€<?php echo number_format($ordine['importo'], 2); ?></span>
                                <span style="font-weight: bold; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; background: <?php echo $colore_stato; ?>; color: white; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($stato_corrente); ?>
                                </span>
                            </div>
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse;">
                            <?php foreach ($prodotti_per_ordine[$ordine['id_ordine']] ?? [] as $prod): ?>
                                <tr>
                                    <td style="padding: 5px 0; color: #4A2C2A; font-weight: 500;">🍞 <?php echo htmlspecialchars($prod['nome_prodotto']); ?></td>
                                    <td style="padding: 5px 0; text-align: right; color: #4A2C2A; font-weight: bold;">x<?php echo $prod['quantita']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
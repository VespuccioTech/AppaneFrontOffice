<?php
require_once("config.php");

if (!isset($_GET['nome'])) { header('Location: index.php'); exit; }
$nome_prodotto = $_GET['nome'];

$stmt = $pdo->prepare("SELECT * FROM tprodotto WHERE nome = ?");
$stmt->execute([$nome_prodotto]);
$prodotto = $stmt->fetch();

$stmt_ing = $pdo->prepare("SELECT nome_ingrediente FROM tcomposizione WHERE nome_prodotto = ?");
$stmt_ing->execute([$nome_prodotto]);
$ingredienti = $stmt_ing->fetchAll(PDO::FETCH_COLUMN);

// Estrai l'immagine principale
$stmt_img = $pdo->prepare("SELECT percorso_file FROM timmagine_prodotto WHERE nome_prodotto = ? LIMIT 1");
$stmt_img->execute([$nome_prodotto]);
$immagine = $stmt_img->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Dettagli - <?php echo htmlspecialchars($prodotto['nome']); ?></title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header">
        <a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a>
    </header>
    <nav class="sub-nav"><div><a href="index.php" style="color: #5E3A8C; font-weight: bold; text-decoration: none;">← Torna al menù</a></div></nav>
    <main class="content-area">
        <div class="form-container" style="text-align: center;">
            
            <?php if(!empty($immagine)): ?>
                <img src="../backoffice/<?php echo htmlspecialchars($immagine); ?>" alt="<?php echo htmlspecialchars($prodotto['nome']); ?>" style="max-width: 100%; height: 300px; object-fit: cover; border-radius: 8px; border: 2px solid #D4A373; margin-bottom: 20px;">
            <?php endif; ?>

            <h1 style="color: #8B4513;"><?php echo htmlspecialchars($prodotto['nome']); ?></h1>
            <h2 style="color: #5E3A8C;">€<?php echo number_format($prodotto['prezzo'], 2); ?></h2>
            <p style="margin: 20px 0;"><?php echo htmlspecialchars($prodotto['descrizione'] ?? 'Nessuna descrizione disponibile.'); ?></p>
            
            <div style="background: #FFFAF4; padding: 15px; border-radius: 8px; border: 1px solid #D4A373;">
                <strong>Ingredienti:</strong> <?php echo empty($ingredienti) ? 'Non specificati' : htmlspecialchars(implode(', ', $ingredienti)); ?>
            </div>
            
            <form method="POST" action="index.php" style="margin-top: 30px;">
                <input type="hidden" name="nome_prodotto" value="<?php echo htmlspecialchars($prodotto['nome']); ?>">
                <input type="hidden" name="prezzo" value="<?php echo htmlspecialchars($prodotto['prezzo']); ?>">
                <button type="submit" name="aggiungi" class="btn btn-purple" style="font-size: 1.2rem;">Aggiungi al Carrello 🛒</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

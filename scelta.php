<?php 
session_start();
// Se l'utente è già loggato, salta la scelta e va al checkout utente
if (isset($_SESSION['utente_loggato'])) {
    header("Location: checkout_utente.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Scegli come procedere</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><img src="appane logo.jpg" alt="Logo Appane" style="height: 70px; width: auto;"></a></header>
    <main class="content-area" style="display:flex; justify-content:center; align-items:center;">
        <div class="form-container" style="text-align: center; width: 600px;">
            <h2 style="color: #8B4513; margin-bottom: 30px;">Come vuoi procedere?</h2>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <a href="login.php?checkout=1" class="btn btn-purple" style="font-size: 1.2rem;">Accedi (Ho già un account)</a>
                <a href="registrazione.php?checkout=1" class="btn btn-bread" style="font-size: 1.2rem;">Registrati (Nuovo utente)</a>
                <hr style="border: 1px solid #D4A373; margin: 10px 0;">
                <a href="checkout_ospite.php" class="btn" style="background: #e0e0e0; color: #333; font-size: 1.2rem;">Procedi senza Login (Ospite)</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>

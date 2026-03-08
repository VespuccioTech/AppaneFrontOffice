<?php
require_once("config.php");
$errore = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM taccount WHERE username = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch();
    
    // NOTA: Password lasciate in chiaro come richiesto
    if ($account && $account['password'] === $password) {
        $_SESSION['utente_loggato'] = $username;
        if (isset($_GET['checkout'])) { 
            header("Location: checkout_utente.php"); 
            exit; 
        }
        header("Location: index.php");
        exit;
    } else {
        $errore = "Credenziali non valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Login - Appane</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><img src="appane logo.jpg" alt="Logo Appane" style="height: 70px; width: auto;"></a></header>
    <main class="content-area" style="display:flex; justify-content:center; align-items:center;">
        <div class="form-container" style="width: 400px;">
            <h2 style="color: #8B4513; text-align:center; margin-bottom: 20px;">LOGIN</h2>
            <?php if($errore) echo "<div class='alert alert-error'>$errore</div>"; ?>
            <form method="POST">
                <div class="form-row"><div class="form-col">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div></div>
                <div class="form-row"><div class="form-col">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div></div>
                <button type="submit" class="btn btn-purple" style="width: 100%;">Accedi</button>
            </form>
            <p style="text-align:center; margin-top:15px;"><a href="registrazione.php" style="color:#5E3A8C;">Non hai un account? Registrati</a></p>
        </div>
    </main>
</div>
</body>
</html>

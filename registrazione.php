<?php
require_once("config.php");
$errore = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $telefono = trim($_POST['telefono']);

    try {
        $pdo->beginTransaction();
        $stmt_acc = $pdo->prepare("INSERT INTO taccount (username, password) VALUES (?, ?)");
        $stmt_acc->execute([$username, $password]);

        // Inserimento con i nuovi campi separati
        $stmt_cli = $pdo->prepare("INSERT INTO tcliente (email, n_telefono, nome, cognome) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE n_telefono = VALUES(n_telefono), nome = VALUES(nome), cognome = VALUES(cognome)");
        $stmt_cli->execute([$email, $telefono, $nome, $cognome]);

        $stmt_reg = $pdo->prepare("INSERT INTO tregistrazione (email_cliente, username_account, data) VALUES (?, ?, CURDATE())");
        $stmt_reg->execute([$email, $username]);

        $pdo->commit();
        $_SESSION['utente_loggato'] = $username;

        if (isset($_GET['checkout'])) {
            header("Location: checkout_utente.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } catch (\PDOException $e) {
        $pdo->rollBack();
        $errore = "Errore durante la registrazione. Forse l'username esiste già?";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Registrazione - Appane</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="dashboard-wrapper">
    <header class="main-header"><a href="index.php" class="logo-link"><h1 style="color:white; margin:0;">APPANE</h1></a></header>
    <main class="content-area" style="display:flex; justify-content:center; align-items:center;">
        <div class="form-container" style="width: 600px;">
            <h2 style="color: #8B4513; text-align:center; margin-bottom: 20px;">REGISTRAZIONE</h2>
            <?php if($errore) echo "<div class='alert alert-error'>$errore</div>"; ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-col"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" required></div>
                    <div class="form-col"><label class="form-label">Cognome</label><input type="text" name="cognome" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-col"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                    <div class="form-col"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-col"><label class="form-label">Telefono</label><input type="text" name="telefono" class="form-control"></div>
                    <div class="form-col"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                </div>
                <button type="submit" class="btn btn-purple" style="width: 100%;">Registrati</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

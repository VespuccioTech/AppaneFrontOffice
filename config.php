<?php
session_start();
$host = 'localhost'; $db = 'appane_vespa'; $user = 'root'; $pass = '';
try { $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); } 
catch (\PDOException $e) { die("Errore DB"); }

// --- GESTIONE APERTURA ORDINAZIONI ---
$ordinazioni_aperte = false;
$menu_giorno_inizio = 'Non definito';
$menu_giorno_fine = 'Non definito';

try {
    // Recuperiamo le impostazioni dell'ultimo menù
    $stmt_orari = $pdo->query("SELECT giorno_ripubblicazione, giorno_fine_ordinazioni FROM tmenu_settimanale ORDER BY id_menu DESC LIMIT 1");
    $orari_attivi = $stmt_orari->fetch();
    
    if ($orari_attivi) {
        $menu_giorno_inizio = $orari_attivi['giorno_ripubblicazione'];
        $menu_giorno_fine = $orari_attivi['giorno_fine_ordinazioni'];
        
        // Mappiamo i giorni in numeri per fare i calcoli (1 = Lunedì, 7 = Domenica)
        $mappa_giorni = ['Lunedì'=>1, 'Martedì'=>2, 'Mercoledì'=>3, 'Giovedì'=>4, 'Venerdì'=>5, 'Sabato'=>6, 'Domenica'=>7];
        
        $oggi_num = date('N'); // Giorno attuale in formato numerico (1-7)
        $inizio_num = $mappa_giorni[$menu_giorno_inizio] ?? 3;
        $fine_num = $mappa_giorni[$menu_giorno_fine] ?? 5;
        
        // Calcolo se oggi è compreso tra inizio (incluso) e fine (escluso)
        if ($inizio_num < $fine_num) {
            // Settimana normale (es. da Mercoledì a Venerdì)
            if ($oggi_num >= $inizio_num && $oggi_num < $fine_num) {
                $ordinazioni_aperte = true;
            }
        } else if ($inizio_num > $fine_num) { 
            // Settimana a cavallo del weekend (es. da Sabato a Martedì)
            if ($oggi_num >= $inizio_num || $oggi_num < $fine_num) {
                $ordinazioni_aperte = true;
            }
        } else {
            // Stesso giorno (es. da Giovedì a Giovedì: aperto solo il Giovedì)
            if ($oggi_num == $inizio_num) {
                $ordinazioni_aperte = true;
            }
        }
    }
} catch (\PDOException $e) {}
?>
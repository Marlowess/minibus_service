<?php
    include("myfunctions.php");
    include("sessions.php");
    if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"){
        header("Location: https://" . htmlspecialchars($_SERVER["HTTP_HOST"]) . htmlspecialchars($_SERVER["REQUEST_URI"]));
        exit();
    }
    if(check_session() == 1){
        $reservations = 0;
        $user = $_SESSION['myuser'];
        $user = explodeSessionName($user);
    }
    else {
        header('HTTP/1.1 307 temporary redirect');
        header('Location: index.php');
        exit;
    }

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <link href="styles/index.css" rel=stylesheet type="text/css">
    <meta charset="UTF-8">
    <title>PendularTravelBus - Area Riservata</title>
</head>
<body>
<header class="welcome" >
    <h1>PendularTravelBus - Area personale</h1>
    <?php
    $user = htmlspecialchars($user);
        echo "<h2>Benvenuto $user</h2>";
    ?>
    <hr>
</header>
<noscript>
    <div align=”center” class="noscript">
        <h3>Hai Javascript disattivato. Il sito potrebbe non funzionare correttamente.</h3>
    </div>
</noscript>
<div align="center" class="prenotazioni">
    <h3>Qui trovi il riepilogo delle prenotazioni</h3>
    <h5>Puoi prenotare un solo viaggio alla volta, utilizzando l'apposito
        bottone.<br>Per cambiare le caratteristiche del tuo viaggio prenotato devi prima disdire
        la prenotazione e poi effettuarne una nuova.</h5>
    <?php
    if(isset($_GET['reservok'])){
        echo "<p class='errmsg'><span style='color: green'>Prenotazione avvenuta con successo.</span></p>";
    }
    ?>
    <table>
        <tr>
            <th class="tableheader">Partenza</th>
            <th class="tableheader">Destinazione</th>
            <th class="tableheader">Persone prenotate</th>
            <th class="tableheader">Dettaglio</th>
            <?php
                $reservations = getAuthenticatedData($user);
            ?>
        </tr>
    </table>
    <?php
        // Se c'è una prenotazione, mostro il bottone per eliminarla
        if($reservations){
            echo '<form method="post" action="deletereservation.php">
                    <input type="hidden" name="user" value="' . $user .'">
                    <input type="submit" value="Cancella prenotazione">
                  </form>';
        }
        else{
            echo '<form method="post" action="newreservation.php">
                    <input type="hidden" name="user" value="' . $user .'">
                    <input type="submit" value="Nuova prenotazione">
                  </form>';
        }
    ?>
</div>
<div class="sidenav">
    <a href="index.php">Homepage</a>
    <a href="logout.php">Logout</a>
</div>
<div id="footer" align="right">
    <footer>
        <small>©2018 Created and designed by Stefano Brilli - student ID 249914&nbsp;&nbsp;</small>
    </footer>
</div>
</body>
</html>

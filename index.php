<?php
    include("myfunctions.php");
    include("sessions.php");
    if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"){
        header("Location: https://" . htmlspecialchars($_SERVER["HTTP_HOST"]) . htmlspecialchars($_SERVER["REQUEST_URI"]));
        exit();
    }
    $auth = false;
    if(check_session() == 1){
        $auth = true;
        $user = $_SESSION['myuser'];
        $user = explodeSessionName($user);
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <script type="text/javascript" src="scripts/myfunctions.js"></script>
    <script>
        if(!checkCookie())
            window.location.replace("error.php");
    </script>
    <link href="styles/index.css" rel=stylesheet type="text/css">
    <meta charset="UTF-8">
    <title>PendularTravelBus - Home</title>
</head>
<body>
<header class="welcome" >
    <h1>Benvenuto su PendularTravelBus</h1>
    <h2>Grazie a questo servizio puoi prenotare i tuoi spostamenti con il nostro comodo minibus!</h2>
    <hr>
    <?php
        if($auth)
            echo '<span>Sei autenticato come ' . "<b>$user</b></span>";
        else
            echo "<span>Non sei autenticato. Effettua il login</span>";
    ?>
</header>
<noscript>
    <div align=”center” class="noscript">
        <h3>Hai Javascript disattivato. Il sito potrebbe non funzionare correttamente.</h3>
    </div>
</noscript>
<div align="center" class="prenotazioni">
    <h3>Qui trovi il riepilogo delle prenotazioni già effettuate</h3>
    <h4>Numero di posti disponibili sul minibus:
        <?php
            echo getBusSize();
        ?>
    </h4>
    <br>
    <table>
        <tr>
            <th class="tableheader">Partenza</th>
            <th class="tableheader">Destinazione</th>
            <th class="tableheader">Persone prenotate per questa tratta</th>
            <?php
                if(isset($_GET['msg'])){
                    if($_GET['msg'] == "badconnection")
                        echo "<p class=errmsg style='color: red'>Si è verificato un problema di connessione al database</p>";
                    else
                        getUnauthenticatedData();
                }
                else
                    getUnauthenticatedData();
            ?>
        </tr>
    </table>
</div>
<div class="sidenav">
    <a href="index.php">Homepage</a>
    <?php
    if(!$auth){
        echo "<a href=\"login.php\">Login</a>";
        echo "<a href=\"signup.php\">Registrati</a>";
    }
    else{
        echo "<a href=\"reservedarea.php\">Area personale</a>";
        echo "<a href=\"logout.php\">Logout</a>";
    }
    ?>
</div>
<footer>
    <small>©2018 Created and designed by Stefano Brilli - student ID 249914&nbsp;&nbsp;</small>
</footer>
</body>
</html>
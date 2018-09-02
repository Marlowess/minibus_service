<?php
    include ("myfunctions.php");
    include("sessions.php");

    if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"){
        header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
        exit();
    }

    if(check_session() == 1){
        // Questo utente è già autenticato
        header('HTTP/1.1 307 temporary redirect');
        header('Location: index.php');
        exit;
    }
    ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <script type="text/javascript" src="scripts/myfunctions.js"></script>
    <link href="styles/index.css" rel=stylesheet type="text/css">
    <meta charset="UTF-8">
    <title>PendularTravelBus - Login</title>
</head>
<body>
<header class="welcome">
    <h1>PendularTravelBus - Login</h1>
    <h2>Effettuando il login potrai accedere alla tua area personale e prenotare un viaggio</h2>
    <hr>
</header>
<noscript>
    <div align=”center” class="noscript">
        <h3>Hai Javascript disattivato. Il sito potrebbe non funzionare correttamente.</h3>
    </div>
</noscript>
<div id="loginDiv" align="center">
    <?php
        if(isset($_GET['msg'])){
            if($_GET['msg'] == "wronglogin"){
                echo "<p class='errmsg'><span style='color: red'>Utente e/o password errati</span></p>";
                echo '<hr>';
            }
        }
        else if(isset($_GET['err'])){
            echo "<p class='errmsg'><span style='color: red'>" . 'Si è verificato un errore con l\'autenticazione' . "</span></p>";
            echo '<hr>';
        }
        else if(isset($_GET['authok'])){
            echo "<p class='errmsg'><span style='color: green'>Ti sei registrato correttamente. Effettua il login</span></p>";
            echo '<hr>';
        }
    ?>
    <form method="post" action="authentication.php">
        <label for="fname">Nome utente</label>
        <p><input id="fname" class="loginForm" type="text" name="loginEmail" placeholder="Inserisci il tuo nome utente (email)" oninput="checkData(document.getElementById('fname').value, document.getElementById('fpass').value)"></p>
        <span id="errorMsgEmail" style="color: red" hidden></span>
        <br><br>
        <label for="fpass">Password</label>
        <br>
        <p><input id="fpass" class="loginForm" type="password" name="loginPass" placeholder="Inserisci la tua password" oninput="checkData(document.getElementById('fname').value, document.getElementById('fpass').value)"></p>
        <span id="errorMsg" style="color: red" hidden></span>
        <br><br>
        <input type="hidden" name="action" value="login">
        <p><input id="loginButton" type="submit" value="Invia" disabled></p>
    </form>
</div>
<div class="sidenav">
    <a href="index.php">Homepage</a>
    <a href="signup.php">Registrati</a>
</div>
<div id="footer" align="right">
    <footer>
        <small>©2018 Created and designed by Stefano Brilli - student ID 249914&nbsp;&nbsp;</small>
    </footer>
</div>
</body>
</html>

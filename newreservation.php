<?php
include("myfunctions.php");
include("sessions.php");
if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"){
    header("Location: https://" . htmlspecialchars($_SERVER["HTTP_HOST"]) . htmlspecialchars($_SERVER["REQUEST_URI"]));
    exit();
}
$user = "";
$reservations = 0;
if(check_session() == 1){
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
    <script type=text/javascript src=scripts/myfunctions.js></script>
    <link href="styles/index.css" rel=stylesheet type="text/css">
    <meta charset="UTF-8">
    <title>PendularTravelBus - Area Riservata</title>
</head>
<body>
<header class="welcome" >
    <h1>PendularTravelBus - Nuova prenotazione</h1>
    <?php
    $user = htmlspecialchars($user);
    echo "<h2>Utente: $user</h2>";
    ?>
    <hr>
</header>
<noscript>
    <div align=”center” class="noscript">
        <h3>Hai Javascript disattivato. Il sito potrebbe non funzionare correttamente.</h3>
    </div>
</noscript>
<div align="center" class="prenotazioni">
    <h3>Inserisci gli indirizzi di partenza e arrivo</h3>
    <h5>Seleziona l'indirizzo di partenza e quello di arrivo attraverso i due menù di selezione multipla.<br>
    Indica inoltre per quante persone vuoi prenotare (da 1 fino alla capienza massima del minibus).
    </h5>
    <?php
        if(isset($_GET['err'])){
            echo "<p class='errmsg'><span style='color: red'>La prenotazione non è andata a buon fine. Riprova.</span></p>";
        }
    ?>
    <form id="newResForm" method="post" action="newreservationcheck.php">
        <table>
            <tr>
                <th class="tableheader">Partenza</th>
                <th class="tableheader">Destinazione</th>
                <th class="tableheader">Numero persone</th>
            <tr>
            <tr>
                <td><?php showAddressesSelect()?></td>
                <td><?php showStopAddresses()?></td>
                <td><?php showMaxNumberOfPeople()?></td>
            </tr>
            <tr>
                <input type="hidden" name="user" value="<?php echo $user;?>">
                <td><input id="startMan" type="text" name="manualStartAddr" placeholder="Indirizzo di partenza" oninput="checkReservationStartField(document.getElementById('startMan').value)"></td>
                <td><input id="stopMan" type="text" name="manualStopAddr" placeholder="Indirizzo di arrivo" oninput="checkReservationStopField(document.getElementById('stopMan').value)"></td>
            </tr>
        </table>
        <br>
        <input type="submit" value="Prenota">
    </form>
</div>
<div class="sidenav">
    <a href="index.php">Homepage</a>
    <a href="reservedarea.php">Area personale</a>
</div>
<div id="footer" align="right">
    <footer>
        <small>©2018 Created and designed by Stefano Brilli - student ID 249914&nbsp;&nbsp;</small>
    </footer>
</div>
</body>
</html>

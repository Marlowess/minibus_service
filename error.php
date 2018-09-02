<?php
if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on"){
    header("Location: https://" . htmlspecialchars($_SERVER["HTTP_HOST"]) . htmlspecialchars($_SERVER["REQUEST_URI"]));
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <link href="styles/index.css" rel=stylesheet type="text/css">
    <meta charset="UTF-8">
    <title>PendularTravelBus - Errore</title>
</head>
<body>
<header class="welcome" >
    <h1>PendularTravelBus - Errore</h1>
    <h2>Devi abilitare i cookie per utilizzare questo sito WEB</h2>
    <hr>
</header>
<noscript>
    <div align=”center” class="noscript">
        <h3>Hai Javascript disattivato. Il sito potrebbe non funzionare correttamente.</h3>
    </div>
</noscript>
<div class="sidenav">
    <a href="index.php">Homepage</a>
</div>
</body>
</html>

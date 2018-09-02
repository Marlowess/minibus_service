<?php
include("myfunctions.php");
include("sessions.php");

/*
    Un utente autenticato potrebbe tentare di effettuare una nuova prenotazione dopo la scadenza della sessione.
    Questo è vietato dalle specifiche, quindi deve essere fatto un ulteriore controllo prima di fare una prenotazione
*/
if(check_session() == 0){
    header('HTTP/1.1 307 temporary redirect');
    header('Location: index.php');
    exit;
}

$loggedUser = $_SESSION['myuser'];
$loggedUser = explodeSessionName($loggedUser);

if(isset($_POST['startAddr']))
    $start = $_POST['startAddr'];
else {
    if(isset($_POST['manualStartAddr'])) {
        $start = $_POST['manualStartAddr'];
    }
    else{
        header('Location: newreservation.php?err');
        exit;
    }
}

if(isset($_POST['stopAddr']))
    $stop = $_POST['stopAddr'];
else {
    if(isset($_POST['manualStopAddr']))
        $stop = $_POST['manualStopAddr'];
    else{
        header('Location: newreservation.php?err');
        exit;
    }
}

if(isset($_POST['numpeople'])){
    $numpeople = $_POST['numpeople'];
}
else{
    header('Location: newreservation.php?err');
    exit;
}

if(isset($_POST['user'])){
    $user = $_POST['user'];
}
else{
    header('Location: newreservation.php?err');
    exit;
}


if(($start == "") || ($stop == "") || ($user == "") || ($numpeople == 0) || ($user != $loggedUser)){
    header('Location: newreservation.php?err');
    exit;
}

    $start = strip_tags($start);
    $stop = strip_tags($stop);
    $user = strip_tags($user);
    $numpeople = strip_tags($numpeople);

    /* Prima di iniziare le operazioni di prenotazione verifico se i due indirizzi sono stati passatti in ordine
       alfabetico, altrimenti ritorno subito un messaggio d'errore all'utente, che dovrà procedere con una nuova
       prenotazione.
       I due indirizzi devono essere progressivi, e non devono essere uguali */
    if((strnatcmp ( $start , $stop ) >= 0) || !is_numeric($numpeople)){
        header('Location: newreservation.php?err');
    }
    else{
        newReservation($user, $start, $stop, $numpeople);
    }
    exit;
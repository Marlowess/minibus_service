<?php

 /**
  * Ritorna la capienza massima del minibus. In caso di modifica a tale parametro si deve modificare il valore
  * all'interno di questa funzione
  */
 function getBusSize(){
     return 4;
 }

/** Reindirizza l'utente alla pagina di login inserendo il messaggio da errore da visualizzare in una
 *  richiesta GET
 * @param string $msg
 */
function myRedirect($msg = ""){
    //header('HTTP/1.1 307 temporary redirect');
    header("Location: index.php?msg=" . urlencode($msg));
    exit;
}

/** Questa funzione apre la connessione con il database, e reindirizza alla pagina di login in caso di errore
 */
function dbConnect(){
	/* Sostituire le seguenti quattro variabili con i propri dati del DB */
	$host = "";
	$user = "";
	$password = "";
	$db_name = "";
		
    $conn = mysqli_connect($host, $user, $password, $db_name);
    if(mysqli_connect_error()){
        myRedirect("badconnection");
        exit;
    }
    return $conn;
}

/**
 * Questa funzione permette al sistema di autenticare l'utente, estraendo le informazioni dal database
 * e confrontandole con quelle fornite dall'utente. Il sistema è doppiamente protetto contro l'injection ed ogni altro
 * tipo di intrusione. Infatti, la stringa viene pulita una prima volta all'interno della funzione di autenticazione,
 * e una seconda volta prima di essere sottomessa al database.
 * @param $utente
 * @param $password
 */
function login($utente, $password){
    $conn = dbConnect();
    $utente = mysqli_real_escape_string($conn, $utente);
    $password = md5($password);

    $sql = "SELECT password FROM users WHERE username =" . "'" . $utente . "';";
    if(!$risposta = mysqli_query($conn, $sql)){
        myRedirect("badconnection");
    }
    $riga = mysqli_fetch_array($risposta); // qui c'è la password
    if((mysqli_num_rows($risposta) == 0)){
        header("Location: login.php?msg=wronglogin");
        exit;
    }

    /* Rimuovo gli ultimi 10 caratteri dalla password, ossia i caratteri casuali a scopo di sicurezza */
    $passwordDB = substr($riga["password"], 0, -10);

    if($passwordDB != $password){
        header('Location:login.php?msg=wronglogin');
        exit;
    }
    mysqli_close($conn);
    session_start();
    $_SESSION['myuser'] = "s249914_" . $utente; // metto la matricola per evitare interferenze tra sessioni con lo stesso user
    $_SESSION['time'] = time();
    header('Location: index.php');
    exit;
}

/**
 * La chiamata a questa funzione mostra le prenotazioni attualmente effettuate dagli utenti per i viaggi.
 * In particolare, questa variante viene mostrata in homepage a qualunque utente raggiunga la pagina. Viene solo
 * mostrato il numero di prenotazioni per ogni tratta, mentre il dettaglio è visibile solamente nell'area personale
 * di un utente registrato.
 */
function getUnauthenticatedData(){
    $conn = dbConnect();
    $sql = "SELECT departure, destination, SUM(passengers) as passengers \n"
        . "FROM `prenotazioni` \n"
        . "GROUP BY departure, destination";

    if(!$risposta = mysqli_query($conn, $sql)){
        myRedirect("badconnection");
        exit;
    }
    if((mysqli_num_rows($risposta) == 0)){
        echo '<p class="errmsg">Non è presente alcuna prenotazione per i nostri viaggi</p>';
        mysqli_close($conn);
        return 0;
    }
    mysqli_close($conn);
    foreach($risposta as $item){
        $available = getFreeSlots($item['passengers']);
        echo "<tr><td>".$item['departure']."</td><td>".$item['destination']."</td><td>".$item['passengers']."&nbsp;" . $available . "</td></tr>";
    }
    return 1;
}

/** Funzione simile alla precedente, ma mostra maggiori dettagli sulle prenotazioni già effettuate.
 *  Visibile solamente da parte di un utente autenticato e all'interno della sua area personale
 *
 *  Il parametro passato alla funzione rappresenta l'utente. Grazie a questa informazione posso rappresentare in rosso
 *  le tratte che l'utente ha prenotato
 * @param $user
 * @return int
 */
function getAuthenticatedData($user){
    $conn = dbConnect();

    $user = mysqli_real_escape_string($conn, $user);
    $sqlUser = "SELECT departure\n"
        . "FROM `prenotazioni` \n"
        . "WHERE user = '$user'";
    $res = mysqli_query($conn, $sqlUser);

    $indirizzi = array();
    $i = 0;
    foreach ($res as $k){
        $indirizzi[$i] = $k['departure'];
        $i++;
    }

    $sql = "SELECT p.departure, p.destination, tab2.totUsers as tot_utenti, user, pt.pass as totPass, p.passengers as passByUser\n"
        . "FROM prenotazioni p, \n"
        . "	(SELECT p2.departure as dep, p2.destination as dest, COUNT(p2.user) as totUsers\n"
        . "     FROM prenotazioni p2          \n"
        . "     GROUP BY p2.departure, p2.destination) tab2, \n"
        . "         \n"
        . "     (SELECT p3.departure, p3.destination, SUM(passengers) as pass\n"
        . "      FROM prenotazioni p3\n"
        . "      GROUP BY p3.departure, p3.destination) pt                    \n"
        . "WHERE p.departure = tab2.dep AND p.destination = tab2.dest AND\n"
        . "      p.departure = pt.departure AND p.destination = pt.destination\n"
        . "GROUP BY departure, destination, user\n"
        . "ORDER BY departure";

    $sqlStartStop = "SELECT MIN(departure) as min, MAX(destination) as max FROM prenotazioni WHERE user = '$user' GROUP BY departure, destination";


    /* Adesso devo estrarre la partenza e la destinazione di questo utente, così che possano essere mostrati in rosso */
    if(!$risposta = mysqli_query($conn, $sqlStartStop)){
        myRedirect("badconnection");
    }

    $i = 0;
    $count = mysqli_num_rows($risposta);
    $first = "";
    $last = "";
    foreach ($risposta as $row){
        if($i == 0)
            $first = $row['min'];
        if($i == $count - 1)
            $last = $row['max'];
        $i++;
    }

    if(!$risposta = mysqli_query($conn, $sql)){
        myRedirect("badconnection");
    }
    if((mysqli_num_rows($risposta) == 0)){
        echo '<p class="errmsg">Non è presente alcuna prenotazione per i nostri viaggi</p>';
        mysqli_close($conn);
        return 0;
    }


    mysqli_close($conn);
    $j = 0;
    foreach($risposta as $item){
        if($j > 0){
            echo $item['user']." (" . $item['passByUser'] . ")";
            if($j == 1){
                echo "</td></tr>";
            }
            else
                echo "<br>";
            $j--;
        }
        else{
            if($first == $item['departure'])
                echo "<tr><td class='prenotato'>$first</td>";
            else
                echo "<tr><td>".$item['departure']."</td>";

            if($last == $item['destination'])
                echo "<td class='prenotato'>$last</td>";
            else
                echo "<td>".$item['destination']."</td>";

            echo "<td>".$item['totPass']."</td><td>";

            $j = $item['tot_utenti'];
            if(html_entity_decode($item['user']) == '@'){$j = 0; continue;}
            else{
                echo $item['user']." (" . $item['passByUser'] . ")";
                if($j == 1){
                    echo "</td></tr>";
                }
                else
                    echo "<br>";
                $j--;
            }
        }
    }

    /* In base al valore ritornato potrò mostrare i messaggi duali:
       1) Nuova prenotazione se l'array è vuoto
       2) Cancella prenotazione se c'è almeno un elemento nell'array
     */
    if(sizeof($indirizzi) > 0)
        return 1;
    else
        return 0;
}

/**
 *  Funzione per la registrazione di un nuovo utente. Username e password arrivano a questa funzione già
 *  liberati da problematiche di injection. Prima di essere sottoposti al database, i dati vengono
 *  ulteriormente puliti, per evitare attacchi di SQL injection.
 *  Questa funzione non deve fare ulteriori controlli sulla correttezza di username o password dal
 *  punto di vista delle specifiche, perchè viene invocata solo se le suddette sono corrette
 * @param $username
 * @param $password
 */
function signup($username, $password){
    $a = ""; // stringa casuale per il salvataggio della password in maniera sicura
    for ($i = 0; $i < 10; $i++){
        $a .= mt_rand(0,10);
    }
    $conn = dbConnect();
    $username = mysqli_real_escape_string($conn, $username);

    $password = md5($password) . $a;
    mysqli_autocommit($conn,true);
    $sql = "INSERT INTO users VALUES ('".
        $username . "', '" . $password . "')";

    if (!mysqli_query($conn, $sql)) {
        mysqli_close($conn);
        header('Location: signup.php?err');
        exit;
    }
    mysqli_close($conn);
    header('Location: login.php?authok');
    exit;

}

/**
 *  Grazie a questa funzione il server può accorgersi immediatamente se il formato di username e/o password
 *  sia corretto o meno. I seguenti controlli sono effettuati
 *  1) l'username deve avere un formato di indirizzo email valido
 *  2) la password deve contenere un carattere minuscolo, e almeno uno tra carattere maiuscolo e numero
 *
 *  Questo controllo viene fatto anche lato client tramite uno script js, ma dato che tali script possono
 *  facilmente essere modificati dall'utente, eludendo di fatto qualunque controllo, un ulteriore verifica deve
 *  essere effettuata lato server, dove solo l'amministratore di sistema è in grado di manipolare gli script
 * @param $username
 * @param $password
 * @return bool
 */
function checkSpelling($username, $password){
    $rePass = '/^(?:((?=.*\d)|(?=.*[A-Z]))(?=.*[a-z]).*)$/m';
    $reUser = '/\w+@\w+\.\w+/m';
    preg_match_all($rePass, $password, $matchesPass, PREG_SET_ORDER, 0);
    preg_match_all($reUser, $username, $matchesUser, PREG_SET_ORDER, 0);

    if((sizeof($matchesUser) != 0) && (sizeof($matchesPass) != 0))
        return true;
    else
        return false;
}

/** *
 *  Questa funzione accetta il numero di persone prenotate e restituisce quanti posti liberi ci sono, in base
 *  al valore della variabile
 * @param $prenotazioni
 * @return string
 */
function getFreeSlots($prenotazioni){
    $availableSolts = getBusSize() - $prenotazioni;
    return "($availableSolts liberi)";
}

/**
 *  Questa funzione permette di creare una nuova prenotazione da parte di un utente.
 *  Devono essere controllate i seguenti vincoli:
 *  1) l'utente non deve avere altre prenotazioni attive
 *  2) devono esserci posti liberi per la tratta che si vuole prenotare (protetta da lock)
 *  3) dopo l'avvenuta prenotazione deve essere modificata la tabella che indica i passeggeri per ogni tratta
 * @param $user
 * @param $startNE
 * @param $stopNE
 * @param $numpeople
 */
function newReservation($user, $startNE, $stopNE, $numpeople){
    $conn = dbConnect();
    $user = mysqli_real_escape_string($conn, $user);
    $start = mysqli_real_escape_string($conn, $startNE);
    $stop = mysqli_real_escape_string($conn, $stopNE);
    $numpeople = mysqli_real_escape_string($conn, $numpeople);

    $sqlLock = "SELECT * FROM prenotazioni FOR UPDATE";
    $sql1 = "SELECT COUNT(*) as totRes FROM prenotazioni WHERE user = '$user'";
    $sqlRead = "SELECT departure, destination FROM prenotazioni GROUP BY departure, destination";

    try {
        mysqli_autocommit($conn,false);
        
        // Metto un lock sull'intera tabella
		if(!mysqli_query($conn,$sqlLock)){
		    mysqli_close($conn);
		    header('Location: newreservation.php?err');
		    exit;
		}

        // PARTE 1: questo utente ha già altre prenotazioni attive?
        if(!$risposta = mysqli_query($conn,$sql1))
            throw new Exception("comando1 fallito");
        $item = "";
        foreach($risposta as $r)
            $item = $r['totRes'];
        if($item > 0)
            throw new Exception("Prenotazione già effettuata");


        /* PARTE 2: l'utente è abilitato a fare questa transazione */
        // Estraggo tutte le tuple dalla tabella
        if(!$risposta = mysqli_query($conn,$sqlRead))
            throw new Exception("Fallita lettura");

        /* Salvo i dati dentro ad un array, per lavorare con indici numerici */
        $tuple = array();
        $j = -1;
        foreach ($risposta as $k){
            $j++;
            $tuple[$j]['departure'] = $k['departure'];
            $tuple[$j]['destination'] = $k['destination'];
        }

        
        // La prima cosa da fare è controllare se, per caso, i due indirizzi su cui si vuole fare la prenotazione
        // si trovino entrambi all'esterno dell'insieme degli indirizzi già presenti.
        // Questa sezione di programma sarà acceduta anche se l'insieme degli indirizzi è vuoto
        if(sizeof($tuple) == 0){
            $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$stop', '$user', $numpeople)";
            if(!$risposta = mysqli_query($conn,$sqlIns))
                throw new Exception("Fallita scrittura");
        }
        else{
            // La partenza non è presente, mentre l'arrivo combacia con la prima partenza. Inserisco semplicemente la tupla
            if($stopNE == $tuple[0]['departure']){
                $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$stop', '$user', $numpeople)";
                if(!$risposta = mysqli_query($conn,$sqlIns))
                    throw new Exception("Fallita scrittura");
            }

            // La partenza da inserire combacia con l'ultimo arrivo. Inserisco semplicemente la tupla
            else if($startNE == $tuple[$j]['destination']){
                $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$stop', '$user', $numpeople)";
                if(!$risposta = mysqli_query($conn,$sqlIns))
                    throw new Exception("Fallita scrittura");
            }

            // Esterni e in fondo, servono due inserimenti
            else if($startNE > $tuple[$j]['destination']){
                // Inserisco la coppia (last -> myStart, con 0 passeggeri e user @)
                $newStart = mysqli_real_escape_string($conn,$tuple[$j]['destination']);
                $sqlIns = "INSERT INTO prenotazioni VALUES ('$newStart', '$start', '@', 0)";
                if(!$risposta = mysqli_query($conn,$sqlIns))
                    throw new Exception("Fallita scrittura");

                // Adesso inserisco semplicemente la tupla
                $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$stop', '$user', $numpeople)";
                if(!$risposta = mysqli_query($conn,$sqlIns))
                    throw new Exception("Fallita scrittura");
            }

            // Esterni e all'inizio, servono due inserimenti
            else if($stopNE < $tuple[0]['departure']){
                $newStop = mysqli_real_escape_string($conn,$tuple[0]['departure']);
                $sqlIns = "INSERT INTO prenotazioni VALUES ('$stop', '$newStop', '@', 0)";
                if(!$risposta = mysqli_query($conn,$sqlIns))
                    throw new Exception("Fallita scrittura");

                // Adesso inserisco semplicemente la tupla
                $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$stop', '$user', $numpeople)";
                if(!$risposta = mysqli_query($conn,$sqlIns))
                    throw new Exception("Fallita scrittura");
            }

            /* Coppia esterna che ingloba tutti gli indirizzi, servono due inserimenti */
            else if(($startNE <= $tuple[0]['departure']) && ($stopNE >= $tuple[$j]['destination'])){
                // Inserisco i due collegamenti esterni
                if($startNE < $tuple[0]['departure']){
                    $firstStop = mysqli_real_escape_string($conn,$tuple[0]['departure']);
                    $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$firstStop', '$user', $numpeople)";
                    if(!$risposta = mysqli_query($conn,$sqlIns))
                        throw new Exception("Fallita scrittura");
                }
                if($stopNE > $tuple[$j]['destination']){
                    $lastStart = mysqli_real_escape_string($conn,$tuple[$j]['destination']);
                    $sqlIns = "INSERT INTO prenotazioni VALUES ('$lastStart', '$stop', '$user', $numpeople)";
                    if(!$risposta = mysqli_query($conn,$sqlIns))
                        throw new Exception("Fallita scrittura");
                }

                // Adesso ciclo lungo tutte le vecchie tuple, aggiungendo la prenotazione nuova
                for($i = 0; $i <= $j; $i++){
                    $newStart = mysqli_real_escape_string($conn,$tuple[$i]['departure']);
                    $newStop = mysqli_real_escape_string($conn,$tuple[$i]['destination']);
                    $sqlIns = "INSERT INTO prenotazioni VALUES ('$newStart', '$newStop', '$user', $numpeople)";
                    if(!$risposta = mysqli_query($conn,$sqlIns))
                        throw new Exception("Fallita scrittura");
                }
            }

            else{
                /* Tutti gli altri casi vanno gestiti con un apposito algoritmo di inserimento */
                // $start = partenza che devo inserire
                // $stop = arrivo che devo inserire
                // $oldStart = vecchia partenza, dovrò cancellare le tuple associate ad essa
                // $oldStop = vecchio arrivo, dovrò cancellare le tuple associate ad esso
                $changeStop = "";

                //1) Inserimento della nuova partenza. Itero lungo la tabella fino a quando non trovo una coppia in cui
                // la nuova partenza è minore o uguale dell'arrivo

                if($startNE < $tuple[0]['departure']){
                    $newStart = mysqli_real_escape_string($conn,$tuple[0]['departure']);
                    $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$newStart', '$user', $numpeople)";
                    if(!mysqli_query($conn,$sqlIns))
                        throw new Exception("Fallito controllo finale");
                }
                else{
                    for($i = 0; $i < sizeof($tuple); $i++){
                        if(($startNE >= $tuple[$i]['departure']) && ($startNE < $tuple[$i]['destination'])){
                            if($startNE == $tuple[$i]['departure']){
                                // inserisco semplicemente la tupla
                                $oldStop = mysqli_real_escape_string($conn,$tuple[$i]['destination']);
                                $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$oldStop', '$user', $numpeople)";
                                if(!mysqli_query($conn,$sqlIns))
                                    throw new Exception("Fallito controllo finale");
                                break;
                            }
                            else{
                                // La partenza è maggiore
                                $oldStart = mysqli_real_escape_string($conn,$tuple[$i]['departure']);
                                $oldStop = mysqli_real_escape_string($conn,$tuple[$i]['destination']);
                                $changeStop = $tuple[$i]['destination'];


                                // Inserisco una tupla per ogni nuovo sottotratta
                                $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$oldStop', '$user', $numpeople)";
                                if(!mysqli_query($conn,$sqlIns))
                                    throw new Exception("Fallito controllo finale");

                                // Adesso devo modificare le prenotazioni degli altri utenti sulla vecchia tratta
                                insertOldData($conn, $oldStart, $oldStop, $oldStart, $startNE, $user);
                                insertOldData($conn, $oldStart, $oldStop, $startNE, $oldStop, $user);
                                deleteOldData($conn, $oldStart, $oldStop);
                                break;
                            }
                        }
                    }
                }

                if(!$risposta = mysqli_query($conn,$sqlRead))
                    throw new Exception("Fallita lettura");

                /* Salvo i dati dentro ad un array, per lavorare con indici numerici */
                $tuple = array();
                $j = -1;
                foreach ($risposta as $k){
                    $j++;
                    $tuple[$j]['departure'] = $k['departure'];
                    $tuple[$j]['destination'] = $k['destination'];
                }
                $stopInserted = false;

                for($i = 0; $i < sizeof($tuple); $i++){
                    if(($stopNE >= $tuple[$i]['departure']) && ($stopNE <= $tuple[$i]['destination'])){
                        $stopInserted = true; // ho inserito la partenza
                        if($stopNE == $tuple[$i]['departure']){
                            // inserisco semplicemente la tupla
                            $oldStart = mysqli_real_escape_string($conn,$tuple[$i]['departure']);
                            $sqlIns = "INSERT INTO prenotazioni VALUES ('$stop', '$oldStart', '$user', $numpeople)";
                            if(!$risposta = mysqli_query($conn,$sqlIns))
                                throw new Exception("Fallito controllo finale");
                            break;
                        }
                        else{
                            // La partenza è maggiore
                            $oldStart = $tuple[$i]['departure'];
                            $oldStop = $tuple[$i]['destination'];

                            if(($oldStop == $stopNE)){
                                if(($oldStart == $startNE)){
                                    break;
                                }
                                else{
                                    $oldStart = mysqli_real_escape_string($conn,$tuple[$i]['departure']);
                                    $sqlIns = "INSERT INTO prenotazioni VALUES ('$oldStart', '$stop', '$user', $numpeople)";
                                    if(!$risposta = mysqli_query($conn,$sqlIns))
                                        throw new Exception("Fallito controllo finale2");
                                    break;
                                }
                            }

                            // Inserisco una tupla per ogni nuova sottotratta
                            $oldStart = mysqli_real_escape_string($conn,$tuple[$i]['departure']);
                            $sqlIns = "INSERT INTO prenotazioni VALUES ('$oldStart', '$stop', '$user', $numpeople)";
                            if(!$risposta = mysqli_query($conn,$sqlIns))
                                throw new Exception("Fallito controllo finale2");

                            // Adesso devo modificare le prenotazioni degli altri utenti sulla vecchia tratta

                            insertOldData($conn, $oldStart, $oldStop, $oldStart, $stopNE, $user);
                            insertOldData($conn, $oldStart, $oldStop, $stopNE, $oldStop, $user);
                            deleteOldData($conn, $oldStart, $oldStop);
                            break;

                        }
                    }

                    /* Se la nuova partenza e la nuova destinazione non sono consecutivi, inserisco una tupla
                       per ogni tratta che aggiungo, fino a quando non aggiungerò l'ultima
                     */
                    if(($tuple[$i]['departure']) > $startNE){
                        $oldStart = mysqli_real_escape_string($conn,$tuple[$i]['departure']);
                        $oldStop = mysqli_real_escape_string($conn,$tuple[$i]['destination']);
                        $sqlIns = "INSERT INTO prenotazioni VALUES ('$oldStart', '$oldStop', '$user', $numpeople)";
                        if(!mysqli_query($conn,$sqlIns))
                            throw new Exception("Fallito inserimento lungo tratte $oldStart $oldStop");
                    }

                }

                /* Se sono arrivato qui e questo flag non è settato significa che la destinazione della nuova prenotazione
                   si trova all'esterno dell'intervallo. Inserisco semplicemente la tupla in fondo e termino
                 */
                if(!$stopInserted){
                    // Devo inserire una tupla in coda a tutte le vecchie tratte
                    $lastStop = mysqli_real_escape_string($conn,$tuple[sizeof($tuple) - 1]['destination']);
                    if($stopNE > $changeStop){
                        $sqlIns = "INSERT INTO prenotazioni VALUES ('$lastStop', '$stop', '$user', $numpeople)";
                        if(!mysqli_query($conn,$sqlIns))
                            throw new Exception("Fallito inserimento finale");
                    }
                }
            }
        }

        // Cancello le tuple nulle associate alle coppie (start,stop) che contengono anche prenotazioni valide
        findAndDeleteEmptyRoute($conn);

        // Adesso verifico che non siano state effettuate prenotazioni oltre i limiti, ossia prenotando persone oltre
        // i posti disponibili
        $numRes = getBusSize();
        $sqlCheck = "SELECT SUM(passengers) as checkNum FROM prenotazioni GROUP BY departure, destination HAVING SUM(passengers) > $numRes";

        if(!$risposta = mysqli_query($conn,$sqlCheck))
            throw new Exception("Fallito controllo finale");

        // Se ci sono tratte con più persone della capienza massima lancio un'eccezione e non permetto la prenotazione
        if(mysqli_num_rows ( $risposta) > 0){
            throw new Exception("Fallito controllo finale2");
        }

        /* Se tutti i precedenti controlli sono andati a buon fine faccio commit delle operazioni */
        if (!mysqli_commit($conn)) {
            throw new Exception("Commit fallita");
        }

        /* Arrivato qui le modifiche sono state salvate. Chiudo la connessione e reindirizzo l'utente sulla sua pagina personale, dove
            potrà vedere la sua nuova prenotazione
         */
        dbCloseConnection($conn);
        header('Location: reservedarea.php?reservok');
        exit;
    } catch (Exception $e) {
            mysqli_rollback($conn);
            dbCloseConnection($conn);
            header('Location: newreservation.php?err');
            exit;
    }
}

/**
 * Questa funzione permette di eliminare la prenotazione effettuata da un utente in precedenza.
 *  Devono essere svolte le seguenti operazioni:
 *  1) prendere il lock sulla tabella delle prenotazioni.
 *  2) fare DELETE su tutte le righe che riguardano l'utente
 *  3) Rilasciare il lock
 * @param $user
 */
function deleteReservation($user){
    $conn = dbConnect();
    $user = mysqli_real_escape_string($conn, $user);
    $sqlLock = "SELECT * FROM prenotazioni FOR UPDATE";
    $sqlRead = "SELECT * FROM prenotazioni";
    $sqlToDelete = "SELECT DISTINCT(departure), destination\n"
    . "FROM prenotazioni\n"
    . "WHERE (departure, destination) IN (SELECT departure, destination\n"
    . "                                   FROM prenotazioni\n"
    . "                                   WHERE user = '@'\n"
    . "                                   GROUP BY departure, destination)\n"
    . "      AND (departure, destination) IN (SELECT departure, destination\n"
    . "                                       FROM prenotazioni\n"
    . "                                       GROUP BY departure, destination\n"
    . "                                       HAVING COUNT(*) > 1)";

    try{
        mysqli_autocommit($conn,false);
        
        // Metto un lock sull'intera tabella
		if(!mysqli_query($conn,$sqlLock)){
		    mysqli_close($conn);
		    header('Location: newreservation.php?err');
		    exit;
		}

        $sqlUpdate = "UPDATE prenotazioni SET user = '@', passengers = 0 WHERE user = '$user'";
        if(!mysqli_query($conn,$sqlUpdate))
            throw new Exception("Fallita modifica");

        // 2) Cerco le coppie (start,stop) associate alla tupla nulla ma che contengono anche prenotazioni
        // Questi record devono essere cancellati
        if(!$risposta = mysqli_query($conn,$sqlToDelete))
            throw new Exception("Fallita lettura12");

        /* Salvo i dati dentro ad un array */
        $tuple = array();
        $j = 0;
        foreach ($risposta as $k){
            $tuple[$j]['departure'] = mysqli_real_escape_string($conn,$k['departure']);
            $tuple[$j]['destination'] = mysqli_real_escape_string($conn,$k['destination']);
            $j++;
        }

        /* Adesso devo cancellare tutte le tuple con indirizzo di partenza uguale a quello nell'array */
        for($i = 0; $i < sizeof($tuple); $i++){
            $dep = $tuple[$i]['departure'];
            $dest = $tuple[$i]['destination'];
            $sqlDelRoute = "DELETE FROM prenotazioni WHERE departure = '$dep' AND destination = '$dest' AND user = '@'";
            if(!mysqli_query($conn, $sqlDelRoute)){
                throw new Exception("Fallita cancellazione tratte vuote");
            }
        }

        if(!$risposta = mysqli_query($conn,$sqlRead))
            throw new Exception("Fallita lettura12");
        $j = 0;
        foreach ($risposta as $k){
            $tuple[$j]['departure'] = mysqli_real_escape_string($conn,$k['departure']);
            $tuple[$j]['destination'] = mysqli_real_escape_string($conn,$k['destination']);
            $tuple[$j]['passengers'] = mysqli_real_escape_string($conn,$k['passengers']);
            $j++;
        }

        $first = true;
        $last = true;
        $dataToErase = array();
        $x = 0;

        for($i = 0, $k = (sizeof($tuple) - 1); $i < $j, $k >= 0; $i++, $k--){
            // Controllo se il primo record è da cancellare
            if(($tuple[$i]['passengers'] == 0) && $first){
                $dataToErase[$x] = $tuple[$i]['departure'];
                $x++;
            }
            else
                $first = false;

            if(($tuple[$k]['passengers'] == 0) && $last){
                $dataToErase[$x] = $tuple[$k]['departure'];
                $x++;
            }
            else
                $last = false;

            if(!$first && !$last){
                break;
            }

        }

        for($i = 0; $i < sizeof($dataToErase); $i++){
            $dep = $dataToErase[$i];
            $sqlDelRoute = "DELETE FROM prenotazioni WHERE departure = '$dep' AND user = '@'";
            if(!mysqli_query($conn, $sqlDelRoute)){
                throw new Exception("Fallita cancellazione tratte vuote");
            }
        }

        // commit
        if(!mysqli_commit($conn)){
            throw new Exception("Fallito commit");
        }

        dbCloseConnection($conn);
        header('Location: reservedarea.php');
        exit;
    }
    catch(Exception $e){
        mysqli_rollback($conn);
        dbCloseConnection($conn);
        header('Location: reservedarea.php?reserv=err' . $e->getMessage());
    }
}

/**
 *  Funzione che mostra un menu a tendina, nella pagina di nuova prenotazione, con gli indirizzi di partenza
 *  che l'utente può selezionare, in quanto esistono già
 */
function showAddressesSelect(){
    $conn = dbConnect();
    $sql = "SELECT DISTINCT departure, destination FROM prenotazioni";

    if (!$risposta = mysqli_query($conn, $sql)) {
        mysqli_close($conn);
        header('Location: newreservation.php?err');
        exit;
    }
    mysqli_close($conn);
    $set = array();
    $i = 0;
    echo '<select id="startSelect" name="startAddr">';
    foreach ($risposta as $item){
        $str = $item['departure'];
        if(!in_array($str, $set)){
            echo "<option value='$str'>$str</option>";
            $set[$i++] = $str;
        }
        $str = $item['destination'];
        if(!in_array($str, $set)){
            echo "<option value='$str'>$str</option>";
            $set[$i++] = $str;
        }
    }
    echo '</select>';
}

/**
 *  Funzione che mostra un menu a tendina, nella pagina di nuova prenotazione, con gli indirizzi di destinazione
 *  che l'utente può selezionare, in quanto esistono già
 */
function showStopAddresses(){
    $conn = dbConnect();
    $sql = "SELECT DISTINCT departure, destination FROM prenotazioni";

    if (!$risposta = mysqli_query($conn, $sql)) {
        mysqli_close($conn);
        header('Location: newreservation.php?err');
        exit;
    }
    mysqli_close($conn);
    $set = array();
    $i = 0;
    echo '<select id="stopSelect" name="stopAddr">';
    foreach ($risposta as $item){
        $str = $item['departure'];
        if(!in_array($str, $set)){
            echo "<option value='$str'>$str</option>";
            $set[$i++] = $str;
        }
        $str = $item['destination'];
        if(!in_array($str, $set)){
            echo "<option value='$str'>$str</option>";
            $set[$i++] = $str;
        }
    }
    echo '</select>';
}

/**
 *  Funzione che mostra un menu a tendina, nella pagina di nuova prenotazione, contenente i vari numeri
 *  di persone prenotabili per il viaggio. Lato client non si tiene conto delle persone già prenotate,
 */
function showMaxNumberOfPeople(){
    echo '<select name="numpeople">';
    for($i = 0; $i < getBusSize(); $i++){
        $j = $i + 1;
        echo "<option value='$j'>$j</option>";
    }
    echo '</select>';
}

/** *
 * @param $conn
 * @param $oldStart
 * @param $oldStop
 * @param $start
 * @param $stop
 * @param $newUser
 * @throws Exception
 */
function insertOldData($conn, $oldStart, $oldStop, $start, $stop, $newUser) {
    $oldStart = mysqli_real_escape_string($conn, $oldStart);
    $oldStop = mysqli_real_escape_string($conn, $oldStop);
    $start = mysqli_real_escape_string($conn, $start);
    $stop = mysqli_real_escape_string($conn, $stop);


    $sqlGet = "SELECT * FROM prenotazioni WHERE departure = '$oldStart' AND destination = '$oldStop'";
    if(!$risposta = mysqli_query($conn, $sqlGet)){
        throw new Exception("Fallita collassamento tuple2");
    }
    foreach($risposta as $item){
        // Due inserimenti per ciascun record
        $user = $item['user'];
        $user = mysqli_real_escape_string($conn, $user);
        if($user == $newUser) continue;
        $numpeople = $item['passengers'];
        $sqlIns = "INSERT INTO prenotazioni VALUES ('$start', '$stop', '$user', $numpeople)";
        if(!mysqli_query($conn,$sqlIns))
            throw new Exception("Fallito inserimento dati vecchi $start $stop $user $numpeople");
    }
}

/** *
 * @param $conn
 * @param $oldStart
 * @param $oldStop
 * @throws Exception
 * Quando una nuova prenotazione spezza una vecchia tratta diretta, essa deve essere spezzata in due e le prenotazioni
 * vecchie riassegnate. Questa funzione elimina tutte le prenotazioni che riguardano la vecchia tratta, non più esistente
 * nel database
 */
function deleteOldData($conn, $oldStart, $oldStop){
    $oldStart = mysqli_real_escape_string($conn, $oldStart);
    $oldStop = mysqli_real_escape_string($conn, $oldStop);
    $sqlDel = "DELETE FROM prenotazioni WHERE departure = '$oldStart' AND destination = '$oldStop'";
    if(!mysqli_query($conn,$sqlDel))
        throw new Exception("Fallito cancellazione equal");
}

/**
 * @param $conn
 * @throws Exception
 * Questa funzione permette di eliminare le tuple nulle nel caso in cui una nuova prenotazione l'abbia sovrascritta.
 */
function findAndDeleteEmptyRoute($conn){
    $sqlToDelete = "SELECT DISTINCT(departure), destination\n"
        . "FROM prenotazioni\n"
        . "WHERE (departure, destination) IN (SELECT departure, destination\n"
        . "                                   FROM prenotazioni\n"
        . "                                   WHERE user = '@'\n"
        . "                                   GROUP BY departure, destination)\n"
        . "      AND (departure, destination) IN (SELECT departure, destination\n"
        . "                                       FROM prenotazioni\n"
        . "                                       GROUP BY departure, destination\n"
        . "                                       HAVING COUNT(*) > 1)";

    if(!$risposta = mysqli_query($conn,$sqlToDelete))
        throw new Exception("Fallita lettura12");

    /* Salvo i dati dentro ad un array */
    $tuple = array();
    $j = 0;
    foreach ($risposta as $k){
        $tuple[$j]['departure'] = mysqli_real_escape_string($conn,$k['departure']);
        $tuple[$j]['destination'] = mysqli_real_escape_string($conn,$k['destination']);
        $j++;
    }

    /* Adesso devo cancellare tutte le tuple con indirizzo di partenza uguale a quello nell'array */
    for($i = 0; $i < sizeof($tuple); $i++){
        $dep = $tuple[$i]['departure'];
        $dest = $tuple[$i]['destination'];
        $sqlDelRoute = "DELETE FROM prenotazioni WHERE departure = '$dep' AND destination = '$dest' AND user = '@'";
        if(!mysqli_query($conn, $sqlDelRoute)){
            throw new Exception("Fallita cancellazione tratte vuote");
        }
    }
    return;
}

/**
 * @param $conn
 * Questa funzione permette di chiudere la connessione con il database
 */
function dbCloseConnection($conn){
    mysqli_close($conn);
}

/**
 * @param $user
 * @return mixed
 * Per rendere univoca la sessione del mio programma, il campo nomeUtente anteponendo la mia matricola.
 * Con questa funzione posso stampare a schermo il solo nome dell'utente, senza la matricola
 */
function explodeSessionName($user){
    $pieces = explode("_", $user);
    return $pieces[1];
}

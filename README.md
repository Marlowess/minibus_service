# PD1Website
Questa repo contiene il progetto del sito web valido per il primo appello (AA 2017/2018) del corso di Programmazione Distribuita 1 al Politecnico di Torino.


Requisiti:
Si realizzi una versione semplificata di un sito web che gestisce delle prenotazioni per un servizio di trasporto
passeggeri tramite minibus condiviso da più utenti. Per semplicità si supponga che esista un solo minibus, di capienza
fissa (ma modificabile nel codice all'occorrenza tramite una define o una variabile). Per semplicità non si consideri il
tempo (ore o giorni). Per semplicità il minibus parte dal primo indirizzo in ordine alfabetico e visita tutti gli indirizzi, in
ordine alfabetico, fino all'ultimo.
1. Nella pagina iniziale del sito è possibile, senza alcuna registrazione, visionare in ordine alfabetico il percorso
completo del minibus (dall'indirizzo di partenza, passando per gli eventuali indirizzi intermedi, fino all'indirizzo di
destinazione), mostrando per ogni tratta il solo numero di passeggeri prenotati. Se non ci sono prenotazioni si mostri un
apposito messaggio.
2. Ogni utente può registrarsi liberamente sul sito fornendo solamente uno username, che deve essere un'email valida,
ed una password che deve contenere almeno un carattere alfabetico minuscolo, ed un altro carattere che sia alfabetico
maiuscolo oppure un carattere numerico. In caso contrario l'utente deve essere avvertito prima dell'invio della password
al server, e comunque la registrazione deve essere impedita.
3. Un utente autenticato vede, nella sua pagina personale, il percorso completo del minibus (dall'indirizzo di partenza,
passando per gli eventuali indirizzi intermedi, fino all'indirizzo di destinazione), mostrando per ogni tratta il dettaglio
completo di quanti passeggeri totali sono presenti sul minibus, gli utenti che hanno prenotato per quella tratta e per
quante persone.
4. Un utente autenticato può effettuare una sola richiesta di prenotazione per un certo numero di persone che devono
viaggiare insieme (da minimo 1 a massimo la capienza del minibus), un certo indirizzo di partenza ed un certo indirizzo
di destinazione. L'indirizzo di partenza e di destinazione possono essere scelti sia cliccando su quelli già presenti nel
sistema, che devono essere mostrati all'utente per essere selezionati, sia inserendoli tramite una stringa se sono nuovi
indirizzi. L'operazione di prenotazione ed eventuale aggiunta di indirizzi nuovi deve avvenire in un'unica sottomissione
di informazioni al server, ossia la creazione di eventuali nuovi indirizzi deve essere contestuale al tentativo di
prenotazione. Come risposta, il sistema indicherà se la prenotazione ha avuto successo oppure no. In caso di successo, i
nuovi indirizzi saranno creati e il sistema deve mostrare il percorso completo del minibus come già descritto per la
pagina personale, evidenziando però in colore rosso l'indirizzo di partenza e di arrivo dell'utente autenticato. In caso di
insuccesso si deve mostrare un messaggio che avverta l'utente dell'insuccesso, e i nuovi indirizzi non devono essere
creati. L'utente autenticato può cancellare la propria prenotazione tramite un apposito bottone. Non sono possibili
cancellazioni parziali o modifiche.
5. Esempio:
Inizialmente ci sono 4 indirizzi tra cui gli utenti vogliono viaggiare: AA, BB, DD, EE.
Si ipotizzi che il minibus abbia capienza 4 persone. Percorso attuale:
AA → BB: totale 2: utente u1@p.it (2 passeggeri)
BB → DD: totale 3: utente u1@p.it (2 passeggeri), utente u2@p.it (1 passeggero)
DD → EE: totale 2: utente u3@p.it (1 passeggero), utente u2@p.it (1 passeggero)
L'utente u4 richiede di viaggiare tra AL e BZ (due nuovi indirizzi immessi dall’utente) con 2 passeggeri: richiesta
negata perché nel tratto BB → BZ il minibus non avrebbe capienza sufficiente. Si ricorda che gli indirizzi devono
essere visitati in ordine alfabetico, e a partire dall’indirizzo BB ci sono già 3 passeggeri sul minibus che non ha capienza
per altri 2.
L'utente u4 richiede di viaggiare tra AL e DD con 1 passeggero: prenotato. Nuova situazione:
AA → AL: totale 2: utente u1@p.it (2 passeggeri)
AL → BB: totale 3: utente u1@p.it (2 passeggeri), utente u4@p.it (1passeggero)
BB → DD: totale 4: utente u1@p.it (2 passeggeri), utente u4@p.it (1passeggero), utente u2@p.it (1 passeggero)
DD → EE: totale 2: utente u3@p.it (1 passeggero), utente u2@p.it (1 passeggero)
L'utente u1 cancella la sua prenotazione. Nuova situazione (si noti che il minibus non parte più da AA):
AL → BB: totale 1: utente u4@p.it (1 passeggero)
BB → DD: totale 2: utente u4@p.it (1passeggero), utente u2@p.it (1 passeggero)
DD → EE: totale 2: utente u3@p.it (1 passeggero), utente u2@p.it (1 passeggero)
L'utente u1 richiede di viaggiare tra FF e KK con 4 passeggeri: prenotato. Nuova situazione:
AL → BB: totale 1: utente u4@p.it (1 passeggero)
BB → DD: totale 2: utente u4@p.it (1passeggero), utente u2@p.it (1 passeggero)
DD → EE: totale 2: utente u3@p.it (1 passeggero), utente u2@p.it (1 passeggero)
EE → FF: totale 0: vuoto
FF → KK: totale 4: utente u1@p.it (4 passeggeri)
6. Nel progetto consegnato devono essere già presenti quattro utenti u1@p.it, u2@p.it, u3@p.it, 4@p.it, con password
p1, p2, p3, p4, che si trovano nella situazione al termine dell'esempio precedente.
7. L’autenticazione o registrazione attraverso username e password rimane valida finché l’utente non ha periodi di
inattività superiori a 2 minuti. Se un utente tenta di eseguire un'operazione che richiede registrazione o autenticazione
dopo che l'inattività è stata superiore a 2 minuti l'operazione non ha effetto e l’utente è costretto a ri-autenticarsi con
username e password. Deve essere imposto l'utilizzo del protocollo HTTPS per la registrazione e l'autenticazione ed in
ogni parte del sito che mostra informazioni relative ad un utente registrato o autenticato.
8. L’aspetto generale delle pagine web dovrà contenere: una intestazione nella parte superiore, una barra di navigazione
sul lato sinistro con i link o bottoni per poter effettuare le varie operazioni possibili, ed una parte centrale in cui avviene
l’operazione principale.
9. I cookies e il Javascript debbono essere abilitati, altrimenti il sito può non funzionare correttamente (in caso
contrario, per i cookies avvertire l’utente e impedire il funzionamento del sito, per il Javascript avvertire l'utente). I
form debbono prevedere brevi messaggi di spiegazione sul significato dei vari campi, che possono essere presenti
all'interno dei campi stessi o comparire nel momento in cui il puntatore si trova sopra il campo stesso.
10. Bisogna fare in modo che la visualizzazione, anche semplice, sia quanto più possibile uniforme al variare del
browser utilizzato.

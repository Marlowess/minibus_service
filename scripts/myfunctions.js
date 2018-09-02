/**
 * Questa funzione permette di controllare la sintassi della password, per verificare che sia concorde con quanto dichiarato
 * nelle specifiche.
 * @param password
 * @returns {boolean}
 */
function checkPassword(password){
    const regex = /^(?:((?=.*\d)|(?=.*[A-Z]))(?=.*[a-z]).*)$/;
    return regex.test(password);
}

/**
 * Funzione per il controllo della correttezza dello username, sulla falsariga della funzione precedente.
 * @param email
 * @returns {boolean}
 */
function checkemail(email){
    const regex = /\w+@\w+\.\w+$/gm;
    return regex.test(email);
}

/**
 * Dopo ogni digitazione da parte dell'utente nei campi di form avviene la verifica della correttezza di username e password.
 * Se uno dei due campi è mal formattato il bottone di submitting viene inibito.
 * @param email
 * @param password
 */
function checkData(email, password){
    var flag1 = checkemail(email);
    var flag2 = checkPassword(password);

    // Email e password ben formattati
    if(flag1 && flag2){
        document.getElementById('loginButton').disabled = false;
        document.getElementById('errorMsgEmail').hidden = true;
        document.getElementById('errorMsg').hidden = true;
    }

    // Email mal formattata
    else if(!flag1 && flag2){
        if(email != null && email !== ""){
            document.getElementById('errorMsgEmail').hidden = false;
            document.getElementById('errorMsgEmail').innerText = "Inserisci un indirizzo email valido";
        }
        document.getElementById('errorMsg').hidden = true;
        document.getElementById('loginButton').disabled = true;
    }

    // Password mal formattata
    else if(!flag2 && flag1){
        if(password != null && password !== ""){
            document.getElementById('errorMsg').hidden = false;
            document.getElementById('errorMsg').innerText = "Il formato della password è errato";
        }
        document.getElementById('errorMsgEmail').hidden = true;
        document.getElementById('loginButton').disabled = true;
    }

    // tutto mal formattato
    else{
        if(email != null && email !== ""){
            document.getElementById('errorMsgEmail').hidden = false;
            document.getElementById('errorMsgEmail').innerText = "Inserisci un indirizzo email valido";
        }

        if(password != null && password !== ""){
            document.getElementById('errorMsg').hidden = false;
            document.getElementById('errorMsg').innerText = "Il formato della password è errato";
        }

        document.getElementById('loginButton').disabled = true;

    }
}

/**
 * Questa funzione abilita e disabilita la selezione multipla della tratta.
 */
function checkReservationStartField(text){
    document.getElementById('startSelect').disabled = text !== "";

}

function checkReservationStopField(text){
    document.getElementById('stopSelect').disabled = text !== "";

}


function checkCookie(){
    var cookieEnabled = navigator.cookieEnabled;
    if (!cookieEnabled){
        return 0;
    }
    else
        return 1;
}
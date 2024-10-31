// funzione che invia i dati sulle mire nel menu a tendina in alto
function clickButton() {
    // Raccolgo i valori del form
    var mira = Array.from(document.getElementById('mira').selectedOptions).map(option => option.value);
    var tipo = document.getElementById('tipo').value;
    var percorso = document.getElementById('percorso').value;


    // Crea un oggetto FormData per inviare i dati del form
    var formData = new FormData();
    formData.append('mira', JSON.stringify(mira)); // Invia la lista di mire come JSON
    formData.append('tipo', tipo);
    formData.append('percorso', percorso);

    // creo la richiesta di tipo POST
    var url = "eventi/nuova_lettura3.php";
    var http = new XMLHttpRequest();
    http.open("POST", url, true);

    // Imposta la funzione di callback
    http.onreadystatechange = function() {
        if (http.readyState === XMLHttpRequest.DONE) {
            if (http.status === 200) {
                // Gestisci la risposta dal server
                console.log(http.responseText); // Puoi aggiornare l'interfaccia utente se necessario
            } else {
                console.error("Errore nella richiesta: " + http.status);
            }
        }
    };

    http.send(formData);

    // resetto i campi del form
    $('#percorso').val('NO');
    $('#mira').val('');
    $('#tipo').val('');

    // refresh della tabella
    $('#t_mire').bootstrapTable('refresh', { silent: true });

    // prevengo il submit predefinito del form
    return false;
}

// funzione che aggiorna le mire massivamente tramite il pulsante a fondo pagine
function clickButton2() {

    // Ottieni le righe selezionate
    const selectedRows = $('#t_mire').bootstrapTable('getSelections');
    if (selectedRows.length === 0) {
        alert('Nessuna riga selezionata!');
        return;
    }

    let value = $('#tipo2').val()

    // Raccogli gli ID delle righe selezionate
    const ids = selectedRows.map(row => row.id);

    // Invia i dati al server
    $.ajax({
        url: "eventi/nuova_lettura2.php",
        type: 'POST',
        data: { ids: ids, value: value },
        success: function(response) {
            // Gestisci la risposta dal server
            alert('Aggiornamento completato: ' + response);
            location.reload(); // Ricarica la pagina per vedere le modifiche
        },
        error: function(xhr, status, error) {
            alert('Si è verificato un errore: ' + error);
        }
    });
}


function getMira(val, perc) {
    $.ajax({
        type: "POST",
        url: "get_mira.php",
        data: { 'cod': val, 'f': perc },
        success: function (data) {
            $("#mira").html(data);
        }
    });
    return false;
}


// Funzione per generare i pulsanti
function createButton(iconClass, title, target, value) {
    return `<button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="${target}${value}">
                <i class="${iconClass}" title="${title}"></i>
            </button>`;
}

function nameFormatterInsert(value, row) {
    // Variabili per il bottone di lettura
    const commonButtonTitle = `Aggiungi lettura per ${row.nome}`;
    let buttons = '';

    // Controlla il tipo di idrometro e genera i pulsanti di conseguenza
    if (row.tipo !== 'IDROMETRO COMUNE' && row.tipo !== 'IDROMETRO ARPA') {
        buttons += createButton('fas fa-search-plus', commonButtonTitle, '#new_lettura', value);
        buttons += ` - <a class="btn btn-info" target="_blank" href="mira.php?id=${value}">
                        <i class="fas fa-chart-line" title="Visualizza ed edita dati storici"></i>
                    </a>`;
    } else if (row.tipo === 'IDROMETRO ARPA') {
        buttons += createButton('fas fa-chart-line', `Visualizza grafico idro lettura per ${row.nome}`, '#grafico_i_a', value);
    } else if (row.tipo === 'IDROMETRO COMUNE') {
        buttons += createButton('fas fa-chart-line', `Visualizza grafico idro lettura per ${row.nome}`, '#grafico_i_c', value);
    }

    return buttons;
}


function nameFormatterLettura(value, row) {
    if (row.tipo == 'IDROMETRO ARPA') {
        if (value < row.arancio) {
            return '<font style="color:#00bb2d;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else if (value > row.arancio && value < row.rosso) {
            return '<font style="color:#FFC020;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else if (value > row.rosso) {
            return '<font style="color:#cb3234;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else {
            return '-';
        }
    } else if (row.tipo == 'IDROMETRO COMUNE') {
        if (value < row.arancio) {
            return '<font style="color:#00bb2d;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else if (value > row.arancio && value < row.rosso) {
            return '<font style="color:#FFC020;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else if (value > row.rosso) {
            return '<font style="color:#cb3234;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else {
            return '-';
        }
    } else {
        if (value == 1) {
            return '<i class="fas fa-circle" title="Livello basso" style="color:#00bb2d;"></i>';
        } else if (value == 2) {
            return '<i class="fas fa-circle" title="Livello medio" style="color:#ffff00;"></i>';
        } else if (value == 3) {
            return '<i class="fas fa-circle" title="Livello alto" style="color:#cb3234;"></i>';
        } else {
            return '-';
        }
    }
}

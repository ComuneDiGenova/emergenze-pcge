// funzione che invia i dati sulle mire nel menu a tendina in alto
function clickButton() {
    // Raccolgo i valori del form
    var mira = Array.from(document.getElementById('mira').selectedOptions).map(option => option.value);
    var tipo = document.getElementById('tipo').value;
    var percorso = document.getElementById('percorso').value;


   // Esegui la chiamata AJAX
   $.ajax({
        url: "eventi/nuova_lettura3.php",
        type: 'POST',
        data: {
            mira: JSON.stringify(mira),  // Converti la lista di mire in JSON
            tipo: tipo,
            percorso: percorso
        },
        success: function(response) {
            // Gestisci la risposta dal server
            alert("Lettura inserita con successo!");
            console.log(response); // Se necessario, mostra la risposta in console

            // Refresh della tabella dopo l'inserimento
            $('#t_mire').bootstrapTable('refresh', { silent: true });
        },
        error: function(xhr, status, error) {
            // Gestione dell'errore
            console.error("Errore nella richiesta: " + xhr.status + " " + error);
            alert("Errore durante l'inserimento delle letture. Per favore, riprova.");
        }
    });

    // Reset dei campi del form
    $('#percorso').val('NO');
    $('#mira').val('');
    $('#tipo').val('');

    // Prevengo il submit predefinito del form
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

    let value = $('#tipo').val()
    if (!value) {
        alert("Seleziona un valore per 'Valore lettura mira'!");
        return;
    }

    // Crea una lista di oggetti per ogni riga selezionata con id e valore
    const data = selectedRows.map(row => ({
        id: row.id,
        value: value
    }));

    // Invia i dati al server
    $.ajax({
        url: "eventi/nuova_lettura2.php",
        type: 'POST',
        data: { data: JSON.stringify(data) },
        success: function(response) {
            // Gestisci la risposta dal server
            // console.log("Dati inviati: ", data);
            alert('Le mire selezionate sono state aggiornate');
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
    // DEBUG: controllo valori nulli o non definiti
    // if (value == null || row.arancio == null || row.rosso == null) {
    //         return '?';  // Ritorna '-' se mancano valori cruciali
    //     }

    // console.log("Nome:", row.nome, "Row tipo:", row.tipo, "Value:", value, "Arancio:", row.arancio, "Rosso:", row.rosso);
    
    // Applico la logica di visualizazione
    if ( row.tipo == 'IDROMETRO ARPA' || row.tipo == 'IDROMETRO COMUNE' ) {
        if (value < row.arancio) {
            return '<font style="color:#00bb2d;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else if (value > row.arancio && value < row.rosso) {
            return '<font style="color:#FFC020;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else if (value > row.rosso) {
            return '<font style="color:#cb3234;">' + Math.round(value * 1000) / 1000 + '</font>';
        } else {
            return '-';
        }
    } else if ( row.tipo == 'MIRA' || row.tipo == 'RIVO' ) {
        if (value == 1) {
            return '<i class="fas fa-circle" title="Livello basso" style="color:#00bb2d;" data-export-value="verde"></i>';
        } else if (value == 2) {
            return '<i class="fas fa-circle" title="Livello medio" style="color:#ffff00;" data-export-value="giallo"></i>';
        } else if (value == 3) {
            return '<i class="fas fa-circle" title="Livello alto" style="color:#cb3234;" data-export-value="rosso"></i>';
        } else {
            return '-';
        }
    }
}

// Export Excel
$(function() {
  $('#t_mire').bootstrapTable('refreshOptions', {
    formatLoadingMessage: function () {
      return 'Caricamento dati…';
    },
    formatNoMatches: function () {
      // evita il flash "nessun record" durante la prima chiamata AJAX
      return 'Caricamento dati…';
    },
    exportOptions: {
      onCellHtmlData: function (cell, rowIndex, colIndex, htmlData) {
        // 1) prova a leggere da DOM
        if (cell && cell.querySelector) {
          const el = cell.querySelector('[data-export-value]');
          if (el && el.dataset) return el.dataset.exportValue;
        }
        // 2) se hai solo la stringa HTML, estrai data-export-value="..."
        if (typeof htmlData === 'string') {
          const m = htmlData.match(/data-export-value="([^"]*)"/i);
          if (m && m[1] !== undefined) return m[1];
          // fallback: rimuovi HTML e torna testo puro
          return htmlData.replace(/<[^>]*>/g, '').trim();
        }
        // 3) ultimo fallback
        return '';
      }
    }
  });
});

// Blocca le larghezze delle colonne (evita resize con filtri/refresh)
$(function () {
  var $table = $('#t_mire');
  if ($table.length === 0) return;

  var lockedWidths = null;

  function applyColWidths() {
    if (!lockedWidths || lockedWidths.length === 0) return;

    var $container = $table.closest('.bootstrap-table');
    if ($container.length === 0) return;

    var $headerCols = $container.find('.fixed-table-header table colgroup col');
    var $bodyCols = $container.find('.fixed-table-body table colgroup col');

    // Se non esiste colgroup (dipende da versione/plugin), fallback: imposta width sui TH
    if ($headerCols.length === 0 || $bodyCols.length === 0) {
      $container.find('.fixed-table-header table thead th').each(function (i) {
        if (lockedWidths[i] != null) $(this).css('width', lockedWidths[i] + 'px');
      });
      $container.find('.fixed-table-body table tbody tr:first-child td').each(function (i) {
        if (lockedWidths[i] != null) $(this).css('width', lockedWidths[i] + 'px');
      });
      return;
    }

    $headerCols.each(function (i) {
      if (lockedWidths[i] != null) $(this).css('width', lockedWidths[i] + 'px');
    });
    $bodyCols.each(function (i) {
      if (lockedWidths[i] != null) $(this).css('width', lockedWidths[i] + 'px');
    });

    // Stabilizza il layout: niente ricalcolo automatico col contenuto
    $container.find('.fixed-table-header table, .fixed-table-body table').css('table-layout', 'fixed');
    $container.find('.fixed-table-body td, .fixed-table-header th').css({
      overflow: 'hidden',
      'text-overflow': 'ellipsis',
      'white-space': 'nowrap'
    });
  }

  function lockInitialWidths() {
    if (lockedWidths) return;

    var $container = $table.closest('.bootstrap-table');
    var $ths = $container.find('.fixed-table-header table thead th');
    if ($ths.length === 0) return;

    lockedWidths = [];
    $ths.each(function () {
      // outerWidth include bordi/padding: è quella che “vedi” a schermo
      lockedWidths.push($(this).outerWidth());
    });

    applyColWidths();
  }

  // Al primo load “buono” congela, poi riapplica ad ogni aggiornamento
  $table.on('load-success.bs.table', function () {
    lockInitialWidths();
    // dopo un tick: bootstrap-table può ricalcolare dopo l'evento
    setTimeout(applyColWidths, 0);
  });
  $table.on('post-body.bs.table', function () {
    setTimeout(applyColWidths, 0);
  });

  // Se la tabella era già inizializzata prima di questo script, prova comunque
  setTimeout(function () {
    lockInitialWidths();
    applyColWidths();
  }, 0);
});
// Questo ti permette di vedere a colpo d'occhio se il file viene caricato
console.log('reportistica2.js caricato');

$(function () {
    $(".fixed-table-toolbar").addClass("noprint");
});

function stampaMire() {
    // Flag per chiedere a mire.php di lanciare la stampa tabella
    var url = window.location.origin + '/emergenze/pages/mire.php?autoprint=1';

    var w = window.open(url, '_blank');
    if (!w) {
        console.error('Popup bloccato o finestra non aperta');
    }
}

function nameFormatter0(value) {
    switch (value) {
        case 't':
            return '<i class="fa fa-play" aria-hidden="true" title="In corso"></i>';
        case 'f':
            return '<i class="fa fa-stop" aria-hidden="true" title="Chiuso"></i>';
        default:
            return '<i class="fa fa-hourglass-half" aria-hidden="true" title="In chiusura"></i>';
    }
}

function nameFormatter1(value) {
    return `
        <a href="./reportistica.php?id=${value}" class="btn btn-info" title="Report 8 h (riepilogo segnalazioni in corso di evento)" role="button">
            <i class="fa fa-file-invoice" aria-hidden="true"></i> 8h
        </a>
        <a href="./reportistica_personale.php?id=${value}" class="btn btn-info" title="Report esteso (dettagli squadre e personale impiegato)" role="button">
            <i class="fa fa-file-invoice" aria-hidden="true"></i> Esteso
        </a>
    `;
}
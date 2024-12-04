// Funzione per la formattazione della lettura
function letturaFormatter(value, row) {
    if (!row['data_invio'] && !row['ora_invio']) {
        return '<center><i class="far fa-times-circle" style="color:#9C9C9C; font-size: xx-large;"></i></center>';
    } else {
        const iconClass = value === 't' ? 'check-circle' : 'times-circle';
        const color = value === 't' ? '#14c717' : '#ff0000';
        return `<center><i class="far fa-${iconClass}" style="color:${color}; font-size: xx-large;"></i></center>`;
    }
}

// Funzione per la formattazione della lettura convocazione
function letturaFormatterCOC(value, row) {
    if (row.data_invio_conv == null) {
        return '<center>-</center>';
    }
    const iconClass = value === 't' ? 'check-circle' : 'times-circle';
    const color = value === 't' ? '#14c717' : '#ff0000';
    return `<center><i class="fas fa-${iconClass}" style="color:${color}; font-size: xx-large;"></i></center>`;
}
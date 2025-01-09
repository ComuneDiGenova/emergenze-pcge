function getComuni(provincia) {
    if (provincia) {
        // Fai una richiesta AJAX al server
        fetch('scripts/get_comuni.php?provincia=' + provincia)
            .then(response => response.json())
            .then(data => {
                let comuneList = document.getElementById('comune-list');
                comuneList.innerHTML = '<option value="">Seleziona il comune</option>'; // Resetta il menu
                data.forEach(comune => {
                    comuneList.innerHTML += `<option value="${comune.nome}">${comune.nome}</option>`;
                });
            })
            .catch(error => console.error('Errore:', error));
    } else {
        document.getElementById('comune-list').innerHTML = '<option value="">Seleziona il comune</option>';
    }
}

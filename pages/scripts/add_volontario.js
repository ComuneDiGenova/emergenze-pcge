function getComuni(provincia) {
    if (provincia) {
        fetch('scripts/get_comuni.php?provincia=' + provincia)
            .then(response => response.json())
            .then(data => {
                let comuneList = document.getElementById('comune-list');
                comuneList.innerHTML = '<option value="">Seleziona il comune</option>';
                data.forEach(comune => {
                    comuneList.innerHTML += `<option value="${comune.nome}">${comune.nome}</option>`;
                });
            })
            .catch(error => console.error('Errore:', error));
    } else {
        document.getElementById('comune-list').innerHTML = '<option value="">Seleziona il comune</option>';
    }
}


function getUO2(uo1_id) {
    const uo2Select = document.getElementById('UO_II');
    console.log("Chiamata a getUO2 con id:", uo1_id);

    if (uo1_id) {
        fetch('scripts/get_uo2.php?uo1_id=' + encodeURIComponent(uo1_id))
            .then(response => response.json())
            .then(data => {
                uo2Select.innerHTML = '<option value="">Seleziona...</option>';

                if (data.length === 0) {
                    // Nessun risultato: disabilita il campo
                    uo2Select.disabled = true;
                    console.warn("Nessuna UO II livello trovata per UO I:", uo1_id);
                } else {
                    // Popola le opzioni e abilita il campo
                    data.forEach(item => {
                        uo2Select.innerHTML += `<option value="${item.id}">${item.descrizione}</option>`;
                    });
                    uo2Select.disabled = false;
                }

                // Refresh di bootstrap-select
                if (typeof $ !== 'undefined' && $(uo2Select).hasClass('selectpicker')) {
                    $(uo2Select).selectpicker('refresh');
                }
            })
            .catch(error => {
                console.error('Errore nel caricamento UO II livello:', error);
                uo2Select.innerHTML = '<option value="">Errore nel caricamento</option>';
                uo2Select.disabled = true;

                if (typeof $ !== 'undefined' && $(uo2Select).hasClass('selectpicker')) {
                    $(uo2Select).selectpicker('refresh');
                }
            });
    } else {
        uo2Select.innerHTML = '<option value="">Seleziona...</option>';
        uo2Select.disabled = true;

        if (typeof $ !== 'undefined' && $(uo2Select).hasClass('selectpicker')) {
            $(uo2Select).selectpicker('refresh');
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {
    console.log("add_volontario.js caricato");

    const uo1Select = document.getElementById('UO_I');
    if (uo1Select) {
        // console.log("Listener associato a UO_I");
        uo1Select.addEventListener('change', () => {
            const selectedValue = uo1Select.value;
            // console.log("UO_I selezionato:", selectedValue);
            getUO2(selectedValue);
        });
    } else {
        console.warn("Elemento UO_I non trovato");
    }
});

function getUO(val) {
    $.ajax({
    type: "POST",
    url: "get_uo.php",
    data:'cod='+val,
    success: function(data){
        $("#uo-list").html(data);
    }
    });
}


document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('riassegnazione');
    const reassignForm = document.getElementById('reassignForm');
    const eventSelect = document.getElementById('id_evento');
    const feedback = document.getElementById('feedback');
    const submitButton = document.getElementById('conferma');

    // Mostra il modal e carica le opzioni degli eventi
    $(modal).on('shown.bs.modal', function () {
        eventSelect.innerHTML = '<option value="" disabled selected>Caricamento eventi...</option>';
        feedback.style.display = 'none';

        fetch('./scripts/get_eventi_validi.php')
            .then(response => {
                if (!response.ok) throw new Error('Errore nel caricamento degli eventi');
                return response.json();
            })
            .then(data => {
                eventSelect.innerHTML = '<option value="" disabled selected>Seleziona evento</option>';
                data.forEach(evento => {
                    const option = document.createElement('option');
                    option.value = evento.id;
                    option.textContent = `${evento.id} - ${evento.descrizione}`;
                    eventSelect.appendChild(option);
                });
            })
            .catch(error => {
                feedback.style.display = 'block';
                feedback.textContent = 'Errore nel caricamento degli eventi: ' + error.message;
            });
    });

    // Gestione invio form
    reassignForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitButton.disabled = true;
        feedback.style.display = 'none';

        const formData = new FormData(reassignForm);

        fetch('./segnalazioni/riassegna_segnalazione.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => {
                if (!response.ok) throw new Error("Errore durante l'invio: " + response.statusText);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    $(modal).modal('hide');
                    location.reload(true); // ricarica la pagina per vedere i dati aggiornati
                } else {
                    feedback.style.display = 'block';
                    feedback.textContent = 'Errore: ' + data.message;
                }
            })
            .catch(error => {
                feedback.style.display = 'block';
                feedback.textContent = "Errore durante l'invio: " + error.message;
            })
            .finally(() => {
                submitButton.disabled = false;
            });
    });
});


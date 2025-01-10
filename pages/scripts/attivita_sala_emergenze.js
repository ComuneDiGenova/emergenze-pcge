$(document).ready(function () {
    let maxHeight = 0;

    $('.shift-container').each(function () {
        maxHeight = Math.max(maxHeight, $(this).height());
    });

    $('.shift-container').height(maxHeight);
});


$(document).ready(function() {
    $('.datepicker').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
});


function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    window.print();

    document.body.innerHTML = originalContents;
}


// Controlla che almeno una checkbox sia selezionata
$(document).on('submit', '.shift-form', function(event) {
    // console.log('Submit triggered'); // Debug

    const checkboxes = $(this).find('.event-checkbox');
    let isChecked = false;

    checkboxes.each(function() {
        // console.log('Checkbox status:', $(this).is(':checked')); // Debug
        if ($(this).is(':checked')) {
            isChecked = true;
        }
    });

    if (!isChecked) {
        event.preventDefault();
        alert('Devi selezionare almeno un evento.');

        // Rimuovi e riapplica il listener al form
        const form = $(this);
        form.off('submit');
        setTimeout(function() {
            form.on('submit', function(event) {
                const checkboxes = form.find('.event-checkbox');
                let isChecked = false;

                checkboxes.each(function() {
                    if ($(this).is(':checked')) {
                        isChecked = true;
                    }
                });

                if (!isChecked) {
                    event.preventDefault();
                }
            });
        }, 0); // Riapplica subito dopo aver gestito l'errore
    }
});

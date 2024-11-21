function copybuttonwms() {
    var copyText = document.getElementById("wms");
    copyText.select();
    document.execCommand("copy");
    alert("Link WMS copiato: " + copyText.value);
  }


function copybuttonwfs() {
    var copyText = document.getElementById("wfs");
    copyText.select();
    document.execCommand("copy");
    alert("Link WFS copiato: " + copyText.value);
}


function nameFormatterBKP(value) {
    if (value=='t'){
            return '<i class="fas fa-play" style="color:#5cb85c"></i> in lavorazione';
    } else if (value=='f') {
           return '<i class="fas fa-stop"></i> chiusa';
    } else {
           return '<i class="fas fa-exclamation" style="color:#ff0000"></i> da prendere in carico';;
    }

}

function nameFormatter(value) {
    if (value=='t'){
            return 'in lavorazione';
    } else if (value=='f') {
           return 'chiusa';
    } else {
           return 'da prendere in carico';;
    }

}

function nameFormatterEdit(value) {
    return '<a class="btn btn-warning" target="_blank" href=./dettagli_segnalazione.php?id='+value+'> '+value+' </a>';
}


function manutenzioni(value) {
    if (value){	
        return '<a class="btn btn-info" target="_new" href="' + urlManutenzioni + 'id=' + value + '"> ' + value + ' </a>';
    } else {
        return '-';
    }
}

function nameFormatterRischio(value) {    
    if (value=='t'){
        return '<i class="fas fa-exclamation-triangle" style="color:#ff0000"></i>';
    } else if (value=='f') {
        return '<i class="fas fa-check" style="color:#5cb85c"></i>';
    } else {
        return '<i class="fas fa-question" style="color:#505050"></i>';
    }
}


function nameFormatterMappa1(value, row) {
    return `
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#myMap${value}">
            <i class="fas fa-map-marked-alt"></i>
        </button>
        <div class="modal fade" id="myMap${value}" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Anteprima segnalazione ${value}</h4>
                    </div>
                    <div class="modal-body">
                        <iframe class="embed-responsive-item" style="width:100%; padding-top:0%; height:600px;" src="./mappa_leaflet.php#17/${row.lat}/${row.lon}"></iframe>
                    </div>
                </div>
            </div>
        </div>`;
}


$(function () {
    var $table = $('#segnalazioni');
    $('#toolbar').find('select').change(function () {
        $table.bootstrapTable('destroy').bootstrapTable({
            exportDataType: $(this).val()
        });
    });
});
	
$(document).ready(function() {
    $("form[id=filtro_cr], form[id=filtro_mun]").submit(function() {
        let checkboxId = $(this).attr("id");
        if ($(`input[type=checkbox][id=${checkboxId}]`).filter(':checked').length < 1) {
            alert(`Seleziona almeno un${checkboxId === "filtro_cr" ? "a criticità" : " municipio"}!`);
            return false;
        }
    });
});
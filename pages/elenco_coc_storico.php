<?php 
$subtitle = "Storico Convocazione COC Direttivo";

// Sanitizzazione e validazione degli input
$getfiltri = filter_input(INPUT_GET, 'f', FILTER_SANITIZE_STRING);
$filtro_evento_attivo = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);

$uri = basename($_SERVER['REQUEST_URI']);

require('./req.php');
require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');
require('./check_evento.php');

// Ottimizzazione filtro
if ($profilo_ok == 3) {
    $filter = ' ';
} elseif ($profilo == 8) {
    $filter = " WHERE id_profilo='$profilo' AND nome_munic = '$livello' ";
} else {
    $filter = " WHERE id_profilo='$profilo' ";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberta">

    <title>Gestione emergenze</title>
</head>

<body>

<div id="wrapper">
    <div id="navbar1">
        <?php require('navbar_up.php'); ?>
    </div>

    <?php require('./navbar_left.php'); ?>

    <div id="page-wrapper">
        <br>

        <div class="row">
            <div id="toolbar">
                <select class="form-control">
                    <option value="">Esporta i dati visualizzati</option>
                    <option value="all">Esporta tutto (lento)</option>
                    <option value="selected">Esporta solo selezionati</option>
                </select>
            </div>

            <table id="convocati" class="table-hover" data-toggle="table" 
                   data-url="./tables/griglia_storico_convocazione_coc.php?p=<?php echo $profilo_ok; ?>&l=<?php echo $livello1; ?>" 
                   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf']
                   data-search="true" data-page-size=25 data-click-to-select="true" data-show-print="true" 
                   data-pagination="true" data-sidePagination="true" data-show-refresh="true" 
                   data-show-toggle="true" data-show-columns="true" data-filter-control="true" data-toolbar="#toolbar">

                <thead>
                <tr>
                    <th data-field="funzione" data-sortable="true">Funzione COC</th>
                    <th data-field="cognome" data-sortable="true">Cognome</th>
                    <th data-field="nome" data-sortable="true">Nome</th>
                    <th data-field="data_invio" data-sortable="true" data-filter-control="select">Data invio notifica</th>
                    <th data-field="ora_invio" data-sortable="true" data-filter-control="input">Ora invio notifica</th>
                    <th data-field="lettura" data-sortable="true" data-formatter="letturaFormatter">Conferma lettura</th>
                    <th data-field="data_conferma" data-sortable="true" data-visible="false">Data/ora conferma lettura</th>
                    <th data-field="data_invio_conv" data-sortable="true" data-filter-control="select">Data invio Convocazione</th>
                    <th data-field="ora_convocazione" data-sortable="true" data-filter-control="input">Ora invio Convocazione</th>
                    <th data-field="lettura_conv" data-sortable="true" data-formatter="letturaFormatter2">Conferma Convocazione</th>
                    <th data-field="data_conferma_conv" data-sortable="true" data-visible="false">Data/ora conferma convocazione</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    function letturaFormatter(value, row) {
        if (!row['data_invio'] && !row['ora_invio']) {
            return '<center><i class="far fa-times-circle" style="color:#9C9C9C; font-size: xx-large;"></i></center>';
        } else {
            const iconClass = value === 't' ? 'check-circle' : 'times-circle';
            const color = value === 't' ? '#14c717' : '#ff0000';
            return `<center><i class="far fa-${iconClass}" style="color:${color}; font-size: xx-large;"></i></center>`;
        }
    }

    function letturaFormatter2(value, row) {
        if (row.data_invio_conv == null) {
            return '<center>-</center>';
        }
        const iconClass = value === 't' ? 'check-circle' : 'times-circle';
        const color = value === 't' ? '#14c717' : '#ff0000';
        return `<center><i class="fas fa-${iconClass}" style="color:${color}; font-size: xx-large;"></i></center>`;
    }
</script>

<?php 
require('./footer.php');
require('./req_bottom.php');
?>
</body>
</html>

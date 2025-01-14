<?php
$subtitle = "Form aggiunta utente esterno";
?>


<?php
    session_start();
    if (isset($_SESSION['import_message'])) {
        echo "<div id='import-message' style='background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>
            {$_SESSION['import_message']}
        </div>";
        unset($_SESSION['import_message']); // Rimuovi il messaggio dopo averlo mostrato
    }
?>
<script>
    // Nasconde il messaggio dopo 5 secondi
    setTimeout(() => {
        const msgDiv = document.getElementById('import-message');
        if (msgDiv) msgDiv.style.display = 'none';
    }, 5000);
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto">

    <title>Gestione emergenze</title>

    <?php 
    require('./req.php');
    require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');
    require('./check_evento.php');
    ?>
</head>

<body>
    <div id="wrapper">
        <div id="navbar1">
            <?php require('navbar_up.php'); ?>
        </div>

        <?php require('./navbar_left.php'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">
                        <i class="fa fa-user-plus"></i> Aggiunta all'anagrafe del personale esterno
                        <br>
                        <small>(volontari, personale delle Municipalizzate, etc)</small>
                    </h1>
                </div>
            </div>

            <div class="form-group">
                <a href="templates/utenti_template.xlsx" class="btn btn-success" style="margin-left: 10px;">
                    <i class="fa fa-download"></i> Scarica Template
                </a>
                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#uploadModal">
                    <i class="fa fa-upload"></i> Carica CSV
                </button>
            </div>

            <!-- Modale per il caricamento del file CSV -->
            <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="scripts/upload_csv.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadModalLabel">Carica file CSV</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="csvFile">Seleziona un file CSV:</label>
                                    <input type="file" name="csvFile" class="form-control" accept=".csv" required>
                                    <small class="form-text text-muted">
                                        Assicurati che il file CSV abbia le colonne richieste: <strong>CF, nome, cognome, data di nascita, comune, telefono, email, ecc.</strong>
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                                <button type="submit" class="btn btn-primary">Carica e Importa</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Form aggiunta utente singolo -->
            <form action="add_volontario2.php" method="POST">
                <!-- Credenziali -->
                <h4><i class="fa fa-address-card"></i> Credenziali:</h4>

                <div class="form-group">
                    <label for="nome">Nome</label> <font color="red">*</font>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="cognome">Cognome</label> <font color="red">*</font>
                    <input type="text" name="cognome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="CF">Codice fiscale:</label> <font color="red">*</font>
                    <input type="text" pattern=".{16,16}" maxlength="16" name="CF" class="form-control" required>
                    <small class="form-text text-muted">
                        Il Codice Fiscale è obbligatorio e sarà utilizzato per accedere al sistema tramite le credenziali 
                        <a target="_new" href="https://www.spid.gov.it/">SPID</a>.
                    </small>
                </div>

                <!-- Data di nascita -->
                <div class="form-group">
                    <label for="data_nascita">Data nascita:</label> <font color="red">*</font>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <select class="form-control" name="dd" required>
                                <option value="">Giorno</option>
                                <?php for ($j = 1; $j <= 31; $j++) echo "<option value='" . sprintf('%02d', $j) . "'>" . sprintf('%02d', $j) . "</option>"; ?>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <select class="form-control" name="mm" required>
                                <option value="">Mese</option>
                                <?php 
                                setlocale(LC_TIME, 'it_IT.iso88591');
                                for ($m = 1; $m <= 12; $m++) {
                                    $month_label = strftime('%B', mktime(0, 0, 0, $m, 1));
                                    echo "<option value='" . sprintf('%02d', $m) . "'>" . sprintf('%02d', $m) . " - $month_label</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <select class="form-control" name="yyyy" required>
                                <option value="">Anno</option>
                                <?php 
                                $year = date('Y');
                                for ($i = $year; $i >= $year - 110; $i--) echo "<option value='$i'>$i</option>";
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Nazionalità -->
                <div class="form-group">
                    <label for="naz">Nazionalità:</label> <font color="red">*</font>
                    <select class="form-control" name="naz">
                        <option value="ITALIA">ITALIA</option>
                        <?php 
                        $query2 = "SELECT * FROM \"varie\".\"stati_2018\";";
                        $result2 = pg_query($conn, $query2);
                        while ($r2 = pg_fetch_assoc($result2)) {
                            echo "<option value='{$r2['nome']}'>{$r2['nome']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Residenza/Domicilio -->
                <h4><i class="fa fa-building"></i> Residenza / Domicilio:</h4>

                <div class="form-group">
                    <label for="provincia">Provincia:</label> <font color="red">*</font>
                    <select class="selectpicker show-tick form-control" data-live-search="true" onchange="getComuni(this.value);" required>
                        <option value="">Seleziona la provincia</option>
                        <?php 
                        $query2 = "SELECT * FROM varie.province;";
                        $result2 = pg_query($conn, $query2);
                        while ($r2 = pg_fetch_assoc($result2)) {
                            echo "<option value='{$r2['cod']}'>{$r2['nome']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="comune">Comune:</label> <font color="red">*</font>
                    <select class="form-control" name="comune" id="comune-list" required>
                        <option value="">Seleziona il comune</option>
                    </select>
                </div>

                <script src="scripts/add_volontario.js"></script>

                <div class="form-group">
                    <label for="indirizzo">Indirizzo:</label> <font color="red">*</font>
                    <input type="text" name="indirizzo" class="form-control" required>
                    <small class="form-text text-muted">Specificare via/piazza/località, numero civico ed eventualmente interno.</small>
                </div>

                <div class="form-group">
                    <label for="cap">CAP:</label>
                    <input type="text" name="cap" class="form-control" maxlength="5">
                </div>

                <!-- Contatti -->
                <h4><i class="fa fa-phone"></i> Contatti:</h4>

                <div class="form-group">
                    <label for="telefono1">Telefono principale:</label> <font color="red">*</font>
                    <input type="text" name="telefono1" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="telefono2">Telefono secondario:</label>
                    <input type="text" name="telefono2" class="form-control">
                </div>

                <div class="form-group">
                    <label for="fax">Fax:</label>
                    <input type="text" name="fax" class="form-control">
                </div>

                <div class="form-group">
                    <label for="mail">Mail:</label> <font color="red">*</font>
                    <input type="email" name="mail" class="form-control" required>
                </div>

                <!-- Unita Operative -->
                <div class="form-group">
                    <label for="UO_I">Unità operativa I livello:</label> <font color="red">*</font>
                    <select name="UO_I" class="selectpicker show-tick form-control" data-live-search="true" required>
                        <option value="">Seleziona...</option>
                        <?php 
                        $query2 = "SELECT * FROM \"users\".\"uo_1_livello\";";
                        $result2 = pg_query($conn, $query2);
                        while ($r2 = pg_fetch_assoc($result2)) {
                            echo "<option value='{$r2['id1']}'>{$r2['descrizione']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="num_GG">Numero tessera Gruppo Genova:</label>
                    <input type="text" name="num_GG" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Aggiungi</button>
            </form>
        </div>
    </div>

    <?php 
    require('./footer.php');
    require('./req_bottom.php');
    ?>
</body>
</html>

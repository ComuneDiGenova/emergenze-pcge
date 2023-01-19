<?php

$subtitle = "Form inserimento nuova segnalazione"

  ?>
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

    $page = basename($_SERVER['PHP_SELF']);

    if ($profilo_sistema > 8) {
      header("location: ./divieto_accesso.php");
    }
    ?>

    <link rel="stylesheet" href="../vendor//leaflet-search/src/leaflet-search.css">

  </head>

  <body>

    <div id="wrapper">

      <div id="navbar1">
        <?php
        require('navbar_up.php');
        ?>
      </div>
      <?php
      require('./navbar_left.php')
        ?>


      <div id="page-wrapper">
        <form action="segnalazioni/import_segnalazione.php" method="POST">
          <?php
          if ($contatore_eventi == 0) {
            echo '<hr> <h2> <font color="red"> <i class="fas fa-ban"></i> ';
            echo 'In questo momento non è possibile inserire nuove segnalazioni in quanto non ci sono eventi attivi.';
            echo '</font></h2><hr>';
          }
          if ($profilo_sistema == 8) {
            $uo_ins = $descrizione_profilo . ' - ' . $livello1 . '';
          }
          else {
            $uo_ins = $descrizione_profilo;
          }

          ?>
          <!--Stai inserendo questa segnalazione con il profilo <?php echo $uo_ins; ?>-->
          <input type="hidden" id="uo_ins" name="uo_ins" value="<?php echo $uo_ins; ?>">

          <div class="row">
            <h4><i class="fa fa-address-card"></i> Generalità segnalante:</h4>

            <div class="col-md-6">


              <div class="form-group">
                <label for="nome"> Nome segnalante</label>
                <font color="red">*</font>
                <input type="text" id="nome" name="nome" class="form-control" required>
                <small>Se non specificato scrivere <i>Anonimo</i></small>
              </div>
              <div class="form-group">
                <label for="telefono"> Telefono </label>
                <input type="text" name="telefono" class="form-control">
                <small>Non è un dato obbligatorio, ma è fortemente consigliato avere un recapito telefonico del
                  segnalante.</small>
              </div>

            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="note_segnalante"> Note segnalante </label>
                <textarea class="form-control" rows="3" name="note_segnalante" id="note_segnalante"></textarea>
              </div>

            </div>
          </div>
          <div class="row">
            <div class="form-group col-md-6">
              <label for="tipo_segn">Tipo segnalante:</label>
              <font color="red">*</font>
              <select class="form-control" name="tipo_segn" id="tipo_segn" required="">
                <option name="tipo_segn" value=""> Specifica una tipologia segnalante </option>
                <?php
                $query2 = "SELECT * FROM segnalazioni.tipo_segnalanti WHERE valido='t';";
                echo $query2;
                $result2 = pg_query($conn, $query2);
                //echo $query1;    
                while ($r2 = pg_fetch_assoc($result2)) {
                  ?>
                  <option name="tipo_segn" value="<?php echo $r2['id']; ?>">
                    <?php echo $r2['descrizione']; ?>
                  </option>
                <?php } ?>

              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="altro"> Specifica altro...</label>
              <input type="text" id="altro" name="altro" class="form-control" disabled="">
            </div>

          </div>

          <?php
          if ($profilo_ok == 3) {
            ?>
            <div class="row">
              <div class="form-group col-md-6">
                <label for="nverde"> Operatore numero verde?</label>
                <font color="red">*</font><br>
                <label class="radio-inline"><input type="radio" name="nverde" value="t">Sì</label>
                <label class="radio-inline"><input type="radio" name="nverde" value="f" checked>Non specificato</label>
              </div>
            </div>
          <?php } ?>






          <hr>
          <div class="row">
            <h4><i class="fa fa-play"></i> Evento:</h4>
            <div class="form-group col-md-12">
              <label for="nome"> Evento</label>
              <font color="red">*</font>
              <?php
              $len = count($eventi_attivi);

              if ($len == 1) {
                ?>


                <select readonly="" class="form-control" name="evento" required>

                  <?php
                  for ($i = 0; $i < $len; $i++) {

                    echo '<option name="evento" value="' . $tipo_eventi_attivi[0][0] . '">' . $tipo_eventi_attivi[0][1] . ' (id=' . $tipo_eventi_attivi[0][0] . ')</option>';
                  }
                  ?>
                </select>
                <small id="eventohelp" class="form-text text-muted">Un solo evento attivo (per trasparenza lo mostriamo ma
                  possiamo anche decidere di non farlo).</small>

              <?php }
              else {
                ?>

                <select class="form-control" name="evento" required>
                  <option value=''>Seleziona un evento tra quelli attivi </option>
                  <?php
                  for ($i = 0; $i < $len; $i++) {

                    echo '<option name="evento" value="' . $tipo_eventi_attivi[$i][0] . '">' . $tipo_eventi_attivi[$i][1] . ' (id=' . $tipo_eventi_attivi[$i][0] . ')</option>';
                  }
                  ?>
                </select>

              <?php
              }
              ?>

            </div>

          </div>
          <hr>
          <div class="row">

            <h4><i class="fa fa-tasks"></i> Oggetto della segnalazione:</h4>

            <div class="form-group col-md-6">
              <label for="naz">Tipo criticità:</label>
              <font color="red">*</font>
              <select class="form-control" name="crit" id="crit" required="">
                <option name="crit" value=""> ... </option>
                <?php
                $query2 = "SELECT * FROM segnalazioni.tipo_criticita WHERE valido='t' ORDER BY descrizione;";
                echo $query2;
                $result2 = pg_query($conn, $query2);
                //echo $query1;    
                while ($r2 = pg_fetch_assoc($result2)) {
                  ?>
                  <option name="crit" value="<?php echo $r2['id']; ?>">
                    <?php echo $r2['descrizione']; ?>
                  </option>
                <?php } ?>

              </select>
            </div>







            <div class="form-group col-md-6">
              <label for="descrizione"> Descrizione</label>
              <font color="red">*</font>
              <input type="text" name="descrizione" class="form-control" required="">
            </div>

          </div>
          <div class="row">

            <div class="form-group col-md-6">
              <label for="nome"> Ci sono persone in pericolo?</label>
              <font color="red">*</font><br>
              <label class="radio-inline"><input type="radio" name="rischio" value="" checked>Non specificato</label>
              <label class="radio-inline"><input type="radio" name="rischio" value="t">Sì</label>
              <label class="radio-inline"><input type="radio" name="rischio" value="f">Nessuna persona a rischio</label>
            </div>


            <div class="form-group col-md-6">
              <label for="descrizione"> Ulteriori comunicazioni a carattere riservato</label>
              <font color="red">*</font>
              <textarea class="form-control" rows="3" name="riservate" id="riservate"></textarea>
              <small>Tali informazioni saranno visibili solo agli operatori di protezione civile.</small>
            </div>



          </div>
          <hr>
          <div class="row">
            <h4><i class="fa fa-map-marker-alt"></i> Geolocalizzazione:</h4>


            <div class="form-group">
              <label for="nome"> Seleziona l'opzione che intendi usare per georeferenziare la segnalazione</label>
              <font color="red">*</font><br>
              <label class="radio-inline"><input type="radio" name="georef" id="civico" required="">Tramite
                civico</label>
              <label class="radio-inline"><input type="radio" name="georef" id="mappa">Tramite mappa</label>
              <label class="radio-inline"><input type="radio" name="georef" id="coord">Con coordinate note</label>
            </div>


          </div>
          <div class="row">


            <script>
              function getCivico(val) {
                $.ajax({
                  type: "POST",
                  url: "get_civico.php",
                  data: 'cod=' + val,
                  success: function (data) {
                    $("#civico-list").html(data);
                  }
                });
              }

            </script>



            <div class="col-md-6">
              <div class="form-group  ">
                <label for="via">Via:</label>
                <font color="red">*</font>
                <select disabled="" id="via-list" class="selectpicker show-tick form-control" data-live-search="true"
                  onChange="getCivico(this.value);" required="">
                  <option value="">Seleziona la via</option>
                  <?php
                  $query2 = "SELECT * From \"geodb\".\"m_vie_unite\";";
                  $result2 = pg_query($conn, $query2);
                  //echo $query1;    
                  while ($r2 = pg_fetch_assoc($result2)) {
                    $valore = $r2['codvia'] . ";" . $r2['desvia'];
                    ?>

                    <option name="codvia" value="<?php echo $r2['codvia']; ?>">
                      <?php echo $r2['desvia']; ?>
                    </option>
                  <?php } ?>

                </select>
              </div>


              <div class="form-group">
                <label for="id_civico">Civico:</label>
                <font color="red">*</font>
                <select disabled="" class="form-control" name="id_civico" id="civico-list" class="demoInputBox"
                  required="">
                  <option value="">Seleziona il civico</option>
                </select>
              </div>

              <div class="form-group">
                <label for="civrischio"> Il civico è in pericolo?</label><br>
                <label class="radio-inline"><input type="radio" name="civrischio" value="" checked>Non
                  specificato</label>
                <label class="radio-inline"><input type="radio" name="civrischio" value="t">Sì</label>
                <label class="radio-inline"><input type="radio" name="civrischio" value="f">No</label>
              </div>


            </div> <!-- Chiudo col-md-6-->
            <div class="col-md-6">




              <div class="form-group">
                <label for="lat"> Latitudine </label>
                <font color="red">*</font>
                <input disabled="" type="text" name="lat" id="lat" class="form-control" required="">
              </div>

              <div class="form-group">
                <label for="lon"> Longitudine </label>
                <font color="red">*</font>
                <input disabled="" type="text" name="lon" id="lon" class="form-control" required="">
              </div>




              <div class="form-group">
                <label for="oggrischio"> C'è uno specifico oggetto in pericolo?</label> <br>
                <label class="radio-inline"><input type="radio" name="oggrischio" id=oggrischiot value="t">Sì</label>
                <label class="radio-inline"><input type="radio" name="oggrischio" id=oggrischiof value="f">No</label>
              </div>

              <div class="form-group">
                <label for="tipo_oggetto">Oggetto:</label>
                <select class="form-control" name="tipo_oggetto" id="tipo_oggetto" required="">
                  <option name="tipo_oggetto" value=""> Specifica oggetto </option>
                  <?php
                  $query2 = "SELECT * FROM segnalazioni.tipo_oggetti_rischio WHERE valido='t' and elenco_elementi_segnalazione='t' ORDER BY descrizione;";
                  echo $query2;
                  $result2 = pg_query($conn, $query2);
                  //echo $query1;    
                  while ($r2 = pg_fetch_assoc($result2)) {
                    ?>
                    <option name="tipo_oggetto" value="<?php echo $r2['id']; ?>">
                      <?php echo $r2['descrizione']; ?>
                    </option>
                  <?php } ?>

                </select>
              </div>

            </div> <!-- Chiudo col-md-6-->

          </div>
          <div class="row">

            <div class="panel-group">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapse1">Mappa</a>
                  </h4>
                </div>
                <div id="collapse1" class="panel-collapse collapse">
                  <div id="mapid" style="width: 100%; height: 600px;"></div>
                </div>
              </div>
            </div>



            <!--script type="text/javascript" >
$('#collapse1').on('shown.bs.collapse', function (e) {
    mymap.invalidateSize(true);
});
</script-->




          </div>
          <div class="row">

            <hr>
            <h4><i class="fa fa-plus"></i> Altro:</h4>


            <div class="form-group">
              <label for="note_geo"> Altre note utili alla localizzazione </label>
              <input type="text" name="note_geo" id="note_geo" class="form-control">
              <small id="addrHelp" class="form-text text-muted"> Qua è possibile specificare altre annotazioni, </small>
            </div>





            <button type="submit" class="btn btn-primary">Invia segnalazione</button>
          </div>
          <!-- /.row -->


        </form>






        <br><br>
        <div class="row">

        </div>
        <!-- /.row -->
      </div>
      <!-- /#wrapper -->

      <?php

      require('./footer.php');

      require('./req_bottom.php');

      require('./mappa_georef.php');

      ?>



      <script type="text/javascript">

        // con questa parte scritta in JQuery si evita che 
        // l'uso del tasto enter abbia effetto sul submit del form

        $(document).on("keydown", ":input:not(textarea)", function (event) {
          if (event.key == "Enter") {
            event.preventDefault();
          }
        });

      </script>





  </body>

</html>
<!-- create a HTML page -->
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="description" content="">
        <meta name="author" content="szymon">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard - Python API GUI</title>
        <?php
        $_DEBUG = false;
        define("ROOT_DIR", realpath(__DIR__ . "/.."));
        try {
            $config_path = __DIR__ . "/config.php";
            $path_to_boostrap = explode("/", __DIR__);
            require __DIR__ . "/../req.php";
            # Getting the conn.php file from the parent directory
            $conn_php_path =
                explode("emergenze-pcge", getcwd())[0] .
                "emergenze-pcge/conn.php";
        } catch (Exception $e) {
            echo "Caught exception: ", $e->getMessage(), "\n";
        }

// This is an example of how to use SQL queries:
// $my_query = "SELECT * FROM users.utenti WHERE username = '$_SESSION[username]';";
// $result = pg_query($conn, $my_query);
// $r = pg_fetch_assoc($result);
// echo $r['username'];
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-autocomplete@2.3.7/dist/latest/bootstrap-autocomplete.min.js" integrity="sha256-yYoz9OwGJUuV2927SrBHgg/bdqOF6oA0v781vA0/0FU=" crossorigin="anonymous"></script>
<?php
// This is workaround for SPID account check
// require "../check_event_fake.php";
// This is for the navbar component
require "../navbar_up.php";
?>

        <style>
        body {
            margin-top: 100px;
        }

        .select,
        #locale {
            width: 100%;
        }

        .create_campaign {
            margin-right: 10px;
        }

        .form-group.autoComplete:hover > .bootstrap-autocomplete.dropdown-menu, .form-group.autoComplete:focus > .bootstrap-autocomplete.dropdown-menu {
            display: inline;
        }
        </style>
        <link rel="stylesheet" href="register.css">

    </head>

    <body>

      <div class="container">
        <div class="page-header">
          <h1>Registrazione <small>servizio di allerta meteo di Protezione Civile</small></h1>
        </div>
        <blockquote>
          <p class="text-info">
            La registrazione al servizio di allerta meteo di Protezione Civile è consentita per i <strong>soli</strong> civici
            che risultano ad alta pericolosità idraulica.
          </p>
        </blockquote>
      </div>
      <section class="signup-step-container">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-md-12">
                    <div class="wizard">
                        <div class="wizard-inner">
                            <div class="connecting-line"></div>
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab" aria-expanded="true">
                                      <span class="round-tab">1 </span> <i>Indirizzo</i></a>
                                </li>
                                <li role="presentation" class="disabled">
                                    <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab" aria-expanded="false">
                                      <span class="round-tab">2</span> <i>Dati generali</i></a>
                                </li>
                                <li role="presentation" class="disabled">
                                    <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab">
                                      <span class="round-tab">3</span> <i>Pericolosità</i></a>
                                </li>
                                <li role="presentation" class="disabled">
                                    <a href="#step4" data-toggle="tab" aria-controls="step4" role="tab">
                                      <span class="round-tab">4</span> <i>Riepilogo</i></a>
                                </li>
                            </ul>
                        </div>

                        <form role="form" class="login-box">
                            <div class="tab-content" id="main_form">
                                <div class="tab-pane active" role="tabpanel" id="step1">
                                    <h4 class="text-center">Dati Indirizzo</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group autoComplete">
                                                <label>Toponimo</label>
                                                <input class="form-control advancedAutoComplete" type="text" autocomplete="on"
                                                    placeholder="ricerca per toponimo" id="toponimo" name="toponimo">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group autoComplete">
                                                <label>Civico</label>
                                                <input class="form-control advancedAutoComplete" type="text" autocomplete="on"
                                                    placeholder="ricerca civico" id="civico" name="civico" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="list-inline pull-right">
                                        <li><button type="button" class="default-btn next-step">Continua</button></li>
                                    </ul>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="step2">
                                    <h4 class="text-center">Dati generali dell'utente</h4>

                                    <div class="col-md-6">
                                        <div class="form-group has-warning has-feedback">
                                            <label>Nome</label>
                                            <input class="form-control" type="text" name="nome" data-form="utente" id="nome" placeholder="Inserisci il nome">
                                            <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(RICHIESTO)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-warning has-feedback">
                                            <label>Cognome</label>
                                            <input class="form-control" type="text" data-form="utente" name="cognome" id="cognome" placeholder="Inserisci il cognome">
                                            <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(RICHIESTO)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-warning has-feedback">
                                            <label>Codice fiscale</label>
                                            <input class="form-control" type="text" data-form="utente" name="codiceFiscale" id="codiceFiscale" placeholder="Inserisci il codice fiscale">
                                            <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(RICHIESTO)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input class="form-control" type="text" data-form="utente" name="eMail" id="eMail" placeholder="Inserisci l'indirizzo mail">
                                            <!-- <div class="alert alert-danger hidden" role="alert">
                                              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                              <span class="sr-only">Errore:</span>
                                              Inserire un indirizzo email valido.
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-warning has-feedback">
                                            <label>Numero di telefono</label>
                                            <input class="form-control" type="text" name="numero" id="numero" placeholder="Inserisci un recapito telefonico">
                                            <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(RICHIESTO)</span>
                                            <!-- <div class="alert alert-danger" role="alert">
                                              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                              <span class="sr-only">Error:</span>
                                              Inserire un recapito telefonico valido.
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Usare classe has-warning per enfatizzare i campi obbligatori  -->
                                        <div class="form-group has-warning has-feedback">
                                            <label>Tipo telefono</label>
                                            <select class="form-control" name="tipo" id="tipo" placeholder="">
                                              <option></option>
                                              <option value="FISSO">FISSO</option>
                                              <option value="CELLULARE">CELLULARE</option>
                                            </select>
                                            <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Comprensione dei messaggi vocali</label>
                                            <select class="form-control" name="lingua" id="lingua" placeholder="">
                                              <option></option>
                                              <option value="BUONA SE IN LINGUA ITALIANA">BUONA SE IN LINGUA ITALIANA</option>
                                              <option value="BUONA SOLO SE IN LINGUA STRANIERA">BUONA SOLO SE IN LINGUA STRANIERA</option>
                                              <option value="AUDIOLESO o NON UDENTE">AUDIOLESO o NON UDENTE</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Lingua straniera</label>
                                            <select class="form-control" name="linguaNoItalia" id="linguaNoItalia" placeholder="">
                                              <option></option>
                                            </select>
                                        </div>
                                    </div>

                                    <ul class="list-inline pull-right">
                                        <li><button type="button" class="default-btn prev-step">In dietro</button></li>
                                        <!-- <li><button type="button" class="default-btn next-step skip-btn">Skip</button></li> -->
                                        <li><button type="button" class="default-btn next-step" id="submitStep2">Continua</button></li>
                                    </ul>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="step3">
                                    <h4 class="text-center">Pericolosità</h4>

                                    <div class="col-md-6">
                                        <div class="form-group has-feedback">
                                            <label>Posizione</label>
                                            <select class="form-control" name="posizione" id="posizione" placeholder="">
                                              <option></option>
                                              <option value="STRADA">STRADA</option>
                                              <option value="SOTTOSTRADA">SOTTOSTRADA</option>
                                              <option value="">SOPRASTRADA</option>
                                            </select>
                                            <!-- <input class="form-control" type="text" name="posizione" placeholder=""> -->
                                            <span class="glyphicon glyphicon-exclamation-sign form-control-feedback text-primary" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-feedback">
                                            <label>Vulnerabilità personale</label>
                                            <select class="form-control" name="vulnerabilita" id="vulnerabilita" placeholder="">
                                              <option></option>
                                              <option value="SOSTENIBILE">SOSTENIBILE</option>
                                              <option value="MATERIALE">MATERIALE</option>
                                              <option value="PERSONALE">PERSONALE</option>
                                            </select>
                                            <span class="glyphicon glyphicon-exclamation-sign form-control-feedback text-primary" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-warning has-feedback">
                                            <label>Telefono amministratore</label>
                                            <input class="form-control" type="text" name="tel_amministratore" id="tel_amministratore" placeholder="">
                                            <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                            <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-warning has-feedback">
                                            <label>Telefono proprietario</label>
                                            <div class="custom-file">
                                              <input class="form-control" type="text" name="tel_proprietario" id="tel_proprietario" placeholder="">
                                              <span class="glyphicon glyphicon-asterisk form-control-feedback" aria-hidden="true"></span>
                                              <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Tipo utente</label>
                                            <!-- <input class="form-control" type="text" name="tipo" placeholder=""> -->
                                            <select class="form-control" name="tipo" placeholder="">
                                              <option></option>
                                              <option value="RESIDENTE">RESIDENTE</option>
                                              <option value="NON RESIDENTE">NON RESIDENTE</option>
                                            </select>
                                        </div>
                                    </div>

                                    <ul class="list-inline pull-right">
                                        <li><button type="button" class="default-btn prev-step">In dietro</button></li>
                                        <!-- <li><button type="button" class="default-btn next-step skip-btn">Skip</button></li> -->
                                        <li><button type="button" class="default-btn next-step" id="submitStep3">Continua</button></li>
                                    </ul>

                                    <div class="col-md-12">
                                      <blockquote>
                                        <p class="text-info">la registrazione al servizio è consentita per i soli appartamenti situati
                                          al <strong>piano strada</strong> o <strong>sottostrada</strong> dei civici che risultano ad alta pericolosità idraulica.</p>
                                      </blockquote>
                                      <blockquote>
                                        <p class="text-info">
                                          PIANO STRADA=se piano di calpestio abitabile è situato ad una quota a livello stradale <br/>
                                          PIANO SOTTOSTRADA=se piano di calpestio abitabile è situato a una quota inferiore al livello stradale <br/>
                                          PIANO SOPRASTRADA=se piano di calpestio abitabile è situato a una quota superiore al livello stradale</p>
                                      </blockquote>
                                      <blockquote>
                                        <p class="text-info">
                                          Per vulnerabilità si intende:
                                          <ul class="text-info">
                                              <li>Sostenibile (AUTOPROTEZIONE DIRETTA)=possibilità di raggiungere posizioni di sicurezza situate a quote superiori al livello stradale</li>
                                              <li>Materiale (AUTOPROTEZIONE INDIRETTA)=dovuta all’impossibilità di raggiungere posizioni di sicurezza a causa delle caratteristiche strutturali dell'unità immobiliare (assenza di piani superiori all’abitazione)</li>
                                              <li>Personale (PROTEZIONE ASSISTITA)=dovuta all'impossibilità di raggiungere posizioni di sicurezza a causa di inabilità di componenti del nucleo familiare</li>
                                          </ul>
                                        </p>
                                      </blockquote>
                                        <blockquote>
                                        <p class="text-info">Nel caso non fosse presente l'amministratore, indicare nel campo "Telefono Amministratore" lo stesso numero di telefono del proprietario.</p>
                                      </blockquote>
                                    </div>

                                </div>
                                <div class="tab-pane" role="tabpanel" id="step4">
                                    <h4 class="text-center">Riepilogo delle informazioni</h4>
                                    <!-- <div class="all-info-container">
                                        <div class="list-content">
                                            <a href="#listone" data-toggle="collapse" aria-expanded="false" aria-controls="listone">Collapse 1 <i class="fa fa-chevron-down"></i></a>
                                            <div class="collapse" id="listone">
                                                <div class="list-box">
                                                    <div class="row">

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>First and Last Name *</label>
                                                                <input class="form-control" type="text"  name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Phone Number *</label>
                                                                <input class="form-control" type="text" name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-content">
                                            <a href="#listtwo" data-toggle="collapse" aria-expanded="false" aria-controls="listtwo">Collapse 2 <i class="fa fa-chevron-down"></i></a>
                                            <div class="collapse" id="listtwo">
                                                <div class="list-box">
                                                    <div class="row">

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Address 1 *</label>
                                                                <input class="form-control" type="text" name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>City / Town *</label>
                                                                <input class="form-control" type="text" name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Country *</label>
                                                                <select name="country2" class="form-control" id="country2" disabled="disabled">
                                                                    <option value="NG" selected="selected">Nigeria</option>
                                                                    <option value="NU">Niue</option>
                                                                    <option value="NF">Norfolk Island</option>
                                                                    <option value="KP">North Korea</option>
                                                                    <option value="MP">Northern Mariana Islands</option>
                                                                    <option value="NO">Norway</option>
                                                                </select>
                                                            </div>
                                                        </div>



                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Legal Form</label>
                                                                <select name="legalform2" class="form-control" id="legalform2" disabled="disabled">
                                                                    <option value="" selected="selected">-Select an Answer-</option>
                                                                    <option value="AG">Limited liability company</option>
                                                                    <option value="GmbH">Public Company</option>
                                                                    <option value="GbR">No minimum capital, unlimited liability of partners, non-busines</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Business Registration No.</label>
                                                                <input class="form-control" type="text" name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Registered</label>
                                                                <select name="vat2" class="form-control" id="vat2" disabled="disabled">
                                                                    <option value="" selected="selected">-Select an Answer-</option>
                                                                    <option value="yes">Yes</option>
                                                                    <option value="no">No</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Seller</label>
                                                                <input class="form-control" type="text" name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Company Name *</label>
                                                                <input class="form-control" type="password" name="name" placeholder="" disabled="disabled">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-content">
                                            <a href="#listthree" data-toggle="collapse" aria-expanded="false" aria-controls="listthree">Collapse 3 <i class="fa fa-chevron-down"></i></a>
                                            <div class="collapse" id="listthree">
                                                <div class="list-box">
                                                    <div class="row">

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Name *</label>
                                                                <input class="form-control" type="text" name="name" placeholder="">
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Number *</label>
                                                                <input class="form-control" type="text" name="name" placeholder="">
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->

                                    <ul class="list-inline pull-right">
                                        <li><button type="button" class="default-btn prev-step">In dietro</button></li>
                                        <li><button type="button" class="default-btn next-step">Sottoscrivi</button></li>
                                    </ul>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
      <script type="text/javascript" defer src="register.js"></script>
    </body>


</html>

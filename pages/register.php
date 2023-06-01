<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Cache-control" content="public">
        <meta name="author" content="szymon">
        <link rel="stylesheet" href="styles/style.php" media="screen">
        <link rel="stylesheet" href="../vendor//leaflet-search/src/leaflet-search.css">
        <link rel="stylesheet" href="register.css">

        <title>Register</title>
        <?php
        function js_console_log($message) {
            $current_file = basename(__FILE__);
            echo '<script>console.log(' . "\"{$current_file}: {$message}\"" . ')</script>';
        }
        function safe_import($const_path) {
            file_exists($const_path) ? require($const_path) : js_console_log($const_path . ' does not exist');
        }
        define('REQ_PATH', __DIR__ . '/req.php');
        define('CHECK_EVENTO_PATH', __DIR__ . '/check_evento.php');
        define('CONTEGGI_DASHBOARD_PATH', __DIR__ . '/conteggi_dashboard.php');
        define('NAVBAR_UP_PATH', __DIR__ . '/navbar_up.php');
        define('NAVBAR_LEFT_PATH', __DIR__ . '/navbar_left.php');
        define('FOOTER_PATH', __DIR__ . '/footer.php');
        define('REQ_BOTTOM_PATH', __DIR__ . '/req_bottom.php');
        define('CONTATORI_EVENTO_EMBED_PATH', __DIR__ . '/contatori_evento_embed.php');
        try {
            safe_import(REQ_PATH);
            // Loading check_evento.php using safe_import does not load useful variable defined in it (i.e. $CF or $nome...)
            // safe_import(CHECK_EVENTO_PATH);
            require(CHECK_EVENTO_PATH);
            safe_import(CONTEGGI_DASHBOARD_PATH);
            if ($profilo_sistema == 10) {
                header("location: ./index_nverde.php");
            }
            elseif ($profilo_sistema > 8) {
                header("location: ./divieto_accesso.php");
            }
        } catch (ErrorException $e) {
            echo 'test_page.php: Eror in head' . $e->getMessage();
        }
        // finally {
        //     js_console_log('Finally loaded head');
        // }
        ?>

    </head>

    <body>
        <?php
            if ($profilo_sistema > 3) {
                echo "<script>location.href='./divieto_accesso.php';</script>";
            };
        ?>
        <div id="wrapper">
            <div id="navbar1">
                <?php
                safe_import(NAVBAR_UP_PATH);
                ?>
            </div>
            <?php
            safe_import(NAVBAR_LEFT_PATH);
            ?>
            <div id="page-wrapper">
                <div class="container">
                    <div class="page-header">
                        <h1>Registrazione <small>servizio di allerta meteo di Protezione Civile</small></h1>
                    </div>
                    <blockquote>
                        <p class="text-info">
                            La registrazione al servizio di allerta meteo di Protezione Civile è consentita per i
                            <strong>soli</strong> civici
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
                                                <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab"
                                                    aria-expanded="true">
                                                    <span class="round-tab">1 </span> <i>Indirizzo</i></a>
                                            </li>
                                            <li role="presentation" class="disabled">
                                                <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab"
                                                    aria-expanded="false">
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
                                            <li role="presentation" class="disabled">
                                                <a href="#step5" data-toggle="tab" aria-controls="step5" role="tab">
                                                    <span class="round-tab">5</span> <i>Esito</i></a>
                                            </li>
                                        </ul>
                                    </div>

                                    <form role="form" class="login-box">
                                        <div class="tab-content" id="main_form">
                                            <div class="tab-pane active" role="tabpanel" id="step1">
                                                <h4 class="text-center">Dati Indirizzo</h4>
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group autoComplete">
                                                            <label>Toponimo</label>
                                                            <input class="form-control advancedAutoComplete" type="text"
                                                                autocomplete="on" placeholder="ricerca per toponimo"
                                                                id="toponimo" name="toponimo">
                                                            <span class="help-block">Seleziona tra i toponimo possibili
                                                                proposti
                                                                dalla ricerca.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group autoComplete">
                                                            <label>Civico</label>
                                                            <input class="form-control advancedAutoComplete" type="text"
                                                                autocomplete="on" placeholder="ricerca civico"
                                                                id="civico" name="civico" disabled>
                                                            <span class="help-block">Seleziona tra i civici possibili
                                                                proposti
                                                                dalla ricerca.</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Interno</label>
                                                            <input class="form-control" type="text"
                                                                placeholder="specificare interno"
                                                                id="internoCivico" name="internoCivico">
                                                        </div>
                                                    </div>
                                                </div>
                                                <ul class="list-inline pull-right">
                                                    <li><button id="submitStep1" type="button"
                                                            class="btn btn-success next-step" disabled>
                                                            <i class="fa fa-step-forward" aria-hidden="true"></i>
                                                            Continua</button></li>
                                                </ul>
                                                <div class="col-md-12">
                                                    <blockquote>
                                                        <p class="text-info">
                                                            Possono registrarsi al servizio i <strong>residenti</strong>
                                                            ed i
                                                            <strong>domiciliati</strong> per i soli indirizzi
                                                            e numeri civici che risultano sul territorio delle
                                                            <strong>aree ad
                                                                ALTA pericolosità per RISCHIO IDRAULICO</strong>
                                                            che abitano al <strong>piano strada</strong> o
                                                            <strong>sottostrada</strong> del Comune di Genova (piano di
                                                            protezione civile).
                                                        </p>
                                                    </blockquote>
                                                </div>
                                            </div>
                                            <div class="tab-pane" role="tabpanel" id="step2">
                                                <h4 class="text-center">Dati generali dell'utente</h4>

                                                <div class="col-md-6">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Nome</label>
                                                        <input class="form-control" type="text" name="nome"
                                                            data-form="utente" id="nome"
                                                            placeholder="Inserisci il nome">
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status"
                                                            class="sr-only">(RICHIESTO)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Cognome</label>
                                                        <input class="form-control" type="text" data-form="utente"
                                                            name="cognome" id="cognome"
                                                            placeholder="Inserisci il cognome">
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status"
                                                            class="sr-only">(RICHIESTO)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Codice fiscale</label>
                                                        <input class="form-control" type="text" data-form="utente"
                                                            name="codiceFiscale" id="codiceFiscale"
                                                            placeholder="Inserisci il codice fiscale">
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status"
                                                            class="sr-only">(RICHIESTO)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input class="form-control" type="text" data-form="utente"
                                                            name="eMail" id="eMail"
                                                            placeholder="Inserisci l'indirizzo mail">
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
                                                        <input class="form-control" type="text" data-form="contatto"
                                                            name="numero" id="numero"
                                                            placeholder="Inserisci un recapito telefonico">
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status"
                                                            class="sr-only">(RICHIESTO)</span>
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
                                                        <select class="form-control" data-form="contatto" name="tipo"
                                                            id="tipo" placeholder="">
                                                            <option></option>
                                                            <option value="FISSO">FISSO</option>
                                                            <option value="CELLULARE">CELLULARE</option>
                                                        </select>
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Comprensione dei messaggi vocali</label>
                                                        <select class="form-control" data-form="contatto" name="lingua"
                                                            id="lingua" placeholder="">
                                                            <option></option>
                                                            <option value="BUONA SE IN LINGUA ITALIANA">BUONA SE IN
                                                                LINGUA
                                                                ITALIANA</option>
                                                            <option value="BUONA SOLO SE IN LINGUA STRANIERA">BUONA SOLO
                                                                SE IN
                                                                LINGUA STRANIERA</option>
                                                            <option value="AUDIOLESO o NON UDENTE">AUDIOLESO o NON
                                                                UDENTE
                                                            </option>
                                                        </select>
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Lingua straniera</label>
                                                        <select class="form-control" data-form="contatto"
                                                            name="linguaNoItalia" id="linguaNoItalia" placeholder="">
                                                            <option></option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <ul class="list-inline pull-right">
                                                    <li><button type="button" class="btn btn-default prev-step">
                                                            <i class="fa fa-step-backward" aria-hidden="true"></i>
                                                            In dietro</button></li>
                                                    <!-- <li><button type="button"
                                                            class="btn btn-warning skip-step skip-btn">Skip</button>
                                                    </li> -->
                                                    <li><button type="button" class="btn btn-success next-step"
                                                            id="submitStep2">
                                                            <i class="fa fa-step-forward" aria-hidden="true"></i>
                                                            Continua</button></li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" role="tabpanel" id="step3">
                                                <h4 class="text-center">Pericolosità</h4>

                                                <div class="col-md-6">
                                                    <div class="form-group has-feedback">
                                                        <label>Posizione</label>
                                                        <select class="form-control" data-form="recapito"
                                                            name="posizione" id="posizione" placeholder="">
                                                            <option></option>
                                                            <option value="STRADA">STRADA</option>
                                                            <option value="SOTTOSTRADA">SOTTOSTRADA</option>
                                                            <option value="">SOPRASTRADA</option>
                                                        </select>
                                                        <!-- <input class="form-control" type="text" name="posizione" placeholder=""> -->
                                                        <span
                                                            class="glyphicon glyphicon-exclamation-sign form-control-feedback text-primary"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group has-feedback">
                                                        <label>Vulnerabilità personale</label>
                                                        <select class="form-control" data-form="recapito"
                                                            name="vulnerabilita" id="vulnerabilita" placeholder="">
                                                            <option></option>
                                                            <option value="SOSTENIBILE">SOSTENIBILE (Gruppo 1)</option>
                                                            <option value="MATERIALE">MATERIALE (Gruppo 2)</option>
                                                            <option value="PERSONALE">PERSONALE (Gruppo 3)</option>
                                                        </select>
                                                        <span
                                                            class="glyphicon glyphicon-exclamation-sign form-control-feedback text-primary"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Telefono amministratore</label>
                                                        <input class="form-control" type="text" data-form="recapito"
                                                            name="amministratore" id="amministratore" placeholder="">
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Telefono proprietario</label>
                                                        <div class="custom-file">
                                                            <input class="form-control" type="text" data-form="recapito"
                                                                name="proprietario" id="proprietario" placeholder="">
                                                            <span
                                                                class="glyphicon glyphicon-asterisk form-control-feedback"
                                                                aria-hidden="true"></span>
                                                            <span id="inputWarning2Status"
                                                                class="sr-only">(warning)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group has-warning has-feedback">
                                                        <label>Tipo utente</label>
                                                        <!-- <input class="form-control" type="text" name="tipo" placeholder=""> -->
                                                        <select class="form-control" data-form="nucleo" name="tipo"
                                                            id="utente-tipo" placeholder="">
                                                            <option></option>
                                                            <option value="RESIDENTE">RESIDENTE</option>
                                                            <option value="NON RESIDENTE">NON RESIDENTE</option>
                                                        </select>
                                                        <span class="glyphicon glyphicon-asterisk form-control-feedback"
                                                            aria-hidden="true"></span>
                                                        <span id="inputWarning2Status" class="sr-only">(warning)</span>
                                                    </div>
                                                </div>

                                                <ul class="list-inline pull-right">
                                                    <li><button type="button" class="btn btn-default prev-step">
                                                            <i class="fa fa-step-backward" aria-hidden="true"></i>
                                                            In dietro</button></li>
                                                    <!-- <li><button type="button"
                                                            class="btn btn-warning skip-step skip-btn">Skip</button>
                                                    </li> -->
                                                    <li><button type="button" class="btn btn-success next-step"
                                                            id="submitStep3" disabled>
                                                            <i class="fa fa-step-forward" aria-hidden="true"></i>
                                                            Continua</button></li>
                                                </ul>

                                                <div class="col-md-12">
                                                    <blockquote>
                                                        <p class="text-info">la registrazione al servizio è consentita
                                                            per i
                                                            soli appartamenti situati
                                                            al <strong>piano strada</strong> o
                                                            <strong>sottostrada</strong> dei
                                                            civici che risultano ad alta pericolosità idraulica.
                                                        </p>
                                                    </blockquote>
                                                    <blockquote>
                                                        <p class="text-info">
                                                            PIANO STRADA=se piano di calpestio abitabile è situato ad
                                                            una quota
                                                            a livello stradale <br />
                                                            PIANO SOTTOSTRADA=se piano di calpestio abitabile è situato
                                                            a una
                                                            quota inferiore al livello stradale <br />
                                                            PIANO SOPRASTRADA=se piano di calpestio abitabile è situato
                                                            a una
                                                            quota superiore al livello stradale</p>
                                                    </blockquote>
                                                    <blockquote>
                                                        <p class="text-info">
                                                            Per vulnerabilità si intende:
                                                        <ul class="text-info">
                                                            <li>Sostenibile (AUTOPROTEZIONE DIRETTA)=possibilità di
                                                                raggiungere
                                                                posizioni di sicurezza situate a quote superiori al
                                                                livello
                                                                stradale</li>
                                                            <li>Materiale (AUTOPROTEZIONE INDIRETTA)=dovuta
                                                                all’impossibilità di
                                                                raggiungere posizioni di sicurezza a causa delle
                                                                caratteristiche
                                                                strutturali dell'unità immobiliare (assenza di piani
                                                                superiori
                                                                all’abitazione)</li>
                                                            <li>Personale (PROTEZIONE ASSISTITA)=dovuta
                                                                all'impossibilità di
                                                                raggiungere posizioni di sicurezza a causa di inabilità
                                                                di
                                                                componenti del nucleo familiare</li>
                                                        </ul>
                                                        </p>
                                                    </blockquote>
                                                    <blockquote>
                                                        <p class="text-info">Nel caso non fosse presente
                                                            l'amministratore,
                                                            indicare nel campo "Telefono Amministratore" lo stesso
                                                            numero di
                                                            telefono del proprietario.</p>
                                                    </blockquote>
                                                </div>

                                            </div>
                                            <div class="tab-pane" role="tabpanel" id="step4">
                                                <h4 class="text-center">Riepilogo delle informazioni</h4>


                                                <div class="all-info-container">
                                                    <div class="list-content">
                                                        <a href="#listone" data-toggle="collapse" aria-expanded="false"
                                                            aria-controls="listone">Dati generali dell'utente <i
                                                                class="fa fa-chevron-down"></i></a>
                                                        <div class="collapse in" id="listone">
                                                            <div class="list-box">
                                                                <div class="row">

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Nome</label>
                                                                            <input class="form-control" type="text"
                                                                                name="nome" data-form="utente"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Cognome</label>
                                                                            <input class="form-control" type="text"
                                                                                name="cognome" data-form="utente"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="row">

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Codice fiscale</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="utente" name="codiceFiscale"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Email</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="utente" name="eMail"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="row">

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Numero di telefono</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="contatto" name="numero"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Tipo telefono</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="contatto" name="tipo"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="row">

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Comprensione dei messaggi
                                                                                vocali</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="contatto" name="lingua"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Lingua straniera</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="contatto"
                                                                                name="linguaNoItalia" data-binded
                                                                                disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="row">

                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label>Tipo utente</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="nucleo" name="tipo"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>



                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="list-content">
                                                        <a href="#listtwo" data-toggle="collapse" aria-expanded="false"
                                                            aria-controls="listtwo">Dati indirizzo <i
                                                                class="fa fa-chevron-down"></i></a>
                                                        <div class="collapse in" id="listtwo">
                                                            <div class="list-box">
                                                                <div class="row">

                                                                    <div class="col-md-10">
                                                                        <div class="form-group">
                                                                            <label>Indirizzo</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="recapito"
                                                                                name="indirizzoCompleto"
                                                                                id="indirizzoCompleto" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label>Interno</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="recapito"
                                                                                name="interno"
                                                                                id="interno" disabled>
                                                                        </div>
                                                                    </div>

                                                                    <input class="form-control hidden" type="text"
                                                                        data-form="recapito" name="idVia" id="idVia"
                                                                        disabled>
                                                                    <input class="form-control hidden" type="text"
                                                                        data-form="recapito" name="numeroCivico"
                                                                        id="numeroCivico" disabled>
                                                                    <input class="form-control hidden" type="text"
                                                                        data-form="recapito" name="esponente"
                                                                        id="esponente" disabled>
                                                                    <input class="form-control hidden" type="text"
                                                                        data-form="recapito" name="colore" id="colore"
                                                                        disabled>

                                                                </div>
                                                                <div class="row">

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Telefono amministratore</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="recapito"
                                                                                name="amministratore" data-binded
                                                                                disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Telefono proprietario</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="recapito" name="proprietario"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                                <div class="row">

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Posizione</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="recapito" name="posizione"
                                                                                data-binded disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Vulnerabilità personale</label>
                                                                            <input class="form-control" type="text"
                                                                                data-form="recapito"
                                                                                name="vulnerabilita" data-binded
                                                                                disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- <div class="list-content">
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
                                        </div> -->
                                                </div>

                                                <ul class="list-inline pull-right">
                                                    <li><button type="button" class="btn btn-default prev-step">
                                                            <i class="fa fa-step-backward" aria-hidden="true"></i>
                                                            In dietro</button></li>
                                                    <li><button type="button" class="btn btn-success next-step"
                                                            id="submitSubscribe">
                                                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                                            Sottoscrivi</button></li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" role="tabpanel" id="step5">
                                                <h4 class="text-center">Esito</h4>

                                                <div class="jumbotron" id="successPanel" hidden>
                                                    <h1>
                                                        <i class="fa fa-flag-checkered" aria-hidden="true"></i>
                                                        Complimenti!
                                                    </h1>
                                                    <p>Operazione conclusa con successo</p>
                                                </div>
                                                <div class="panel panel-danger" id="failPanel" hidden>
                                                    <div class="panel-heading">
                                                        Riscontrati problemi nella registrazione!</div>
                                                    <div class="panel-body">
                                                        Attenzione riscontrati problemi nella registrazione!
                                                        Se il problema persiste si prega di contattare l'assistenza.
                                                    </div>
                                                    <!-- <div class="panel-footer">Panel footer</div> -->
                                                </div>

                                            </div>
                                            <div class="clearfix"></div>
                                        </div>

                                    </form>
                                </div>
                                <!-- <button type="button" class="btn btn-warning">Warning</button> -->
                                <a type="button" class="btn btn-warning" href="./dashboard_menu.php">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                                    Torna alla dashboard Chiamate</a>
                            </div>
                        </div>
                    </div>
                </section>
                <script
                    src="https://cdn.jsdelivr.net/npm/bootstrap-autocomplete@2.3.7/dist/latest/bootstrap-autocomplete.min.js"
                    integrity="sha256-yYoz9OwGJUuV2927SrBHgg/bdqOF6oA0v781vA0/0FU=" crossorigin="anonymous"></script>
                <script type="text/javascript" defer src="config.js"></script>
                <script type="text/javascript" defer src="register.js"></script>

            </div>
        </div>
        <?php
        safe_import(FOOTER_PATH);
        safe_import(REQ_BOTTOM_PATH);
        ?>
    </body>


</html>
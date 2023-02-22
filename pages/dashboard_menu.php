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
        <link rel="stylesheet" href="./styles/dashboard_menu.css">
        <title>Dashboard</title>
        <?php
        function js_console_log($message) {
            $current_file = basename(__FILE__);
            echo '<script>console.log(' . "\"{$current_file}: {$message}\"" . ')</script>';
        }
        function safe_import($const_path) {
            js_console_log('checking: ' . $const_path);
            file_exists($const_path) ? require($const_path) : js_console_log($const_path . ' does not exist');
        }
        define('REQ_PATH', __DIR__ . '/req.php');
        // define('CHECK_EVENTO_PATH', __DIR__ . '/check_evento.php');
        // define('CONTEGGI_DASHBOARD_PATH', __DIR__ . '/conteggi_dashboard.php');
        define('FAKE_SPID', __DIR__ . '/check_event_fake.php');
        define('NAVBAR_UP_PATH', __DIR__ . '/navbar_up.php');
        define('NAVBAR_LEFT_PATH', __DIR__ . '/navbar_left.php');
        define('FOOTER_PATH', __DIR__ . '/footer.php');
        define('REQ_BOTTOM_PATH', __DIR__ . '/req_bottom.php');
        define('CONTATORI_EVENTO_EMBED_PATH', __DIR__ . '/contatori_evento_embed.php');
        define('FORBIDDEN_PATH', __DIR__ . '/divieto_accesso.php');
        try {

            safe_import(REQ_PATH);
            // Loading check_evento.php using safe_import does not load useful variable defined in it (i.e. $CF or $nome...)
            // safe_import(CHECK_EVENTO_PATH);
            // require(CHECK_EVENTO_PATH);
            safe_import(FAKE_SPID);
            // safe_import(CONTEGGI_DASHBOARD_PATH);
        } catch (ErrorException $e) {
            echo 'test_page.php: Eror in head' . $e->getMessage();
        }
        ?>

    </head>

    <body>

        <?php
        if ($profilo_sistema > 3) {
            echo "<script>location.href='./divieto_accesso.php';</script>";
        }
        ;
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
                <!-- //  -->
                <!-- PUT YOUR PAGE HERE -->
                <!-- // -->
                <div class="container" id="foretext_container">
                    <div class="page-header">
                        <h1>Dashboard <small>Informazioni relative al servizio di allerta meteo di Protezione
                                Civile</small>
                        </h1>
                    </div>
                </div>
                <div class="container" id="tabs_container">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#user_tab" aria-controls="users" role="tab"
                                data-toggle="tab">Gestione utenti</a></li>
                        <li role="presentation"><a href="#msg_tab" aria-controls="messages" role="tab"
                                data-toggle="tab">Gestione messaggi</a></li>
                        <li role="presentation"><a href="#camp_tab" aria-controls="campaigns" role="tab"
                                data-toggle="tab">Dashboad campagne</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">

                        <div role="tabpanel" class="tab-pane fade in active" id="user_tab">
                            <h1 id="dashboard_header">Gestione utenti</h1>
                            <a href="./register.php" type="button" class="btn btn-primary">
                                <i class="fa fa-user-plus" aria-hidden="true"></i>
                                Registra un nuovo utente
                            </a>
                            <div class="container " id="bstr_user" style="display: none;">
                                <h3 id="user_list_header">Utenti registrati</h3>
                                <!-- Data table for users -->
                                <table class="table-hover" id="user_table_1">
                                </table>
                                </tr>
                            </div>
                            <hr class="hr" />
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="msg_tab">
                            <h1 id="dashboard_header">Dashboard messaggi</h1>
                            <div class="container" id="API_bttn_msg_container">
                                <form>
                                    <div class="form-group">
                                        <label for="comment">Testo del messaggio:</label>
                                        <textarea class="form-control" rows="5" id="msg_content"
                                            placeholder="Contenuto del messaggio"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Note:</label>
                                        <input type="text" class="form-control" id="msg_note"
                                            placeholder="Inserisci una descrizione utile a identificare il messaggio">
                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Numeri di telefono:</label>
                                        <textarea class="form-control" rows="5" id="test_phone_numbers"
                                            placeholder="Inserisci numeri di telefono separati da uno spazio e lancia una campagna di test"></textarea>
                                    </div>

                                </form>
                                <div class="form-group">
                                    <label for="comment">Vulnerabilità:</label>
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-primary active">
                                            <input type="radio" value=2 name="group_option" id="radio_grp_2" checked>
                                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                            materiale</input>
                                        </label>
                                        <label class="btn btn-primary">
                                            <input type="radio" value=3 name="group_option" id="radio_grp_3">
                                            <i class="fa fa-wheelchair" aria-hidden="true"></i>
                                            personale</input>
                                        </label>
                                        <label class="btn btn-primary">
                                            <input type="radio" value=1 name="group_option" id="radio_grp_1">
                                            <i class="fa fa-handshake" aria-hidden="true"></i>
                                            sostenibile</input>
                                        </label>
                                    </div>
                                </div>

                                <!-- Buttons voice -->
                                <div class="form-group">
                                    <label for="comment">Voce messaggio:</label>
                                    <div class="btn-group">
                                        <div class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary active" id="voice_picker_female">
                                                <input type="radio" value="F" name="voice_options" checked>
                                                <i class="fa fa-female" aria-hidden="true"></i>
                                                Femminile</input>
                                            </label>
                                            <label class="btn btn-primary" id="voice_picker_male">
                                                <input type="radio" value="M" name="voice_options">
                                                <i class="fa fa-male" aria-hidden="true"></i>
                                                Maschile</input>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-toolbar" role="toolbar">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-success btn-auto" type="submit"
                                            id="button_create_message">
                                            <i class="fa fa-edit" aria-hidden="true"></i>
                                            <!-- <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> -->
                                            Crea nuovo messaggio
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-warning btn-auto" type="submit"
                                            id="button_create_campaign" data-toggle="tooltip" data-placement="bottom"
                                            title="Crea campagna a partire dal messaggio">
                                            <i class="fa fa-bullhorn" aria-hidden="true"></i>
                                            Lancia campagna di chiamate da messaggio
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-info btn-auto" type="submit" id="button_test_message"
                                            data-toggle="tooltip" data-placement="bottom"
                                            title="Creare un messaggio di prova per ascoltarlo in seguito">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            Test del messaggio
                                        </button>
                                        <a class="btn btn-info btn-auto" href="#" id="button_download_mp3"
                                            data-toggle="tooltip" data-placement="bottom"
                                            title="Scarica il messaggio mp3" disabled>
                                            <i class=" fa fa-download" aria-hidden="true"></i>
                                            Scarica il messaggio mp3
                                        </a>
                                    </div>
                                </div>
                                <!-- Add an outline for container -->
                                <!-- Data table for messages -->
                                <div class="container" id="bstr_message" style="display: none;">
                                    <h3 id="msg_list_header">Lista messaggi</h3>
                                    <div id="msg_toolbar">
                                        <button id="button_delete" class="btn btn-danger">
                                            <i class="fa fa-trash"></i> Rimuovi i messaggi selezionati
                                        </button>
                                    </div>
                                    <table class="table-hover " id="msg_table" data-togle="table"
                                        data-toolbar="msg_toolbar">
                                    </table>
                                </div>
                                <hr class="hr" />
                                <button class="btn btn-primary" id="button_msg_list" type="button">
                                    <i class="fa fa-table" aria-hidden="true"></i>
                                    Recupera la lista dei messagi</button>

                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="camp_tab">
                            <h1 id="dashboard_header">Dashboard chiamate</h1>
                            <div class="container" id="API_date_container">
                                <!-- <h3 id="date_header">Seleziona un periodo di ricerca</h3> -->

                                <label for="basic-url">Seleziona un periodo di ricerca</label>
                                <div class="input-group input-daterange">
                                    <span class="input-group-addon">Dal</span>
                                    <input placeholder="Select start date" class="form-control" id="ui_date_start"
                                        autocomplete="off">
                                    <span class="input-group-addon">al</span>
                                    <input placeholder="Select end date" class="form-control" id="ui_date_end"
                                        autocomplete="off">
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" id="button_campaign_from_to" type="button">
                                            <i class="fa fa-table" aria-hidden="true"></i>
                                            Recupera informazioni
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <!-- Add an outline for container -->
                            <!-- Data table for campaigns -->
                            <div class="container " id="bstr_camp_vis" style="display: none;">
                                <hr class="hr" />

                                <div class="well well-lg">
                                    <h3 id="camp_vis_header">Dettaglio campagna chiamate</h3>
                                    <table class="table-hover" id="camp_table" data-togle="table">
                                    </table>
                                </div>
                            </div>
                            <!-- Data table for campaign -->
                            <div class="container" id="bstr_camp" style="display: none;">
                                <hr class="hr" />
                                <h3 id="camp_list_header">Campaign list</h3>
                                <div id="camp_toolbar">
                                </div>
                                <table class="table-hover" id="camp_table_time" data-togle="table"
                                    data-toolbar="camp_toolbar">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- defer blocks execution of script untill document is loaded -->
        <!-- <script type="text/javascript" src="..\node_modules\dayjs\dayjs.min.js"></script> -->
        <?php
        safe_import(FOOTER_PATH);
        safe_import(REQ_BOTTOM_PATH);
        ?>
        <script type="text/javascript" defer src="config.js"></script>
        <!-- <script type="text/javascript" defer src="dashboard_js.js"></script> -->
        <script type="module" defer src="dashboard_js.js"></script>
        <script type="module" src="dashboard_api.js"></script>
    </body>

</html>
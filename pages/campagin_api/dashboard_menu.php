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
        define('ROOT_DIR', realpath(__DIR__ . '/..'));
        try {
            $config_path = __DIR__ . '/config.php';
            $path_to_boostrap = explode("/", __DIR__);
            require(__DIR__ . '/../req.php');
            # Getting the conn.php file from the parent directory
            $conn_php_path = explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        // This is an example of how to use SQL queries:
        // $my_query = "SELECT * FROM users.utenti WHERE username = '$_SESSION[username]';";
        // $result = pg_query($conn, $my_query);
        // $r = pg_fetch_assoc($result);
        // echo $r['username'];
        ?>
        <style>
            body {
                margin-top: 100px;
            }
        </style>

    </head>

    <body>
        <div class="container" id="foretext_container">
            <?php if ($_DEBUG) {
                echo "This is the __DIR__: " . __DIR__ . "<br>";
                echo "This is the config path: " . "$config_path" . "<br>";
                echo "This is the root path: " . ROOT_DIR . "<br>";
                echo "This is the bootstrap path: $path_to_boostrap[2]" . "<br>";
            }

            require('../check_event_fake.php');
            require('../navbar_up.php');
            ?>
            <h1 id="dashboard_header">Dashboard</h1>
            <p id="button_result">testing javascript</p>
        </div>

        <div class="container" id="API_bttn_container">
            <button class="btn btn-primary" id="button_msg_list" type="button">Get message
                list </button>
            <button class="btn btn-primary" id="button_vis_campaign" type="button">Visualise campaign </button>
            <button class="btn btn-primary" id="button_user_list" type="button">Get user list</button>
            <button class="btn btn-primary" id="button_campaign_from_to" type="button">Get campaign from/to</button>
        </div>

        <div class="container" id="API_date_container">
            <div class="md-form md-outline input-with-post-icon input-group date datepicker" data-provide="datepicker">
                <input placeholder="Select start date" type="text" class="form-control" id="ui_date_start">
                <label for="ui_date_start">Pick a date for campaign</label>
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-th"></span>
                </div>
            </div>
            <div class="md-form md-outline input-with-post-icon input-group date datepicker" data-provide="datepicker">
                <input placeholder="Select end date" type="text" class="form-control" id="ui_date_end">
                <label for="ui_date_end">Pick a date for campaign</label>
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-th"></span>
                </div>
            </div>
        </div>

        <div class="container" id="API_message_container">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button class="btn btn-outline-secondary" type="button">Add a message</button>
                </div>
                <input type="text" class="form-control" placeholder="Write the message here" aria-label=""
                    aria-describedby="basic-addon1">
                <input type="text" class="form-control" placeholder="Pick a male of female voic" aria-label=""
                    aria-describedby="basic-addon1">
            </div>
            <div class="input-group">
                <input type="text" class="form-control" aria-label="Text input with dropdown button">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">Dropdown</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>
                        <div role="separator" class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Separated link</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" id="API_results_container">
            <h1 id="results_header">Results</h1>
        </div>
        <!-- Data table for messages -->
        <div class="container" id="bstr_message" style="display: none;">
            <table class="table-hover" id="msg_table">
                <h3 id="msg_list_header">Messages</h3>
                <thead>
                    <!-- Add a header for table -->
                    <tr>
                        <th data-field="message_date">Date</th>
                        <th data-field="message_id">ID</th>
                        <th data-field="message_note">Note</th>
                        <th data-field="message_duration">Duration</th>
                        <th data-field="message_dimension">Dimension</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!-- Data table for campaigns -->
        <div class="container" id="bstr_camp_vis" style="display: none;">
            <table class="table-hover" id="camp_table">
                <thead>
                    <!-- Add a header for table -->
                    <h3 id="camp_viz_header">Campaign visualization</h3>
                    <tr>
                        <th data-field="campaign_id">Campaign id</th>
                        <th data-field="campaign_telephone">Campaign telephone</th>
                        <th data-field="campaign_note">Note</th>
                        <th data-field="campaign_type">Type</th>
                        <th data-field="campaign_duration">Duration</th>
                        <th data-field="campaign_start_date">Start date</th>
                        <th data-field="campaign_end_date">End date</th>
                        <th data-field="campaign_status">Status</th>
                        <th data-field="campaign_identifier">Identifier</th>
                        <th data-field="campaign_contacts_num">Number of contacts</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!-- Data table for users -->
        <div class="container" id="bstr_user" style="display: none;">
            <table class="table-hover" id="user_table_1">
                <thead>
                    <!-- Add a header for table -->
                    <!-- "cognome" : "gruppo" : "id" :", "indirizzo" : "nome" : "numero_civico" : "sorgente" : "telefono": -->
                    <h3 id="user_list_header">Users list</h3>
                    <tr>
                        <th data-field="user_id">User id</th>
                        <th data-field="user_surname">Surname</th>
                        <th data-field="user_name">Name</th>
                        <th data-field="user_group">Group</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!-- Data table for campaign -->
        <div class="container" id="bstr_camp" style="display: none;">
            <table class="table-hover" id="camp_table_time">
                <thead>
                    <!-- Add a header for table -->
                    <h3 id="camp_list_header">Campaign list</h3>
                    <tr>
                        <th data-field="camp_id">Campaign ID</th>
                        <th data-field="camp_type">Type</th>
                        <th data-field="camp_date">Campaign date</th>
                        <th data-field="camp_user">Campaign user</th>
                        <th data-field="camp_contact">Campaign contact</th>
                        <th data-field="camp_identifier">Campaign noter</th>
                    </tr>
                </thead>
            </table>
        </div>
        <script type="text/javascript" src="dashboard_js.js"></script>
    </body>

</html>
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
    #TODO Make the routing using getcwd() and dirname() functions
    $_DEBUG = false;
    define('ROOT_DIR', realpath(__DIR__ . '/..'));
    try {
        $config_path = __DIR__ . '/config.php';
        $path_to_boostrap = explode("/", __DIR__);
        require(__DIR__ . '/../req.php');
        // check_evento.php already does this:
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
    <div class="container" id="API_buttons_container">
        <button class="btn btn-primary" id="button_msg_list" type="button">Get message
            list </button>
        <button class="btn btn-primary" id="button_vis_campaign" type="button">Visualise campaign </button>
        <button class="btn btn-primary" id="button_user_list" type="button">Get user list</button>
        <button class="btn btn-primary" id="button_campaign_from_to" type="button">Get campaign from/to</button>

        <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
    </div>
    <div class="container" id="API_results_container">
        <h1 id="results_header">Results</h1>
    </div>
    <!-- Data table for messages -->
    <div class="container" id="bstr_message" style="display: none;">
        <table class="table-hover" id="msg_table">
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
    <script type="text/javascript" src="dashboard_js.js">
    // This is ommited in the output
    console.log("inside of a script tag");
    </script>
</body>

</html>
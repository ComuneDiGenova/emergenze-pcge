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
        <style>
        body {
            margin-top: 100px;
        }
        </style>

    </head>

    <body>
        <div class="container" id="foretext_container">
            <?php
            // This is workaround for SPID account check
            require "../check_event_fake.php";
            // This is for the navbar component
            require "../navbar_up.php";
            ?>
            <h1 id="dashboard_header">Dashboard</h1>
            <p id="dashboard_text">This is a dashboard for the Python API. You can use it to send messages to the users
                of the application. You can also visualise the campaign and get the list of users.</p>
        </div>

        <div class="container" id="API_bttn_container">
            <button class="btn btn-primary" id="button_msg_list" type="button">Get message
                list </button>
            <button class="btn btn-primary" id="button_vis_campaign" type="button">Visualise campaign </button>
            <button class="btn btn-primary" id="button_user_list" type="button">Get user list</button>

        </div>
        <div class="container">
            <hr class="hr" />

            <input type="text" class="form-control" id="camp_id" placeholder="ID of campaign to visualize ">
        </div>

        <div class="container" id="API_date_container">
            <div class="md-form md-outline input-with-post-icon input-group date datepicker" data-provide="datepicker">
                <label for="ui_date_start">Pick a start date for campaign search</label>
                <input placeholder="Select start date" type="text" class="form-control" id="ui_date_start">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-th"></span>
                </div>
            </div>

            <div class="md-form md-outline input-with-post-icon input-group date datepicker" data-provide="datepicker">
                <label for="ui_date_end">Pick a end date for campaign search</label>
                <input placeholder="Select end date" type="text" class="form-control" id="ui_date_end">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-th"></span>
                </div>
            </div>

            <button class="btn btn-primary" id="button_campaign_from_to" type="button">Get campaign from/to</button>
        </div>

        <div class="container" id="API_message_container">
            <div class="container">
                <hr class="hr" />
            </div>
            <form>
                <div class="form-group">
                    <label for="comment">Message:</plabel>
                        <textarea class="form-control" rows="5" id="msg_content"
                            placeholder="Message content"></textarea>
                        <input type="text" class="form-control" id="msg_note" placeholder="Message note ">
                        <input type="text" class="form-control" id="msg_id" placeholder="ID of pre-made message ">
                </div>
            </form>
            <div class="card">
                <label>
                    <input type="radio" class="option-input radio" value=1 name="group_option" id="radio_grp_1" />
                    Group 1
                </label>
                <label>
                    <input type="radio" class="option-input radio" value=2 name="group_option" id="radio_grp_2"
                        checked />
                    Group 2
                </label>

            </div>
            <!-- Buttons voice -->
            <div class="btn-group">

                <div class="btn-group" data-toggle="buttons">
                    <button class="btn btn-primary" type="submit" name="voice_options" id="voice_picker_female"
                        value="F">
                        Female
                        voice
                    </button>
                </div>

                <div class="btn-group" data-toggle="buttons">
                    <button class="btn btn-primary" type="submit" name="voice_options" id="voice_picker_male" value="M">
                        Male
                        voice</button>
                </div>

                <div class="btn-group" data-toggle="buttons">
                    <button class="btn btn-success" type="submit" id="button_send_message">Create a
                        message</button>
                </div>
                <!-- <div class="col-2 text-left" role="group"> -->
                <div class="btn-group" data-toggle="buttons">
                    <button class="btn btn-warning" type="submit" id="button_create_campaign">Create and start a
                        campaign</button>
                </div>
            </div>



        </div>
        <div class="container" id="API_results_container">
            <h1 id="results_header">Results</h1>
        </div>
        <!-- Data table for messages -->
        <div class="container" id="bstr_message" style="display: none;">
            <h3 id="msg_list_header">Messages</h3>
            <div id="msg_toolbar">
                <button id="button_delete" class="btn btn-danger">
                    <i class="glyphicon glyphicon-remove"></i> Delete selected messages
                </button>
            </div>
            <table class="table-hover" id="msg_table" data-togle="table" data-toolbar="msg_toolbar">
                <thead>
                    <!-- Add a header for table -->
                    <tr>
                        <th data-field="state" data-checkbox="true"> Select checkbox</th>
                        <th data-field="message_date"> Date</th>
                        <th data-field="message_id"> ID</th>
                        <th data-field="message_note"> Note</th>
                        <th data-field="message_duration"> Duration</th>
                        <th data-field="message_dimension"> Dimension</th>
                    </tr>
                </thead>
                <!-- <tbody id="msg_table_body">
                    <tr>
                        <td>
                            <button class="btn btn-danger badge-pill" id="deletion_button">
                                Delete_wololo
                            </button>
                        </td>
                    </tr>
                </tbody> -->
            </table>
        </div>
        <!-- Data table for campaigns -->
        <div class="container" id="bstr_camp_vis" style="display: none;">
            <div class="container">
                <hr class="hr" />
            </div>
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
            <div class="container">
                <hr class="hr" />
            </div>
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
            <div class="container">
                <hr class="hr" />
            </div>
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
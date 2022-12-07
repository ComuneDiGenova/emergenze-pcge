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
</head>

<body>
    <div>
    </div>

    <div class="container" id="foretext_container">
        <?php
        echo "This is the __DIR__ HELLO : " . "$config_path" . "<br>";
        echo "This is the root path : " . ROOT_DIR . "<br>";
        echo "This is the bootstrap path: $path_to_boostrap[2]" . "<br>";
        require('../check_event_fake.php');
        require('../navbar_up.php');
        ?>
        <h1 id="empty_paragraph">Dashboard</h1>
        <p id="button_result">testing javascript</p>
    </div>
    <div class="container" id="API_buttons_container">
        <button class="btn btn-primary" type="button" onclick="_retr_message_list()">Get message list </button>
        <button class="btn btn-primary" type="button" onclick="_retr_campaign_list()">Get campagin list </button>
        <script type="text/javascript" src="dashboard_js.js">
            // This is ommited in the output
            console.log("inside of a script tag");
        </script>
    </div>
    <!-- Data table -->

</body>

</html>
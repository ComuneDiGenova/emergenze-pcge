<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test document</title>
        <?php
        function js_console_log($message) {
            $current_file = basename(__FILE__);
            echo '<script>console.log(' . "\"{$current_file}: {$message}\"" . ')</script>';
        }
        define('REQ_PATH', __DIR__ . '/req.php');
        define('CHECK_EVENTO_PATH', __DIR__ . '/check_evento.php');
        define('CONTEGGI_DASHBOARD_PATH', __DIR__ . '/conteggi_dashboard.php');
        define('NAVBAR_UP_PATH', __DIR__ . '/navbar_up.php');
        define('NAVBAR_LEFT_PATH', __DIR__ . '/navbar_left.php');
        REQ_PATH
        try {
            file_exists(REQ_PATH) ? require(REQ_PATH) : js_console_log(REQ_PATH.' does not exist');
            file_exists(CHECK_EVENTO_PATH) ? require(CHECK_EVENTO_PATH) : js_console_log(CHECK_EVENTO_PATH.' does not exist');
            file_exists(CONTEGGI_DASHBOARD_PATH) ? require(CONTEGGI_DASHBOARD_PATH) : js_console_log(CONTEGGI_DASHBOARD_PATH.' does not exist');
            if ($profilo_sistema == 10) {
                header("location: ./index_nverde.php");
            }
        } catch (ErrorException $e) {
            echo 'test_page.php: Eror in head' . $e->getMessage();
        } finally {
            echo 'test_page.php: Finally loaded head<br>';
        }
        ?>

    </head>

    <body>


        <div id="wrapper">
            <div id="navbar1">
                <?php
                try {
                    echo 'test_page.php: Loading navbar<br>';
                    require(__DIR__ . '/navbar_up.php');
                } catch (ErrorException $e) {
                    echo 'test_page.php: Eror in navbar' . $e->getMessage();
                } finally {
                    echo 'test_page.php: Finally loaded navbar<br>';
                }
                ?>
            </div>
        </div>
        <?php
        try {
            // Make a console log to console with php
            js_console_log("Hello from PHP buddy!");
        } catch (Exception $e) {
            echo "Funtion failed: " . $e->getMessage();
        }
        ?>

    </body>

</html>
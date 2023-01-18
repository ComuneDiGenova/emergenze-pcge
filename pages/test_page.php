<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test document</title>
        <meta http-equiv="Cache-control" content="public">
        <link rel="stylesheet" href="styles/style.php" media="screen">
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
        try {
            safe_import(REQ_PATH);
            safe_import(CHECK_EVENTO_PATH);
            safe_import(CONTEGGI_DASHBOARD_PATH);
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
                safe_import(NAVBAR_UP_PATH);
                ?>
            </div>
            <div id="navbar2">
                <?php
                safe_import(NAVBAR_LEFT_PATH);
                ?>

            </div>
            <?php
            try {
                js_console_log("Hello from PHP buddy!");
            } catch (Exception $e) {
                echo "Funtion failed: " . $e->getMessage();
            }
            ?>
        </div>
        <?php
        safe_import(FOOTER_PATH);
        safe_import(REQ_BOTTOM_PATH);
        ?>
    </body>

</html>
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
        <title>Test document</title>
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
            safe_import(CHECK_EVENTO_PATH);
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
                <!-- PUT YOUR PAGE HERE -->
            </div>
        </div>
        <?php
        safe_import(FOOTER_PATH);
        safe_import(REQ_BOTTOM_PATH);
        ?>
    </body>

</html>
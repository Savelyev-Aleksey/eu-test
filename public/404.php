<?php
    require_once '../system/Router.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Page not found.</title>
        <link href="/css/main.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <p>
            Sorry, the page was you looked -
            (<?php echo Router::input_filter($_GET['_ref']);?>)
            was not found.
        </p>

    </body>
</html><?php
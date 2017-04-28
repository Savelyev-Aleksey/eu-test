<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Page not found.</title>
    </head>
    <body>
        <p>
            Sorry, the page was you looked - 
            <?php echo $_SERVER['HTTP_REFERER'];?>
            was not found.
        </p>
            
    </body>
</html><?php
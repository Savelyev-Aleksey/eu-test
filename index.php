<?php
error_reporting(E_ALL);

ini_set('display_errors', 'on');

spl_autoload_register(function ($class) {

    $directories = array('system', 'models', 'controllers');
    foreach ($directories as $dir)
    {
        $path = realpath($dir.'/'. $class. '.php');
        if (is_file($path))
        {
            include_once $path;
            return;
        }
    }
    throw new Exception("Class ($class) not autoloaded");
});


date_default_timezone_set('Asia/Novosibirsk');
setlocale(LC_ALL, 'en_US.utf-8');


// PHP RFC: Semi-Automatic CSRF Protection
// [link]https://wiki.php.net/rfc/automatic_csrf_protection
// Since PHP 7.1
session_start(['csrf_rewrite'=>SESSION_CSRF_POST, 'csrf_validate'=>SESSION_CSRF_POST]);


DB::init();

Router::add_router('/', NULL, array('controller' => 'user', 'action' => 'index'));

// add default router
Router::add_router('/<controller>(/<action>(/<id>))', NULL, NULL);

Router::runner();
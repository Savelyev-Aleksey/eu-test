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


DB::init();

$user = User::authorize('admin', 'admin');

Router::add_router('/', NULL, array('controller' => 'user', 'action' => 'index'));

// add default router
Router::add_router('/<controller>(/<action>(/<id>))', NULL, NULL);

Router::runner();
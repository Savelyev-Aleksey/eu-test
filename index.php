<?php
error_reporting(-1);

require_once 'models/DB.php';
require_once 'models/User.php';
require_once 'models/Router.php';

DB::init();

$user = User::authorize('admin', 'admin');
var_dump($user->is_authorized());

// add default router
self::add_router('<controller>(/<action>(/:id))', NULL, NULL);

Router::runner();
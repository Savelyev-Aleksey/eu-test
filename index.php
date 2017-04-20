<?php
error_reporting(-1);

require_once 'models/DB.php';
require_once 'models/User.php';

DB::init();

$user = User::authorize('admin', 'admin');
var_dump($user->is_authorized());

<?php

/**
 * Description of Controller_User
 *
 */

class Controller_Base_Auth extends Controller_Base
{
    protected function before()
    {
        if (!self::is_authorized())
            Router::redirect ('user/login');
    }
}

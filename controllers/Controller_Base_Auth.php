<?php

/**
 * Description of Controller_User
 *
 */

class Controller_Base_Auth extends Controller_Base
{

    protected static $template = 'main';

    /**
     * Check that exist session for user before get access in other methods
     * @return type true - if user authorized
     */
    protected static function is_authorized()
    {
        return User::get_authorized_user() !== NULL;
    }


    protected function before()
    {
        if (!self::is_authorized())
            Router::redirect ('user/login');
    }
}

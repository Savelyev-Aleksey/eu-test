<?php

/**
 * Description of Controller_User
 *
 */

class Controller_User extends Controller_Base
{

    public function action_index()
    {
        if ($this->is_authorized())
            Router::redirect('user/login');
    }

    public function action_login()
    {
        self::$login = Router::post('login');
        if (isset(Router::post($key)))
        {
            $user = User::authorize(self::$login, Router::post('password'));
        }

        self::view('login');
    }
}

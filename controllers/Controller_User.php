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
        if (isset(Router::post($key)))
        {
            $user = User::authorize(Router::post('login'), Router::post('password'));
        }

    }
}

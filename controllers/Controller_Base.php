<?php

/**
 * Description of Controller_Base
 *
 
 */
class Controller_Base 
{
    protected static function is_authorised()
    {
        return User::get_authorized_user() !== NULL;
    }
}

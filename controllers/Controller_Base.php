<?php

/**
 * Description of Controller_Base
 *

 */
class Controller_Base
{

    /**
     * Check that exist session for user before get access in other methods
     * @return type true - if user authorized
     */
    protected static function is_authorized()
    {
        return User::get_authorized_user() !== NULL;
    }


    
    public function __construct()
    {
        if (method_exists($this, 'before'))
        {
            $this->before();
        }
    }
}

<?php

/**
 * Description of Controller_Base
 *

 */
class Controller_Base extends Render
{

    public function __construct()
    {
        if (method_exists($this, 'before'))
        {
            $this->before();
        }
    }
}

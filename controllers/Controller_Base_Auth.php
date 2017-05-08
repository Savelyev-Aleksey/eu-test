<?php

/**
 * add before section User auth check.
 * Add CSRF token for post queries
 *
 * @uses User get from User class authorized user.
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
    {
      Router::redirect('user/login');
    }

    if (!Session::check_csrf_token())
    {
      echo 'Warning CSRF detected. Please use insite forms.';
      exit;
    }
  }



}

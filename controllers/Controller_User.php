<?php

/**
 * Description of Controller_User
 *
 */
class Controller_User extends Controller_Base
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



  public function action_index()
  {
    if (!self::is_authorized())
    {
      Router::redirect('user/login');
    }
    Router::redirect('/');
  }



  public function action_login()
  {
    $login = Router::post('login');

    if (isset($login))
    {
      $user = User::authorize($login, Router::post('password'));
      if ($user->is_authorized())
      {
        Router::redirect('/');
      }
    }

    self::view('login', ['login' => $login]);
  }



  public function action_logout()
  {
    $logout = Router::post('logout');

    if (isset($logout))
    {
      User::get_authorized_user()->logout();
      Router::redirect('/');
      exit();
    }
  }

}

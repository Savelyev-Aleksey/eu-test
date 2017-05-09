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
      Request::redirect('user/login');
    }
    Request::redirect('/');
  }



  public function action_login()
  {
    $login = NULL;
    if (Request::is_post())
    {
      $login = Request::post('login');

      if (isset($login))
      {
        $user = User::authorize($login, Request::post('password'));
        if ($user->is_authorized())
        {
          Request::redirect('/');
        }
      }
    }

    self::view('login', ['login' => $login]);
  }



  public function action_logout()
  {
    $logout = Request::post('logout');

    if (isset($logout))
    {
      $user = User::get_authorized_user();
      if ($user)
      {
        $user->logout();
      }
      Request::redirect('/');
      exit();
    }
  }

}

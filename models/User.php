<?php

class User extends ORM
{

  protected static $has_many = ['Good_Review'];
  protected static $authorized_user = NULL;
  protected $authorized = false;
  protected $auth_error = NULL;

  // Test func for manually set adm pass
  public static function get_hash_pass($pass = 'admin')
  {
    return password_hash($pass, PASSWORD_DEFAULT);
  }

  // Check - is user authorized
  public function is_authorized()
  {
    return $this->authorized;
  }

  /*
    Return current instance of auth user.
    If session exist trying to autoload user.
   */

  public static function get_authorized_user()
  {
    if (self::$authorized_user === NULL)
    {
      $uid = Session::get('uid');
      if (!isset($uid))
      {
        return NULL;
      }

      try
      {
        self::$authorized_user = User::find($uid);
      }
      catch (Exception $e)
      {
        $this->logout();
      }
    }
    return self::$authorized_user;
  }

  /**
   * Static function to authorize new logining user.
   * Return authorized user instance on success
   * @param string $login
   * @param string $raw_pass
   * @return NULL if error occur or User authorized instance
   */
  public static function authorize($login, $raw_pass)
  {
    $where = array('login={1}', array('{1}' => $login));
    $users = self::where($where, 'id.ASC', 1);

    if (!count($users) || count($users) > 1)
    {
      $this->auth_error = 'User not found';
      return NULL;
    }

    $user = $users[0];

    if (!password_verify($raw_pass, $user->password))
    {
      $this->auth_error = 'Password was incorrect';
      return NULL;
    }

    $user->authorized = true;

    if (self::$authorized_user)
      self::$authorized_user->logout();

    self::$authorized_user = $user;

    Session::set('uid', $user->id);

    return $user;
  }

  // logout user and close current session
  public function logout()
  {
    self::$authorized_user = NULL;
    if ($this->authorized)
      $this->authorized = false;

    Session::destroy();

    return true;
  }

}

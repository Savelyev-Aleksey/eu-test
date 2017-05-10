<?php

/*
 * The MIT License
 *
 * Copyright 2017 aleksey.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Static session wrapper for more comfortable using.
 * Non blocking Session class.
 * Can be used in ajax parallel requests.
 *
 */
class Session
{

  protected static $life_time = 60 * 60 * 24; // one day
  protected static $csrf_key = '_csrf';



  public static function get($key)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
      session_start(['cookie_lifetime' => self::$life_time]);
    }
    session_write_close();

    if (array_key_exists($key, $_SESSION))
    {
      return $_SESSION[$key];
    }
    return NULL;
  }



  public static function set($key, $value)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
      session_start(['cookie_lifetime' => self::$life_time]);
    }
    $_SESSION[$key] = $value;
    session_write_close();
  }



  public static function free($key)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
      session_start(['cookie_lifetime' => self::$life_time]);
    }
    if (array_key_exists($key, $_SESSION))
    {
      unset($_SESSION[$key]);
    }
    session_write_close();
  }



  public static function pop($key)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
      session_start(['cookie_lifetime' => self::$life_time]);
    }
    $value = NULL;
    if (array_key_exists($key, $_SESSION))
    {
      $value = $_SESSION[$key];
      unset($_SESSION[$key]);
    }
    session_write_close();
    return $value;
  }



  public static function destroy()
  {
    if (session_status() != PHP_SESSION_ACTIVE)
    {
      session_start();
    }
    // close session
    // remove all session variables
    $_SESSION = [];
    session_write_close();
  }



  /**
   * CSRF method generates random token
   * @return string hash
   */
  protected static function new_token()
  {
    $p1 = dechex(time() * 3);
    $p2 = dechex(time() * 5);
    $list = str_split($p1 . $p2);

    shuffle($list);

    return hash('sha1', implode('', $list));
  }



  /**
   * Check Session on POST query and check csrf token must be same for session
   * If token not set generates new.
   * @return boolean FALSE - if POST are sent, but token not setup.
   * FALSE - if token from POST form and Session token not compared.
   * TRUE - if not post query or Tokens are same.
   */
  public static function check_csrf_token()
  {
    $token = Session::get(self::$csrf_key);

    if (count($_POST))
    {
      $post_token = $_POST[self::$csrf_key];
      if ($post_token === NULL)
      {
        return false;
      }
      unset($_POST[self::$csrf_key]);

      if (strcmp($token, $post_token) !== 0)
      {
        return false;
      }
      // Compared successfully - clear old token
      $token = NULL;
    }

    if ($token === NULL)
    {
      Session::set(self::$csrf_key, self::new_token());
    }
    return true;
  }



  /**
   * Gets existing token for Form helper.
   * If token not set, generates new
   * @return string exist or new saved token
   */
  public static function get_token()
  {
    $token = Session::get(self::$csrf_key);
    if ($token === NULL)
    {
      $token = self::new_token();
      Session::set(self::$csrf_key, $token);
    }
    return $token;
  }



  public static function token_key()
  {
    return self::$csrf_key;
  }



  public static function flash(string $text = NULL)
  {
    if ($text === NULL)
    {
      return self::pop('_flash');
    }
    self::set('_flash', $text);
  }

}

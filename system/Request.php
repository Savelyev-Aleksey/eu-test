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
 * Description of Request
 *
 */
class Request
{
  protected static $_method = NULL;
  static protected $_get = array();
  static protected $_post = array();





    /**
   * Return values given by $_GET array
   * @param string $key
   */
  static public function get($key): string
  {
    return array_key_exists($key, self::$_get) ? self::$_get[$key] : NULL;
  }

  /**
   * Return values given by $_POST array
   * @param string $key
   */
  static public function post($key): string
  {
    return array_key_exists($key, self::$_post) ? self::$_post[$key] : NULL;
  }

  /**
   * Escape input from all global arrays $_GET, $_POST
   * replace HTML spec chars on escaped entities.
   * @param string $value
   * @return string
   */
  static public function input_filter(string $value): string
  {
    return htmlspecialchars( trim($value) );
  }



  /**
   * Start current request. And call Router
   */
  static public function start()
  {
    self::$_method = $_SERVER['REQUEST_METHOD'];

    if (count($_POST))
    {
      foreach ($_POST as $key => $value)
      {
        $key = self::input_filter($key);
        self::$_post[$key] = self::input_filter($value);
      }
    }
    if (count($_GET))
    {
      foreach ($_GET as $key => $value)
      {
        $key = self::input_filter($key);
        self::$_get[$key] = self::input_filter($value);
      }
    }

    Router::start();
  }



  /**
   *
   * @return string name of current method or NULL if not init
   */
  public static function get_method(): string
  {
    return self::$_method;
  }


  public static function is_post(): bool
  {
    return self::$_method == 'POST';
  }



  public static function is_get(): bool
  {
    return self::$_method == 'GET';
  }



  public static function redirect($rel_path, bool $add_ref = false, $status = NULL)
  {
    $http = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';
    $server = $_SERVER['SERVER_NAME'];
    if ($rel_path === '/')
      $rel_path = '';
    $ref = $add_ref ? '?_ref=' . self::$_current_path : '';
    header("Location: $http://$server/$rel_path$ref", true, $status);
    exit;
  }

}

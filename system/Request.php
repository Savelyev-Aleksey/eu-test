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
 * @uses Router to recall controller action with matched uri in start() method
 */
class Request
{

  protected static $_method = NULL;
  static protected $_get = array();
  static protected $_post = array();

  /**
   * @var string relative path to script root location
   */
  static protected $_dir = '/';



  /**
   * Return values given by $_GET array
   * @param string $key
   */
  static public function get($key)
  {
    return array_key_exists($key, self::$_get) ? self::$_get[$key] : NULL;
  }



  /**
   * Return values given by $_POST array
   * @param string $key
   */
  static public function post($key)
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
    return (trim($value));
  }



  /**
   * Start current request. And call Router
   */
  static public function start()
  {
    self::$_method = $_SERVER['REQUEST_METHOD'];

    self::$_dir = substr($_SERVER['SCRIPT_NAME'], 0, -9);

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
    $host = $_SERVER['SERVER_NAME'];
    if ($rel_path === '/')
    {
      $rel_path = '';
    }
    $dir = self::$_dir;
    $ref = $add_ref ? '?_ref=' . Router::get_current_path() : '';
    header('Location: ' . $http . '://' . $host . $dir . $rel_path . $ref, true, $status);
    exit;
  }



  /**
   * Concate current directory of project to relative path
   * @param string $rel_path
   * @return string
   */
  public static function uri(string $rel_path): string
  {
    if ($rel_path[0] == '/')
    {
      $rel_path = substr($rel_path, 1);
    }
    return self::$_dir . $rel_path;
  }



  /**
   * return array of values to set in Object.
   * if set type 'all' Get values from get first, after from post
   * @param string $type get, post or all - where get values
   * @param array $fields
   * @return array of values from request
   */
  static public function filter(string $type, array $fields): array
  {
    if (!in_array($type, ['get', 'post', 'all']))
    {
      return NULL;
    }

    $values = [];
    if ($type === 'get' || $type === 'all')
    {
      foreach ($fields as $key)
      {
        $values[$key] = self::get($key);
      }
    }

    if ($type === 'post' || $type === 'all')
    {
      foreach ($fields as $key)
      {
        $val = self::post($key);
        if (array_key_exists($key, $values) && is_null($val))
        {
          continue;
        }
        $values[$key] = $val;
      }
    }
    return $values;
  }

}

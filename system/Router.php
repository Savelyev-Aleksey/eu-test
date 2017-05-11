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
 * Description of Router
 * This class manage routing for app.
 * It's stores all available routes for app.
 * And call matched control class / action.
 *
 * @uses Request for redirect and input_filter
 */
class Router
{

  // Matches a URI group and captures the contents
  const REGEX_GROUP = '\(((?:(?>[^()]+)|(?R))*)\)';
  // Defines the pattern of a <segment>
  const REGEX_KEY = '<([a-zA-Z0-9_]++)>';
  // What can be part of a <segment> value
  const REGEX_SEGMENT = "[^/.,;?\n]++";
  // What must be escaped in the route regex
  const REGEX_ESCAPE = '[.\\+*?\[^\]${}=!|]';

  static protected $_routes = array();
  static protected $_params = array();
  static protected $_current_path = NULL;
  
  /**
   * Pushes new route to Router - routes matching by historical added order.
   * @param type $path added to router by type '<controller>(/<action>(/<id>))'
   * @param type $filter array(
   *           'controller' => '[a-z]+',
   *           'id' => '\d+',
   *         ) for example
   * @param type $defaults associative array for params controller and action
   */
  static public function add_router($path, $filter = NULL, $defaults = NULL)
  {
    $reg_str = self::compile($path, $filter);
    self::$_routes[] = array($reg_str, $defaults);
  }



  /**
   * Return values given by routing parameters such id
   * @param string $key
   * @return string value or NULL if not exist
   */
  static public function param($key): string
  {
    return array_key_exists($key, self::$_params) ? self::$_params[$key] : NULL;
  }



  /**
   * Find route for requested url.
   * Gets all params from string for controller params exclude $_GET options.
   * Camel case controller name by normalize_name function
   * @param type $uri
   * @throws Exception if route sets incorrect
   * @redirect 404 if no one route matched
   */
  static protected function find_requested_path($uri)
  {
    $found = false;
    $path = NULL;
    $defaults = NULL;
    $matches = [];
    foreach (self::$_routes as $route)
    {
      $path = $route[0];
      $defaults = $route[1];

      $found = preg_match($path, $uri, $matches);
      if ($found === false)
        throw new Exception("Route is incorrect, check path: $path");
      if ($found === 1)
        break;
    }

    if (!$found)
      Request::redirect('public/404.php');

    $params = array();

    foreach ($matches as $key => $value)
    {
      if (is_int($key))
      {
        // Skip all unnamed keys
        continue;
      }

      // Set the value for all matched keys
      $params[$key] = $value;
    }
    unset($matches);

    if (is_array($defaults))
    {
      foreach ($defaults as $key => $value)
      {
        if (!isset($params[$key]) OR $params[$key] === '')
        {
          // Set default values for any key that was not matched
          $params[$key] = $value;
        }
      }
    }

    if (!array_key_exists('controller', $params))
    {
      throw new Exception("Not set controller for path: $uri");
    }

    $params['controller'] = self::normalize_name($params['controller']);

    // Set default action if not exist
    if (!array_key_exists('action', $params))
    {
      $params['action'] = 'index';
    }

    self::$_params = $params;
    return true;
  }



  /**
   * Perform lower-case class name to Camel_Case underscore string
   */
  static public function normalize_name($name): string
  {
    $names = explode('-', $name);
    foreach ($names as &$value)
    {
      $value = ucfirst($value);
    }
    unset($value);
    return implode('_', $names);
  }



  /**
   * basic init Router method. Start matched Controller action
   */
  static public function start()
  {


    $path = array_key_exists('PATH_INFO', $_SERVER) ?
            Request::input_filter($_SERVER['PATH_INFO']) : '/';

    self::$_current_path = $path = strtolower($path);

    self::find_requested_path($path);

    $controller_name = 'Controller_' . self::$_params['controller'];
    $action_name = 'action_' . self::$_params['action'];

    $controller = new $controller_name();

    $controller->$action_name();
  }

  /**
   * Returns the compiled regular expression for the route. This translates
   * keys and optional groups to a proper PCRE regular expression.
   *
   *     $compiled = Route::compile(
   *        '<controller>(/<action>(/<id>))',
   *         array(
   *           'controller' => '[a-z]+',
   *           'id' => '\d+',
   *         )
   *     );
   *
   * @return  string
   * @uses    Route::REGEX_ESCAPE
   */
  protected static function compile($uri, array $regex = NULL)
  {
    // The URI should be considered literal except for keys and optional parts
    // Escape everything preg_quote would escape except for : ( ) < >
    $expression = preg_replace('#' . Router::REGEX_ESCAPE . '#', "\\\\$0", $uri);

    if (strpos($expression, '(') !== FALSE)
    {
      // Make optional parts of the URI non-capturing and optional
      $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
    }

    // Insert default regex for keys
    $expression = str_replace(array('<', '>'), array('(?<', '>' . Router::REGEX_SEGMENT . ')'), $expression);

    if ($regex)
    {
      $search = $replace = array();
      foreach ($regex as $key => $value)
      {
        $search[] = "<$key>" . Router::REGEX_SEGMENT;
        $replace[] = "<$key>$value";
      }

      // Replace the default regex with the user-specified regex
      $expression = str_replace($search, $replace, $expression);
    }

    return '#^' . $expression . '$#uD';
  }

}

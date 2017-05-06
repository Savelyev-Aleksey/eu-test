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

class DB
{
  /**
   * Maximum affected records on select state
   */
  const QUERY_LIM = 1000;

  private static $mysqli = NULL;



  function __construct()
  {
    self::init();
  }


  // Init singleton Connection to DB
  public static function init()
  {
    if (!is_null(self::$mysqli))
      return;

    $settings =  parse_ini_file('settings.ini');

    self::$mysqli = new mysqli("localhost", $settings['login'],
      $settings['password'], $settings['database']);

    /* check connection */
    if (self::$mysqli->connect_errno) {
        echo "Connect failed: ", self::$mysqli->connect_errno, " / ",
          self::$mysqli->connect_error, PHP_EOL;
        exit();
    }
  }

  function __destruct()
  {
    if (self::$mysqli)
      self::$mysqli->close();
  }


  static function escape($value)
  {
    return self::$mysqli->real_escape_string($value);
  }


  // Return same array with columns instead of copying
  static function &show_columns($table)
  {
    if (is_null($table))
      return NULL;

    self::init();


    // safe $table string
    $table = self::escape_table($table);

    $result = self::$mysqli->query("SHOW COLUMNS FROM $table");

    if ($result === false)
      throw new Exception("Table $table was not found. ", 1);

    if ($result->num_rows === 0)
      return false;

    $columns = array();
    $data = $result->fetch_all(MYSQLI_NUM);
    foreach ($data as $value)
    {
      // Take only column names without other params
      $columns[] = $value[0];
    }
    $result->free();
    return $columns;
  }


  private static function escape_table($table)
  {
      return self::$mysqli->real_escape_string($table);
  }



  /**
   * condition must set as
   * array('filed={val-key}', array('{val-key}' => $value))
   * set only unique key in query - see PHP doc strtr() more info.
   * @param array $condition - first element is where query,
   * second - associative array of values
   * @return string sql safe where statement WHERE ... or empty string for NULL
   * @throws Exception if array set incorrect
   */
  private static function escape_condition($condition)
  {
    if (is_null($condition))
    {
      return '';
    }

    if (!is_array($condition) || count($condition) != 2)
    {
      throw new Exception("Query condition is set incorrect. ", 1);
    }

    $where = $condition[0];
    $values = $condition[1];
    foreach ($values as &$val)
    {
      if (is_string($val))
      {
        $val = '"' . self::escape($val) . '"';
      }
      elseif (!is_numeric($val))
      {
        throw new Exception("Values in query must be only strings or numbers. ", 1);
      }
    }
    unset($val);
    $where = 'WHERE ' .strtr($where, $values);

    return $where;
  }



  /**
   * Check to safe select filer fields. SELECT `one`, `two` ...
   * Gets array of fields ['one', 'two', 'three'] or 'one_field'.
   * @param array or string $fields
   * @return string
   * @throws Exception if fields set incorrect array of fields or one field
   */
  private static function escape_fields($fields)
  {
    if ($fields === '*' || is_null($fields))
    {
      return '*';
    }

    if (!is_array($fields))
    {
      throw new Exception("fields must array or one string field, or *", 1);
    }

    foreach($fields as &$f)
    {
      $f = '`'. self::escape($f). '`';
    }
    unset($f);

    return implode(',', $fields);
  }



  private static function escape_order($order)
  {
    if (is_null($order))
    {
      return '';
    }

    $fields = explode(',', $order);
    $params = [];
    foreach ($fields as $f)
    {
      $fo = explode('.', $f);
      $name = '"'. self::escape(trim($fo[0])). '"';
      $ord = self::escape(trim($fo[1]));
      $params[] = $name. ' '. $ord;
    }

    return 'ORDER BY '. implode(',', $params);
  }



  /*
    get database select by mysqli and return result
    condition must set as array('filed={val-key}', array('{val-key}' => $value))
    set only unique key in query - see doc strtr() more info.
  */
  static function select($table, $condition = NULL, $fields = '*', $order = NULL, $limit = NULL)
  {
    self::init();

    // 1) Safe table
    $table = self::escape_table($table);

    // 2) Safe condition
    $where = self::escape_condition($condition);

    // 3) fields safe set
    $fields = self::escape_fields($fields);

    // 4 safe order
    if (isset($order))
    {
      $order = self::escape_order($order);
    }

    // 5 safe limit
    if (!(is_int($limit) && $limit > 0))
    {
      $limit = self::QUERY_LIM;
    }

    $limit = 'LIMIT '. $limit;

    $query = "SELECT $fields FROM $table $where $order $limit";
    $res = self::$mysqli->query($query);

    if ($res === false)
    {
      throw new Exception("Query was incorrect. " . $query, 1);
    }

    return $res;
  }

}

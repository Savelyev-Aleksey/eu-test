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
  private static $error = NULL;



  function __construct()
  {
    self::init();
  }



  // Init singleton Connection to DB
  public static function init()
  {
    if (!is_null(self::$mysqli))
      return;

    $settings = parse_ini_file('settings.ini');

    self::$mysqli = new mysqli("localhost", $settings['login'], $settings['password'], $settings['database']);

    /* check connection */
    if (self::$mysqli->connect_errno)
    {
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
      } elseif (!is_numeric($val))
      {
        throw new Exception("Values in query must be only strings or numbers. ", 1);
      }
    }
    unset($val);
    $where = 'WHERE ' . strtr($where, $values);

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

    foreach ($fields as &$f)
    {
      $f = '`' . self::escape($f) . '`';
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
      $name = '"' . self::escape(trim($fo[0])) . '"';
      $ord = self::escape(trim($fo[1]));
      $params[] = $name . ' ' . $ord;
    }

    return 'ORDER BY ' . implode(',', $params);
  }



  /**
   * create database select query but not execute;
   * Added for multi query operations.
   * For example insert new record with default values and return from DB set values.
   *
   * condition must set as array('filed={val-key}', array('{val-key}' => $value))
   * set only unique key in query - see doc strtr() more info.
   * @param string $table name where query affected
   * @param array $condition type of ['id={id} AND ...', ['{id}' => 2, ...]]
   * @param string or array $fields fields filter
   * @param string $order type of 'name.ASC, date.DESC'
   * @param type $limit
   * @return type
   * @throws Exception if query exec fails
   */
  static protected function select_query(string $table, array $condition = NULL, $fields = '*', string $order = NULL, int $limit = NULL)
  {
    self::init();

    if (!strlen($table))
    {
      throw new Exception("Not set table to select");
    }

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

    $limit = 'LIMIT ' . $limit;

    return "SELECT $fields FROM $table $where $order $limit;";
  }



  /**
   * get database select by mysqli and return result
   * condition must set as array('filed={val-key}', array('{val-key}' => $value))
   * set only unique key in query - see doc strtr() more info.
   * @param string $table name where query affected
   * @param array $condition type of ['id={id} AND ...', ['{id}' => 2, ...]]
   * @param string or array $fields fields filter
   * @param string $order type of 'name.ASC, date.DESC'
   * @param type $limit
   * @return type
   * @throws Exception if query exec fails
   */
  static public function select(string $table, array $condition = NULL, $fields = '*', string $order = NULL, int $limit = NULL)
  {
    $query = self::select_query($table, $condition, $fields, $order, $limit);
    $res = self::$mysqli->query($query);

    if ($res === false)
    {
      throw new Exception('Query (' . $query . ') returns error: ' . self::$mysqli->error, 1);
    }

    return $res;
  }



  /**
   * Return prepared insert query string
   * @param string $table
   * @param array $raw_values associative array field_name => field_value
   * @return string prepared insert query string
   * @throws Exception if table or values not set
   */
  static protected function insert_query(string $table, array $raw_values): string
  {
    if (!strlen($table) || !count($raw_values))
    {
      throw new Exception("Nothing to insert");
    }
    $keys = [];
    $values = [];

    foreach ($raw_values as $key => $value)
    {
      $keys[] = '`' . self::escape($key) . '`';
      $values[] = is_null($value) ? 'NULL' : '\'' . self::escape($value) . '\'';
    }

    $keys = implode(',', $keys);
    $values = implode(',', $values);

    return "INSERT INTO $table($keys) VALUES($values);";
  }



  /**
   * Insert in DB new records only.
   * To return newly created record use DB::insert() instead.
   * @param string $table
   * @param array $raw_values
   * @return mixed MySQLi query result Object - selected from DB data
   * @throws Exception if query exec fails
   */
  static public function insert_exec(string $table, array $raw_values): bool
  {
    $query = self::insert_query($table, $raw_values);
    $res = self::$mysqli->query($query);

    if ($res === false)
    {
      throw new Exception('Query (' . $query . ') returns error: ' . self::$mysqli->error, 1);
    }

    return $res;
  }



  /**
   * Insert in DB new record and returns newly created record with filled params.
   * For example returns set id, or default value for field.
   * @param string $table
   * @param array $raw_values
   * @return mixed MySQLi query result Object - selected from DB data
   * @throws Exception if query exec fails
   */
  static public function insert(string $table, array $raw_values)
  {
    $query = self::insert_query($table, $raw_values)
            . "SELECT * FROM $table WHERE id = LAST_INSERT_ID();";
    $res = self::$mysqli->query($query);

    if ($res === false)
    {
      throw new Exception('Query (' . $query . ') returns error: ' . self::$mysqli->error, 1);
    }

    return $res;
  }



  /**
   * Prepare query string for update query
   * @param string $table
   * @param array $raw_values values to be updated in record
   * @param array $condition - WHERE condition look syntax on select_query
   * @return string compiled query
   * @throws Exception if table name not set
   */
  static protected function update_query(string $table, array $raw_values, array $condition): string
  {
    if (!strlen($table) || !count($raw_values))
    {
      throw new Exception("Nothing to update");
    }

    $where = self::escape_condition($condition);


    $values = [];

    foreach ($raw_values as $key => $value)
    {
      $key = '`' . self::escape($key) . '`';
      $value = is_null($value) ? 'DEFAULT' : '\'' . self::escape($value) . '\'';
      $values[] = $key . ' = ' . $value;
    }

    $values = implode(',', $values);

    return "UPDATE $table($keys) SET $values $where;";
  }



  /**
   * Update record and return updated records via MySQL query result
   * @param string $table
   * @param array $raw_values values to be updated in record
   * @param array $condition - WHERE condition look syntax on select_query
   * @return bool true if success
   * @throws Exception
   */
  static public function update(string $table, array $raw_values, array $condition)
  {
    $query = self::update_query($table, $raw_values, $condition)
            . "SELECT * FROM $table WHERE id = LAST_INSERT_ID();";

    $res = self::$mysqli->query($query);

    if ($res === false)
    {
      throw new Exception('Query (' . $query . ') returns error: ' . self::$mysqli->error, 1);
    }

    return $res;
  }



  /**
   * Update record without return updated records
   * @param string $table
   * @param array $raw_values values to be updated in record
   * @param array $condition - WHERE condition look syntax on select_query
   * @return bool true if success
   * @throws Exception
   */
  static public function update_exec(string $table, array $raw_values, array $condition): bool
  {
    $query = self::update_query($table, $raw_values, $condition);
    $res = self::$mysqli->query($query);

    if ($res === false)
    {
      throw new Exception('Query (' . $query . ') returns error: ' . self::$mysqli->error, 1);
    }

    return $res;
  }

}

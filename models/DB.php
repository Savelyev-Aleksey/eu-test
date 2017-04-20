<?php

class DB
{
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


  /*
    condition must set as array('filed={val-key}', array('{val-key}' => $value))
    set only unique key in query - see doc strtr() more info.
  */
  private static function escape_condition($condition)
  {
    $where = '';
    if (is_array($condition) && count($condition) == 2)
    {
      $where = $condition[0];
      $values = $condition[1];
      foreach ($values as &$val)
      {
        if (is_string($val))
          $val = '"'. self::$mysqli->real_escape_string($val). '"';
        elseif (!is_numeric($val)) {
          throw new Exception("Values in query must be only strings or numbers. ", 1);
        }
      }
      unset($val);
      $where = 'WHERE ' .strtr($where, $values);
    }
    elseif (!is_null($condition)) {
      throw new Exception("Query condition is set incorrect. ", 1);
    }
    return $where;
  }



  private static function escape_fields($fields)
  {
    if (is_array($fields))
    {
      foreach($fields as &$f)
      {
        $f = self::$mysqli->real_escape_string($f);
      }
      unset($f);
      $fields = '"'. implode('","', $fields) .'"';
    }
    elseif (is_string($fields))
    {
      if ($fields !== '*')
        $fields = self::$mysqli->real_escape_string($fields);
    }
    else
    {
        throw new Exception("fields must array or one string field, or *", 1);
    }
    return $fields;
  }



  /*
    get database select by mysqli and return result
    condition must set as array('filed={val-key}', array('{val-key}' => $value))
    set only unique key in query - see doc strtr() more info.
  */
  static function select($table, $condition = NULL, $fields = '*', $order = NULL, $limit = 1000)
  {
    self::init();

    // 1) Safe table
    $table = self::escape_table($table);

    // 2) Safe condition
    $where = self::escape_condition($condition);

    // 3) fields safe set
    $fields = self::escape_fields($fields);

    // 4 safe order
    if (!is_null($order))
      $order = 'ORDER BY '. self::$mysqli->real_escape_string($order);

    // 5 safe limit
    if (!(is_int($limit) && $limit > 0))
      $limit = 1000;

    $limit = 'LIMIT '. $limit;

    $query = "SELECT $fields FROM $table $where $order $limit";
    $res = self::$mysqli->query($query);

    if ($res === false)
      throw new Exception("Query was incorrect. ". $query, 1);

    return $res;
  }

}

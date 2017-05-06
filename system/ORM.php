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

class ORM
{
  // cache of class fields from database
  protected static $columns = NULL;
  protected static $table_name = NULL;
  protected static $class_name = NULL;
  protected $_values = array();
  protected $_loaded = false;



  function __construct($values)
  {

    self::init();
    // Work with params obj init with values
    if (is_array($values))
    {
      $this->set_values($values);
    }
  }



  static function init()
  {
    // Naming convention to many items
    if (is_null(self::$table_name))
    {
      self::$class_name = get_called_class();
      self::$table_name = strtolower(self::$class_name) . 's';
    }

    if (is_null(self::$columns))
      self::$columns = DB::show_columns(self::$table_name);
  }


  // Alias for DB select but add current table name
  protected static function select($condition = NULL, $fields = '*', $order = NULL,
    $limit = NULL)
  {
    self::init();
    return DB::select(self::$table_name, $condition, $fields, $order, $limit);
  }


  // ORM basic function dynamically add options from DB by DB fileds naming or manually
  public function __set($name, $value)
  {
    if (in_array($name, self::$columns))
    {
      $this->_values[$name] = $value;
    }
  }


// ORM basic function dynamically get object options by DB fileds naming
  public function __get($name)
  {
    if (array_key_exists($name, $this->_values))
    {
      return $this->_values[$name];
    }

    $trace = debug_backtrace();
    trigger_error(
      'ORM undefined option __get(): ' . $name .' file ' . $trace[0]['file'] .
      ' line ' . $trace[0]['line']. PHP_EOL,
      E_USER_NOTICE);
    return null;
  }


  // Add each field as option of object
  private function set_values(&$data)
  {
    if (!count($data))
      return false;

    foreach ($data as $key => $value)
    {
      // invoke __set method
      $this->$key = $value;
    }
  }


  // Find in DB object by id (PHP 7)
  public static function find(int $id)
  {
    self::init();
    $where = array('id={id}', array('{id}' => $id));
    $res = $this->select($where, '*', 'id ASC', 1);
    if (!($row = $res->fetch_assoc()))
    {
      throw new Exception("Object not found with id = $id", 1);
    }
    $user = new self::$class_name($row);
    $this->_loaded = true;
    $res->free();
    return $user;
  }


  // Find some records in DB, return array of ORM objects
  public static function where($condition, $order = 'id ASC', $limit = NULL)
  {
    $objects = array();
    $res = self::select($condition, '*', $order, $limit);
    while ($row = $res->fetch_assoc())
    {
      $obj = new self::$class_name($row);
      $obj->_loaded = true;
      $objects[] = $obj;
    }
    $res->free();
    return $objects;
  }

  public static function all($order = 'id ASC', $limit = NULL)
  {
      return self::where(NULL, $order);
  }

}

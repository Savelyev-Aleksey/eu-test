<?php

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
  protected static function select($condition = NULL, $fields = '*', $order = NULL, $limit = 1000)
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
  public static function where($condition, $order = 'id ASC', $limit = 1000)
  {
    self::init();
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
}

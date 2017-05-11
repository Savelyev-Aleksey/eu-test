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
 * @uses DB for connection with Database
 */
class ORM
{

  // cache of class fields from database
  protected static $columns = [];
  // Array of object properties - used in __get __set methods
  protected $_values = [];
  protected $_touched_values = [];
  // Marker for loaded from DB object
  protected $_loaded = false;
  protected $_valid = false;
  protected $_last_error = NULL;



  /**
   * Return current in last static connection class name.
   * For example User
   * @return string
   */
  static public function class_name()
  {
    return get_called_class();
  }



  /**
   * Pluralized class name for table DB naming.
   * For example User -> users
   * @return string
   */
  static public function table_name()
  {
    return strtolower(get_called_class()) . 's';
  }



  /**
   * Initialize table fields names.
   * They are store in local cache for speed up access.
   */
  static function init()
  {
    $class_name = self::class_name();
    $table_name = self::table_name();
    // Naming convention to many items
    if (!array_key_exists($class_name, self::$columns))
    {
      self::$columns[$class_name] = DB::show_columns($table_name);
    }
  }



  function __construct($values)
  {
    self::init();
    // Work with params obj init with values
    if (is_array($values))
    {
      $this->set_values($values);
    }
  }



  /**
   * Alias for DB select with added current table name
   * @param type $condition
   * @param type $fields
   * @param type $order
   * @param type $limit
   * @return MySQLi query result object
   */
  protected static function select($condition = NULL, $fields = '*', $order = NULL, $limit = NULL)
  {
    self::init();
    return DB::select(self::table_name(), $condition, $fields, $order, $limit);
  }



  /**
   * Try to insert newly created object in DB.
   * Setup ORM::_last_error to error string.
   * @return bool true if stored in DB false if fails.
   */
  protected function insert(): bool
  {
    self::init();
    $result = DB::insert(self::table_name(), $this->_values);
    if ($result === false)
    {
      $this->_last_error = 'Error: ' . DB::get_error() . ' in query '
              . DB::get_query_error();
      return false;
    }
    $this->_last_error = NULL;

    if ($result > 0)
    {
      $this->id = $result;
    }
    $this->_loaded = true;
    return true;
  }



  protected function update(): bool
  {
    self::init();
    if (!count($this->_touched_values))
    {
      return true;
    }
    $values = [];
    foreach ($this->_touched_values as $key)
    {
      if ($key == 'id')
      {
        continue;
      }
      $values[$key] = $this->_values[$key];
    }

    $where = ['`id`={id}', ['{id}' => (int) $this->id]];

    $result = DB::update(self::table_name(), $values, $where);
    if ($result === false)
    {
      $this->_last_error = 'Error: ' . DB::get_error() . ' in query '
              . DB::get_query_error();
      return false;
    }
    $this->_last_error = NULL;

    $this->_touched_values = [];
    $this->_loaded = true;
    return true;
  }



  /**
   * ORM basic function dynamically set object property value
   * @param string $name object property name
   * @param mixed $value - DB compatible value for store object
   */
  public function __set(string $name, $value)
  {
    if (!in_array($name, self::$columns[self::class_name()]))
    {
      throw new Exception("Column $name not exist in object");
    }

    if (array_key_exists($name, $this->_values) && $this->_values[$name] == $value)
    {
      return;
    }
    $this->_values[$name] = $value;
    if (!in_array($name, $this->_touched_values))
    {
      $this->_touched_values[] = $name;
    }
  }



  /**
   * ORM basic function dynamically get object options by DB fields naming.
   * @param string $name for get object property
   * @return mixed object property value
   * @throws Exception if property not exist (look by DB fields)
   */
  public function __get(string $name)
  {
    if (!in_array($name, self::$columns[self::class_name()]))
    {
      throw new Exception("Field ($name) not set in object");
    }

    if (array_key_exists($name, $this->_values))
    {
      return $this->_values[$name];
    }
    return NULL;
  }



  /**
   *
   * @param array $data associative array from DB to setup object values
   * @return false if data if empty
   */
  private function set_values(array $data)
  {
    if (!count($data))
    {
      return false;
    }

    foreach ($data as $key => $value)
    {
      // invoke __set() method
      $this->$key = $value;
    }
    return true;
  }



  /**
   * Find one record of object by id.
   * @param int $id
   * @return object record
   * @throws Exception if not loaded
   */
  public static function find(int $id)
  {
    $init = self::init();
    $where = array('id={id}', array('{id}' => $id));
    $res = self::select($where, '*', NULL, 1);
    if ($res === false)
    {
      throw new Exception('Query occur error: ' . DB::get_error()
      . ' in query ' . DB::get_query_error());
    }
    if (!($values = $res->fetch_assoc()))
    {
      throw new Exception("Object not found with id = $id", 1);
    }
    $class_name = self::class_name();
    $obj = new $class_name($values);
    $obj->_touched_values = [];
    $obj->_loaded = true;
    $res->free();
    return $obj;
  }



  /**
   * @param array $values associative array of values to be set in object
   */
  public function values(array $values = [])
  {
    if ($this->_loaded && array_key_exists('id', $values))
    {
      unset($values['id']);
    }

    foreach ($values as $key => $val)
    {
      $this->$key = $val;
    }
  }



  /**
   * Find some records in DB, return array of ORM objects
   * @param array $condition ["where_query",[associative_key=>value,...]]
   * example ["id={user_id}", ['user_id' => 25]]
   * @param string $order look Field.ASC, Field2.DESC ... etc
   * @param int $limit records limit
   * @return array of objects
   */
  public static function where($condition, $order = 'id.ASC', $limit = NULL)
  {
    $objects = [];
    $res = self::select($condition, '*', $order, $limit);
    $class_name = self::class_name();
    while ($row = $res->fetch_assoc())
    {
      $obj = new $class_name($row);
      $obj->_loaded = true;
      $objects[] = $obj;
    }
    $res->free();
    return $objects;
  }



  /**
   * Select all records where statement without any filter.
   * @param type $order
   * @param type $limit
   * @return array of ORM child objects
   */
  public static function all($order = NULL, $limit = NULL)
  {
    return self::where(NULL, $order, $limit);
  }



  /**
   * Returns array of objects thats references by has_many relation
   * @param string $ref_class child class name of object - from has_many array
   * @param mixed $arguments - additional parameters. if set where statements
   * they are concate "has_many_where and (user_where_query)"
   * @return array of Objects - look ORM::where for more info
   */
  private function where_has_many(string $ref_class, array $arguments)
  {
    // For example User -> user_id
    $filed_id = strtolower(self::class_name()) . '_id';
    // get additional where statemnt if set

    if (count($arguments))
    {
      $where_array = array_shift($arguments);
      $where = & $where_array[0];
      $where = "$filed_id=\{$filed_id\} and ($where)";
      // add value to values array user_id = 5
      $where_array[1]["\{$filed_id\}"] = $this->id;
    }
    else
    {
      $where_array = ["$filed_id=\{$filed_id\}", ["\{$filed_id\}" => $this->id]];
    }
    return $ref_class::where($where_array, ...$arguments);
  }



  /**
   * This method returns parent references Object
   * @param string $ref_class - belongs_to parent class
   * @return Object of parent relation class
   */
  private function where_belongs_to(string $ref_class)
  {
    // Example
    // Call Comment->user
    // static - Review
    // $ref_class = user
    // For example User -> user_id
    $filed_id = strtolower($ref_class) . '_id';
    $id = $this->{$filed_id};
    $where_array = ["id=\{$filed_id\}", ["\{$filed_id\}" => $id]];
    return $ref_class::where($where_array)[0];
  }



  /**
   *
   * @param string $table_name - name of class in plural (messages - example)
   * @param array $arguments - additional options, see ORM::where for params
   * @return Array of objects for has_many statement, and Object for belongs.
   * @throws Exception if relation not set
   */
  public function __call($table_name, $arguments)
  {
    // get class name without ended s - users -> user
    $class_name = substr($table_name, 0, -1);
    if (isset(static::$has_many))
    {
      foreach (static::$has_many as $search)
      {
        // Compare case insensentive User and user for example
        if (strcasecmp($search, $class_name) == 0)
        {
          return self::where_has_many($search, $arguments);
        }
      }
    }

    if (isset(static::$belongs_to))
    {
      foreach (static::$belongs_to as $search)
      {
        // belongs to compare in one plural - User, not Users
        if (strcasecmp($search, $table_name) == 0)
        {
          return self::where_belongs_to($search);
        }
      }
    }
    throw new Exception("Class $class_name is not in has_many or belongs_to references");
  }



  public function save(): bool
  {
    $this->_last_error = NULL;
    return $this->_loaded ? $this->update() : $this->insert();
  }



  public function get_error()
  {
    return $this->_last_error;
  }



  public function remove(): bool
  {
    if (!$this->_loaded)
    {
      $this->_last_error = 'Object not loaded yet.';
      return false;
    }

    $condition = ['`id`={id}', ['{id}' => $this->id]];
    $res = DB::delete(self::table_name(), $condition);

    if ($res === false)
    {
      $this->_last_error = 'Error: ' . DB::get_error() . ' in query '
              . DB::get_query_error();
      return false;
    }
    $this->_last_error = NULL;
    $this->_loaded = false;
    return true;
  }

}

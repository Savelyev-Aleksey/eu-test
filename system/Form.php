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
 * Helper static class to build form based on current object set in open.
 * Stores in open Form session current object and gets from values.
 *
 * To start build call Form::open(...);
 * Work with fields Form::input('comment', []);
 * To end session close form Form::close();
 *
 * @uses Session for gets csrf token
 */
class Form
{

  /**
   * @var mixed Object stored for open form session to gets values.
   */
  protected static $obj = NULL;



  /**
   * Glue options in form ( id="login" class="form-control").
   * Prepend space. So no need additional space.
   * @param array $options to implode
   * @return string ready attr string
   */
  protected static function glue(array $options)
  {
    $options_buf = '';
    foreach ($options as $key => $option)
    {
      $options_buf .= ' ' . $key . '="' . $option . '"';
    }
    return $options_buf;
  }



  public static function open($object, array $options = [])
  {
    if (!is_null(self::$obj))
    {
      throw new Exception('Last form not closed. Please check code.');
    }

    // Object not set, array of options given
    if (is_array($object))
    {
      $options = $object;
    }
    else
    {
      self::$obj = $object;
    }

    if (!array_key_exists('id', $options) && isset(self::$obj))
    {
      $options['id'] = strtr(self::$obj->class_name(), ['\\' => '-', '_' => '-']);
    }

    if (!array_key_exists('method', $options))
    {
      $options['method'] = 'get';
    }

    if (array_key_exists('multipart', $options))
    {
      if ($options['multipart'] === true)
      {
        $options['enctype'] = 'multipart/form-data';
      }
      unset($options['multipart']);
    }

    if (array_key_exists('action', $options))
    {
      $options['action'] = Request::uri($options['action']);
    }

    $attr = self::glue($options);

    $form = '<form' . $attr . '>' . PHP_EOL;

    // Add CSRF token
    if (strcasecmp($options['method'], 'post') === 0)
    {
      $form .= self::hidden(Session::token_key(), Session::get_token());
    }

    return $form;
  }



  public static function close()
  {
    self::$obj = NULL;
    return '</form>' . PHP_EOL;
  }



  public static function submit($text, array $options = [])
  {
    $attr = self::glue($options);
    return "<button type=\"submit\"$attr>$text</button>" . PHP_EOL;
  }



  public static function select($name, array $options = [], $selected = NULL, array $attributes = NULL)
  {
    if (!array_key_exists('id', $attributes))
    {
      $attributes['id'] = $name;
    }

    $attr = self::glue($attributes);

    $buf = "<select name=\"$name\"$attr>" . PHP_EOL;

    if (!is_array($selected))
    {
      if (is_null($selected))
      {
        $selected = isset(self::$obj) ? [self::$obj->$name] : [];
      }
      else
      {
        $selected = [(string) $selected];
      }
    }

    foreach ($options as $key => $value)
    {
      $sel_opt = in_array($value, $selected) ? ' selected' : '';
      if ($value === NULL)
      {
        $buf = $buf . '<option' . $sel_opt . '></option>' . PHP_EOL;
      }
      else
      {
        $buf = $buf . '<option' . $sel_opt . ' value="' . $value . '">' . $key . '</option>' . PHP_EOL;
      }
    }

    $buf .= '</select>' . PHP_EOL;

    return $buf;
  }



  public static function label($for, $title, array $options = [])
  {
    $options_buf = self::glue($options);
    return "<label for=\"$for\"$options_buf>$title</label>" . PHP_EOL;
  }



  public static function input($name, array $options = [])
  {
    if (!array_key_exists('id', $options))
    {
      $options['id'] = $name;
    }
    if (!array_key_exists('type', $options))
    {
      $options['type'] = 'text';
    }

    if (array_key_exists('placeholder', $options) && $options['placeholder'] === true)
    {
      $title = $name;
      $options['placeholder'] = ucfirst($title);
      unset($title);
    }

    if (array_key_exists('value', $options))
    {
      if (is_null($options['value']))
      {
        unset($options['value']);
      }
    }
    else
    {
      if (self::$obj === NULL)
      {
        throw new Exception('Object not set in form and value not given.');
      }
      $options['value'] = self::$obj->$name;
    }

    $attr = self::glue($options);

    return "<input name=\"$name\"$attr>" . PHP_EOL;
  }



  public static function hidden(string $name, $value = NULL, array $options = [])
  {
    if (!isset($value) && isset(self::$obj))
    {
      $options['type'] = 'hidden';
      return self::input($name, $options);
    }
    else
    {
      $options['value'] = $value;
    }

    $attr = self::glue($options);

    return "<input type=\"hidden\" name=\"$name\"$attr>" . PHP_EOL;
  }



  public static function password($name, array $options = [])
  {
    $options['type'] = 'password';
    $options['value'] = NULL; // clear value if data sent incorrect
    return self::input($name, $options);
  }



  public static function email($name, array $options = [])
  {
    $options['type'] = 'email';
    return self::input($name, $options);
  }



  public static function number($name, array $options = [])
  {
    $options['type'] = 'numeric';
    return self::input($name, $options);
  }



  public static function date($name, array $options = [])
  {
    $options['type'] = 'date';
    return self::input($name, $options);
  }



  public static function today($name, array $options = [])
  {
    $options['type'] = 'date';
    if (!array_key_exists('value', $options))
    {
      $val = isset(self::$obj) ? self::$obj->$name : NULL;
      $options['value'] = isset($val) ? $val : date('Y-m-d');
    }
    return self::input($name, $options);
  }



  public static function textarea($name, array $options = [])
  {
    if (array_key_exists('value', $options))
    {
      $val = $opions['value'];
      unset($opions['value']);
    }
    else
    {
      $val = isset(self::$obj) ? self::$obj->$name : NULL;
    }

    if (!array_key_exists('id', $options))
    {
      $opions['id'] = $name;
    }

    $attr = self::glue($options);

    return "<textarea name=\"$name\" $attr>$val</textarea>" . PHP_EOL;
  }

}

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
 * Static session wrapper for more comfortable using.
 * Non blocking Session class.
 * Can be used in ajax parallel requests.
 *
 */
class Session
{
  protected static $life_time = 60*60*24; // one day



  public static function get($key)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
      session_start(['cookie_lifetime' => self::$life_time]);
    }
    session_write_close();

    return $_SESSION[$key];
  }



  public static function set($key, $value)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
      session_start(['cookie_lifetime' => self::$life_time]);
    }
    $_SESSION[$key] = $value;
    session_write_close();
  }


  public static function destroy()
  {
    if (!session_status() == PHP_SESSION_ACTIVE)
    {
      session_start();
    }
    // close session
    // remove all session variables
    $_SESSION = [];
    session_write_close();
  }
}

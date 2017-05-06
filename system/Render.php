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
 * Description of Render
 *
 */
class Render
{
    private static $counter = 0;

    /**
     * Use class name and $view variable such path to file
     * @param string $view
     * @throws Exception if view file not found
     */
    static public function view($view, $vars = [])
    {
        self::$counter++;
        $model = strtolower(substr(get_called_class(), 11));

        $_content = self::get_include_contents($model.'/'.$view, $vars);

        if ($_content === false)
            throw Exception ("File not found by Render: /views/$model/$view");

        self::$counter--;

        // Load template after all loaded views
        // Pop on top level in stack
        if (!self::$counter && isset(self::$template))
        {
            include '/views/template/'. self::$template. '.php';
        }
    }



    /**
     * Return files contest in variable for example
     * @param string $filename relative path for view
     * @return file contest
     */
    protected static function get_include_contents($filename, $vars)
    {
        $file = realpath("views/$filename.php");
        if (is_file($file)) {
            ob_start();
            foreach ($vars as $key => $val)
            {
                $$key = $val;
            }

            include $file;
            $buf = ob_get_contents();
            ob_end_flush();
            return $buf;
        }
        return false;
    }

}

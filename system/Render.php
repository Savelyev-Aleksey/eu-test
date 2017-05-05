<?php

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

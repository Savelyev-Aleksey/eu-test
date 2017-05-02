<?php

/**
 * Description of Render
 *
 */
class Render
{
    protected static $content = NULL;
    private static $counter = 0;

    /**
     * Use class name and $view variable such path to file
     * @param string $view
     * @throws Exception if view file not found
     */
    static public function view($view)
    {
        self::$counter++;
        $model = strtolower(get_called_class());

        self::$content = self::get_include_contents($model.'/'.$view);

        if (self::$content === false)
            throw Exception ("File not found by Render: /views/$model/$view");

        self::$counter--;

        // Load template after all loaded views
        // Pop on top level in stack
        if (!self::$counter && isset(self::$template))
        {
            include '/template/'. self::$template. '.php';
        }
    }



    /**
     * Return files contest in variable for example
     * @param string $filename relative path for view
     * @return file contest
     */
    protected static function get_include_contents($filename)
    {
        if (is_file($filename)) {
            ob_start(NULL, 0, PHP_OUTPUT_HANDLER_CLEANABLE);
            include "/views/$filename.php";
            return ob_get_clean();
        }
        return false;
    }

}

<?php

/**
 * Description of Router
 * This class manage routing for app.
 * It's stores all available routes for app.
 * And call matched control class / action.
 *
 */
class Router
{

    // Matches a URI group and captures the contents
    const REGEX_GROUP   = '\(((?:(?>[^()]+)|(?R))*)\)';

    // Defines the pattern of a <segment>
    const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';

    // What can be part of a <segment> value
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    // What must be escaped in the route regex
    const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';


    static protected $_get = array();
    static protected $_post = array();
    static protected $_routes = array();
    static protected $_params = array();

        /**
     * Pushes new route to Router - routes matching by historical added order.
     * @param type $path added to router by type '<controller>(/<action>(/<id>))'
     * @param type $filter array(
     *           'controller' => '[a-z]+',
     *           'id' => '\d+',
     *         ) for example
     * @param type $defaults associative array for params controller and action
     */
    static public function add_router($path, $filter = NULL, $defaults = NULL)
    {
        $reg_str = self::compile($path, $filter);
        self::$_routes[] = array($reg_str, $defaults);
    }


    static public function param($key)
    {
        return key_exists($key, self::$_params) ? self::$_params[$key] : NULL;
    }


    /**
     * Escape input from all global arrays $_GET, $_POST
     * @param type $value
     * @return type
     */
    static public function input_filter($value)
    {
        $v = trim($value);
        return htmlspecialchars($v);
    }


    /**
     * Find route for requested url.
     * Gets all params from string for controller params exclude $_GET options.
     * Camel case controller name by normalize_name function
     * @param type $uri
     * @throws Exception if route sets incorrect
     * @redirect 404 if no one route matched
     */
    static protected function find_requested_path($uri)
    {
        $found = false;
        $path = NULL;
        $defaults = NULL;
        foreach (self::$_routes as $route)
        {
            $path = $route[0];
            $defaults = $route[1];

            $found = preg_match($path, $uri, $matches);
            if ($found === false)
                throw new Exception ("Route is incorrect, check path: $path");
            if ($found === 1)
                break;
        }

        if (!$found)
            Router::redirect('public/404.php');

        $params = array();

        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                // Skip all unnamed keys
                continue;
            }

            // Set the value for all matched keys
            $params[$key] = $value;
        }

        foreach ($defaults as $key => $value) {
            if (!isset($params[$key]) OR $params[$key] === '') {
                // Set default values for any key that was not matched
                $params[$key] = $value;
            }
        }

        if (!array_key_exists('controller', $params))
            throw new Exception("Not set controller for path: $uri");

        $params['controller'] = self::normalize_name($params['controller']);

        // Set default action if not exist
        if (!array_key_exists('action', $params))
            $params['action'] = 'index';

        self::$_params = &$params;
        return true;
    }



    /**
     * Perform lower-case class name to Camel_Case underscore string
     */
    static public function normalize_name($name)
    {
        $names = explode('-', $name);
        foreach ($names as &$value)
        {
            ucwords($value);
        }
        unset($value);
        return implode('_', $names);
    }

    /**
     * basic init Router method
     * filter $_GET, $_POST params and store safely in Router arrays
     */
    static public function runner()
    {
        if (count($_POST))
            foreach ($_POST as $key => $value)
            {
                $key = self::input_filter($key);
                self::$_post[$key] = self::input_filter($value);
            }

        if (count($_GET))
            foreach ($_GET as $key => $value)
            {
                $key = self::input_filter($key);
                self::$_get[$key] = self::input_filter($value);
            }

        $path = self::input_filter($_SERVER['PATH_INFO']);

        $path = strtolower($path);

        // Split to class name and action
        $path = split('/', $path);

        self::find_requested_path($path);

        $controller_name = self::$_params['controller'];
        $action_name = self::$_params['action'];

        $controller = new $controller_name();

        $controller::$action_name();
    }

    /**
     * Returns the compiled regular expression for the route. This translates
     * keys and optional groups to a proper PCRE regular expression.
     *
     *     $compiled = Route::compile(
     *        '<controller>(/<action>(/<id>))',
     *         array(
     *           'controller' => '[a-z]+',
     *           'id' => '\d+',
     *         )
     *     );
     *
     * @return  string
     * @uses    Route::REGEX_ESCAPE
     * @uses    Route::REGEX_SEGMENT
     */
    protected static function compile($uri, array $regex = NULL)
    {
        // The URI should be considered literal except for keys and optional parts
        // Escape everything preg_quote would escape except for : ( ) < >
        $expression = preg_replace('#' . Route::REGEX_ESCAPE . '#', '\\\\$0', $uri);

        if (strpos($expression, '(') !== FALSE)
        {
            // Make optional parts of the URI non-capturing and optional
            $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
        }

        // Insert default regex for keys
        $expression = str_replace(array('<', '>'), array('(?<', '>' . Route::REGEX_SEGMENT . ')'), $expression);

        if ($regex)
        {
            $search = $replace = array();
            foreach ($regex as $key => $value)
            {
                $search[] = "<$key>" . Route::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            // Replace the default regex with the user-specified regex
            $expression = str_replace($search, $replace, $expression);
        }

        return '#^' . $expression . '$#uD';
    }


    public static function redirect($rel_path)
    {
        $http = $_SERVER['HTTPS'] ? 'https' : 'http';
        $server = $_SERVER['SERVER_NAME'];
        header("Location: $http://$server/$rel_path");
    }

}

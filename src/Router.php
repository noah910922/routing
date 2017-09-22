<?php
namespace DevLibs\Routing;

class Router
{
    const METHOD_DELETE = 'DELETE';

    const METHOD_GET = 'GET';

    const METHOD_HEAD = 'HEAD';

    const METHOD_OPTIONS = 'OPTIONS';

    const METHOD_PATCH = 'PATCH';

    const METHOD_POST = 'POST';

    const METHOD_PUT = 'PUT';

    /**
     * @var array mapping from group prefix to group router.
     */
    private $groups = [];

    /**
     * @var array a set of route.
     */
    private $routes = [];

    /**
     * @var array extra data for extending.
     */
    private $settings = [];

    /**
     * @var int a trick for dispatch and handle a request.
     * @see handle()
     * @see dispatch()
     */
    private $routesNextIndex = 1;

    /**
     * @var array a set of route's patterns.
     */
    private $patterns = [];

    /**
     * @var null|string a combined pattern of all patterns.
     */
    private $combinedPattern;

    /**
     * @var mixed
     * @see handle()
     */
    public static $replacePatterns = [
        '~<([^:]+)>~',
        '~<([^:]+):([^>]+)>?~',
        '~/$~'
    ];

    /**
     * @var mixed
     * @see handle()
     */
    public static $replacements = [
        '([^/]+)',
        '($2)',
        ''
    ];

    /**
     * @var string the name of route class which implements RouteInterface
     */
    private static $routeClass = Route::class;

    /**
     * @param string $class
     */
    public static function setRouteClass($class)
    {
        static::$routeClass = $class;
    }

    /**
     * Router constructor.
     *
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Create a group router.
     *
     * @param string $prefix the group router's prefix should not contains '/'.
     * @param array $settings
     * @return static a instance of group router
     *
     * For example:
     *     $router = new Router();
     *     $groupBackend = $router->group('admin');
     *     $groupBackend->get('/', 'backend panel');
     *
     *     It will matches "/admin"
     */
    public function group($prefix, array $settings = [])
    {
        // inherit it's parent's settings.
        $router = new static(array_merge_recursive($this->settings, $settings));
        $this->groups[$prefix] = $router;
        return $this->groups[$prefix];
    }

    /**
     * Register a handler for handling the specific request which
     * relevant to the given method and path.
     *
     * @param string|array $method request method
     * Please convert method to uppercase(recommended) or lowercase, since it is case sensitive.
     *
     * Examples:
     *     method              validity
     *     "GET"               valid(recommended)
     *     "GET|POST"          valid(recommended)
     *     "GET,POST"          invalid
     *     ['GET', 'POST']     valid
     *
     * @param string $path the regular expression.
     * Param pattern should be one of "<name>" and "<name:regex>", in default, it will be converted to "([^/]+)" and "(regex)" respectively.
     * The path will format by @see $replacePatterns and @see $replacements, you can change it in need.
     * @param mixed $handler request handler.
     * @param null|array $settings extra data for extending.
     *
     * Examples:
     *     path                              matched
     *     "/users"                           "/users"
     *     "/users/<id:\d+>"                  "/users/123"
     *     "/users/<id:\d+>/posts"            "/users/123/posts"
     *     "/users/<id:\d+>/posts/<post>"     "/users/123/posts/456", "/users/123/posts/post-title"
     */
    public function handle($method, $path, $handler, $settings = null)
    {
        if (is_array($method)) {
            $method = implode('|', $method);
        }

        // format path to regular expression.
        $pattern = preg_replace(static::$replacePatterns, static::$replacements, $path);
        // store pattern
        $this->patterns[$this->routesNextIndex] = "({$method})\\ {$pattern}(/?)";

        // collect param's name.
        preg_match_all('/<([^:]+)(:[^>]+)?>/', $path, $matches);
        $params = empty($matches[1]) ? [] : $matches[1];
        $this->routes[$this->routesNextIndex] = [$handler, $params, $settings];

        // calculate the next index of routes.
        $this->routesNextIndex += count($params) + 2;

        // set combinedPattern as null when routes has been changed.
        $this->combinedPattern = null;
    }

    /**
     * A shortcut for registering a handler to handle GET request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param null|array $setting
     */
    public function get($path, $handler, $setting = null)
    {
        $this->handle(self::METHOD_GET, $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle DELETE request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param null|array $setting
     */
    public function delete($path, $handler, $setting = null)
    {
        $this->handle(self::METHOD_DELETE, $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle PATCH request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param null|array $setting
     */
    public function patch($path, $handler, $setting = null)
    {
        $this->handle(self::METHOD_PATCH, $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle POST request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param null|array $setting
     */
    public function post($path, $handler, $setting = null)
    {
        $this->handle(self::METHOD_POST, $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle PUT request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param null|array $setting
     */
    public function put($path, $handler, $setting = null)
    {
        $this->handle(self::METHOD_PUT, $path, $handler, $setting);
    }

    /**
     * @param string $method request method
     * @param string $path request URL without the query string. It's first character should not be "/".
     * @return mixed|null if matched, returns a route instance which implements RouteInterface, otherwise null will be returned.
     */
    public function dispatch($method, $path)
    {
        // look for group router via the prefix.
        if ($path != '' && $path != '/' && count($this->groups) > 0) {
            $start = ($path[0] == '/') ? 1 : 0;
            if (false !== $pos = strpos($path, '/', $start)) {
                $len = $pos + 1 - $start - (($path[$pos] == '/') ? 1 : 0);
                $prefix = substr($path, $start, $len);
            } else {
                $prefix = substr($path, $start);
            }
            if (isset($this->groups[$prefix])) {
                // dispatch recursive.
                $group = $this->groups[$prefix];
                $path = substr($path, strlen($prefix) + $start);
                return $group->dispatch($method, $path);
            }
        }

        return $this->dispatchInternal($method, $path, $this->settings);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $settings router's setting.
     * @return null|RouteInterface
     */
    private function dispatchInternal($method, $path, $settings)
    {
        if ($this->combinedPattern === null) {
            if (empty($this->patterns)) {
                return null;
            }
            $this->combinedPattern = "~^(?:" . implode("|", $this->patterns) . ")$~x";
        }

        $path = "{$method} {$path}";
        if (preg_match($this->combinedPattern, $path, $matches)) {
            // retrieves route
            for ($i = 1; $i < count($matches) && $matches[$i] === ''; ++$i) ;
            $route = $this->routes[$i];

            // create a route instance which implements RouterInterface
            /**
             * @var RouteInterface $instance
             */
            $instance = new static::$routeClass;
            $instance->setHandler($route[0]);

            // fills up param's value
            $params = [];
            foreach ($route[1] as $param) {
                $params[$param] = $matches[++$i];
            }
            $instance->setParams($params);

            // merges group's settings
            $instance->setSettings(is_array($route[2]) ? array_merge_recursive($settings, $route[2]) : $settings);

            // determines whether the path is end with slash
            $instance->setIsEndWithSlash($matches[++$i] == '/');

            return $instance;
        }

        return null;
    }

    public function getAllowMethods($path)
    {
        if ($this->combinedPattern === null) {
            if (empty($this->patterns)) {
                return null;
            }
            $this->combinedPattern = "~^(?:" . implode("|", $this->patterns) . ")$~x";
        }
        $path = "([GET|POST]) {$path}";
        var_dump(preg_match_all($this->combinedPattern, $path, $matches));
        var_dump($this->combinedPattern);
        return $matches;
    }
}
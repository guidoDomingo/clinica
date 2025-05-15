<?php
namespace Api\Core;

/**
 * Router Class
 * 
 * Handles API routing and dispatches requests to the appropriate controllers
 */
class Router
{
    /**
     * @var array Routes configuration
     */
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Add a GET route
     * 
     * @param string $uri The route URI
     * @param string $controller The controller class
     * @param string $method The controller method
     * @return void
     */
    public function get($uri, $controller, $method)
    {
        $this->addRoute('GET', $uri, $controller, $method);
    }

    public function map($method, $uri, $controller, $action)
    {
        $this->addRoute($method, $uri, $controller, $action);
    }

    /**
     * Add a POST route
     * 
     * @param string $uri The route URI
     * @param string $controller The controller class
     * @param string $method The controller method
     * @return void
     */
    public function post($uri, $controller, $method)
    {
        $this->addRoute('POST', $uri, $controller, $method);
    }

    /**
     * Add a PUT route
     * 
     * @param string $uri The route URI
     * @param string $controller The controller class
     * @param string $method The controller method
     * @return void
     */
    public function put($uri, $controller, $method)
    {
        $this->addRoute('PUT', $uri, $controller, $method);
    }

    /**
     * Add a DELETE route
     * 
     * @param string $uri The route URI
     * @param string $controller The controller class
     * @param string $method The controller method
     * @return void
     */
    public function delete($uri, $controller, $method)
    {
        $this->addRoute('DELETE', $uri, $controller, $method);
    }

    /**
     * Add a route to the routes array
     * 
     * @param string $method HTTP method
     * @param string $uri The route URI
     * @param string $controller The controller class
     * @param string $action The controller method
     * @return void
     */
    // private function addRoute($method, $uri, $controller, $action)
    // {
    //     $this->routes[$method][$uri] = [
    //         'controller' => $controller,
    //         'action' => $action
    //     ];
    // }

    private function addRoute($method, $uri, $controller, $action)
    {
        $uri = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^\/]+)', $uri);
        $this->routes[$method][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Dispatch the request to the appropriate controller
     * 
     * @param string $uri The request URI
     * @param string $method The request method
     * @return void
     * @throws \Exception If the route is not found
     */
    public function dispatch($uri, $method)
    {
        // Iterate through registered routes to find a match
        foreach ($this->routes[$method] as $route => $handler) {            // Convert route parameters like {code} to regex capture groups
            $routeRegex = preg_replace('/{([a-zA-Z0-9_]+)}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $routeRegex . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                // Filter out numeric keys (full matches) and keep only named parameters
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                
                $controller = $handler['controller'];
                $action = $handler['action'];

                // Create controller instance
                if (!class_exists($controller)) {
                    throw new \Exception("Controller {$controller} not found", 500);
                }
                $controllerInstance = new $controller();

                // Check method exists
                if (!method_exists($controllerInstance, $action)) {
                    throw new \Exception("Method {$action} not found in controller {$controller}", 500);
                }                // Call controller method with parameters
                // If we have a parameter with key 'code', extract it and pass it directly
                if (isset($params['code'])) {
                    return $controllerInstance->$action($params['code']);
                }
                // Otherwise pass the parameters as before
                return empty($params) ? $controllerInstance->$action() : $controllerInstance->$action($params);
            }
        }

        throw new \Exception('Route not found', 404);

        // Check if the controller exists
        if (!class_exists($controller)) {
            throw new \Exception("Controller {$controller} not found", 500);
        }

        // Create an instance of the controller
        $controllerInstance = new $controller();

        // Check if the method exists
        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Method {$action} not found in controller {$controller}", 500);
        }

        // Call the controller method
        return $controllerInstance->$action();
    }


}
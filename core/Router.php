<?php

/**
 * User: Philip Heimböck
 * Date: 23.10.14
 * Time: 12:10
 */
class Router
{

    protected $service_loader;

    function __construct()
    {
        $this->service_loader = new ServiceLoader();
    }

    public function getServiceRoute($http_method, $uri)
    {
        return $http_method . ':' . $uri;
    }


    public function getService($service_route, $params)
    {
        $map = $this->service_loader->loadPaths();

        if (array_key_exists($service_route, $map)) {
            $tmp = $map[$service_route];

            /** @var ReflectionMethod $method */
            $method = $tmp[0];
            $service = $tmp[1];
            $template = $tmp[2];
            $default_params = $tmp[3];

            // Set params
            foreach ($params as $key => $value) {
                $default_params[$key] = $value;
            }

            return $this->invoke_method($method, $service, $template, $default_params);
        }
        return false;
    }

    private function invoke_method(ReflectionMethod $method, $service, $template, $params)
    {
        // Invoke the method
        $result = $method->invokeArgs($service, $params);

        // Todo Implement Pipes to manipulate data

        // Extract the returned arrays to variables
        // This way the template ca use those variables
        if (is_array($result)) {
            foreach ($result as $key => $data) {
                ${$key} = $data;
            }
        }

        ob_start();
        include $template;
        return ob_get_clean();
    }
} 
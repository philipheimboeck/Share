<?php

/**
 * User: Philip Heimböck
 * Date: 23.10.14
 * Time: 12:10
 */

namespace core;

use ReflectionMethod;

class Router
{

    protected $service_loader;

    function __construct()
    {
        $this->service_loader = new ClassLoader();
    }

    public function getControllerRoute($http_method, $uri)
    {
        return $http_method . ':' . $uri;
    }


    public function callController($controller_route, $params)
    {
        $map = $this->service_loader->loadPaths();

        if (array_key_exists($controller_route, $map)) {
            $tmp = $map[$controller_route];

            /** @var ReflectionMethod $method */
            $method = $tmp[0];
            $service = $tmp[1];
            $template = $tmp[2];
            $default_params = $tmp[3];

            // Set params
            foreach ($params as $key => $value) {
                $default_params[$key] = $value;
            }

            return $this->invoke_controller($method, $service, $template, $default_params);
        }
        return false;
    }

    private function invoke_controller(ReflectionMethod $method, $service, $template, $params)
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
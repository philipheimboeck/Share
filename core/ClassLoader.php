<?php

/**
 * User: Philip Heimböck
 * Date: 20.10.14
 * Time: 20:10
 */

namespace core;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

class ClassLoader
{

    // Todo create cache
    // Todo define pathes in config file
    const API_PATH = "./src/";

    /**
     * Returns all service paths
     *
     * @return array
     */
    public function loadPaths()
    {
        $api_instances = $this->loadApiInstances();

        $controller_map = array();
        $services = array();

        // First get Service instances
        foreach ($api_instances as $instance) {
            $reflection = new ReflectionClass($instance);
            $doc_comment = $reflection->getDocComment();

            if (preg_match('/.*@Service\(\)/', $doc_comment)) {
                $services[$reflection->getName()] = $instance;
            }
        }

        // Get Controllers
        foreach ($api_instances as $instance) {
            $reflection = new ReflectionClass($instance);
            $doc_comment = $reflection->getDocComment();

            // Inject Service instances
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property_comment = $property->getDocComment();
                $property_matches = array();
                if (preg_match('/.*@Service\([\"\'](.*)[\"\']\)/', $property_comment, $property_matches)) {
                    $needed_service = $property_matches[1];
                    if (array_key_exists($needed_service, $services)) {
                        $setter = 'set' . ucfirst($property->getName());
                        $instance->{$setter}($services[$needed_service]);
                    }
                }
            }

            // Get Controller methods
            $matches = array();
            if (preg_match('/.*@Path\([\"\'](.*)[\"\']\)/', $doc_comment, $matches)) {
                // Get service path
                $path = $matches[1];
                // Remove ending slashes
                $path = rtrim($path, '/');

                // Add methods
                foreach ($reflection->getMethods() as $method) {
                    $method_comment = $method->getDocComment();

                    $method_matches = array();
                    if (preg_match('/.*@Method\("(Get)|(Post)"\).*/', $method_comment, $method_matches)) {
                        $http_method = null;
                        if ($method_matches[1] === 'Get') {
                            $http_method = 'GET';
                        } elseif ($method_matches[2] === 'Post') {
                            $http_method = 'POST';
                        }

                        // Get method path
                        $method_path = "";
                        $method_matches = array();
                        if (preg_match('/.*@Path\([\"\'](.*)[\"\']\)/', $method_comment, $method_matches)) {
                            $method_path = $method_matches[1];
                        }
                        // Ensure leading slash
                        if (strpos($method_path, '/') !== 0) $method_path = '/' . $method_path;

                        // Get method template
                        $template = 'bootstrap.php';
                        $method_matches = array();
                        if (preg_match('/.*@Template\([\"\'](.*)[\"\']\)/', $method_comment, $method_matches)) {
                            $template = $method_matches[1];
                        }

                        // Get method params
                        $params = array();
                        foreach ($method->getParameters() as $param) {
                            $params[$param->getName()] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                        }

                        $controller_map[$http_method . ':' . $path . $method_path] = array($method, $instance, $template, $params);
                    }
                }
            }
        }

        return $controller_map;
    }

    /**
     * Loads all Controller classes (contained in api) dynamically
     */
    private function loadApiInstances()
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::API_PATH, FilesystemIterator::SKIP_DOTS));

        $instances = array();

        /**
         * @var String $filename
         * @var SplFileInfo $file
         */
        foreach ($iterator as $filename => $file) {
            if ($file->getExtension() === 'php') {
                require $file->getPathname();
            }

            // Get classes from file
            $file_content = file_get_contents($file->getPathname());
            $classes = $this->get_php_classes($file_content);

            foreach ($classes as $class) {
                $reflection_class = new ReflectionClass($class);
                $instances[] = $reflection_class->newInstance();
            }
        }

        return $instances;
    }

    private function get_php_classes($php_code)
    {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);

        $namespace = "";
        // Find classes
        for ($i = 2; $i < $count; $i++) {
            // Get namespace
            if ($tokens[$i - 2][0] === T_NAMESPACE
                && $tokens[$i - 1][0] === T_WHITESPACE) {
                $namespace = "";
                while($tokens[$i] !== ';') {
                    if ( $tokens[$i][0] === T_STRING || $tokens[$i][1] === '\\')
                        $namespace .= $tokens[$i][1];
                    $i++;
                }
            }
            // Get token
            elseif ($tokens[$i - 2][0] === T_CLASS
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) {
                $class_name = $tokens[$i][1];
                $classes[] = $namespace . '\\' . $class_name;
            }
        }
        return $classes;
    }
}





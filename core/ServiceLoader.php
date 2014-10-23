<?php

/**
 * User: Philip Heimböck
 * Date: 20.10.14
 * Time: 20:10
 */
class ServiceLoader
{

    // Todo create cache
    // Todo define pathes in config file

    /**
     * Returns all service paths
     *
     * @return array
     */
    public function loadPaths()
    {
        $services = $this->loadServices();
        $map = array();

        /**
         * @var  $filename
         * @var SplFileInfo $file
         */
        foreach ($services as $service) {

            $reflection = new ReflectionClass($service);
            $doc_comment = $reflection->getDocComment();

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
                        $template = 'index.php';
                        $method_matches = array();
                        if (preg_match('/.*@Template\([\"\'](.*)[\"\']\)/', $method_comment, $method_matches)) {
                            $template = $method_matches[1];
                        }

                        // Get method params
                        $params = array();
                        foreach ($method->getParameters() as $param) {
                            $params[$param->getName()] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                        }

                        $map[$http_method . ':' . $path . $method_path] = array($method, $service, $template, $params);
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Loads all Service classes (contained in api) dynamically
     */
    private function loadServices()
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . "/../api/", FilesystemIterator::SKIP_DOTS));

        /**
         * @var String $filename
         * @var SplFileInfo $file
         */
        foreach ($iterator as $filename => $file) {
            if ($file->getExtension() === 'php') {
                require $file->getPathname();
            }
        }

        // Todo Create instances dynamically
        $services = array();
        $services[] = new Service();

        return $services;
    }


}
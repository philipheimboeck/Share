<?php
/**
 * User: Philip Heimböck
 * Date: 16.10.14
 * Time: 19:02
 */

require 'core/ServiceLoader.php';
require 'core/Router.php';

$router = new Router();

$service_route = $router->getServiceRoute($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
$result = $router->getService($service_route, $_REQUEST);
if ( $result ) {
    echo $result;
}
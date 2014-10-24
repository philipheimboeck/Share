<?php
/**
 * User: Philip Heimböck
 * Date: 16.10.14
 * Time: 19:02
 */

use core\Router;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once 'vendor/autoload.php';


require_once 'core/ClassLoader.php';
require_once 'core/Router.php';

/* Doctrine */

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
// or if you prefer yaml or XML
//$config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
//$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

// database configuration parameters
$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);

// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);



/* Simple Framework */

// Handle Request
if ( $_REQUEST ) {
    $router = new Router();

    $service_route = $router->getControllerRoute($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    $result = $router->callController($service_route, $_REQUEST);
    if ( $result ) {
        echo $result;
    }
    else {
        header("HTTP/1.0 404 Not Found");
        echo "404";
    }
}



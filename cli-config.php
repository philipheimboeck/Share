<?php
/**
 * User: Philip Heimböck
 * Date: 24.10.14
 * Time: 10:14
 */
require_once "bootstrap.php";

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
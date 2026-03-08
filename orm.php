<?php
// bootstrap.php
// Include Composer Autoload (relative to project root).
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once __DIR__ . '/vendor/autoload.php';

$paths = array($_SERVER['DOCUMENT_ROOT'] . '/admin/Entity');
$isDevMode = true;

// the connection configuration
$dbParams = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'user'     => 'wellnesstrade',
    'password' => 'Wellnesstrade2510',
    'dbname'   => 'wellnesstrade',
);

$config = ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$em = EntityManager::create($dbParams, $config);
/*


$paths = array(__DIR__ . '/entities');
$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths);

# set up configuration parameters for doctrine.
# Make sure you have installed the php7.0-sqlite package.
$connectionParams = array(
    'driver' => 'pdo_sqlite',
    'path'   => __DIR__ . '/data/my-database.db',
);

$entityManager = \Doctrine\ORM\EntityManager::create($connectionParams, $config);
*/



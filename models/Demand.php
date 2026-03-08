<?php
// bootstrap.php
// Include Composer Autoload (relative to project root).
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

$paths = array("/path/to/entity-files");
$isDevMode = false;

// the connection configuration
$dbParams = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'user'     => 'wellnesstrade',
    'password' => 'Wellnesstrade2510',
    'dbname'   => 'wellnesstrade',
);


/*
'driver'         => 'pdo_pgsql',
    'user'           => 'user1',
    'password'       => 'my-awesome-password',
    'host'           => 'postgresql.mydomain.com',
    'port'           => 5432,
    'dbname'         => 'myDbName',
    'charset'        => 'UTF-8',
*/

//const DB_HOST = 'localhost';

$config = ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);
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

/*


/**
 * @Entity
 * @Table(name="demands")
 */
class Demand
{
    /**
     * @Id
     * @Column(type="integer")
     */
    private $id;

    /** @Column(length=100) */
    private $user_name;

    /*

    public function __construct(string $name, sring $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
    */


    # Accessors
    public function getId() : int { return $this->id; }
    public function getName() : string { return $this->user_name; }

}


$users = $entityManager->getRepository("Demand")->findAll();

print "Users: " . print_r($users, true) . PHP_EOL;



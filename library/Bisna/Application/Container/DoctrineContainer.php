<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Bisna\Application\Container;

use Bisna\Application\Exception;

/**
 * Doctrine Container class.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link www.doctrine-project.org
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class DoctrineContainer
{
    /**
     * @var string Default DBAL Connection name. 
     */
    public $defaultConnection = 'default';

    /**
     * @var default Default Cache Instance name.
     */
    public $defaultCacheInstance = 'default';

    /**
     * @var string Default ORM EntityManager name.
     */
    public $defaultEntityManager = 'default';

    /**
     * @var array Doctrine Context configuration.
     */
    private $configuration = array();

    /**
     * @var array Available DBAL Connections.
     */
    private $connections = array();

    /**
     * @var array Available Cache Instances.
     */
    private $cacheInstances = array();

    /**
     * @var array Available ORM EntityManagers.
     */
    private $entityManagers = array();

    
    /**
     * Constructor.
     *
     * @param array $config Doctrine Container configuration
     */
    public function __construct(array $config = array())
    {
        // Defining DBAL configuration
        $dbalConfig = $this->prepareDBALConfiguration($config);

        // Defining default DBAL Connection name
        $this->defaultConnection = $dbalConfig['defaultConnection'];

        // Defining Cache configuration
        $cacheConfig = array();

        if (isset($config['cache'])) {
            $cacheConfig = $this->prepareCacheInstanceConfiguration($config);

            // Defining default Cache instance
            $this->defaultCacheInstance = $cacheConfig['defaultCacheInstance'];
        }

        // Defining ORM configuration
        $ormConfig = array();

        if (isset($config['orm'])) {
            $ormConfig  = $this->prepareORMConfiguration($config);

            // Defining default ORM EntityManager
            $this->defaultEntityManager = $ormConfig['defaultEntityManager'];
        }

        // Defining Doctrine Context configuration
        $this->configuration = array(
            'dbal'  => $dbalConfig['connections'],
            'cache' => $cacheConfig['instances'],
            'orm'   => $ormConfig['entityManagers']
        );
    }

    /**
     * Prepare DBAL Connections configurations.
     *
     * @param array $config Doctrine Container configuration
     *
     * @return array
     */
    private function prepareDBALConfiguration(array $config = array())
    {
        $dbalConfig = $config['dbal'];
        $defaultConnectionName = isset($dbalConfig['defaultConnection'])
            ? $dbalConfig['defaultConnection'] : $this->defaultConnection;

        unset($dbalConfig['defaultConnection']);

        $defaultConnection = array(
            'eventManagerClass' => 'Doctrine\Common\EventManager',
            'eventSubscribers'   => array(),
            'configurationClass' => 'Doctrine\DBAL\Configuration',
            'sqlLoggerClass'    => null,
            'parameters'          => array(
                'wrapperClass'       => null,
                'driver'              => 'pdo_mysql',
                'host'                => 'localhost',
                'user'                => 'root',
                'password'            => null,
                'port'                => null,
                'driverOptions'       => array()
            )
        );

        $connections = array();

        if (isset($dbalConfig['connections'])) {
            $configConnections = $dbalConfig['connections'];

            foreach ($configConnections as $name => $connection) {
                $name = isset($connection['id']) ? $connection['id'] : $name;
                $connections[$name] = array_replace_recursive($defaultConnection, $connection);
            }
        } else {
            $connections = array(
                $defaultConnectionName => array_replace_recursive($defaultConnection, $dbalConfig)
            );
        }

        return array(
            'defaultConnection' => $defaultConnectionName,
            'connections'       => $connections
        );
    }

    /**
     * Prepare Cache Instances configurations.
     *
     * @param array $config Doctrine Container configuration
     *
     * @return array
     */
    private function prepareCacheInstanceConfiguration(array $config = array())
    {
        $cacheConfig = $config['cache'];
        $defaultCacheInstanceName = isset($cacheConfig['defaultCacheInstance'])
            ? $cacheConfig['defaultCacheInstance'] : $this->defaultCacheInstance;

        unset($cacheConfig['defaultCacheInstance']);
            
        $defaultCacheInstance = array(
            'adapterClass' => 'Doctrine\Common\Cache\ArrayCache',
            'namespace'    => '',
            'options'      => array()
        );

        $instances = array();

        if (isset($cacheConfig['instances'])) {
            $configInstances = $cacheConfig['instances'];

            foreach ($configInstances as $name => $instance) {
                $name = isset($instance['id']) ? $instance['id'] : $name;
                $instances[$name] = array_replace_recursive($defaultCacheInstance, $instance);
            }
        } else {
            $instances = array(
                $defaultCacheInstanceName => array_replace_recursive($defaultCacheInstance, $cacheConfig)
            );
        }

        return array(
            'defaultCacheInstance' => $defaultCacheInstanceName,
            'instances'            => $instances
        );
    }

    /**
     * Prepare ORM EntityManagers configurations.
     *
     * @param array $config Doctrine Container configuration
     *
     * @return array
     */
    private function prepareORMConfiguration(array $config = array())
    {
        $ormConfig = $config['orm'];
        $defaultEntityManagerName = isset($ormConfig['defaultEntityManager'])
            ? $ormConfig['defaultEntityManager'] : $this->defaultEntityManager;

        unset($ormConfig['defaultEntityManager']);

        $defaultEntityManager = array(
            'entityManagerClass'      => 'Doctrine\ORM\EntityManager',
            'configurationClass'      => 'Doctrine\ORM\Configuration',
            'entityNamespaces'        => array(),
            'connection'              => $this->defaultConnection,
            'proxy'                   => array(
                'autoGenerateClasses' => true,
                'namespace'           => 'Proxy',
                'dir'                 => APPLICATION_PATH . '/../library/Proxy'
            ),
            'queryCache'              => $this->defaultCacheInstance,
            'resultCache'             => $this->defaultCacheInstance,
            'metadataCache'           => $this->defaultCacheInstance,
            'metadataDrivers'         => array(),
            'DQLFunctions'            => array(
                'numeric'             => array(),
                'datetime'            => array(),
                'string'              => array()
            )
        );

        $entityManagers = array();

        if (isset($ormConfig['entityManagers'])) {
            $configEntityManagers = $ormConfig['entityManagers'];

            foreach ($configEntityManagers as $name => $entityManager) {
                $name = isset($entityManager['id']) ? $entityManager['id'] : $name;
                $entityManagers[$name] = array_replace_recursive($defaultEntityManager, $entityManager);
            }
        } else {
            $entityManagers = array(
                $this->defaultConnection => array_replace_recursive($defaultEntityManager, $ormConfig)
            );
        }

        return array(
            'defaultEntityManager' => $defaultEntityManagerName,
            'entityManagers'       => $entityManagers
        );
    }

    /**
     * Retrieve DBAL Connection based on its name. If no argument is provided,
     * it will attempt to get the default Connection.
     * If DBAL Connection name could not be found, NameNotFoundException is thrown.
     *
     * @throws Bisna\Application\Exception\NameNotFoundException
     *
     * @param string $connName Optional DBAL Connection name
     *
     * @return Doctrine\DBAL\Connection DBAL Connection
     */
    public function getConnection($connName = null)
    {
        $connName = $connName ?: $this->defaultConnection;

        // Check if DBAL Connection has not yet been initialized
        if ( ! isset($this->connections[$connName])) {
            // Check if DBAL Connection is configured
            if ( ! isset($this->configuration['dbal'][$connName])) {
                throw new Exception\NameNotFoundException("Unable to find Doctrine DBAL Connection '{$connName}'.");
            }

            $this->connections[$connName] = $this->startDBALConnection($this->configuration['dbal'][$connName]);

            unset($this->configuration['dbal'][$connName]);
        }

        return $this->connections[$connName];
    }

    /**
     * Retrieve Cache Instance based on its name. If no argument is provided,
     * it will attempt to get the default Instance.
     * If Cache Instance name could not be found, NameNotFoundException is thrown.
     *
     * @throws Bisna\Application\Exception\NameNotFoundException
     *
     * @param string $cacheName Optional Cache Instance name
     *
     * @return Doctrine\Common\Cache\Cache Cache Instance
     */
    public function getCacheInstance($cacheName = null)
    {
        $cacheName = $cacheName ?: $this->defaultCacheInstance;

        // Check if Cache Instance has not yet been initialized
        if ( ! isset($this->cacheInstances[$cacheName])) {
            // Check if Cache Instance is configured
            if ( ! isset($this->configuration['cache'][$cacheName])) {
                throw new Exception\NameNotFoundException("Unable to find Doctrine Cache Instance '{$cacheName}'.");
            }

            $this->cacheInstances[$cacheName] = $this->startCacheInstance($this->configuration['cache'][$cacheName]);

            unset($this->configuration['cache'][$cacheName]);
        }

        return $this->cacheInstances[$cacheName];
    }

    /**
     * Retrieve ORM EntityManager based on its name. If no argument provided,
     * it will attempt to get the default EntityManager.
     * If ORM EntityManager name could not be found, NameNotFoundException is thrown.
     *
     * @throws Bisna\Application\Exception\NameNotFoundException
     *
     * @param string $emName Optional ORM EntityManager name
     *
     * @return Doctrine\ORM\EntityManager ORM EntityManager
     */
    public function getEntityManager($emName = null)
    {
        $emName = $emName ?: $this->defaultEntityManager;

        // Check if ORM Entity Manager has not yet been initialized
        if ( ! isset($this->entityManagers[$emName])) {
            // Check if ORM EntityManager is configured
            if ( ! isset($this->configuration['orm'][$emName])) {
                throw new Exception\NameNotFoundException("Unable to find Doctrine ORM EntityManager '{$emName}'.");
            }

            $this->entityManagers[$emName] = $this->startORMEntityManager($this->configuration['orm'][$emName]);

            unset($this->configuration['orm'][$emName]);
        }
        
        return $this->entityManagers[$emName];
    }

    /**
     * Initialize the DBAL Connection.
     *
     * @param array $config DBAL Connection configuration.
     *
     * @return Doctrine\DBAL\Connection
     */
    private function startDBALConnection(array $config = array())
    {
        return \Doctrine\DBAL\DriverManager::getConnection(
            $config['parameters'],
            $this->startDBALConfiguration($config),
            $this->startDBALEventManager($config)
        );
    }

    /**
     * Initialize the DBAL Configuration.
     *
     * @param array $config DBAL Connection configuration.
     *
     * @return Doctrine\DBAL\Configuration
     */
    private function startDBALConfiguration(array $config = array())
    {
        $configClass = $config['configurationClass'];
        $configuration = new $configClass();

        // SQL Logger configuration
        if ( ! empty($config['sqlLoggerClass'])) {
            $sqlLoggerClass = $config['sqlLoggerClass'];
            $configuration->setSQLLogger(new $sqlLoggerClass());
        }

        return $configuration;
    }

    /**
     * Initialize the EventManager.
     *
     * @param array $config DBAL Connection configuration.
     *
     * @return Doctrine\Common\EventManager
     */
    private function startDBALEventManager(array $config = array())
    {
        $eventManagerClass = $config['eventManagerClass'];
        $eventManager = new $eventManagerClass();

        // Event Subscribers configuration
        foreach ($config['eventSubscribers'] as $subscriber) {
            $eventManager->addEventSubscriber(new $subscriber());
        }

        return $eventManager;
    }

    /**
     * Initialize Cache Instance.
     *
     * @param array $config Cache Instance configuration.
     *
     * @return Doctrine\Common\Cache\Cache
     */
    private function startCacheInstance(array $config = array())
    {
        $adapterClass = $config['adapterClass'];
        $adapter = new $adapterClass();

        // Define namespace for cache
        if (isset($config['namespace']) && ! empty($config['namespace'])) {
            $adapter->setNamespace($config['namespace']);
        }

        if (method_exists($adapter, 'initialize')) {
            $adapter->initialize($config);
        } else if ($adapter instanceof \Doctrine\Common\Cache\MemcacheCache) {
            // Prevent stupid PHP error of missing extension (if other driver is being used)
            $memcacheClassName = 'Memcache';
            $memcache = new $memcacheClassName();

            // Default server configuration
            $defaultServer = array(
                'host'          => 'localhost',
                'port'          => 11211,
                'persistent'    => true,
                'weight'        => 1,
                'timeout'       => 1,
                'retryInterval' => 15,
                'status'        => true
            );

            if (isset($config['options']['servers'])) {
                foreach ($config['options']['servers'] as $server) {
                    $server = array_replace_recursive($defaultServer, $server);

                    $memcache->addServer(
                        $server['host'],
                        $server['port'],
                        $server['persistent'],
                        $server['weight'],
                        $server['timeout'],
                        $server['retryInterval'],
                        $server['status']
                    );
                }
            }

            $adapter->setMemcache($memcache);
        }

        return $adapter;
    }

    /**
     * Initialize ORM EntityManager.
     *
     * @param array $config ORM EntityManager configuration.
     *
     * @return Doctrine\ORM\EntityManager
     */
    private function startORMEntityManager(array $config = array())
    {
        return \Doctrine\ORM\EntityManager::create(
            $this->getConnection($config['connection']),
            $this->startORMConfiguration($config)
        );
    }

    /**
     * Initialize ORM Configuration.
     *
     * @param array $config ORM EntityManager configuration.
     *
     * @return Doctrine\ORM\Configuration
     */
    private function startORMConfiguration(array $config = array())
    {
        $configClass = $config['configurationClass'];
        $configuration = new $configClass();

        $configuration = new \Doctrine\ORM\Configuration();

        // Entity Namespaces configuration
        foreach ($config['entityNamespaces'] as $alias => $namespace) {
            $configuration->addEntityNamespace($alias, $namespace);
        }

        // Proxy configuration
        $configuration->setAutoGenerateProxyClasses(
            ! in_array($config['proxy']['autoGenerateClasses'], array("0", "false", false))
        );
        $configuration->setProxyNamespace($config['proxy']['namespace']);
        $configuration->setProxyDir($config['proxy']['dir']);

        // Cache configuration
        $configuration->setMetadataCacheImpl($this->getCacheInstance($config['metadataCache']));
        $configuration->setResultCacheImpl($this->getCacheInstance($config['resultCache']));
        $configuration->setQueryCacheImpl($this->getCacheInstance($config['queryCache']));

        // Metadata configuration
        $configuration->setMetadataDriverImpl($this->startORMMetadata($config['metadataDrivers']));

        // DQL Functions configuration
        $dqlFunctions = $config['DQLFunctions'];

        foreach ($dqlFunctions['datetime'] as $name => $className) {
            $configuration->addCustomDatetimeFunction($name, $className);
        }

        foreach ($dqlFunctions['numeric'] as $name => $className) {
            $configuration->addCustomNumericFunction($name, $className);
        }

        foreach ($dqlFunctions['string'] as $name => $className) {
            $configuration->addCustomStringFunction($name, $className);
        }

        return $configuration;
    }

    /**
     * Initialize ORM Metadata drivers.
     *
     * @param array $config ORM Mapping drivers.
     *
     * @return Doctrine\ORM\Mapping\Driver\DriverChain
     */
    private function startORMMetadata(array $config = array())
    {
        $metadataDriver = new \Doctrine\ORM\Mapping\Driver\DriverChain();

        // Default metadata driver configuration
        $defaultMetadataDriver = array(
            'adapterClass'               => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'mappingNamespace'           => '',
            'mappingDirs'                => array(),
            'annotationReaderClass'      => 'Doctrine\Common\Annotations\AnnotationReader',
            'annotationReaderCache'      => $this->defaultCacheInstance,
            'annotationReaderNamespaces' => array()
        );

        foreach ($config as $driver) {
            $driver = array_replace_recursive($defaultMetadataDriver, $driver);
            
            $reflClass = new \ReflectionClass($driver['adapterClass']);
            $nestedDriver = null;

            if (
                $reflClass->getName() == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver' ||
                $reflClass->isSubclassOf('Doctrine\ORM\Mapping\Driver\AnnotationDriver')
            ) {
                $annotationReaderClass = $driver['annotationReaderClass'];
                $annotationReader = new $annotationReaderClass($this->getCacheInstance($driver['annotationReaderCache']));
                $annotationReader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');

                foreach ($driver['annotationReaderNamespaces'] as $alias => $namespace) {
                    $annotationReader->setAnnotationNamespaceAlias($namespace, $alias);
                }

                $nestedDriver = $reflClass->newInstance($annotationReader, $driver['mappingDirs']);
            } else {
                $nestedDriver = $reflClass->newInstance($driver['mappingDirs']);
            }
            
            $metadataDriver->addDriver($nestedDriver, $driver['mappingNamespace']);
        }

        if (($drivers = $metadataDriver->getDrivers()) && count($drivers) == 1) {
            reset($drivers);
            $metadataDriver = $drivers[key($drivers)];
        }

        return $metadataDriver;
    }
}

# Zend Framework 1.X + Doctrine 2.X integration

ZF1-D2 is an integration tool to allow you use Doctrine 2 at the top of Zend Framework 1.

## Installation

Install ZF1-D2 using [Composer](http://getcomposer.org)

### Create your composer.json file

      {
          "require": {
              "chriswoodford/ZendFramework1-Doctrine2": "master-dev"
          },
          "minimum-stability": "dev"
      }

### Download composer into your application root

      $ curl -s http://getcomposer.org/installer | php

### Install your dependencies

      $ php composer.phar install

## Configuring

Doctrine 2 requires different parts of configuration.

- Cache
- DBAL
- ORM

### Configuring Namespaces

Since parts of Doctrine rely on specific commit pointers of individual Doctrine packages, a class loader is required to allow a customized configuration of Namespaces.
One good example is default Doctrine GIT clone, which points to Doctrine\Common and Doctrine\DBAL packages through git submodules.
To address this different paths issue, Bisna provides an specific class laoder configuration, which allows you to correclty map your environment.
Here is an example of configuration:

    ; Doctrine Common ClassLoader class and file
    resources.doctrine.classLoader.loaderClass = "Doctrine\Common\ClassLoader"
    resources.doctrine.classLoader.loaderFile  = APPLICATION_PATH "/../library/vendor/Doctrine/lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php"

    ; Namespace loader for Doctrine\Common
    resources.doctrine.classLoader.loaders.doctrine_common.namespace   = "Doctrine\Common"
    resources.doctrine.classLoader.loaders.doctrine_common.includePath = APPLICATION_PATH "/../library/vendor/Doctrine/lib/vendor/doctrine-common/lib"

    ; Namespace loader for Doctrine\DBAL
    resources.doctrine.classLoader.loaders.doctrine_dbal.namespace   = "Doctrine\DBAL"
    resources.doctrine.classLoader.loaders.doctrine_dbal.includePath = APPLICATION_PATH "/../library/vendor/Doctrine/lib/vendor/doctrine-dbal/lib"

    ; Namespace loader for Doctrine\ORM
    resources.doctrine.classLoader.loaders.doctrine_orm.namespace   = "Doctrine\ORM"
    resources.doctrine.classLoader.loaders.doctrine_orm.includePath = APPLICATION_PATH "/../library/vendor/Doctrine/lib"

    ; Namespace loader for Symfony\Component\Console
    resources.doctrine.classLoader.loaders.symfony_console.namespace   = "Symfony\Component\Console"
    resources.doctrine.classLoader.loaders.symfony_console.includePath = APPLICATION_PATH "/../library/vendor/Doctrine/lib/vendor"

    ; Namespace loader for Symfony\Component\Yaml
    resources.doctrine.classLoader.loaders.symfony_yaml.namespace   = "Symfony\Component\Yaml"
    resources.doctrine.classLoader.loaders.symfony_yaml.includePath = APPLICATION_PATH "/../library/vendor/Doctrine/lib/vendor"


### Configuring Cache

Currently, Doctrine 2 allows you to use the following different Cache drivers:

- APC
- Array
- Memcache
- Xcache

You are allowed to define multiple cache connections. If you attempt to retrieve a cache instance without a name, it will attempt to grab the defaultCacheInstance value as key. The default name of default cache instance is "default". To change it, you can simply alter the default name with the following line:

	resources.doctrine.cache.defaultCacheInstance = my_cache_instance

All cache instances have a name. Based on this name you are able to correctly configure your cache instance.
Here is a good example of a Memcache driver with multiple servers being used.

	; Cache Instance configuration for "default" cache
	resources.doctrine.cache.instances.default.adapterClass = "Doctrine\Common\Cache\MemcacheCache"
	resources.doctrine.cache.instances.default.namespace    = "MyApplication_"

	; Server configuration (index "0")
	resources.doctrine.cache.instances.default.options.servers.0.host = localhost
	resources.doctrine.cache.instances.default.options.servers.0.port = 11211
	resources.doctrine.cache.instances.default.options.servers.0.persistent    = true
	resources.doctrine.cache.instances.default.options.servers.0.retryInterval = 15

	; Server configuration (index "1")
	resources.doctrine.cache.instances.default.options.servers.1.host = localhost
	resources.doctrine.cache.instances.default.options.servers.1.port = 11211
	resources.doctrine.cache.instances.default.options.servers.1.persistent    = true
	resources.doctrine.cache.instances.default.options.servers.1.weight        = 1
	resources.doctrine.cache.instances.default.options.servers.1.timeout       = 1
	resources.doctrine.cache.instances.default.options.servers.1.retryInterval = 15
	resources.doctrine.cache.instances.default.options.servers.1.status        = true

### Configuring DBAL

Doctrine DataBase Abstraction Layer follows the same idea of Cache drivers.

	; Points to default connection to be used. Optional if only one connection is defined
	resources.doctrine.dbal.defaultConnection = default

	; DBAL Connection configuration for "default" connection
	;resources.doctrine.dbal.connections.default.id = default
	;resources.doctrine.dbal.connections.default.eventManagerClass  = "Doctrine\Common\EventManager"
	;resources.doctrine.dbal.connections.default.eventSubscribers[] = "DoctrineExtensions\Sluggable\SluggableSubscriber"
	;resources.doctrine.dbal.connections.default.configurationClass = "Doctrine\DBAL\Configuration"
	;resources.doctrine.dbal.connections.default.sqlLoggerClass     = "Doctrine\DBAL\Logging\EchoSQLLogger"

	; Database configuration
	;resources.doctrine.dbal.connections.default.parameters.wrapperClass = ""
	resources.doctrine.dbal.connections.default.parameters.driver   = "pdo_mysql"
	resources.doctrine.dbal.connections.default.parameters.dbname   = "fmm"
	resources.doctrine.dbal.connections.default.parameters.host = "localhost"
	resources.doctrine.dbal.connections.default.parameters.port = 3306
	resources.doctrine.dbal.connections.default.parameters.user = "root"
	resources.doctrine.dbal.connections.default.parameters.password = "password"
	;resources.doctrine.dbal.connections.default.parameters.driverOptions.ATTR_USE_BUFFERED_QUERIES = true

### Configuring ORM

TBD

## Using

### Accessing Doctrine Container

It is strongly recommended to encapsulate the default Zend_Controller_Action class into our project's one.
By using this encapsulation, it allows you to include your own support without having to hack default Zend implementation.

A very rudimentary implementation of a possible base class is here:


	<?php

	namespace Bisna\Controller;

	/**
	 * Action class.
	 *
	 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
	 */
	class Action extends \Zend_Controller_Action
	{
	    /**
	     * Retrieve the Doctrine Container.
	     *
	     * @return Bisna\Doctrine\Container
	     */
	    public function getDoctrineContainer()
	    {
	        return $this->getInvokeArg('bootstrap')->getResource('doctrine');
	    }		
	}

### Doctrine Container API

The following API exposes all available Doctrine Container methods to be used by developers:

	<?php

	namespace Bisna\Application\Container;

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
	    public function getCacheInstance($cacheName = null);

	    /**
	     * Retrieve DBAL Connection based on its name. If no argument is provided,
	     * it will attempt to get the default Connection.
	     * If DBAL Connection could not be retrieved, NameNotFoundException is thrown.
	     *
	     * @throws Bisna\Application\Exception\NameNotFoundException
	     *
	     * @param string $connName Optional DBAL Connection name
	     *
	     * @return Doctrine\DBAL\Connection DBAL Connection
	     */
	    public function getConnection($connName = null);

	    /**
	     * Retrieve ORM EntityManager based on its name. If no argument provided,
	     * it will attempt to get the default EntityManager.
	     * If ORM EntityManager could not be retrieved, NameNotFoundException is thrown.
	     *
	     * @throws Bisna\Application\Exception\NameNotFoundException
	     *
	     * @param string $emName Optional ORM EntityManager name
	     *
	     * @return Doctrine\ORM\EntityManager ORM EntityManager
	     */
	    public function getEntityManager($emName = null);
	}

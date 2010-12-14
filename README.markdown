# Zend Framework 1.X + Doctrine 2.X integration

ZF1D2 is an integration tool to allow you use Doctrine 2 at the top of Zend Framework 1.

## Configuring

Doctrine 2 requires 2 different parts of configuration.

- Cache
- DBAL
- ORM

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

TBD

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
	     * @return Bisna\Application\Container\DoctrineContainer
	     */
	    public function getDoctrineContainer()
	    {
	        return $this->getInvokeArg('bootstrap')->getResource('doctrine');
	    }		
	}

### Doctrine Container API

The following API exposes all available Doctrine Container methods to be used by developers:

	<?php

	namespace Core\Application\Container;

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

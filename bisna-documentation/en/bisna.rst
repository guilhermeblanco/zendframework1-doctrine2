.. include:: ./include/html-entities.txt

Bisna: Zend Framework 1.X + Doctrine 2.X integration
====================================================

Guilherme Blanco's `Bisna package <https://github.com/guilhermeblanco/ZendFramework1-Doctrine2>`_ integrates `Doctrine 2 <http://www.doctrine-project.org/projects/orm>`_ and `Zend Framework 1 <http://framework.zend.com/>`_ using a custom Zend Framework application resource plug: ``Bisna\Application\Resource\Doctrine``.

Since configuration settings are stored in ``application.ini``, the developer can easily switch between development and production environments. ``Bisna\Application\Resource\Doctrine`` creates the :ref:`doctrine-container` instance, which it stores in the Zend registry. This is the class you will retrieve
and use in your action controllers. It is described in the :ref:`container-api` section below.

Configuring the Bisna Application Resource
------------------------------------------

The ``Bisna\Application\Resource\Doctrine`` class is a custom Zend Framework application resource plugin that parses the ``resources.doctrine`` sections of the ``application/configs/application.ini`` that are used to configure **Doctrine 2**.

As the sample Bisna ``application.ini`` shows, you will first need to add these lines to your ``application.ini`` file, so Zend Framework can find find and load the plugin:

.. code-block:: ini

    ; ----------------------------------------
    ; Zend Framework Application Configuration
    ; ----------------------------------------
    
    pluginPaths.Bisna\Application\Resource\ = "Bisna/Application/Resource"
    
    autoloaderNamespaces[] = Bisna

These lines were directly excerpted from the ``applicationi.ini`` in the `Bisna package <https://github.com/guilhermeblanco/ZendFramework1-Doctrine2>`_.

.. important:: 
  
   You will, of course, have to configure autoloading for your entites by, for example, adding ``autoloaderNamespaces[] = MyProject`` to application.ini.

Configuring Doctrine 2 
----------------------

There are four main Bisna application resource configuration sections:

* Class Loader
* Cache
* DBAL
* ORM

Configuring Namespaces
~~~~~~~~~~~~~~~~~~~~~~

To autoload the **Doctrine** namespace classes, you can simply add this line to your ``application.ini``

.. code-block:: ini

   autoloaderNamespaces[] = Doctrine

Zend Framework's autoloader will now automatically load all classes in the Doctrine namespace (and all its sub\ |ndash|\ namespaces).

.. warning::

   This assumes, of course, that Doctrine is in your PHP ``include_path``.

If you need to more fully customize autoloading, this is discussed below.

Autoloading for the **Symfony** namespace classes\ |mdash|\ used by the Doctrine command line tool\ |mdash|\ needs to be configured separately. This is true, for example,
if you installed Doctrine using pear

.. code-block:: bash

   # pear install -a DoctrineORM

because, then, the Symfony components are installed under the Doctrine directory, in ``Doctrine\Symfony``. The Zend autoloader cannot handle such an arrangement, and you must
therefore separately configure Symfony autoloading. This can be done either in 1.) the ``application/Bootstrap.php``

.. code-block:: php

   <?php
    class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
    {
        public function _initAutoloaderNamespaces()
        {
            require_once 'Doctrine/Common/ClassLoader.php';

            $autoloader = \Zend_Loader_Autoloader::getInstance();   
            $symfonyAutoloader = new \Doctrine\Common\ClassLoader('Symfony', 'Doctrine');
            $autoloader->pushAutoloader(array($symfonyAutoloader, 'loadClass'), 'Symfony');
        }
    }

or in 2.) the ``resources.doctrine.classloader`` section\ |mdash|\ more fully described below\ |mdash|\ by adding these lines:

.. code-block:: ini

    ; ------------------------------------------------------------------------------
    ; Doctrine Class Loader Configuration
    ; ------------------------------------------------------------------------------
    
    resources.doctrine.classLoader.loaderClass = "Doctrine\Common\ClassLoader"
    resources.doctrine.classLoader.loaderFile  = "Doctrine/Common/ClassLoader.php"
    
    resources.doctrine.classLoader.loaders.symfony_console.namespace   = "Symfony"
    resources.doctrine.classLoader.loaders.symfony_console.includePath = "Doctrine" 

.. important::

   Again, this assumes, that Doctrine is in your PHP ``include_path`` and its Symfony components are in ``Doctrine/Symfony``.

You can individually configure the include path for the following **Doctrine 2** namespaces

* Doctrine\\Common
* Doctrine\\DBAL
* Doctrine\\ORM
* Symfony\\Component\\Console
* Symfony\\Component\\Yaml

using the ``includePath`` and ``namespace`` entries shown below. In addition, you can also specify the class name and location of the autoloader. These lines, excerpted directly from
the ``application.ini`` in the Bisna package, illustrate how:

.. code-block:: ini

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

Configuring the Cache
~~~~~~~~~~~~~~~~~~~~~

You can define multiple cache connections. Currently, Doctrine 2 supports the following Cache drivers:

* APC
* Array
* Memcache
* Xcache

If you attempt to retrieve a cache instance without a name, Bisna will attempt to grab the ``resources.doctrine.cache.defaultCacheInstance`` cache,
whose default name is ``default`` To change the name of the default cache instance, alter this line: 

.. code-block:: ini

	resources.doctrine.cache.defaultCacheInstance = my_cache_instance

All cache instances must have a name; here, for example, the **Memcache** driver is being used with multiple servers:

.. code-block:: ini

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

Configuring the Database Abstraction Layer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Like the cache configuration, you can configure multiple database connections. The default connection is specified using ``resources.doctrine.dbal.defaultConnection`` 

.. code-block:: ini

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

Configuring ORM
~~~~~~~~~~~~~~~

The **ORM** configuration section is fairly self\ |ndash|\ explanatory. ``resources.doctrine.orm.defaultEntityManager`` is optional. It
is the name of the default entity manager.

.. code-block:: ini

    ; ------------------------------------------------------------------------------
    ; Doctrine ORM Configuration
    ; ------------------------------------------------------------------------------
    
    ; Points to default EntityManager to be used. Optional if only one EntityManager is defined
    resources.doctrine.orm.defaultEntityManager = default
    
The configuration for the each named entity manager is given in the ``resources.doctrine.orm.entityManagers.<name>`` settings. Below is the
configuration for our default entity manager:

.. code-block:: ini

    ; EntityManager configuration for "default" manager
    ;resources.doctrine.orm.entityManagers.default.id = default
    ;resources.doctrine.orm.entityManagers.default.entityManagerClass   = "Doctrine\ORM\EntityManager"
    ;resources.doctrine.orm.entityManagers.default.configurationClass   = "Doctrine\ORM\Configuration"

    resources.doctrine.orm.entityManagers.default.entityNamespaces.app = "Square\Entity"
    resources.doctrine.orm.entityManagers.default.connection     = default
    resources.doctrine.orm.entityManagers.default.proxy.autoGenerateClasses = true
    resources.doctrine.orm.entityManagers.default.proxy.namespace           = "Square\Entity\Proxy"
    resources.doctrine.orm.entityManagers.default.proxy.dir                 = APPLICATION_PATH "/../library/Square/Entity/Proxy"

    ;resources.doctrine.orm.entityManagers.default.metadataCache = default
    ;resources.doctrine.orm.entityManagers.default.queryCache    = default
    ;resources.doctrine.orm.entityManagers.default.resultCache   = default
    ;resources.doctrine.orm.entityManagers.default.DQLFunctions.numeric.PI = "DoctrineExtensions\ORM\Query\Functions\Numeric\PiFunction"

    resources.doctrine.orm.entityManagers.default.metadataDrivers.annotationRegistry.annotationFiles[]     = APPLICATION_PATH "/../library/vendors/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php"

    ;resources.doctrine.orm.entityManagers.default.metadataDrivers.annotationRegistry.annotationNamespaces.0.namespace   = "Gedmo"
    ;resources.doctrine.orm.entityManagers.default.metadataDrivers.annotationRegistry.annotationNamespaces.0.includePath = APPLICATION_PATH "/../library/vendors"

    resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.adapterClass          = "Doctrine\ORM\Mapping\Driver\AnnotationDriver"
    resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.mappingNamespace      = "Square\Entity"
    resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.mappingDirs[]         = APPLICATION_PATH "/../library/Square/Entity"
    resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.annotationReaderClass = "Doctrine\Common\Annotations\AnnotationReader"
    resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.annotationReaderCache = default

    ;resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.annotationReaderNamespaces.App = "Application\DoctrineExtensions\ORM\Mapping"

Usage
-----

Development vs Production
~~~~~~~~~~~~~~~~~~~~~~~~~

Since Zend Framework allows development settings to inherit all production settings with this syntax

.. code-block:: ini

   [development : production]

it becomes trivial to configure the **Doctrine 2** development environment. During development, you would want to auto\ |ndash|\ generate proxies and cache using ``ArrayCache``:

.. code-block:: ini
    
    [development : production]
    phpSettings.display_startup_errors = 1
    phpSettings.display_errors = 1
    resources.frontController.params.displayExceptions = 1
    ; snip . . .
    ; Doctrine 2 development settings
    resources.doctrine.cache.instances.default.adapterClass = "Doctrine\Common\Cache\ArrayCache"
    resources.doctrine.orm.entityManagers.default.proxy.autoGenerateClasses = true
   
The development environment will inherit all the ``[production]`` settings, overriding only two of its settings. In the ``[production]`` section, you would set auto\ |ndash|\ generate proxies to **false** and use a different caching mechanism, say, ``ApcCache``:
 
.. code-block:: ini

   [production] 
   ; See Bisna application.ini for complete settings.
   resources.doctrine.cache.instances.default.adapterClass = "Doctrine\Common\Cache\ApcCache"
   ; snip...
   resources.doctrine.orm.entityManagers.default.proxy.autoGenerateClasses = false

.. _doctrine-container:

``Bisna\Application\Doctrine\Container``, the Doctrine Container
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In your action controllers, you can retrieve the Doctrine Container ``Bisna\Application\Resource\Container`` from the Zend registry.

.. code-block:: php

	<?php

	namespace Bisna\Controller;

	/**
	 * Action class.
	 *
	 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
	 */
	class Action extends \Zend_Controller_Action {
	    /**
	     * Retrieve the Doctrine Container.
	     *
	     * @return Bisna\Application\Container\DoctrineContainer
	     */
	    public function getDoctrineContainer()
	    {
	        return $this->doctrine = Zend_Registry::get('doctrine'); 
            // or: return $this->getInvokeArg('bootstrap')->getResource('doctrine'); 
	    }		
	}

.. _container-api:

Doctrine Container API
~~~~~~~~~~~~~~~~~~~~~~

The following API exposes all available Doctrine Container methods to be used by developers:

.. code-block:: php

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
	class DoctrineContainer {
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
	
Action Controller Example
^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php
    
    class Catalog_ItemController extends Zend_Controller_Action {
        
      protected $em = null; 
        
      public function init()
      {
          $this->em = \Zend_Registry::get('doctrine')->getEntityManager();
            
      }
        // action to display a catalog item
      public function displayAction()
      {
                
        $input->setData($this->getRequest()->getParams());        
        
	    try {  
          $stamp_item = $em->getRepository('\Square\Entity\StampItem')->find($input->id);

          if (!is_null($stamp_item)) {

            // snip...
          }
        catch (Exception $e) {
			// snip...
        }  
      }
    }  

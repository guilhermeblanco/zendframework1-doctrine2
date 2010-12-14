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

TBD

### Doctrine Container API

TBD

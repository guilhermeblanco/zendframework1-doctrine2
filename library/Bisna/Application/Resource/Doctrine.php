<?php

// Zend Framework cannot deal with Resources using namespaces
//namespace Bisna\Application\Resource;

use Bisna\Doctrine\Container as DoctrineContainer;

/**
 * Zend Application Resource Doctrine class
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class Bisna_Application_Resource_Doctrine extends \Zend_Application_Resource_ResourceAbstract
{
    /**
     * Initializes Doctrine Context.
     *
     * @return Bisna\Doctrine\Container
     */
    public function init()
    {
        $config = $this->getOptions();
        
        // Starting Doctrine container
        $container = new DoctrineContainer($config);

        // Add to Zend Registry
        \Zend_Registry::set('doctrine', $container);

        return $container;
    }
}
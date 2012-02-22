<?php

namespace Bisna\Application\Resource;

use Bisna\Doctrine\Container as DoctrineContainer;

/**
 * Zend Application Resource Doctrine class
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class Doctrine extends \Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Bisna\Doctrine\Container
	 */
	protected $_container;
    
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
    
    /**
     * Get Doctrine Container
	 * @return Bisna\Doctrine\Container
	 */
	public function getContainer(){
		return $this->_container;
	}
}
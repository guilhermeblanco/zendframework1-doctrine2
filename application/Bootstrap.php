<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initAutoloaderNamespaces()
    {
        require_once APPLICATION_PATH . '/../library/vendor/Doctrine/lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

        $autoloader = \Zend_Loader_Autoloader::getInstance();
        $fmmAutoloader = new \Doctrine\Common\ClassLoader('Bisna');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'Bisna');

        // If you have installed DoctrineORM using pear, add this:
        $symfonyAutoloader = new \Doctrine\Common\ClassLoader('Symfony', 'Doctrine');
        $autoloader->pushAutoloader(array($symfonyAutoloader, 'loadClass'), 'Symfony'); 
    }

}


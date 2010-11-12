<?php

class IndexController extends Zend_Controller_Action {

    protected $em;

    public function init() {
        $doctrine = Zend_Registry::get("doctrine");
        $this->em = $doctrine->getEntityManager();
    }

    public function indexAction() {
        $user = new Application\Entity\User();
        $user->setName("Robson");
        $this->em->persist($user);
        $this->em->flush();
    }

}


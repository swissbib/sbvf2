<?php

namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config as ZendConfig;

class Config extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /**
     * @var    ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var    ZendConfig
     */
    protected $config;



    /**
     * Inject service locator
     *
     * @param    ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
//        $this->config            = new ZendConfig($serviceLocator->get('Config'));
    }



    /**
     * Get service locator
     *
     * @return    ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }



    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->serviceLocator->getServiceLocator()->get('VuFind\Config')->get('config');
        }

        return $this->config;
    }



    public function __invoke()
    {
        return $this->getConfig();
//        return $this->config;
    }
}

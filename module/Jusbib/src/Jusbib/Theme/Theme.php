<?php

namespace Jusbib\Theme;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Theme
 *
 * @package    Swissbib\Theme
 */
class Theme implements ServiceLocatorAwareInterface
{

    /**
     * @var    ServiceLocatorInterface
     */
    protected $serviceLocator;



    /**
     * Set serviceManager instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     *
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }



    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }



    /**
     * Get all configuration for theme tabs
     *
     * @return    Array[]
     */
    public function getThemeTabsConfig()
    {
        $moduleConfig = $this->getServiceLocator()->get('Config');

        return $moduleConfig['jusbib']['adv_tabs'];
    }

}
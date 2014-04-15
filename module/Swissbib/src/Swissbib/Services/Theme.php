<?php

namespace Swissbib\Services;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Theme
 * @package    Swissbib\Services
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
     * Get active theme
     *
     * @return    String
     */
    protected function getTheme()
    {
        return $this->getServiceLocator()->get('Vufind\Config')->get('config')->Site->theme;
    }



    /**
     * Get all configuration for theme tabs
     *
     * @return    Array[]
     */
    public function getThemeTabsConfig()
    {
        $theme            = $this->getTheme();
        $tabs            = array();
        $moduleConfig    = $this->getServiceLocator()->get('Config');
        $tabsConfig        = $moduleConfig['swissbib']['resultTabs'];
        $allTabs        = $tabsConfig['tabs'];
        $themeTabs        = isset($tabsConfig['themes'][$theme]) ? $tabsConfig['themes'][$theme] : array();

        foreach ($themeTabs as $themeTab) {
            if (isset($allTabs[$themeTab])) {
                $tabs[$themeTab] = $allTabs[$themeTab];
            }
        }

        return $tabs;
    }

}
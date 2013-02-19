<?php
namespace Swissbib\Controller;

use VuFind\Controller\SearchController as VFSearchController;

/**
 * [Description]
 *
 * @package       Swissbib
 * @subpackage    [Subpackage]
 */
class SearchController extends VFSearchController {

	/**
	 * Get model for home view
	 *
	 * @return	\Zend\View\Model\ViewModel
	 */
	public function homeAction() {
		$homeView = parent::homeAction();

		$this->layout()->setTemplate('layout/layout.home');

		return $homeView;
	}



	/**
	 * Get model for general results view (all tabs, content of active tab only)
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function resultsAction() {
        /** @var    $view    \Zend\View\Model\ViewModel */
		$view = parent::resultsAction();

            // Initialize tab(s) config
        $config             = $this->getServiceLocator()->get('Config');
        $resultTabsConfig   = $config['swissbib']['result_tabs'];
            // Init all tabs
        foreach($resultTabsConfig as $idTab => $tabConfig) {
            $resultTabsConfig[$idTab]   = $this->getTabConfig($tabConfig, $view);
        }

        $view->tabHeadConfigs = $resultTabsConfig;

		    // Add view params to layout
        $this->layout()->resultViewParams = $view->params;

		return $view;
	}



    /**
     * Get built SbResultsTab config
     *
     * @param   Array                       $tabConfig
     * @param   \Zend\View\Model\ViewModel  $view
     * @return  Array
     */
    private function getTabConfig($tabConfig, $view = null) {
        if( is_null($view) ) {
            $view = parent::resultsAction();
        }

        /** @var $tabModel \Swissbib\ResultTab\SbResultTab */
        $tabModel   = $tabConfig['model'];
        $tabParams  = $tabConfig['params'];
        $templates  = array_key_exists('templates', $tabConfig) ? $tabConfig['templates'] : array();

        /** @var    \Swissbib\ResultTab\SbResultTab     $tab  */
        $tab   = new $tabModel($view, $tabParams, $templates);

        return $tab->getConfig();
    }



    /**
     * Returns results content of single tab (called via AJAX)
     *
     * @return \Zend\View\Model\ViewModel
     * @throws  \Exception
     */
    public function tabcontentAction() {
        $tabKey = $_REQUEST['tab'];

            // Initialize tab config
        $config = $this->getServiceLocator()->get('Config');
        if( !array_key_exists($tabKey, $config['swissbib']['result_tabs']) ) {
            throw new \Exception('Result tab not defined: ' . $tabKey);
        }

        $tabConfig   = $config['swissbib']['result_tabs'][$tabKey];

        /** @var    $view    \Zend\View\Model\ViewModel */
        $view = parent::resultsAction();
        $view->tabHeadConfig = $this->getTabConfig($tabConfig, $view);

            // Add view params to layout
        $this->layout()->resultViewParams = $view->params;

            // Set the model terminal
        $view->setTerminal(true);

        return $view;
    }



    /**
     * Returns results content of single tab (called via AJAX)
     *
     * @return \Zend\View\Model\ViewModel
     * @throws  \Exception
     */
    public function tabsidebarAction() {
        $tabKey = $_REQUEST['tab'];

            // Initialize tab config
        $config = $this->getServiceLocator()->get('Config');
        if( !array_key_exists($tabKey, $config['swissbib']['result_tabs']) ) {
            throw new \Exception('Result tab not defined: ' . $tabKey);
        }

        $tabConfig   = $config['swissbib']['result_tabs'][$tabKey];

        /** @var    $view    \Zend\View\Model\ViewModel */
        $view = parent::resultsAction();
        $view->tabHeadConfig = $this->getTabConfig($tabConfig, $view);

            // Add view params to layout
        $this->layout()->resultViewParams = $view->params;

            // Set the model terminal
        $view->setTerminal(true);

        return $view;
    }

}
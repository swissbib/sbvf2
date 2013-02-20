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
            // Initialize tab(s) config
        $config             = $this->getServiceLocator()->get('Config');
        $resultTabsConfig   = $config['swissbib']['result_tabs'];

            // Init all tabs
        $views  = array();
        foreach($resultTabsConfig as $idTab => $tabConfig) {
            $this->searchClassId= $tabConfig['searchClassId']; //'Solr'
            $views[$idTab]      = parent::resultsAction();
            if( array_key_exists('selected', $tabConfig['params']) && $tabConfig['params']['selected'] === true ) {
                $view = $views[$idTab];
            }

            /** @var    $view    \Zend\View\Model\ViewModel */
            $resultTabsConfig[$idTab]   = $this->getTabConfig($tabConfig, $views[$idTab]);
        }

		    // Add view params
        $view->tabHeadConfigs = $resultTabsConfig;
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
            $this->searchClassId    = $tabConfig['searchClassId'];
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
        $this->searchClassId = $tabConfig['searchClassId'];
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
        $this->searchClassId = $tabConfig['searchClassId'];
        $view = parent::resultsAction();
        $view->tabHeadConfig = $this->getTabConfig($tabConfig, $view);

            // Add view params to layout
        $this->layout()->resultViewParams = $view->params;

            // Set the model terminal
        $view->setTerminal(true);

        return $view;
    }

}
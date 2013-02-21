<?php
namespace Swissbib\Controller;

use VuFind\Controller\SearchController as VFSearchController;
use Zend\Session\Container as SessionContainer;
use VuFind\Search\Memory as VFMemory;

/**
 * [Description]
 *
 * @package       Swissbib
 * @subpackage    [Subpackage]
 */
class SearchController extends VFSearchController {

	/**
	 * (Default Action) Get model for home view
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
        $preloadNonSelectedTabResultCounts  = !!$this->getModuleConfigParam('preload_result_tabs_counts');
        $resultTabsConfig                   = $this->getModuleConfigParam('result_tabs');

            // Init all tabs
        $views  = array();
        foreach($resultTabsConfig as $idTab => $tabConfig) {
            $this->searchClassId= $tabConfig['searchClassId']; //'Solr'
            $this->rememberTabbedSearchURI($idTab);

            if( array_key_exists('selected', $tabConfig['params']) &&  $tabConfig['params']['selected'] === true ) {
                    // selected tab
                $views[$idTab]  = parent::resultsAction();
                $selectedView   = $views[$idTab];
            } else {
                    // non-selected tabs (preload results optionally)
                if( $preloadNonSelectedTabResultCounts ) {
                    $views[$idTab]      = parent::resultsAction();
                } else {
                    $views[$idTab]  = null;
                }
            }

            /** @var    $selectedView    \Zend\View\Model\ViewModel */
            $resultTabsConfig[$idTab]   = $this->getTabConfig($tabConfig, $views[$idTab]);
        }

		    // Add view params
        $selectedView->tabHeadConfigs       = $resultTabsConfig;
        $this->layout()->resultViewParams   = $selectedView->params;

		return $selectedView;
	}



    /**
     * Returns results content of single tab (called via AJAX)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tabcontentAction() {
        return $this->tabAction();
    }



    /**
     * Returns sidebar content of single tab (called via AJAX)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tabsidebarAction() {
        return $this->tabAction();
    }



    /**
     * Wrapper for AJAX "tabbed" actions
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function tabAction() {
        $tabKey = $_REQUEST['tab'];

            // Initialize tab config
        $resultTabsConfig   = $this->getModuleConfigParam('result_tabs');
        $tabConfig          = $resultTabsConfig[$tabKey];
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
     * Get built SbResultsTab config
     *
     * @param   Array                       $tabConfig
     * @param   \Zend\View\Model\ViewModel  $view
     * @return  Array
     */
    private function getTabConfig($tabConfig, $view = null) {
        if( is_null($view) ) {
            $this->searchClassId    = $tabConfig['searchClassId'];
            $view = null; //parent::resultsAction();
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
     * Store selected tab's search query Uri to session container
     */
    private function rememberTabbedSearchURI($idTab) {
        $requestUri  = $this->request->getRequestUri();

        $session = new SessionContainer('SbTabbedSearch_' . $idTab);
        $session->last = $requestUri;
    }



    /**
     * Get given parameter from (given / or ) swissbib module config
     *
     * @throws \Exception
     * @param   String  $moduleKey
     * @param   String  $parameterKey
     * @return  Mixed
     */
    private function getModuleConfigParam($parameterKey, $moduleKey = 'swissbib') {
        $config         = $this->getServiceLocator()->get('Config');
        $moduleConfig   = $config[$moduleKey];

        if( !array_key_exists($parameterKey, $moduleConfig) ) {
            throw new \Exception('swissbib config param missing: ' . $parameterKey);
        }

        return $moduleConfig[$parameterKey];
    }
}
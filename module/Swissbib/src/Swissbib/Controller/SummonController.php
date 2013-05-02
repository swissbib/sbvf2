<?php

namespace Swissbib\Controller;

use VuFind\Controller\SummonController as VFSummonController;
use Zend\Session\Container as SessionContainer;

use Swissbib\Controller\Helper\Search as SearchHelper;

/**
 * Summon Controller
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */

class SummonController extends VFSummonController
{

    /**
     * Search action
     *
     * @return mixed
     */
    public function searchAction()
    {
        return $this->resultsAction();
    }


	/**
	 * Get model for general results view (all tabs, content of active tab only)
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function resultsAction()
	{
		// Initialize tab(s) config
		$preloadNonSelectedTabResultCounts = !!$this->getModuleConfigParam('preload_result_tabs_counts');
		$idSelectedTab                     = $this->getIdSelectedTab();
		$resultTabsConfig                  = $this->getModuleConfigParam('result_tabs');

		// Init all tabs
		$views = array();
		foreach ($resultTabsConfig as $idTab => $tabConfig) {
			$this->searchClassId	= $tabConfig['searchClassId'];	// Solr, Summon, WorldCat, ...
			SearchHelper::rememberTabbedSearchURI($idTab, $this->request->getRequestUri());

			if ($idTab === $idSelectedTab) {
				// Selected tab
				$views[$idTab]                   = parent::resultsAction();
				$selectedView                    = $views[$idTab];
				$tabConfig['params']['selected'] = true;
			} else {
				// Non-selected tabs (preload results optionally)
				if ($preloadNonSelectedTabResultCounts) {
					$views[$idTab] = parent::resultsAction();
				} else {
					$views[$idTab] = null;
				}
			}

			$resultTabsConfig[$idTab] = $this->getTabConfig($tabConfig, $views[$idTab]);
		}

		// Add view params
		/** @var    $selectedView    \Zend\View\Model\ViewModel */
		$selectedView->tabHeadConfigs     = $resultTabsConfig;
		$selectedView->facetsConfig       = $this->getServiceLocator()->get('VuFind\Config')->get('facets');
		$this->layout()->resultViewParams = $selectedView->params;

		return $selectedView;
	}



	/**
	 * Returns results content of single tab (called via AJAX)
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function tabcontentAction()
	{
		return $this->tabAction();
	}



	/**
	 * Returns sidebar content of single tab (called via AJAX)
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function tabsidebarAction()
	{
		return $this->tabAction();
	}



	/**
	 * Wrapper for AJAX "tabbed" actions
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	private function tabAction()
	{
		$tabKey = $_REQUEST['tab'];

		// Initialize tab config
		$resultTabsConfig = $this->getModuleConfigParam('result_tabs');
		$tabConfig        = $resultTabsConfig[$tabKey];
		/** @var    $view    \Zend\View\Model\ViewModel */
		$this->searchClassId = $tabConfig['searchClassId'];

		$view                = parent::resultsAction();
		$view->tabHeadConfig = $this->getTabConfig($tabConfig, $view);
		$view->facetsConfig  = $this->getServiceLocator()->get('VuFind\Config')->get('facets');

		// Add view params to layout
		$this->layout()->resultViewParams = $view->params;

		// Set the model terminal
		$view->setTerminal(true);

		return $view;
	}



	/**
	 * Get given parameter from (given / or ) swissbib module config
	 *
	 * @throws \Exception
	 * @param   String  $moduleKey
	 * @param   String  $parameterKey
	 * @return  Mixed
	 */
	private function getModuleConfigParam($parameterKey, $moduleKey = 'swissbib')
	{
		$config       = $this->getServiceLocator()->get('Config');
		$moduleConfig = $config[$moduleKey];

		if (!array_key_exists($parameterKey, $moduleConfig)) {
			throw new \Exception('swissbib config param missing: ' . $parameterKey);
		}

		return $moduleConfig[$parameterKey];
	}



	/**
	 * Get ID of selected tab
	 * User pref: lastly selected tab (cookie set in jquery.tabbed.js)
	 * Or module config: default tab (if no user pref stored yet)
	 *
	 * @return  String  ID of the previously selected / default tab
	 */
	private function getIdSelectedTab()
	{
		$idTab = null;

		// Get selected tab from cookie if set
		if (isset($_COOKIE[SearchHelper::COOKIENAME_SELECTED_TAB])) {
			$cookieContent = $_COOKIE[SearchHelper::COOKIENAME_SELECTED_TAB];
			$idTab         = str_replace('tabbed_', '', $cookieContent);
		}

		return !empty($idTab) ? $idTab : $this->getModuleConfigParam('default_result_tab');
	}



	/**
	 * Get built SbResultsTab config
	 *
	 * @param   Array                       $tabConfig
	 * @param   \Zend\View\Model\ViewModel  $view
	 * @return  Array
	 */
	private function getTabConfig($tabConfig, $view = null)
	{
		if (is_null($view)) {
			$this->searchClassId = $tabConfig['searchClassId'];
			$view                = null; //parent::resultsAction();
		}

		/** @var $tabModel \Swissbib\ResultTab\SbResultTab */
		$tabModel  = $tabConfig['model'];
		$tabParams = $tabConfig['params'];

		$templates = array_key_exists('templates', $tabConfig) ? $tabConfig['templates'] : array();

		/** @var    \Swissbib\ResultTab\SbResultTab     $tab */
		$tab = new $tabModel($view, $tabParams, $templates);

		return $tab->getConfig();
	}

}


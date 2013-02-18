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
	 * Get model for results view
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function resultsAction() {
        /** @var    $resultView    \Zend\View\Model\ViewModel */
		$resultView = parent::resultsAction();

            // Initialize tab(s) config
        $config             = $this->getServiceLocator()->get('Config');
        $resultTabsConfig   = $config['swissbib']['result_tabs'];
            // Init all tabs
        foreach($resultTabsConfig as $idTab => $tabConfig) {
            /** @var $tabModel \Swissbib\ResultTab\SbResultTab */
            $tabModel   = $tabConfig['model'];
            $tabParams  = $tabConfig['params'];
            $templates  = array_key_exists('templates', $tabConfig) ? $tabConfig['templates'] : array();

            /** @var    \Swissbib\ResultTab\SbResultTab     $tab  */
            $tab   = new $tabModel($resultView, $tabParams, $templates);
            $resultTabsConfig[$idTab]   = $tab->getConfig();
        }

        $resultView->tabHeadConfigs = $resultTabsConfig;

		    // Add view params to layout
        $this->layout()->resultViewParams = $resultView->params;

		return $resultView;
	}

}
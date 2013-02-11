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

		$this->layout()->setTemplate("layout/layout.home");

		return $homeView;
	}



	/**
	 * Get model for results view
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function resultsAction() {
		$resultView = parent::resultsAction();

		// Store result view params in layout
		$this->layout()->resultViewParams = $resultView->params;

		parent::advancedAction();

		return $resultView;
	}

}
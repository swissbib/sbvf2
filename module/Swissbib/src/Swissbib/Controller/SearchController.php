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

            // Add tab(s) config to view
        $amountResults  = $resultView->results->getResultTotal();
        $resultView->tabHeadConfigs = array(
            array(
                'id'		=> 'swissbib',
                'label'		=> 'Bücher & mehr',
                'count'		=> $amountResults,
                'selected'	=> true
            ),
            array(
                'id'	=> 'external',
                'label'	=> 'Artikel & mehr',
                'count'	=> 1234
            )
        );

		    // Add view params to layout
        $this->layout()->resultViewParams = $resultView->params;

		return $resultView;
	}

}
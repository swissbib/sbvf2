<?php
namespace Swissbib\Controller;

use VuFind\Controller\MyResearchController as VFMyResearchController;
use Zend\View\Model\ViewModel;


class MyResearchController extends VFMyResearchController {

	/**
	 * Get location parameter from route
	 *
	 * @return	String|Boolean
	 */
	protected function getLocationFromRoute() {
		return $this->params()->fromRoute('location', false);
	}


//	protected function injectLocation(ViewModel $viewModel)



	/**
	* Inject location from route
	*
	* @inheritDoc
	*/
	protected function createViewModel($params = null) {
		$viewModel	= parent::createViewModel($params);

		$location	= $this->getLocationFromRoute();
		$location	= $location ?: 'baselbern';

		$viewModel->location = $location;

		return $viewModel;
	}

}
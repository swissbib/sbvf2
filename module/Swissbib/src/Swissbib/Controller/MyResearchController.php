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



	/**
	* Inject location from route
	*
	* @inheritDoc
	*/
	protected function createViewModel($params = null) {
		$viewModel	= parent::createViewModel($params);

		$viewModel->location = $this->getLocationFromRoute() ?: 'baselbern';

		return $viewModel;
	}

}
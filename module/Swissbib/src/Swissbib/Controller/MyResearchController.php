<?php
namespace Swissbib\Controller;

use VuFind\Controller\MyResearchController as VFMyResearchController;
use Swissbib\VuFind\ILS\Driver\Aleph;


class MyResearchController extends VFMyResearchController {


	public function photocopiesAction() {
		// Stop now if the user does not have valid catalog credentials available:
		if( !is_array($patron = $this->catalogLogin()) ) {
			return $patron;
		}

		/** @var Aleph $catalog  */
		$catalog = $this->getILS();

		// Get photo copies details:
		$photoCopies = $catalog->getPhotocopies($patron);

		return $this->createViewModel(array('photoCopies' => $photoCopies));
	}

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
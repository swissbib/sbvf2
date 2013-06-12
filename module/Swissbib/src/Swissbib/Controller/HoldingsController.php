<?php
namespace Swissbib\Controller;

use VuFind\Controller\AbstractBase as BaseController;
use Zend\View\Model\ViewModel;

use VuFind\Record\Loader as RecordLoader;
use Swissbib\RecordDriver\SolrMarc;

/**
 * [Description]
 *
 */
class HoldingsController extends BaseController
{

	public function listAction()
	{
		$institution = $this->params()->fromRoute('institution');
		$idRecord    = $this->params()->fromRoute('record');
		$holdingsData= $this->getRecord($idRecord)->getInstitutionHoldings($institution);

		$viewModel	 = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setVariables($holdingsData);

		if (isset($holdingsData['items'])) {
			$viewModel->setTemplate('Holdings/items');
		} elseif (isset($holdingsData['holdings'])) {
			$viewModel->setTemplate('Holdings/holdings');
		} else {
			$viewModel->setTemplate('Holdings/nodata');
		}

		return $viewModel;
	}



	/**
	 * Load solr record
	 *
	 * @param	Integer			$idRecord
	 * @return	SolrMarc
	 */
	protected function getRecord($idRecord) {
		return $this->getServiceLocator()->get('VuFind\RecordLoader')->load($idRecord, 'Solr');
	}

}

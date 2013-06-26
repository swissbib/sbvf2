<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel;

use VuFind\Controller\AbstractBase as BaseController;
use VuFind\Record\Loader as RecordLoader;

use Swissbib\RecordDriver\SolrMarc;

/**
 * Serve holdings data (items and holdings) for solr records over ajax
 *
 */
class HoldingsController extends BaseController
{

	/**
	 * Get list for items or holdings, depending on the data
	 *
	 * @return ViewModel
	 */
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
	protected function getRecord($idRecord)
	{
		return $this->getServiceLocator()->get('VuFind\RecordLoader')->load($idRecord, 'Solr');
	}
}

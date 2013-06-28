<?php
namespace Swissbib\Controller;

use Swissbib\RecordDriver\Helper\Holdings;
use Zend\Mvc\Exception;
use Zend\View\Model\ViewModel;

use VuFind\Controller\AbstractBase as BaseController;

use Swissbib\RecordDriver\SolrMarc;
use Swissbib\VuFind\ILS\Driver\Aleph;
use Swissbib\Helper\BibCode;

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
		$template	 = 'Holdings/nodata';

		$holdingsData['record']   	 = $idRecord;
		$holdingsData['institution'] = $institution;

		if (isset($holdingsData['items'])) {
			$template = 'Holdings/items';
		} elseif (isset($holdingsData['holdings'])) {
			$template = 'Holdings/holdings';
		}

		return $this->getViewModel($holdingsData, $template);
	}



	/**
	 * Get items for holding
	 *
	 * @return ViewModel
	 */
	public function holdingItemsAction()
	{
		$idRecord    = $this->params()->fromRoute('record');
		$record		 = $this->getRecord($idRecord);
		$institution = $this->params()->fromRoute('institution');
		$resourceId  = $this->params()->fromRoute('resource');
		$offset      = (int)$this->params()->fromRoute('offset');
		$year        = (int)$this->params()->fromQuery('year');
		$volume      = $this->params()->fromQuery('volume');

		/** @var Aleph $aleph */
		$aleph		 = $this->getILS();
		$holdingItems= $aleph->getHoldingHoldingItems($resourceId, $institution, $offset, $year, $volume);
		$totalItems	= $aleph->getHoldingItemCount($resourceId, $institution);
		/** @var Holdings $helper */
		$helper		= $this->getServiceLocator()->get('Swissbib\HoldingsHelper');

		foreach ($holdingItems as $index => $holdingItem) {
			$holdingItem['institution'] = $institution;
			$holdingItems[$index] = $helper->extendItem($holdingItem, $record);
		}

		$data = array(
			'items'		=> $holdingItems,
			'offset'	=> $offset,
			'year'		=> $year,
			'volume'	=> $volume,
			'filters'	=> $aleph->getResourceFilters($resourceId),
			'total'		=> $totalItems
		);

		return $this->getViewModel($data, 'Holdings/holding-holding-items');
	}



	/**
	 * Get view model with special template and terminated for ajax
	 *
	 * @param array $variables
	 * @param null  $template
	 * @param bool  $terminal
	 * @return ViewModel
	 */
	protected function getViewModel(array $variables = array(), $template = null, $terminal = true)
	{
		$viewModel = new ViewModel($variables);

		if ($template) {
			$viewModel->setTemplate($template);
		}
		if ($terminal) {
			$viewModel->setTerminal(true);
		}

		return $viewModel;
	}



	/**
	 * Build a resource id
	 *
	 * @param $idRecord
	 * @param $network
	 * @return string
	 */
	protected function getResourceId($idRecord, $network)
	{
		/** @var BibCode $bibHelper */
		$bibHelper	= $this->getServiceLocator()->get('Swissbib\BibCodeHelper');
//		$record		= $this->getRecord($idRecord);
		$idls		= $bibHelper->getBibCode($network);

		return strtoupper($idls) . $idRecord;
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

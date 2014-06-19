<?php
namespace Swissbib\Controller;

use Zend\Mvc\Exception;
use Zend\View\Model\ViewModel;

use Swissbib\Controller\BaseController;
use Swissbib\RecordDriver\SolrMarc;
use Swissbib\VuFind\ILS\Driver\Aleph;
use Swissbib\Helper\BibCode;
use Swissbib\RecordDriver\Helper\Holdings;

/**
 * Serve holdings data (items and holdings) for solr records over ajax
 *
 */
class HoldingsController extends BaseController
{
    /** @var    Integer        page size for holding items popup */
    protected $PAGESIZE_HOLDINGITEMS = 10;



    /**
     * Get list for items or holdings, depending on the data
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $institution = $this->params()->fromRoute('institution');
        $idRecord = $this->params()->fromRoute('record');
        $record = $this->getRecord($idRecord);
        $template = 'Holdings/nodata';

        try {
            $holdingsData = $record->getInstitutionHoldings($institution);
        } catch (\Exception $e) {
            $holdingsData = array();
        }

        $holdingsData['record'] = $idRecord;
        $holdingsData['recordTitle'] = $record->getTitle();
        $holdingsData['institution'] = $institution;

        if (isset($holdingsData['holdings']) && !empty($holdingsData['holdings']) || isset($holdingsData['items']) && !empty($holdingsData['items'])) {
            $template = 'Holdings/holdings-and-items';
        }

        return $this->getAjaxViewModel($holdingsData, $template);
    }



    /**
     * Get items of a holding
     * Displayed in a popup, opened from a holdings/items list in holdings tab
     *
     * @return ViewModel
     */
    public function holdingItemsAction()
    {
        $idRecord = $this->params()->fromRoute('record');
        $record = $this->getRecord($idRecord);
        $institution = $this->params()->fromRoute('institution');
        $resourceId = $this->params()->fromRoute('resource');
        $page = (int)$this->params()->fromQuery('page', 1);
        $year = (int)$this->params()->fromQuery('year');
        $volume = $this->params()->fromQuery('volume');
        $offset = ($page - 1) * $this->PAGESIZE_HOLDINGITEMS;

        /** @var Aleph $aleph */
        $aleph = $this->getILS();
        $holdingItems = $aleph->getHoldingHoldingItems($resourceId, $institution, $offset, $year, $volume, $this->PAGESIZE_HOLDINGITEMS);
        $totalItems = $aleph->getHoldingItemCount($resourceId, $institution, $year, $volume);
        /** @var Holdings $helper */
        $helper = $this->getServiceLocator()->get('Swissbib\HoldingsHelper');
        $dummyHoldingItem = $this->getFirstHoldingItem($idRecord, $institution);
        $networkCode = $dummyHoldingItem['network'];
        $bibSysNumber = $dummyHoldingItem['bibsysnumber'];
        $admCode = $dummyHoldingItem['adm_code'];
        $bib = $dummyHoldingItem['bib_library'];
        $resourceFilters = $aleph->getResourceFilters($resourceId);
        $extendingOptions = array(
            'availability' => true
        );

        // Add missing data to holding items
        foreach ($holdingItems as $index => $holdingItem) {
            $holdingItem['institution'] = $institution;
            $holdingItem['institution_chb'] = $institution;
            $holdingItem['network'] = $networkCode;
            $holdingItem['bibsysnumber'] = $bibSysNumber;
            $holdingItem['adm_code'] = $admCode;
            $holdingItem['bib_library'] = $bib;
            $holdingItems[$index] = $helper->extendItem($holdingItem, $record, $extendingOptions);
        }

        $data = array(
            'items'         => $holdingItems,
            'record'        => $idRecord,
            'recordTitle'   => $record->getTitle(),
            'institution'   => $institution,
            'page'          => $page,
            'year'          => $year,
            'volume'        => $volume,
            'filters'       => $resourceFilters,
            'total'         => $totalItems, // for paging
            'baseUrlParams' => array(
                'institution' => $institution,
                'record'      => $idRecord,
                'resource'    => $resourceId
            )
        );

        return $this->getAjaxViewModel($data, 'Holdings/holding-holding-items');
    }



    /**
     * @param $idRecord
     * @param $institutionCode
     *
     * @return    Array
     */
    protected function getFirstHoldingItem($idRecord, $institutionCode)
    {
        $holdingItems = $this->getRecord($idRecord)->getInstitutionHoldings($institutionCode, false);

        return $holdingItems['holdings'][0];
    }



    /**
     * Extract network from resource id
     * The five first chars of the resource are the bib code.
     * Convert the bib code into network code
     *
     * @todo    Is there a more stable version to do this? It works, but..
     *
     * @param    String $resourceId
     *
     * @return    String
     */
    protected function getNetworkFromResource($resourceId)
    {
        $bibCode = strtoupper(substr($resourceId, 0, 5));

        return $this->getBibCodeHelper()->getNetworkCode($bibCode);
    }



    /**
     * Get bib code helper service
     *
     * @return BibCode $bibHelper
     */
    protected function getBibCodeHelper()
    {
        return $this->getServiceLocator()->get('Swissbib\BibCodeHelper');
    }



    /**
     * Build a resource id
     *
     * @param $idRecord
     * @param $network
     *
     * @return string
     */
    protected function getResourceId($idRecord, $network)
    {
        /** @var BibCode $bibHelper */
        $bibHelper = $this->getServiceLocator()->get('Swissbib\BibCodeHelper');
        $idls = $bibHelper->getBibCode($network);

        return strtoupper($idls) . $idRecord;
    }



    /**
     * Load solr record
     *
     * @param    Integer $idRecord
     *
     * @return    SolrMarc
     */
    protected function getRecord($idRecord)
    {
        return $this->getServiceLocator()->get('VuFind\RecordLoader')->load($idRecord, 'Solr');
    }
}

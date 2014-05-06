<?php
namespace Swissbib\VuFind\Search\Solr;

use VuFind\Search\Solr\Params as VuFindSolrParams;
use VuFindSearch\ParamBag;

/*
 * Class to extend the core VF2 SOLR functionality related to Parameters
 */
class Params extends VuFindSolrParams
{

    /**
     * @var array
     */
    protected $dateRange = array(
        'isActive' => false
    );



    /**
     * Override to prevent problems with namespace
     * See implementation of parent for details
     *
     * @return    String
     */
    public function getSearchClassId()
    {
        return 'Solr';
    }



    /**
     * overridden function - we need some more parameters.
     *
     * @return ParamBag
     */
    public function getBackendParameters()
    {
        $backendParams = parent::getBackendParameters();

        //with SOLR 4.3 AND is no longer the default parameter
        $backendParams->add("q.op", "AND");

        $backendParams = $this->addUserInstitutions($backendParams);

        return $backendParams;


    }



    /**
     * @return ParamBag
     */
    public function getSpellcheckBackendParameters()
    {
        $backendParams = parent::getBackendParameters();
        $backendParams->remove("spellcheck");

        //with SOLR 4.3 AND is no longer the default parameter
        $backendParams->add("q.op", "AND");

        //we need this homegrown param to control the behaviour of InjectSwissbibSpellingListener
        //I don't see another possibilty yet
        $backendParams->add("swissbibspellcheck", "true");


        //$backendParams = $this->addUserInstitutions($backendParams);

        return $backendParams;
    }



    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return $this->getServiceLocator()->get('Swissbib\TypeLabelMappingHelper')->getLabel($this);
    }



    /**
     * @return array
     */
    public function getDateRange()
    {
        $this->dateRange['min'] = 1450;
        $this->dateRange['max'] = intval(date('Y')) + 1;

        if (!$this->dateRange['isActive']) {
            $this->dateRange['from']    = (int) $this->dateRange['min'];
            $this->dateRange['to']      = (int) $this->dateRange['max'];
        }

        return $this->dateRange;
    }



    /**
     * @Override
     *
     * @param string $field field to use for filtering.
     * @param string $from  year for start of range.
     * @param string $to    year for end of range.
     *
     * @return string       filter query.
     */
    protected function buildDateRangeFilter($field, $from, $to)
    {
        $this->dateRange['from']        = (int) $from;
        $this->dateRange['to']          = (int) $to;
        $this->dateRange['isActive']    = true;

        return parent::buildDateRangeFilter($field, $from, $to);
    }



    /**
     * Add user institutions as facet queries to backend params
     *
     * @param    ParamBag $backendParams
     *
     * @return    ParamBag
     */
    protected function addUserInstitutions(ParamBag $backendParams)
    {
        /** @var Manager $favoritesManger */
        $favoritesManger = $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
        /** @var String[] $favoriteInstitutions */
        $favoriteInstitutions = $favoritesManger->getUserInstitutions();

        if (sizeof($favoriteInstitutions) >  0) {
            //facet parameter has to be true in case it's false
            $backendParams->set("facet", "true");

            foreach ($favoriteInstitutions as $institutionCode) {
                $backendParams->add("facet.query", "institution:" . $institutionCode);
                //$backendParams->add("bq", "institution:" . $institutionCode . "^5000");
            }
        }

        return $backendParams;
    }



    /**
     * @override
     *
     * @param string $field Facet field name.
     *
     * @return string       Human-readable description of field.
     */
    public function getFacetLabel($field)
    {
        switch($field) {
            case 'publishDate':
                return 'adv_search_year';
            default:
                return parent::getFacetLabel($field);
        }
    }

}

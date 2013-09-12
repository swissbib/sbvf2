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
	 * Override to prevent problems with namespace
	 * See implementation of parent for details
	 *
	 * @return	String
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



    public function getSpellcheckBackendParameters()
    {
        $backendParams = parent::getBackendParameters();
        $backendParams->remove("spellcheck");

        //with SOLR 4.3 AND is no longer the default parameter
        $backendParams->add("q.op", "AND");

        $backendParams->add("spellcheck", "true");
        $backendParams->add("spellcheck.q","new yerk");
        //$spelling = $query->getAllTerms();
        //if ($spelling) {
        //    $backendParams->set('spellcheck.q', $spelling);
        //    $this->spellingQuery = $spelling;
        //}



        //$backendParams = $this->addUserInstitutions($backendParams);

        return $backendParams;
    }




    /**
     * Add user institutions as facet queries to backend params
     *
     * @param	ParamBag	$backendParams
     * @return	ParamBag
     */
    protected function addUserInstitutions(ParamBag $backendParams)
    {
        /** @var Manager $favoritesManger */
        $favoritesManger		= $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
        /** @var String[] $favoriteInstitutions */
        $favoriteInstitutions	= $favoritesManger->getUserInstitutions();

        if (sizeof($favoriteInstitutions > 0)) {
            //facet parameter has to be true in case it's false
            $backendParams->set("facet", "true");

            foreach ($favoriteInstitutions as $institutionCode) {
                $backendParams->add("facet.query", "institution:" . $institutionCode);
                $backendParams->add("bq", "institution:" . $institutionCode . "^5000");
            }
        }

        return $backendParams;
    }



}

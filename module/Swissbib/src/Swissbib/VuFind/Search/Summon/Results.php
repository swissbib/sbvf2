<?php


namespace Swissbib\Vufind\Search\Summon;

use VuFind\Search\Summon\Results as VFSummonResults;


class Results extends VFSummonResults
{


    /**
     * Create search backend parameters for advanced features.
     *
     * @param Params $params Search parameters
     *
     * @return ParamBag
     */
    protected function createBackendParameters(Params $params)
    {

        parent::createBackendParameters($params);


   }


}
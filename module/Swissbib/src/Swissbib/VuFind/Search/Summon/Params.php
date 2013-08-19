<?php


namespace Swissbib\VuFind\Search\Summon;

use VuFind\Search\Summon\Params as VFSummonParams;


class Params extends VFSummonParams
{


    public function getSearchClassId()
    {



        //$class = explode('\\', get_class($this));
        //return $class[2];
        //we can't use the basic VuFind mechanism return class[2] because our namespace is build as
        //Swissbib/Vufind/Search/[specialized Search target]
        //therefor it has o be $class[3]
        //My guess: the whole Design related to search types will be refactored by VuFind in the upcoming time (More intensive use of EventManager)
        //so return the name of the target makes it more explicit for a type only responsible for Summon results
        return 'Summon';

    }


}
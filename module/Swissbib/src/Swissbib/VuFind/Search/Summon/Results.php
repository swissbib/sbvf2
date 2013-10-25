<?php


namespace Swissbib\Vufind\Search\Summon;

use SerialsSolutions_Summon_Query as SummonQuery,
    VuFind\Exception\RecordMissing as RecordMissingException,
    VuFind\Search\Base\Results as BaseResults,
    VuFind\Solr\Utils as SolrUtils,
    VuFindSearch\ParamBag;

use VuFind\Search\Summon\Results as VFSummonResults;


class Results extends VFSummonResults
{

    /**
     * @var String
     */
    protected $target = 'summon';



    /**
     * Turn the list of spelling suggestions into an array of urls
     *   for on-screen use to implement the suggestions.
     *
     * @return array Spelling suggestion data arrays
     */
    public function getSpellingSuggestions()
    {
        $retVal = array();
        foreach ($this->getRawSuggestions() as $term => $details) {
            foreach ($details['suggestions'] as $word) {
                // Strip escaped characters in the search term (for example, "\:")
                $term = stripcslashes($term);
                $word = stripcslashes($word);
                // strip enclosing parentheses
                $from = array( '/^\(/', '/\)$/');
                $to = array('','');
                $term = preg_replace($from,$to,$term);
                $word = preg_replace($from,$to,$word);
                $retVal[$term]['suggestions'][$word] = array('new_term' => $word);
            }
        }
        return $retVal;
    }



    /**
     * @return String $target
     */
    public function getTarget()
    {
      return $this->target;
    }

}

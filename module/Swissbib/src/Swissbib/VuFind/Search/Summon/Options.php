<?php


namespace Swissbib\Vufind\Search\Summon;

use VuFind\Search\Summon\Options as VFSummonOptions;


class Options extends VFSummonOptions
{
    /**
     * Constructor
     *
     * @param \VuFind\Config\PluginManager $configLoader Config loader
     */
    public function __construct(\VuFind\Config\PluginManager $configLoader)
    {
        parent::__construct($configLoader);
        $searchSettings = $configLoader->get('Summon');
        if (isset($searchSettings->General->default_limit)) {
            $this->defaultLimit = $searchSettings->General->default_limit;
        }
        if (isset($searchSettings->General->limit_options)) {
            $this->limitOptions = explode(",", $searchSettings->General->limit_options);
        }
    }



    /**
     * Set default limit
     *
     * @param	Integer		$limit
     */
    public function setDefaultLimit($limit)
    {
        $maxLimit = max($this->getLimitOptions());
        if ($limit > $maxLimit) {
            $this->defaultLimit = $maxLimit;
        } else {
            $this->defaultLimit = intval($limit);
        }
    }

}
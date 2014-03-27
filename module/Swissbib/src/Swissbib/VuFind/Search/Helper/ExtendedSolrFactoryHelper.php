<?php
namespace Swissbib\VuFind\Search\Helper;


class ExtendedSolrFactoryHelper
{

    /**
     * @var    String[]     List of targets which should be extended by swissbib
     */
    protected $extendedTargets = array();



    /**
     * Initialize with list of extended targets
     *
     * @param    String[]        $extendedTargets
     */
    public function __construct($extendedTargets)
    {
        $this->extendedTargets = array_map('trim', array_map('strtolower', $extendedTargets));
    }



    /**
     *
     * Check whether name is in list of extended search targets
     *
     * @param    String            $name
     * @param    String            $requestedName
     * @return    Boolean
     */
    public function isExtendedTarget($name, $requestedName)
    {
        $name = strtolower($name);

        return in_array($name, $this->extendedTargets);
    }



    /**
     * Get namespace
     * swissbib namespace for extensible targets, else default namespace
     *
     * @param    String            $name
     * @param    String            $requestedName
     * @return    String
     */
    public function getNamespace($name, $requestedName)
    {
        if ($this->isExtendedTarget($name, $requestedName)) {
            return 'Swissbib\VuFind\Search';
        } else {
            return 'VuFind\Search';
        }
    }
}

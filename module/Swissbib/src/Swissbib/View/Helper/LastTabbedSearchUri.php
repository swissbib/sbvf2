<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use VuFind\Search\Memory;
use Zend\Session\Container as SessionContainer;

/**
 * Get last tabbed search URL
 */
class LastTabbedSearchUri extends AbstractHelper
{

    /**
     * Get last search URI of given tab
     *
     * @param   String  $idTab
     * @return  String
     */
    public function __invoke($idTab = 'swissbib')
    {
        $session = new SessionContainer('SbTabbedSearch_' . $idTab);

        return isset($session->last) ? $session->last : '';
    }
}

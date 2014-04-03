<?php

namespace Swissbib\TargetsProxy;

use Swissbib\Libadmin\Exception\Exception;

/**
 * UrlMatcher - detect whether (hostname of) URL matches patterns
 */
class UrlMatcher
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Check whether one of the given url patterns matches the URL
     *
     * @param    String    $host
     * @param    Array    $hostPatterns
     * @return    Boolean
     */
    public function isMatching($host, array $hostPatterns = array())
    {
        foreach($hostPatterns as $hostPattern)
        {
            if ( !empty($hostPattern) && strstr($hostPattern, $host)!== false ) {
                return true;
            }

        }

        return false;
    }

}

?>
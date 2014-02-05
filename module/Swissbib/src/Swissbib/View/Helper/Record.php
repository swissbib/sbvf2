<?php
namespace Swissbib\View\Helper;

use VuFind\View\Helper\Root\Record as VuFindRecord;

/**
 * Build record links
 * Override related method to support ctrlnum type
 *
 */
class Record extends VuFindRecord
{

    public function getLocalValues( $params = array()) {


        $allParams = array('localunions' => array(), 'localtags'  => array(), 'indicators' => array(), 'subfields' => array());
        $diffarray =  array_merge(array_diff_key($allParams, $params),$params);
        $diffArrayInCorrectOrder = array('localunions' => $diffarray['localunions'],'localtags' => $diffarray['localtags'], 'indicators' => $diffarray['indicators'], 'subfields' => $diffarray['subfields']);

        return  $this->driver->tryMethod('getLocalValues',$diffArrayInCorrectOrder);


    }

    /**
     * @param $urlArray
     * @return array
     *
     * Default: when $urlArray contains multiple links with the same URL,
     * only the first will be kept.
     *
     * If you prefer to display all links, define this value in config.ini:
     * [Record]
     * create_multiple_856_links = true
     *
     */
    private function createUniqueLinks($urlArray) {

        $config = $this->driver->getServiceLocator()->get('VuFind\Config')->get('config');
        if ( $config ) {
            $config = $config->get('Record');
            if ( $config ) {
                $config = $config->get('create_multiple_856_links');
                if ( $config ) {
                    return $urlArray;
                }
            }
        }

        $uniqueURLs = array();
        $collectedArrays = array();

        foreach ($urlArray as $url) {
            if (!array_key_exists($url['url'],$uniqueURLs)) {
                $uniqueURLs[$url['url']] = "";
                $collectedArrays[] = $url;
            }
        }

        return $collectedArrays;
    }

    /**
     * Get all the links associated with this record.  Returns an array of
     * associative arrays each containing 'desc' and 'url' keys.
     *
     * @return array
     */
    public function getExtendedLinkDetails($localRestrictions = array(), $globalRestrictions = array())
    {
        // See if there are any links available:

        if (empty($localRestrictions)) {
            $localtags = array('856','956');

            //$indicators = array('7','-');
            //$params = compact('unions' => array(),'700',array('1','-'),array('a','x'));
            //$params = compact('localtags','indicators');
            $params = compact('localtags');
            $linksInLocalFields = $this->getLocalValues($params);

        } else {
            $linksInLocalFields = $this->getLocalValues($localRestrictions);
        }


        $collectedLinks = array();

        foreach ($linksInLocalFields as $linkData) {

            $linkID = $linkData['subfields']['u'];
            $linkDescription = $linkData['subfields']['z'];
            if ($linkID) {
                if (! $linkDescription) {
                    $linkDescription = $linkID;
                }
                $collectedLinks[] = array('url' => $linkID, 'desc' => $linkDescription);
            }

        }

        if (empty($globalRestrictions)) {
            //fetch 'all' the links you can find in 856 / 956
            $urls = $this->driver->tryMethod('getURLs');
            $collectedLinks = array_merge($collectedLinks,$urls);
        } else {

            $allParamsGlobalTags = array('globalunions' => array(), 'tags'  => array());
            $diffarray =  array_merge(array_diff_key($allParamsGlobalTags, $globalRestrictions),$globalRestrictions);
            $diffArrayInCorrectOrder = array('globalunions' => $diffarray['globalunions'],'tags' => $diffarray['tags']);

            $urls =  $this->driver->tryMethod('getExtendedURLs',$diffArrayInCorrectOrder);
            $collectedLinks = array_merge($collectedLinks,$urls);

        }


        // If we found links, we may need to convert from the "route" format
        // to the "full URL" format.
        $urlHelper = $this->getView()->plugin('url');
        $serverUrlHelper = $this->getView()->plugin('serverurl');
        $formatLink = function ($link) use ($urlHelper, $serverUrlHelper) {
            // Error if route AND URL are missing at this point!
            if (!isset($link['route']) && !isset($link['url'])) {
                throw new \Exception('Invalid URL array.');
            }

            // Build URL from route/query details if missing:
            if (!isset($link['url'])) {
                $routeParams = isset($link['routeParams'])
                    ? $link['routeParams'] : array();

                $link['url'] = $serverUrlHelper(
                    $urlHelper($link['route'], $routeParams)
                );
                if (isset($link['queryString'])) {
                    $link['url'] .= $link['queryString'];
                }
            }

            // Apply prefix if found
            if (isset($link['prefix'])) {
                $link['url'] = $link['prefix'] . $link['url'];
            }
            // Use URL as description if missing:
            if (!isset($link['desc'])) {
                $link['desc'] = $link['url'];
            }

            return $link;
        };

        return $this->createUniqueLinks(array_map($formatLink, $collectedLinks));


    }



    /**
     * @param string $format Format text to convert into CSS class
     *
     * @return string
     */

    public function getFormatClass($format)
    {
        if (!($this->driver instanceof \Swissbib\RecordDriver\SolrMarc) || !$this->driver->getUseMostSpecificFormat()) return parent::getFormatClass($format);

        $mediatypesIconsConfig = $this->driver->getServiceLocator()->get('VuFind\Config')->get('mediatypesicons');
        $mediaType = $mediatypesIconsConfig->MediatypesIcons->$format;

        return pathinfo($mediaType, PATHINFO_FILENAME);
    }

    /**
     * @param $titleStatement
     *
     * @return string
     */

    public function getSubtitle($titleStatement)
    {
        if (isset($titleStatement['parts_name'])) {
            $parts_name           = is_array($titleStatement['parts_name']) ? implode('. ', $titleStatement['parts_name']) : $titleStatement['parts_name'];
        }
        if (isset($titleStatement['title_remainder'])) {
            $title_remainder      = $titleStatement['title_remainder'];
        }

        if (!empty($title_remainder) && empty($parts_name))
        {
            return $title_remainder;
        }

        elseif (!empty($title_remainder) && !empty($parts_name))
        {
            return $title_remainder . '. ' . $parts_name;
        }

        elseif (empty($title_remainder) && !empty($parts_name))
        {
            return $parts_name;
        }
    }

    /**
     * @param $titleStatement
     * @param $record
     *
     * @return string
     */
    
    public function getResponsible($titleStatement, $record)
    {
        if (isset($titleStatement['statement_responsibility']))
        {
            return $titleStatement['statement_responsibility'];
        }

        elseif ($record->getPrimaryAuthor(true))
        {
            return $record->getPrimaryAuthor();
        }

        elseif ($record->getSecondaryAuthors(true))
        {
            return implode('; ', $record->getSecondaryAuthors());
        }

        elseif ($record->getCorporationNames(true))
        {
            return implode('; ', $record->getCorporationNames());
        }
    }

    /**
     * Generate a thumbnail URL (return false if unsupported).
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|bool
     */
    public function getThumbnail($size = 'small')
    {
        // Try to build thumbnail:
        $thumb = $this->driver->tryMethod('getThumbnail', array($size));

        // No thumbnail?  Return false:
        if (empty($thumb)) {
            if (!empty ($this->config->Content->externalResourcesServer)) {
                //$urlHelper = $this->getView()->plugin('url');
                //$urlSrc = $urlHelper('cover-unavailable');
                //sometimes our app is not the root domain
                //$position =  strpos($urlSrc,'/Cover');
                //return  $this->config->Content->externalResourcesServer . substr($urlSrc,$position) ;
                return $this->config->Content->externalResourcesServer . "/img/noCover2.gif";

            } else {
                $urlHelper = $this->getView()->plugin('url');
                return $urlHelper('cover-unavailable') ;
            }

        }

        // Array?  It's parameters to send to the cover generator:
        if (is_array($thumb)) {

            if (!empty ($this->config->Content->externalResourcesServer)) {
                $urlHelper = $this->getView()->plugin('url');
                $urlSrc = $urlHelper('cover-show');
                //sometimes our app is not the root domain
                $position =  strpos($urlSrc,'/Cover');
                return  $this->config->Content->externalResourcesServer . substr($urlSrc,$position) .  '?' . http_build_query($thumb);

            } else {

                $urlHelper = $this->getView()->plugin('url');
                return $urlHelper('cover-show') . '?' . http_build_query($thumb);
            }

        }

        // Default case -- return fixed string:
        return $thumb;
    }

}
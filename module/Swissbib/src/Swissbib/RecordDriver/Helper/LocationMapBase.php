<?php
namespace Swissbib\RecordDriver\Helper;

use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;

/**
 * Base class for location map
 * Contains all basic helper methods, to keep them away from the custom implementations in LocationMap
 *
 */
abstract class LocationMapBase extends CustomizedMethods
{
    /**
     * Get a link for an item
     *
     * @param    HoldingsHelper        $holdingsHelper
     * @param    Array                  $item
     * @return    String|Boolean
     */
    public function getLinkForItem(HoldingsHelper $holdingsHelper, array $item)
    {
        if ($this->isItemValidForLocationMap($item, $holdingsHelper)) {
            return $this->buildLocationMapLink($item, $holdingsHelper);
        }

        return false;
    }



    /**
     * Check whether location map link should be shown
     *
     *
     * @param    Array          $item
     * @param    HoldingsHelper $holdingsHelper
     * @return    Boolean
     */
    protected function isItemValidForLocationMap(array $item, HoldingsHelper $holdingsHelper)
    {
        return $this->callMethod('isItemValidForLocationMap', $item['institution'], array($item, $holdingsHelper));
    }



    /**
     * Build link for location map
     *
     * @param   Array           $item
     * @param    HoldingsHelper $holdingsHelper
     * @return    String|Boolean
     */
    protected function buildLocationMapLink(array $item, HoldingsHelper $holdingsHelper)
    {
        return $this->callMethod('buildLocationMapLink', $item['institution'], array($item, $holdingsHelper));
    }



    /**
     * Build simple map link form link pattern and a value for PARAMS placeholder
     * Use this if you don't need a very special behaviour
     *
     * @param    String        $mapLinkPattern
     * @param    String        $paramsValue
     * @return    String
     */
    protected function buildSimpleLocationMapLink($mapLinkPattern, $paramsValue)
    {
        $data = array(
            'PARAMS' => urlencode($paramsValue)
        );

        return $this->templateString($mapLinkPattern, $data);
    }
}

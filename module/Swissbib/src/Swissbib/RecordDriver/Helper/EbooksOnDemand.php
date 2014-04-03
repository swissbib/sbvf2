<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use Swissbib\RecordDriver\SolrMarc;

/**
 * Build ebook links depending on institution configuration
 * Config in config_base.ini[eBooksOnDemand]
 *
 */
class EbooksOnDemand extends EbooksOnDemandBase
{

    /**
     * Check whether A100 item is valid for EOD link
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    Boolean
     */

    protected function isValidForLinkA100(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
        $institutionCode    = $item['institution_chb'];
        $publishYear        = $recordDriver->getPublicationDates();
        $itemFormats        = $recordDriver->getMostSpecificFormat();

        return        $this->isYearInRange($institutionCode, $publishYear)
                &&    $this->isSupportedInstitution($institutionCode)
                &&    $this->isSupportedFormat($institutionCode, $itemFormats)
                &&    $this->hasStopWords($institutionCode, $recordDriver->getLocalCodes()) === false;
    }


    /**
     * Build EOD link for A100 item
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    String
     */

    protected function buildLinkA100(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
         $linkPattern    = $this->getLinkPattern($item['institution_chb']);
        $data    = array(
            'SYSID'            => $item['bibsysnumber'],
            'INSTITUTION'    => urlencode($item['institution_chb'] . $item['signature']),
            'LANGUAGE'        => $this->getConvertedLanguage()
        );

        return $this->templateString($linkPattern, $data);
    }


    /**
     * Check whether B400 item is valid for EOD link
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    Boolean
     */

    protected function isValidForLinkB400(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
            // Works the same way, just forward to A100. But use B400 as institution code
        return $this->isValidForLinkA100($item, $recordDriver, $holdingsHelper);
    }


    /**
     * Build EOD link for B400 item
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    String
     */

    protected function buildLinkB400(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
            // Works the same way, just forward to A100. But use B400 as institution code
        return $this->buildLinkA100($item, $recordDriver, $holdingsHelper);
    }


    /**
     * Check whether Z01 item is valid for EOD link
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    Boolean
     */

    protected function isValidForLinkZ01(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
        return $this->isValidForLinkA100($item, $recordDriver, $holdingsHelper);
    }


    /**
     * Build EOD link for B400 item
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    String
     */

    protected function buildLinkZ01(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
        $linkPattern    = $this->getLinkPattern($item['institution_chb']);
        $data    = array(
            'SYSID'        => $item['bibsysnumber'],
            'CALLNUM'    => urlencode($item['signature'])
        );

        return $this->templateString($linkPattern, $data);
    }


    /**
     * Check whether AX005 item is valid for EOD link
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    Boolean
     */

    protected function isValidForLinkAX5(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
        $institutionCode    = $item['institution_chb'];
        $publishYear        = $recordDriver->getPublicationDates();
        $itemFormats        = $recordDriver->getFormatsRaw();

        return         // $item['location_code'] != 'AX50001' // not this location code
                // &&    stripos($item['signature'], 'BIG') !== 0 // doesn't start with BIG
                    $this->isYearInRange($institutionCode, $publishYear)
                &&    $this->isSupportedInstitution($institutionCode)
                &&    $this->isSupportedFormat($institutionCode, $itemFormats)
                &&    $this->hasStopWords($institutionCode, $recordDriver->getLocalCodes()) === false; // no stop words
    }


    /**
     * Build EOD link for AX005 item
     *
     * @param    Array        $item
     * @param    SolrMarc    $recordDriver
     * @param    Holdings    $holdingsHelper
     * @return    String
     */

    protected function buildLinkAX5(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
    {
        $linkPattern    = $this->getLinkPattern($item['institution_chb']);
        $data    = array(
            'SYSID'            => str_replace('vtls', '', $item['bibsysnumber']),
            'CALLNUM'        => urlencode($item['signature']),
        );

        return $this->templateString($linkPattern, $data);
    }
}
<?php
namespace Swissbib\XSLT;

use \VuFind\XSLT\Processor as VFProcessor;
use \DOMDocument;
use \XSLTProcessor;


class Processor extends VFProcessor
{
    /**
     * Perform an XSLT transformation and return the results.
     *
     * @param string $xslt   Name of stylesheet (in application/xsl directory)
     * @param string $xml    XML to transform with stylesheet
     * @param array  $params Associative array of XSLT parameters
     *
     * @return string      Transformed XML
     */
    public static function process($xslt, $xml, $params = array())
    {
        if ($xslt != 'record-marc.xsl') {
            return parent::process($xslt, $xml, $params);
        }

        $style = new DOMDocument();
        $style->load(APPLICATION_PATH . '/module/Swissbib/xsl/' . $xslt);
        $xsl = new XSLTProcessor();
        $xsl->registerPHPFunctions();
        $xsl->importStyleSheet($style);
        $doc = new DOMDocument();
        if ($doc->loadXML($xml)) {
            foreach ($params as $key => $value) {
                $xsl->setParameter('', $key, $value);
            }

            return $xsl->transformToXML($doc);
        }

        return '';
    }
}
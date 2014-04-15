<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response as HttpResponse;

use Swissbib\RecordDriver\Helper\BibCode as BibCodeHelper;

/**
 * Get availability for items
 *
 */
class Availability
{
    /** @var Config  */
    protected $config;
    /** @var  BibCode */
    protected $bibCodeHelper;



    /**
     * Initialize
     * Build IDLS mapping for networks
     *
     * @param    BibCode        $bibCodeHelper
     * @param    Config        $config
     */
    public function __construct(BibCodeHelper $bibCodeHelper, Config $config)
    {
        $this->config        = $config;
        $this->bibCodeHelper = $bibCodeHelper;
    }



    /**
     * Get availability info
     *
     * @param    String        $sysNumber
     * @param    String        $barcode
     * @param    String        $network
     * @param    String        $locale
     * @return    Array|Boolean
     */
    public function getAvailability($sysNumber, $barcode, $bib, $locale)
    {
        $apiUrl    = $this->getApiUrl($sysNumber, $barcode, $bib, $locale);

//        echo $network . ' : ' . $apiUrl;

        try {
            $responseBody    = $this->fetch($apiUrl);
            $responseData    = json_decode($responseBody, true);
            //the following line could be used to check on json errors (possible trouble with UTF8 encountered)
            //$error          = json_last_error();

            if (is_array($responseData)) {
                return $responseData;
            }

            throw new \Exception('Unknown response data');
        } catch (\Exception $e) {
            return false;
        }
    }



    /**
     * Get IDLS code for network
     *
     * @param    String        $network
     * @return    String
     */
    protected function getIDLS($network)
    {
        return $this->bibCodeHelper->getBibCode($network);
    }



    /**
     * Build API url from params
     *
     * @param    String        $sysNumber
     * @param    String        $barcode
     * @param    String        $idls
     * @param    String        $locale
     * @return    String
     */
    protected function getApiUrl($sysNumber, $barcode, $bib, $locale)
    {
        return     $this->config->apiEndpoint
                . '?sysnumber=' . $sysNumber
                . '&barcode=' . $barcode
                . '&idls=' . $bib
                . '&language=' . $locale;
    }



    /**
     * Download data from server
     *
     * @param    String        $url
     * @return    Array
     * @throws    \Exception
     */
    protected function fetch($url)
    {
        $client = new HttpClient($url, array(
            'timeout'      => 3
        ));
        $client->setOptions(array('sslverifypeer' => false));

        /** @var HttpResponse $response */
        $response = $client->send();

        if ($response->isSuccess()) {
            return $response->getBody();
        } else {
            throw new \Exception('Availability request failed: ' . $response->getReasonPhrase());
        }
    }
}

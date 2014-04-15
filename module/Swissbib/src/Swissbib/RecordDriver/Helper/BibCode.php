<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;

/**
 * Convert network code into bib code
 * Uses holding config for mapping
 *
 */
class BibCode
{
    /** @var  Array */
    protected $network2bib = array();

    protected $bib2network = array();



    /**
     *
     *
     * @param    Config    $alephNetworkConfig
     */
    public function __construct(Config $alephNetworkConfig)
    {
        foreach ($alephNetworkConfig as $networkCode => $info) {
            list($url, $idls) = explode(',', $info);
            $networkCode = strtolower($networkCode);

            $this->network2bib[$networkCode] = strtoupper($idls);
        }

        $this->bib2network = array_flip($this->network2bib);
    }



    /**
     * Get bib code for network code
     *
     * @param    String        $networkCode
     * @return    String
     */
    public function getBibCode($networkCode)
    {
        $networkCode = strtolower($networkCode);

        return isset($this->network2bib[$networkCode]) ? $this->network2bib[$networkCode] : '';
    }



    /**
     * @param $bibCode
     * @return string
     */
    public function getNetworkCode($bibCode)
    {
        $bibCode = strtoupper($bibCode);

        return isset($this->bib2network[$bibCode]) ? $this->bib2network[$bibCode] : '';
    }
}

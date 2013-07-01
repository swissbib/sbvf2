<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response as HttpResponse;

use Swissbib\Helper\BibCode;

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
	 * @param	BibCode		$bibCodeHelper
	 * @param	Config		$config
	 */
	public function __construct(BibCode $bibCodeHelper, Config $config)
	{
		$this->config        = $config;
		$this->bibCodeHelper = $bibCodeHelper;
	}



	/**
	 * Get availability info
	 *
	 * @param	String		$sysNumber
	 * @param	String		$barcode
	 * @param	String		$network
	 * @param	String		$locale
	 * @return	Array|Boolean
	 */
	public function getAvailability($sysNumber, $barcode, $network, $locale)
	{
		$idls	= $this->getIDLS($network);
		$apiUrl	= $this->getApiUrl($sysNumber, $barcode, $idls, $locale);

//		echo $network . ' : ' . $apiUrl;

		try {
			$responseBody	= $this->fetch($apiUrl);
			$responseData	= json_decode($responseBody, true);

			if (is_array($responseData) && isset($responseData[0])) {
				return $responseData[0];
			}

			throw new \Exception('Unknown response data');
		} catch (\Exception $e) {
			return false;
		}
	}



	/**
	 * Get IDLS code for network
	 *
	 * @param	String		$network
	 * @return	String
	 */
	protected function getIDLS($network)
	{
		return $this->bibCodeHelper->getBibCode($network);
	}



	/**
	 * Build API url from params
	 *
	 * @param	String		$sysNumber
	 * @param	String		$barcode
	 * @param	String		$idls
	 * @param	String		$locale
	 * @return	String
	 */
	protected function getApiUrl($sysNumber, $barcode, $idls, $locale)
	{
		return 	$this->config->apiEndpoint
				. '?sysnumber=' . $sysNumber
				. '&barcode=' . $barcode
				. '&idls=' . $idls
				. '&language=' . $locale;
	}



	/**
	 * Download data from server
	 *
	 * @param	String		$url
	 * @return    Array
	 * @throws    \Exception
	 */
	protected function fetch($url)
	{
		$client = new HttpClient($url, array(
							'timeout'      => 3
					   ));

		/** @var HttpResponse $response */
		$response = $client->send();

		if ($response->isSuccess()) {
			return $response->getBody();
		} else {
			throw new \Exception('Availability request failed: ' . $response->getReasonPhrase());
		}
	}
}

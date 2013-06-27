<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response as HttpResponse;

use VuFind\Log\Logger;

/**
 * Get availability for items
 *
 */
class Availability
{
	/** @var Config  */
	protected $config;
	/** @var Array  */
	protected $idlsMap = array();
	/** @var Logger  */
	protected $logger;



	/**
	 * Initialize
	 * Build IDLS mapping for networks
	 *
	 * @param Config $config
	 * @param Config $alephNetworkConfig
	 * @param Logger $logger
	 */
	public function __construct(Config $config, Config $alephNetworkConfig, Logger $logger)
	{
		$this->config = $config;
		$this->logger = $logger;

		foreach ($alephNetworkConfig as $networkCode => $info) {
			list($url, $idls) = explode(',', $info);

			$this->idlsMap[$networkCode] = $idls;
		}
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
		$network = strtolower($network);

		return isset($this->idlsMap[$network]) ? $this->idlsMap[$network] : '';
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
		$client = new HttpClient($url);

		/** @var HttpResponse $response */
		$response = $client->send();

		if ($response->isSuccess()) {
			return $response->getBody();
		} else {
			throw new \Exception('Availability request failed: ' . $response->getReasonPhrase());
		}
	}
}

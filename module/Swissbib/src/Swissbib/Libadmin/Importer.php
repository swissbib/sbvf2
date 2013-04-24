<?php
namespace Swissbib\Libadmin;

use Zend\Config\Config;
use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Swissbib\Libadmin\Exception as Exceptions;

/**
 * Libadmin data importer
 * Fetch data from libadmin api and store in local files
 *
 */
class Importer implements ServiceLocatorAwareInterface
{

	protected $serviceLocator;

	protected $debugActive = true;

	protected $cacheDir;

	/**
	 * @var    Config
	 */
	protected $config;



	public function __construct(Config $config)
	{
		$this->config      = $config;
		$this->debugActive = !!$this->config->Settings->debug;
		$this->cacheDir    = realpath(APPLICATION_PATH . '/data/cache');
	}



	/**
	 * Import data from libadmin api
	 *
	 * @param    Boolean        $dryRun
	 * @return    Result
	 */
	public function import($dryRun = false)
	{
		$result = new Result();

		$result->addMessage('Start import');

		try {
			$importData = $this->getData();

			$result->addMessage('Data fetched from libadmin');
		} catch (Exceptions\Exception $e) {
			return $result->addError($e->getMessage());
		}

		if (!$dryRun) {
			try {
				$storeResult = $this->storeData($importData['data']);

				$result->addMessage('Data stored in local system');
			} catch (Exceptions\Store $e) {
				return $result->addError($e->getMessage());
			}
		} else {
			$result->addMessage('Skipped storing of data on local system (dry run)');
		}

		$result->addMessage('Import completed');

		return $result;
	}



	protected function storeData(array $data)
	{
		$this->storeInstitutionLabels($data);
		$this->storeLibraryInfoLinks($data);
		$this->storeGroupLabels($data);

		return true;
	}



	protected function storeInstitutionLabels(array $data)
	{

	}



	protected function storeLibraryInfoLinks(array $data)
	{

	}



	protected function storeGroupLabels(array $data)
	{

	}



	/**
	 * Download data from server
	 *
	 * @return    String
	 * @throws    Exceptions\Fetch
	 */
	protected function download()
	{
		$url    = $this->getApiEndpointUrl();
		$client = new HttpClient($url);

		/** @var Response $response */
		$response = $client->send();

		if ($response->isSuccess()) {
			$responseBody = $response->getBody();

			if (!$this->storeDownloadedData($responseBody)) {
				throw new Exceptions\Fetch('Was not able to store downloaded data in a local cache (data/cache/libadmin.json');
			}

			return $responseBody;
		} else {
			throw new Exceptions\Fetch('Request failed: ' . $response->getReasonPhrase());
		}
	}



	/**
	 * Save downloaded response
	 * Just for history and debugging
	 *
	 * @param    String        $responseBody
	 * @return    Boolean
	 */
	protected function storeDownloadedData($responseBody)
	{
		if ($this->cacheDir && is_writable($this->cacheDir)) {
			$cacheFile = $this->cacheDir . '/libadmin.json';

			return file_put_contents($cacheFile, $responseBody) !== false;
		}

		return false;
	}



	/**
	 * @return    Array
	 * @throws Exception\Data
	 * @throws Exception\Fetch
	 */
	protected function getData()
	{
		$jsonString = $this->download();
		$data       = json_decode($jsonString, true);

		if (is_null($data) || !is_array($data)) {
			throw new Exceptions\Fetch('Received data is invalid');
		}

		if (!isset($data['success']) || !isset($data['data'])) {
			throw new Exceptions\Data('Unknown data format');
		}

		if ($data['success'] !== true) {
			throw new Exceptions\Data('Server reported failed request');
		}

		return $data;
	}



	protected function getApiEndpointUrl()
	{
		return $this->config->Server->uri . '/' . $this->config->Server->api . '/' . $this->config->Server->path;
	}



	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}



	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}



	/**
	 * Write debug message if active
	 *
	 * @param    String        $message
	 */
	protected function debug($message)
	{
		if ($this->debugActive) {
			echo $message . "\n";
		}
	}
}

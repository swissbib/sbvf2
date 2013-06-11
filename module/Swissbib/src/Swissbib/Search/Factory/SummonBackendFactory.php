<?php

/**
 * Adapted factory for summon backend.
 * Contains proxy detection and resp. switching of API key.
 */

namespace Swissbib\Search\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

use VuFind\Search\Factory\SummonBackendFactory as SummonBackendFactoryBase;
use SerialsSolutions\Summon\Zend2 as Connector;

use Swissbib\TargetsProxy\TargetsProxy;

/**
 * Factory for Summon backends.
 */
class SummonBackendFactory extends SummonBackendFactoryBase
{

	/**
	 * Superior service manager.
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	/**
	 * VuFind configuration
	 *
	 * @var \Zend\Config\Config
	 */
	protected $config;

	/**
	 * Summon configuration
	 *
	 * @var \Zend\Config\Config
	 */
	protected $summonConfig;



	/**
	 * Create the Summon connector.
	 * Detects clients relevant for target switching by IP and hostname
	 *
	 * @return Connector
	 */
	protected function createConnector()
	{
		// Load credentials:
		$id  = isset($this->config->Summon->apiId) ? $this->config->Summon->apiId : null;
		$key = isset($this->config->Summon->apiKey) ? $this->config->Summon->apiKey : null;

		$overrideCredentials = $this->getOverrideApiCredentialsFromProxy();
		if ($overrideCredentials !== false) {
			$id  = array_key_exists('apiId', $overrideCredentials) && !empty($overrideCredentials['apiId']) ? $overrideCredentials['apiId'] : $id;
			$key = array_key_exists('apiKey', $overrideCredentials) && !empty($overrideCredentials['apiKey']) ? $overrideCredentials['apiKey'] : $key;
		}

		// Build HTTP client:
		$client  = $this->serviceLocator->get('VuFind\Http')->createClient();
		$timeout = isset($this->summonConfig->General->timeout) ? $this->summonConfig->General->timeout : 30;
		$client->setOptions(array('timeout' => $timeout));

		$connector = new Connector($id, $key, array(), $client);
		$connector->setLogger($this->logger);

		return $connector;
	}



	/**
	 * Detect client to possibly switch API key from proxy configuration
	 *
	 * @todo	Use exception handling instead of custom error handling!
	 * @return    Boolean|String        false or the API key to switch to
	 */
	protected function getOverrideApiCredentialsFromProxy()
	{
		try {
			/** @var TargetsProxy $targetsProxy */
			$targetsProxy = $this->serviceLocator->get('Swissbib\TargetsProxy\TargetsProxy');
			$targetsProxy->setSearchClass('Summon');

			$proxyDetected = $targetsProxy->detectTarget();
//			$proxyDetected = $targetsProxy->detectTarget('99.0.0.0', 'snowflake.ch');

			if ($proxyDetected !== false) {
				return array(
					'apiId'  => $targetsProxy->getTargetApiId(),
					'apiKey' => $targetsProxy->getTargetApiKey()
				);
			}
		} catch (\Exception $e) {
			// handle exceptions
			echo "- Fatal error\n";
			echo "- Stopped with exception: " . get_class($e) . "\n";
			echo "====================================================================\n";
			echo $e->getMessage() . "\n";
			echo $e->getPrevious()->getMessage() . "\n";
		}

		return false;
	}
}

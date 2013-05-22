<?php

/**
 * Adapted factory for summon backend.
 * Contains proxy detection and resp. switching of API key.
 */

namespace Swissbib\Search\Factory;

use VuFindSearch\Backend\BackendInterface;
use VuFindSearch\Backend\Summon\Response\RecordCollectionFactory;
use VuFindSearch\Backend\Summon\QueryBuilder;
use VuFindSearch\Backend\Summon\Connector;
use VuFindSearch\Backend\Summon\Backend;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

use Swissbib\TargetsProxy\TargetsProxy;



/**
 * Factory for Summon backends.
 */
class SummonBackendFactory extends \VuFind\Search\Factory\SummonBackendFactory
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
        $id = isset($this->config->Summon->apiId) 	? $this->config->Summon->apiId : null;
        $key = isset($this->config->Summon->apiKey)	? $this->config->Summon->apiKey : null;

		$overrideKey	= $this->getOverrideApiKeyFromProxy();
		if( $overrideKey !== false ) {
			$key	= $overrideKey;
		}

        // Build HTTP client:
        $client = $this->serviceLocator->get('VuFind\Http')->createClient();
		$timeout = isset($this->summonConfig->General->timeout)	? $this->summonConfig->General->timeout : 30;
        $client->setOptions(array('timeout' => $timeout));

        $connector = new Connector($id, $key, array(), $client);
        $connector->setLogger($this->logger);
        return $connector;
    }


	/**
	 * Detect client to possibly switch API key from proxy configuration
	 *
	 * @return	Boolean|String		false or the API key to switch to
	 */
	protected function getOverrideApiKeyFromProxy() {
		try {
			/** @var TargetsProxy $targetsProxy */
			$targetsProxy = $this->serviceLocator->get('Swissbib\TargetsProxy\TargetsProxy');
			$targetsProxy->setSearchClass('Summon');

			$proxyDetected = $targetsProxy->detectTarget();
//			$proxyDetected = $targetsProxy->detectTarget('171.0.1.5', 'unibas.swissbib.ch');

			if( $proxyDetected !== false ) {
				return $targetsProxy->getTargetApiKey();
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
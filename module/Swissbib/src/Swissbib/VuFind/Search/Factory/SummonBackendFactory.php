<?php

/**
 * Adapted factory for summon backend.
 * Contains proxy detection and resp. switching of API key.
 */

namespace Swissbib\VuFind\Search\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;
use Zend\Http\Client as HttpClient;

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
     * @var Config
     */
    protected $config;

    /**
     * Summon configuration
     *
     * @var Config
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
            if (isset($overrideCredentials['apiId']) && !empty($overrideCredentials['apiId'])) {
                $id = $overrideCredentials['apiId'];
            }
            if (isset($overrideCredentials['apiKey']) && !empty($overrideCredentials['apiKey'])) {
                $key = $overrideCredentials['apiKey'];
            }
        }

        /** @var HttpClient $client */
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
     * @return    Boolean|String        false or the API key to switch to
     */
    protected function getOverrideApiCredentialsFromProxy()
    {
        $targetsProxy = $this->serviceLocator->get('Swissbib\TargetsProxy\TargetsProxy');
        $targetsProxy->setSearchClass('Summon');
        $proxyDetected = $targetsProxy->detectTarget();
        if ($proxyDetected !== false) {
            return array(
                'apiId'  => $targetsProxy->getTargetApiId(),
                'apiKey' => $targetsProxy->getTargetApiKey()
            );
        }
        return false;
    }
}

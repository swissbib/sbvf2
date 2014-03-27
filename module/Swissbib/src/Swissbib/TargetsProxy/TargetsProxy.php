<?php
namespace Swissbib\TargetsProxy;

use Zend\Config\Config;
use Zend\Di\ServiceLocator;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\Http\PhpEnvironment\Request;

use Swissbib\TargetsProxy\IpMatcher;
use Zend\Log\Logger as ZendLogger;

/**
 * Targets proxy
 * Analyze connection parameters (IP address + requested hostname) and switch target config respectively
 */
class TargetsProxy implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var string
     */
    protected $searchClass    = 'Summon';
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var    String
     */
    protected $clientIp;

    /**
     * @var \Zend\Uri\Http
     */
    protected $clientUri;


    /**
     * @var    Boolean|String
     */
    protected $targetKey    = false;

    /**
     * @var    Boolean|String
     */
    protected $targetApiKey    = false;

    /**
     * @var    Boolean|String
     */
    protected $targetApiId = false;

    protected $logger;



    /**
     * Initialize proxy with config
     *
     * @param    Config    $config
     */
    public function __construct(Config $config,ZendLogger $logger, Request $request )
    {
        $this->config   = $config;
        $this->logger   = $logger;
        $trustedProxies = explode(',', $this->config->get('TrustedProxy')->get('loadbalancer'));

        // Populate client info properties from request
        $RemoteAddress    = new RemoteAddress();
        $RemoteAddress->setUseProxy();
        $RemoteAddress->setTrustedProxies($trustedProxies);
        $ipAddress        = $RemoteAddress->getIpAddress();
        $this->clientIp    = array(
            'IPv4'    => $ipAddress,            // i.e.: aaa.bbb.ccc.ddd - standard dotted format
        );
        $Request    = new Request();
        $this->clientUri= $Request->getUri();


        //todo: make it configurable in case you want logging of headers

        //foreach ($_SERVER as $key => $value) {
        //    $this->logger->debug("_SERVER_KEY: " . " . $key => " . $value);
        //}

        //foreach($request->getHeaders() as $header) {

            //now log the headers -> todo: read the manual rlated to ZF2 headers (there are different header types...)


        //}

    }

    /**
     * @param string $className
     */
    public function setSearchClass($className = 'Summon') {
        $this->searchClass    = $className;
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
     * @return    Array
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @return    String    Client IP address in IPv4 notation (standard dotted format), i.e.: aaa.bbb.ccc.ddd
     */
    public function getClientIpV4()
    {
        return $this->clientIp['IPv4'];
    }

    /**
     * @return \Zend\Uri\Http
     */
    public function getClientUrl()
    {
        return $this->clientUri;
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getConfig()
    {
        return $this->config;
    }



    /**
     * Check whether an IP address matches the given IP pattern
     *
     * @param    String        $ipAddress
     * @param    String        $allowPatterns        List of allow-patterns, possible types: IP / IP wildcard / IP mask / IP section
     * @return    Boolean
     */
    private function isMatchingIp($ipAddress, $allowPatterns)
    {
        try {
            /**
             * @var    \Swissbib\TargetsProxy\IpMatcher    $IpMatcher
             */
            $IpMatcher        = $this->getServiceLocator()->get('Swissbib\TargetsProxy\IpMatcher');
            $allowPatterns    = explode(',', $allowPatterns);
            $matches        = $IpMatcher->isMatching($ipAddress, $allowPatterns);

        } catch (\Exception $e) {
            // handle exceptions
            echo "- Fatal error\n";
            echo "- Stopped with exception: " . get_class($e) . "\n";
            echo "====================================================================\n";
            echo $e->getMessage() . "\n";
            echo $e->getPrevious()->getMessage() . "\n";

            return false;
        }

        return $matches;
    }


    /**
     * Get target to be used for the client's IP range + sub domain
     *
     * @param   String     $overrideIP      Simulate request from given instead of detecting real IP
     * @param   String       $overrideHost    Simulate request from given instead of detecting from real URL
     * @return    Boolean   Target detected or not?
     */
    public function detectTarget($overrideIP = '', $overrideHost = '')
    {
        $this->targetKey    = false;    // Key of detected target config
        $this->targetApiId    = false;
        $this->targetApiKey    = false;

        $targetKeys    = explode(',', $this->config->get('TargetsProxy')->get('targetKeys' . $this->searchClass));
            // Check whether the current IP address matches against any of the configured targets' IP / sub domain patterns
        $ipAddress    = !empty($overrideIP) ? $overrideIP : $this->getClientIpV4();
        if( empty($overrideHost) ) {
            $url        = $this->getClientUrl();
        } else {
            $url        = new \Zend\Uri\Http();
            $url->setHost($overrideHost);
        }

        $IpMatcher    = new IpMatcher();
        $UrlMatcher    = new UrlMatcher();

        foreach($targetKeys as $targetKey) {
            $isMatchingIP    = false;
            $isMatchingUrl    = false;

            /** @var    \Zend\Config\Config    $targetConfig */
            $targetConfig    = $this->config->get($targetKey);
            $patternsIP        = '';
            $patternsURL    = '';

                // Check match of IP address if any pattern configured.
                // If match is found, set corresponding keys and continue matching
            if ($targetConfig->offsetExists('patterns_ip')) {
                $patternsIP    = $targetConfig->get('patterns_ip');
                if( !empty($patternsIP) ) {
                    $targetPatternsIp    = explode(',', $patternsIP);
                    $isMatchingIP    = $IpMatcher->isMatching($ipAddress, $targetPatternsIp);
                    if ( $isMatchingIP === true ) {
                        $this->setConfigKeys($targetKey);
                    }
                }
            }
                // Check match of URL hostname if any pattern configured.
                // If match is found, set corresponding keys and exit immediately
            if ($targetConfig->offsetExists('patterns_url')) {
                $patternsURL    = $targetConfig->get('patterns_url');
                if( !empty($patternsURL) ) {
                    $targetPatternsUrl    = explode(',', $patternsURL);
                    $isMatchingUrl        = $UrlMatcher->isMatching($url->getHost(), $targetPatternsUrl);
                    if ( $isMatchingUrl === true ) {
                        $this->setConfigKeys($targetKey);
                        return true;
                    }
                }
            }
        }
        return ( $this->targetKey != ""  ? true : false );
    }

    /**
     * Set relevant keys from the target key section in config.ini
     *
     * @param $targetKey
     * @return void
     */
    private function setConfigKeys($targetKey)
    {
        $this->targetKey = $targetKey;
        $vfConfig = $this->serviceLocator->get('VuFind\Config')->get('config')->toArray();
        $this->targetApiId    = $vfConfig[$this->targetKey]['apiId'];
        $this->targetApiKey    = $vfConfig[$this->targetKey]['apiKey'];
    }


    /**
     * Get key of detected target to be rerouted to
     *
     * @return bool|String
     */
    public function getTargetKey()
    {
        return $this->targetKey;
    }

    /**
     * @return bool|String
     */
    public function getTargetApiKey()
    {
        return $this->targetApiKey;
    }

    /**
     * @return bool|String
     */
    public function getTargetApiId()
    {
        return $this->targetApiId;
    }

}
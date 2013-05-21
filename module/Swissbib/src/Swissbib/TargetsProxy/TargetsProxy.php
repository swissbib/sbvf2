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

/**
 * Targets proxy
 * Analyze connection parameters (IP address + requested sub domain) and switch target config respectively
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
	protected $searchClass	= 'Summon';
	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var	String
	 */
	protected $clientIp;

	/**
	 * @var \Zend\Uri\Http
	 */
	protected $clientUri;


	/**
	 * @var	Boolean|String
	 */
	protected $targetKey	= false;

	/**
	 * @var	Boolean|String
	 */
	protected $targetApiKey	= false;



	/**
	 * Initialize proxy with config
	 *
	 * @param Config           $config
	 */
	public function __construct(Config $config)
	{
		$this->config        = $config;

			// Populate client info properties from request
		$RemoteAddress	= new RemoteAddress();
		$ipAddress		= $RemoteAddress->getIpAddress();
		$this->clientIp	= array(
			'IPv4'	=> $ipAddress,			// i.e.: x:x:x:x:x:x:x:x - up to 8 colon separated segments
			'IPv6'	=> long2ip($ipAddress),	// i.e.: aaa.bbb.ccc.ddd - standard dotted format
		);

		$Request	= new Request();
		$this->clientUri= $Request->getUri();
	}

	/**
	 * @param string $className
	 */
	public function setSearchClass($className = 'Summon') {
		$this->searchClass	= $className;
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
	 * @return	Array
	 */
	public function getClientIp()
	{
		return $this->clientIp;
	}

	/**
	 * @return	String	Client IP address in IPv4 notation (up to 8 colon separated segments), i.e.: x:x:x:x:x:x:x:x
	 */
	public function getClientIpV4()
	{
		return $this->clientIp['IPv4'];
	}

	/**
	 * @return	String	Client IP address in IPv6 notation (standard dotted format), i.e.: aaa.bbb.ccc.ddd
	 */
	public function getClientIpV6()
	{
		return $this->clientIp['IPv6'];
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
	 * @param	String		$ipAddress
	 * @param	String		$allowPatterns		List of allow-patterns, possible types: IP / IP wildcard / IP mask / IP section
	 * @return	Boolean
	 */
	private function isMatchingIp($ipAddress, $allowPatterns)
	{
		try {
			/**
			 * @var	\Swissbib\TargetsProxy\IpMatcher	$IpMatcher
			 */
			$IpMatcher		= $this->getServiceLocator()->get('Swissbib\TargetsProxy\IpMatcher');
			$allowPatterns	= explode(',', $allowPatterns);
			$matches		= $IpMatcher->isMatching($ipAddress, $allowPatterns);

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
	 * @param   String	   $overrideHost    Simulate request from given instead of detecting from real URL
	 * @return	Boolean   Target detected or not?
	 */
	public function detectTarget($overrideIP = '', $overrideHost = '')
	{
		$this->targetKey = false;
		$this->targetApiKey	= false;

		$targetKeys	= explode(',', $this->config->get('targetKeys' . $this->searchClass));

			// Check whether the current IP address matches against any of the configured targets' IP / sub domain patterns
		$ipAddress	= !empty($overrideIP) ? $overrideIP : $this->getClientIpV6();
		if( empty($overrideHost) ) {
			$url		= $this->getClientUrl();
		} else {
			$url		= new \Zend\Uri\Http();
			$url->setHost($overrideHost);
		}

		$vfConfig	= $this->getServiceLocator()->get('VuFind\Config');

		$IpMatcher	= new IpMatcher();
		$UrlMatcher	= new UrlMatcher();


		foreach($targetKeys as $targetKey) {
			$isMatchingIP	= false;
			$isMatchingUrl	= false;

			/** @var	\Zend\Config\Config	$targetConfig */
			$targetConfig		= $vfConfig->get('TargetsProxy')->get($targetKey);

			$ipPatterns	= $targetConfig->get('patterns_ip');
			if( !empty($ipPatterns) ) {
				$targetPatternsIp	= explode(',', $ipPatterns);
				$isMatchingIP	= $IpMatcher->isMatching($ipAddress, $targetPatternsIp);
			}

			$urlPatterns	= $targetConfig->get('patterns_url');
			if( !empty($ipPatterns) ) {
				$targetPatternsUrl	= explode(',', $urlPatterns);
				$isMatchingUrl		= $UrlMatcher->isMatching($url->getHost(), $targetPatternsUrl);
			}

			if(     (empty($ipPatterns) || $isMatchingIP === true)
				 && (empty($urlPatterns) || $isMatchingUrl === true) ) {
					// Matching target config detected
				$this->targetKey = $targetKey;
				$this->targetApiKey	= $targetConfig->get('apiKey');

				return true;
			}
		}

		return false;
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

}

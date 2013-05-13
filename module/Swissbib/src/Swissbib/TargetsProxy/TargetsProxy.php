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
	 * @var Config
	 */
	protected $config;

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
		$this->clientIpAddress	= $RemoteAddress->getIpAddress();

		$Request	= new Request();
		$this->clientUri= $Request->getUri();
	}



	/**
	 * Get target to be used for IP range + sub domain of connecting party
	 *
	 * @return    Array
	 */
	public function getTarget()
	{

		return array();
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

}

<?php
namespace SwissbibTest\TargetsProxy;

use VuFindTest\Unit\TestCase as VuFindTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Log\Logger;
use Zend\Http\PhpEnvironment\Request;
use Zend\Config\Config;

use Swissbib\TargetsProxy\TargetsProxy;

/**
 * [Description]
 *
 */
class TargetsProxyTestCase extends VuFindTestCase
{

	/**
	 * @var    TargetsProxy
	 */
	protected $targetsProxy;



	/**
	 * Initialize targets proxy
	 *
	 * @param    String        $configFile
	 */
	public function initialize($configFile)
	{
		if (!$this->targetsProxy) {
			$_SERVER['REMOTE_ADDR'] = '1.1.1.1';
			$iniReader	= new \Zend\Config\Reader\Ini();
			$config	= new Config($iniReader->fromFile($configFile));
			$serviceLocator = new ServiceManager();
			$serviceLocator->setService('VuFind\Config',$config);
			$this->targetsProxy = new TargetsProxy($config, new Logger(),new Request());
			$this->targetsProxy->setSearchClass('Summon');
			$this->targetsProxy->setServiceLocator($serviceLocator);
		}
	}

}

<?php
namespace SwissbibTest\TargetsProxy;

use VuFindTest\Unit\TestCase as VuFindTestCase;
use Zend\ServiceManager\ServiceManager;

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
			$iniReader	= new \Zend\Config\Reader\Ini();
			$config	= new \Zend\Config\Config($iniReader->fromFile($configFile));
			$this->targetsProxy = new TargetsProxy($config);
			$this->targetsProxy->setSearchClass('Summon');
		}
	}

}

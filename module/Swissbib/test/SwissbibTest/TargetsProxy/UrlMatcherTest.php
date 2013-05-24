<?php
namespace SwissbibTest\TargetsProxy;

use SwissbibTest\TargetsProxy\TargetsProxyTestCase;

/**
 * [Description]
 *
 */
class UrlMatcherTest extends TargetsProxyTestCase
{

	public function setUp()
	{
		$path	= getcwd() . '/SwissbibTest/TargetsProxy';
		$this->initialize($path . '/config_detect_url.ini');
	}

	/**
	 * Test single IP address to NOT match
	 */
	public function testUrlFalse()
	{
		$proxyDetected = $this->targetsProxy->detectTarget('99.1.99.1', 'thiswillnotmat.ch');
//		$k=$this->targetsProxy->getTargetKey();

		$this->assertInternalType('bool', $proxyDetected);
		$this->assertFalse($proxyDetected);
	}

	/**
	 * Test single hostname
	 */
	public function testUrlSb()
	{
		$proxyDetected = $this->targetsProxy->detectTarget('192.128.0.1', 'swissbib');

		$this->assertInternalType('bool', $proxyDetected);
		$this->assertTrue($proxyDetected);
		$this->assertEquals('Target_URL_SBch', $this->targetsProxy->getTargetKey());
		$this->assertEquals('apiKeyUrlSbch', $this->targetsProxy->getTargetApiKey());
	}

	/**
	 * Test hostname matching comparing a CSV of values
	 */
	public function testUrlBobCSV()
	{
		$proxyDetected = $this->targetsProxy->detectTarget('192.128.0.1', 'swissbob');

		$this->assertInternalType('bool', $proxyDetected);
		$this->assertTrue($proxyDetected);
		$this->assertEquals('Target_URL_Bobch_CSV', $this->targetsProxy->getTargetKey());
		$this->assertEquals('apiKeyUrlBobchCSV', $this->targetsProxy->getTargetApiKey());
	}

}

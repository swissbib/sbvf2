<?php
namespace SwissbibTest\TargetsProxy;

use SwissbibTest\TargetsProxy\TargetsProxyTestCase;

/**
 * Test detection of targets from combined match patterns (IP + URL)
 *
 */
class CombinedMatcherTest extends TargetsProxyTestCase
{

	public function setUp()
	{
		$path	= getcwd() . '/SwissbibTest/TargetsProxy';
		$this->initialize($path . '/config_detect_combined.ini');
	}

	/**
	 * Test single IP address to NOT match
	 */
	public function testBothFail()
	{
		$proxyDetected = $this->targetsProxy->detectTarget('1.2.3.4', 'swiishbiib.ch');

		$this->assertInternalType('bool', $proxyDetected);
		$this->assertFalse($proxyDetected);
	}




	/**
	 * Test single hostname
	 */
	public function testUrlSb()
	{
		$proxyDetected = $this->targetsProxy->detectTarget('200.20.0.4', 'swsb');

		$this->assertInternalType('bool', $proxyDetected);
		$this->assertTrue($proxyDetected);
		$this->assertEquals('Target_Both_Match', $this->targetsProxy->getTargetKey());
		$this->assertEquals('apiKeyBothMatch', $this->targetsProxy->getTargetApiKey());
	}

}

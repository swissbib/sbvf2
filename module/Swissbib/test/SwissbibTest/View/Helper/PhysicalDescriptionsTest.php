<?php
namespace SwissbibTest\View\Helper;

use Swissbib\View\Helper\PhysicalDescriptions;

/**
 * [Description]
 *
 */
class PhysicalDescriptionsTest extends \PHPUnit_Framework_TestCase
{

	public function testEmpty()
	{
		$desc = new PhysicalDescriptions();
		$data = array();

		$result = $desc($data);

		$this->assertInternalType('string', $result);
		$this->assertEmpty($result);
	}



	public function testNormal()
	{
		$desc = new PhysicalDescriptions();
		$data = array(
			array(
				'extent'  => array(
					'a',
					'b'
				),
				'details' => 'c',
				'unknown' => 'x'
			)
		);

		$result = $desc($data);

		$this->assertInternalType('string', $result);
		$this->assertEquals('a; b; c', $result);
	}
}

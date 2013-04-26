<?php
namespace SwissbibTest\View\Helper;

use Swissbib\View\Helper\Number;

class NumberTest extends \PHPUnit_Framework_TestCase
{

	public function testInvokeLarge()
	{
		$number   = new Number();
		$input    = 123456;
		$expected = '123\'456';
		$output   = $number($input);

		$this->assertEquals($expected, $output);
	}



	public function testInvokeSmall()
	{
		$number   = new Number();
		$input    = 123;
		$expected = '123';
		$output   = $number($input);

		$this->assertEquals($expected, $output);
	}
}

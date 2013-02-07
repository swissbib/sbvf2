<?php
namespace SwissbibTest;

use Swissbib\View\Helper\Number;

class NumberTest extends \PHPUnit_Framework_TestCase {

	public function testInvoke() {
		$number		= new Number();
		$input		= 123456;
		$expected	= '123\'456';
		$output		= $number($input);

		$this->assertEquals($expected, $output);
	}

}
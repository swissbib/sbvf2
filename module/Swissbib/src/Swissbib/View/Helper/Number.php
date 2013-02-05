<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config as ZendConfig;

class Number extends AbstractHelper {

	public function __invoke($number) {
		return number_format($number, 0, '', '\'');
	}

}
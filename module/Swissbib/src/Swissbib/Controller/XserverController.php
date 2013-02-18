<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Swissbib\XServer\Client as XServerClient;

use Swissbib\XServer\Exception\Exception as xException;

class XserverController extends AbstractActionController {

	public function testAction() {
		$client	= new XServerClient('http://alephtest.unibas.ch:8991/X');
		$client->setCredentials('VUFIND', 'VUFIND');

		try {
			$userKey	= $client->getUserID();
		} catch(xException $e) {
			die($e->getMessage());
		}

		$view = new ViewModel(array(
			'userkey' => $userKey,
		));

		// Disable layouts; `MvcEvent` will use this View Model instead
		$view->setTerminal(true);

		return $view;
	}

}
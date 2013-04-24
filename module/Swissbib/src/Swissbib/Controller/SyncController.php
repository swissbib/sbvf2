<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Swissbib\Libadmin\Importer;

/**
 * [Description]
 *
 */
class SyncController extends AbstractActionController
{

	public function indexAction()
	{
		/** @var Importer $importer */
		$importer = $this->getServiceLocator()->get('Swissbib\Libadmin\Importer');

		$result = $importer->import();

		echo "Import status: " . ($result->isSuccess() ? 'Success' : 'Failed') . "\n";

		if ($result->hasErrors()) {
			echo "Errors:\n";
			foreach ($result->getErrors() as $error) {
				echo ' - ' . $error . "\n";
			}
		}

		if ($result->hasMessages()) {
			echo "Messages:\n";
			foreach ($result->getMessages() as $message) {
				echo ' - ' . $message . "\n";
			}
		}
	}
}

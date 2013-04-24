<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

use Swissbib\Libadmin\Importer;

/**
 * [Description]
 *
 */
class LibadminSyncController extends AbstractActionController
{

	/**
	 * Synchronize with libadmin system
	 *
	 * @throws \RuntimeException
	 */
	public function syncAction()
	{
		$request = $this->getRequest();

		if (!$request instanceof ConsoleRequest) {
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$verbose    = $request->getParam('verbose', false) || $request->getParam('v', false);
		$showResult = $request->getParam('result', false) || $request->getParam('r', false);
		$dryRun     = $request->getParam('dry', false) || $request->getParam('d', false);

		/** @var Importer $importer */
		$importer = $this->getServiceLocator()->get('Swissbib\Libadmin\Importer');

		$result = $importer->import($dryRun);

		if ($verbose) {
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

		if (!$verbose || $showResult) {
			echo $result->isSuccess() ? 1 : 0;
		}
	}
}

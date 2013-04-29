<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

use Swissbib\Libadmin\Importer;

/**
 * Synchronize VuFind with LibAdmin
 * Import data into local files
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
		$result   = $importer->import($dryRun);
		$hasErrors= $result->hasErrors();


			// Show all messages?
		if ($verbose || $hasErrors) {
			foreach ($result->getFormattedMessages() as $message) {
				echo '- ' . $message . "\n";
			}
		}

			// No messages printed, but result required?
		if (!$verbose && $showResult) {
			echo $result->isSuccess() ? 1 : 0;
		}
	}
}

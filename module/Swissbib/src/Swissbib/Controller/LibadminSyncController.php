<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

use Swissbib\Libadmin\Importer;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

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
        try {
            $importer = $this->getServiceLocator()->get('Swissbib\Libadmin\Importer');
            $result   = $importer->import($dryRun);
            $hasErrors= $result->hasErrors();
        } catch (ServiceNotCreatedException $e) {
                // handle service exception
            echo "- Fatal error\n";
            echo "- Stopped with exception: " . get_class($e) . "\n";
            echo "====================================================================\n";
            echo $e->getMessage() . "\n";
            echo $e->getPrevious()->getMessage() . "\n";

            return false;
        }



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

        return '';
    }

    public function syncMapPortalAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $verbose    = $request->getParam('verbose', false) || $request->getParam('v', false);
        $showResult = $request->getParam('result', false) || $request->getParam('r', false);
        //$dryRun     = $request->getParam('dry', false) || $request->getParam('d', false);
        $path     = $request->getParam('path','mapportal/green.json');

        /** @var Importer $importer */
        try {
            $importer = $this->getServiceLocator()->get('Swissbib\Libadmin\Importer');
            $result   = $importer->importMapPortalData($path);
            $hasErrors= $result->hasErrors();
        } catch (ServiceNotCreatedException $e) {
            // handle service exception
            echo "- Fatal error\n";
            echo "- Stopped with exception: " . get_class($e) . "\n";
            echo "====================================================================\n";
            echo $e->getMessage() . "\n";
            echo $e->getPrevious()->getMessage() . "\n";

            return false;
        }

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


        // Show all messages?

        return '';
    }

}

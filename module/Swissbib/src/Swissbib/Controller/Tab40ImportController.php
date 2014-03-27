<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

use Swissbib\Tab40Import\Importer as Tab40Importer;


/**
 * Import tab40.xxx files and convert them to label files
 * Use this controller over the command line
 *
 */
class Tab40ImportController extends AbstractActionController
{

    /**
     * Import file as label data
     *
     * @return    String
     * @throws    \RuntimeException
     */
    public function importAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $network    = $request->getParam('network');
        $locale        = $request->getParam('locale');
        $sourceFile = $request->getParam('source');

        $importResult    = $this->getImporter()->import($network, $locale, $sourceFile);

        echo "Imported language data from tab40 file\n";
        echo "Source: $sourceFile\n";
        echo "Network: $network\n";
        echo "Locale: $locale\n";
        echo "\nResult:\n";
        echo "Written File: {$importResult->getFilePath()}\n";
        echo "Items imported: {$importResult->getRecordCount()}\n";

        return '';
    }



    /**
     *
     *
     * @return    Tab40Importer
     */
    protected function getImporter()
    {
        return $this->getServiceLocator()->get('Swissbib\Tab40Importer');
    }
}

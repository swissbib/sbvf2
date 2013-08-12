<?php
namespace Swissbib\Controller;

use Zend\Mvc\Exception;
use Zend\View\Model\ViewModel;

use Swissbib\Controller\BaseController;
use Swissbib\RecordDriver\SolrMarc;
use Swissbib\VuFind\ILS\Driver\Aleph;
use Swissbib\Helper\BibCode;
use Swissbib\RecordDriver\Helper\Holdings;

/**
 * Serve holdings data (items and holdings) for solr records over ajax
 *
 */
class ShibtestController extends BaseController
{


    public function shibAction() {

        $serverArray = [];

        foreach($_SERVER as $key => $value) {
            $serverArray[$key] = $value;
        }

        return $this->createViewModel(


            array ('serverVariables' =>  $serverArray)

        );




    }

}

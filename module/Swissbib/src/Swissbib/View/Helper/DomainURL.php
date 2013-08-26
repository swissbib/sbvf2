<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Http\Request;

/**
 * get the basic domain for view script
 *
 */
class DomainURL extends AbstractHelper
{



    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function normalize() {

        return $this->request->getUri()->normalize();

    }

    public function basePath () {

        return $this->request->getBasePath();
    }


    public function getRefererURL() {

        return  $this->request->getServer('HTTP_REFERER');

    }

}



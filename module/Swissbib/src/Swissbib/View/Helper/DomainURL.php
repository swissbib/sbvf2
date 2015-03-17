<?php
namespace Swissbib\View\Helper;

use Zend\Mvc\Router\RouteStackInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Http\Request;

/**
 * get the basic domain for view script
 *
 */
class DomainURL extends AbstractHelper
{



    protected $request;
    protected $router;

    public function __construct(Request $request, RouteStackInterface $router)
    {
        $this->request = $request;
        //$test = $router->match($request);
        $this->router = $router;
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


    public function getMatchedRouteName ()
    {
        $routeMatch = $this->router->match($this->request);

        if ($this->router && $routeMatch)
        {
            return $routeMatch->getMatchedRouteName();
        } else {
            return null;
        }


    }

}



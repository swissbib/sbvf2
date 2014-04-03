<?php
namespace Swissbib\Controller;

use Zend\Http\Response;
use VuFind\Controller\CartController as VuFindCartController;

/**
 * Customized cart controller
 *
 */
class CartController extends VuFindCartController
{

    /**
     * Catch exception after login redirect
     *
     * @return    Response
     */
    public function myresearchbulkAction()
    {
        try {
            return parent::myresearchbulkAction();
        } catch (\Exception $e) {
            $this->flashMessenger()->setNamespace('error')->addMessage($e->getMessage());

            $target = $this->url()->fromRoute('myresearch-home');

            return $this->redirect()->toUrl($target);
        }
    }
}

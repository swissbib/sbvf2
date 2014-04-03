<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel;

use VuFind\Controller\AbstractBase as VuFindController;

/**
 * [Description]
 *
 */
class BaseController extends VuFindController
{

    /**
     * Get view model with special template and terminated for ajax
     *
     * @param    Array        $variables
     * @param    String        $template
     * @param    Boolean        $terminal
     * @return    ViewModel
     */
    protected function getAjaxViewModel(array $variables = array(), $template = null, $terminal = true)
    {
        $viewModel = new ViewModel($variables);

        if ($template) {
            $viewModel->setTemplate($template);
        }
        if ($terminal) {
            $viewModel->setTerminal(true);
        }

        return $viewModel;
    }
}

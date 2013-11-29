<?php
namespace Libadmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Libadmin\Institution\InstitutionLoader;

class LibrariesController extends AbstractActionController
{


    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $institutionLoader  = new InstitutionLoader();
        $viewModel          = new ViewModel();

        $viewModel->setTerminal(true);
        $viewModel->setTemplate('libraries/layout');
        $viewModel->groupedInstitutions = $institutionLoader->getGroupedInstitutions();

        return $viewModel;
    }

}
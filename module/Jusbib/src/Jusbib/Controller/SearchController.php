<?php
namespace Jusbib\Controller;

use Zend\Config\Config;
use Zend\Http\PhpEnvironment\Response;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\ResolverInterface;

use VuFind\Controller\SearchController as VuFindSearchController;
use VuFind\Search\Results\PluginManager as VuFindSearchResultsPluginManager;

use Swissbib\Controller\SearchController as SwissbibSearchController;
use Swissbib\VuFind\Search\Results\PluginManager as SwissbibSearchResultsPluginManager;
use Swissbib\Hierarchy\SimpleTreeGenerator;

/**
 * @package       Swissbib
 * @subpackage    Controller
 */
class SearchController extends SwissbibSearchController
{

    /**
     * Render advanced search
     *
     * @return    ViewModel
     */
    public function advancedAction()
    {
        $viewModel              = parent::advancedAction();

        $allTabsConfig          = $this->getAdvancedThemeTabsConfig();
        $activeTabKey           = 'swissbib';

        $activeTabConfig        = $allTabsConfig[$activeTabKey];
        $this->searchClassId    = $activeTabConfig['searchClassId'];

        $viewModel->setVariable('allTabsConfig', $allTabsConfig);
        $viewModel->setVariable('activeTabKey', $activeTabKey);

        return $viewModel;
    }



    /**
     * Render advanced search
     *
     * @return    ViewModel
     */
    public function advancedClassificationAction()
    {
        $viewModel              = parent::advancedAction();

        $allTabsConfig          = $this->getAdvancedThemeTabsConfig();
        $activeTabKey           = 'classification';

        $activeTabConfig        = $allTabsConfig[$activeTabKey];
        $this->searchClassId    = $activeTabConfig['searchClassId'];

        $viewModel->setVariable('classificationTrees', $this->getServiceLocator()->get('Swissbib\Hierarchy\MultiTreeGenerator')->getTrees($viewModel->facetList));
        $viewModel->setVariable('allTabsConfig', $allTabsConfig);
        $viewModel->setVariable('activeTabKey', $activeTabKey);

        $viewModel->setTemplate('search/advanced');

        return $viewModel;
    }



    /**
     * Get all configuration for theme tabs
     *
     * @return    Array[]
     */
    protected function getAdvancedThemeTabsConfig()
    {
        return $this->getServiceLocator()->get('Jusbib\Theme\Theme')->getThemeTabsConfig();
    }

}

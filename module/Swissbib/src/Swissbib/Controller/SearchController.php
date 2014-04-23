<?php
namespace Swissbib\Controller;

use Zend\Config\Config;
use Zend\Http\PhpEnvironment\Response;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\ResolverInterface;

use VuFind\Controller\SearchController as VuFindSearchController;
use VuFind\Search\Results\PluginManager as VuFindSearchResultsPluginManager;

use Swissbib\VuFind\Search\Results\PluginManager as SwissbibSearchResultsPluginManager;
use Swissbib\Hierarchy\SimpleTreeGenerator;

/**
 * @package       Swissbib
 * @subpackage    Controller
 */
class SearchController extends VuFindSearchController
{

    /**
     * @var    Boolean        Forced tab key by controller
     */
    protected $forceTabKey = false;

    /**
     * @var    String[]   search targets extended by swissbib
     */
    protected $extendedTargets;



    /**
     * (Default Action) Get model for home view
     *
     * @return    \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {
        $homeView = parent::homeAction();

        $this->layout()->setVariable('pageClass', 'template_home');

        return $homeView;
    }



    /**
     * Get model for general results view (all tabs, content of active tab only)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function resultsAction()
    {
        $allTabsConfig      = $this->getThemeTabsConfig();
        $activeTabKey       = $this->getActiveTab();
        $resultsFacetConfig = $this->getFacetConfig();
        $activeTabConfig    = $allTabsConfig[$activeTabKey];

        // Set default target
        $this->searchClassId = $activeTabConfig['searchClassId'];

        //do not remember FRBR searches because we ant to jump back to the original search

        $type = $this->params()->fromQuery('type');

        if (!empty($type) && $type == "FRBR") {
            $this->rememberSearch = false;
        }

        $resultViewModel = parent::resultsAction();

        if ($resultViewModel instanceof Response) {
            return $resultViewModel;
        }

        $allTabsConfig[$activeTabKey]['active'] = true;
        $allTabsConfig[$activeTabKey]['count']  = $resultViewModel->results->getResultTotal();

        $this->layout()->setVariable('resultViewParams', $resultViewModel->getVariable('params'));

        $resultViewModel->setVariable('allTabsConfig', $allTabsConfig);
        $resultViewModel->setVariable('activeTabKey', $activeTabKey);
        $resultViewModel->setVariable('activeTabConfig', $activeTabConfig);
        $resultViewModel->setVariable('facetsConfig', $resultsFacetConfig);

        return $resultViewModel;
    }



    /**
     * Render advanced search
     *
     * @return    ViewModel
     */
    public function advancedAction()
    {
        $allTabsConfig          = $this->getThemeTabsConfig();
        $activeTabKey           = $this->getActiveTab();
        $activeTabConfig        = $allTabsConfig[$activeTabKey];
        $this->searchClassId    = $activeTabConfig['searchClassId'];
        $viewModel              = parent::advancedAction();
        $viewModel->options     = $this->getServiceLocator()->get('Swissbib\SearchOptionsPluginManager')->get($this->searchClassId);
        $results                = $this->getResultsManager()->get($this->searchClassId);

        $viewModel->setVariable('allTabsConfig', $allTabsConfig);
        $viewModel->setVariable('activeTabKey', $activeTabKey);
        $viewModel->setVariable('params', $results->getParams());

        $mainConfig = $this->getServiceLocator()->get('Vufind\Config')->get('config');
        $viewModel->adv_search_activeTabId = $mainConfig->Site->adv_search_activeTabId;
        $viewModel->adv_search_useTabs     = $mainConfig->Site->adv_search_useTabs;
        $isCatTreeElementConfigured = $mainConfig->Site->displayCatTreeElement;
        $isCatTreeElementConfigured = !empty($isCatTreeElementConfigured) && ($isCatTreeElementConfigured == "true" || $isCatTreeElementConfigured == "1") ? "1" : 0;

        if ($isCatTreeElementConfigured) {
            $treeGenerator                   = $this->serviceLocator->get('Swissbib\Hierarchy\SimpleTreeGenerator');
            $viewModel->classificationTree   = $treeGenerator->getTree($viewModel->facetList['navDrsys_Gen']['list'], 'navDrsys_Gen');
        }

        return $viewModel;
    }



    /**
     * Find active tab
     *
     * @return    String
     */
    protected function getActiveTab()
    {
        if ($this->forceTabKey) {
            $activeTabKey = $this->forceTabKey;
        } else {
            $activeTabKey   = trim(strtolower($this->params()->fromRoute('tab')));
            $allTabsConfig  = $this->getThemeTabsConfig();

            if (empty($activeTabKey) || !isset($allTabsConfig[$activeTabKey])) {
                $activeTabKey = key($allTabsConfig);
            }
        }

        return $activeTabKey;
    }



    /**
     * Get template for tab
     * A tab template always contains a tab-key postfox
     *
     * @example
     * TabKey: foobar
     * Base Template: path/to/base-template.phtml
     * Tab Template:  path/to/base-template.foobar.phtml
     *
     * Returns the path to the tab template if available. Else return base template
     *
     * @param    String $tab
     * @param    String $baseTemplate
     *
     * @return    String
     */
    protected function getTabTemplate($tab, $baseTemplate)
    {
        /** @var ResolverInterface $resolver */
        $resolver   = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer')->resolver();
        $pathInfo   = pathInfo($baseTemplate);
        $tab        = strtolower($tab);
        $customTemplate = $pathInfo['dirname'] .
            '/' . $pathInfo['basename'] .
            '.' . $tab .
            (isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '');

        return $resolver->resolve($customTemplate) !== false ? $customTemplate : $baseTemplate;
    }



    /**
     * Get all configuration for theme tabs
     *
     * @return    Array[]
     */
    protected function getThemeTabsConfig()
    {
        return $this->getServiceLocator()->get('Swissbib\Theme\Theme')->getThemeTabsConfig();
    }



    /**
     * Get base view model
     * Inject search class id into layout
     *
     * @param    Array|null $params
     *
     * @return    ViewModel
     */
    protected function createViewModel($params = null)
    {
        $this->layout()->setVariable('searchClassId', $this->searchClassId);

        return parent::createViewModel($params);
    }



    /**
     * Get facet config
     *
     * @return    Config
     */
    protected function getFacetConfig()
    {
        return $this->getServiceLocator()->get('VuFind\Config')->get('facets')->get('Results_Settings');
    }



    /**
     * Get results manager
     * If target is extended, get a customized manager
     *
     * @return    VuFindSearchResultsPluginManager|SwissbibSearchResultsPluginManager
     */
    protected function getResultsManager()
    {
        if (!isset($this->extendedTargets)) {
            $mainConfig = $this->getServiceLocator()->get('Vufind\Config')->get('config');
            $extendedTargetsSearchClassList = $mainConfig->SwissbibSearchExtensions->extendedTargets;

            $this->extendedTargets = array_map('trim', explode(',', $extendedTargetsSearchClassList));
        }

        if (in_array($this->searchClassId, $this->extendedTargets)) {
            return $this->getServiceLocator()->get('Swissbib\SearchResultsPluginManager');
        }

        return parent::getResultsManager();
    }
}

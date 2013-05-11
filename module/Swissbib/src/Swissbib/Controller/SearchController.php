<?php
namespace Swissbib\Controller;

use VuFind\Controller\SearchController as VFSearchController;
use Zend\Config\Config;
use Zend\Session\Container as SessionContainer;
use VuFind\Search\Memory as VFMemory;

use Swissbib\Controller\Helper\Search as SearchHelper;
use Zend\View\Resolver\ResolverInterface;

/**
 * @package       Swissbib
 * @subpackage    Controller
 */
class SearchController extends VFSearchController
{

	/**
	 * @var	Boolean		Forced tab key by controller
	 */
	protected $forceTabKey = false;


    protected $extendedTargets = array();

	/**
	 * (Default Action) Get model for home view
	 *
	 * @return    \Zend\View\Model\ViewModel
	 */
	public function homeAction()
	{
		$homeView = parent::homeAction();

		$this->layout()->setTemplate('layout/layout.home');

		return $homeView;
	}



	/**
	 * Get model for general results view (all tabs, content of active tab only)
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function resultsAction()
	{

        $tExtended = $this->getServiceLocator()->get('Vufind\Config')->get('config')->Index->extendedTargets;

        if (!empty($tExtended)) {
            $this->extendedTargets = explode(",", $tExtended);

            array_walk($this->extendedTargets, function(&$v) {
                $v = strtolower($v);
            });
        }

        //$this->extendedTargets

		$allTabsConfig  	= $this->getThemeTabsConfig();
		$activeTabKey   	 = trim(strtolower($this->params()->fromRoute('tab')));
		$resultsFacetConfig	= $this->getServiceLocator()->get('VuFind\Config')->get('facets')->get('Results_Settings');

		if ($this->forceTabKey) {
			$activeTabKey = $this->forceTabKey;
		} else {
			if (empty($activeTabKey) && isset($_COOKIE['tab'])) {
				$activeTabKey = trim(strtolower($_COOKIE['tab']));
			}
			if (empty($activeTabKey) || !isset($allTabsConfig[$activeTabKey])) {
				$activeTabKey = key($allTabsConfig);
			}
		}
		$activeTabConfig = $allTabsConfig[$activeTabKey];

		setcookie('tab', $activeTabKey, strtotime('+1 month'));


		$this->searchClassId = $activeTabConfig['searchClassId'];
		$resultViewModel     = parent::resultsAction();

		$allTabsConfig[$activeTabKey]['active'] = true;
		$allTabsConfig[$activeTabKey]['count'] = $resultViewModel->results->getResultTotal();

		$this->layout()->setVariable('resultViewParams', $resultViewModel->getVariable('params'));

		$sideBarTemplate	= $this->getTabTemplate($activeTabConfig['type'], 'search/sidebar/results');

		$resultViewModel->setVariable('allTabsConfig', $allTabsConfig);
		$resultViewModel->setVariable('activeTabKey', $activeTabKey);
		$resultViewModel->setVariable('activeTabConfig', $activeTabConfig);
		$resultViewModel->setVariable('facetsConfig', $resultsFacetConfig);
		$resultViewModel->setVariable('sidebarTemplate', $sideBarTemplate);

		return $resultViewModel;
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
	 * @param	String		$tab
	 * @param	String		$baseTemplate
	 * @return	String
	 */
	protected function getTabTemplate($tab, $baseTemplate)
	{
		/** @var ResolverInterface $resolver */
		$resolver          = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer')->resolver();
		$pathInfo          = pathInfo($baseTemplate);
		$tab               = strtolower($tab);
		$customTemplate	   = $pathInfo['dirname'] .
							'/' . $pathInfo['basename'] .
							'.' . $tab .
							(isset($pathInfo['extension'])? '.' . $pathInfo['extension']:'');

		return $resolver->resolve($customTemplate) !== false ? $customTemplate : $baseTemplate;
	}



	/**
	 * Get all configuration for theme tabs
	 *
	 * @return	Array[]
	 */
	protected function getThemeTabsConfig()
	{
		$theme			= $this->getTheme();
		$tabs			= array();
		$moduleConfig	= $this->getServiceLocator()->get('Config');
		$tabsConfig		= $moduleConfig['swissbib']['resultTabs'];
		$allTabs		= $tabsConfig['tabs'];
		$themeTabs		= isset($tabsConfig['themes'][$theme]) ? $tabsConfig['themes'][$theme] : array();

		foreach ($themeTabs as $themeTab) {
			if (isset($allTabs[$themeTab])) {
				$tabs[$themeTab] = $allTabs[$themeTab];
			}
		}

		return $tabs;
	}



	/**
	 * Get active theme
	 *
	 * @return	String
	 */
	protected function getTheme()
	{
		return $this->getServiceLocator()->get('Vufind\Config')->get('config')->Site->theme;
	}

    protected function getResultsManager()
    {


        if (!empty($this->extendedTargets)  && in_array(strtolower($this->searchClassId),$this->extendedTargets)) {
            return $this->getServiceLocator()->get('Swissbib\SearchResultsPluginManager');
        } else {
            return parent::getResultsManager();
        }

    }

}

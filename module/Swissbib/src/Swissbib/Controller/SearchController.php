<?php
namespace Swissbib\Controller;

use Zend\Config\Config;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\ResolverInterface;

use VuFind\Controller\SearchController as VFSearchController;
use VuFind\Search\Memory as VFMemory;

use Swissbib\Controller\Helper\Search as SearchHelper;

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

	/**
	 * @var	Array
	 */
	protected $extendedTargets = array();

	/**
	 * Constructor
	 * Init proxy: detect and switch target for configured IP range / URLs
	 */
	public function __construct() {
		parent::__construct();

		$this->forceTabKey       = 'swissbib';
		$this->searchClassId	 = 'Solr';
	}



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
			$this->extendedTargets = explode(',', $tExtended);

			array_walk($this->extendedTargets, function (&$v) {
				$v = strtolower($v);
			});
		}

		$allTabsConfig      = $this->getThemeTabsConfig();
		$activeTabKey       = $this->getActiveTab();
		$resultsFacetConfig = $this->getFacetConfig();
		$activeTabConfig    = $allTabsConfig[$activeTabKey];

			// Set default target
		$this->searchClassId = $activeTabConfig['searchClassId'];

		$resultViewModel     = parent::resultsAction();

		$allTabsConfig[$activeTabKey]['active'] = true;
		$allTabsConfig[$activeTabKey]['count'] = $resultViewModel->results->getResultTotal();

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
	 * @return	ViewModel
	 */
	public function advancedAction()
	{
		$allTabsConfig = $this->getThemeTabsConfig();
		$activeTabKey  = $this->getActiveTab(false);
		$viewModel     = parent::advancedAction();

		$viewModel->setVariable('allTabsConfig', $allTabsConfig);
		$viewModel->setVariable('activeTabKey', $activeTabKey);

		return $viewModel;
	}



	/**
	 * Find active tab
	 *
	 * @param	Boolean		$useCookie		Check and save active tab as cookie
	 * @return	String
	 */
	protected function getActiveTab($useCookie = true)
	{
		if ($this->forceTabKey) {
			$activeTabKey = $this->forceTabKey;
		} else {
			$activeTabKey  = trim(strtolower($this->params()->fromRoute('tab')));
			$allTabsConfig = $this->getThemeTabsConfig();
			if (empty($activeTabKey) && $useCookie && isset($_COOKIE['tab'])) {
				$activeTabKey = trim(strtolower($_COOKIE['tab']));
			}
			if (empty($activeTabKey) || !isset($allTabsConfig[$activeTabKey])) {
				$activeTabKey = key($allTabsConfig);
			}
		}

		if ($useCookie) {
			setcookie('tab', $activeTabKey, strtotime('+1 month'), '/');
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
		return $this->getServiceLocator()->get('Swissbib\Theme\Theme')->getThemeTabsConfig();
	}



	/**
	 * Get base view model
	 * Inject search class id into layout
	 *
	 * @param	Array|null	$params
	 * @return	ViewModel
	 */
	protected function createViewModel($params = null)
	{
		$this->layout()->setVariable('searchClassId', $this->searchClassId);

		return parent::createViewModel($params);
	}



	/**
	 * Get facet config
	 *
	 * @return	Config
	 */
	protected function getFacetConfig()
	{
		return $this->getServiceLocator()->get('VuFind\Config')->get('facets')->get('Results_Settings');
	}



	/**
	 *
	 * @return array|object|\VuFind\Search\Results\PluginManager
	 */
	protected function getResultsManager()
	{
		if (!empty($this->extendedTargets) && in_array(strtolower($this->searchClassId), $this->extendedTargets)) {
			return $this->getServiceLocator()->get('Swissbib\SearchResultsPluginManager');
		} else {
			return parent::getResultsManager();
		}
	}
}

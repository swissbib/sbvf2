<?php
namespace Swissbib\Module\Config;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use Swissbib\TargetsProxy\TargetsProxy;
use Swissbib\TargetsProxy\IpMatcher;
use Swissbib\TargetsProxy\UrlMatcher;
use Swissbib\Theme\Theme;
use Swissbib\Libadmin\Importer as LibadminImporter;
use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;
use Swissbib\View\Helper\InstitutionSorter;
use Swissbib\Tab40Import\Importer as Tab40Importer;
use Swissbib\View\Helper\TranslateLocation;
use Swissbib\RecordDriver\Helper\LocationMap;
use Swissbib\RecordDriver\Missing as RecordDriverMissing;
use Swissbib\RecordDriver\Summon;
use Swissbib\RecordDriver\WorldCat;
use Swissbib\RecordDriver\Helper\EbooksOnDemand;
use Swissbib\RecordDriver\Helper\Availability;

return array(
	'router'          => array(
		'routes' => array(
			// ILS location, e.g. baselbern
			'accountWithLocation' => array(
				'type'    => 'segment',
				'options' => array(
					'route'       => '/MyResearch/:action/:location',
					'defaults'    => array(
						'controller' => 'my-research',
						'action'     => 'Profile',
						'location'   => 'baselbern'
					),
					'constraints' => array(
						'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
						'location' => '[a-z]+',
					),
				)
			),
			// Search results with tab
			'search-results' => array(
				'type' => 'segment',
				'options' => array(
					'route'    => '/Search/Results[/:tab]',
					'defaults' => array(
						'controller' => 'Search',
						'action'     => 'results'
					)
				)
			),
			// (local) Search User Settings
			'search-settings'     => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/MyResearch/Profile/Searchsettings',
					'defaults' => array(
						'controller' => 'MyResearch',
						'action'     => 'Searchsettings',
					)
				)
			),
			'help-page'     => array(
				'type'    => 'segment',
				'options' => array(
					'route'    => '/HelpPage[/:topic]',
					'defaults' => array(
						'controller' => 'helppage',
						'action'     => 'index'
					)
				)
			),
			'holdings-ajax'     => array( // load holdings details for record with ajax
				'type'    => 'segment',
				'options' => array(
					'route'    => '/Holdings/:record/:institution',
					'defaults' => array(
						'controller' => 'holdings',
						'action'     => 'list'
					)
				)
			)
		)
	),
	'console'         => array(
		'router' => array(
			'routes' => array(
				'libadmin-sync' => array(
					'options' => array(
						'route'    => 'libadmin sync [--verbose|-v] [--dry|-d] [--result|-r]',
						'defaults' => array(
							'controller' => 'libadminsync',
							'action'     => 'sync'
						)
					)
				),
				'tab40-import' => array( // Importer for aleph tab40 files
					'options' => array(
						'route'    => 'tab40import <network> <locale> <source>',
						'defaults' => array(
							'controller' => 'tab40import',
							'action'     => 'import'
						)
					)
				)
			)
		)
	),
	'controllers'     => array(
		'invokables' => array(
			'helppage'     => 'Swissbib\Controller\HelpPageController',
			'libadminsync' => 'Swissbib\Controller\LibadminSyncController',
			'my-research'  => 'Swissbib\Controller\MyResearchController',
			'search'       => 'Swissbib\Controller\SearchController',
			'summon'       => 'Swissbib\Controller\SummonController',
			'holdings'     => 'Swissbib\Controller\HoldingsController',
			'tab40import'  => 'Swissbib\Controller\Tab40ImportController'
		)
	),
	'service_manager' => array(
		'invokables' => array(
			'VuFindTheme\ResourceContainer'       => 'Swissbib\VuFind\ResourceContainer',
			'Swissbib\RecordDriverHoldingsHelper' => 'Swissbib\RecordDriver\Helper\Holdings'
		),
		'factories'  => array(
			'Swissbib\HoldingsHelper'    => function ($sm) {
				$ilsConnection  = $sm->get('VuFind\ILSConnection');
				$hmac           = $sm->get('VuFind\HMAC');
				$authManager    = $sm->get('VuFind\AuthManager');
				$config			= $sm->get('VuFind\Config');
				$translator		= $sm->get('VuFind\Translator');
				$locationMap	= $sm->get('Swissbib\LocationMap');
				$eBooksOnDemand	= $sm->get('Swissbib\EbooksOnDemand');
				$availability	= $sm->get('Swissbib\Availability');

				return new HoldingsHelper($ilsConnection, $hmac, $authManager, $config, $translator, $locationMap, $eBooksOnDemand, $availability);
			},
			'Swissbib\TargetsProxy\TargetsProxy' => function ($sm) {
				$config        = $sm->get('VuFind\Config')->get('TargetsProxy');

				return new TargetsProxy($config);
			},
			'Swissbib\TargetsProxy\IpMatcher' => function ($sm) {
				return new IpMatcher();
			},
			'Swissbib\TargetsProxy\UrlMatcher' => function ($sm) {
				return new UrlMatcher();
			},
			'Swissbib\Theme\Theme' => function () {
				return new Theme();
			},
			'Swissbib\Libadmin\Importer' => function ($sm) {
				$config        = $sm->get('VuFind\Config')->get('config')->Libadmin;
				$languageCache = $sm->get('VuFind\CacheManager')->getCache('language');

				return new LibadminImporter($config, $languageCache);
			},
			'Swissbib\Tab40Importer' => function ($sm) {
				$config        = $sm->get('VuFind\Config')->get('config')->tab40import;

				return new Tab40Importer($config);
			},
			'Swissbib\LocationMap' => function ($sm) {
				$locationMapConfig = $sm->get('VuFind\Config')->get('config')->locationMap;

				return new LocationMap($locationMapConfig);
			},
			'Swissbib\EbooksOnDemand' => function ($sm) {
				$eBooksOnDemandConfig = $sm->get('VuFind\Config')->get('config')->eBooksOnDemand;
				$translator			  = $sm->get('VuFind\Translator');

				return new EbooksOnDemand($eBooksOnDemandConfig, $translator);
			},
			'Swissbib\Availability' => function ($sm) {
				$logger				= $sm->get('VuFind\Logger');
				$availabilityConfig = $sm->get('VuFind\Config')->get('config')->Availability;
				$alephNetworkConfig	= $sm->get('VuFind\Config')->get('Holdings')->AlephNetworks;

				return new Availability($availabilityConfig, $alephNetworkConfig, $logger);
			}
		)
	),
	'view_helpers'    => array(
		'invokables' => array(
			'Authors'                 => 'Swissbib\View\Helper\Authors',
			'facetItem'               => 'Swissbib\View\Helper\FacetItem',
			'facetItemLabel'          => 'Swissbib\View\Helper\FacetItemLabel',
			'lastSearchWord'          => 'Swissbib\View\Helper\LastSearchWord',
			'lastTabbedSearchUri'     => 'Swissbib\View\Helper\LastTabbedSearchUri',
			'mainTitle'               => 'Swissbib\View\Helper\MainTitle',
			'myResearchSideBar'       => 'Swissbib\View\Helper\MyResearchSideBar',
			'noHolding'               => 'Swissbib\View\Helper\NoHolding',
			'number'                  => 'Swissbib\View\Helper\Number',
			'pageFunctions'           => 'Swissbib\View\Helper\PageFunctions',
			'physicalDescription'     => 'Swissbib\View\Helper\PhysicalDescriptions',
			'publicationDateMarc'     => 'Swissbib\View\Helper\YearFormatterMarc',
			'publicationDateSummon'	  => 'Swissbib\View\Helper\YearFormatterSummon',
			'publicationDateWorldCat' => 'Swissbib\View\Helper\YearFormatterWorldCat',
			'subjectHeadingFormatter' => 'Swissbib\View\Helper\SubjectHeadings',
			'shorttitleSummon'		  => 'Swissbib\View\Helper\ShortTitleFormatterSummon',
			'SortAndPrepareFacetList' => 'Swissbib\View\Helper\SortAndPrepareFacetList',
			'subjectVocabularies'	  => 'Swissbib\View\Helper\SubjectVocabularies',
			'tabTemplate'			  => 'Swissbib\View\Helper\TabTemplate',
			'zendTranslate'           => 'Zend\I18n\View\Helper\Translate',
			'getVersion'              => 'Swissbib\View\Helper\GetVersion',
			'holdingActions'          => 'Swissbib\View\Helper\HoldingActions',
			'availabilityInfo'        => 'Swissbib\View\Helper\AvailabilityInfo'
		),
		'factories' => array(
			'institutionSorter' => function ($sm) {
				/** @var Config $relationConfig */
				$relationConfig	= $sm->getServiceLocator()->get('VuFind\Config')->get('libadmin-groups');
				$institutionList= array();

				if ($relationConfig->count() !== null) {
					$institutionList = array_keys($relationConfig->institutions->toArray());
				}

				return new InstitutionSorter($institutionList);
			},
			'transLocation'	=> function ($sm) { // Translate holding locations
				/** @var Translator $translator */
				$translator	= $sm->getServiceLocator()->get('VuFind\Translator');

				return new TranslateLocation($translator);
			}
		)
	),
	'vufind'	=> array(
		'recorddriver_tabs'	=> array(
			'VuFind\RecordDriver\SolrMarc' => array(
				'tabs' => array(
					'UserComments'	=> null
				)
			),
			'VuFind\RecordDriver\Summon' => array(
				'tabs' => array(
					'UserComments'	=> null, // Disable user comments tab
					'Description' => null, // Disable description tab
                    'TOC' => null, // Disable TOC tab
				)
			)
		),
		// This section contains service manager configurations for all VuFind
		// pluggable components:
		'plugin_managers' => array(
			'search_backend'	=> array(
				'factories'	=> array(
//					'Solr' => 'VuFind\Search\Factory\SolrDefaultBackendFactory',
					'Summon'	=> 'Swissbib\Search\Factory\SummonBackendFactory',
//					'WorldCat' => 'VuFind\Search\Factory\WorldCatBackendFactory',
				)
			),
			'recorddriver' => array(
				'factories' => array(
					'solrmarc' => function ($sm) {
						$driver = new \Swissbib\RecordDriver\SolrMarc(
							$sm->getServiceLocator()->get('VuFind\Config')->get('config'),
							null,
							$sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
							$sm->getServiceLocator()
						);
						$driver->attachILS(
							$sm->getServiceLocator()->get('VuFind\ILSConnection'),
							$sm->getServiceLocator()->get('VuFind\ILSHoldLogic'),
							$sm->getServiceLocator()->get('VuFind\ILSTitleHoldLogic')
						);
						return $driver;
					},
					'summon' => function ($sm) {
						$baseConfig   = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
						$summonConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('Summon');

						return new Summon(
							$baseConfig, // main config
							$summonConfig // record config
						);
					},
					'worldcat' => function ($sm) {
						$baseConfig     = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
						$worldcatConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('WorldCat');

						return new WorldCat(
							$baseConfig, // main config
							$worldcatConfig // record config
						);
					},
					'missing'  => function ($sm) {
						$baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');

						return new RecordDriverMissing($baseConfig);
					}
				)
			),
			'ils_driver'   => array(
				'factories' => array(
					'aleph' => function ($sm) {
						return new \Swissbib\VuFind\ILS\Driver\Aleph(
							$sm->getServiceLocator()->get('VuFind\CacheManager')
						);
					}
				)
			),
			'hierarchy_driver' => array(
				'factories' => array(
					'series' => function ($sm) {
						return \VuFind\Hierarchy\Driver\Factory::get($sm->getServiceLocator(), 'HierarchySeries');
					},
				)
			),
			'hierarchy_treerenderer' => array(
				'invokables' => array(
					'jstree' => 'Swissbib\VuFind\Hierarchy\TreeRenderer\JSTree'
				)
			)
		)
	),
	//'swissbib' => array(
	//	'ignore_assets' => array(
	//		'blueprint/screen.css',
	//		'jquery-ui.css'
	//	),

    'swissbib' => array(
        'ignore_css_assets' => array(
            'blueprint/screen.css',
            'css/smoothness/jquery-ui.css'
        ),

        'ignore_js_assets' => array(
            'jquery.min.js', // jquery 1.6
            'jquery.form.js',
            'jquery.metadata.js',
            'jquery.validate.min.js',
            'jquery-ui/js/jquery-ui.js',
            'lightbox.js',
            'common.js',
            //has a dependency to jQuery so has to be linked after this general component
            //move it into the swissbib libs
        ),

		// This section contains service manager configurations for all Swissbib
		// pluggable components:
		'plugin_managers' => array(
			'db_table' => array(
				'factories'  => array(
					'userlocaldata' => function ($sm) {
						return new \Swissbib\Db\Table\UserLocalData();
					},
				),
				'invokables' => array(
					'holdingsitems' => 'Swissbib\Db\Table\SbHoldingsItems',
					'userlocaldata' => 'Swissbib\Db\Table\UserLocalData',
				),
			),

            'search_options' => array(
                'abstract_factories' => array('Swissbib\Search\Options\PluginFactory'),
            ),
            'search_params' => array(
                'abstract_factories' => array('Swissbib\Search\Params\PluginFactory'),
            ),

            'search_results' => array(
                'abstract_factories' => array('Swissbib\Search\Results\PluginFactory'),
            ),

		),
		// Search result tabs
		'resultTabs' => array(
				// Active tabs for a theme
			'themes' => array(
				'swissbibmulti' => array(
					'swissbib',
					'summon'
				),
				'swissbibsingle' => array(
					'swissbib'
				)
			),
				// Configuration of tabs
			'tabs' => array(
				'swissbib' => array(
					'searchClassId' => 'Solr',			// VuFind searchClassId
					'label'			=> 'tab.swissbib',	// Label
					'type'			=> 'swissbibsolr',	// Key for custom templates
					'advSearch'		=> 'search-advanced'
				),
				'summon' => array(
					'searchClassId' => 'Summon',
					'label'			=> 'tab.summon',
					'type'			=> 'summon',
					'advSearch'		=> 'summon-advanced'
				)
			)
		)
	)
);

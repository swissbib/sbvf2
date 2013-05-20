<?php
namespace Swissbib\Module\Config;

use Zend\Config\Config;

use Swissbib\TargetsProxy\TargetsProxy;
use Swissbib\TargetsProxy\IpMatcher;
use Swissbib\TargetsProxy\UrlMatcher;
use Swissbib\Libadmin\Importer;
use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;
use Swissbib\View\Helper\InstitutionSorter;

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
			'summon'       => 'Swissbib\Controller\SummonController'
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

				return new HoldingsHelper($ilsConnection, $hmac, $authManager, $config, $translator);
			},
			'Swissbib\TargetsProxy\TargetsProxy' => function ($sm) {
				$config        = $sm->get('VuFind\Config')->get('TargetsProxy')->get('TargetsProxy');

				return new TargetsProxy($config);
			},
			'Swissbib\TargetsProxy\IpMatcher' => function ($sm) {
				return new IpMatcher();
			},
			'Swissbib\TargetsProxy\UrlMatcher' => function ($sm) {
				return new UrlMatcher();
			},
			'Swissbib\Libadmin\Importer' => function ($sm) {
				$config        = $sm->get('VuFind\Config')->get('config')->Libadmin;
				$languageCache = $sm->get('VuFind\CacheManager')->getCache('language');

				return new Importer($config, $languageCache);
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
			'zendTranslate'           => 'Zend\I18n\View\Helper\Translate'
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
			}
		)
	),
	'vufind'          => array(
		// This section contains service manager configurations for all VuFind
		// pluggable components:
		'plugin_managers' => array(
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

						return new \Swissbib\RecordDriver\Summon(
							$baseConfig, // main config
							$summonConfig // record config
						);
					},
					'worldcat' => function ($sm) {
						$baseConfig     = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
						$worldcatConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('WorldCat');

						return new \Swissbib\RecordDriver\WorldCat(
							$baseConfig, // main config
							$worldcatConfig // record config
						);
					},
					'missing'  => function ($sm) {
						$baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');

						return new \Swissbib\RecordDriver\Missing($baseConfig);
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
            'jquery-ui.css'
        ),

        'ignore_js_assets' => array(
            'jquery.min.js',
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

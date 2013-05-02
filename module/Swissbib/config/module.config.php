<?php
namespace Swissbib\Module\Config;

use Swissbib\Libadmin\Importer;
use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;

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
						'action'     => 'index',
						'topic'		 => 'search'
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
				$ils            = $sm->get('VuFind\ILSConnection');
				$holdingsConfig = $sm->get('VuFind\Config')->get('Holdings');
				$hmac           = $sm->get('VuFind\HMAC');
				$authManager    = $sm->get('VuFind\AuthManager');

				return new HoldingsHelper($ils, $holdingsConfig, $hmac, $authManager);
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
			'zendTranslate'           => 'Zend\I18n\View\Helper\Translate'
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
	'swissbib'        => array(
		'ignore_assets'              => array(
			'blueprint/screen.css',
			'jquery-ui.css'
		),
		// This section contains service manager configurations for all Swissbib
		// pluggable components:
		'plugin_managers'            => array(
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
		),

		// Search result tabs
		'preload_result_tabs_counts' => false, // Fetch(+display) results-count of non-selected tab(s) initially?
		'default_result_tab'         => 'swissbib', // ID of default selected tab
		'result_tabs'                => array(
			'swissbib' => array(	// Primary tab: swissbib solr
				'searchClassId' => 'Solr',
				'model'         => '\Swissbib\ResultTab\SbResultTabSolr',
				'params'        => array(
					'id'    => 'swissbib',
					'label' => 'Bücher & mehr',
				),
				'templates'     => array( // templates for tab content and sidebar (=filters)
					'tab'     => 'search/tabs/base.phtml', // default
					'sidebar' => 'global/sidebar/search/facets.swissbib.phtml'
				)
			),
			'summon' => array(
				'searchClassId' => 'Summon',
				'model'         => '\Swissbib\ResultTab\SbResultTab', //Summon',
				'params'        => array(
					'id'    => 'summon',
					'label' => 'Artikel & mehr'
				),
				'templates'     => array(
					'tab'     => 'search/tabs/summon.phtml',
					'sidebar' => 'global/sidebar/search/facets.summon.phtml',
				)
			),
//			'external' => array(
//				'searchClassId' => 'WorldCat',
//				'model'         => '\Swissbib\ResultTab\SbResultTab',
//				'params'        => array(
//					'id'    => 'external',
//					'label' => 'Artikel & mehr'
//				),
//				'templates'     => array(
//					'tab'     => 'search/tabs/external.phtml',
//					'sidebar' => 'global/sidebar/search/facets.external.phtml',
//				)
//			),
		)
	)
);

<?php
namespace Swissbib\Module\Config;

return array(
	'router' => array(
		'routes' => array(
                // ILS location, e.g. baselbern
			'accountWithLocation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/MyResearch/:action/:location',
					'defaults' => array(
						'controller'=> 'my-research',
						'action'	=> 'Profile',
						'location'	=> 'baselbern'
					),
					'constraints' => array(
						'action'	=> '[a-zA-Z][a-zA-Z0-9_-]*',
						'location'	=> '[a-z]+',
					),
				)
			),
                // (local) Search User Settings
            'search-settings' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/MyResearch/Profile/Searchsettings',
                    'defaults' => array(
                        'controller' => 'MyResearch',
                        'action'     => 'Searchsettings',
                    )
                )
            )

		)
	),
    'controllers' => array(
        'invokables' => array(
            'search'		=> 'Swissbib\Controller\SearchController',
            'my-research'	=> 'Swissbib\Controller\MyResearchController',
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'VuFindTheme\ResourceContainer'         => 'Swissbib\VuFind\ResourceContainer',
			'Swissbib\RecordDriverHoldingsHelper'   => 'Swissbib\RecordDriver\Helper\Holdings'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'number'					=> 'Swissbib\View\Helper\Number',
            'SortAndPrepareFacetList'	=> 'Swissbib\View\Helper\SortAndPrepareFacetList',
            'Authors'					=> 'Swissbib\View\Helper\Authors',
            'publicationDateMarc'		=> 'Swissbib\View\Helper\YearFormatterMarc',
            'publicationDateWorldCat'	=> 'Swissbib\View\Helper\YearFormatterWorldCat',
            'facetItemLabel'			=> 'Swissbib\View\Helper\FacetItemLabel',
            'facetItem'			        => 'Swissbib\View\Helper\FacetItem',
            'lastSearchWord'			=> 'Swissbib\View\Helper\LastSearchWord',
            'lastTabbedSearchUri'		=> 'Swissbib\View\Helper\LastTabbedSearchUri',
			'myResearchSideBar'			=> 'Swissbib\View\Helper\MyResearchSideBar',
			'pageFunctions'				=> 'Swissbib\View\Helper\PageFunctions'
        )
    ),
    'vufind' => array(
            // This section contains service manager configurations for all VuFind
            // pluggable components:
        'plugin_managers' => array(
            'recorddriver' => array(
                'factories' => array(
                    'solrmarc' => function ($sm) {
						$driver = new \Swissbib\RecordDriver\SolrMarc(
							$sm->getServiceLocator()->get('VuFind\Config')->get('config'),
							null,
							$sm->getServiceLocator()->get('VuFind\Config')->get('searches')
						);
						$driver->attachILS(
							$sm->getServiceLocator()->get('VuFind\ILSConnection'),
							$sm->getServiceLocator()->get('VuFind\ILSHoldLogic'),
							$sm->getServiceLocator()->get('VuFind\ILSTitleHoldLogic')
						);
						return $driver;
//
//
//
//						$baseConfig		= $sm->getServiceLocator()->get('VuFind\Config')->get('config');
//						$searchConfig	= $sm->getServiceLocator()->get('VuFind\Config')->get('searches');
//
//                        return new \Swissbib\RecordDriver\SolrMarc(
//							$baseConfig, // main config
//							null,
//							$searchConfig// record config
//                        );
                    },
                    'worldcat' => function ($sm) {
						$baseConfig		= $sm->getServiceLocator()->get('VuFind\Config')->get('config');
						$worldcatConfig	= $sm->getServiceLocator()->get('VuFind\Config')->get('WorldCat');

                        return new \Swissbib\RecordDriver\WorldCat(
							$baseConfig,     // main config
							$worldcatConfig // record config
                        );
                    },
					'missing' => function ($sm) {
						$baseConfig		= $sm->getServiceLocator()->get('VuFind\Config')->get('config');

						return new \Swissbib\RecordDriver\Missing($baseConfig);
					}
                )
            ),
			'ils_driver' => array(
				'factories' => array(
					'aleph' => function ($sm) {
						return new \Swissbib\VuFind\ILS\Driver\Aleph(
							$sm->getServiceLocator()->get('VuFind\CacheManager')
						);
					}
				)
        )
    ),
    'swissbib' => array(
        'ignore_assets' => array(
            'blueprint/screen.css',
            'jquery-ui.css'
        ),
            // This section contains service manager configurations for all Swissbib
            // pluggable components:
        'plugin_managers' => array(
            'db_table' => array(
                'factories' => array(
                    'userlocaldata' => function ($sm) {
                        return new \Swissbib\Db\Table\UserLocalData();
                    },
                ),
                'invokables'            => array(
                    'holdingsitems' => 'Swissbib\Db\Table\SbHoldingsItems',
                    'userlocaldata' => 'Swissbib\Db\Table\UserLocalData',
                ),
            ),
        ),

            // Search result tabs
        'preload_result_tabs_counts'=> false,       // Fetch(+display) results-count of non-selected tab(s) initially?
        'default_result_tab'        => 'swissbib',  // ID of default selected tab
        'result_tabs' => array(
                // Primary tab: swissbib
            'swissbib' => array(
                'searchClassId' =>  'Solr',
                'model'         => '\Swissbib\ResultTab\SbResultTabSolr',
                'params'        => array(
                    'id'            => 'swissbib',
                    'label'         => 'Bücher & mehr',
                ),
                'templates'  => array(  // templates for tab content and sidebar (=filters)
                        'tab'       => 'search/tabs/base.phtml',    // default
                        'sidebar'   => 'global/sidebar/search/facets.swissbib.phtml'

                )
            ),
                // Secondary tab
            'external' => array(
                'searchClassId' =>  'WorldCat',
                'model'         => '\Swissbib\ResultTab\SbResultTab',
                'params'        => array(
                        'id'        => 'external',
                        'label'     => 'Artikel & mehr'
                ),
                'templates' => array(
                        'tab'   => 'search/tabs/external.phtml',
                        'sidebar'=> 'global/sidebar/search/facets.external.phtml',
                )
            ),
        )
    )
);
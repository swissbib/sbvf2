<?php
namespace Swissbib\Module\Config;

return array(
	'router' => array(
		'routes' => array(
			'xservertest' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/xservertest',
					'defaults' => array(
						'controller' => 'xserver',
						'action' => 'test'
					)
				)
			)
		)
	),
    'controllers' => array(
        'invokables' => array(
            'search'	=> 'Swissbib\Controller\SearchController',
            'xserver'	=> 'Swissbib\Controller\XserverController'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'VuFindTheme\ResourceContainer' => 'Swissbib\VuFind\ResourceContainer'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'number'					=> 'Swissbib\View\Helper\Number',
            'SortAndPrepareFacetList'	=> 'Swissbib\View\Helper\SortAndPrepareFacetList',
            'Authors'					=> 'Swissbib\View\Helper\Authors',
            'publicationDate'			=> 'Swissbib\View\Helper\YearFormatter',
            'lastSearchWord'			=> 'Swissbib\View\Helper\LastSearchWord'
// 'config' => 'Swissbib\View\Helper\Config'
        )
    ),
    'vufind' => array(
            // This section contains service manager configurations for all VuFind
            // pluggable components:
        'plugin_managers' => array(
            'recorddriver' => array(
                'factories' => array(
                    'solrmarc' => function () {
                        return new \Swissbib\RecordDriver\SbSolrMarc(
                            \VuFind\Config\Reader::getConfig(), null,
                            \VuFind\Config\Reader::getConfig('searches')
                        );
                    }
                )
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
                'abstract_factories'    => array('Swissbib\Db\Table\SbPluginFactory'),
                'invokables'            => array(
                    'holdingsitems' => 'Swissbib\Db\Table\SbHoldingsItems',
                ),
            ),
        ),
        'invokables' => array(
            'Swissbib\HoldingsHelper' => 'Swissbib\RecordDriver\Helper\HoldingsHelper',
        ),
            // Search result tabs
        'result_tabs' => array(
            'swissbib' => array(
                'model'     => '\Swissbib\ResultTab\SbResultTabSolr',
                'template'  => 'search/tabs/base.phtml',    // default
                'params'    => array(
                    'id'        => 'swissbib',
                    'label'     => 'Bücher & mehr',
                    'selected'  => true
                )
            ),
            'ext' => array(
                'model'     => '\Swissbib\ResultTab\SbResultTab',
                'template'  => 'search/tabs/external.phtml',
                'params'    => array(
                    'id'        => 'external',
                    'label'     => 'Artikel & mehr'
                )
            ),
        )
    )
);
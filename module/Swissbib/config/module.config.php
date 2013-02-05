<?php
//namespace SwissBib\Module\Config;
namespace Swissbib\Module\Configuration;

//Todo: the old style for the namespace was SwissBib\Module\Config; (same as VuFind standard module) now Configuration - why?




$config = array(
    'router' => array(
        'routes' => array(

            'default' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/[:controller[/:action]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        //'controller' => 'index',
                        'controller' => 'Swissbib\Controller\Index',
                        //'action'     => 'Home',
                        'action'     => 'Home',
                    ),
                ),
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
<<<<<<< HEAD
            'Swissbib\Controller\Index' => 'Swissbib\Controller\IndexController',
            //'record' => 'Swissbib\Controller\RecordController',
            //'search' => 'Swissbib\Controller\SearchController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array()
    ),
    'service_manager' => array(
        'factories' => array(),
        'invokables' => array(),
        'initializers' => array(),
        'aliases' => array(),
    ),
    'translator' => array(),
    'view_helpers' => array(),
    'view_manager' => array(),
    // This section contains all VuFind-specific settings (i.e. configurations
    // unrelated to specific Zend Framework 2 components).
=======
			'search'	=> 'Swissbib\Controller\SearchController'
        )
    ),
	'service_manager' => array(
		'invokables' => array(
			'VuFindTheme\ResourceContainer' => 'Swissbib\VuFind\ResourceContainer'
		)
	),
	'view_helpers' => array(
		'invokables' => array(
			'number'	=> 'Swissbib\View\Helper\Number',
//			'config' => 'Swissbib\View\Helper\Config'
		)
	),
>>>>>>> 9d59fcab4c52d62a336229b2f27dbc923b2d7a82
    'vufind' => array(
        // This section contains service manager configurations for all VuFind
        // pluggable components:
        'plugin_managers' => array(
            'auth' => array(
                'abstract_factories' => array(),
                'factories' => array(),
                'invokables' => array(),
                'aliases' => array(),
            ),
            'autocomplete' => array(
                'abstract_factories' => array(),
                'invokables' => array(),
                'aliases' => array(),
            ),
            'db_table' => array(
                'abstract_factories' => array(),
                'invokables' => array(),
            ),
            'hierarchy_driver' => array(
                'factories' => array()
            ),
            'hierarchy_treedatasource' => array(
                'factories' => array(),
                'invokables' => array(),
            ),
            'hierarchy_treerenderer' => array(
                'invokables' => array()
            ),
            'ils_driver' => array(
                'abstract_factories' => array(),
                'factories' => array(),
                'invokables' => array(),
            ),
            'recommend' => array(
                'abstract_factories' => array(),
                'factories' => array(),
                'invokables' => array(),
            ),
            'recorddriver' => array(
                'abstract_factories' => array(),
                'factories' => array(
                    'solrmarc' => function () {
                        return new \Swissbib\RecordDriver\SbSolrMarc(
                            \VuFind\Config\Reader::getConfig(), null,
                            \VuFind\Config\Reader::getConfig('searches')
                        );
                    },
                ),
            ),

            'recordtab' => array(
                'abstract_factories' => array(),
                'factories' => array(),
                'invokables' => array(),
            ),
            'related' => array(
                'abstract_factories' => array(),
                'invokables' => array(),
            ),
            'resolver_driver' => array(
                'abstract_factories' => array(),
                'invokables' => array(),
                'aliases' => array(),
            ),
            'session' => array(
                'abstract_factories' => array(),
                'invokables' => array(),
                'aliases' => array(),
            ),
            'statistics_driver' => array(
                'abstract_factories' => array(),
                'invokables' => array(),
                'aliases' => array(),
            ),
        ),
        // This section controls which tabs are used for which record driver classes.
        // Each sub-array is a map from a tab name (as used in a record URL) to a tab
        // service (found in recordtab_plugin_manager, below).  If a particular record
        // driver is not defined here, it will inherit configuration from a configured
        // parent class.
        'recorddriver_tabs' => array(
            'VuFind\RecordDriver\SolrAuth' => array(),
            'VuFind\RecordDriver\SolrDefault' => array(),
            'VuFind\RecordDriver\SolrMarc' => array(),
            'VuFind\RecordDriver\Summon' => array(),
            'VuFind\RecordDriver\WorldCat' => array(),
        ),
        // This section controls the SearchManager service:
        'search_manager' => array(
            'default_namespace' => 'VuFind\Search',
            'namespaces_by_id' => array(),
            'aliases' => array(),
        ),
    ),
    'swissbib' => array(
        // This section contains service manager configurations for all Swissbib
        // pluggable components:
        'plugin_managers' => array(

            'db_table' => array(
                'abstract_factories' => array('Swissbib\Db\Table\SbPluginFactory'),
                'invokables' => array(
                    'holdingsitems' => 'Swissbib\Db\Table\SbHoldingsItems',
                ),
            ),


        ),


    ),

);


// Add the home route last
$config['router']['routes']['home'] = array(
    'type' => 'Zend\Mvc\Router\Http\Literal',
    'options' => array(
        'route'    => '/',
        'defaults' => array(
            'controller' => 'Swissbib\Controller\Index',
            'action'     => 'Home',
        )
    )
);



return $config;

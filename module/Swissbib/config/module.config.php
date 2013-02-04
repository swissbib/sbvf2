<?php
namespace Swissbib\Module\Config;

return array(
    'controllers' => array(
        'invokables' => array(
			'index'		=> 'Swissbib\Controller\IndexController',
			'search'	=> 'Swissbib\Controller\SearchController'
        )
    ),
	'service_manager' => array(
		'invokables' => array(
			'VuFindTheme\ResourceContainer' => 'Swissbib\VuFind\ResourceContainer'
		),
//		'factories' => array(
//			'VuFindTheme\ResourceContainer' => function ($sm) {
//				return new Swissbib\VuFind\ResourceContainer(
//					$sm->getServiceLocator()->get('Config')
//				);
//			}
//		)
	),
    'vufind' => array(
        // This section contains service manager configurations for all VuFind
        // pluggable components:
        'plugin_managers' => array(
            'recorddriver' => array(
                'invokables' => array(
                    'solrmarc' => 'Swissbib\RecordDriver\SbSolrMarc'
                )
            )
        )
    ),
	'swissbib' => array(
		'ignore_assets' => array(
			'blueprint/screen.css'
		)
	)
);
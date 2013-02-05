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
                ),
				'aliases' => array(
					'solrsbmarc' => 'solrmarc'
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
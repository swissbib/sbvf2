<?php
namespace Swissbib\Module\Config;

return array(
    'controllers' => array(
        'invokables' => array(
			'index'			=> 'Swissbib\Controller\IndexController',
			'search'		=> 'Swissbib\Controller\SearchController'
        )
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
    )
);
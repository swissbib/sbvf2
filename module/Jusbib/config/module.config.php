<?php
namespace Jusbib\Module\Config;

use Jusbib\Theme\Theme;
use Jusbib\VuFind\Search\Helper\ExtendedSolrFactoryHelper;

return array(
    'router' => array(
        'routes' => array(
            'search-advancedClassification' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/Search/AdvancedClassification',
                    'defaults' => array(
                        'controller' => 'search',
                        'action'     => 'advancedClassification'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'search' => 'Jusbib\Controller\SearchController',
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Jusbib\Theme\Theme'                => 'Jusbib\Theme\Factory::getJusbibTheme',
            'Jusbib\ExtendedSolrFactoryHelper'  => 'Jusbib\VuFind\Search\Factory::getJusbibSOLRFactoryHelper',

        )
    ),
    'swissbib' => array(
        'resultTabs' => array(
            'themes' => array(
                'jusbib' => array(
                    'swissbib'
                )
            )
        )
    ),
    'jusbib' => array(
        'adv_tabs' => array(
            'swissbib'       => array(
                'searchClassId' => 'Solr', // VuFind searchClassId
                'label'         => 'Advanced Search', // Label
                'type'          => 'swissbibsolr', // Key for custom templates
                'advSearch'     => 'search-advanced'
            ),
            'classification' => array(
                'searchClassId' => 'Solr',
                'label'         => 'classification_tree',
                'type'          => 'swissbibsolr',
                'advSearch'     => 'search-advancedClassification'
            )
        ),
        // This section contains service manager configurations for all Swissbib
        // pluggable components:
        'plugin_managers' => array(
            'vufind_search_options' => array(
                'abstract_factories' => array('Jusbib\VuFind\Search\Options\PluginFactory'),
            ),
            'vufind_search_params'  => array(
                'abstract_factories' => array('Swissbib\VuFind\Search\Params\PluginFactory'),
            ),
            'vufind_search_results' => array(
                'abstract_factories' => array('Swissbib\VuFind\Search\Results\PluginFactory'),
            )
        ),
    )
);

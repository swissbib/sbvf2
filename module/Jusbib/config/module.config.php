<?php
namespace Jusbib\Module\Config;

use Jusbib\Theme\Theme;

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
            'Jusbib\Theme\Theme' => function () {
                    return new Theme();
                }
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
        )
    )
);

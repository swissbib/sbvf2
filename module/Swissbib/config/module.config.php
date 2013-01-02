<?php
//namespace SwissBib\Module\Config;
namespace swissbib\Module\Configuration;

//Todo: the old style for the namespace was SwissBib\Module\Config; (same as VuFind standard module) now Configuration - why?


$config = array(


    'controllers' => array(
        'invokables' => array(
            'Swissbib\Controller\Index' => 'Swissbib\Controller\IndexController',
            'record' => 'Swissbib\Controller\RecordController',
            'search' => 'Swissbib\Controller\SearchController',




        ),
    ),

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
                        'controller' => 'Swissbib\Controller\Index',
                        'action'     => 'Home',
                    ),
                ),
            )
        )
    )


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
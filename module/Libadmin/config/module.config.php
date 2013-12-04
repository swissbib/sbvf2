<?php

return array(
    'router' => array(
        'routes' => array(
            'libraries-index' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/Libraries',
                    'defaults' => array(
                        'controller' => 'libraries',
                        'action'     => 'index'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'libraries' => 'Libadmin\Controller\LibrariesController'
        )
    )
);
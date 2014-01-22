<?php
return array(
    'extends' => 'swissbibsingle',
    'css' => array(
        'jusbib.swissbib.css'
    ),
    'helpers' => array(
        'factories' => array(
            'searchoptions' => function ($sm) {
                return new VuFind\View\Helper\Root\SearchOptions(
                    $sm->getServiceLocator()->get('Jusbib\SearchOptionsPluginManager')
                );
            }
        )
    )
);
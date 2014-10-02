<?php
return array(
    'extends' => 'swissbibsingle',
    'css' => array(
        'jusbib.swissbib.css'
    ),
    'helpers' => array(
        'factories' => array(
            'searchoptions' => 'Jusbib\VuFind\Search\Factory::getJusbibSearchOptionsForHelperOptions'
        )
    )
);
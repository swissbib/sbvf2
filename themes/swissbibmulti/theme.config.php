<?php
return array(
    'extends' => 'swissbib',
	'css' => array(
		'orange.css'
	),
    'js' => array(
    'sb.extensions.js',
    //same as with sb.extensions.singletarget.js in swissbibsingle theme
    //here we had to implement workarounds to support stable target behaviour
    //e.g ones the user has chosen a facet in one target this target should be
    //    presented once rendering of the request had finished
    //guess shouldn't be necessary with VuFind
    ),

);
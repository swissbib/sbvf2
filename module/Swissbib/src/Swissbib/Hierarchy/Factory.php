<?php

namespace Swissbib\Hierarchy;

use Zend\ServiceManager\ServiceManager;

class Factory {

    /**
     * @param   ServiceManager $sm
     *
     * @return  SimpleTreeGenerator
     */
    public static function getSimpleTreeGenerator(ServiceManager $sm) {
        $objectCache = $sm->get('VuFind\CacheManager')->getCache('object');

        return new SimpleTreeGenerator($objectCache);
    }



    /**
     * @param ServiceManager $sm
     *
     * @return MultiTreeGenerator
     */
    public static function getMultiTreeGenerator(ServiceManager $sm) {
        $config                 = $sm->get('Vufind\Config')->get('Config');
        $simpleTreeGenerator    = $sm->get('Swissbib\Hierarchy\SimpleTreeGenerator');

        return new MultiTreeGenerator($config, $simpleTreeGenerator);
    }

} 
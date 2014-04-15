<?php

/**
 * Factory for services.
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category swissbib VuFind2
 * @package  Swissbib\Services
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */


namespace Swissbib\Services;
use Zend\ServiceManager\ServiceManager;



/**
 * Factory for Services.
 *
 * @category swissbib VuFind2
 * @package  Controller
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory
{

    /**
     * Constructs a type for redirecting resources using the appropriate protocol
     * (most often used for http resources in https environments).
     *
     * @param ServiceManager $sm Service manager.
     * @return RedirectProtocolWrapper
     */
    public static function getProtocolWrapper(ServiceManager $sm)
    {
        $config = $sm->get('VuFind\Config')->get('config');
        return new RedirectProtocolWrapper($config);

    }

    /**
     * Constructs Theme - a type used to load Theme specific configuration
     * @param ServiceManager $sm
     * @return Theme
     */
    public static function getThemeConfigs(ServiceManager $sm)
    {
        //Factory Method doesn't make sense but was introduced by Snowflake
        //perhaps we can use it later to enhance the Theme type
        //once the Responsive Design project has finished (and no enhancement is necessary) we could throw it away
        //and simplify the mechanism with invokables
        return new Theme();
    }


    /**
     * creates a service to configure the requests against SOLR to receive highlighting snippets in fulltext
     * @param ServiceManager $sm
     * @return \Swissbib\Highlight\SolrConfigurator
     */
    public static function getSOLRHighlightingConfigurator(ServiceManager $sm)
    {
        $config = $sm->get('Vufind\Config')->get('config')->Highlight;
        $eventsManager = $sm->get('SharedEventManager');
        $memory = $sm->get('VuFind\Search\Memory');

        return new \Swissbib\Highlight\SolrConfigurator($eventsManager, $config, $memory);

    }

    /**
     * creates a Swissbib specific logger type
     * @param ServiceManager $sm
     * @return \Swissbib\Log\Logger
     */
    public static function getSwissbibLogger(ServiceManager $sm)
    {
        $logger = new  \Swissbib\Log\Logger();
        $logger->addWriter(
            'stream', 1, array(
                'stream' => 'log/swissbib.log'
            )
        );
        return $logger;
    }


}
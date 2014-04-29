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
use Swissbib\VuFind\Recommend\FavoriteFacets;



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

    public static function getTranslator(ServiceManager $sm)
    {
        $factory = new \Zend\I18n\Translator\TranslatorServiceFactory();
        $translator = $factory->createService($sm);

        // Set up the ExtendedIni plugin:
        $config = $sm->get('VuFind\Config')->get('config');
        $pathStack = array(
            APPLICATION_PATH  . '/languages',
            LOCAL_OVERRIDE_DIR . '/languages',
            LOCAL_OVERRIDE_DIR . '/languages/bibinfo',
            LOCAL_OVERRIDE_DIR . '/languages/group',
            LOCAL_OVERRIDE_DIR . '/languages/institution',
            LOCAL_OVERRIDE_DIR . '/languages/location',
            LOCAL_OVERRIDE_DIR . '/languages/union',

        );
        //$translator->getPluginManager()->setService(
        //    'extendedini',
        //    new \VuFind\I18n\Translator\Loader\ExtendedIni(
        //        $pathStack, $config->Site->language
        //    )
        //);

        //Todo: discuss this issue with VuFind List

        $translator->getPluginManager()->setService(
            'extendedini',
            new \Swissbib\VuFind\l18n\Translator\Loader\ExtendedIni(
                $pathStack, $config->Site->language
            )
        );



        // Set up language caching for better performance:
        try {
            $translator->setCache(
                $sm->get('VuFind\CacheManager')->getCache('language')
            );
        } catch (\Exception $e) {
            // Don't let a cache failure kill the whole application, but make
            // note of it:
            $logger = $sm->get('VuFind\Logger');
            $logger->debug(
                'Problem loading cache: ' . get_class($e) . ' exception: '
                . $e->getMessage()
            );
        }

        return $translator;
    }

    /**
     * Factory for FavoriteFacets module.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return FavoriteFacets
     */
    public static function getFavoriteFacets(ServiceManager $sm)
    {
        /*
        the VuFind mechanism isn't flexible enough. They changed the mechanism displaying "Merklisten" (favorite lists in VF terminology)
        because they should be present on all the pages after users have logged in. This is not compatible with our current UI.
        VF core is using only tags as mainfacets
        $this->mainFacets = ($tagSetting && $tagSetting !== 'disabled')
            ? array('tags' => 'Your Tags') : array();
        we need tags and lists for our current UI ....
        solve this in RD design project
        */

        return new FavoriteFacets(
            $sm->getServiceLocator()->get('VuFind\Config')
        );
    }


}
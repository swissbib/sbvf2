<?php
/**
 * Factory for RecordDrivers.
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
 * @package  Swissbib\RecordDriver
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */





namespace Swissbib\RecordDriver;
use Zend\ServiceManager\ServiceManager;


/**
 * Factory for controllers.
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
     * @param ServiceManager $sm
     * @return SolrDefaultAdapter
     */
    public static function getSolrDefaultAdapter(ServiceManager $sm)
    {
        $config = $sm->get('Vufind\Config')->get('Config');
        return new SolrDefaultAdapter($config);
    }


    /**
     * @param ServiceManager $sm
     * @return SolrMarc
     */
    public static function getSolrMarcRecordDriver(ServiceManager $sm)
    {
        $serviceLocator = $sm->getServiceLocator();

        $driver = new \Swissbib\RecordDriver\SolrMarc(
            $serviceLocator->get('VuFind\Config')->get('config'),
            null,
            $serviceLocator->get('VuFind\Config')->get('searches'),
            $serviceLocator->get('Swissbib\Services\RedirectProtocolWrapper')
        );
        $driver->attachILS(
            $serviceLocator->get('VuFind\ILSConnection'),
            $serviceLocator->get('VuFind\ILSHoldLogic'),
            $serviceLocator->get('VuFind\ILSTitleHoldLogic')
        );

        return $driver;

    }


    public static function getSummonRecordDriver(ServiceManager $sm)
    {
        $baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
        $summonConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('Summon');

        return new Summon(
            $baseConfig, // main config
            $summonConfig // record config
        );

    }


    /**
     * @param ServiceManager $sm
     * @return WorldCat
     */
    public static function getWorldCatRecordDriver(ServiceManager $sm)
    {
        $baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
        $worldcatConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('WorldCat');

        return new WorldCat(
            $baseConfig, // main config
            $worldcatConfig // record config
        );

    }

    /**
     * @param ServiceManager $sm
     * @return Missing
     */
    public static function getRecordDriverMissing(ServiceManager $sm)
    {
        $baseConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('config');

        return new Missing($baseConfig);

    }

}
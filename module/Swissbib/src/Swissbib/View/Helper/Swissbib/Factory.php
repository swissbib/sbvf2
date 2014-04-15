<?php
/**
 * Factory for view helpers related to the Swissbib theme.
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
 * @package  Swissbib\View\Helper\Swissbib
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */



namespace Swissbib\View\Helper\Swissbib;
use Zend\ServiceManager\ServiceManager;


/**
 * Factory for swissbib specific view helpers related to the Swissbib Theme.
 * these theme related static factory functions were refactored from Closures
 * which were part of the configuration. Because configuration can now be cached we have to write factory methods
 *
 * @category swissbib VuFind2
 * @package  Controller
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory
{


    public static function getRecordHelper(ServiceManager $sm)
    {
        return new \Swissbib\View\Helper\Record(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config')
        );

    }

    public static function getFlashMessages(ServiceManager $sm)
    {
        $messenger = $sm->getServiceLocator()->get('ControllerPluginManager')
            ->get('FlashMessenger');

        return new \Swissbib\VuFind\View\Helper\Root\Flashmessages($messenger);

    }


    public static function getCitation(ServiceManager $sm)
    {
        return new \Swissbib\VuFind\View\Helper\Root\Citation(
            $sm->getServiceLocator()->get('VuFind\DateConverter')
        );

    }

    public static function getRecordLink(ServiceManager $sm)
    {
        return new \Swissbib\View\Helper\RecordLink(
            $sm->getServiceLocator()->get('VuFind\RecordRouter')
        );

    }

    public static function getExtendedLastSearchLink(ServiceManager $sm)
    {
        return new \Swissbib\View\Helper\GetExtendedLastSearchLink(
            $sm->getServiceLocator()->get('VuFind\Search\Memory')
        );

    }


}
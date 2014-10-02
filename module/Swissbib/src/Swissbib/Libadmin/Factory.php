<?php

/**
 * Factory for Libadmin Types.
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
 * @package  Swissbib\Libadmin
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */


namespace Swissbib\Libadmin;
use Zend\ServiceManager\ServiceManager;
use Swissbib\Libadmin\Importer as LibadminImporter;



/**
 * Factory for Types used for communication with the Libadmin web application.
 *
 * @category swissbib VuFind2
 * @package  Libadmin
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory
{

    public static function getLibadminImporter(ServiceManager $sm)
    {
        $config = $sm->get('VuFind\Config')->get('config')->Libadmin;
        $languageCache = $sm->get('VuFind\CacheManager')->getCache('language');

        return new LibadminImporter($config, $languageCache);

    }


}
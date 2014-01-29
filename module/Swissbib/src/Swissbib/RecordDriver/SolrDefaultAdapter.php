<?php
/**
 * SolrDefaultAdapter to load
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 1/2/13
 * Time: 4:09 PM
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
 * @category swissbib_VuFind2
 * @package  RecordDriver
 * @author   Maechler Markus
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
namespace Swissbib\RecordDriver;

use Zend\Config\Config;


class SolrDefaultAdapter {


    /**
     * @var Config
     */
    protected $mainConfig;


    /**
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->mainConfig = $config;
    }


    /**
     * @return array Strings representing citation formats.
     */
    public function getCitationFormats()
    {
        if (isset($this->mainConfig->Record->citation_formats)
            && !empty($this->mainConfig->Record->citation_formats)
        ){
            return explode(",",$this->mainConfig->Record->citation_formats);
        } else {
            return array();
        }
    }

} 
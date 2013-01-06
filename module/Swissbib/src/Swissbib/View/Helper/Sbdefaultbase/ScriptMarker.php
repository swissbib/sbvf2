<?php

/**
 * swissbib / VuFind Marker for comments at the beginning and end of view scripts
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 1/2/13
 * Time: 7:11 PM
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
 * @package  View_Helpers
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     www.swissbib.org
 */


namespace Swissbib\View\Helper\Sbdefaultbase;
use Zend\View\Helper\AbstractHelper;


/**
 * @category swissbib_VuFind2
 * @package  View_Helpers
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     www.swissbib.org
 */
class ScriptMarker extends AbstractHelper
{

    public function headline($scriptname, $debug = false) {

        if ($debug) {
            return "<!-- Start of $scriptname / " . date("Y-m-d H:i:s") . " -->";
        }
        else {
            return "<!-- Start of $scriptname  -->";
        }

    }

    public function footer($scriptname, $debug = false) {

        if ($debug) {
            return "<!-- End of $scriptname / " . date("Y-m-d H:i:s") . " -->";
        }
        else {
            return "<!-- End of $scriptname  -->";
        }

    }

}

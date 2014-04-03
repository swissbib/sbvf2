<?php
 
 /**
 * [...description of the type ...]
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 2/13/14
 * Time: 1:49 PM
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
 * @package  [...package name...]
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */


namespace Swissbib\View\Helper;


use Zend\View\Helper\AbstractHelper;
use Swissbib\Services\RedirectProtocolWrapper as ServiceRedirectProtocolWrapper;


class RedirectProtocolWrapper extends AbstractHelper {

    private  $serviceProtolWrapper;

    public function __construct(ServiceRedirectProtocolWrapper $spW) {

        $this->serviceProtolWrapper = $spW;

    }

    /*
     * wrapper Function to wrap URL'S for another service
     * for swissbib we use the service for http URL's which should used within a https environment
     */
    public function getWrappedURL($url) {
        return $this->serviceProtolWrapper->getWrappedURL($url);

    }

} 
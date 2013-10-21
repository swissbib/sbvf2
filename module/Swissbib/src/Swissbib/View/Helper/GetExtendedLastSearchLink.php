<?php
 
 /**
 * [...description of the type ...]
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 10/17/13
 * Time: 10:24 AM
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
use VuFind\Search\Memory, Zend\View\Helper\AbstractHelper;


class GetExtendedLastSearchLink extends AbstractHelper {


    /**
     * Search memory
     *
     * @var Memory
     */
    protected $memory;

    /**
     * Constructor
     *
     * @param Memory $memory Search memory
     */
    public function __construct(Memory $memory)
    {
        $this->memory = $memory;
    }


    /**
     * If a previous search is recorded in the session, return a link to it;
     * otherwise, return a blank string.
     * This method is the same as in VF2 core Helper GetLastSearchLink
     * Because of the invoke method in VF2 you can't use another method where you get only the link without any decoration
     *
     * @param string $link   Text to use as body of link
     * @param string $prefix Text to place in front of link
     * @param string $suffix Text to place after link
     *
     * @return string
     */
    public function getWithDecoration($link, $prefix = '', $suffix = '')
    {
        $last = $this->memory->retrieve();
        if (!empty($last)) {
            $escaper = $this->getView()->plugin('escapeHtml');
            return $prefix . '<a href="' . $escaper($last) . '">' . $link . '</a>'
            . $suffix;
        }
        return '';
    }


    public function getLinkOnly()
    {
        $last = $this->memory->retrieve();
        if (!empty($last)) {
            //no escape should be done in the client
            //$escaper = $this->getView()->plugin('escapeHtml');
            return $last;
        }
        return '';
    }

    public function getEscapedLinkOnly()
    {
        $last = $this->memory->retrieve();
        if (!empty($last)) {
            $escaper = $this->getView()->plugin('escapeHtml');
            return $last;
        }
        return '';
    }


}
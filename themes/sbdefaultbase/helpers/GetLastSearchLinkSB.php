<?php
/**
 *
 *
 * PHP version 5
 *
 * Copyright (C) University Library of Basel, Switzerland 2012
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
 * @category VuFind2 / Swissbib
 * @package
 * @author   Guenter Hipler <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.Swissbib.org   Main Site
 */
class VuFind_Theme_Greenprintbase_Helper_GetLastSearchLinkSB extends Zend_View_Helper_Abstract
{

    public function getLastSearchLinkSB()
    {
        $last = VF_Search_Memory::retrieve();
        if (!empty($last)) {
            return  $this->view->escape($last);

        }
        return '';
    }

}

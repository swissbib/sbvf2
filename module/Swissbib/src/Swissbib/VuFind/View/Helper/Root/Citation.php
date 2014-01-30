<?php
/**
 * [...description of the type ...]
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 9/12/13
 * Time: 11:46 AM
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
 * @author   Maechler Markus
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
namespace Swissbib\VuFind\View\Helper\Root;

use VuFind\View\Helper\Root\Citation as VuFindCitation;
use VuFind\Exception\Date as DateException;

class Citation extends VuFindCitation
{

    /**
     * @override
     * @param \VuFind\RecordDriver\Base $driver Record driver object.
     *
     * @return Citation
     */
    public function __invoke($driver)
    {
        parent::__invoke($driver);
        $pubDates = $driver->tryMethod('getPublicationDates');

        if (empty($this->details['journal'])) {
            $this->details['pubDate'] = $this->view->publicationDateMarc($pubDates);
        } else {
            $this->details['pubDate'] = $this->view->publicationDateSummon($pubDates);
        }

        return $this;
    }


    /**
     * Get Custom citation.
     *
     * This function assigns all the necessary variables and then returns a Custom
     * citation.
     *
     * @return string
     */
    public function getCitationCustom()
    {
        $custom = array(
            'title' => $this->getAPATitle(),
            'authors' => $this->getAPAAuthors(),
            'edition' => $this->getEdition()
        );

        // Behave differently for books vs. journals:
        $partial = $this->getView()->plugin('partial');

        if (empty($this->details['journal'])) {
            return $partial('Citation/custom.phtml', $custom);
        } else {
            return $partial('Citation/custom-article.phtml', $custom);
        }
    }


    /**
     * @override
     * @return string
     */
    protected function getPublisher()
    {
        if (isset($this->details['pubName'])
            && !empty($this->details['pubName'])
        ) {
            return $this->details['pubName'];
        } else {
            return '';
        }
    }

    /**
     * @override
     * @return string
     */
    protected function getYear()
    {
        return !empty($this->details['pubDate']) ? $this->details['pubDate'] : '';
    }

}
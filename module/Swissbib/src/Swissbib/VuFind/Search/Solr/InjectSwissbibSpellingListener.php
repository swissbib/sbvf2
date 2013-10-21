<?php
 
 /**
 * [...description of the type ...]
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 9/19/13
 * Time: 8:33 PM
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


namespace Swissbib\VuFind\Search\Solr;

use VuFind\Search\Solr\InjectSpellingListener as VFSpellingListener;
use VuFindSearch\Service;
use Zend\EventManager\EventInterface;
use VuFindSearch\ParamBag;
use VuFindSearch\Backend\Solr\Response\Json\Spellcheck;
use VuFindSearch\Query\Query;




class InjectSwissbibSpellingListener  extends VFSpellingListener {



    /**
     * Set up spelling parameters.
     *
     * @param EventInterface $event Event
     *
     * @return EventInterface
     */
    public function onSearchPre(EventInterface $event)
    {
        $backend = $event->getTarget();
        if ($backend === $this->backend) {
            $params = $event->getParam('params');
            if ($params) {
                // Set spelling parameters unless explicitly disabled:
                $sc = $params->get('swissbibspellcheck');
                if (!empty($sc) && $sc[0] != 'false') {

                    //remove the homegrown parameter only needed to activate the spellchecker in case of zero hits
                    $params->remove("swissbibspellcheck");
                    $this->active = true;
                    if (empty($this->dictionaries)) {
                        throw new \Exception(
                            'Spellcheck requested but no dictionary configured'
                        );
                    }

                    // Set relevant Solr parameters:
                    reset($this->dictionaries);
                    $params->set('spellcheck', 'true');
                    $params->set(
                        'spellcheck.dictionary', current($this->dictionaries)
                    );

                    // Turn on spellcheck.q generation in query builder:
                    $this->backend->getQueryBuilder()
                        ->setCreateSpellingQuery(true);
                }
            }
        }
        return $event;
    }


    protected function aggregateSpellcheck(Spellcheck $spellcheck, $query)
    {
        /*
            while (next($this->dictionaries) !== false) {
                $params = new ParamBag();
                $params->set('spellcheck', 'true');
                $params->set('spellcheck.dictionary', current($this->dictionaries));
                $queryObj = new Query($query, 'AllFields');
                $collection = $this->backend->search($queryObj, 0, 0, $params);
                $spellcheck->mergeWith($collection->getSpellcheck());
            }
        */
        foreach ( $this->dictionaries as $dictionary) {
            $params = new ParamBag();
            $params->set('spellcheck', 'true');
            $params->set('spellcheck.dictionary', $dictionary);
            $queryObj = new Query($query, 'AllFields');
            $collection = $this->backend->search($queryObj, 0, 0, $params);
            $spellcheck->mergeWith($collection->getSpellcheck());
        }

    }
}
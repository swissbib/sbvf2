<?php
 
 /**
 * extended version of the VuFind Solr Backend Factory
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 8/19/13
 * Time: 10:21 PM
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
 * @package  Swissbib\VuFind\Search
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */



namespace Swissbib\VuFindSearch\Backend\Solr;

use VuFindSearch\Backend\Solr\Backend as VuFindSearchBackend;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\ParamBag;
use VuFindSearch\Backend\Solr\Connector;
use VuFindSearch\Response\RecordCollectionInterface;






class Backend extends VuFindSearchBackend {



    /**
     * Constructor.
     *
     * @param Connector $connector SOLR connector
     *
     * @return void
     */
    public function __construct(Connector $connector)
    {

        parent::__construct($connector);

    }



    /**
     * Perform a search and return record collection.
     *
     * @param AbstractQuery $query  Search query
     * @param integer       $offset Search offset
     * @param integer       $limit  Search limit
     * @param ParamBag      $params Search backend parameters
     *
     * @return RecordCollectionInterface
     */
    public function search(AbstractQuery $query, $offset, $limit,
                           ParamBag $params = null
    ) {
        $params = $params ?: new ParamBag();
        $this->injectResponseWriter($params);

        $spellcheck = $params->get('spellcheck.q');

        $tSpellcheck = null;
        $collection = null;

        if ($spellcheck) {

            $paramsToUse = array("spellcheck","spellcheck.q","qf","json.nl","wt","q","q.op","qt");
            $spellParams = new ParamBag();
            foreach($params->getArrayCopy() as $key => $value)
            {
                if (in_array($key,$paramsToUse)) {

                    $spellParams->add($key,$value);

                }

            }




            //if ($spellcheck) {
            //    if (empty($this->dictionaries)) {
            //        $this->log(
            //            'warn',
            //            'Spellcheck requested but no spellcheck dictionary configured'
            //        );
            //        $spellcheck = false;
            //    } else {
            //        reset($this->dictionaries);
            //        $params->set('spellcheck', 'true');
            //        $params->set('spellcheck.dictionary', current($this->dictionaries));
            //    }
            //}




            $this->setDictionaries(array("default","basicSpell"));

            foreach($this->dictionaries as $dictionary) {
                $spellParams->set("spellcheck.dictionary",$dictionary);
                $spellParams->set("rows",0);

                //todo: build new ParamBag
                //$params->remove("hl.fragsize");
                //$params->remove("hl.simple.pre");
                //$params->remove("hl.simple.post");
                //$params->remove("hl.fl");
                //$params->remove("hl");

                $params->mergeWith($this->getQueryBuilder()->build($query));
                $response   = $this->connector->search($spellParams);
                $collection = $this->createRecordCollection($response);

                $tt = $collection->getSpellcheck();
                if ($tSpellcheck == null) {
                    $tSpellcheck = $collection->getSpellcheck();

                    foreach($tt as $key => $value) {
                        $bla = "";
                    }


                } else {

                    $t1 = $tt->count();

                    $tt->mergeWith($tSpellcheck);
                    $t1 = $tt->count();


                    foreach($tt as $key => $value) {

                        $bla = "";

                    }


                    $bravo = "";

                }








            }

            //todo: is injectSourceIdentifier necessary while SpellChecking?


        } else {


            $params->set('rows', $limit);
            $params->set('start', $offset);
            $params->mergeWith($this->getQueryBuilder()->build($query));
            $response   = $this->connector->search($params);
            $collection = $this->createRecordCollection($response);
            $this->injectSourceIdentifier($collection);
        }
        //if ($spellcheck) {


        //$spellcheckQuery = $params->get('spellcheck.q');
        //}

        return $collection;
    }



}
<?php


/**
 * swissbib extended Results type for the Solr target
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 11/05/13
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
 * @package  Swissbib\Search\Solr
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

namespace Swissbib\Search\Solr;

use VuFind\Search\Solr\Results as VFSolrResults;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;
//use VuFindSearch\Query\Query;
use VuFindSearch\ParamBag;
//use VuFindSearch\Backend\Solr\Response\Json\Spellcheck;



/**
 * Class to extend the core VF2 SOLR functionality related to Solr Results
 */

class Results extends VFSolrResults {

    public function __construct(Params $params)
    {
        parent::__construct($params);
    }

    //public function performAndProcessSearch() {
    //    parent::performAndProcessSearch();
    //}


    //protected function performSearch () {
    //    parent::performSearch();
    //}


    protected function createBackendParameters(AbstractQuery $query, Params $params)  {

        $paramBag = parent::createBackendParameters($query,$params);

        //only as an example how to use
        $paramBag->add("q.op","AND");
        return $paramBag;

    }

}
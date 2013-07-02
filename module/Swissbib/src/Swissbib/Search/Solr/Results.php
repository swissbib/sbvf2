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


    protected function createBackendParameters(AbstractQuery $query, Params $params)  {

        $paramBag = parent::createBackendParameters($query,$params);

        //with SOLR 4.3 AND is no longer the default parameter
        $paramBag->add("q.op","AND");


        //create query parameters for favorites
        $favoriteInstitutions =  $this->getParams()->getUserFavoritesInstitutions();
        if (sizeof( $favoriteInstitutions > 0 )) {

            //facet parameter has to be true in case it's false
            $paramBag->remove("facet");
            $paramBag->add("facet","true");

            foreach ($favoriteInstitutions as $instititution) {

                $paramBag->add("facet.query","institution:" . $instititution);
                $paramBag->add("facet.query","institution:" . $instititution);

            }

            foreach ($favoriteInstitutions as $instititution) {

                $paramBag->add("bq","institution:" . $instititution . "^5000");

            }

        }

        return $paramBag;

    }


    public function getFavoritesFacets() {

        $favoritesWithNonZero = array();
        $iterator  = $this->responseFacets->getQueryFacets()->getIterator();;

        while($iterator->valid()) {

            if (strpos($iterator->key(), "institution:" ) === 0 && $iterator->current() > 0) {

                $tParts = explode(":",$iterator->key());

                $facetItem = array(
                    "value" => $tParts[1],
                    "displayText" => $tParts[1], //todo: how to translate the value??
                    "count" => $iterator->current(),
                    "isApplied" => false //todo: lookup used filters to decide if this item should be applied
                );

                $favoritesWithNonZero [] = $facetItem;
            }

            $iterator->next();
        }

        return $favoritesWithNonZero;
    }

}
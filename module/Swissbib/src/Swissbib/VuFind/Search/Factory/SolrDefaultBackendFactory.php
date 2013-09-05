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
 * @author   Fabian Erni
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */



namespace Swissbib\VuFind\Search\Factory;

use VuFind\Search\Factory\SolrDefaultBackendFactory as VuFindSolrDefaultBackendFactory;
use VuFind\Search\Solr\V4\ErrorListener;
use Swissbib\VuFindSearch\Backend\Solr\Backend;

use Swissbib\Highlight\SolrConfigurator as HighlightSolrConfigurator;
use VuFindSearch\Backend\Solr\Connector;
use VuFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory;

/**
 * [Description]
 *
 */
class SolrDefaultBackendFactory extends VuFindSolrDefaultBackendFactory
{

	protected function createListeners(Backend $backend)
	{
		parent::createListeners($backend);

		$this->attachHighlightSolrConfigurator($backend);
	}


	protected function attachHighlightSolrConfigurator(Backend $backend)
	{
//		$events = $this->serviceLocator->get('SharedEventManager');

		/** @var HighlightSolrConfigurator $highlightListener */
		$highlightListener = $this->serviceLocator->get('Swissbib\Highlight\SolrConfigurator');

		$highlightListener->attach($backend/*, $events*/);
	}


    /**
     * Create the SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector)
    {



        $config  = $this->config->get('config');
        $backend = new Backend($connector);
        $backend->setQueryBuilder($this->createQueryBuilder());

        // Spellcheck
        if (isset($config->Spelling->enabled) && $config->Spelling->enabled) {
            if (isset($config->Spelling->simple) && $config->Spelling->simple) {
                $dictionaries = array('basicSpell');
            } else {
                $dictionaries = array('default', 'basicSpell');
            }
            $backend->setDictionaries($dictionaries);
        }

        if ($this->logger) {
            $backend->setLogger($this->logger);
        }



        $manager = $this->serviceLocator->get('VuFind\RecordDriverPluginManager');
        $factory = new RecordCollectionFactory(array($manager, 'getSolrRecord'));
        $backend->setRecordCollectionFactory($factory);
        return $backend;



    }



}

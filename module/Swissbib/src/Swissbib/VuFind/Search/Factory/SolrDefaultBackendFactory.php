<?php
namespace Swissbib\VuFind\Search\Factory;

use VuFind\Search\Factory\SolrDefaultBackendFactory as VuFindSolrDefaultBackendFactory;
use VuFind\Search\Solr\V4\ErrorListener;
use VuFindSearch\Backend\Solr\Backend;

use Swissbib\Highlight\SolrConfigurator as HighlightSolrConfigurator;

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
}

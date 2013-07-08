<?php
namespace Swissbib\VuFind\Search\Solr;

use VuFind\Search\Solr\Params as VuFindSolrParams;

/*
 * Class to extend the core VF2 SOLR functionality related to Parameters
 */
class Params extends VuFindSolrParams
{

	/**
	 * Override to prevent problems with namespace
	 * See implementation of parent for details
	 *
	 * @return	String
	 */
	public function getSearchClassId()
	{
		return 'Solr';
	}
}

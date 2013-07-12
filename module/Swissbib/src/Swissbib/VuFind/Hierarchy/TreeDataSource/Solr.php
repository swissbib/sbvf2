<?php
namespace Swissbib\VuFind\Hierarchy\TreeDataSource;

use VuFind\Hierarchy\TreeDataSource\Solr as VuFindTreeDataSourceSolr;
use VuFindSearch\Query\Query;

/**
 * Override Solr tree data source
 *
 */
class Solr extends VuFindTreeDataSourceSolr
{
	/** New child record limit to prevent timeout */
	const TREE_LIMIT = 500;


	/**
	 * @inheritDoc
	 * @note Just changed the hard limit for child records to prevent timeouts
	 */
	protected function getChildren($parentID, &$count)
	{
		$query   = new Query(
			'hierarchy_parent_id:"' . addcslashes($parentID, '"') . '"'
		);
		$results = $this->searchService->search('Solr', $query, 0, self::TREE_LIMIT);
		if ($results->getTotal() < 1) {
			return '';
		}
		$xml     = array();
		$sorting = $this->getHierarchyDriver()->treeSorting();

		foreach ($results->getRecords() as $current) {
			++$count;
			if ($sorting) {
				$positions = $current->getHierarchyPositionsInParents();
				if (isset($positions[$parentID])) {
					$sequence = $positions[$parentID];
				}
			}

			$this->debug("$parentID: " . $current->getUniqueID());
			$xmlNode      = '';
			$isCollection = $current->isCollection() ? "true" : "false";
			$xmlNode .= '<item id="' . htmlspecialchars($current->getUniqueID()) .
					'" isCollection="' . $isCollection . '"><content><name>' .
					htmlspecialchars($current->getTitle()) . '</name></content>';
			$xmlNode .= $this->getChildren($current->getUniqueID(), $count);
			$xmlNode .= '</item>';
			array_push($xml, array((isset($sequence) ? $sequence : 0), $xmlNode));
		}

		if ($sorting) {
			$this->sortNodes($xml, 0);
		}

		$xmlReturnString = '';
		foreach ($xml as $node) {
			$xmlReturnString .= $node[1];
		}
		return $xmlReturnString;
	}
}

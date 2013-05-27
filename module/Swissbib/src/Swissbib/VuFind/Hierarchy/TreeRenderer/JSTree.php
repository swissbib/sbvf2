<?php
namespace Swissbib\VuFind\Hierarchy\TreeRenderer;

use VuFind\Hierarchy\TreeRenderer\JSTree as VfJsTree;

/**
 * Temporary override to fix problem with invalid solr data (count of top ids does not match top titles)
 *
 * @package Swissbib\VuFind\Hierarchy\TreeRenderer
 */
class JSTree extends VfJsTree
{

	/**
	 * @todo	Fix solr index or implement core fix
	 * @inheritDoc
	 */
	public function getTreeList($hierarchyID = false)
	{
		$record             = $this->getRecordDriver();
		$id                 = $record->getUniqueID();
		$inHierarchies      = $record->getHierarchyTopID();
		$inHierarchiesTitle = $record->getHierarchyTopTitle();

		if ($hierarchyID) {
			// Specific Hierarchy Supplied
			if (in_array($hierarchyID, $inHierarchies)
					&& $this->getDataSource()->supports($hierarchyID)
			) {
				return array(
					$hierarchyID => $this->getHierarchyName(
						$hierarchyID, $inHierarchies, $inHierarchiesTitle
					)
				);
			}
		} else {
			// Return All Hierarchies
			$i           = 0;
			$hierarchies = array();
			foreach ($inHierarchies as $hierarchyTopID) {
				if ($this->getDataSource()->supports($hierarchyTopID)) {
					$hierarchies[$hierarchyTopID] = isset($inHierarchiesTitle[$i]) ? $inHierarchiesTitle[$i] : '';
				}
				$i++;
			}
			if (!empty($hierarchies)) {
				return $hierarchies;
			}
		}

		// If we got this far, we couldn't find valid match(es).
		return false;
	}



	/**
	 * Prevent error on empty xml file
	 *
	 * @todo	Implement real fix for this (in code?)
	 * @inheridDoc
	 */
	protected function transformCollectionXML($context, $mode, $hierarchyID, $recordID)
	{
		$xmlFile = $this->getDataSource()->getXML($hierarchyID);

		if (empty($xmlFile)) {
			return 'Missing data for tree rendering';
		}

		return parent::transformCollectionXML($context, $mode, $hierarchyID, $recordID);
	}

}

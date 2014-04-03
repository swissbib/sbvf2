<?php
namespace Swissbib\VuFind\Hierarchy\TreeRenderer;

use VuFind\Hierarchy\TreeRenderer\JSTree as VfJsTree;
use VuFindSearch\Query\Query;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Temporary override to fix problem with invalid solr data (count of top ids does not match top titles)
 *
 * @package Swissbib\VuFind\Hierarchy\TreeRenderer
 */
class JSTree extends VfJsTree implements ServiceLocatorAwareInterface
{

    protected $serviceLocator;
    protected $searchService;


    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator= $serviceLocator;
        $this->searchService = $serviceLocator->getServiceLocator()->get('VuFind\Search');
    }



    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }



    /**
     * Prevent error from missing hierarchy title data
     *
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

            // Return dummy tree list (for top most records)
        if ($id && $this->hasChildren($id)) {
            return array(
                $id => 'Unknown hierarchie title'
            );
        }

        // If we got this far, we couldn't find valid match(es).
        return false;
    }



    /**
     * Check whether item has children in hierarchy
     *
     * @param    String        $id
     * @return    Boolean
     */
    protected function hasChildren($id)
    {
        $query = new Query(
            'hierarchy_parent_id:"' . addcslashes($id, '"') . '"'
        );
        $results    = $this->searchService->search('Solr', $query, 0, 1);

        return $results->getTotal() > 0;
    }



    /**
     * Prevent error on empty xml file
     *
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



    /**
     * Prevent error from missing title
     *
     * @inheritDoc
     */
    public function getHierarchyName($hierarchyID, $inHierarchies, $inHierarchiesTitle)
    {
        if (in_array($hierarchyID, $inHierarchies)) {
            $keys = array_flip($inHierarchies);
            $key = $keys[$hierarchyID];

            if (isset($inHierarchiesTitle[$key])) {
                return $inHierarchiesTitle[$key];
            }
        }

        return 'No title found';
    }
}

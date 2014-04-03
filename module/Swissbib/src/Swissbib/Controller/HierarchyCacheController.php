<?php
namespace Swissbib\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

use VuFind\Search\Solr\Results as SolrResults;

use Swissbib\VuFind\Hierarchy\TreeDataSource\Solr as TreeDataSourceSolr;

/**
 * Console controller for hierarchy cache generation
 *
 */
class HierarchyCacheController extends AbstractActionController
{

    /**
     * Build cache files
     *
     * @return    String
     */
    public function buildCacheAction()
    {
        $counter = 1;
        /** @var ConsoleRequest $request */
        $request = $this->getRequest();
        $verbose = $request->getParam('verbose', false) || $request->getParam('v', false);
        $limit   = $request->getParam('limit');

        echo "Start building hierarchy tree cache in local/cache/hierarchy\n";

        if ($limit) {
            echo "Limit for child records is set to $limit\n";
        }

        echo "\n";

        $recordLoader = $this->getServiceLocator()->get('VuFind\RecordLoader');
        /** @var SolrResults $solrResults */
        $solrResults = $this->getServiceLocator()->get('VuFind\SearchResultsPluginManager')->get('Solr');
        $hierarchies = $solrResults->getFullFieldFacets(array('hierarchy_top_id'));

        foreach ($hierarchies['hierarchy_top_id']['data']['list'] as $hierarchy) {
            if ($verbose) {
                echo "Building tree for {$hierarchy['value']} (" . ($counter++) . ")\n";
            }

            $driver = $recordLoader->load($hierarchy['value']);
                // Only do this if the record is actually a hierarchy type record
            if ($driver->getHierarchyType()) {
                /** @var TreeDataSourceSolr $treeDataSource */
                $treeDataSource = $driver->getHierarchyDriver()->getTreeSource();

                if ($limit) {
                    $treeDataSource->setTreeChildLimit(1000);
                }

                $treeDataSource->getXML($hierarchy['value'], array('refresh' => true));
            }
        }

        return "Building of hierarchy cache finished. Created " . ($counter-1) . " cache files\n";
    }
}

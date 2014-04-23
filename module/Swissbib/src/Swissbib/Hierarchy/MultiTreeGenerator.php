<?php

namespace Swissbib\Hierarchy;

use Zend\Config\Config;
use Swissbib\Hierarchy\SimpleTreeGenerator;


class MultiTreeGenerator {

    /**
     * @var array
     */
    protected $treeConfig = array();



    /**
     * @var SimpleTreeGenerator
     */
    protected $simpleTreeGenerator;



    /**
     * @param Config              $config
     * @param SimpleTreeGenerator $simpleTreeGenerator
     */
    public function __construct(Config $config, SimpleTreeGenerator $simpleTreeGenerator) {
        $this->setTreeConfig($config);
        $this->simpleTreeGenerator = $simpleTreeGenerator;
    }



    /**
     * @param array $facetList
     *
     * @return array
     */
    public function getTrees(array $facetList) {
        $treesToGenerate = array_intersect(array_keys($facetList), $this->treeConfig);
        $generatedTrees = array();

        foreach ($treesToGenerate as $tree) {
            $generatedTrees[$tree] = $this->simpleTreeGenerator->getTree($facetList[$tree]['list'], $tree);
        }

        return $generatedTrees;
    }



    /**
     * @param Config $config
     */
    protected function setTreeConfig(Config $config) {
        if ($config->Site->classificationTrees instanceof Config) {
            $this->treeConfig = $config->Site->classificationTrees->toArray();
        } else {
            $this->treeConfig = array();
        }
    }

} 
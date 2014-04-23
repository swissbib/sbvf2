<?php

namespace Swissbib\Hierarchy;

use Zend\Cache\Storage\Adapter\Filesystem as ObjectCache;

class SimpleTreeGenerator {

    /**
     * @var ObjectCache
     */
    private $objectCache;



    /**
     * @param ObjectCache $objectCache
     */
    public function __construct(ObjectCache $objectCache) {
        $this->objectCache = $objectCache;
    }



    /**
     * @param array     $datas
     * @param string    $currentNode
     *
     * @return string
     */
    private function generatePageTree(array &$datas, $currentNode = ""){
        $tree = array();

        $currentNodeHead = explode(".", $currentNode);
        $currentNodeHead = $currentNodeHead[0];

        foreach($datas as $key => $data){
            $datasParent = explode(".", $data['value']);
            $head = $datasParent[0];

            if (!empty($currentNodeHead) && $head > $currentNodeHead) break;

            array_pop($datasParent);
            $parent = implode(".", $datasParent);

            if ($parent === $currentNode) {
                unset($datas[$key]);
                $tree[] = array(
                    "entry" => $data,
                    "children" => $this->generatePageTree($datas, $data['value'])
                );
            }
        }

        return $tree;
    }



    /**
     * Orders Facets and removes wrong instances. For instance D 14.5, D 14.5 e and D 14.5 CH get trunked to D 14.5
     * @param $arrayList
     *
     * @return array
     */
    private function orderAndFilter(array $arrayList = array()) {
        $sorted = array();

        foreach ($arrayList as $classification) {
            preg_match_all("/[0-9]/",$classification['value'],$out,PREG_OFFSET_CAPTURE );
            $lastMatch = end($out[0]);
            $key = substr($classification['value'],0,$lastMatch[1]+1);

            if (!isset($sorted[$key])) {
                $sorted[$key] = $classification;
                $sorted[$key]['queryValue'] = $key;
            } else {
                $sorted[$key]['count'] += $classification['count'];
            }
            $sorted[$key]['value'] = $key;
        }

        uksort($sorted, 'strnatcmp');

        return $sorted;
    }



    /**
     * @param   array $facets
     * @param   string $treeKey
     *
     * @return  array
     */
    public function getTree(array $facets = array(), $treeKey = '') {
        $cacheTreeId    = 'simpleTree-' . $treeKey;
        $cachedTree     = $this->objectCache->getItem($cacheTreeId);

        if (is_array($cachedTree))  return $cachedTree;
        if ($treeKey === '')        return $this->generatePageTree($this->orderAndFilter($facets));

        $tree = $this->generatePageTree($this->orderAndFilter($facets));
        $this->objectCache->setItem($cacheTreeId, $tree);

        return $tree;
    }

} 
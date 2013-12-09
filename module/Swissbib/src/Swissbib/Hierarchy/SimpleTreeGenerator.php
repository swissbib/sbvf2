<?php


namespace Swissbib\Hierarchy;


class SimpleTreeGenerator {


    /**
     * @var mixed
     */
    private $facets;



    /**
     * @param $facets
     */
    public function __construct($facets) {
        $this->facets = $facets;
    }



    /**
     * @param        $datas
     * @param string $currentNode
     *
     * @return string
     */
    private function generatePageTree(&$datas, $currentNode = ""){
        $tree = [];

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
                $tree[] = [
                    "entry" => $data,
                    "children" => $this->generatePageTree($datas, $data['value'])
                ];
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
    private function orderAndFilter($arrayList) {
        $sorted = [];

        foreach ($arrayList as $classification) {
            preg_match_all("/[0-9]/",$classification['value'],$out,PREG_OFFSET_CAPTURE );
            $lastMatch = end($out[0]);
            $key = substr($classification['value'],0,$lastMatch[1]+1);

            if (!isset($sorted[$key])) {
                $sorted[$key] = $classification;
                $sorted[$key]['queryValue'] = $classification['value'];
            } else {
                $sorted[$key]['count'] += $classification['count'];
                $sorted[$key]['queryValue'] .= " OR " . $classification['value'];
            }
            $sorted[$key]['value'] = $key;
        }

        uksort($sorted, 'strnatcmp');

        return $sorted;
    }



    /**
     * @return array
     */
    public function getTree() {
        return $this->generatePageTree($this->orderAndFilter($this->facets));
    }

} 
<?php
/**
 * Created by PhpStorm.
 * User: swissbib
 * Date: 5/12/14
 * Time: 10:47 AM
 */

namespace Swissbib\VuFind\Search\Backend\Solr;


use VuFindSearch\Backend\Solr\LuceneSyntaxHelper as VFCoreLuceneSyntaxHelper;

class LuceneSyntaxHelper extends VFCoreLuceneSyntaxHelper {

    protected function prepareForLuceneSyntax($input) {

        $input = parent::prepareForLuceneSyntax($input);

        //user complained:
        //"Das medizinische Berlin – Ein Stadtführer durch 300 Jahre Geschichte" wasn't found because of the special character copied from Wikipedia
        //will be converted to:
        //"Das medizinische Berlin Ein Stadtführer durch 300 Jahre Geschichte"
        $patterns = array("/\xE2\x80\x93/");
        //in case you want more patterns to remove
        //$patterns = array("/\xE2\x80\x93/", "/Das/");

        $input=  preg_replace($patterns,'',$input);

        return $input;

    }



} 
<?php
namespace Swissbib\XSLT;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class MARCFormatter implements ServiceManagerAwareInterface
{
    /**
     * @var array
     */

    private static $sM;

    protected static $institutionURLs = array(
        "NEBIS" => "http://opac.nebis.ch/F/?local_base=EBI01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "IDSBB" => "http://aleph.unibas.ch/F/?local_base=DSV01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "IDSSG2" => "http://aleph.unisg.ch/F?local_base=HSB02&con_lng=GER&func=direct&doc_number=%s",
        "IDSSG" => "http://aleph.unisg.ch/F?local_base=HSB01&con_lng=GER&func=direct&doc_number=%s",
        "SBT" => "http://aleph.sbt.ti.ch/F?local_base=SBT01&con_lng=ITA&func=find-b&find_code=SYS&request=%s",
        "SNL" => "http://opac.admin.ch/cgi-bin/gw/chameleon?lng=de&inst=consortium&search=KEYWORD&function=CARDSCR&t1=%s&u1=12101",
        "RERO" => "http://opac.rero.ch/gateway?beginsrch=1&lng=de&inst=consortium&search=KEYWORD&function=CARDSCR&t1=%s&u1=12",
        "IDSLU" => "http://ilu.zhbluzern.ch/F/?local_base=ILU01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "OCoLC" => "http://www.worldcat.org/search?q=no%3A%s",
        "CCSA" => "http://opac.admin.ch/cgi-bin/gw/chameleon?skin=affiches&lng=de&inst=consortium&search=KEYWORD&function=INITREQ&t1=%s&u1=12101",
        "CHARCH" => "http://www.helveticarchives.ch/detail.aspx?ID=%s",
        "BGR" => "http://aleph.gr.ch/F/?local_base=BGR01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "ABN" => "http://aleph.ag.ch/F/?local_base=ABN01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "SGBN" => "http://aleph.sg.ch/F/?local_base=SGB01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "ALEX" => "http://opac.admin.ch/cgi-bin/gwalex/chameleon?lng=de&inst=consortium&skin=portal&search=KEYWORD&function=CARDSCR&t1=%s&u1=12101",
        "DDB" => "http://d-nb.info/%s",
        "RETROS" => "http://retro.seals.ch/oai/dataprovider?verb=GetRecord&metadataPrefix=oai_dc&identifier=%s",
        "ZORA" => "http://www.zora.uzh.ch/cgi/oai2?verb=GetRecord&metadataPrefix=oai_dc&identifier=%s"
        //"ALEX" => "http://www.alexandria.ch/primo_library/libweb/action/dlSearch.do?institution=BIG&vid=ALEX&scope=default_scope&query=lsr07,contains,vtls%s-41big_inst",
    );



    /**
     * @var array
     */
    protected static $trimPrefixes = array(
        "vtls",
        "on",
        "ocn",
        "ocm",
        "cha"
    );


    /**
     * @param array $domArray
     *
     * @return mixed
     */
    public static function compileSubfield(array $domArray)
    {
        $domNode = $domArray[0];
        if ($domNode->parentNode !== null && $domNode->parentNode->getAttribute('tag') != '035') return $domNode; //return before trying to find institution

        $nodeValue = preg_replace('/\s+/', '', $domNode->textContent);
        $institution = self::getInstitutionFromNodeText($nodeValue);

        if ($domNode->getAttribute('code') != 'a' || empty($institution)) {
            return $domNode;
        } else {
            $request = substr($nodeValue, strlen($institution) + 2);
            $request = str_replace(self::$trimPrefixes, '', $request);
            $url = str_replace('%s', $request, self::$institutionURLs[$institution]);

            $pW =  static::$sM->get("Swissbib\Services\RedirectProtocolWrapper");

            return '<a href="' . $pW->getWrappedURL( $url) . '" target="_blank">' . htmlentities('(' . $institution . ')' . $request) . '</a>';
        }
    }



    /**
     * @param String $nodeText
     *
     * @return String
     */
    protected static function getInstitutionFromNodeText($nodeText)
    {
        preg_match('/\(([a-zA-Z0-9]+)\)/', $nodeText, $matches);

        if (count($matches) == 0) {
            return '';
        }
        $match = $matches[1];
        if (!empty($match)) {
            foreach (self::$institutionURLs as $key => $value) {
                if ($match === $key) {
                    return $key;
                }
            }
        }

        return '';
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        static::$sM = $serviceManager;
    }


}
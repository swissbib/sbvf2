<?php
namespace Swissbib\XSLT;


class MARCFormatter
{
    /**
     * @var array
     */
    protected static $institutionURLs = array(
        "NEBIS"  => "http://opac.nebis.ch/F/?local_base=EBI01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "IDSBB"  => "http://aleph.unibas.ch/F/?local_base=DSV01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "IDSUZH" => "https://biblio.unizh.ch/F/?local_base=UZH01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "IDSSG"  => "http://aleph.unisg.ch/F?local_base=HSB01&con_lng=GER&func=direct&doc_number=%s",
        "IDSSG2" => "http://aleph.unisg.ch/F?local_base=HSB02&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "SBT"    => "http://aleph.sbt.ti.ch/F?local_base=SBT01&con_lng=ITA&func=find-b&find_code=SYS&request=%s",
        "SNL"    => "http://opac.admin.ch/cgi-bin/gw/chameleon?lng=de&inst=consortium&search=KEYWORD&function=CARDSCR&t1=%s&u1=12101",
        "RERO"   => "http://opac.rero.ch/gateway?beginsrch=1&lng=de&inst=consortium&search=KEYWORD&function=CARDSCR&t1=%s&u1=12101",
        "IDSLU"  => "http://ilu.zhbluzern.ch/F/?local_base=ILU01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "OCoLC"  => "http://www.worldcat.org/search?q=no%3A%s",
        "CCSA"   => "http://opac.admin.ch/cgi-bin/gw/chameleon?skin=affiches&lng=de&inst=consortium&search=KEYWORD&function=INITREQ&t1=%s&u1=12101",
        "CHARCH" => "http://www.helveticarchives.ch/detail.aspx?ID=%s",
        "BGR"    => "http://aleph.gr.ch/F/?local_base=BGR01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "ABN"    => "http://aleph.ag.ch/F/?local_base=ABN01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "SGBN"   => "http://aleph.sg.ch/F/?local_base=SGB01&con_lng=GER&func=find-b&find_code=SYS&request=%s",
        "ALEX"   => "http://opac.admin.ch/cgi-bin/gwalex/chameleon?lng=de&inst=consortium&skin=portal&search=KEYWORD&function=CARDSCR&t1=%s&u1=12101"
    );



    /**
     * @param array $domArray
     *
     * @return mixed
     */
    public static function compileSubfield(array $domArray)
    {
        $domNode = $domArray[0];
        $nodeValue = trim($domNode->textContent);
        $institution = self::getInstitutionFromNodeText($nodeValue);

        if ($domNode->parentNode->getAttribute('tag') != '035' || $domNode->getAttribute('code') != 'a' || empty($institution)) {
            return $domNode;
        } else {
            $request = substr($nodeValue, strlen($institution) + 2);
            $url = str_replace('%s', $request, self::$institutionURLs[$institution]);

            return '<a href="' . $url . '" target="_blank">' . htmlentities($nodeValue) . '</a>';
        }
    }



    /**
     * @param String $nodeText
     *
     * @return String
     */
    protected static function getInstitutionFromNodeText($nodeText)
    {
        foreach (self::$institutionURLs as $key => $value) {
            if (strpos($nodeText, $key) !== false) {
                return $key;
            }
        }

        return '';
    }

}
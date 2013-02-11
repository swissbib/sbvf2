<?php

namespace Swissbib\RecordDriver\Helper;

/**
 * swissbib / VuFind <<full descriptive name of the class>>
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 2/7/13
 * Time: 9:02 PM
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category swissbib_VuFind2
 * @package  <<name of package>>
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     << link to further documentation related to this resource type (Wiki, tracker ...)
 */


/**
 * probably HoldingsHelper should be a subtype of ZF2 AbstractHelper
 *at first I need a better understanding how things are wired up in this case using means of ZF2
 */
class HoldingsHelper implements HoldingsAwareInterface
{

    private $rawHoldingsData;
    private $holdingsType;

    private $parsed = false;



    private function parseRawData() {

        $tHoldingsType  = new \File_MARCXML($this->rawHoldingsData, \File_MARCXML::SOURCE_STRING);

        $this->holdingsType = $tHoldingsType->next();
        if (!$this->holdingsType) {
            throw new \File_MARC_Exception('Cannot Process Holdings Structure');
        }

        $parsed = true;


    }


    public function  getHoldings () {

        if (!$this->parsed)  $this->parseRawData();

        $t949 = $this->getFieldValues("949");
        $t852 = $this->getFieldValues("852");
        $allholdings =  array_merge($t949,$t852);
        return $allholdings;

    }



    /**
     * Returns an array of all values extracted from the specified holdings field
     * either 949 / 852
     * the returned array is defined in the order
     * @param string $field  The MARC field number to read
     * @return array[networkid][institutioncode] = array() of values for the current item
     */
    private function getFieldValues($field)
    {

        // Initialize return array
        $matches = array();

        // Try to look up the specified field, return empty array if it doesn't
        // exist.
        $fields = $this->holdingsType->getFields($field);
        if (!is_array($fields)) {
            return $matches;
        }

        // Extract all the File_MARC_Data_Field 's for the requested tag number
        foreach ($fields as $currentField) {

            $tempArray = array();
            //now get the subfields for the current data field
            //it's really weird I can only use the getSubfields - method and not the
            //getSubfield("subfieldcode") method as documented for File_MARC_Data_Field
            //don't know why so far...
            //therefor the little hack to fill a temporary array with all the given subfield - values
            $allSubfields = $currentField->getSubfields();
            if (count($allSubfields) > 0) {
                foreach($allSubfields as $subcode => $subdata) {
                    $tempArray[$subcode] = $subdata->getData();
                }

                $item = array();
                //some documentation
                //http://www.swissbib.org/wiki/index.php?title=Members:Item-holding-url
                // I guess we need more differantiation between Aleph and Virtua systems - tbd later
                //these are only first examples
                $item["bibsysnumber"] = $tempArray["E"]; // should always available - I guess...
                $item["barcode"] =  array_key_exists("p",$tempArray) ?  $tempArray["p"] : "";
                $item["location_expanded"] = array_key_exists("1",$tempArray)? $tempArray["1"] : "" ; //Standort (Expandiert)
                $item["local_branch_expanded"] = array_key_exists("0",$tempArray)? $tempArray["0"] : "" ; //Zweigstelle (Expandiert)
                $item["signature2"] = array_key_exists("s",$tempArray)? $tempArray["s"] : "" ; //signature 2
                $item["adm_code"] = array_key_exists("C",$tempArray)? $tempArray["C"] : "" ; //adm_code -> more general name?
                $item["opac_note"] = array_key_exists("y",$tempArray)? $tempArray["y"] : "" ; //opac note
                $item["holding_information"] = array_key_exists("a",$tempArray)? $tempArray["a"] : "" ; //holding information


                //$tempArray["B"] -> networkcode
                //[$tempArray["b"] -> institution code
                //[$tempArray["q"] -> local record id

                $matches[$tempArray["B"]][$tempArray["b"]][$tempArray["q"]] = $item;

            }

        }

        return $matches;
    }



    /**
     * Set holdings structure
     * @param $holdings
     */
    public function setHoldingsContent($holdings)
    {
        $this->rawHoldingsData = $holdings;
    }


    public function getTestData() {

        return
            $testholdings = <<<EOT
        <record>
            <datafield tag="949" ind1=" " ind2=" ">
                <subfield code="B">RERO</subfield>
                <subfield code="E">vtls0034515</subfield>
                <subfield code="b">A100</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">1889908238</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
            <datafield tag="949" ind1=" " ind2=" ">
                <subfield code="B">RERO</subfield>
                <subfield code="E">vtls003451557</subfield>
                <subfield code="b">610650002</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">1889908238</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
            <datafield tag="949" ind1=" " ind2=" ">
                <subfield code="B">RERO</subfield>
                <subfield code="E">vtls003451557</subfield>
                <subfield code="b">1234</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">1889908238</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
            <datafield tag="852" ind1=" " ind2=" ">
                <subfield code="B">IDSBB</subfield>
                <subfield code="E">sysnr</subfield>
                <subfield code="b">1234</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">recid</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
        </record>
EOT;

    }


}

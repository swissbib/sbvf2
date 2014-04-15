<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vfsb
 * Date: 10/16/13
 * Time: 3:55 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Swissbib\VuFind\Date;

use VuFind\Date\Converter as VFConverter;
use DateTime, VuFind\Exception\Date as DateException;


class Converter extends VFConverter{



    /**
     * Generic method for conversion of a time / date string
     *
     * @param string $inputFormat  The format of the time string to be changed
     * @param string $outputFormat The desired output format
     * @param string $dateString   The date string
     *
     * @throws DateException
     * @return string               A re-formated time string
     */
    public  function convert($inputFormat, $outputFormat, $dateString)
    {
        $errors = "Date/time problem: Details: ";

        // For compatibility with PHP 5.2.x, we have to restrict the input formats
        // to a fixed list...  but we'll check to see if we have access to PHP 5.3.x
        // before failing if we encounter an input format that isn't whitelisted.
        $validFormats = array(
            "m-d-Y", "m-d-y", "m/d/Y", "m/d/y", "U", "m-d-y H:i", "Y-m-d",
            "Y-m-d H:i"
        );
        $isValid = in_array($inputFormat, $validFormats);
        if ($isValid) {
            if ($inputFormat == 'U') {
                // Special case for Unix timestamps:
                $dateString = '@' . $dateString;
            } else {
                // Strip leading zeroes from date string and normalize date separator
                // to slashes:
                $regEx = '/0*([0-9]+)(-|\/)0*([0-9]+)(-|\/)0*([0-9]+)/';
                $dateString = trim(preg_replace($regEx, '$1/$3/$5', $dateString));
            }
            $getErrors = array(
                'warning_count' => 0, 'error_count' => 0, 'errors' => array()
            );
            try {
                $date = new DateTime($dateString);
            } catch (\Exception $e) {
                $getErrors['error_count']++;
                $getErrors['errors'][] = $e->getMessage();
            }
        } else {
            if (!method_exists('DateTime', 'createFromFormat')) {
                throw new DateException(
                    "Date format {$inputFormat} requires PHP 5.3 or higher."
                );
            }
            $date = DateTime::createFromFormat($inputFormat, $dateString);
            $getErrors = DateTime::getLastErrors();
        }

        if ($getErrors['warning_count'] == 0
            && $getErrors['error_count'] == 0 && $date
        ) {
            return $date->format($outputFormat);
        } else {
            //yymd
            //todo GH: just an intermediary solution because we get an conversion error using the content sent by Aleph
            //dates with 00000000
            //e.g.: http://alephtest.unibas.ch:1891/rest-dlf/patron/B219684/circulationActions/loans/?view=full
            //<z30-inventory-number-date>00000000</z30-inventory-number-date>
            return DateTime::createFromFormat('yymd', '19000101');

            //if (is_array($getErrors['errors']) && $getErrors['error_count'] > 0) {
            //    foreach ($getErrors['errors'] as $error) {
            //        $errors .= $error . " ";
            //    }
            //} else if (is_array($getErrors['warnings'])) {
            //    foreach ($getErrors['warnings'] as $warning) {
            //        $errors .= $warning . " ";
            //    }
            //}

            //throw new DateException($errors);
        }
    }


}
<?php
namespace Swissbib\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Show availability infos
 */
class AvailabilityInfo extends AbstractHelper
{
    /** Expected status codes */
    const LENDABLE_AVAILABLE = "lendable_available"; // generell ausleihbar und vorhandene Exemplare
    const LENDABLE_BORROWED = "lendable_borrowed"; // generell ausleihbar, jedoch bereits ausgeliehene Exemplare
    const USE_ON_SITE = "use-on-site"; // vor Ort einsehbare Exemplare (Lesesaal)
    const LOOK_ON_SITE = "lookOnSite"; // Informationsabruf über das lokale System (fallback)
    const EXHIBITION = "exhibition"; // nicht einsehbar, extern ausgestellt (mit Datum bis)
    const INPROCESS = "inProcess";
    const ONLINE_AVAILABLE = "onlineAvailable"; // by now only for ETH, could be enhanced for other library systems (labels for LoanStatus needed!)
    const UNAVAILABLE = "unavailable"; // vermisst, in Reparatur, abbestellt: Exemplar für Benutzer verloren
    const SUBSTITUTE = "substitute";

    /**
     * Convert availability info into html string
     *
     * @param    Boolean|Array $availability
     * @return    String
     */

    public function __invoke($availability)
    {
        $escapedTranslation = $this->getView()->plugin('transEsc');

        /* availability always contains an associative  array with only 'one' key (the barcode of the item)
         * (this method is called for every single item)
         * the barcode references an additional array (derived from json) which contains the so called statusfield
         * the value of the statusfield is part of the translation files
         */

        if (is_array($availability)) {

            $statusfield = self::LOOK_ON_SITE;
            $borrowinginformation = array();

            foreach ($availability as $barcode => $availinfo) {
                $statusfield = $availinfo["statusfield"];

                if (isset ($availinfo["borrowingInformation"])) {
                    $borrowinginformation = $availinfo["borrowingInformation"];

                }
            }

            switch ($statusfield) {
                case self::LENDABLE_AVAILABLE:

                    $info = "<div class='availability_ok'>&nbsp;</div>";
                    break;
                case self::LENDABLE_BORROWED:

                    unset($borrowinginformation['due_hour']);
                    $info = "<div class='availability_notok'>";

                    if ($borrowinginformation['due_date'] === 'on reserve') {
                        $info .= $escapedTranslation('On Reserve') . " (" . $borrowinginformation['no_requests'] . ")";
                    }
                    elseif ($borrowinginformation['due_date'] === 'claimed returned') {
                        $info .= $escapedTranslation('Claimed Returned');
                    }
                    elseif ($borrowinginformation['due_date'] === 'lost') {
                        $info .= $escapedTranslation('Lost');
                    }
                    else {
                        foreach ($borrowinginformation as $key => $value) {
                            if (strcmp(trim($value), "") != 0) {
                                $info .= "<div class='nice'>" . $escapedTranslation($key) . "&nbsp;" . $value . "</div>";
                            }
                        }
                    }

                    $info .= "</div>";

                    break;
                case self::USE_ON_SITE:

                    $infotext = $escapedTranslation($statusfield);
                    $info = "<div class='availability_ok'>" . "$infotext" . "</div>";
                    break;
                case self::LOOK_ON_SITE:

                    $infotext = $escapedTranslation($statusfield);
                    $info = "<div class='availability_moreInfo'><div class='nice'>" . "$infotext" . "</div></div>";
                    break;
                case self::EXHIBITION:

                    unset($borrowinginformation['due_hour']);
                    $infotext = $escapedTranslation($statusfield);
                    $info = "<div class='availability_notok'>Ausstellung";

                    if ($borrowinginformation['due_date'] === 'on reserve') {
                        $info .= $escapedTranslation('On Reserve') . " (" . $borrowinginformation['no_requests'] . ")";
                    } else {
                        foreach ($borrowinginformation as $key => $value) {
                            if (strcmp(trim($value), "") != 0) {
                                $info .= "<div class='nice'>" . $escapedTranslation($key) . "&nbsp;" . $value . "</div>";
                            }
                        }
                    }

                    $info .= "</div>";

                    break;
                case self::INPROCESS:
                    $infotext = $escapedTranslation($statusfield);
                    $info = "<div class='availability_ok'><div class='nice'>" . "$infotext" . "</div></div>";
                    break;
                case self::ONLINE_AVAILABLE:

                    //do something special for online resources (dedicated icon and / or text?)
                    $info = $escapedTranslation($statusfield);
                    break;
                case self::UNAVAILABLE:
                case self::SUBSTITUTE:

                $infotext = $escapedTranslation($statusfield);
                    $info = "<div class='availability_notok'>" . "$infotext" . "</div>";
                    break;
                default:
                    /**
                     * Any other value defined in the availability service
                     * should be translated in the language files of VuFind (local/languages/)
                     */
                    $info = $escapedTranslation($statusfield);
            }

        } else {
            $info = $escapedTranslation('no_ava_info');
        }

        return $info;
    }
}

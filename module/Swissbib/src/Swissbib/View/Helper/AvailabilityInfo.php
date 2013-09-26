<?php
namespace Swissbib\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Show availability infos
 *
 */
class AvailabilityInfo extends AbstractHelper
{
	/** Expected status codes */
	const LENDABLE_AVAILABLE = "lendable_available";    //  -> genrell ausleihbar und vorhanden
	const LENDABLE_BORROWED = "lendable_borrowed";      //  -> generell ausleihbar jedoch bereits ausgeliehen
    const LOOK_ON_SITE      = "lookOnSite";             //  -> Informationsabruf über das lokale System (fallback)
    const ONLINE_AVAILABLE   = "onlineAvailable";        //  -> by now only for ETH could be enhanced for other library systems (labels for LoanStatus needed!)



	/**
	 * Convert availability info into html string
	 *
	 * @param	Boolean|Array		$availability
	 * @return	String
	 */
	public function __invoke($availability)
	{

        //availability always contains an associative  array with only 'one' key (the barcode of the item)
        //(this method is called for every single item)
        //the barcode references an additional array (derived from json) which contains the so called statusfield
        //the value of the statusfield is part of the translation files

		if (is_array($availability)) {

            $escapedTranslation =  $this->getView()->plugin('transEsc');


            $statusfield = self::LOOK_ON_SITE;
            $borrowinginformation = array();

            foreach ($availability as  $barcode => $availinfo ) {

                $statusfield = $availinfo["statusfield"];

                if (isset ($availinfo["borrowingInformation"]) ){

                    $borrowinginformation = $availinfo["borrowingInformation"];

                }
            }


			switch ($statusfield) {
				case self::LENDABLE_AVAILABLE:

                    $info = "<div class='availability_ok'>&nbsp;</div>";
                    break;
				case self::LENDABLE_BORROWED:

                    $info = "<div class='availability_notok'>" . "<br/>";
                    foreach ($borrowinginformation as $key => $value) {

                        if (strcmp(trim($value),"") != 0) {
                            $info .=  "<div class='nice'>" . $escapedTranslation($key) . "&nbsp;" . $value . "</div>";
                        }
                    }

                    $info .= "</div>";

					break;
                case self::LOOK_ON_SITE:
                    $info = $escapedTranslation($statusfield);
                    break;
                case self::ONLINE_AVAILABLE:
                    //do something special for online resources (dedicated icon and / or text?)
                    $info = $escapedTranslation($statusfield);
                    break;
				default:
                    //any other value defined in the availabiluty service
                    //should be translated in the language file on vufind site
                    $info = $escapedTranslation($statusfield);
			}

		} else {
			$info = 'No data';
		}

		return $info;
	}
}

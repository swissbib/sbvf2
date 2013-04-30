<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view script for formatting publication dates from Summon control field 008 format
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @author   Oliver Schihin oliver.schihin@unibas.ch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class YearFormatterSummon extends AbstractHelper
{

	/**
	 * @param   Array   $publicationDate
	 * @return  String
	 * @todo	still a copy of the solrMarc year formatter viewHelper, adapt to summon
	 */
	public function __invoke($publicationDate)
	{
		if (!is_array($publicationDate) || sizeof($publicationDate) == 0) {
			return '';
		}

		$retVal = '';

		$dateType = $publicationDate[0];
		$year1    = $publicationDate[1];
		$year2    = $publicationDate[2];

		switch ($dateType) {
			case 's':
			case 'n':
			case 'e':
				$retVal = $year1;
				break;

			case 'c':
			case 'u':
				$retVal = $year1 . '-';
				break;

			case 'd':
				$retVal = $year1 . '-' . $year2;
				break;

			case 'p':
			case 'r':
				$retVal = $year1 . ' [' . $year2 . ']';
				break;

			case 'q':
				if ($year2 === '9999') {
					$retVal = $year1;
				} elseif ($year2 != '9999') {
					$retVal = $year1 . ' / ' . $year2;
				}
				break;

			case 'm':
				if ($year2 === '9999') {
					$retVal = $year1 . '-';
				} elseif ($year2 != '9999') {
					$retVal = $year1 . '-' . $year2;
				}
				break;
		}

		return str_replace('u', '?', $retVal);
	}
}

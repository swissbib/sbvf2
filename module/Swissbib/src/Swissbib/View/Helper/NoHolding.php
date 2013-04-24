<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * "No holding" view script
 *
 * @category swissbib_VuFind2
 * @package  ViewHelpers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

class NoHolding extends AbstractHelper
{

	/**
	 * @return  String
	 */
	public function __invoke()
	{

		return 'Keine Bestandsangaben';
	}
}

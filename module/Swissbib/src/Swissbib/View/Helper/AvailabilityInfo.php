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
	const STATUS_NO_INFO = 0;
	const STATUS_AVAILABLE = 1;
	const STATUS_NOT_AVAILABLE = 2;


	/**
	 * Convert availability info into html string
	 *
	 * @param	Boolean|Array		$availability
	 * @return	String
	 */
	public function __invoke($availability)
	{
		if (is_array($availability)) {
			$status	= intval($availability['availStatus']);

			switch ($status) {
				case self::STATUS_NO_INFO:
				case self::STATUS_AVAILABLE:
					$info = $availability['loanIcon'];
					break;

				case self::STATUS_NOT_AVAILABLE:
					$info = $availability['loanIcon'];

					if (isset($availability['dueDate']) && !empty($availability['dueDate'])) {
						$info .= $availability['dueDate'];
					}
					if (isset($availability['numberRequests']) && $availability['numberRequests'] > 0) {
						$info .= '(' . $availability['numberRequests'] . ')';
					}
					break;

				default:
					$info = 'Invalid status';
			}

			if (isset($availability['wholeMessage']) && !empty($availability['wholeMessage'])) {
				$info .= $availability['wholeMessage'];
			}
		} else {
			$info = 'No data';
		}

		return $info;
	}
}

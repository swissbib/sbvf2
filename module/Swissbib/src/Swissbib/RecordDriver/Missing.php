<?php
namespace Swissbib\RecordDriver;

use \VuFind\RecordDriver\Missing as VFMissing;

class Missing extends VFMissing
{

	/**
	 * Get short title
	 * Override base method to assure a string and not an array
	 *
	 * @return    String
	 */
	public function getTitle()
	{
		$title = parent::getTitle();

		if (is_array($title)) {
			$title = reset($title);
		}

		return $title;
	}



	/**
	 * Get short title
	 * Override base method to assure a string and not an array
	 *
	 * @return    String
	 */
	public function getShortTitle()
	{
		$shortTitle = parent::getShortTitle();

		if (is_array($shortTitle)) {
			$shortTitle = reset($shortTitle);
		}

		return $shortTitle;
	}
}

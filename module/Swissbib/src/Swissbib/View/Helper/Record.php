<?php
namespace Swissbib\View\Helper;

use VuFind\View\Helper\Root\Record as VuFindRecord;

/**
 * Build record links
 * Override related method to support ctrlnum type
 *
 */
class Record extends VuFindRecord
{

	/**
	 *
	 * @param	String		$name
	 * @param 	Array|null	$context
	 * @return	String
	 */
	protected function renderTemplate($name, $context = null)
	{
		$html = parent::renderTemplate($name, $context);

		return $this->removeTemplateWrapping($html);
	}



	/**
	 * Remove wrap info for this template
	 *
	 * @param	String		$html
	 * @return	String
	 */
	protected function removeTemplateWrapping($html)
	{
		$html = preg_replace('/^\n<!-- Begin: (.*?) -->/', '', $html);
		$html = preg_replace('/<!-- End: (.*?) -->$/', '', $html);

		return $html;
	}
}

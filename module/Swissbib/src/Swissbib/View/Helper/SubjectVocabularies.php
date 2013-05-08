<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Format subject vocabularies
 *
 */
class SubjectVocabularies extends AbstractHelper
{

	/**
	 * Build info string from subject vocabulary data
	 *
	 * @param    Array        $subjectVocabularies
	 * @return   String
	 */
	public function __invoke(array $subjectVocabularies)
	{
		return '$subjectVocabularies view helper';
	}
}

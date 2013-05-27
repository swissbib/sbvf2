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
		$titleLcsh		= '';
		$titleGnd		= '';
		$titleMesh		= '';
		$titleBisacsh	= '';
		$titleIdszbz	= '';

        foreach ($subjectVocabularies as $vocab => $subjectVocabulary) {
            switch($vocab) {
                case 'gnd':
                    $titleGnd = '<h4>gnd</h4>';
//                    foreach ($subjectVocabulary as $fields) {
//                        $subject = '';
//                        foreach ($fields as $field) {
//                            if (!is_numeric($field->getKey()));
//                        };
//                    }
                    break;
                case 'lcsh':
                    $titleLcsh = '<h4>lcsh</h4>';
                    break;
                case 'mesh':
                    $titleMesh = '<h4>MESH</h4>';
                    break;
                case 'bisacsh':
                    $titleBisacsh = '<h4>bisacsh</h4>';
                    break;
                case 'ids zbz':
                    $titleIdszbz = '<h4>ZBZ-Schlagworte</h4>';
                    break;
                // to be continued
            }
            continue;
        }

        return $titleGnd . $titleLcsh . $titleMesh . $titleBisacsh . $titleIdszbz;
	}
}

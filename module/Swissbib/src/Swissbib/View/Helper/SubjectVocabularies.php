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
        foreach ($subjectVocabularies as $vocab => $subjectVocabulary) {
            switch($vocab) {
                case 'gnd':
                    $gndtitle = '<h4>gnd</h4>';
                    foreach ($subjectVocabulary as $fields) {
                        foreach ($fields as $field) {
                            //if (!is_numeric($field->getKey()));
                        };
                    }
                    break;
                case 'lcsh':
                    $lcshtitle = '<h4>lcsh</h4>';
                    break;
                case 'mesh':
                    $meshtitle = '<h4>MESH</h4>';
                    break;
                // to be continued
                case 'bisacsh':
                    $bisacshtitle = '<h4>bisacsh</h4>';
                    break;
                case 'ids zbz':
                    $idszbztitle = '<h4>IDS ZB Z&uuml;rich</h4>';
                    break;
            }
            continue;
        }
        return $gndtitle . $lcshtitle . $meshtitle . $bisacshtitle . $idszbztitle;
	}
}

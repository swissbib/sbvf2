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
		$titleLcsh		= '<h4>LCSH</h4>';
		$titleGnd		= '<h4>gnd</h4>';
		$titleMesh		= '<h4>MESH</h4>';
		$titleBisacsh	= '<h4>BISAC</h4>';
		$titleIdszbz	= '<h4>IDS ZBZ</h4>';
		$itemlist		= '';

        foreach ($subjectVocabularies as $vocab => $subjectVocabulary) {
            switch($vocab) {
                case 'gnd':
					$terms = array();
                    foreach ($subjectVocabulary as $fields) {
                        foreach ($fields as $subfields); {
                            if (isset($subfields['a'])) {
                                $terms[] = '<p>' . $subfields['a'] . '</p>';
                            };
                        };
                        $itemlist = implode('', $terms);
                    }
                    break;
                    //}
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

        return $titleGnd . $itemlist . $titleLcsh . $titleMesh . $titleBisacsh . $titleIdszbz;
	}
}

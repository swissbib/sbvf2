<?php
namespace Swissbib\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * Filter out untranslated institutions from list
 *
 */
class FilterUntranslatedInstitutions extends AbstractTranslatorHelper
{

    /**
     * Filter institutions
     *
     * @param    String[]    $institutionCodes
     * @return    String[]
     */
    public function __invoke($institutionCodes)
    {
        $filtered = array();

            // Filter not translated institutions
        foreach ($institutionCodes as $institutionCode) {
            if ($institutionCode !== $this->translator->translate($institutionCode, 'institution')) {
                $filtered[] = $institutionCode;
            }
        }

        return $filtered;
    }
}

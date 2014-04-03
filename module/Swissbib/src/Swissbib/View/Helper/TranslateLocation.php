<?php
namespace Swissbib\View\Helper;

use Zend\I18n\Translator\Translator;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * Translate locations
 *
 */
class TranslateLocation extends AbstractTranslatorHelper
{
    /**
     * Translate location
     *
     * @param    String        $network
     * @param    String        $subLibrary
     * @param    String        $code
     * @param    String|Null    $locale
     * @return    String
     */
    public function __invoke($network, $subLibrary, $code, $locale = null)
    {
        $labelKey    = strtolower($subLibrary . '_' . $code);
        $textDomain    = 'location-' . strtolower($network);

        return $this->translator->translate($labelKey, $textDomain, $locale);
    }
}

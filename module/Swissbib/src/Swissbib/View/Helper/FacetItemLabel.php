<?php
namespace Swissbib\View\Helper;

use Zend\I18n\View\Helper\Translate;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\EscapeHtml;
use Swissbib\View\Helper\Number;

/**
 * Renders a facet item label
 */
class FacetItemLabel extends AbstractHelper
{

    /**
     * @var    EscapeHtml
     */
    protected $escaper;

    /**
     * @var    Translate    Zend translate view helper
     */
    protected $translator;

    /** @var  Number */
    protected $number;

    /**
     * Mapping for special facets to textDomain. Text domains need to be added in bootstrapper initLanguage()
     *
     * @var    Array
     */
    protected $customTranslations = array(
        'institution' => 'institution',
        'union'       => 'union',
    );



    /**
     *
     * @param   Array          $facet
     * @param    String        $facetType
     * @return  String
     */
    public function __invoke(array $facet, $facetType)
    {
        $displayText = trim($facet['displayText']);
        $count       = intval($facet['count']);

        if (!isset($this->escaper)) {
            $this->escaper    = $this->getView()->plugin('escapeHtml');
            $this->number    = $this->getView()->plugin('number');
        }
        $escaper = $this->escaper;
        $number  = $this->number;

        if (isset($this->customTranslations[$facetType])) {
            if (!isset($this->translator)) {
                $this->translator = $this->getView()->plugin('zendTranslate');
            }
            $translator  = $this->translator;
            $textDomain  = $this->customTranslations[$facetType];
            $displayText = $translator($displayText, $textDomain);
        }

        return $escaper($displayText) . '&nbsp;(' . $number($count) . ')';
    }
}

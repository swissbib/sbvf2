<?php
namespace Swissbib\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use QRCode\Service\QRCode as QRCodeService;

/**
 * Build holding qr code url
 *
 */
class QrCodeHolding extends AbstractTranslatorHelper
{
    /** @var  QrCode */
    protected $qrCodeHelper;



    /**
     * Build CRCode image source url for holding
     *
     * @param    Array    $item
     * @param    String    $recordTitle
     * @return    String
     */
    public function __invoke(array $item, $recordTitle = '')
    {
        if (!$this->qrCodeHelper) {
            $this->qrCodeHelper = $this->getView()->plugin('qrCode');
        }

        $data = array();

        if (!empty($recordTitle)) {
            $data[] = $recordTitle;
        }
        if (!empty($item['institution'])) {
            $data[] = $this->translator->translate($item['institution'], 'institution');
        }
        if (!empty($item['locationLabel'])) {
            $data[] = $item['locationLabel'];
        }
        if (!empty($item['signature'])) {
            $data[] = $item['signature'];
        }

        $text        = implode(', ', $data);
        $qrCodeUrl    = $this->qrCodeHelper->source($text, 250, false);

        return $this->getView()->render('Holdings/qr-code', array(
                                                                 'item'    => $item,
                                                                 'url'    => $qrCodeUrl,
                                                                 'text'    => $text
                                                            ));
    }
}

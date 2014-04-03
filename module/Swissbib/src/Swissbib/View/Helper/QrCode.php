<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Swissbib\CRCode\QrCodeService;

/**
 * Build a qr code link or image
 *
 */
class QrCode extends AbstractHelper
{
    /** @var QRCodeService  */
    protected $qrCodeService;



    /**
     * Initialize with service
     *
     * @param QRCodeService $qrCodeService
     */
    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;

            // set defaults
        $this->qrCodeService->setDimensions(100, 100);
        $this->qrCodeService->isHttp();
    }



    /**
     * Read config from options array
     * Return a new copy of the qr code service
     *
     * @param    Array    $options
     * @return    QRCodeService
     */
    protected function build(array $options)
    {
        $qrCode = clone $this->qrCodeService;

        if (isset($options['data'])) {
            $encode = isset($options['encodeData']) ? $options['encodeData'] : true;

            $qrCode->setData($options['data'], $encode);
        }
        if (isset($options['charset'])) {
            $qrCode->setCharset($options['charset']);
        }
        if (isset($options['correctionLevel'])) {
            $qrCode->setCorrectionLevel($options['correctionLevel']);
        }
        if (isset($options['dimensions'])) {
            if (is_array($options['dimensions'])) {
                list($with,$height) = $options['dimensions'];
            } else {
                list($with,$height) = explode(',', $options['dimensions']);
            }
            $qrCode->setDimensions($with, $height);
        }
        if (isset($options['https'])) {
            $options['https'] ? $qrCode->isHttps() : $qrCode->isHttp();
        }

        return $qrCode;
    }



    /**
     * Get URl only
     *
     * @param    Array    $options
     * @return    String
     */
    public function url(array $options)
    {
        return $this->build($options)->getResult();
    }



    /**
     * @param array $options
     * @return string
     */
    public function img(array $options)
    {
        $qrCode = $this->build($options);
        $class    = isset($options['class']) && $options['class'] ? ' class="' . $options['class'] . '"' : '';
        $title    = isset($options['title']) && $options['title'] ? ' title="' . $options['title'] . '"' : '';

        list($w,$h)    = explode('x', $qrCode->getDimensions());

        return '<img src="' . $qrCode->getResult() . '" width="' . $w . '" height="' . $h . '"' . $class . $title . '>';
    }



    /**
     * Simplified version of img
     * Get full image tag
     *
     * @param    String        $text
     * @param    Integer        $size
     * @param    Boolean        $encode
     * @return    String
     */
    public function image($text, $size, $encode = true)
    {
        return $this->img(array(
                               'data'        => $text,
                               'encodeData'    => !!$encode,
                               'dimensions' => array($size, $size)
                          ));
    }



    /**
     * Simplified version of url
     *
     * @param    String        $text
     * @param    Integer        $size
     * @param    Boolean        $encode
     * @return    String
     */
    public function source($text, $size, $encode = true)
    {
        return $this->url(array(
                               'data'        => $text,
                               'encodeData'    => !!$encode,
                               'dimensions' => array($size, $size)
                          ));
    }
}

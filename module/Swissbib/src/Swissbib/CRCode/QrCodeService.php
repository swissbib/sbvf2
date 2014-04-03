<?php
namespace Swissbib\CRCode;

use QRCode\Service\QRCode;
use Zend\Config\Config;

/**
 * Extend qrcode service. Allow not encoded content
 *
 */
class QrCodeService extends QRCode
{

    /**
     * Set data
     *
     * @param    String        $data
     * @param    Boolean        $encode        Encode text as url
     * @return    $this
     */
    public function setData($data, $encode = true)
    {
        $this->properties['chl'] = $encode ? urlencode($data) : $data;

        return $this;
    }
}

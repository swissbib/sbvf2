<?php

namespace QRCode\Service;
/**
 * QRCode service for Zend Framework 2
 */
class QRCode {

    /**
     * Properties for the qrcode
     * @var Array
     */
	protected $properties = array();
    
    /**
     * The Final Endpoint
     * @var String The final endpoint 
     */
    protected $endpoint = null;
    
    /**
     * The Start endpoint
     */
    const END_POINT = 'chart.googleapis.com/chart?';

    function __construct() {
        $this->setCharset();
        $this->setCorrectionLevel();
        $this->setTypeChart();
    }
    
    /**
     * Is Http?
     */
    public function isHttp()
    {
        $this->endpoint = 'http://'.self::END_POINT;
        return $this;
    }
    /**
     * Is Https?
     */
    public function isHttps()
    {
        $this->endpoint = 'https://'.self::END_POINT;
        return $this;
    }

    /**
     * Set chart type, here 'qr' is default chart type, mainly for qrcode 
     * @param String $chart Chart Type
     */
    public function setTypeChart($chart = 'qr') {
        $this->properties['cht'] = $chart;
        return $this;
    }

    /**
     * Returns the chart type
     * @return String 
     */
    public function getTypeChart() {
        return $this->properties['cht'];
    }

    /**
     * Get the link for image of qrcode
     * @return String
     */
    public function getResult() {
        return $this->endpoint.http_build_query($this->properties);
    }

    /**
     * Set dimensions (width, height) of image
     * @param Integer $w Width of image
     * @param Integer $h Height of image
     * @throws \InvalidArgumentException
     */
    public function setDimensions($w, $h) {
        if (is_int($w) && is_int($h)) {
            $this->properties['chs'] = "{$w}x{$h}";
        } else {
            throw new \InvalidArgumentException('The parameter $w and $h must be integer type');
        }
        return $this;
    }

    /**
     * Return the dimensions of image
     * @return String
     */
    public function getDimensions() {
        return $this->properties['chs'];
        
    }

    /**
     * Set the charset of content data. Default is 'UTF-8'
     * @param String $charset charset of content data
     */
    public function setCharset($charset = 'UTF-8') {
        $this->properties['choe'] = $charset;
        return $this;
    }

    /**
     * Return the charset of content data
     * @return String
     */
    public function getCharset() {
        return $this->properties['choe'];
    }
    
    /**
     * Set level of loss of content and margin of image
     * @param String $cl Level of loss
     * @param Integer $m Margin of image
     */
    public function setCorrectionLevel($cl = 'L',$m = 0)
    {
        $this->properties['chld'] = "{$cl}|{$m}";
        return $this;
    }
    
    /**
     * Return level of loss of content and margin of image
     * @return String
     */
    public function getCorrectionLevel()
    {
        return $this->properties['chld'] ;
    }
    
    /**
     * Set content data in urlencode format
     * @param String $data Content
     */
    public function setData($data)
    {
        $this->properties['chl'] = urlencode($data);
        return $this;
    }
    /**
     * Return the content data in urldecode format.
     * @return String
     */
    public function getData()
    {
        return urldecode($this->properties['chl']);
    }
}
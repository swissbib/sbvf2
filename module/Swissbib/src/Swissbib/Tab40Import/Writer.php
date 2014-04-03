<?php
namespace Swissbib\Tab40Import;

use Zend\Config\Config;
use Zend\Config\Writer\Ini as IniWriter;

/**
 * Write tab40 data to label file
 *
 */
class Writer
{
    /** @var String    Base path for storage */
    protected $basePath;



    /**
     * Initialize with base path
     *
     * @param    String        $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = realpath($basePath);
    }



    /**
     * Write data to label file
     *
     * @param    String        $network
     * @param    String        $locale
     * @param    Array[]        $data
     * @return    String        Path to file
     */
    public function write($network, $locale, array $data)
    {
        $data    = $this->convertData($data);
        $config    = new Config($data, false);
        $writer    = new IniWriter();

        $pathFile    = $this->buildPath($network, $locale);

        $writer->toFile($pathFile, $config);

        return $pathFile;
    }



    /**
     * Convert data to label file format
     *
     * @param    Array    $data
     * @return    Array
     */
    protected function convertData(array $data)
    {
        $labelData = array();

        foreach ($data as $item) {
            $key    = strtolower($item['sublibrary'] . '_' . $item['code']);
            $label    = str_replace('"', '', $item['label']);

            $labelData[$key] = $label;
        }

        return $labelData;
    }



    /**
     * Build file path based on base path, network and locale
     *
     * @param    String        $network
     * @param    String        $locale
     * @return    String
     */
    protected function buildPath($network, $locale)
    {
        $network= strtolower(trim($network));
        $locale    = strtolower(trim($locale));

        $path    = $this->basePath . '/' . $network . '-' . $locale . '.ini';

        return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    }
}

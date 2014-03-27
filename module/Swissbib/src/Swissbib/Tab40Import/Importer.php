<?php
namespace Swissbib\Tab40Import;

use Zend\Config\Config;

/**
 * Import and convert a tab40 file into a vufind language file
 *
 */
class Importer
{
    /** @var  Config */
    protected $config;



    /**
     *
     *
     * @param    Config    $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }



    /**
     * Import data from source file and write to predefined path
     *
     * @param    String        $network
     * @param    String        $locale
     * @param    String        $sourceFile
     * @return    Result
     */
    public function import($network, $locale, $sourceFile)
    {
            // Read data
        $importedData    = $this->read($sourceFile);
            // Write data
        $languageFile    = $this->write($network, $locale, $importedData);

        return new Result(array(
            'file'        => $languageFile,
            'count'        => sizeof($importedData),
            'network'    => $network,
            'locale'    => $locale,
            'source'    => $sourceFile
        ));
    }



    /**
     * Read file into named list
     *
     * @param    String        $sourceFile
     * @return    Array[]
     */
    protected function read($sourceFile)
    {
        $reader    = new Reader();

        return $reader->read($sourceFile);
    }



    /**
     * Write imported data to language file
     *
     * @param    String        $network
     * @param    String        $locale
     * @param    Array[]        $data
     * @return    String
     */
    protected function write($network, $locale, array $data)
    {
        $writer    = new Writer($this->config->path);

        return $writer->write($network, $locale, $data);
    }
}

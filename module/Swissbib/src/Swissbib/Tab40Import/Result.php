<?php
namespace Swissbib\Tab40Import;

/**
 * Import result
 *
 */
class Result
{
    /** @var    Array  */
    protected $importData;



    /**
     * Initialize
     *
     * @param    Array    $importData
     */
    public function __construct(array $importData)
    {
        $this->importData = $importData;
    }



    /**
     * Get amount of imported items
     *
     * @return    Integer
     */
    public function getRecordCount()
    {
        return $this->importData['count'];
    }



    /**
     * Get generate file path
     *
     * @return    String
     */
    public function getFilePath()
    {
        return $this->importData['file'];
    }
}

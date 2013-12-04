<?php
namespace Libadmin\Institution;


use Zend\Json\Server\Exception\ErrorException;

class InstitutionLoader
{


    /**
     * @var string
     */
    protected $cacheDir;


    /**
     * @var string
     */
    protected $cacheFile;



    /**
     *
     */
    public function __construct()
    {
        $this->cacheDir     = realpath(APPLICATION_PATH . '/data/cache');
        $this->cacheFile    = 'libadmin_all.json';
    }



    /**
     * @throws ErrorException
     * @return array
     */
    public function getGroupedInstitutions()
    {
        $filePath   = $this->cacheDir . '/' . $this->cacheFile;
        $cacheData  = file_exists($filePath) ? file_get_contents($filePath) : '';
        $jsonData   = json_decode($cacheData,true);

        if (empty($jsonData['data'])) throw new ErrorException("No valid library data supplied.");

        return $jsonData['data'];
    }

} 
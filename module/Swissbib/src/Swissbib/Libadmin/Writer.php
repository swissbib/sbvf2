<?php
namespace Swissbib\Libadmin;

use Zend\Config\Writer\Ini as IniWriter;
use Zend\Config\Config;

/**
 * Write imported data to local system
 *
 */
class Writer
{

	/**
	 * @var	String
	 */
	protected $basePath;



	/**
	 * Initialize with base path
	 * Defaults base path is languages in override dir
	 *
	 * @param	String|Null		$basePath
	 */
	public function __construct($basePath = null)
	{
		$this->setBasePath($basePath);
	}



	/**
	 * Set base path
	 * null or false resets to default base path
	 *
	 * @param	String|null $path
	 */
	protected function setBasePath($path)
	{
		if (is_null($path) || $path === false) {
			$this->basePath = LOCAL_OVERRIDE_DIR . '/languages';
		} else {
			$this->basePath = $path;
		}
	}



	/**
	 * Save language file data into defined folder (depends on type and locale)
	 *
	 * @param	Array	$data
	 * @param	String	$type
	 * @param	String	$locale
	 * @return	String
	 * @throws	\Exception
	 */
	public function saveLanguageFile(array $data, $type, $locale)
	{
		$pathFile = $this->basePath . '/' . $type . '/' . $locale . '.ini';
		$pathDir  = dirname($pathFile);
		$dirStatus= is_dir($pathDir) || mkdir($pathDir, 0777, true);

		if (!$dirStatus) {
			throw new \Exception('Cannot create language folder ' . $type);
		}

			// Replace double quotes, because they're invalid for ini format in zend
		foreach ($data as $key => $value) {
			$data[$key] = str_replace('"', '', $value);
		}

		$config	= new Config($data, false);
		$writer	= new IniWriter();

		$writer->toFile($pathFile, $config);

		return $pathFile;
	}
}

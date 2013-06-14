<?php
namespace Swissbib\Tab40Import;

use Zend\Config\Config;
use Zend\Config\Writer\Ini as IniWriter;

/**
 * [Description]
 *
 */
class Writer
{
	protected $basePath;

	public function __construct($basePath)
	{
		$this->basePath = $basePath;
	}

	public function write($network, $locale, array $data)
	{
		$data	= $this->convertData($data);
		$config	= new Config($data, false);
		$writer	= new IniWriter();

		$pathFile	= $this->buildPath($network, $locale);

		$writer->toFile($pathFile, $config);

		return array();
	}

	/**
	 * Clean data
	 * Cleanup: Remove double quotes
	 *
	 * @param	Array	$data
	 * @return	Array
	 */
	protected function convertData(array $data)
	{
		$labelData = array();

		foreach ($data as $item) {
			$key	= strtolower($item['sublibrary'] . '_' . $item['code']);
			$label	= str_replace('"', '', $item['label']);

			$labelData[$key] = $label;
		}

		return $labelData;


		$callback = function ($item) {
			return str_replace('"', '', $item);
		};

		return array_map($callback, $data);

//		foreach ($data as $key => $value) {
//			$data[$key] = str_replace('"', '', $value);
//		}
//
//		return $data;
	}



	/**
	 *
	 *
	 * @param	String		$network
	 * @param	String		$locale
	 * @return	String|Boolean
	 */
	protected function buildPath($network, $locale)
	{
		$network= strtolower(trim($network));
		$locale	= strtolower(trim($locale));

		return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($this->basePath) . '/' . $network . '-' . $locale . '.ini');
	}
}

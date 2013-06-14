<?php
namespace Swissbib\Tab40Import;

use Zend\Config\Config;

/**
 * [Description]
 *
 */
class Importer
{
	/** @var  Config */
	protected $config;



	/**
	 *
	 *
	 * @param	Config	$config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}



	/**
	 * Import data from source file and write to predefined path
	 *
	 * @param	String		$network
	 * @param	String		$locale
	 * @param	String		$sourceFile
	 * @return	Result
	 */
	public function import($network, $locale, $sourceFile)
	{
			// Read data
		$data	= $this->read($sourceFile);
			// Write data
		$result	= $this->write($network, $locale, $data);

		return new Result($result);
	}



	/**
	 * Read file into named list
	 *
	 * @param	String		$sourceFile
	 * @return	Array[]
	 */
	protected function read($sourceFile)
	{
		$reader	= new Reader();

		return $reader->read($sourceFile);
	}



	/**
	 *
	 *
	 * @param	String		$network
	 * @param	String		$locale
	 * @param	Array[]		$data
	 * @return	Array
	 */
	protected function write($network, $locale, array $data)
	{
		$writer	= new Writer($this->config->path);

		return $writer->write($network, $locale, $data);
	}
}

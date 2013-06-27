<?php
namespace Swissbib\Helper;

use Zend\Config\Config;

/**
 * Convert network code into bib code
 * Uses holding config for mapping
 *
 */
class BibCode
{
	/** @var  Array */
	protected $mapping;



	/**
	 *
	 *
	 * @param	Config	$alephNetworkConfig
	 */
	public function __construct(Config $alephNetworkConfig)
	{
		foreach ($alephNetworkConfig as $networkCode => $info) {
			list($url, $idls) = explode(',', $info);

			$this->mapping[$networkCode] = $idls;
		}
	}



	/**
	 * Get bib code for network code
	 *
	 * @param	String		$networkCode
	 * @return	String
	 */
	public function getBibCode($networkCode)
	{
		$networkCode = strtolower($networkCode);

		return isset($this->mapping[$networkCode]) ? $this->mapping[$networkCode] : '';
	}
}

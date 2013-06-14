<?php
namespace Swissbib\Tab40Import;

/**
 * [Description]
 *
 */
class Result
{
	/** @var	Array  */
	protected $importData;

	public function __construct(array $importData)
	{
		$this->importData = $importData;
	}

	public function getRecordCount()
	{

		return 444;
	}
}

<?php
namespace SwissbibTest\RecordDriver;

use VuFindTest\Unit\TestCase as VuFindTestCase;
use Swissbib\RecordDriver\SolrMarc as SolrMarcDriver;

/**
 * [Description]
 *
 */
class SolrMarcTestCase extends VuFindTestCase
{

	/**
	 * @var    SolrMarcDriver
	 */
	protected $driver;



	/**
	 * Initialize driver with fixture
	 *
	 * @param    String        $file
	 */
	public function initialize($file)
	{
		if (!$this->driver) {
			$this->driver = new SolrMarcDriver();
			$fixture      = $this->getFixtureData($file);

			$this->driver->setRawData($fixture);
		}
	}



	/**
	 * Get record fixture
	 *
	 * @param    String        $file
	 * @return    Array
	 */
	protected function getFixtureData($file)
	{
		return json_decode(file_get_contents(realpath(SWISSBIB_TEST_FIXTURES . '/' . $file)), true);
	}
}

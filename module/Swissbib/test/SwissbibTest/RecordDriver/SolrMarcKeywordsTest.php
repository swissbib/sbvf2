<?php
namespace SwissbibTest\RecordDriver;

/**
 * [Description]
 *
 */
class SolrMarcKeywordsTest extends SolrMarcTestCase {

	public function setUp() {
		$this->initialize('marc-keywords.json');
	}


	public function testGetTopicalTerms() {
		$topicTerms	= $this->driver->getTopicalTerms();

		$this->assertInternalType('array', $topicTerms);
		$this->assertEquals(8, sizeof($topicTerms));

		$this->assertArrayHasKey('@ind1', $topicTerms[0]);

			// Check order
		$this->assertEquals('Amulett', $topicTerms[0]['term']);
		$this->assertEquals('Islam', $topicTerms[1]['term']);
		$this->assertEquals('Islam', $topicTerms[2]['term']);
		$this->assertEquals('Blockdruck', $topicTerms[3]['term']);
		$this->assertEquals('Amulett', $topicTerms[4]['term']);

		$this->assertInternalType('array', $topicTerms[5]['form_subdivision']);
	}


	public function testGetAddedGeographicNames() {
		$geoNames	= $this->driver->getAddedGeographicNames();

		$this->assertInternalType('array', $geoNames);
		$this->assertEquals(2, sizeof($geoNames));

		$this->assertEquals('USA', $geoNames[0]['name']);

		$this->assertInternalType('array', $geoNames[0]['form_subdivision']);
		$this->assertEquals(4, sizeof($geoNames[0]['general_subdivision']));
		$this->assertEquals('Arabisch', $geoNames[0]['general_subdivision'][3]);
	}

}

?>
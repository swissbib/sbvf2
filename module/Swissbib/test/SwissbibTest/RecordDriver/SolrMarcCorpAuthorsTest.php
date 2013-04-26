<?php
namespace SwissbibTest\RecordDriver;

/**
 * [Description]
 *
 */
class SolrMarcCorpAuthorsTest extends SolrMarcTestCase
{

	public function setUp()
	{
		$this->initialize('marc-withcorpauthors.json');
	}



	public function testGetAddedCorporateNames()
	{
		$corpNames = $this->driver->getAddedCorporateNames();

		$this->assertInternalType('array', $corpNames);
		$this->assertEquals(1, sizeof($corpNames));

		$this->assertArrayHasKey('@ind1', $corpNames[0]);
		$this->assertArrayHasKey('@ind2', $corpNames[0]);

		$expect = 'Schule für Gestaltung Bern und Biel';
		$this->assertEquals($expect, $corpNames[0]['name']);
	}
}

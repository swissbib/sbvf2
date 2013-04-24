<?php
namespace SwissbibTest\RecordDriver;

/**
 * [Description]
 *
 */
class SolrMarcComplexTest extends SolrMarcTestCase
{

	public function setUp()
	{
		$this->initialize('marc-complex.json');
	}



	public function testGetFormattedContentNotes()
	{
		$notes = $this->driver->getFormattedContentNotes();

		$this->assertInternalType('array', $notes);
		$this->assertEquals(1, sizeof($notes));
		$this->assertArrayHasKey('responsibility', $notes[0]);
		$this->assertArrayHasKey('title', $notes[0]);

		$this->assertInternalType('array', $notes[0]['responsibility']);
		$this->assertInternalType('array', $notes[0]['title']);

		$this->assertEquals('(Goethe)', $notes[0]['responsibility'][0]);
		$this->assertEquals('1. Türkisches Schenkenlied ("Setze mir nicht, du Grobian, den Krug so derb vor die Nase!")', $notes[0]['title'][0]);
	}

}

?>
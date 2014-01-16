<?php
namespace SwissbibTest\RecordDriver;

use Swissbib\RecordDriver\SolrMarc;
use SwissbibTest\RecordDriver\SolrMarcTestCase;

/**
 * [Description]
 *
 */
class SolrMarcSubjectVocabulariesTest extends SolrMarcTestCase
{

	public function setUp()
	{
		$this->initialize('marc-subjectheadings.json');
	}



	public function testGetAllSubjectVocabularies()
	{
		$subjectVocabularies = $this->driver->getAllSubjectVocabularies();

		$this->assertInternalType('array', $subjectVocabularies);

		$this->assertEquals(2, sizeof($subjectVocabularies));
		$this->assertArrayHasKey('gnd', $subjectVocabularies);
		$this->assertArrayHasKey('lcsh', $subjectVocabularies);
//		$this->assertArrayHasKey('bisacsh', $subjectVocabularies);
//		$this->assertArrayHasKey('ids zbz', $subjectVocabularies);
		$this->assertArrayNotHasKey('local', $subjectVocabularies);

//		$this->assertEquals(0, $subjectVocabularies['lcsh']['650'][0]['@ind2']);

		$this->assertInternalType('array', $subjectVocabularies['gnd']);
		$this->assertArrayHasKey('650', $subjectVocabularies['gnd']);
	}
}

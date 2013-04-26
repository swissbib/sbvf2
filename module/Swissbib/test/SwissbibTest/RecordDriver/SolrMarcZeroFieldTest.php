<?php
namespace SwissbibTest\RecordDriver;

/**
 * [Description]
 *
 */
class SolrMarcSubjectHeadingsTest extends SolrMarcTestCase
{

	public function setUp()
	{
		$this->initialize('marc-zero-field-bug.json');
	}



	public function testZeroFieldInSubjectHeadings()
	{
		$subjectHeadings = $this->driver->getAllSubjectHeadings();

			// This item contains a zero field
		$testItem	= $subjectHeadings[5];

		$this->assertEquals('Indianer', $testItem['650a']);
		$this->assertEquals('(DE-588)4026718-0', $testItem['6500']);
	}
}

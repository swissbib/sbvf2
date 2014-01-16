<?php
namespace SwissbibTest\RecordDriver;

use Swissbib\RecordDriver\SolrMarc;
use SwissbibTest\RecordDriver\SolrMarcTestCase;

/**
 * [Description]
 *
 */
class SolrMarcSimpleTest extends SolrMarcTestCase
{

	public function setUp()
	{
		$this->initialize('marc-simple.json');
	}



	public function testPrimaryAuthor()
	{
		$primaryAuthor = $this->driver->getPrimaryAuthor(false);

		$this->assertInternalType('array', $primaryAuthor);
		$this->assertEquals('Telemann', $primaryAuthor['name']);
		$this->assertEquals('Georg Philipp', $primaryAuthor['forname']);

	}



	public function testGetUniqueId()
	{
		$id = $this->driver->getUniqueID();

		$this->assertEquals('005378974', $id);
	}



	public function testGetPublicationDates()
	{
		$dates = $this->driver->getPublicationDates();

		$this->assertInternalType('array', $dates);
		$this->assertEquals(1954, $dates[1]);
	}



	public function testGetSecondaryAuthors()
	{
		$authors = $this->driver->getSecondaryAuthors(false);

		$this->assertInternalType('array', $authors);
		$this->assertEquals(2, sizeof($authors));

		$this->assertEquals('Kölbel', $authors[0]['name']);
		$this->assertEquals('Herbert', $authors[0]['forname']);
	}



	public function testGetEdition()
	{
		$edition = $this->driver->getEdition();

		$this->assertNull($edition);
	}



	public function testGetGroup()
	{
		$group = $this->driver->getGroup();

		$this->assertInternalType('string', $group);
		$this->assertEquals('005378974', $group);
	}



	public function testGetInstitutions()
	{
		$institutions = $this->driver->getInstitutions();

		$this->assertInternalType('array', $institutions);
		$this->assertEquals('LUMH1', $institutions[0]);
	}



	public function testGetLocalTopicTerms()
	{
		$terms = $this->driver->getLocalTopicalTerms();

		$this->assertInternalType('array', $terms);
		$this->assertEquals(2, sizeof($terms));

		$this->assertEquals('Konzerte', $terms[0]['term']);
		$this->assertArrayHasKey('label', $terms[0]);
	}


	public function testGetHostItemEntry()
	{
		$entry = $this->driver->getHostItemEntry();

		$this->assertInternalType('array', $entry);
		$this->assertEquals(0, sizeof($entry));
	}



	public function testGetPublisher()
	{
		$publishers = $this->driver->getPublishers(false);

		$this->assertInternalType('array', $publishers);
		$this->assertEquals(1, sizeof($publishers));

		$this->assertArrayHasKey('place', $publishers[0]);
		$this->assertArrayHasKey('name', $publishers[0]);
		$this->assertArrayHasKey('date', $publishers[0]);

		$this->assertEquals('Kassel', $publishers[0]['place']);
	}



	public function testGetPhysicalDescriptions()
	{
		$physicalDescriptions = $this->driver->getPhysicalDescriptions(false);

		$this->assertInternalType('array', $physicalDescriptions);
		$this->assertEquals(1, sizeof($physicalDescriptions));
		$this->assertArrayHasKey('extent', $physicalDescriptions[0]);
		$this->assertEquals('1 Partitur', $physicalDescriptions[0]['extent'][0]);
	}



	public function testGetTitle()
	{
		$title  = $this->driver->getTitle();
		$expect = 'Konzert e-Moll, für Blockflöte, Querflöte, zwei Violinen, Viola und Basso continuo, [TWV 52 e 1] :'.
				' Concerto in e minor, for recorder, flute, two violins, viola and basso continuo';

		$this->assertInternalType('string', $title);
		$this->assertEquals($expect, $title);
	}



	public function testGetShortTitle()
	{
		$title  = $this->driver->getShortTitle();
		$expect = 'Konzert e-Moll, für Blockflöte, Querflöte, zwei Violinen, Viola und Basso continuo, [TWV 52 e 1]';

		$this->assertInternalType('string', $title);
		$this->assertEquals($expect, $title);
	}



	public function testGetUnions()
	{
		$unions = $this->driver->getUnions();

		$this->assertInternalType('array', $unions);
		$this->assertEquals(2, sizeof($unions));
		$this->assertEquals('IDSLU', $unions[0]);
	}



	public function testGetTitleStatementSimple()
	{
		$titleSimple  = $this->driver->getTitleStatement();
		$expectSimple = 'Georg Philipp Telemann ; hrsg. von Herbert Kölbel ; Generalbass-Bearb. von Otto Kiel';

		$this->assertInternalType('string', $titleSimple);
		$this->assertEquals($expectSimple, $titleSimple);
	}



	public function testGetTitleStatementFull()
	{
		$titleFull = $this->driver->getTitleStatement(true);

		$this->assertInternalType('array', $titleFull);

		$expect = 'Konzert e-Moll, für Blockflöte, Querflöte, zwei Violinen, Viola und Basso continuo, [TWV 52 e 1]';

		$this->assertEquals($expect, $titleFull['title']);
	}



	public function testGetAddedCorporateNames()
	{
		$corporateName = $this->driver->getAddedCorporateNames();

		$this->assertInternalType('array', $corporateName);
	}



	public function testIndicators()
	{
		$terms = $this->driver->getLocalTopicalTerms();
		$first = $terms[0];

		$this->assertInternalType('array', $first);
		$this->assertArrayHasKey('@ind1', $first);
		$this->assertArrayHasKey('@ind2', $first);

		$this->assertEquals('L', $first['@ind1']);
		$this->assertEquals('A', $first['@ind2']);
	}
}

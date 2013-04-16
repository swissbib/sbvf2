<?php
namespace SwissbibTest\RecordDriver;

use Swissbib\RecordDriver\SolrMarc;
use SwissbibTest\RecordDriver\SolrMarcTestCase;

/**
 * [Description]
 *
 */
class SolrMarcSimpleTest extends SolrMarcTestCase {

	public function setUp() {
		$this->initialize('marc-simple.json');
	}


	public function testPrimaryAuthor() {
		$primaryAuthor	= $this->driver->getPrimaryAuthor();

		$this->assertInternalType('array', $primaryAuthor);
		$this->assertEquals('Telemann', $primaryAuthor['name']);
		$this->assertEquals('Georg Philipp', $primaryAuthor['forname']);

	}


	public function testGetUniqueId() {
		$id	= $this->driver->getUniqueID();

		$this->assertEquals('005378974', $id);
	}

	public function testGetPublicationDates() {
		$dates	= $this->driver->getPublicationDates();

		$this->assertInternalType('array', $dates);
		$this->assertEquals(1954, $dates[1]);
	}


	public function testGetSecondaryAuthors() {
		$authors	= $this->driver->getSecondaryAuthors();

		$this->assertInternalType('array', $authors);
		$this->assertEquals(2, sizeof($authors));

		$this->assertEquals('Kölbel', $authors[0]['name']);
		$this->assertEquals('Herbert', $authors[0]['forname']);
	}


	public function testGetCorporateAuthor() {
		$author	= $this->driver->getCorporateAuthor();

		$this->assertInternalType('array', $author);
		$this->assertEquals(0, sizeof($author));
	}


	public function testGetSubtitle() {
		$subtitle	= $this->driver->getSubtitle();
		$expect		= 'Concerto in e minor, for recorder, flute, two violins, viola and basso continuo';

		$this->assertInternalType('string', $subtitle);
		$this->assertEquals($expect, $subtitle);
	}


	public function testGetEdition() {
		$edition	= $this->driver->getEdition();

		$this->assertNull($edition);
	}


	public function testGetGNDSubjectHeadings() {
		$headings	= $this->driver->getGNDSubjectHeadings();

		$this->assertInternalType('array', $headings);
		$this->assertEquals(0, sizeof($headings));
	}

	public function testGetGroup() {
		$group	= $this->driver->getGroup();

		$this->assertInternalType('string', $group);
		$this->assertEquals('005378974', $group);
	}


	public function testGetInstitution() {
		$institution	= $this->driver->getInstitution();

		$this->assertInternalType('string', $institution);
		$this->assertEquals('LUMH1', $institution);
	}


	public function testGetLocalTopicTerms() {
		$terms	= $this->driver->getLocalTopicTerms();

		$this->assertInternalType('array', $terms);
		$this->assertEquals(2, sizeof($terms));

		$this->assertEquals('Konzerte', $terms[0]['term']);
		$this->assertArrayHasKey('label', $terms[0]);
	}


	public function testGetHostItemEntry() {
		$entry	= $this->driver->getHostItemEntry();

		$this->assertInternalType('array', $entry);
		$this->assertEquals(0, sizeof($entry));
	}


	public function testGetPublisher() {
		$publishers	= $this->driver->getPublishers();

		$this->assertInternalType('array', $publishers);
		$this->assertEquals(1, sizeof($publishers));

		$this->assertArrayHasKey('place', $publishers[0]);
		$this->assertArrayHasKey('name', $publishers[0]);
		$this->assertArrayHasKey('date', $publishers[0]);

		$this->assertEquals('Kassel', $publishers[0]['place']);
	}

	public function testGetPhysicalDescriptions() {
		$physicalDescriptions	= $this->driver->getPhysicalDescriptions();

		$this->assertInternalType('array', $physicalDescriptions);
		$this->assertEquals(1, sizeof($physicalDescriptions));
		$this->assertArrayHasKey('extent', $physicalDescriptions[0]);
		$this->assertEquals('1 Partitur', $physicalDescriptions[0]['extent'][0]);
	}

}

?>
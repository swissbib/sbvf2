<?php

/**
 * OAI-PMH harvester unit test.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category Search
 * @package  Service
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/dmj/vf2-proxy
 */

namespace VuFindTest\Harvester;

use VuFind\Harvester\OAI;

/**
 * OAI-PMH harvester unit test.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category Search
 * @package  Service
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/dmj/vf2-proxy
 */

class OAITest extends \VuFindTest\Unit\TestCase
{
    /**
     * Test configuration.
     *
     * @return void
     */
    public function testConfig()
    {
        $config = array(
            'url' => 'http://localhost',
            'set' => 'myset',
            'metadataPrefix' => 'fakemdprefix',
            'idPrefix' => 'fakeidprefix',
            'idSearch' => 'search',
            'idReplace' => 'replace',
            'harvestedIdLog' => '/my/harvest.log',
            'injectId' => 'idtag',
            'injectSetSpec' => 'setspectag',
            'injectDate' => 'datetag',
            'injectHeaderElements' => 'headertag',
            'dateGranularity' => 'mygranularity',
            'verbose' => true,
            'sanitize' => true,
            'badXMLLog' => '/my/xml.log',
        );
        $oai = new OAI('test', $config, $this->getMockClient());

        // Special cases where config key != class property:
        $this->assertEquals(
            $config['url'], $this->getProperty($oai, 'baseURL')
        );
        $this->assertEquals(
            $config['dateGranularity'], $this->getProperty($oai, 'granularity')
        );

        // Special case where value is transformed:
        $this->assertEquals(
            array($config['injectHeaderElements']),
            $this->getProperty($oai, 'injectHeaderElements')
        );

        // Unset special cases in preparation for generic loop below:
        unset($config['url']);
        unset($config['dateGranularity']);
        unset($config['injectHeaderElements']);

        // Generic case for remaining configs:
        foreach ($config as $key => $value) {
            $this->assertEquals($value, $this->getProperty($oai, $key));
        }
    }

    /**
     * Test the injectSetName configuration.
     *
     * @return void
     */
    public function testInjectSetNameConfig()
    {
        $client = $this->getMockClient();
        $response = $client->send();
        $response->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(true));
        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue('<?xml version="1.0"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"><responseDate>2013-10-11T10:06:06Z</responseDate><request verb="ListSets" metadataPrefix="oai_dc" resumptionToken="" submit="Go">http://vu61162/vufind3/OAI/Server</request><ListSets><set><setSpec>Audio (Music)</setSpec><setName>Audio (Music)</setName></set><set><setSpec>Audio (Non-Music)</setSpec><setName>Audio (Non-Music)</setName></set></ListSets></OAI-PMH>'));
        $config = array(
            'url' => 'http://localhost',
            'injectSetName' => 'setnametag',
            'verbose' => false,
            'dateGranularity' => 'mygranularity',
        );
        $oai = new OAI('test', $config, $client);
        $this->assertEquals(
            $config['injectSetName'], $this->getProperty($oai, 'injectSetName')
        );
        $this->assertEquals(
            array(
                'Audio (Music)' => 'Audio (Music)',
                'Audio (Non-Music)' => 'Audio (Non-Music)'
            ), $this->getProperty($oai, 'setNames')
        );
    }

    /**
     * Test that a missing URL throws an exception.
     *
     * @return void
     * @expectedException Exception
     * @expectedExceptionMessage Missing base URL for test.
     */
    public function testMissingURLThrowsException()
    {
        $oai = new OAI('test', array(), $this->getMockClient());
    }

    // Internal API

    /**
     * Get a fake HTTP client
     *
     * @return \Zend\Http\Client
     */
    protected function getMockClient()
    {
        $query = $this->getMock('Zend\Stdlib\Parameters');
        $request = $this->getMock('Zend\Http\Request');
        $request->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $response = $this->getMock('Zend\Http\Response');
        $client = $this->getMock('Zend\Http\Client');
        $client->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $client->expects($this->any())
            ->method('setMethod')
            ->will($this->returnValue($client));
        $client->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));
        return $client;
    }
}
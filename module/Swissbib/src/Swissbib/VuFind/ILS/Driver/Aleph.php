<?php
namespace Swissbib\VuFind\ILS\Driver;

use VuFind\ILS\Driver\Aleph as AlephDriver;
use \SimpleXMLElement;
use VuFind\Exception\ILS as ILSException;
use DateTime;

class Aleph extends AlephDriver {

	/**
	 * Extract a list of values out of the XML response
	 *
	 * @param	\SimpleXMLElement $xmlResponse
	 * @param	Array		$map
	 * @return	Array
	 */
	protected function extractResponseData(SimpleXMLElement $xmlResponse, array $map) {
		$data	= array();

		foreach($map as $resultField => $path) {
			list($group, $field) = explode('-', $path);

			$data[$resultField] = (string)$xmlResponse->$group->$path;
		}

		return $data;
	}



	/**
	 * Get data for photo copies
	 *
	 * @param	Array		$patron
	 * @return	Array
	 */
	public function getPhotocopies(array $patron) {
		$xmlResponse = $this->doRestDLFRequest(
			array('patron', $patron['id'], 'circulationActions', 'requests', 'photocopies'),
			array('view' => 'full')
		);
		$photoCopyRequests	= $xmlResponse->xpath('//photocopy-request');
		$dataMap	= array(
			'title'			=> 'z13-title',
			'title2'		=> 'z38-title',
			'dateOpen'		=> 'z38-open-date',
			'dateUpdate'	=> 'z30-update-date',
			'author'		=> 'z38-author',
			'pages'			=> 'z38-pages',
			'note1'			=> 'z38-note-1',
			'note2'			=> 'z38-note-2',
			'status'		=> 'z38-status',
			'printStatus'	=> 'z38-print-status',
			'pickup'		=> 'z38-pickup-location',
			'library'		=> 'z30-sub-library',
			'description'	=> 'z30-description',
			'callNumber'	=> 'z30-call-no',
			'callNumberKey'	=> 'z30-call-no-key',
			'additionalInfo'=> 'z38-additional-info',
			'requesterName'	=> 'z38-requester-name',
			'sequence'		=> 'z38-sequence',
			'itemSequence'	=> 'z38-item-sequence',
			'id'			=> 'z38-id',
			'number'		=> 'z38-number',
			'alpha'			=> 'z38-alpha'
		);

		$photoCopiesData = array();

		foreach($photoCopyRequests as $photoCopyRequest) {
			$photoCopyData	= $this->extractResponseData($photoCopyRequest, $dataMap);

				// Process data
			$photoCopyData['dateOpen']	= DateTime::createFromFormat('Ymd', $photoCopyData['dateOpen'])->getTimestamp();
			$photoCopyData['dateUpdate']= DateTime::createFromFormat('Ymd', $photoCopyData['dateUpdate'])->getTimestamp();

			$photoCopiesData[]	= $photoCopyData;
		}

		return $photoCopiesData;
	}



	/**
	 * Fix xserver port problem
	 *
	 * @param	String		$op
	 * @param	Array		$params
	 * @param	Boolean		$auth
	 * @return SimpleXMLElement
	 * @throws ILSException
	 * @throws \Exception
	 */
	protected function doXRequest($op, $params, $auth=false) {
		if( isset($this->config['xServer']['port']) ) {
			$port	= $this->config['xServer']['port'];
			$auth	= isset($this->config['xServer']['auth']) ? !!$this->config['xServer']['auth'] : $auth;

			try {
				$oldHost	= $this->host;
				$this->host	.= ':' . $port;

				$returnValue= parent::doXRequest($op, $params, $auth);

				$this->host	= $oldHost;

				return $returnValue;
			} catch(\Exception $e) {
					// Reset host to leave it clear
				$this->host	= $oldHost;
					// Go on with exception
				throw $e;
			}
		} else {
				// Normal handling
			return parent::doXRequest($op, $params, $auth);
		}
	}

}
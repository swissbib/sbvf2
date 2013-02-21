<?php
namespace Swissbib\VuFind\ILS\Driver;

use VuFind\ILS\Driver\Aleph as AlephDriver;
use \SimpleXMLElement;
use VuFind\Exception\ILS as ILSException;
use DateTime;

class Aleph extends AlephDriver {

	/**
	 * Get data for photo copies
	 *
	 * @param	Integer		$idPatron
	 * @return	Array
	 */
	public function getPhotocopies($idPatron) {
		$photoCopyRequests	= $this->getPhotoCopyRequests($idPatron);

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
	 *
	 *
	 * @param	Integer		$idPatron
	 * @return	Array
	 */
	public function getBookings($idPatron) {
		$bookingRequests	= $this->getBookingRequests($idPatron);
		$dataMap	= array(
			'sequence'			=> 'z37-sequence',
			'title'				=> 'z13-title',
			'author'			=> 'z13-author',
			'dateStart'			=> 'z37-booking-orig-start-time',
			'dateEnd'			=> 'z37-booking-orig-end-time',
			'pickupLocation'	=> 'z37-pickup-location',
			'pickupSubLocation'	=> 'z37-delivery-sub-location',
			'itemStatus'		=> 'z30-item-status',
			'callNumber'		=> 'z30-call-no',
			'library'			=> 'z30-sub-library',
			'note1'				=> 'z37-note-1',
			'note2'				=> 'z37-note-2',
			'barcode'			=> 'z30-barcode',
			'collection'		=> 'z30-collection',
			'description'		=> 'z30-description'
		);

		$bookingsData = array();

		foreach($bookingRequests as $bookingRequest) {
			$bookingData	= $this->extractResponseData($bookingRequest, $dataMap);

				// Process data
			$bookingData['dateStart']	= DateTime::createFromFormat('YmdHi', $bookingData['dateStart'])->getTimestamp();
			$bookingData['dateEnd']		= DateTime::createFromFormat('YmdHi', $bookingData['dateEnd'])->getTimestamp();

			$bookingsData[]	= $bookingData;
		}

		return $bookingsData;
	}



	/**
	 * Get booking requests
	 *
	 * @param	Integer		$idPatron
	 * @return	\SimpleXMLElement[]
	 */
	protected function getBookingRequests($idPatron) {
		$xmlResponse	= $this->doRestDLFRequest(
			array('patron', $idPatron, 'circulationActions', 'requests', 'bookings'),
			array('view' => 'full')
		);

		return $xmlResponse->xpath('//booking-request');
	}



	/**
	 * Get photo copy requests
	 *
	 * @param	Integer		$idPatron
	 * @return	\SimpleXMLElement[]
	 */
	protected function getPhotoCopyRequests($idPatron) {
		$xmlResponse	= $this->doRestDLFRequest(
			array('patron', $idPatron, 'circulationActions', 'requests', 'photocopies'),
			array('view' => 'full')
		);

		return $xmlResponse->xpath('//photocopy-request');
	}





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
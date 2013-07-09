<?php
namespace Swissbib\VuFind\ILS\Driver;

use VuFind\ILS\Driver\Aleph as AlephDriver;
use \SimpleXMLElement;
use VuFind\Exception\ILS as ILSException;
use DateTime;

class Aleph extends AlephDriver
{

	protected $itemLinks;

	/**
	 * Get data for photo copies
	 *
	 * @param    Integer        $idPatron
	 * @return    Array
	 */
	public function getPhotocopies($idPatron)
	{
		$photoCopyRequests = $this->getPhotoCopyRequests($idPatron);

		$dataMap = array(
			'title'          => 'z13-title',
			'title2'         => 'z38-title',
			'dateOpen'       => 'z38-open-date',
			'dateUpdate'     => 'z30-update-date',
			'author'         => 'z38-author',
			'pages'          => 'z38-pages',
			'note1'          => 'z38-note-1',
			'note2'          => 'z38-note-2',
			'status'         => 'z38-status',
			'printStatus'    => 'z38-print-status',
			'pickup'         => 'z38-pickup-location',
			'library'        => 'z30-sub-library',
			'description'    => 'z30-description',
			'callNumber'     => 'z30-call-no',
			'callNumberKey'  => 'z30-call-no-key',
			'additionalInfo' => 'z38-additional-info',
			'requesterName'  => 'z38-requester-name',
			'sequence'       => 'z38-sequence',
			'itemSequence'   => 'z38-item-sequence',
			'id'             => 'z38-id',
			'number'         => 'z38-number',
			'alpha'          => 'z38-alpha'
		);

		$photoCopiesData = array();

		foreach ($photoCopyRequests as $photoCopyRequest) {
			$photoCopyData = $this->extractResponseData($photoCopyRequest, $dataMap);

			// Process data
			$photoCopyData['dateOpen']   = DateTime::createFromFormat('Ymd', $photoCopyData['dateOpen'])->getTimestamp();
			$photoCopyData['dateUpdate'] = DateTime::createFromFormat('Ymd', $photoCopyData['dateUpdate'])->getTimestamp();

			$photoCopiesData[] = $photoCopyData;
		}

		return $photoCopiesData;
	}



	/**
	 *
	 *
	 * @param    Integer        $idPatron
	 * @return    Array
	 */
	public function getBookings($idPatron)
	{
		$bookingRequests = $this->getBookingRequests($idPatron);
		$dataMap         = array(
			'sequence'          => 'z37-sequence',
			'title'             => 'z13-title',
			'author'            => 'z13-author',
			'dateStart'         => 'z37-booking-orig-start-time',
			'dateEnd'           => 'z37-booking-orig-end-time',
			'pickupLocation'    => 'z37-pickup-location',
			'pickupSubLocation' => 'z37-delivery-sub-location',
			'itemStatus'        => 'z30-item-status',
			'callNumber'        => 'z30-call-no',
			'library'           => 'z30-sub-library',
			'note1'             => 'z37-note-1',
			'note2'             => 'z37-note-2',
			'barcode'           => 'z30-barcode',
			'collection'        => 'z30-collection',
			'description'       => 'z30-description'
		);

		$bookingsData = array();

		foreach ($bookingRequests as $bookingRequest) {
			$bookingData = $this->extractResponseData($bookingRequest, $dataMap);

			// Process data
			$bookingData['dateStart'] = DateTime::createFromFormat('YmdHi', $bookingData['dateStart'])->getTimestamp();
			$bookingData['dateEnd']   = DateTime::createFromFormat('YmdHi', $bookingData['dateEnd'])->getTimestamp();

			$bookingsData[] = $bookingData;
		}

		return $bookingsData;
	}



	/**
	 * Get allowed actions for current user for holding item
	 * Actions: hold, shortLoan, photocopyRequest, bookingRequest
	 *
	 * @param    String        $patronId          Catalog user id
	 * @param    String        $id                Item id
	 * @param    String        $group             Group id
	 * @return    Array        List with flags for actions
	 */
	public function getAllowedActionsForItem($patronId, $id, $group)
	{
		list($bib, $sys_no) = $this->parseId($id);
		$resource = $bib . $sys_no;
		$xml      = $this->doRestDLFRequest(
			array('patron', $patronId, 'record', $resource, 'items', $group)
		);

		$result    = array();
		$functions = array(
			'hold'             => 'HoldRequest',
			'shortLoan'        => 'ShortLoan',
			'photocopyRequest' => 'PhotocopyRequest',
			'bookingRequest'   => 'BookingRequest'
		);

		// Check flags for each info node
		foreach ($functions as $key => $type) {
			$typeInfoNodes = $xml->xpath('//info[@type="' . $type . '"]');
			$result[$key]  = (string)$typeInfoNodes[0]['allowed'] === 'Y';
		}

		return $result;
	}



	/**
	 * Get all circulation status infos for item
	 *
	 * @param    String        $sysNumber
	 * @param    String        $library
	 * @return    Array[]
	 */
	public function getCirculationStatus($sysNumber, $library = 'DSV01')
	{
		$xml = $this->doXRequest('circ-status', array(
													 'sys_no'  => $sysNumber,
													 'library' => $library
												));

		$itemDataNodes = $xml->xpath('item-data');
		$data          = array();

		foreach ($itemDataNodes as $itemDataNode) {
			$itemData = array();

			foreach ($itemDataNode as $fieldName => $fieldValue) {
				$itemData[$fieldName] = (string)$fieldValue;
			}

			$data[] = $itemData;
		}

		return $data;
	}


	protected function getHoldingHoldingsLinkList(
					$resourceId,
					$institutionCode = '',
					$offset = 0,
					$year = 0,
					$volume = 0,
					array $extraRestParams = array()
	) {
		if (!is_array($this->itemLinks) || true) {
			$pathElements	= array('record', $resourceId, 'items');
			$parameters		= $extraRestParams;

			if ($institutionCode) {
				$parameters['sublibrary'] = $institutionCode;
			}
			if ($offset) {
				$parameters['startPos'] = intval($offset) + 1;
			}
			if ($year) {
				$parameters['year'] = intval($year);
			}
			if ($volume) {
				$parameters['volume'] = intval($volume);
			}

			$xmlResponse = $this->doRestDLFRequest($pathElements, $parameters);

			/** @var SimpleXMLElement[] $items */
			$items = $xmlResponse->xpath('//item');
			$links = array();

			foreach ($items as $item) {
				$links[] = (string)$item->attributes()->href;
			}

			$this->itemLinks = $links;
		}


		return $this->itemLinks;
	}




	public function getHoldingHoldingItems(
					$resourceId,
					$institutionCode = '',
					$offset = 0,
					$year = 0,
					$volume = 0,
					$numItems = 10,
					array $extraRestParams = array()
	) {
		$links	= $this->getHoldingHoldingsLinkList($resourceId, $institutionCode, $offset, $year, $volume, $extraRestParams);
		$items	= array();
		$dataMap         = array(
			'title'             	=> 'z13-title',
			'author'            	=> 'z13-author',
			'itemStatus'        	=> 'z30-item-status',
			'signature'         	=> 'z30-call-no',
			'library'           	=> 'z30-sub-library',
			'barcode'           	=> 'z30-barcode',
			'location_expanded' 	=> 'z30-collection',
			'location_code'			=> 'z30-collection',
			'description'       	=> 'z30-description',
			'raw-sequence-number'	=> 'z30-item-sequence'
		);

		$linksToExtend = array_slice($links, 0, $numItems);

		foreach ($linksToExtend as $link) {
			$itemResponseData = $this->doHTTPRequest($link);

			$item = $this->extractResponseData($itemResponseData->item, $dataMap);

			if (isset($item['raw-sequence-number'])) {
				$item['sequencenumber'] = sprintf('%06d', trim(str_replace('.', '', $item['raw-sequence-number'])));
			}

			$items[] = $item;
		}

		return $items;
	}



	/**
	 *
	 *
	 * @param $resourceId
	 * @return	Array[]
	 */
	public function getResourceFilters($resourceId)
	{
		$pathElements	= array('record', $resourceId, 'filters');
		$xmlResponse	= $this->doRestDLFRequest($pathElements);

		$yearNodes		= $xmlResponse->{'record-filters'}->xpath('//year');
		$years 			= array_map('trim', $yearNodes);
		sort($years);

		$volumeNodes	= $xmlResponse->{'record-filters'}->xpath('//volume');
		$volumes	= array_map('trim', $volumeNodes);
		sort($volumes);

		return array(
			'years'		=> $years,
			'volumes'	=> $volumes
		);
	}



	/**
	 *
	 *
	 * @param        $resourceId
	 * @param string $institutionCode
	 * @param int    $year
	 * @param int    $volume
	 * @return	Integer
	 */
	public function getHoldingItemCount($resourceId, $institutionCode = '', $year = 0, $volume = 0)
	{
		$links	= $this->getHoldingHoldingsLinkList(	$resourceId,
														$institutionCode,
														0,
														$year,
														$volume);

		return sizeof($links);
	}



	/**
	 * Get booking requests
	 *
	 * @param    Integer        $idPatron
	 * @return    \SimpleXMLElement[]
	 */
	protected function getBookingRequests($idPatron)
	{
		$xmlResponse = $this->doRestDLFRequest(
			array('patron', $idPatron, 'circulationActions', 'requests', 'bookings'),
			array('view' => 'full')
		);

		return $xmlResponse->xpath('//booking-request');
	}



	/**
	 * Get photo copy requests
	 *
	 * @param    Integer        $idPatron
	 * @return    \SimpleXMLElement[]
	 */
	protected function getPhotoCopyRequests($idPatron)
	{
		$xmlResponse = $this->doRestDLFRequest(
			array('patron', $idPatron, 'circulationActions', 'requests', 'photocopies'),
			array('view' => 'full')
		);

		return $xmlResponse->xpath('//photocopy-request');
	}



	/**
	 * Extract a list of values out of the XML response
	 *
	 * @param    \SimpleXMLElement $xmlResponse
	 * @param    Array             $map
	 * @return    Array
	 */
	protected function extractResponseData(SimpleXMLElement $xmlResponse, array $map)
	{
		$data = array();

		foreach ($map as $resultField => $path) {
			list($group, $field) = explode('-', $path, 2);

			$data[$resultField] = (string)$xmlResponse->$group->$path;
		}

		return $data;
	}



	/**
	 * Fix xserver port problem
	 *
	 * @param    String         $op
	 * @param    Array          $params
	 * @param    Boolean        $auth
	 * @return SimpleXMLElement
	 * @throws ILSException
	 * @throws \Exception
	 */
	protected function doXRequest($op, $params, $auth = false)
	{
		if (isset($this->config['xServer']['port'])) {
			$port = $this->config['xServer']['port'];
			$auth = isset($this->config['xServer']['auth']) ? !!$this->config['xServer']['auth'] : $auth;

			try {
				$oldHost = $this->host;
				$this->host .= ':' . $port;

				$returnValue = parent::doXRequest($op, $params, $auth);

				$this->host = $oldHost;

				return $returnValue;
			} catch (\Exception $e) {
				// Reset host to leave it clear
				$this->host = $oldHost;
				// Go on with exception
				throw $e;
			}
		} else {
			// Normal handling
			return parent::doXRequest($op, $params, $auth);
		}
	}



	/**
	 * Perform a RESTful DLF request.
	 *
	 * @param array  $path_elements URL path elements
	 * @param array  $params        GET parameters (null for none)
	 * @param string $method        HTTP method
	 * @param string $body          HTTP body
	 *
	 * @return \SimpleXMLElement
	 */
	protected function doRestDLFRequestIgnoreFailures($path_elements, $params = null, $method = 'GET', $body = null)
	{
		$path = implode('/', $path_elements) . '/';
		$url  = "http://$this->host:$this->dlfport/rest-dlf/" . $path;
		$url  = $this->appendQueryString($url, $params);

		return $this->doHTTPRequest($url, $method, $body);
	}



	/**
	 * Send renew request do REST server
	 * Use non breaking DLF request method to support failure messages (which are still a valid response)
	 *
	 * @param	Array	$details
	 * @return	Array[]
	 */
	public function renewMyItems($details)
	{
		$patron = $details['patron'];
		$blocks	= array();
		foreach ($details['details'] as $id) {
			$result = $this->doRestDLFRequestIgnoreFailures(
				array('patron', $patron['id'], 'circulationActions', 'loans', $id),
				null, 'POST', null
			);

			if ($result->renewals && $result->renewals->institution && $result->renewals->institution->loan) {
				$status   = (string)$result->{'reply-text'};
				$code     = (int)$result->{'reply-code'};
				$reason   = (string)$result->renewals->institution->loan->status;
				$blocks[] = $status . ': ' . $reason . ($code?' [' . $code . ']':'') . ' (' . $id . ')';
			}
		}

		return array('blocks' => $blocks, 'details' => array());
	}



	/**
	 * Get my transactions response items
	 *
	 * @param	Array		$user
	 * @param	Boolean		$history
	 * @return	\SimpleXMLElement[]
	 */
	protected function getMyTransactionsResponse(array $user, $history = false)
	{
		$userId    = $user['id'];
		$transList = array();
		$params    = array("view" => "full");
		if ($history) {
			$params["type"] = "history";
		}
		$xml = $this->doRestDLFRequest(
			array('patron', $userId, 'circulationActions', 'loans'), $params
		);

		return $xml->xpath('//loan');
	}



	/**
	 * Get Patron Transactions
	 *
	 * This is responsible for retrieving all transactions (i.e. checked out items)
	 * by a specific patron.
	 *
	 * @param array $user    The patron array from patronLogin
	 * @param bool  $history Include history of transactions (true) or just get
	 *                       current ones (false).
	 *
	 * @throws \VuFind\Exception\Date
	 * @throws ILSException
	 * @return array        Array of the patron's transactions on success.
	 */
	public function getMyTransactions($user, $history = false)
	{
		$transactionsResponseItems	= $this->getMyTransactionsResponse($user, $history);
		$dataMap         = array(
			'barcode'		=> 'z30-barcode',
			'title'			=> 'z13-title',
			'doc-number'	=> 'z36-doc-number',
			'item-sequence'	=> 'z36-item-sequence',
			'sequence'		=> 'z36-sequence',
			'loaned'		=> 'z36-loan-date',
			'due'			=> 'z36-due-date',
			'status'		=> 'z36-status',
			'return'		=> 'z36-returned-date',
			'renewals'		=> 'z36-no-renewal',
			'library'		=> 'z30-sub-library',
			'callnum'		=> 'z30-call-no'
		);
		$transactionsData	= array();

		foreach ($transactionsResponseItems as $transactionsResponseItem) {
			$itemData	= $this->extractResponseData($transactionsResponseItem, $dataMap);
			$group   	= $transactionsResponseItem->xpath('@href');
			$renewable	= (string)$transactionsResponseItem->attributes()->renew === 'Y';

				// Add special data
			$itemData['id']			= ($history) ? null : $this->barcodeToID($itemData['barcode']);
			$itemData['item_id']	= substr(strrchr($group[0], "/"), 1);
			$itemData['reqnum']		= $itemData['doc-number'] . $itemData['item-sequence'] . $itemData['sequence'];
			$itemData['loandate']	= $this->parseDate($itemData['loaned']);
			$itemData['duedate']	= $this->parseDate($itemData['due']);
			$itemData['returned']	= $this->parseDate($itemData['return']);
			$itemData['renewable']	= $renewable;

			$transactionsData[] = $itemData;
		}

		return $transactionsData;
	}



	/**
	 * @param	String		$userId
	 * @return	\SimpleXMLElement[]
	 */
	protected function getMyHoldsResponse($userId)
	{
		$xml = $this->doRestDLFRequest(
			array('patron', $userId, 'circulationActions', 'requests', 'holds'),
			array('view' => 'full')
		);

		return $xml->xpath('//hold-request');
	}



	/**
	 * @param	Array		$user
	 * @return	Array[]
	 */
	public function getMyHolds($user)
	{
		$holdResponseItems	= $this->getMyHoldsResponse($user['id']);
		$holds				= array();
		$dataMap         = array(
			'location'		=> 'z37-pickup-location',
			'title'			=> 'z13-title',
			'author'		=> 'z13-author',
			'isbn-raw'		=> 'z13-isbn-issn',
			'reqnum'		=> 'z37-doc-number',
			'barcode'		=> 'z30-barcode',
			'expire'		=> 'z37-end-request-date',
			'holddate'		=> 'z37-hold-date',
			'create'		=> 'z37-open-date',
			'status'		=> 'z37-status',
			'sequence'		=> 'z37-sequence',
			'balance'		=> 'z37-balancer-date',
			'institution'	=> 'z30-sub-library-code',
			'signature'		=> 'z30-call-no'
		);

		$holdResponseItems	= array_slice($holdResponseItems, 0, 5);

		foreach ($holdResponseItems as $holdResponseItem) {
			$itemData	= $this->extractResponseData($holdResponseItem, $dataMap);
			$href 		= $holdResponseItem->xpath('@href');
			$delete		= $holdResponseItem->xpath('@delete');

				// Special fields which require calculation
			$itemData['type']		= 'hold';
			$itemData['item_id']	= substr($href[0], strrpos($href[0], '/') + 1);
			$itemData['isbn']		= array($itemData['isbn-raw']);
			$itemData['id']			= $this->barcodeToID($itemData['barcode']);
			$itemData['expire']		= $this->parseDate($itemData['expire']);
			$itemData['create']		= $this->parseDate($itemData['create']);
			$itemData['balance']	= $itemData['balance'] === '00000000' ? false : $this->parseDate($itemData['balance']);
			$itemData['delete']		= (string)($delete[0]) === 'Y';
			$itemData['position']	= ltrim($itemData['sequence'], '0');

			$holds[] = $itemData;
		}

		return $holds;
	}
}

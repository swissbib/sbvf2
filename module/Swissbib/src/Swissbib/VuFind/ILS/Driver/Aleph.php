<?php
namespace Swissbib\VuFind\ILS\Driver;

use VuFind\ILS\Driver\Aleph as VuFindDriver;
use \SimpleXMLElement;
use VuFind\Exception\ILS as ILSException;
use DateTime;

class Aleph extends VuFindDriver
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
            $photoCopyData['dateOpen']   = DateTime::createFromFormat('Ymd', $photoCopyData['dateOpen'])->format('d.m.Y');
            $photoCopyData['dateUpdate'] = DateTime::createFromFormat('Ymd', $photoCopyData['dateUpdate'])->format('d.m.Y');

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
    public function getAllowedActionsForItem($patronId, $id, $group, $bib)
    {
        $resource = $bib . $id;
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
            $pathElements    = array('record', $resourceId, 'items');
            $parameters        = $extraRestParams;

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
        $links    = $this->getHoldingHoldingsLinkList($resourceId, $institutionCode, $offset, $year, $volume, $extraRestParams);
        $items    = array();
        $dataMap         = array(
            'title'                 => 'z13-title',
            'author'                => 'z13-author',
            'itemStatus'            => 'z30-item-status',
            'signature'             => 'z30-call-no',
            'library'               => 'z30-sub-library',
            'barcode'               => 'z30-barcode',
            'location_expanded'     => 'z30-collection',
            'location_code'            => 'z30-collection-code',
            'description'           => 'z30-description',
            'raw-sequence-number'    => 'z30-item-sequence',
            'localid'                => 'z30-doc-number',
            'opac_note'             => 'z30-note-opac',
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
     * @return    Array[]
     */
    public function getResourceFilters($resourceId)
    {
        $pathElements    = array('record', $resourceId, 'filters');
        $xmlResponse    = $this->doRestDLFRequest($pathElements);

        $yearNodes        = $xmlResponse->{'record-filters'}->xpath('//year');
        $years             = array_map('trim', $yearNodes);
        sort($years);

        $volumeNodes    = $xmlResponse->{'record-filters'}->xpath('//volume');
        $volumes    = array_map('trim', $volumeNodes);
        sort($volumes);

        return array(
            'years'        => $years,
            'volumes'    => $volumes
        );
    }



    /**
     *
     *
     * @param        $resourceId
     * @param string $institutionCode
     * @param int    $year
     * @param int    $volume
     * @return    Integer
     */
    public function getHoldingItemCount($resourceId, $institutionCode = '', $year = 0, $volume = 0)
    {
        $links    = $this->getHoldingHoldingsLinkList(    $resourceId,
                                                        $institutionCode,
                                                        0,
                                                        $year,
                                                        $volume);

        return sizeof($links);
    }

    /**
     * Public Function which retrieves renew, hold and cancel settings from the
     * driver ini file.
     *
     * @param string $func The name of the feature to be checked
     *
     * @return array An array with key-value pairs.
     */
    public function getConfig($func)
    {
        if ($func == "Holds") {
            return $this->config['Holds'];
        } else {
            return array();
        }
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
            $data[$resultField] =  isset($xmlResponse->$path) ? (string)$xmlResponse->$path :  (string)$xmlResponse->$group->$path;
        }

        return $data;
    }

    /**
     * Get my transactions response items
     *
     * @param    Array        $user
     * @param    Boolean        $history
     * @return    \SimpleXMLElement[]
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
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $user The patron array
     *
     * @throws ILSException
     * @return array      Array of the patron's profile data on success.
     */
    public function getMyProfile($user)
    {
        if ($this->xserver_enabled) {
            return $this->getMyProfileX($user);
        } else {
            return $this->getMyProfileDLF($user);
        }
    }

    /**
     * Get profile information using X-server.
     *
     * @param array $user The patron array
     *
     * @throws ILSException
     * @return array      Array of the patron's profile data on success.
     * Angepasste Funktion im Bereich Ausgabe der Adresse (z304)
     */
    public function getMyProfileX($user)
    {
        $recordList=array();
        if (!isset($user['college'])) {
            $user['college'] = $this->useradm;
        }
        $xml = $this->doXRequest(
            "bor-auth",
            //array(
            //    'loans' => 'N', 'cash' => 'N', 'hold' => 'N',
            //    'library' => $user['college'], 'bor_id' => $user['id']
            array(
                'library' => $user['college'], 'bor_id' => $user['id'], 'verification' => $user['cat_password']
            ), true
        );
        $id = (string) $xml->z303->{'z303-id'};
        $delinq_1 = (string) $xml->z303->{'z303-delinq-1'};
        $delinq_n_1 = (string) $xml->z303->{'z303-delinq-n-1'};
        $delinq_2 = (string) $xml->z303->{'z303-delinq-2'};
        $delinq_n_2 = (string) $xml->z303->{'z303-delinq-n-2'};
        $delinq_3 = (string) $xml->z303->{'z303-delinq-3'};
        $delinq_n_3 = (string) $xml->z303->{'z303-delinq-n-3'};
        $address1 = (string) $xml->z304->{'z304-address-0'};
        $address2 = (string) $xml->z304->{'z304-address-1'};
        $address3 = (string) $xml->z304->{'z304-address-2'};
        $address4 = (string) $xml->z304->{'z304-address-3'};
        $address5 = (string) $xml->z304->{'z304-address-4'};
        $zip = (string) $xml->z304->{'z304-zip'};
        $phone = (string) $xml->z304->{'z304-telephone'};
        //$barcode = (string) $xml->z304->{'z304-address-0'};
        $group = (string) $xml->z305->{'z305-bor-status'};
        $expiry = (string) $xml->z305->{'z305-expiry-date'};
        $credit_sum = (string) $xml->z305->{'z305-sum'};
        $credit_sign = (string) $xml->z305->{'z305-credit-debit'};
        $name = (string) $xml->z303->{'z303-name'};
        if (strstr($name, ",")) {
            list($lastname, $firstname) = explode(",", $name);
        } else {
            $lastname = $name;
            $firstname = "";
        }
        if ($credit_sign == null) {
            $credit_sign = "C";
        }
        $recordList['firstname'] = $firstname;
        $recordList['lastname'] = $lastname;
        if (isset($user['email'])) {
            $recordList['email'] = $user['email'];
        }
        $recordList['address1'] = $address1;
        $recordList['address2'] = $address2;
        $recordList['address3'] = $address3;
        $recordList['address4'] = $address4;
        $recordList['address5'] = $address5;
        $recordList['zip'] = $zip;
        $recordList['phone'] = $phone;
        $recordList['group'] = $group;
        //$recordList['barcode'] = $barcode;
        $recordList['expire'] = $this->parseDate($expiry);
        $recordList['credit'] = $expiry;
        $recordList['credit_sum'] = $credit_sum;
        $recordList['credit_sign'] = $credit_sign;
        $recordList['id'] = $id;
        $recordList['delinq-1'] = $delinq_1;
        $recordList['delinq-n-1'] = $delinq_n_1;
        $recordList['delinq-2'] = $delinq_2;
        $recordList['delinq-n-2'] = $delinq_n_2;
        $recordList['delinq-3'] = $delinq_3;
        $recordList['delinq-n-3'] = $delinq_n_3;
        return $recordList;
    }

    /**
     * Get profile information using DLF service.
     *
     * @param array $user The patron array
     *
     * @throws ILSException
     * @return array      Array of the patron's profile data on success.
     */
    public function getMyProfileDLF($user)
    {
        $xml = $this->doRestDLFRequest(
            array('patron', $user['id'], 'patronInformation', 'address')
        );
        $address = $xml->xpath('//address-information');
        $address = $address[0];
        $address1 = (string)$address->{'z304-address-1'};
        $address2 = (string)$address->{'z304-address-2'};
        $address3 = (string)$address->{'z304-address-3'};
        $address4 = (string)$address->{'z304-address-4'};
        $address5 = (string)$address->{'z304-address-5'};
        $zip = (string)$address->{'z304-zip'};
        $phone = (string)$address->{'z304-telephone-1'};
        $email = (string)$address->{'z404-email-address'};
        $dateFrom = (string)$address->{'z304-date-from'};
        $dateTo = (string)$address->{'z304-date-to'};
        if (strpos($address2, ",") === false) {
            $recordList['lastname'] = $address2;
            $recordList['firstname'] = "";
        } else {
            list($recordList['lastname'], $recordList['firstname'])
                = explode(",", $address2);
        }
        $recordList['address1'] = $address1;
        $recordList['address2'] = $address2;
        $recordList['address3'] = $address3;
        $recordList['address4'] = $address4;
        $recordList['address5'] = $address5;
        $recordList['zip'] = $zip;
        $recordList['phone'] = $phone;
        $recordList['email'] = $email;
        $recordList['dateFrom'] = $dateFrom;
        $recordList['dateTo'] = $dateTo;
        $recordList['id'] = $user['id'];
        $xml = $this->doRestDLFRequest(
            array('patron', $user['id'], 'patronStatus', 'registration')
        );
        $status = $xml->xpath("//institution/z305-bor-status");
        $expiry = $xml->xpath("//institution/z305-expiry-date");
        $recordList['expire'] = $this->parseDate($expiry[0]);
        $recordList['group'] = $status[0];
        return $recordList;
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
        $transactionsResponseItems    = $this->getMyTransactionsResponse($user, $history);
        $dataMap         = array(
            'barcode'        => 'z30-barcode',
            'title'            => 'z13-title',
            'doc-number'    => 'z36-doc-number',
            'item-sequence'    => 'z36-item-sequence',
            'sequence'        => 'z36-sequence',
            'loaned'        => 'z36-loan-date',
            'due'            => 'z36-due-date',
            'status'        => 'z36-status',
            'return'        => 'z36-returned-date',
            'renewals'        => 'z36-no-renewal',
            'library'        => 'z30-sub-library',
            'callnum'        => 'z30-call-no',
            'renew_info'    => 'renew-info'
        );
        $transactionsData    = array();

        foreach ($transactionsResponseItems as $transactionsResponseItem) {
            $itemData    = $this->extractResponseData($transactionsResponseItem, $dataMap);
            $group       = $transactionsResponseItem->xpath('@href');
            $itemURL    = (string) $group[0];

            // get renew-Information for every Item. ALEPH-logic forces to iterate, info on resultlist is always true
            $response  = $this->doHTTPRequest($itemURL);
            $renewable = (string) $response->loan->attributes()->renew;
            $renewable = $renewable === 'Y' ? true : false;

                // Add special data
            try {
                $itemData['id']            = ($history) ? null : $this->barcodeToID($itemData['barcode']);
                $itemData['item_id']    = substr(strrchr($group[0], "/"), 1);
                $itemData['reqnum']        = $itemData['doc-number'] . $itemData['item-sequence'] . $itemData['sequence'];
                $itemData['loandate']   = DateTime::createFromFormat('Ymd', $itemData['loaned'])->format('d.m.Y');
                $itemData['duedate']    = DateTime::createFromFormat('Ymd', $itemData['due'])->format('d.m.Y');
                $itemData['returned']   = DateTime::createFromFormat('Ymd', $itemData['return'])->format('d.m.Y');
                $itemData['renewable']    = $renewable;

                $transactionsData[] = $itemData;
            } catch (\Exception $ex) {


                $this->logger->err(
                    "error while trying to fetch loaned item from ILS system", array(
                        'barcode' => $itemData['barcode'], 'doc-number' => $itemData['doc-number'],
                        'item-sequence' => $itemData['item-sequence'], 'callnum' => $itemData['callnum']
                    )
                );

            }
        }

        return $transactionsData;
    }

    /**
     * Get Required Date
     *
     * @return
     */

    public function getRequiredDate($patron, $holdInfo=null)
    {
        if ($holdInfo != null) {
            $details = $this->getHoldingInfoForItem(
                $patron['id'], $holdInfo['id'], $holdInfo['item_id']
            );
            $requiredDate = $details['last-interest-date'];
            return $requiredDate;
        }
    }

    /**
     * Get my holds xml data
     *
     * @param    String        $userId
     * @return    \SimpleXMLElement[]
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
     * Get my holds
     *
     * @param    Array        $user
     * @return    Array[]
     */
    public function getMyHolds($user)
    {
        $holdResponseItems    = $this->getMyHoldsResponse($user['id']);
        $holds                = array();
        $dataMap         = array(
            'location'        => 'z37-pickup-location',
            'title'            => 'z13-title',
            'author'        => 'z13-author',
            'isbn-raw'        => 'z13-isbn-issn',
            'reqnum'        => 'z37-doc-number',
            'barcode'        => 'z30-barcode',
            'expire'        => 'z37-end-request-date',
            'holddate'        => 'z37-hold-date',
            'create'        => 'z37-open-date',
            'status'        => 'status',
            'sequence'        => 'z37-sequence',
            'institution'    => 'z30-sub-library-code',
            'signature'        => 'z30-call-no',
            'description'    => 'z30-description'
        );

        foreach ($holdResponseItems as $holdResponseItem) {
            $itemData    = $this->extractResponseData($holdResponseItem, $dataMap);
            $href         = $holdResponseItem->xpath('@href');
            $delete        = $holdResponseItem->xpath('@delete');

                // Special fields which require calculation
            $itemData['type']        = 'hold';
            $itemData['item_id']    = substr($href[0], strrpos($href[0], '/') + 1);
            $itemData['isbn']        = array($itemData['isbn-raw']);
            $itemData['id']            = $this->barcodeToID($itemData['barcode']);
            $itemData['expire']        = DateTime::createFromFormat('Ymd', $itemData['expire'])->format('d.m.Y');
            $itemData['create']     = DateTime::createFromFormat('Ymd', $itemData['create'])->format('d.m.Y');
            $itemData['delete']        = (string)($delete[0]) === 'Y';

            // Auslesen Reservationsstatus
            if (preg_match('/due date/', $itemData['status']))
            {
                $itemData['position'] = preg_replace('/^Waiting in position[\s]+([\d]+).*$/', '$1', $itemData['status']);
                $itemData['duedate']  = DateTime::createFromFormat('d/m/y', preg_replace('/^.* due date ([0-3][0-9]\/[0-2][0-9]\/[0-9][0-9])$/', '$1', $itemData['status']))->format('d.m.Y');
            }
            if (preg_match('/queue$/', $itemData['status']))
            {
                $itemData['position'] = preg_replace('/^Waiting in position[\s]+([\d]+).*$/', '$1', $itemData['status']);
            }
            $holds[] = $itemData;
        }

        return $holds;
    }



    /**
     * Get fine data as xml nodes from server
     *
     * @param    String        $userId
     * @return    \SimpleXMLElement[]
     */
    protected function getMyFinesResponse($userId)
    {
        $xml = $this->doRestDLFRequest(
            array('patron', $userId, 'circulationActions', 'cash'),
            array("view" => "full")
        );

        return $xml->xpath('//cash');
    }



    /**
     * Get fines list
     *
     * @todo    Fetch solr ID to create a link?
     * @param    Array    $user
     * @return    Array[]
     */
    public function getMyFines($user)
    {
        $fineResponseItems    = $this->getMyFinesResponse($user['id']);
        $fines                = array();
        $dataMap         = array(
            'sum'            => 'z31-sum',
            'date'            => 'z31-date',
            'type'            => 'z31-type',
            'description'    => 'z31-description',
            'credittype'    => 'z31-credit-debit',
            'checkout'        => 'z31-date',
            'sequence'        => 'z31-sequence',
            'status'        => 'z31-status',
            'signature'        => 'z30-call-no'
        );

        foreach ($fineResponseItems as $fineResponseItem) {
            $itemData    = $this->extractResponseData($fineResponseItem, $dataMap);

            $itemData['title']      = (string) $fineResponseItem->{'z13'}->{'z13-title'};
            $itemData['amount']     = (float)preg_replace('/[\(\)]/', '', $itemData['sum']);
            $itemData['checkout']   = DateTime::createFromFormat('Ymd', $itemData['checkout'])->format('d.m.Y');
            $itemData['institution']= (string) $fineResponseItem->{'z30-sub-library-code'};

            $sortKey    = $itemData['sequence'];

            $fines[$sortKey] = $itemData;
        }

            // Sort fines by sequence
        ksort($fines);

            // Sum up balance
        $balance    = 0;

        foreach ($fines as $index => $fine) {
            $balance += $fine['amount'];

            $fines[$index]['balance'] = $balance;
        }

            // Return list without sort keys
        return array_values($fines);
    }
}

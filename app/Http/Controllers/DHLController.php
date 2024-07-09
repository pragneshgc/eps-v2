<?php

namespace App\Http\Controllers;

use GuzzleHttp;
use App\Library\Order;
use App\Library\Helper;
use App\Library\Activity;
use App\Library\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class DHLController extends Controller
{
    private $order;
    private $tracking;
    private $h;
    private $activity;
    private $genders = ['1' => 'Male', '2' => 'Female', '3' => 'Transgender', '4' => 'For school use'];
    private $regions = ['1' => 'AF', '2' => 'AM', '3' => 'AM', '4' => 'AN', '5' => 'AP', '6' => 'EU', '7' => 'AU'];
    private $nonEUCountries = ['4', '7', '22', '100', '124', '128', '142', '143', '184', '205', '216', '221', '241', '243'];
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->order = new Order;
        $this->h = new Helper;
        $this->activity = new Activity;
        $this->tracking = new Tracking;
    }

    public function test()
    {
        $ids = [
            '406500',
            //  Germany
            '406501',
            //  Netherlands
            '406507',
            //  Sweden
            '406510',
            //  France
            '406515',
            //  Spain
            '406519', //  Austria
        ];
        $finished = [];

        foreach ($ids as $id) {
            $response = $this->shipmentValidationDHL($id);
            if ($response) {
                array_push($finished, $id);
            }
        }

        dd($finished);
    }

    /**
     * Validate a shippment for DHL and return a ZPL formatted file
     *
     * @param int $id
     */
    public function shipmentValidationDHL($id)
    {
        $order = $this->order->getOrderDetails($id);
        $endpoint = 'http://xmlpitest-ea.dhl.com';
        $accountNumber = '420747769';
        $xmlPath = '/xml/dhl/shipment_validation';
        $vars = [];

        if (App::environment('local')) {
            $endpoint = 'http://xmlpitest-ea.dhl.com';
            $accountNumber = '420747769';
            $xmlPath = '/xml/dhl/shipment_validation_dev';
        }

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $reference = $order->PrescriptionID . '-' . $order->ReferenceNumber;
        $charactersToAdd = 32 - strlen($reference);
        $reference = str_repeat('_', $charactersToAdd) . $reference;
        $timezone = new \DateTimeZone('Europe/London');
        $date = new \DateTime('now');
        $date = $date->setTimezone($timezone)->format(\DateTime::ISO8601);
        $date = '2019-04-20T15:02:46.000+01:00';

        //get shipper data
        $shipper = $this->order->getShipperData();
        $regionCode = $this->regions[$shipper->RegionID];

        $phone = preg_replace('/[^0-9]/', '', $order->Telephone);
        if ($phone == '') {
            $phone = preg_replace('/[^0-9]/', '', $order->Mobile);
        }

        $value = explode(' ', $order->Repeats);

        if (!is_numeric($value[1])) {
            return $this->sendError('Non numeric');
        }

        if ($order->RegionID == '6' && !in_array($order->DCountryCode, $this->nonEUCountries)) {
            $isDutiable = 'N';
            $globalProductCode = 'U';
            $localProductCode = 'U';
        } else {
            $isDutiable = 'Y';
            $globalProductCode = 'D';
            $localProductCode = 'D';
        }

        $vars = [
            'reference' => $reference,
            'accountNumber' => $accountNumber,
            'regionCode' => $regionCode,
            'date' => $date,
            'phone' => $phone,
            'isDutiable' => $isDutiable,
            //what do we need to do with dutiable
            'shipper' => $shipper,
            'value' => $value,
            'globalProductCode' => $globalProductCode,
            'localProductCode' => $localProductCode,
        ];

        $body = \View::make($xmlPath)->with(compact('order', 'vars'))->render();

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        Storage::disk('azure')->put('dhl_test/' . $id . '/validation-request-' . $id . '.xml', $body);

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string(utf8_encode($response));

        Storage::disk('azure')->put('dhl_test/' . $id . '/validation-response-' . $id . '.xml', $xml->asXML());
        Storage::disk('azure')->put('dhl_test/' . $id . '/label-' . $id . '.pdf', base64_decode(strip_tags($xml->LabelImage->OutputImage)));

        return true;
        dd($xml);

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order', 8, $xml->AirwayBillNumber);
            $this->tracking->sendTracking($id, false);
        }

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($file, 'Success');
    }

    /**
     * Book pickup
     *
     * @param int $id
     * @return void
     */
    public function BookPickupGlobal($id)
    {
        $order = $this->order->getOrderDetails($id);
        $endpoint = 'http://xmlpitest-ea.dhl.com';
        $accountNumber = '420747769';

        if (\App::environment('local')) {
            $endpoint = 'http://xmlpitest-ea.dhl.com';
            $accountNumber = '420747769';
        }

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $shipper = $this->order->getShipperData();
        $xml = simplexml_load_file('test_templates/BookPickupRequest_GlobalAP_Valid1.xml'); //load an xml file

        $timezone = new \DateTimeZone('Europe/London');
        $date = new \DateTime('now');
        $date = $date->setTimezone($timezone)->format(\DateTime::ISO8601);
        $date = '2019-04-20T15:02:46.000+01:00';
        //account details
        // $xml->Billing->ShipperAccountNumber = $accountNumber;
        // $xml->Billing->ShippingPaymentType = 'S';
        //order header
        $xml->Request->ServiceHeader->MessageTime = $date;
        $xml->Request->ServiceHeader->MessageReference = $order->PrescriptionID . '-' . $order->ReferenceNumber;
        //stupid workaround
        $charactersToAdd = 32 - strlen($xml->Request->ServiceHeader->MessageReference);
        $xml->Request->ServiceHeader->MessageReference = str_repeat('_', $charactersToAdd) . $xml->Request->ServiceHeader->MessageReference;
        //customer details
        $xml->RegionCode = $this->regions[$shipper->RegionID];
        //requestor
        $xml->Requestor->AccountType = 'D';
        $xml->Requestor->AccountNumber = $accountNumber;
        $xml->Requestor->RequestorContact->PersonName = $shipper->Name . ' ' . $shipper->Surname;
        $xml->Requestor->RequestorContact->Phone = $shipper->Telephone;
        $xml->Requestor->RequestorContact->PhoneExtension = '';
        $xml->Requestor->CompanyName = $shipper->TradingName;
        $xml->Requestor->Address1 = $shipper->Address1 . ',' . $shipper->Address2 . ',' . $shipper->Address3;
        $xml->Requestor->City = $shipper->Address4;
        $xml->Requestor->CountryCode = $shipper->CountryCodeName;
        //place
        $xml->Place->LocationType = 'B';
        $xml->Place->CompanyName = $shipper->TradingName;
        $xml->Place->Address1 = $shipper->Address1;
        $xml->Place->Address2 = $shipper->Address2;
        $xml->Place->Address3 = $shipper->Address3;
        $xml->Place->PackageLocation = $shipper->Address1;
        $xml->Place->City = $shipper->Address4;
        $xml->Place->CountryCode = $shipper->CountryCodeName;
        $xml->Place->PostalCode = $shipper->Postcode;
        //pickup
        $xml->Pickup->PickupDate = date('Y-m-d');
        $xml->Pickup->PickupTypeCode = 'S';
        $xml->Pickup->ReadyByTime = date('h:i');
        $xml->Pickup->CloseTime = '17:00';
        $xml->Pickup->Pieces = '1';
        $xml->Pickup->weight->Weight = '0.5';
        $xml->Pickup->weight->WeightUnit = 'K';
        //pickup contact
        $xml->PickupContact->PersonName = $shipper->Name . ' ' . $shipper->Surname;
        $xml->PickupContact->Phone = $shipper->Telephone;
        $xml->PickupContact->PhoneExtension = '';
        //shippment details
        $xml->ShipmentDetails->AccountType = 'D';
        $xml->ShipmentDetails->AccountNumber = $accountNumber;
        $xml->ShipmentDetails->AWBNumber = $order->AirwayBillNumber;
        $xml->ShipmentDetails->NumberOfPieces = '1';
        $xml->ShipmentDetails->Weight = '0.5';
        $xml->ShipmentDetails->WeightUnit = 'K';
        $xml->ShipmentDetails->GlobalProductCode = 'U';
        $xml->ShipmentDetails->DoorTo = 'DD';
        $xml->ShipmentDetails->DimensionUnit = 'C';

        if ($order->Repeats != '0' && $order->Repeats != '') {
            $repeats = explode(' ', $order->Repeats);
            $amount = $repeats[1];
            $currency = $repeats[0];
        } else {
            $amount = '20';
            $currency = 'GBP';
        }

        $xml->ShipmentDetails->InsuredAmount = $amount;
        $xml->ShipmentDetails->InsuredCurrencyCode = $currency;
        $xml->ShipmentDetails->Pieces->Piece->Weight = '0.5';
        $xml->ShipmentDetails->Pieces->Piece->Depth = '10';
        $xml->ShipmentDetails->Pieces->Piece->Width = '20';
        $xml->ShipmentDetails->Pieces->Piece->Height = '20';

        $body = $xml->asXML();

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        Storage::disk('azure')->put('dhl_test/' . $id . '/pickup-request-' . $id . '.xml', $body);

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string($response);

        //Storage::disk('azure')->put('dhl_test/'.$id.'/access-request-'.$id.'.xml', $xml->asXML());
        Storage::disk('azure')->put('dhl_test/' . $id . '/pickup-response-' . $id . '.xml', $xml->asXML());

        dd($xml);

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order');
            $this->tracking->sendTracking($id);
        }

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($file, 'Success');

        //echo $file;
        //$file = Storage::disk('azure')->get('dhl_label/test.zpl');

        //dd($xml);

        //echo $xml->LabelImage->OutputImage;
        //die();
    }

    /**
     * Book pickup EU
     *
     * @param int $id
     * @return void
     */
    public function BookPickupEU($id)
    {
        $order = $this->order->getOrderDetails($id);
        $endpoint = 'http://xmlpitest-ea.dhl.com';
        $accountNumber = '420747769';

        if (\App::environment('local')) {
            $endpoint = 'http://xmlpitest-ea.dhl.com';
            $accountNumber = '420747769';
        }

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $shipper = $this->order->getShipperData();
        $xml = simplexml_load_file('test_templates/BookPickupRequest_GlobalEU_Valid2.xml'); //load an xml file

        $timezone = new \DateTimeZone('Europe/London');
        $date = new \DateTime('now');
        $date = $date->setTimezone($timezone)->format(\DateTime::ISO8601);
        $date = '2019-04-20T15:02:46.000+01:00';
        //account details
        // $xml->Billing->ShipperAccountNumber = $accountNumber;
        // $xml->Billing->ShippingPaymentType = 'S';
        //order header
        $xml->Request->ServiceHeader->MessageTime = $date;
        $xml->Request->ServiceHeader->MessageReference = $order->PrescriptionID . '-' . $order->ReferenceNumber;
        //stupid workaround
        $charactersToAdd = 32 - strlen($xml->Request->ServiceHeader->MessageReference);
        $xml->Request->ServiceHeader->MessageReference = str_repeat('_', $charactersToAdd) . $xml->Request->ServiceHeader->MessageReference;
        //customer details
        $xml->RegionCode = $this->regions[$shipper->RegionID];
        //requestor
        $xml->Requestor->AccountType = 'D';
        $xml->Requestor->AccountNumber = $accountNumber;
        $xml->Requestor->RequestorContact->PersonName = $shipper->Name . ' ' . $shipper->Surname;
        $xml->Requestor->RequestorContact->Phone = $shipper->Telephone;
        $xml->Requestor->CompanyName = $shipper->TradingName;
        $xml->Requestor->Address1 = $shipper->Address1;
        $xml->Requestor->Address2 = $shipper->Address2;
        $xml->Requestor->Address3 = $shipper->Address3;
        $xml->Requestor->City = $shipper->Address4;
        $xml->Requestor->CountryCode = $shipper->CountryCodeName;
        //place
        $xml->Place->LocationType = 'B';
        $xml->Place->CompanyName = $shipper->TradingName;
        $xml->Place->Address1 = $shipper->Address1;
        $xml->Place->Address2 = $shipper->Address2;
        $xml->Place->Address3 = $shipper->Address3;
        $xml->Place->PackageLocation = $shipper->Address1;
        $xml->Place->City = $shipper->Address4;
        $xml->Place->StateCode = $shipper->CountryCodeName;
        $xml->Place->DivisionName = '';
        $xml->Place->CountryCode = $shipper->CountryCodeName;
        $xml->Place->PostalCode = $shipper->Postcode;
        //pickup
        $xml->Pickup->PickupDate = date('Y-m-d');
        $xml->Pickup->PickupTypeCode = 'S';
        $xml->Pickup->ReadyByTime = date('h:i');
        $xml->Pickup->CloseTime = '17:00';
        $xml->Pickup->AfterHoursClosingTime = '17:00';
        $xml->Pickup->AfterHoursLocation = 'At the Office';
        $xml->Pickup->Pieces = '1';
        $xml->Pickup->weight->Weight = '0.5';
        $xml->Pickup->weight->WeightUnit = 'K';
        //pickup contact
        $xml->PickupContact->PersonName = $shipper->Name . ' ' . $shipper->Surname;
        $xml->PickupContact->Phone = $shipper->Telephone;
        //shippment details
        $xml->ShipmentDetails->AccountType = 'D';
        $xml->ShipmentDetails->AccountNumber = $accountNumber;
        $xml->ShipmentDetails->BillToAccountNumber = $accountNumber;
        $xml->ShipmentDetails->AWBNumber = '';
        $xml->ShipmentDetails->NumberOfPieces = '1';
        $xml->ShipmentDetails->Weight = '0.5';
        $xml->ShipmentDetails->WeightUnit = 'K';
        $xml->ShipmentDetails->GlobalProductCode = 'D';
        $xml->ShipmentDetails->DoorTo = 'DD';
        $xml->ShipmentDetails->DimensionUnit = 'C';

        if ($order->Repeats != '0' && $order->Repeats != '') {
            $repeats = explode(' ', $order->Repeats);
            $amount = $repeats[1];
            $currency = $repeats[0];
        } else {
            $amount = '20';
            $currency = 'GBP';
        }

        $xml->ShipmentDetails->InsuredAmount = $amount;
        $xml->ShipmentDetails->InsuredCurrencyCode = $currency;
        $xml->ShipmentDetails->Pieces->Piece->Weight = '0.5';
        $xml->ShipmentDetails->Pieces->Piece->Depth = '10';
        $xml->ShipmentDetails->Pieces->Piece->Width = '20';
        $xml->ShipmentDetails->Pieces->Piece->Height = '20';
        $xml->ShipmentDetails->SpecialService = '';

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $xml->asXML(), //send it via body as xml
        ];

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string($response);
        dd($xml);

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order');
            $this->tracking->sendTracking($id);
        }

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($file, 'Success');

        //echo $file;
        //$file = Storage::disk('azure')->get('dhl_label/test.zpl');

        //dd($xml);

        //echo $xml->LabelImage->OutputImage;
        //die();
    }

    /**
     * Capability check
     *
     * @param int $id
     * @return void
     */
    public function CapabilityEU($id)
    {
        $order = $this->order->getOrderDetails($id);
        $endpoint = 'https://xmlpi-ea.dhl.com';
        $accountNumber = '420747769';

        // if (\App::environment('local')) {
        //     $endpoint = 'http://xmlpitest-ea.dhl.com';
        //     $accountNumber = '420747769';
        // }

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $shipper = $this->order->getShipperData();
        $xml = simplexml_load_file('test_templates/Valid1_Capbility_EUEU_MultipleBkgs_NonDutiable_Request.xml'); //load an xml file

        $timezone = new \DateTimeZone('Europe/London');
        $date = new \DateTime('now');
        $date = $date->setTimezone($timezone)->format(\DateTime::ISO8601);
        $date = '2019-04-20T15:02:46.000+01:00';
        //account details
        // $xml->Billing->ShipperAccountNumber = $accountNumber;
        // $xml->Billing->ShippingPaymentType = 'S';
        //order header
        $xml->GetCapability->Request->ServiceHeader->MessageTime = $date;
        $xml->GetCapability->Request->ServiceHeader->MessageReference = $order->PrescriptionID . '-' . $order->ReferenceNumber;

        //stupid workaround
        $charactersToAdd = 32 - strlen($xml->GetCapability->Request->ServiceHeader->MessageReference);
        $xml->GetCapability->Request->ServiceHeader->MessageReference = str_repeat('_', $charactersToAdd) . $xml->GetCapability->Request->ServiceHeader->MessageReference;
        //from
        $xml->GetCapability->From->CountryCode = $shipper->CountryCodeName;
        $xml->GetCapability->From->Postalcode = $shipper->Postcode;
        $xml->GetCapability->From->City = $shipper->Address4;
        //bkg details
        $xml->GetCapability->BkgDetails->PaymentCountryCode = $shipper->CountryCodeName;
        $xml->GetCapability->BkgDetails->Date = date('Y-m-d');
        $xml->GetCapability->BkgDetails->ReadyTime = 'PT16H00M';
        $xml->GetCapability->BkgDetails->ReadyTimeGMTOffset = '+01:00';
        $xml->GetCapability->BkgDetails->DimensionUnit = 'CM';
        $xml->GetCapability->BkgDetails->WeightUnit = 'KG';
        //pieces
        $xml->GetCapability->BkgDetails->Pieces->Piece->PieceID = '1';
        $xml->GetCapability->BkgDetails->Pieces->Piece->Weight = '0.5';
        $xml->GetCapability->BkgDetails->Pieces->Piece->Depth = '10';
        $xml->GetCapability->BkgDetails->Pieces->Piece->Width = '20';
        $xml->GetCapability->BkgDetails->Pieces->Piece->Height = '20';
        //is dutiable
        $xml->GetCapability->BkgDetails->IsDutiable = 'N';
        $xml->GetCapability->BkgDetails->NetworkTypeCode = 'AL';

        //shippment details
        $xml->GetCapability->To->CountryCode = $order->CountryCodeName;
        $xml->GetCapability->To->Postalcode = $order->DPostcode;
        $xml->GetCapability->To->City = $order->DAddress3;

        $body = $xml->asXML();

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        Storage::disk('azure')->put('dhl_test/' . $id . '/capability-request-' . $id . '.xml', $body);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string($response);

        Storage::disk('azure')->put('dhl_test/' . $id . '/capability-response-' . $id . '.xml', $xml->asXML());
        dd($xml);

        //Storage::disk('azure')->put('dhl_test/'.$id.'/access-request-'.$id.'.xml', $xml->asXML());

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order');
            $this->tracking->sendTracking($id);
        }

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($file, 'Success');

        //echo $file;
        //$file = Storage::disk('azure')->get('dhl_label/test.zpl');

        //dd($xml);

        //echo $xml->LabelImage->OutputImage;
        //die();
    }

    /**
     * Book pickup
     *
     * @param int $id
     * @return void
     */
    public function Tracking($id)
    {
        $order = $this->order->getOrderDetails($id);
        $endpoint = 'http://xmlpitest-ea.dhl.com';
        $accountNumber = '420747769';

        if (\App::environment('local')) {
            $endpoint = 'http://xmlpitest-ea.dhl.com';
            $accountNumber = '420747769';
        }

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $xml = simplexml_load_file('test_templates/TrackingRequest_SingleLP_PieceEnabled_S_1.xml'); //load an xml file

        $timezone = new \DateTimeZone('Europe/London');
        $date = new \DateTime('now');
        $date = $date->setTimezone($timezone)->format(\DateTime::ISO8601);
        $date = '2019-04-20T15:02:46.000+01:00';

        $xml->Request->ServiceHeader->MessageTime = $date;
        $xml->Request->ServiceHeader->MessageReference = $order->PrescriptionID . '-' . $order->ReferenceNumber;

        //stupid workaround
        $charactersToAdd = 32 - strlen($xml->Request->ServiceHeader->MessageReference);
        $xml->Request->ServiceHeader->MessageReference = str_repeat('_', $charactersToAdd) . $xml->Request->ServiceHeader->MessageReference;
        //order
        $xml->Request->ServiceHeader->MessageTime = $date;
        $xml->LPNumber = $order->TrackingCode;

        $body = $xml->asXML();

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        Storage::disk('azure')->put('dhl_test/' . $id . '/tracking-request-' . $id . '.xml', $body);

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string($response);

        Storage::disk('azure')->put('dhl_test/' . $id . '/tracking-response-' . $id . '.xml', $xml->asXML());

        dd($xml);

        //Storage::disk('azure')->put('dhl_test/'.$id.'/access-request-'.$id.'.xml', $xml->asXML());

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order');
            $this->tracking->sendTracking($id);
        }

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($file, 'Success');
    }

    /**
     * Book pickup
     *
     * @param int $id
     * @return void
     */
    public function TrackingAWB($id)
    {
        $order = $this->order->getOrderDetails($id);
        $endpoint = 'http://xmlpitest-ea.dhl.com';
        $accountNumber = '420747769';

        if (\App::environment('local')) {
            $endpoint = 'http://xmlpitest-ea.dhl.com';
            $accountNumber = '420747769';
        }

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $xml = simplexml_load_file('test_templates/TrackingRequest_SingleAWB.xml'); //load an xml file

        $timezone = new \DateTimeZone('Europe/London');
        $date = new \DateTime('now');
        $date = $date->setTimezone($timezone)->format(\DateTime::ISO8601);
        $date = '2019-04-20T15:02:46.000+01:00';

        $xml->Request->ServiceHeader->MessageTime = $date;
        $xml->Request->ServiceHeader->MessageReference = $order->PrescriptionID . '-' . $order->ReferenceNumber;

        //stupid workaround
        $charactersToAdd = 32 - strlen($xml->Request->ServiceHeader->MessageReference);
        $xml->Request->ServiceHeader->MessageReference = str_repeat('_', $charactersToAdd) . $xml->Request->ServiceHeader->MessageReference;
        //order
        $xml->Request->ServiceHeader->MessageTime = $date;
        if ($order->AirwayBillNumber != '') {
            $xml->AWBNumber = $order->AirwayBillNumber;
        } else {
            return $this->sendError("No AWB set!");
        }

        //$xml->AWBNumber = '4781585060';

        $body = $xml->asXML();

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        Storage::disk('azure')->put('dhl_test/' . $id . '/tracking-awb-request-' . $id . '.xml', $body);

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string($response);

        Storage::disk('azure')->put('dhl_test/' . $id . '/tracking-awb-response-' . $id . '.xml', $xml->asXML());

        dd($xml);

        //Storage::disk('azure')->put('dhl_test/'.$id.'/access-request-'.$id.'.xml', $xml->asXML());

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order');
            $this->tracking->sendTracking($id);
        }

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($file, 'Success');
    }

    /**
     * Get DHL label
     *
     * @return JSON
     */
    public function getLabelDHL($id)
    {
        $this->activity->log($id, 'DHL print label');

        header("Content-Type: text/zpl;encoding=UTF-8");
        header("Content-Disposition: inline; filename=$id.zpl");
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');
        echo $file;
        die();
        //return $this->sendResponse($file, 'Success');
    }
}

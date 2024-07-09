<?php

namespace App\Http\Controllers;

use GuzzleHttp;
use App\Library\Order;
use App\Library\Helper;
use App\Library\Mapper;
use App\Library\Activity;
use App\Library\Tracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class APIController extends Controller
{
    private $order;
    private $tracking;
    private $h;
    private $activity;
    private $map;
    private $genders = ['1' => 'Male', '2' => 'Female', '3' => 'Transgender', '4' => 'For school use'];
    private $regions = ['1' => 'AF', '2' => 'AM', '3' => 'AM', '4' => 'AN', '5' => 'AP', '6' => 'EU', '7' => 'AU'];
    private $nonEUCountries = ['4', '7', '22', '100', '124', '128', '142', '143', '184', '205', '216', '221', '241', '243'];

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->order = new Order;
        $this->h = new Helper;
        $this->map = new Mapper;
        $this->activity = new Activity;
        $this->tracking = new Tracking;
    }

    /**
     * Validate a shipment for UPS and return a ZPL formatted file
     *
     * @param int $id
     * @return JsonResponse
     */
    public function shipmentValidationUPS($id)
    {
        $order = $this->order->getOrderDetails($id);
        $shipper = $this->order->getShipperData();
        $endpoint = 'https://onlinetools.ups.com';

        $params = [
            'paper_invoice' => false,
            'gif' => false,
            'resend' => false
        ];

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        //COD and CI go under 7F
        //any other CI go under 7E

        if (
            ($order->ClientID == 49 || $order->ClientID == 50)
            && ($this->order->isCI($order) && $this->order->isCOD($order))
        ) {
            $shipperNumber = '97W57F';
            $fromName = 'TREATED.COM';
        } else {
            $shipperNumber = '97W57E';
            $fromName = 'HR HEALTHCARE';
        }

        $access = simplexml_load_file('xml_templates/ups_access.xml');
        $access->AccessLicenseNumber = 'ED405DEF7456FECD';
        $access->UserId = '97W57E';
        $access->Password = 'Liverpool1';

        if ($order->CountryCode == 1) {
            $serviceCode = '11';
            $serviceDescription = 'UPS Standard';
        } else {
            $serviceCode = '65';
            $serviceDescription = 'UPS Saver';
        }

        $url = '/ups.app/xml/ShipConfirm';

        //change access details
        $confirm = simplexml_load_file('xml_templates/ups_shipment_confirm.xml');
        $confirm->Request->TransactionReference->CustomerContext = $order->PrescriptionID . '-' . $order->ReferenceNumber;

        //shipper details
        $confirm->Shipment->Shipper->Name = 'Parcel Xpert'; //$shipper->TradingName;
        $confirm->Shipment->Shipper->AttentionName = 'Parcel Xpert'; //$shipper->TradingName;
        $confirm->Shipment->Shipper->TaxIdentificationNumber = 'GB279774238';
        $confirm->Shipment->Shipper->PhoneNumber = $shipper->Telephone;
        $confirm->Shipment->Shipper->ShipperNumber = $shipperNumber;
        $confirm->Shipment->Shipper->Address->AddressLine1 = $shipper->Address1 . ',' . $shipper->Address2;
        $confirm->Shipment->Shipper->Address->City = $shipper->Address3;
        $confirm->Shipment->Shipper->Address->StateProvinceCode = $shipper->Address4;
        $confirm->Shipment->Shipper->Address->CountryCode = $shipper->CountryCodeName;
        $confirm->Shipment->Shipper->Address->PostalCode = $shipper->Postcode;

        //clean mobile phone number
        $phone = preg_replace('/[^0-9]/', '', $order->Telephone);
        if ($phone == '') {
            $phone = preg_replace('/[^0-9]/', '', $order->Mobile);
        }

        //customer details
        $confirm->Shipment->ShipTo->CompanyName = $order->Name . ' ' . $order->Surname;
        $confirm->Shipment->ShipTo->AttentionName = $order->Name . ' ' . $order->Surname;
        $confirm->Shipment->ShipTo->PhoneNumber = $phone;
        $confirm->Shipment->ShipTo->Address->AddressLine1 = $order->DAddress1;
        $confirm->Shipment->ShipTo->Address->AddressLine2 = $order->DAddress2;
        $confirm->Shipment->ShipTo->Address->City = $order->DAddress3;
        $confirm->Shipment->ShipTo->Address->StateProvinceCode = '';
        $confirm->Shipment->ShipTo->Address->CountryCode = $order->CountryCodeName;
        $confirm->Shipment->ShipTo->Address->PostalCode = $order->DPostcode;

        if ($order->UPSAccessPointAddress == 1) {
            $alternate = $this->order->getAlternateShipperData($id);
            $confirm->Shipment->AlternateDeliveryAddress->Name = $alternate->Name;
            $confirm->Shipment->AlternateDeliveryAddress->AttentionName = $alternate->Name;
            $confirm->Shipment->AlternateDeliveryAddress->UPSAccessPointID = $alternate->UPSAccessPoint;
            $confirm->Shipment->AlternateDeliveryAddress->Address->AddressLine1 = $alternate->Address1;
            $confirm->Shipment->AlternateDeliveryAddress->Address->AddressLine2 = $alternate->Address2;
            $confirm->Shipment->AlternateDeliveryAddress->Address->City = $alternate->Address3;
            $confirm->Shipment->AlternateDeliveryAddress->Address->StateProvinceCode = '';
            $confirm->Shipment->AlternateDeliveryAddress->Address->CountryCode = $alternate->CountryCodeName;
            $confirm->Shipment->AlternateDeliveryAddress->Address->PostalCode = $alternate->Postcode;
            $confirm->Shipment->ShipmentIndicationType->Code = '01';
        }

        if ($order->Email != '') {
            $confirm->Shipment->ShipmentServiceOptions->Notification->NotificationCode = '6';
            $confirm->Shipment->ShipmentServiceOptions->Notification->EMailMessage->FromName = $fromName;
            $confirm->Shipment->ShipmentServiceOptions->Notification->EMailMessage->EMailAddress = $order->Email;
            $confirm->Shipment->ShipmentServiceOptions->Notification->EMailMessage->Memo = 'PARCEL FROM ' . $fromName;
            $confirm->Shipment->ShipmentServiceOptions->Notification->Locale->Language = 'ENG';
            $confirm->Shipment->ShipmentServiceOptions->Notification->Locale->Dialect = 'GB';

            if ($order->UPSAccessPointAddress == 1) {
                $notification2 = $confirm->Shipment->ShipmentServiceOptions->addChild('Notification');
                $notification2->addChild('NotificationCode', '012');
                $email = $notification2->addChild('EMailMessage');
                $email->addChild('FromName', $fromName);
                $email->addChild('EMailAddress', $order->Email);
                $email->addChild('Memo', 'PARCEL FROM ' . $fromName);
                $locale = $notification2->addChild('Locale');
                $locale->addChild('Language', 'ENG');
                $locale->addChild('Dialect', 'GB');
            }
        }

        //special case for ci or cod orders
        if ($this->order->isCI($order) || $this->order->isCOD($order)) {
            $products = $this->order->getProducts($id);

            if (count($products) == 0) {
                return $this->sendError("No products found for order");
            }

            if ($order->Repeats != '0' && $order->Repeats != '') {
                $repeats = explode(' ', $order->Repeats);
                $amount = $repeats[1];
                $currency = $repeats[0];
            } else {
                $amount = '20';
                $currency = 'GBP';
            }

            $value = round($amount / count($products), 2);
        }

        //special case for ci orders
        if ($this->order->isCI($order)) {
            // Possible Values are: 01 - Invoice; 03 - CO; 04 - NAFTA CO; 05 - Partial Invoice; 06 - Packinglist,
            // 07 - Customer Generated Forms; 08 – Air Freight Packing List; 09 - CN22 Form; 10 – UPS Premium Care Form,
            // 11 - EEI. For shipment with return service, 01, 05 or 10 are the only valid values.
            // Note: 01 and 05 are mutually exclusive and 05 are only valid for return shipments only.

            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->FormType = '01';
            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->InvoiceNumber = $order->PrescriptionID . '-' . $order->ReferenceNumber; //Required for invoices
            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->PurchaseOrderNumber = $order->PrescriptionID; //Required for invoices
            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->InvoiceDate = date('Ymd'); //Required for invoices
            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->DeclarationStatement = 'I hereby certify that the information on this invoice is true and correct and the contents and value of this shipment is as stated above.'; //Required for invoices
            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->ReasonForExport = 'SALE'; //Required for invoices
            $confirm->Shipment->ShipmentServiceOptions->InternationalForms->CurrencyCode = $currency; //Required for invoices
            // $confirm->Shipment->ShipmentServiceOptions->InternationalForms->NumOfCopies = '01';//Number of pages

            $confirm->Shipment->SoldTo->CompanyName = $order->Name . ' ' . $order->Surname;
            $confirm->Shipment->SoldTo->AttentionName = $order->Name . ' ' . $order->Surname;
            $confirm->Shipment->SoldTo->PhoneNumber = $phone;
            $confirm->Shipment->SoldTo->Address->AddressLine1 = $order->DAddress1;;
            $confirm->Shipment->SoldTo->Address->AddressLine2 = $order->DAddress2;
            // $confirm->Shipment->SoldTo->Address->AddressLine3 = '';
            $confirm->Shipment->SoldTo->Address->City = $order->DAddress3;
            $confirm->Shipment->SoldTo->Address->StateProvinceCode = '';
            $confirm->Shipment->SoldTo->Address->PostalCode = $order->DPostcode;

            if ($order->CountryCodeName == 'IC') {
                $confirm->Shipment->SoldTo->Address->CountryCode = 'ES';
            } else {
                $confirm->Shipment->SoldTo->Address->CountryCode = $order->CountryCodeName;
            }

            //create Products in ShipmentServiceOptions
            foreach ($products as $product) {
                $childProduct = $confirm->Shipment->ShipmentServiceOptions->InternationalForms->addChild('Product');
                $childProduct->addChild('Description', substr($product->Description . ',' . $product->Quantity . 'x' . $product->Dosage . ' ' . $product->Unit, 0, 35));
                $unit = $childProduct->addChild('Unit');
                $unit->addChild('Number', 1);
                $unit->addChild('Value', $value);
                $unitOfMeasurement = $unit->addChild('UnitOfMeasurement');
                $unitOfMeasurement->addChild('Code', 'CT');
                $unitOfMeasurement->addChild('Description', 'Carton');

                $childProduct->addChild('OriginCountryCode', 'GB');
                $childProduct->addChild('CommodityCode', '30049000');
                // $childProduct->addChild('CommodityCode', '');
            }
        }

        if ($this->order->isCOD($order)) {
            $codCode = 9;

            if ($order->PaymentMethod == '1') {
                $codCode = 1;
            }

            $confirm->Shipment->ShipmentServiceOptions->COD->CODCode = 3;
            $confirm->Shipment->ShipmentServiceOptions->COD->CODFundsCode = $codCode;
            $confirm->Shipment->ShipmentServiceOptions->COD->CODAmount->CurrencyCode = $currency;
            $confirm->Shipment->ShipmentServiceOptions->COD->CODAmount->MonetaryValue = $amount;
        }

        //service
        $confirm->Shipment->Service->Code = $serviceCode;
        $confirm->Shipment->Service->Description = $serviceDescription;
        //reference number
        $confirm->Shipment->ReferenceNumber->Value = $order->PrescriptionID . '-' . $order->ReferenceNumber;
        //payment information
        // $confirm->Shipment->PaymentInformation->Prepaid->BillShipper->AccountNumber = $shipperNumber;
        // $confirm->Shipment->PaymentInformation->BillThirdParty->BillThirdPartyShipper->AccountNumber = '97W57E';
        // $confirm->Shipment->PaymentInformation->BillThirdParty->BillThirdPartyShipper->ThirdParty->Address->PostalCode = $shipper->Postcode;
        // $confirm->Shipment->PaymentInformation->BillThirdParty->BillThirdPartyShipper->ThirdParty->Address->CountryCode = $shipper->CountryCodeName;

        $confirm->Shipment->ItemizedPaymentInformation->ShipmentCharge[0]->BillShipper->AccountNumber = $shipperNumber;
        $confirm->Shipment->ItemizedPaymentInformation->ShipmentCharge[1]->BillThirdParty->BillThirdPartyConsignee->AccountNumber = 'E70E75';
        $confirm->Shipment->ItemizedPaymentInformation->ShipmentCharge[1]->BillThirdParty->BillThirdPartyConsignee->ThirdParty->Address->PostalCode = $shipper->Postcode;
        $confirm->Shipment->ItemizedPaymentInformation->ShipmentCharge[1]->BillThirdParty->BillThirdPartyConsignee->ThirdParty->Address->CountryCode = $shipper->CountryCodeName;

        //package
        $confirm->Shipment->Package->PackagingType->Code = '02';
        $confirm->Shipment->Package->PackageWeight->Weight = '0.5';

        //ShipmentConfirmRequest/LabelSpecification/LabelPrintMethod/Code
        $confirm->LabelSpecification->LabelPrintMethod->Code = 'ZPL';

        // Change the billing information
        // dd($confirm);

        //combine the 2 xml files into 1
        $body = $access->asXML() . $confirm->asXML();

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        Storage::disk('azure')->put('ups_xml/access-request-' . $id . '.xml', $body);

        $client = new GuzzleHttp\Client($options);
        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml_response = simplexml_load_string($response);

        Storage::disk('azure')->put('ups_xml/access-response-' . $id . '.xml', $xml_response->asXML());

        if ($xml_response->Response->ResponseStatusCode != '1') {
            $this->order->updateOrderMessage($id, $xml_response->Error->ErrorDescription);
            return $this->sendError("Request could not be completed. Code " . $xml_response->Response->Error->ErrorCode);
        }

        $validation = simplexml_load_file('xml_templates/ups_shipment_validation.xml');

        //ADD DATA FROM XML RESPONSE
        $validation->Request->TransactionReference->CustomerContext = $confirm->Request->TransactionReference->CustomerContext;
        $validation->ShipmentDigest = $xml_response->ShipmentDigest;

        $body = $access->asXML() . $validation->asXML();

        Storage::disk('azure')->put('ups_xml/validation-request-' . $id . '.xml', $body);

        $options['body'] = $body;
        $url = '/ups.app/xml/ShipAccept';

        $client = new GuzzleHttp\Client($options);
        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml_response = simplexml_load_string($response);

        Storage::disk('azure')->put('ups_xml/validation-response-' . $id . '.xml', $xml_response->asXML());

        if ($this->order->isCOD($order)) {
            //if the order is cod we have to respond with gif and html
            $params['gif'] = true; //let the frontend know to expect a gif
            Storage::disk('azure')->put('ups_labels/' . $id . '.gif', base64_decode($xml_response->ShipmentResults->PackageResults->LabelImage->GraphicImage));
            Storage::disk('azure')->put('ups_labels/' . $id . '.html', base64_decode($xml_response->ShipmentResults->CODTurnInPage->Image->GraphicImage));
        } else {
            Storage::disk('azure')->put('ups_labels/' . $id . '.zpl', base64_decode($xml_response->ShipmentResults->PackageResults->LabelImage->GraphicImage));
        }

        // Storage::disk('azure')->put('ups_labels/'.$id.'.zpl', base64_decode($xml_response->ShipmentResults->PackageResults->LabelImage->GraphicImage));

        if ($this->order->isCI($order) && $this->order->isCIPaper($order)) {
            if (!isset($xml_response->ShipmentResults->Form->Image->GraphicImage)) {
                return $this->sendError("Invoice not created");
            }

            //if the order is ci and ci paper store the pdf invoice
            $params['paper_invoice'] = true;
            Storage::disk('azure')->put('ups_invoice/' . $id . '.pdf', base64_decode($xml_response->ShipmentResults->Form->Image->GraphicImage));
        }

        $this->activity->log($id, 'UPS request label');

        if ($xml_response->Response->ResponseStatusCode != '1') {
            $this->order->updateOrderMessage($id, $xml_response->Error->ErrorDescription);
        } else {
            $this->order->updateOrder($id, $xml_response->ShipmentResults->PackageResults->TrackingNumber, 'Successfully Submitted Order');
            $this->activity->log($id, 'Order changed to SHIPPED');

            $response = $this->tracking->sendTracking($id);

            //indicates an issue when sending tracking
            if ($response != true) {
                return $this->sendError($response, 500);
            }
        }

        return $this->sendResponse($params, 'Success');
    }

    /**
     * Validate a DHL shipment
     *
     * @param int $id
     */
    public function shipmentValidationDHL($id)
    {
        $order = $this->order->getOrderDetails($id);
        $pharmacy = $this->order->getPharmacyDetails($order->PharmacyID, $order->ClientID);

        if (!$pharmacy || !$pharmacy->AccountNumber || !$pharmacy->VATNumber || !$pharmacy->EORI) {
            return $this->sendError("Pharmacy not configured correctly");
        }

        $endpoint = 'https://xmlpi-ea.dhl.com';
        // $accountNumber = '424040996';//harrison healthcare ltd
        $accountNumber = $pharmacy->AccountNumber;
        $billingAccountNumber = $pharmacy->BillingAccountNumber ? $pharmacy->BillingAccountNumber : $pharmacy->AccountNumber;
        // $vat = 'XI843237041';
        $vat = $pharmacy->VATNumber;
        $xmlPath = '/xml/dhl/shipment_validation_10';
        $imageUploadPath = '/xml/dhl/imageupload'; //path to upload the PDF file
        // $eori = 'XI843237041000';//hrhealthcare'
        $eori = $pharmacy->EORI;
        $vars = [];

        $params = [
            'gif' => false,
            'paper_invoice' => false,
            'resend' => false,
        ];

        // uncomment this if not testing
        // if (\App::environment('local')) {
        //     $endpoint = 'http://xmlpitest-ea.dhl.com';
        //     $accountNumber = '420747769';
        //     $xmlPath = '/xml/dhl/shipment_validation_dev';
        // }

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
        // $shipper = $this->order->getShipperData();//shipper should be the pharmacy here
        $shipper = $pharmacy; //this is a bit of a hack but should work
        $regionCode = $this->regions[$shipper->RegionID];

        $phone = preg_replace('/[^0-9]/', '', $order->Telephone);
        if ($phone == '') {
            $phone = preg_replace('/[^0-9]/', '', $order->Mobile);
        }

        $value = explode(' ', $order->Repeats);

        if (!is_numeric($value[1])) {
            return $this->sendError('Non numeric');
        }

        // if ($order->RegionID == '6' && !in_array($order->DCountryCode, $this->nonEUCountries)) {
        // double check if UK will use U/U
        if ($order->DCountryCode == 1) {
            // if ($order->RegionID == '6' && !in_array($order->DCountryCode, $this->nonEUCountries)) {
            $isDutiable = 'N';
            // $globalProductCode = 'U';
            // $localProductCode = 'U';
            $globalProductCode = 'P';
            $localProductCode = 'P';
        } else if ($order->DCountryCode == $shipper->CountryCode) {
            $isDutiable = 'N';
            $globalProductCode = 'N';
            $localProductCode = 'N';
        } else {
            $isDutiable = 'N';
            $globalProductCode = 'U';
            $localProductCode = 'U';
        }

        $products = $this->order->getAttachedProducts($id);

        //calculate value for a single item, round to 3 decimals
        // $itemValues = round((float)$value[1] / count($products), 3);
        $singleItemValue = round((float) $value[1] / count($products), 3);
        $itemValues = [];

        foreach ($products as $product) {
            array_push($itemValues, $singleItemValue);
        }
        // $singleItemValue = intval(($singleItemValue*1000))/1000;

        // dd($singleItemValue,intval(($singleItemValue*1000))/1000,(float)($singleItemValue*3),(float)$value[1]);

        //take the remainder and add it to first product
        if ((float) ($singleItemValue * count($products)) != (float) $value[1]) {
            $remainder = (float) $value[1] - $singleItemValue * count($products);
            $itemValues[0] = $itemValues[0] + $remainder;
        }

        $signature = base64_encode(file_get_contents("images/dhl_signature.png"));

        // in case of France use this VAT
        // if($order->DCountryCode == 75){
        $billedTo = 'XI843237041000';
        // } else {
        // $billedTo = $vat;
        // }

        $vars = [
            'reference' => $reference,
            'accountNumber' => $accountNumber,
            'billingAccountNumber' => $billingAccountNumber,
            'regionCode' => $regionCode,
            'date' => $date,
            'phone' => $phone,
            'isDutiable' => $isDutiable,
            //what do we need to do with dutiable
            'shipper' => $shipper,
            'value' => $value,
            'itemValues' => $itemValues,
            'globalProductCode' => $globalProductCode,
            'localProductCode' => $localProductCode,
            'signature' => $signature,
            'vat' => $vat,
            'eori' => $eori,
            'billedToVAT' => $billedTo,
            // 'thirdParyAccountNumber' => $thirdParyAccountNumber,
            // 'thirdPartyVatNumber' => $thirdPartyVatNumber
        ];

        $body = View::make($xmlPath)->with(compact('order', 'vars', 'products'))->render(); //render to XML

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => '<?xml version="1.0" encoding="UTF-8"?>' . $body
        ];

        Storage::disk('azure')->put('dhl_xml/validation-request-' . $id . '.xml', $body);

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();

        $xml = simplexml_load_string(utf8_encode($response));

        Storage::disk('azure')->put('dhl_xml/validation-response-' . $id . '.xml', $xml->asXML());

        Storage::disk('azure')->put('dhl_labels/' . $id . '.zpl', base64_decode($xml->LabelImage->OutputImage));

        // Storage::disk('azure')->put('dhl_labels/'.$id.'.pdf', base64_decode(strip_tags($xml->LabelImage->MultiLabels->MultiLabel->DocImageVal)));

        if (isset($xml->Response->Status) && $xml->Response->Status->ActionStatus == 'Error') {
            $this->order->updateOrderMessage($id, $xml->Response->Status->Condition->ConditionData);
            return $this->sendError($xml->Response->Status->Condition->ConditionData . ' </br></br> ' . $xml->Response->Status->Condition->ConditionCode);
        } else {
            $this->order->updateOrder($id, $xml->Pieces->Piece->LicensePlate, 'Successfully Submitted Order', 8, $xml->AirwayBillNumber);
            $this->activity->log($id, 'Order changed to SHIPPED');
            $this->tracking->sendTracking($id, true);

            //if the label is created and tracking sent send the PDF document
            // try {
            //     $file = \Storage::disk('pdf')->get("$id.pdf");
            // } catch (\Throwable $th) {
            //     $options = [
            //         'base_uri' => "https://esasys.co.uk",
            //     ];

            //     $client = new GuzzleHttp\Client($options);
            //     $file = $client->request('GET', "/?showFile&PRESCRIPTIONID=$id", $options)->getBody()->getContents(); //if no PDF found get directly from ESA
            // }

            // $vars['airwayBillNumber'] = $xml->AirwayBillNumber;
            // $vars['pdf'] = base64_encode($file);

            // $imageBody = \View::make($imageUploadPath)->with(compact('order', 'vars', 'products'))->render();//render to XML

            // $options = [
            //     'base_uri' => $endpoint,
            //     'headers' => [
            //         'Content-Type' => 'text/xml; charset=UTF8',
            //     ],
            //     'body' => $imageBody
            // ];

            // Storage::disk('azure')->put('dhl_xml/image-request-' . $id . '.xml', $imageBody);

            // $url = '/XMLShippingServlet';
            // $client = new GuzzleHttp\Client($options);

            // $response = $client->request('POST', $url, $options)->getBody()->getContents();
            // $xml = simplexml_load_string(utf8_encode($response));
            // Storage::disk('azure')->put('dhl_xml/image-response-' . $id . '.xml', $xml->asXML());

            // try {
            //     if($xml->Note->ActionNote != 'Success'){
            //         $params['resend'] = true;
            //     } else {
            //         $this->activity->log($id, 'PDF prescription successfully submitted to courrier');
            //     }
            // } catch (\Throwable $th) {
            //     $params['resend'] = true;
            // }
        }

        $this->activity->log($id, 'DHL request label');

        return $this->sendResponse($params, 'Success');
    }

    /**
     * Resend the prescription PDF to DHL
     *
     * @param [type] $id
     * @return void
     */
    public function DHLResendPDF($id)
    {
        $order = $this->order->getOrderDetails($id);
        $imageUploadPath = '/xml/dhl/imageupload'; //path to upload the PDF file
        $endpoint = 'https://xmlpi-ea.dhl.com';
        $accountNumber = '420747769'; //pxp

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

        if ($order->DCountryCode == 1) {
            $isDutiable = 'N';
            $globalProductCode = 'P';
            $localProductCode = 'P';
        } else {
            $isDutiable = 'Y';
            $globalProductCode = 'P';
            $localProductCode = 'P';
        }

        $vars = [
            'reference' => $reference,
            'accountNumber' => $accountNumber,
            'regionCode' => $regionCode,
            'date' => $date,
            'isDutiable' => $isDutiable,
            //what do we need to do with dutiable
            'shipper' => $shipper,
            'globalProductCode' => $globalProductCode,
            'localProductCode' => $localProductCode,
        ];

        //if the label is created and tracking sent send the PDF document
        try {
            $file = Storage::disk('pdf')->get("$id.pdf");
        } catch (\Throwable $th) {
            $options = [
                'base_uri' => "https://esasys.co.uk",
            ];

            $client = new GuzzleHttp\Client($options);
            $file = $client->request('GET', "/?showFile&PRESCRIPTIONID=$id", $options)->getBody()->getContents(); //if no PDF found get directly from ESA
        }

        $vars['airwayBillNumber'] = $order->AirwayBillNumber;
        $vars['pdf'] = base64_encode($file);

        $imageBody = View::make($imageUploadPath)->with(compact('order', 'vars', 'products'))->render(); //render to XML

        $options = [
            'base_uri' => $endpoint,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $imageBody
        ];

        Storage::disk('azure')->put('dhl_xml/image-request-' . $id . '.xml', $imageBody);

        $url = '/XMLShippingServlet';
        $client = new GuzzleHttp\Client($options);

        $response = $client->request('POST', $url, $options)->getBody()->getContents();
        $xml = simplexml_load_string(utf8_encode($response));
        Storage::disk('azure')->put('dhl_xml/image-response-' . $id . '.xml', $xml->asXML());

        if ($xml->Note->ActionNote != 'Success') {
            $this->activity->log($id, 'DHL request label - PDF issue');
        } else {
            $this->activity->deactivateLog($id, 'DHL request label - PDF issue');
            $this->activity->log($id, 'PDF prescription successfully submitted to courrier');
            $this->activity->log($id, 'DHL request label - Prescription resent');
        }

        return $this->sendResponse(['Sent successfully'], 'Success');
    }

    /**
     * DPD API shipment validation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function shipmentValidationDPD($id)
    {
        $order = $this->order->getOrderDetails($id);

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $shipper = $this->order->getShipperData();

        $data = $this->map->DPDValidationMap($order, $shipper);

        $credentials = 'HRHEALTH:L!verp00l2020@'; //use as default

        if (in_array($order->ClientID, ['49', '50'])) {
            $dpdAccount = '660159'; //660159
            $credentials = 'HRHEALTHAPI:dqz1WKJ2fxz-hfy_nyf'; //660159
        } else if (in_array($order->ClientID, ['51'])) {
            $dpdAccount = '660229'; //eveadam 660229
            $credentials = 'TREATEDCOM:bku_nyg7JWZ6acf2xfe'; //660229
        } else {
            $dpdAccount = '403642'; //403642
            $credentials = 'HRHEALTH:L!verp00l2020@'; //403642
        }

        $accountNumber = "account/$dpdAccount"; //the account number doesn't actually do anything currently

        $client = new GuzzleHttp\Client();

        $url = 'https://api.dpd.co.uk/user/?action=login';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'GeoClient' => $accountNumber,
                    'Authorization' => "Basic " . base64_encode($credentials),
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }

        $auth = json_decode($response->getBody()->getContents());
        Storage::disk('azure')->put('dpd_labels/access-response' . $id . '.json', json_encode($auth));

        $session = $auth->data->geoSession;

        //request
        Storage::disk('azure')->put('dpd_labels/validation-request' . $id . '.json', json_encode($data));
        $client = new GuzzleHttp\Client();
        $url = 'https://api.dpd.co.uk/shipping/shipment'; //add ?test=true for testing

        $response = $client->post($url, [
            'headers' => [
                'GeoClient' => $accountNumber,
                'GeoSession' => $session,
                'Accept' => 'application/json'
            ],
            GuzzleHttp\RequestOptions::JSON => $data
        ]);

        $shipmentResponse = json_decode($response->getBody()->getContents());
        Storage::disk('azure')->put('dpd_labels/validation-response' . $id . '.json', json_encode($shipmentResponse));

        if (is_object($shipmentResponse->error)) {
            return $this->sendError($shipmentResponse->error);
        }

        if (!is_object($shipmentResponse->data)) {
            return $this->sendError('API error');
        }

        $shipmentId = $shipmentResponse->data->shipmentId;

        $client = new GuzzleHttp\Client();

        $url = "https://api.dpd.co.uk/shipping/shipment/$shipmentId/label/"; //we fetch the label separately

        $response = $client->get($url, [
            'headers' => [
                'GeoClient' => $accountNumber,
                'GeoSession' => $session,
                'Accept' => 'text/vnd.eltron-epl',
                // text/vnd.citizen-clp OR text/html
            ],
            GuzzleHttp\RequestOptions::JSON => $data
        ]);

        $content = $response->getBody()->getContents();

        Storage::disk('azure')->put('dpd_labels/' . $id . '.epl', $content);

        $this->order->updateOrder($id, $shipmentResponse->data->consignmentDetail[0]->consignmentNumber, 'Successfully Submitted Order');
        $this->activity->log($id, 'Order changed to SHIPPED');
        $this->tracking->sendTracking($id, true);

        return $this->sendResponse([], 'Success');
    }

    /**
     * Get UPS label
     *
     */
    public function getLabelUPS($id)
    {
        $this->activity->log($id, 'UPS print label');

        header("Content-Type: text/zpl;encoding=UTF-8");
        header("Content-Disposition: inline; filename=$id.zpl");
        try {
            $file = Storage::disk('azure')->get('ups_labels/' . $id . '.zpl');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $th) {
            echo 'File not found';
            die();
        }

        echo $file;
        die();
    }

    public function getCOD($id, Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return $this->sendError('Token error');
        }

        $logCheck = $this->activity->log($id, 'UPS print COD label', [], 1, $token);

        if (!$logCheck) {
            return $this->sendError('Token error');
        }

        header("Content-Type: text/html");
        //header("Content-Type: application/pdf;encoding=UTF-8");
        header("Content-Disposition: inline; filename=$id.html");
        //header("Content-Disposition: inline; filename=$id.pdf");
        $file = Storage::disk('azure')->get('ups_labels/' . $id . '.html');
        //$file = Storage::disk('azure')->get('dhl_labels/'.$id.'.pdf');
        echo $file;
        die();
        //return $this->sendResponse($file, 'Success');
    }

    /**
     * Get UPS label
     *
     * @return JSON
     */
    public function getLabelGifUPS($id, Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return $this->sendError('Token error');
        }

        $logCheck = $this->activity->log($id, 'UPS print label', [], 1, $token);

        if (!$logCheck) {
            return $this->sendError('Token error');
        }

        header("Content-Type: image/gif;encoding=UTF-8");
        header("Content-Disposition: inline; filename=$id.gif");
        try {
            $file = Storage::disk('azure')->get('ups_labels/' . $id . '.gif');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $th) {
            echo 'File not found';
            die();
        }

        echo $file;
        die();
    }

    /**
     * Get UPS Invoice
     *
     * @return PDF
     */
    public function getInvoiceUPS($id, Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return $this->sendError('Token error');
        }

        $logCheck = $this->activity->log($id, 'UPS print label', [], 1, $token);

        if (!$logCheck) {
            return $this->sendError('Token error');
        }

        header("Content-Type: application/pdf");
        //header("Content-Type: application/pdf;encoding=UTF-8");
        header("Content-Disposition: inline; filename=$id.pdf");
        //header("Content-Disposition: inline; filename=$id.pdf");
        $file = Storage::disk('azure')->get('ups_invoice/' . $id . '.pdf');
        //$file = Storage::disk('azure')->get('dhl_labels/'.$id.'.pdf');
        echo $file;
        die();
        //return $this->sendResponse($file, 'Success');
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

        try {
            $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.zpl');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $th) {
            echo 'File not found';
            die();
        }

        echo $file;
        die();
    }


    /**
     * Get DPD label (will be clp)
     *
     * @return JSON
     */
    public function getLabelDPD($id)
    {
        $this->activity->log($id, 'DPD print label');

        // header("Content-Type: text/clp;encoding=UTF-8");
        // header("Content-Type: text/vnd.eltron-epl;encoding=UTF-8");
        //header("Content-Type: application/pdf;encoding=UTF-8");
        // header("Content-Disposition: inline; filename=$id.clp");
        // header("Content-Disposition: inline; filename=$id.epl");
        //header("Content-Disposition: inline; filename=$id.pdf");

        /**
         * THIS IS A HACK
         */
        header("Content-Type: text/zpl;encoding=UTF-8");
        header("Content-Disposition: inline; filename=$id.zpl");

        try {
            $file = Storage::disk('azure')->get('dpd_labels/' . $id . '.epl');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $th) {
            echo 'File not found';
            die();
        }
        //$file = Storage::disk('azure')->get('dhl_labels/'.$id.'.pdf');
        echo $file;
        die();
        //return $this->sendResponse($file, 'Success');
    }

    /**
     * TNT manual download CSV
     *
     * @param int $id
     * @return CSV
     */
    public function TNTmanual($id)
    {
        $order = $this->order->getOrderDetails($id);

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        if (is_numeric($order->DAddress1)) {
            return $this->sendError("Address issue");
        }

        $csv = function () use ($order) {
            $phone1 = $this->h->formatPhone($order->Telephone);
            $phone2 = '';
            if (strlen($phone1) > 7) {
                $phone2 = substr($phone1, 7, strlen($phone1));
                $phone1 = substr($phone1, 0, 7);
            }

            $file = fopen('php://output', 'w');
            fputcsv($file, [
                $order->ReferenceNumber . '-' . $order->PrescriptionID, $order->Title, $order->Name,
                $order->Surname, $order->DAddress2, $order->DAddress1, $order->DAddress3,
                $order->DPostcode, $order->CountryCodeName, $order->ReferenceNumber . '-' . $order->PrescriptionID,
                '15N',
                'Carton',
                '1',
                '0.50',
                '20',
                '13',
                '9',
                '0.002',
                $phone1,
                $phone2
            ], ';');
            fclose($file);
        };

        $this->order->setToImportAwaiting($id);
        $this->activity->log($id, 'TNT manual print', [], 2);

        return response()->streamDownload($csv, $id . '.csv', $this->h->manualPrintHeaders($id));
    }

    /**
     * Royal mail manual download
     *
     * @param int $id
     * @return CSV
     */
    public function RMmanualOld($id)
    {
        $order = $this->order->getOrderDetails($id);
        //$product = $this->order->getProduct($id);

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        //$csv = function () use ($order, $product) {
        $csv = function () use ($order) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                "Title",
                "First Name",
                "Last Name",
                "DOB",
                "Address Line1",
                "Address Line2",
                "Address Line3",
                "Address Line4",
                "Postcode",
                "Country",
                "Doctor",
                "Gender",
                "Medicine Name",
                "Quantity",
                "Directions"
            ], ',');
            fputcsv($file, [
                $order->Title, $order->Name, $order->Surname, $order->DOB,
                $order->DAddress1, $order->DAddress2, $order->DAddress3, $order->DAddress4,
                $order->DPostcode, $order->CountryCodeName, $order->DoctorName, $this->genders[$order->Sex],
                'Medicine',
                '1',
                'Please read the provided instructions'
                //$product->Description, $product->Quantity, $product->Instructions
            ], ',');
            fclose($file);
        };

        $this->order->setToImportAwaiting($id);
        $this->activity->log($id, 'Royal mail manual print', [], 2);

        return response()->streamDownload($csv, $id . '.csv', $this->h->manualPrintHeaders($id));
    }

    /**
     * Royal mail manual download
     *
     * @param int $id
     * @return CSV
     */
    public function RMmanual($id)
    {
        $order = $this->order->getOrderDetails($id);
        //$product = $this->order->getProduct($id);

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        //$csv = function () use ($order, $product) {
        $csv = function () use ($order) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                "First Name",
                "Last Name",
                "Address Line 1",
                "City",
                "Address Line 2",
                "Address Line 3",
                "Post Code",
                "Country",
                "Email",
                "Phone",
                "Order Reference",
                "Weight ( KG )",
                "Service Code",
                "Package size"
            ], ',');
            fputcsv($file, [
                $order->Name, $order->Surname, $order->DAddress1, $order->DAddress2, $order->DAddress3, $order->DAddress4,
                $order->DPostcode, $order->CountryCodeName, $order->Email, $order->Telephone,
                $order->ReferenceNumber . '-' . $order->PrescriptionID,
                '0.5',
                'TPS48',
                "Medium parcel"
                //$product->Description, $product->Quantity, $product->Instructions
            ], ',');
            fclose($file);
        };

        $this->order->setToImportAwaiting($id);
        $this->activity->log($id, 'Royal mail manual print', [], 2);

        return response()->streamDownload($csv, $id . '.csv', $this->h->manualPrintHeaders($id));
    }

    /**
     * DPD manual print function
     *
     * @param int $id
     * @return CSV
     */
    public function DPDmanual($id)
    {
        $order = $this->order->getOrderDetails($id);

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $csv = function () use ($order) {
            $deliveryTypeCode = '32';
            if ($order->SaturdayDelivery == '1' || (date('D') == 'Fri' && $order->Sex != '4')) {
                $deliveryTypeCode = '16';
            } else if (in_array($order->DCountryCode, ['23', '74', '107', '162'])) {
                $deliveryTypeCode = '19';
            }

            if (in_array($order->ClientID, ['49', '50'])) {
                $dpdAccount = '2';
            } else if (in_array($order->ClientID, ['51'])) {
                $dpdAccount = '3';
            } else {
                $dpdAccount = '1';
            }

            $addresses = $this->h->cleanAddresses($order->DAddress1, $order->DAddress2, $order->DAddress3, $order->DAddress4, false);

            $file = fopen('php://output', 'w');
            fputcsv($file, [
                $order->PrescriptionID, $order->Name, $order->Surname, $order->Middlename,
                $addresses['address1'], $addresses['address2'], $addresses['address3'], $addresses['address4'],
                $order->DPostcode, $order->CountryCodeName, $order->Email, $this->h->formatPhone($order->Mobile),
                $order->ReferenceNumber . '-' . $order->PrescriptionID, $order->CountryCodeName,
                $deliveryTypeCode,
                $dpdAccount
            ], ',');
            fclose($file);
        };

        $this->order->setToImportAwaiting($id);
        $this->activity->log($id, 'DPD manual print', [], 2);

        return response()->streamDownload($csv, $id . '.csv', $this->h->manualPrintHeaders($id));
    }

    /**
     * DHL manual print function
     * Responds with a CSV file
     *
     * @param int $id
     * @return CSV
     */
    public function DHLmanual($id)
    {
        $order = $this->order->getOrderDetails($id);

        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        $csv = function () use ($order) {
            $file = fopen('php://output', 'w');

            $phone = preg_replace('/[^0-9]/', '', $order->Telephone);
            if ($phone == '') {
                $phone = preg_replace('/[^0-9]/', '', $order->Mobile);
            }

            fputcsv($file, [
                '420747769', $order->ReferenceNumber . '-' . $order->PrescriptionID, $order->Name . ' ' . $order->Surname,
                $order->Name . ' ' . $order->Surname, $order->DAddress1, $order->DAddress2, $order->DAddress3, $order->DPostcode,
                $order->CountryName, $order->CountryCodeName,
                $phone,
                'U',
                '1',
                '0.5',
                '1',
                'Medicine',
                ''
            ], ',');
            fclose($file);
        };

        $this->order->setToImportAwaiting($id);
        $this->activity->log($id, 'DHL manual print', [], 2);

        return response()->streamDownload($csv, $id . '.csv', $this->h->manualPrintHeaders($id));
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @return JSON
     */
    public function UPSmanual($id)
    {
        $order = $this->order->getOrderDetails($id);
        if (!$order) {
            return $this->sendError("Order with ID $id not found");
        }

        if (
            ($order->ClientID == 49 || $order->ClientID == 50)
            && ($this->order->isCI($order) && $this->order->isCOD($order))
        ) {
            $shipperNumber = '97W57F';
            $fromName = 'TREATED.COM';
        } else {
            $shipperNumber = '97W57E';
            $fromName = 'HR HEALTHCARE';
        }

        $serviceType = $order->CountryCode == 1 && strtoupper(substr($order->DPostcode, 0, 2)) == 'BT' ? 'ST' : 'SV';

        //ONLY FOR TESTING
        if (App::environment('local')) {
            $shipperNumber = 'E70E75';
        }

        $manual = simplexml_load_file('manual_templates/ups.xml');

        $addresses = $this->h->cleanAddresses($order->DAddress1, $order->DAddress2, $order->DAddress3, $order->DAddress4, true);

        $manual->OpenShipment->ShipTo->CompanyOrName = $order->Company == '' ? $order->Name . ' ' . $order->Surname : $order->Company;
        $manual->OpenShipment->ShipTo->Attention = $order->Name . ' ' . $order->Surname;
        $manual->OpenShipment->ShipTo->Address1 = $addresses['address1'];
        $manual->OpenShipment->ShipTo->Address2 = $addresses['address2'];
        $manual->OpenShipment->ShipTo->Address3 = $addresses['address3'];
        $manual->OpenShipment->ShipTo->CountryTerritory = $order->CountryCodeName;
        $manual->OpenShipment->ShipTo->PostalCode = $order->DPostcode;
        $manual->OpenShipment->ShipTo->CityOrTown = $addresses['address4'];
        $manual->OpenShipment->ShipTo->Telephone = $this->h->formatPhone($order->Mobile);
        $manual->OpenShipment->ShipTo->EmailAddress = $order->Email;

        if ($order->UPSAccessPointAddress == 1) {
            $alternate = $this->order->getAlternateShipperData($id);
            $manual->OpenShipment->AccessPoint->CompanyOrName = $alternate->Name;
            $manual->OpenShipment->AccessPoint->Address1 = $alternate->Address1 . ' ' . $alternate->Address2;
            $manual->OpenShipment->AccessPoint->CountryTerritory = $alternate->CountryCodeName;
            $manual->OpenShipment->AccessPoint->PostalCode = $alternate->Postcode;
            $manual->OpenShipment->AccessPoint->CityOrTown = $alternate->Address3;
        }

        $manual->OpenShipment->ShipmentInformation->ShipperNumber = $shipperNumber;
        $manual->OpenShipment->ShipmentInformation->ServiceType = $serviceType;
        $manual->OpenShipment->ShipmentInformation->Reference1 = $order->ReferenceNumber . '-' . $order->PrescriptionID;
        $manual->OpenShipment->ShipmentInformation->Reference2 = $order->PrescriptionID;

        $manual->OpenShipment->ShipmentInformation->QVNOption->QVNRecipientAndNotificationTypes->CompanyOrName = $order->Company;
        $manual->OpenShipment->ShipmentInformation->QVNOption->QVNRecipientAndNotificationTypes->ContactName = $order->Name . ' ' . $order->Surname;
        $manual->OpenShipment->ShipmentInformation->QVNOption->QVNRecipientAndNotificationTypes->EMailAddress = $order->Email;
        $manual->OpenShipment->ShipmentInformation->QVNOption->Memo = $order->ReferenceNumber;

        if ($order->PaymentMethod != '0') {
            if ($order->PaymentMethod == '1') {
                $manual->OpenShipment->ShipmentInformation->COD->CashOnly = 'Y';
            } else if ($order->PaymentMethod == '2') {
                $manual->OpenShipment->ShipmentInformation->COD->CashierCheckorMoneyOrderOnlyIndicator = 'Y';
            }

            $manual->OpenShipment->ShipmentInformation->COD->AddShippingChargesToCODIndicator = '0';
            $tokenID = explode('-', $order->TokenID);

            $manual->OpenShipment->ShipmentInformation->COD->Amount = $tokenID[0];
            $manual->OpenShipment->ShipmentInformation->COD->Currency = $tokenID[1];
        }

        if ($order->UPSAccessPointAddress == 1) {
            $manual->OpenShipment->ShipmentInformation->DeliverToAddresseeOnly = 'Y';
            $manual->OpenShipment->ShipmentInformation->HoldatUPSAccessPointOption->APNotificationType = '1';
            $manual->OpenShipment->ShipmentInformation->HoldatUPSAccessPointOption->APNotificationValue = '';
            $manual->OpenShipment->ShipmentInformation->HoldatUPSAccessPointOption->APNotificationFailedEmailAddress = 'adam.pearson@natcol.com';
            $manual->OpenShipment->ShipmentInformation->HoldatUPSAccessPointOption->APNotificationCountryTerritory = '';
            $manual->OpenShipment->ShipmentInformation->HoldatUPSAccessPointOption->APNotificationPhoneCountryCode = '';
            $manual->OpenShipment->ShipmentInformation->HoldatUPSAccessPointOption->APNotificationLanguage = 'ENU';
        }

        $this->order->setToImportAwaiting($id);
        $this->activity->log($id, 'UPS manual print', [], 2);

        return response($manual->asXML(), 200, ['Content-Type' => 'application/xml']);
    }

    public function blank()
    {
        return '';
    }
}

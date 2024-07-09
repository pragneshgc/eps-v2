<?php

namespace App\Library;


use GuzzleHttp;
use App\Library\Order;
use App\Library\Client;
use App\Library\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

/**
 * @property \App\Library\Client $client
 */
class Tracking
{
    private $order;
    private $setting;
    private $invoiceEndpoint = 'https://www.esasys.co.uk/?addInvoiceItem&PRESCRIPTIONID=';

    public function __construct()
    {
        $this->order = new Order;
        $this->setting = new Setting;
        $this->client = new Client;
        if (App::environment('local')) {
            $this->invoiceEndpoint = 'http://dev.esasys.co.uk/?addInvoiceItem&PRESCRIPTIONID=';
        }
    }

    /**
     * Send tracking
     *
     * @param [type] $id
     * @param boolean $request
     */
    public function sendTracking($id, $request = true)
    {
        $order = $this->order->getOrderDetails($id);
        $setting = $this->setting->getSetting($order->DeliveryID);
        $client = $this->client->getClient($order->ClientID);

        if (!$order || !$setting || !$client) {
            return false;
        }

        $xml = simplexml_load_file('xml_return/general.xml');

        $xml->DeliveryCompany = $setting->Name;
        $xml->TrackingLink = $setting->Value;
        $xml->TrackingCode = $order->TrackingCode;
        $xml->RefID = $order->ReferenceNumber;
        $xml->TrackingRef = $order->ReferenceNumber . '-' . $order->PrescriptionID;
        $xml->Username = $client->ReturnUsername;
        $xml->Password = $client->ReturnPassword;

        $body = $xml->asXML();

        $options = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
        ];

        Storage::disk('azure')->put('ups_xml/tracking-code-send-' . $id . '.xml', $body);

        $returnURL = App::environment('local') ? url('/') . '/blank-response' : $client->ReturnURL;

        if ($request) {
            //call external endpoint
            try {
                $req = new GuzzleHttp\Client($options);
                $response = $req->request('POST', $returnURL, $options)->getBody()->getContents();
                Storage::disk('azure')->put('ups_xml/tracking-code-response-' . $id . '.xml', $response); //store the tracking code response
            } catch (GuzzleHttp\Exception\RequestException $exception) {
                return $exception->getMessage();
            }

            //call ESA invoice endpoint
            // try {
            //     $req = new GuzzleHttp\Client();
            //     $req->request('GET', $this->invoiceEndpoint . $id);
            // } catch (GuzzleHttp\Exception\RequestException $exception) {
            //     return $exception->getResponse()->getBody()->getContents();
            // }
        }

        return true;
    }
}

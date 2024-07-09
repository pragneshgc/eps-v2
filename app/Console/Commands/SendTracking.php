<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp;

class SendTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:resend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $xml = simplexml_load_string('<?xml version="1.0"?>
        <ESATracking xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Priority="Normal" Version="1.0">
            <DeliveryCompany>DHL</DeliveryCompany>
            <TrackingLink>http://www.dhl.dk/da/express/soeg_forsendelse.html?AWB=</TrackingLink>
            <TrackingCode>JD014600010213862896</TrackingCode>
            <RefID>327512834</RefID>
            <TrackingRef>327512834-374</TrackingRef>
            <Username/>
            <Password/>
        </ESATracking>');

        $body = $xml->asXML();

        $options = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $body, //send it via body as xml
            'verify' => false
        ];

        $req = new GuzzleHttp\Client($options);
        $response = $req->request('POST', 'https://www.vivami.co/order/tracking', $options)->getBody()->getContents();
        dd($response);
    }
}

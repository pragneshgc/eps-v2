<?php
namespace App\Library;

use App\Library\Helper;
/**
 * Maps arrays or XML objects for various API's
 */
class Mapper
{
    private $h;

    public function __construct()
    {
        $this->h = new Helper;
    }

    public function UPSValidationMap()
    {
        # code...
    }

    /**
     * DPD shipment validation endpoint map
     *
     * @param object $order
     * @param object $shipper
     * @return array
     */
    public function DPDValidationMap($order, $shipper): array
    {
        $phone = $this->h->formatPhone($order->Telephone);
        if ($phone == '') {
            $phone = $this->h->formatPhone($order->Mobile);
        }

        $reference = $order->PrescriptionID . '-' . $order->ReferenceNumber;

        if ($order->Repeats != '0' && $order->Repeats != '') {
            $repeats = explode(' ', $order->Repeats);
            $amount = $repeats[1];
            $currency = $repeats[0];
        } else {
            $amount = '20';
            $currency = 'GBP';
        }

        $collectionDate = date('Y-m-d\TH:i:s', strtotime('+0 hours'));

        //DPD specific delivery type code
        //also check if the order is not eveadam or treated
        // if ($order->SaturdayDelivery == '1' || (date('D') == 'Fri' && $order->Sex != '4') && !in_array($order->ClientID, [50, 51, 52])) {
        //     $deliveryTypeCode = '1^16';//saturday delivery
        // } else 
        
        if (in_array($order->DCountryCode, ['23', '74', '107', '162'])) {
            $deliveryTypeCode = '1^19';//outside UK delivery
        } else {
            $deliveryTypeCode = '1^32';//inside UK delivery
        }
        
        // $deliveryTypeCode = '1^16'; // quick hack

        return [
            "jobId" => null,
            "collectionOnDelivery" => false,
            "generateCustomsData" => "N", //this is for international air shipments
            "collectionDate" => $collectionDate,
            "consolidate" => false,
            "consignment" => [
                [
                    "consignmentNumber" => null, // ours or theirs?
                    "consignmentRef" => null, // ours or theirs?
                    "networkCode" => $deliveryTypeCode, // see above
                    "numberOfParcels" => 1, // always 1 package
                    "totalWeight" => 0.5, //in kg
                    "shippingRef1" => $reference,
                    // "shippingRef2" => "shippingRef2",
                    // "shippingRef3" => $order->PrescriptionID,
                    "customsValue" => $amount, // needs to be GBP
                    "deliveryInstructions" => "", //gate number etc.
                    "parcelDescription" => "Medicine",
                    "liabilityValue" => null,
                    "liability" => false,
                    // "shippersDestinationTaxId" => "SHTaxID", //required for australian shipments only
                    // "vatPaid" => "Y", //required for australian shipments only
                    "parcel" => [
                        [
                            "packageNumber" => 1, //we would only be sending 1 parcel
                            "parcelProduct" => [
                                [
                                    // "productCode" => "12345678",//no idea
                                    "productTypeDescription" => "Medicine",
                                    "productItemsDescription" => "Medicine",
                                    // "productFabricContent" => "productFabricContent", //not applicable
                                    "countryOfOrigin" => "GB",
                                    // "productHarmonisedCode" => "productHarmonisedCode", //might be the same as UPS
                                    "unitWeight" => 0.5,
                                    "numberOfItems" => 1,
                                    "unitValue" => $amount,
                                    // "productUrl" => "www.dpd.co.uk/productURLtest"//only for russia
                                ]
                            ]
                        ]
                    ],
                    "collectionDetails" => [
                        "contactDetails" => [
                            "contactName" => " Dispatch",
                            "telephone" => $shipper->Telephone
                        ],
                        "address" => [
                            "organisation" => "HR Healthcare",
                            "countryCode" => "GB",
                            "postcode" => $shipper->Postcode,
                            "street" => $shipper->Address3 . ' ',
                            "locality" => $shipper->Address2 . ' ' . $shipper->Address1,
                            "town" => $shipper->Address4,
                            "county" => $shipper->CountryName
                        ]
                    ],
                    "deliveryDetails" => [
                        "contactDetails" => [
                            "contactName" => $order->Name . ' ' . $order->Surname,
                            "telephone" => $phone
                        ],
                        "address" => [
                            "organisation" => $order->Name . ' ' . $order->Surname,
                            "countryCode" => $order->CountryCodeName,
                            "postcode" => $order->DPostcode,
                            "street" => $order->DAddress1,
                            "locality" => $order->DAddress2,
                            "town" => $order->DAddress3,
                            "county" => $order->CountryName
                        ],
                        "notificationDetails" => [
                            "email" => $order->Email,
                            "mobile" => $phone
                        ]
                    ],
                ]
            ],

            "invoice" => [
                "countryOfOrigin" => "GB",
                // "invoiceCustomsNumber" => "FDA Reg No",
                "invoiceExportReason" => "Sale",
                "invoiceReference" => $reference,
                "invoiceType" => 2,
                "shippingCost" => '0.00', //Note: We highly recommend populating this with ‘0.00’
                // the below is not required if it's the same as collectionDetails and deliveryDetails
                // "invoiceShipperDetails" => [
                //     "contactDetails" => [
                //         "contactName" => " Dispatch",
                //         "telephone" => $shipper->Telephone
                //     ],
                //     "address" => [
                //         "organisation" => "Parcel Xpert",
                //         "countryCode" => "GB",
                //         "postcode" => $shipper->Postcode,
                //         "street" => $shipper->Address3.' ',
                //         "locality" => $shipper->Address2.' '.$shipper->Address1,
                //         "town" => $shipper->Address4,
                //         "county" => $shipper->CountryName
                //     ],
                //     "vatNumber" => "GBUNREG"//
                // ],
                // "invoiceDeliveryDetails" => [
                //     "contactDetails" => [
                //         "contactName" => "invoiceDeliveryDetails ContactName",
                //         "telephone" => "0121 500 2500"
                //     ],
                //     "address" => [
                //         "organisation" => "invoiceDeliveryDetails Organisation",
                //         "countryCode" => "AU",
                //         "postcode" => "8090",
                //         "street" => "invoiceDeliveryDetails Street",
                //         "locality" => "invoiceDeliveryDetails Locality",
                //         "town" => "invoiceDeliveryDetails Town",
                //         "county" => "invoiceDeliveryDetails County"
                //     ],
                //     "vatNumber" => "DELIVERY123456789"
                // ]
            ],
        ];
    }
}

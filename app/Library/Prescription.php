<?php

namespace App\Library;

use Illuminate\Support\Facades\DB;
use App\Library\Country;
use App\Library\Doctor;
use App\Library\Order;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Storage;

// use Carbon\Carbon;

/**
 * Prescription Model used for import
 */
class Prescription
{
    protected $table = 'Prescription';

    /**
     * Prescription template
     *
     * @var array
     */
    protected $template = [
        'DoctorID' => 0,
        'GMCNO' => '',
        'DoctorName' => '',
        'ClientID' => 49,
        'ReferenceNumber' => 0,
        'Email' => '',
        'GUID' => '',
        'TokenID' => '',
        'Title' => '',
        'Name' => '',
        'Middlename' => '',
        'Surname' => '',
        'DOB' => '',
        'Sex' => '',
        'BMI' => '',
        'Address1' => '',
        'Address2' => '',
        'Address3' => '',
        'Address4' => '',
        'Postcode' => '',
        'CountryCode' => 0,
        'PharmacyID' => 0,
        'DAddress1' => '',
        'DAddress2' => '',
        'DAddress3' => '',
        'DAddress4' => '',
        'DPostcode' => '',
        'DCountryCode' => 0,
        'Telephone' => '',
        'Mobile' => '',
        'PaymentMethod' => 0,
        'Exemption' => 0,
        'CreatedDate' => 0,
        'Notes' => '',
        'Repeats' => '',
        'Status' => 7,
        'TrackingCode' => '',
        'AirwayBillNumber' => '',
        'PaymentStatus' => 0,
        'DeliveryID' => 10,
        'UpdatedDate' => 0,
        'UserID' => 0,
        'Message' => '',
        'SaturdayDelivery' => 0,
        'UPSAccessPointAddress' => 0,
        'TrackingSent' => 0,
        'CSNotes' => '',
        'DoctorAddressID' => '',
        'Company' => '',
        'CustomerID' => 0,
        'JVM' => 0,
    ];

    public $id = false;

    public $exists = false;

    public $childPrescription = false;

    public $testKit = false;

    protected $accessPointData = [];

    protected $prescription;

    protected $errors = [];

    protected $xml;

    protected $accessPoint = false;


    public function __construct($xml = false)
    {
        $this->xml = $xml;

        $this->prescription = $this->template;
    }

    /**
     * Validate XML
     *
     * @param SimpleXMLElement $xml
     * @return self
     */
    public function validate($xml = false)
    {
        if (!$xml && !$this->xml) {
            throw new \Exception("No XML set", 500);
        } else if (!$xml && $this->xml) {
            $xml = $this->xml;
        }

        return $this;
    }

    /**
     * Get prescription template
     *
     * @return array
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Fetch a prescription by ID
     *
     * @param int $id
     * @return self
     */
    public function fetch($id)
    {
        $prescription = DB::table($this->table)->where('PrescriptionID', $id)->first();

        if ($prescription) {
            $this->prescription = (array) $prescription;
        }

        return $this;
    }

    /**
     * Set a prescription value
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value)
    {
        $this->prescription[$key] = $value;

        return $this;
    }

    /**
     * Get current prescription array
     *
     * @return array
     */
    public function get()
    {
        return $this->prescription;
    }

    /**
     * Delete a prescription by id
     *
     * @param int $id
     * @return self
     */
    public function delete($id = false)
    {
        if (!$id && !$this->id) {
            throw new \Exception("No prescription set", 500);
        } else if (!$id && $this->id) {
            $id = $this->id;
        }

        $deleted = DB::table('Prescription')->where('PrescriptionID', $id)->delete();

        if ($deleted) {
            $this->id = false;
            $this->prescription = $this->template;
        }

        return $this;
    }

    /**
     * Set multiple prescription data from array
     *
     * @param array $data
     * @return self
     */
    public function setFields($data)
    {
        foreach ($data as $key => $value) {
            $this->prescription[$key] = $value;
        }

        return $this;
    }

    /**
     * Get a field from the prescription object
     *
     * @param string $key
     * @return mixed
     */
    public function getField($key)
    {
        return $this->prescription[$key];
    }

    /**
     * Get prescription errors
     *
     * @return array/boolean
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Insert a new prescription
     *
     * @return self
     */
    public function insert()
    {
        $this->prescription['CreatedDate'] = time();

        if ($this->childPrescription) {
            return $this;
        }

        //if order exists just update the order
        $exists = $this->orderExists($this->prescription['ReferenceNumber']);
        $message = 'SYSTEM (API RECEIVED)';
        $this->exists = false;

        // if($exists && in_array($exists->Status, [1,9])){
        if ($exists) {
            // $this->exists = true;
            $this->id = $exists->PrescriptionID;

            // DB::table($this->table)->where('PrescriptionID', $this->id)->update($this->prescription);
            DB::table($this->table)->where('PrescriptionID', $this->id)->delete();
            DB::table('Product')->where('PrescriptionID', $this->id)->delete();

            $this->id = DB::table($this->table)->insertGetId($this->prescription);

            $message = 'SYSTEM (API UPDATED)';
        } else {
            // $this->exists = $exists;

            $this->id = DB::table($this->table)->insertGetId($this->prescription);
        }

        if ($this->accessPoint) {
            //add access point data
        }

        //add a log in activity
        DB::table('Activity')->insert([
            'UserID' => 0,
            'Name' => $message,
            'OrderID' => $this->id,
            'Action' => 'order received',
        ]);

        return $this;
    }

    /**
     * Update prescription with data array
     *
     * @param array $data
     * @return self
     */
    public function update($data)
    {
        DB::table($this->table)->where('PrescriptionID', $this->id)->update($data);

        return $this;
    }

    /**
     * Save the imported XML file
     *
     * @param boolean $id
     * @param string $xml
     * @return self
     */
    public function saveXml($id = false, $ref, $xml)
    {
        $time = time();

        if (!$id && $this->id) {
            $id = $this->id;
        } else if (!$id && !$this->id) {
            Storage::disk('azure')->put("xml/CHILD-Ref-$ref--$time.xml", $xml);
            array_push($this->errors, 'Child prescription saved');
            // throw new \Exception("No prescription set", 500);
        }

        Storage::disk('azure')->put("xml/$id-Ref-$ref--$time.xml", $xml);

        return $this;
    }

    /**
     * Map prescription information and set errors
     *
     * @param SimpleXMLElement $xml
     * @return self
     */
    public function mapPrescription($xml = false)
    {
        if (!$xml && !$this->xml) {
            throw new \Exception("No XML set", 500);
        } else if (!$xml && $this->xml) {
            $xml = $this->xml;
        }

        //insert test kits and
        //stop the import in case the received prescription is not the parent one (if it's a child prescription)
        if ($xml->Prescription->Product->TestKit) {
            $this->testKit = true;

            if ($xml->Prescription->Product->TestKit->ParentReferenceNumber != $xml->PatientDetail->Patient->PatientId->ReferenceNumber) {
                $this->childPrescription = true;

                return $this;
            }
        }

        $this->errors = [];

        //init singletons
        $country = new Country;
        $doctor = new Doctor;

        try {
            $mapping = [
                //account details
                // 'ClientID' => $xml->AccountID, //what would accountid be?
                'ClientID' => $xml->SenderID,

                //patient details
                'ReferenceNumber' => $xml->PatientDetail->Patient->PatientId->ReferenceNumber,
                //reference number needs to be an integer
                'UserID' => $xml->PatientDetail->Patient->PatientID->UserId,
                'Name' => $xml->PatientDetail->Patient->PatientName->FirstName,
                'Surname' => $xml->PatientDetail->Patient->PatientName->Surname,
                'Middlename' => $xml->PatientDetail->Patient->PatientName->Middlename,
                'Title' => $xml->PatientDetail->Patient->PatientName->Title,
                'DOB' => $xml->PatientDetail->Patient->DOB,
                'BMI' => 0,
                'Sex' => $xml->PatientDetail->Patient->Sex,

                //home address
                'Address1' => $xml->PatientDetail->Patient->HomeAddress->AddressLine1,
                'Address2' => $xml->PatientDetail->Patient->HomeAddress->AddressLine2,
                'Address3' => $xml->PatientDetail->Patient->HomeAddress->AddressLine3,
                'Address4' => $xml->PatientDetail->Patient->HomeAddress->AddressLine4,
                'Postcode' => $xml->PatientDetail->Patient->HomeAddress->PostCode,
                'CountryCode' => 1,

                //delivery address
                'DAddress1' => $xml->PatientDetail->Patient->DeliveryAddress->AddressLine1,
                'DAddress2' => $xml->PatientDetail->Patient->DeliveryAddress->AddressLine2,
                'DAddress3' => $xml->PatientDetail->Patient->DeliveryAddress->AddressLine3,
                'DAddress4' => $xml->PatientDetail->Patient->DeliveryAddress->AddressLine4,
                'DPostcode' => $xml->PatientDetail->Patient->DeliveryAddress->PostCode,
                'DCountryCode' => 1,
                'UPSAccessPointAddress' => strtoupper($xml->PatientDetail->Patient->UPSAccessPointDelivery) == 'N'
                    ? 0
                    : (strtoupper($xml->PatientDetail->Patient->UPSAccessPointDelivery) == 'Y' ? 1 : 0),

                //email and notes
                'Notes' => '',
                //these order notes are sometimes JSON, what to do with that
                'Telephone' => $xml->PatientDetail->Patient->Telephone,
                'Mobile' => $xml->PatientDetail->Patient->Mobile,
                'Email' => $xml->PatientDetail->Patient->Email,

                //doctor
                'DoctorName' => $xml->Prescription->Prescriber->Doctor->DoctorName,
                'GMCNO' => $xml->Prescription->Prescriber->Doctor->GMCNO,
                'DoctorID' => 0,

                //prescription
                'GUID' => $xml->Prescription->Guid,
                'Repeats' => '',
                'JVM' => isset($xml->Prescription->Pouch) ? $xml->Prescription->Pouch : 0,
            ];

            /** SETUP COUNTRY */
            $countryCode = $xml->PatientDetail->Patient->HomeAddress->CountryCode;
            $dCountryCode = $xml->PatientDetail->Patient->DeliveryAddress->CountryCode;

            if ($countryCode == "" || $countryCode == "EN") {
                $countryCode = "GBR";
            }

            if ($dCountryCode == "" || $dCountryCode == "EN") {
                $dCountryCode = "GBR";
            }

            if ($dCountryCode == "IRE") {
                $dCountryCode = "IRL";
            }

            if ($countryCode == "IRE") {
                $countryCode = "IRL";
            }

            if ($dCountryCode == "" || $dCountryCode == "EN") {
                $dCountryCode = "GBR";
            }

            if (strlen($countryCode) == 2) {
                $mapping['CountryCode'] = $country->getId($countryCode, 'CodeName2');
            } elseif (strlen($countryCode) == 3) {
                $mapping['CountryCode'] = $country->getId($countryCode, 'CodeName3');
            }

            if (strlen($dCountryCode) == 2) {
                $mapping['DCountryCode'] = $country->getId($dCountryCode, 'CodeName2');
            } elseif (strlen($dCountryCode) == 3) {
                $mapping['DCountryCode'] = $country->getId($dCountryCode, 'CodeName3');
            }

            /* if ($countryCode != $dCountryCode) {
                $mapping['DCountryCode'] = $country->getId($dCountryCode, 'CodeName2');
            } else {
                $mapping['DCountryCode'] = $countryCode;
            } */

            //if france check if Monaco is selected
            if ($mapping['DCountryCode'] == "75" && (strtoupper($xml->PatientDetail->Patient->DeliveryAddress->DAddress3) == "MONACO" || (strtoupper($xml->PatientDetail->Patient->DeliveryAddress->DAddress3) == "MONACO"))) {
                $mapping['DCountryCode'] = "143";
            };
            /** /SETUP COUNTRY */

            /*PROCESS NOTES*/
            $mapping['Notes'] = '';

            if (isset($xml->Prescription->PrescriptionNotes)) {
                $mapping['Notes'] .= $xml->Prescription->PrescriptionNotes;
            }

            if (isset($xml->PatientDetail->Patient->BMI)) {
                $mapping['BMI'] .= $xml->PatientDetail->Patient->BMI;
            }

            if (isset($xml->PatientDetail->Patient->Notes)) {
                $notes = json_decode($xml->PatientDetail->Patient->Notes);

                if (json_last_error() === 0) {
                    foreach ($notes as $n) {
                        if ($n->Note != null) {
                            if ($mapping['Notes'] == '') {
                                $mapping['Notes'] .= '</br>';
                            }

                            $mapping['Notes'] .= $n->Note . '</br>';
                        }
                    }
                } else {
                    $mapping['Notes'] = $xml->PatientDetail->Patient->Notes;
                }
            }
            /*/PROCESS NOTES*/

            /* SETUP REPEATS */
            $mapping['Repeats'] = $this->returnRepeats($xml->Prescription);
            /*/SETUP REPEATS*/

            /**SETUP DOCTOR */
            $doctorData = $doctor->getDoctorGmcno((string) $xml->Prescription->Prescriber->Doctor->GMCNO);
            $mapping['DoctorID'] = null;
            $mapping['DoctorAddressID'] = null;

            if (empty($doctorData)) {
                array_push($this->errors, "<span class=\"highlight_red\">********* DOCTOR DOES NOT EXIST OR NOT SUPPLIED **********</span>");
            } else {
                $mapping['DoctorID'] = $doctorData->DoctorID;
                //get latest doctor address ID
                $mapping['DoctorAddressID'] = $doctor->getDoctorAddressID($doctorData->DoctorID) ?? null;

                if ($doctorData->Status != 1) {
                    array_push($this->errors, "<span class=\"highlight_red\">********* THIS DOCTOR IS INACTIVE **********</span>");
                }

                if ($doctorData->DoctorID == 50) {
                    array_push($this->errors, "<span class=\"highlight_red\">********* THIS IS A TEST ORDER (PRESCRIBER IS A TEST DOCTOR) **********</span>");
                }
            }
            /* /SETUP DOCTOR */

            $date = (new \DateTime)::createFromFormat('d/m/Y', $xml->PatientDetail->Patient->DOB);
            $now = new \DateTime();
            $interval = $now->diff($date);
            $age = $interval->y;

            if ($age < 18) {
                array_push($this->errors, "span class=\"highlight_red\">********* PATIENT AGE IS UNDER 18 **********</span>");
            }

            if ($age > 89) {
                array_push($this->errors, "<span class=\"highlight_red\">********* PATIENT AGE IS OLDER THAN 89 **********</span>");
            }

            /*SETUP Customer*/
            if (isset($xml->PatientDetail->Patient->PatientId->UserId)) {
                $mapping['CustomerID'] = $xml->PatientDetail->Patient->PatientId->UserId; // Patient user id from the client side
            };
            /*/SETUP Customer*/

            /** SATURDAY DELIVERY */
            $mapping['SaturdayDelivery'] = strtoupper($xml->PatientDetail->Patient->SaturdayDelivery) == 'Y' ? 1 : 0;
            /* /SATURDAY DELIVERY */

            /** UPS ACCESS */
            $mapping['UPSAccessPointAddress'] = strtoupper($xml->PatientDetail->Patient->UPSAccessPointDelivery) == 'Y' ? 1 : 0;
            // add method to populate the upsaddress
            /* /UPS ACCESS */

            /*FETCH PHARMACY*/
            $mapping['PharmacyID'] = Pharmacy::find($xml->PharmacyID)?->PharmacyID;
            //$mapping['PharmacyID'] = DB::table('Pharmacy')->where('PharmacyID', $xml->PharmacyID)->value('PharmacyID');
            /*/FETCH PHARMACY*/

            /*FETCH BANLIST*/
            if (DB::table('PharmacyBanned')->where('PharmacyID', $mapping['PharmacyID'])->where('TypeID', $mapping['DCountryCode'])->exists()) {
                throw new \Exception("Country $dCountryCode is banned for this pharmacy", 500);
            }

            /* COD */
            if (isset($xml->Prescription->COD)) {
                $mapping['PaymentMethod'] = strtoupper($xml->Prescription->COD->CashOnly) == 'Y' ? 1 : 0;

                if ($mapping['PaymentMethod']) {
                    $mapping['TokenID'] = $xml->Prescription->COD->Amount . '-' . $xml->Prescription->COD->Currency;
                }
            }
            /* /COD */

            /* UPS ACCESS POINT SETUP */
            if ($mapping['UPSAccessPointAddress'] == '1' && isset($xml->PatientDetail->Patient->UPSAccessPointAddress)) {
                $this->accessPoint = true;
                $this->mapUPSAccessPoint($xml->PatientDetail->Patient->UPSAccessPointAddress);
            } else {
                $this->accessPoint = false;
            }
            /* /UPS ACCESS POINT SETUP */
        } catch (\Throwable $th) {
            throw new \Exception("Missing XML parameter: " . $th->getMessage(), 500);
        }

        $this->setFields($mapping);

        return $this;
    }

    /**
     * Return repeats string
     *
     * @param object $prescription
     * @return string
     */
    public function returnRepeats($prescription)
    {
        if (isset($prescription->Repeats)) {
            return $prescription->Repeats;
        } else if (isset($prescription->CommercialInvoiceValue)) {
            return $prescription->CommercialInvoiceValue;
        }

        return '';
    }

    /**
     * Check if an order with this reference number already exists in the system
     *
     * @param [type] $referenceNumber
     * @return object
     */
    public function orderExists($referenceNumber)
    {
        return DB::table($this->table)->select('PrescriptionID')->where('ReferenceNumber', $referenceNumber)->first();
    }

    /**
     * Map UPS access point data
     *
     * @param object $upsAccessPointData
     * @return void
     */
    public function mapUPSAccessPoint($upsAccessPointData)
    {
        $order = new Order;
        $country = new Country;

        $countryID = $country->getId($upsAccessPointData->CountryCode, 'CodeName2');
        $notificationLanguage = $order->matchLanguageMapping($countryID, false);


        $this->accessPointData = [
            'Name' => $upsAccessPointData->CompanyOrName,
            'Address1' => $upsAccessPointData->AddressLine1,
            'Address2' => $upsAccessPointData->AddressLine2,
            'Address3' => $upsAccessPointData->CityOrTown,
            'Address4' => '',
            'Postcode' => $upsAccessPointData->PostCode,
            'CountryCode' => $countryID,
            'APINotificationType' => 1,
            'APINotificationValue' => $upsAccessPointData->APNotificationEmail,
            'APINotificationFailedEmailAddress' => 'info@natcol.com',
            'APINotificationCountryTerritory' => $upsAccessPointData->APNotificationCountryTerritory,
            'APINotificationPhoneCountryCode' => '44',
            'APINotificationLanguage' => $notificationLanguage,
            'UPSAccessPoint' => $upsAccessPointData->UPSAccessPointID,
        ];
    }
}

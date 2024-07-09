<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Dto\OrderDto;
use App\Models\Doctor;
use App\Models\Country;
use App\Models\Product;
use App\Helpers\Generic;
use App\Models\Activity;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\Doctoraddress;
use App\Models\PrescriptionFile;
use App\Exceptions\OrderException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class PrescriptionService
{
    protected $accessPointData = [];
    protected $accessPoint = false;
    public bool $childPrescription = false;
    private array $data = [];
    public array $errors = [];
    public ?int $id = null;
    public bool $testKit = false;

    private string $ref;
    public $exists = false;
    public array $validationErrors = [];
    public function __construct(public array $arr, private string $contentType)
    {
        $this->arr = OrderDto::validate($this->arr);
        //$this->arr = convertNullToEmptyString($data);
    }

    public function mapOrderData()
    {
        $patientDetail = $this->arr['PatientDetail'];
        $prescription = $this->arr['Prescription'];
        $products = $this->arr['Prescription']['Product'];

        if (isset($products['TestKit'])) {
            $this->testKit = true;
            if (
                $products['TestKit']['ParentReferenceNumber']
                != $patientDetail['PatientId']['ReferenceNumber']
            ) {
                $this->childPrescription = true;
                return $this;
            }
        }

        $this->data['ClientID'] = $this->arr['ClientID'];

        $this->setPatient($patientDetail);
        $this->setPrescriber($prescription);
        $this->setCountry($patientDetail['Patient']);
        $this->checkAge($patientDetail['Patient']);
        $this->setPharmacy();
        $this->checkOrderPayment($prescription);
        $this->checkAccessPoint($patientDetail['Patient']);

        return $this;
    }

    private function setPatient($patientArr)
    {
        $patient = $patientArr['Patient'];

        //$familyDoctor = $patientArr['FamilyDoctor'];

        $this->data['ReferenceNumber'] = $patient['PatientId']['ReferenceNumber'];
        $this->data['UserID'] = $patient['PatientId']['UserId'];

        $this->data['Title'] = !empty($patient['PatientName']['Title']) ? $patient['PatientName']['Title'] : '';
        $this->data['Name'] = $patient['PatientName']['FirstName'];
        $this->data['Surname'] = $patient['PatientName']['Surname'];
        $this->data['Middlename'] = !empty($patient['PatientName']['Middlename']) ? $patient['PatientName']['Middlename'] : '';

        $this->data['DOB'] = $patient['DOB'];
        $this->data['BMI'] = !empty($patient['BMI']) ? $patient['BMI'] : 0;
        $this->data['Sex'] = $patient['Sex'];

        $deliveryAddress = $patient['DeliveryAddress'];
        $homeAddress = $patient['HomeAddress'];

        //home address
        $this->data['Address1'] = !empty($homeAddress['AddressLine1']) ? $homeAddress['AddressLine1'] : '';
        $this->data['Address2'] = !empty($homeAddress['AddressLine2']) ? $homeAddress['AddressLine2'] : '';
        $this->data['Address3'] = !empty($homeAddress['AddressLine3']) ? $homeAddress['AddressLine3'] : '';
        $this->data['Address4'] = !empty($homeAddress['AddressLine4']) ? $homeAddress['AddressLine4'] : '';
        $this->data['Postcode'] = !empty($homeAddress['PostCode']) ? $homeAddress['PostCode'] : '';
        $this->data['CountryCode'] = !empty($homeAddress['CountryCode']) ? $homeAddress['CountryCode'] : 1;

        //delivery address
        $this->data['DAddress1'] = !empty($deliveryAddress['AddressLine1']) ? $deliveryAddress['AddressLine1'] : '';
        $this->data['DAddress2'] = !empty($deliveryAddress['AddressLine2']) ? $deliveryAddress['AddressLine2'] : '';
        $this->data['DAddress3'] = !empty($deliveryAddress['AddressLine3']) ? $deliveryAddress['AddressLine3'] : '';
        $this->data['DAddress4'] = !empty($deliveryAddress['AddressLine4']) ? $deliveryAddress['AddressLine4'] : '';
        $this->data['DPostcode'] = $deliveryAddress['PostCode'];
        $this->data['DCountryCode'] = !empty($deliveryAddress['CountryCode']) ? $deliveryAddress['CountryCode'] : 1;

        $this->data['SaturdayDelivery'] = strtoupper($patient['SaturdayDelivery']) == 'Y' ? 1 : 0;

        $this->data['UPSAccessPointAddress'] = strtoupper($patient['UPSAccessPointDelivery']) == 'N'
            ? 0
            : (strtoupper($patient['UPSAccessPointDelivery']) == 'Y' ? 1 : 0);

        $this->data['Notes'] = '';
        if (!empty($patient['Notes'])) {
            $notes = json_decode($patient['Notes']);
            if (json_last_error() === 0) {
                foreach ($notes as $n) {
                    if ($n->Note != null) {
                        if ($this->data['Notes'] == '') {
                            $this->data['Notes'] .= '</br>';
                        }

                        $this->data['Notes'] .= $n->Note . '</br>';
                    }
                }
            }
        }

        $this->data['Telephone'] = $patient['Telephone'];
        $this->data['Mobile'] = $patient['Mobile'];
        $this->data['Email'] = $patient['Email'];

        if (isset($patient['PatientId']['UserId'])) {
            $this->data['CustomerID'] = $patient['PatientId']['UserId']; // Patient user id from the client side
        }
    }

    private function setPrescriber($prescription)
    {
        if (empty($prescription)) {
            $this->errors[] = 'Import error: Prescriber data not found';
            return;
        }
        $this->data['GUID'] = $prescription['Guid'] ?? NULL;

        $repeats = '';
        if (isset($prescription['Repeats'])) {
            $repeats = $prescription['Repeats'];
        } else if (isset($prescription['CommercialInvoiceValue'])) {
            $repeats = $prescription['CommercialInvoiceValue'];
        }

        $this->data['Repeats'] = $repeats;

        if (!empty($prescription['PrescriptionNotes'])) {
            $this->data['Notes'] .= $prescription['PrescriptionNotes'];
        }

        $this->data['DoctorName'] = $prescription['Prescriber']['Doctor']['DoctorName'];
        $this->data['GMCNO'] = $prescription['Prescriber']['Doctor']['GMCNO'];
        $this->data['JVM'] = isset($prescription['Pouch']) ? $prescription['Pouch'] : 0;
        $this->data['Status'] = 1;

        $this->setDoctor($prescription);
    }

    private function setDoctor($prescription)
    {
        $this->data['DoctorID'] = 0;

        $doctorData = Doctor::query()
            ->where('GMCNO', $prescription['Prescriber']['Doctor']['GMCNO'])
            ->first();

        if (!$doctorData) {
            $this->errors[] = "<span class=\"highlight_red\">********* DOCTOR DOES NOT EXIST OR NOT SUPPLIED **********</span>";
        } else {
            $this->data['DoctorID'] = $doctorData->DoctorID;
            //get latest doctor address ID
            $this->data['DoctorAddressID'] = Doctoraddress::getDoctorAddressID($doctorData->DoctorID) ?? null;

            if ($doctorData->Status != 1) {
                $this->errors[] = "<span class=\"highlight_red\">********* THIS DOCTOR IS INACTIVE **********</span>";
            }

            if ($doctorData->DoctorType == 4) {
                $this->errors[] = "<span class=\"highlight_red\">********* THIS IS A TEST ORDER (PRESCRIBER IS A TEST DOCTOR) **********</span>";
            }
        }
    }

    private function setCountry($patient)
    {
        /** SETUP COUNTRY */
        $deliveryAddress = $patient['DeliveryAddress'];
        $countryCode = $patient['HomeAddress']['CountryCode'];
        $dCountryCode = $deliveryAddress['CountryCode'];

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

        $this->data['CountryCode'] = Country::where('CodeName2', $countryCode)->value('CountryID');
        if (strlen($countryCode) == 2) {
            $this->data['CountryCode'] = Country::where('CodeName2', $countryCode)->value('CountryID');
        } elseif (strlen($countryCode) == 3) {
            $this->data['CountryCode'] = Country::where('CodeName3', $countryCode)->value('CountryID');
        }

        if (strlen($dCountryCode) == 2) {
            $this->data['DCountryCode'] = Country::where('CodeName2', $dCountryCode)->value('CountryID');
        } elseif (strlen($dCountryCode) == 3) {
            $this->data['DCountryCode'] = Country::where('CodeName3', $dCountryCode)->value('CountryID');
        }

        //if france check if Monaco is selected
        if (
            $this->data['DCountryCode'] == "75"
            && (strtoupper($deliveryAddress['AddressLine3']) == "MONACO"
                || (strtoupper($deliveryAddress['AddressLine3']) == "MONACO"))
        ) {
            $this->data['DCountryCode'] = "143";
        }
    }

    private function checkAge($patient)
    {
        $date = (new \DateTime)::createFromFormat('d/m/Y', $patient['DOB']);
        $now = new \DateTime();
        $interval = $now->diff($date);
        $age = $interval->y;

        if ($age < 18) {
            array_push($this->errors, "<span class=\"highlight_red\">********* PATIENT AGE IS UNDER 18 **********</span>");
        }

        if ($age > 89) {
            array_push($this->errors, "<span class=\"highlight_red\">********* PATIENT AGE IS OLDER THAN 89 **********</span>");
        }

        //add gender check
        if (!in_array($patient['Sex'], [1, 2, 3, 4])) {
            array_push($this->errors, "<span class=\"highlight_red\">********* UNKNOWN GENDER **********</span>");
        }
    }

    private function setPharmacy()
    {
        $this->data['PharmacyID'] = Pharmacy::find($this->arr['PharmacyID'])?->PharmacyID;

        if (DB::table('PharmacyBanned')->where('PharmacyID', $this->arr['PharmacyID'])->where('TypeID', $this->data['DCountryCode'])->exists()) {
            throw new OrderException("Country " . $this->data['DCountryCode'] . " is banned for this pharmacy", 500);
        }
    }

    private function checkOrderPayment($prescription)
    {
        if (isset($prescription['COD']) && !empty($prescription['COD'])) {
            $this->data['PaymentMethod'] = strtoupper($prescription['COD']['CashOnly']) == 'Y' ? 1 : 0;

            if ($this->data['PaymentMethod']) {
                $this->data['TokenID'] = $prescription['COD']['Amount'] . '-' . $prescription['COD']['Currency'];
            }
        } else {
            $this->data['PaymentMethod'] = 0;
        }
    }

    private function checkAccessPoint($patient)
    {
        if ($this->data['UPSAccessPointAddress'] == '1' && isset($patient['UPSAccessPointAddress'])) {
            $this->accessPoint = true;
            $this->mapUPSAccessPoint($patient['UPSAccessPointAddress']);
        } else {
            $this->accessPoint = false;
        }
    }

    private function mapUPSAccessPoint($upsAccessPointData)
    {
        $countryID = Country::where('CodeName2', $upsAccessPointData->CountryCode)->value('CountryID');
        $notificationLanguage = Generic::matchLanguageMapping($countryID, false);

        $this->accessPointData = [
            'PrescriptionID' => NULL,
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

    public function insert()
    {
        $this->data['CreatedDate'] = Carbon::now()->timestamp;
        if ($this->childPrescription) {
            return $this;
        }

        //if order exists just update the order
        $exists = Prescription::orderExists($this->data['ReferenceNumber']);
        $message = 'SYSTEM (API RECEIVED)';

        DB::beginTransaction();

        try {
            if ($exists) {
                $this->id = $exists->PrescriptionID;
                $this->data['PrescriptionID'] = $exists->PrescriptionID;
                Prescription::find($this->id)->delete();
                Product::where("PrescriptionID", $this->id)->delete();
                $message = 'SYSTEM (API UPDATED)';
            }
            $this->id = Prescription::insertGetId($this->data);

            if ($this->accessPoint) {
                //add access point data
            }

            Activity::create([
                'UserID' => 0,
                'Name' => $message,
                'OrderID' => $this->id,
                'Action' => 'order received',
                'Date' => Carbon::now()->format('d/m/Y H:i'),
                'Date2' => Carbon::now()->format('Y-m-d'),
                'Min' => date('H:i', floor(time() / (5 * 60)) * (5 * 60)),
                'Hour' => (int) Carbon::now()->format('H'),
                'Type' => 1,
                'Status' => 1,
            ]);
            DB::commit();

            return $this;
        } catch (QueryException $qe) {
            Log::channel('sql')->info($qe->getSql());
            Log::channel('sql')->info(print_r($qe->getBindings(), true));
            DB::rollBack();
            throw $qe;
        } catch (Exception $ex) {
            $this->errors[] = $ex->getMessage();
            DB::rollBack();
            throw $ex;
        }
    }

    public function saveFile($file, $type)
    {
        $ref = $this->data['ReferenceNumber'];
        $time = time();
        $filepath = '';
        $filetype = 'json';
        if ($this->id) {
            $id = $this->id;
        } else if (!$this->id) {
            if ($type == 'application/json') {
                $filepath = "json/CHILD-Ref-$ref--$time.json";
                $filetype = 'json';
            } else {
                $filepath = "xml/CHILD-Ref-$ref--$time.xml";
                $filetype = 'xml';
            }

            array_push($this->errors, 'Child prescription saved');
        }

        if ($type == 'application/json') {
            $filepath = "json/$id-Ref-$ref--$time.json";
            $filetype = 'json';
        } else {
            $filepath = "xml/$id-Ref-$ref--$time.xml";
            $filetype = 'xml';
        }

        PrescriptionFile::create([
            'prescription_id' => $id,
            'file_path' => $filepath,
            'file_type' => $filetype
        ]);
        try {
            Storage::disk('azure')->put($filepath, $file);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

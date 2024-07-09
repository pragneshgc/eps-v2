<?php

namespace App\Models;

use App\Models\Prescriptionhistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prescription extends Model
{
    use HasFactory;

    protected $table = 'Prescription';
    protected $primaryKey = 'PrescriptionID';

    protected $fillable = [
        'DoctorID',
        'GMCNO',
        'DoctorName',
        'ClientID',
        'ReferenceNumber',
        'Email',
        'GUID',
        'TokenID',
        'Title',
        'Name',
        'Middlename',
        'Surname',
        'DOB',
        'Sex',
        'BMI',
        'Address1',
        'Address2',
        'Address3',
        'Address4',
        'Postcode',
        'CountryCode',
        'DAddress1',
        'DAddress2',
        'DAddress3',
        'DAddress4',
        'DPostcode',
        'DCountryCode',
        'Telephone',
        'Mobile',
        'PaymentMethod',
        'Exemption',
        'CreatedDate',
        'Notes',
        'Repeats',
        'Status',
        'SubStatus',
        'JVM',
        'TrackingCode',
        'AirwayBillNumber',
        'PaymentStatus',
        'DeliveryID',
        'UpdatedDate',
        'UserID',
        'Message',
        'SaturdayDelivery',
        'UPSAccessPointAddress',
        'TrackingSent',
        'CSNotes',
        'DoctorAddressID',
        'Company',
        'CustomerID'
    ];

    protected $casts = [
        'PrescriptionID' => 'int',
        'DoctorID' => 'int',
        'GMCNO' => 'string',
        'DoctorName' => 'string',
        'ClientID' => 'int',
        'ReferenceNumber' => 'string',
        'Email' => 'string',
        'GUID' => 'string',
        'TokenID' => 'string',
        'Title' => 'string',
        'Name' => 'string',
        'Middlename' => 'string',
        'Surname' => 'string',
        'DOB' => 'string',
        'Sex' => 'string',
        'BMI' => 'float',
        'Address1' => 'string',
        'Address2' => 'string',
        'Address3' => 'string',
        'Address4' => 'string',
        'Postcode' => 'string',
        'CountryCode' => 'int',
        'DAddress1' => 'string',
        'DAddress2' => 'string',
        'DAddress3' => 'string',
        'DAddress4' => 'string',
        'DPostcode' => 'string',
        'DCountryCode' => 'int',
        'Telephone' => 'string',
        'Mobile' => 'string',
        'PaymentMethod' => 'int',
        'Exemption' => 'int',
        'CreatedDate' => 'int',
        'Notes' => 'string',
        'Repeats' => 'string',
        //'Status' => 'int',
        'SubStatus' => 'int',
        'JVM' => 'int',
        'TrackingCode' => 'string',
        'AirwayBillNumber' => 'string',
        'PaymentStatus' => 'int',
        'DeliveryID' => 'string',
        'UpdatedDate' => 'int',
        'UserID' => 'int',
        'Message' => 'string',
        'SaturdayDelivery' => 'int',
        'UPSAccessPointAddress' => 'int',
        'TrackingSent' => 'int',
        'CSNotes' => 'string',
        'DoctorAddressID' => 'int',
        'Company' => 'string',
        'CustomerID' => 'int'
    ];

    public $timestamps = false;

    public function scopeStatus(Builder $query, int $status): void
    {
        $query->where('status', $status);
    }

    public function scopeSubstatus(Builder $query, int $status): void
    {
        $query->where('SubStatus', $status);
    }

    // Functions ...
    public static function countTodaysDeliveries(int $countryCode, int $deliveryId): int
    {
        return self::where(['DCountryCode' => $countryCode, 'DeliveryID' => $deliveryId])
            ->where('CreatedDate', '>', strtotime("today"))
            ->where('CreatedDate', '<', (strtotime("tomorrow") - 1))
            ->count();
    }

    public static function orderExists(string $referenceNumber): self|null
    {
        return self::select('PrescriptionID', 'Status')
            ->where('ReferenceNumber', $referenceNumber)
            ->first();
    }

    public static function updateStatus(int $id, int $status, ?int $substatus = NULL, bool $wipeTracking = false): int
    {
        $update = [
            'Status' => $status,
            'SubStatus' => $substatus,
            'UpdatedDate' => time()
        ];

        if (in_array($status, [8, 3, 6, 12])) {
            $update['Email'] = '';
            $update['Telephone'] = '';
            $update['Mobile'] = '';
        }

        if ($wipeTracking) {
            $update['TrackingCode'] = '';
        }

        return self::where('PrescriptionID', $id)->update($update);
    }

    public static function updateMessage(int $id, string $message): int
    {
        return self::where('PrescriptionID', $id)
            ->update([
                'Message' => $message
            ]);
    }
}

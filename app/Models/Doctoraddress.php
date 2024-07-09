<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctoraddress extends Model
{
    use HasFactory;
    protected $table = 'DoctorAddress';
    protected $primaryKey = 'DoctorAddressID';

    protected $fillable = [
        'DoctorID',
        'Title',
        'CompanyName',
        'Name',
        'Surname',
        'Address1',
        'Address2',
        'Address3',
        'Address4',
        'Postcode',
        'CountryID',
        'Telephone',
        'Mobile',
        'Email',
        'CreatedDate',
        'ModifiedDate',
        'AccessedDate',
        'Status',
        'Notes',
        'GMCNO',
        'MedicalInsuranceNo',
        'Password',
        'Username',
        'DoctorType',
        'Type',
        'ParentID'
    ];

    protected $casts = [
        'DoctorAddressID' => 'int',
        'DoctorID' => 'int',
        'Title' => 'string',
        'CompanyName' => 'string',
        'Name' => 'string',
        'Surname' => 'string',
        'Address1' => 'string',
        'Address2' => 'string',
        'Address3' => 'string',
        'Address4' => 'string',
        'Postcode' => 'string',
        'CountryID' => 'int',
        'Telephone' => 'string',
        'Mobile' => 'string',
        'Email' => 'string',
        'CreatedDate' => 'int',
        'ModifiedDate' => 'int',
        'AccessedDate' => 'int',
        'Status' => 'int',
        'Notes' => 'string',
        'GMCNO' => 'string',
        'MedicalInsuranceNo' => 'string',
        'Password' => 'string',
        'Username' => 'string',
        'DoctorType' => 'int',
        'Type' => 'int',
        'ParentID' => 'int'
    ];

    public $timestamps = false;

    public static function getDoctorAddressID(int $id): mixed
    {
        return self::where('DoctorID', $id)
            ->where('Status', 1)
            ->orderBy('DoctorAddressID', 'DESC')
            ->value('DoctorAddressID');
    }
}

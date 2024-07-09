<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $table = 'Doctor';
    protected $primaryKey = 'DoctorID';

    protected $fillable = [
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
        'ParentID'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
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
        'ParentID' => 'int'
    ];

    public $timestamps = false;
}

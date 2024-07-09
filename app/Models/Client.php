<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table = 'Client';
    protected $primaryKey = 'ClientID';
    public $timestamps = false;

    protected $fillable = [
        'CompanyName',
        'Title',
        'Name',
        'Middlename',
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
        'CreditLimit',
        'CreatedDate',
        'ModifiedDate',
        'AccessedDate',
        'IP',
        'Type',
        'Status',
        'Notes',
        'CompanyNumber',
        'GPHCNO',
        'ReturnURL',
        'Username',
        'Password',
        'APIKey',
        'ITName',
        'ITEmail',
        'TradingName',
        'AdditionalComment',
        'ReturnUsername',
        'ReturnPassword',
        'VAT'
    ];

    protected $casts = [
        'ClientID' => 'int',
        'CompanyName' => 'string',
        'Title' => 'string',
        'Name' => 'string',
        'Middlename' => 'string',
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
        'CreditLimit' => 'float',
        'CreatedDate' => 'int',
        'ModifiedDate' => 'int',
        'AccessedDate' => 'int',
        'IP' => 'string',
        'Type' => 'int',
        'Status' => 'int',
        'Notes' => 'string',
        'CompanyNumber' => 'string',
        'GPHCNO' => 'string',
        'ReturnURL' => 'string',
        'Username' => 'string',
        'Password' => 'string',
        'APIKey' => 'string',
        'ITName' => 'string',
        'ITEmail' => 'string',
        'TradingName' => 'string',
        'AdditionalComment' => 'string',
        'ReturnUsername' => 'string',
        'ReturnPassword' => 'string',
        'VAT' => 'float'
    ];
}

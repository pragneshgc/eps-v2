<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    use HasFactory;

    protected $table = 'pharmacy';

    protected $primaryKey = 'PharmacyID';

    protected $fillable = [
        'Title',
        'Location',
        'AccountNumber',
        'BillingAccountNumber',
        'ShipperName',
        'VATNumber',
        'EORI',
        'Telephone',
        'Email',
        'Address1',
        'Address2',
        'Address3',
        'Address4',
        'Postcode',
        'Contents',
        'CountryCode',
        'Status',
        'CreatedAt',
        'UpdatedAt',
        'DeletedAt',
    ];
    public $timestamps = false;
}

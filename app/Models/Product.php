<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'Product';
    protected $primaryKey = 'ProductID';
    protected $fillable = [
        'PrescriptionID',
        'GUID',
        'Code',
        'Description',
        'Instructions',
        'Instructions2',
        'Quantity',
        'Unit',
        'Dosage',
    ];
    protected $casts = [
        'ProductID' => 'int',
        'PrescriptionID' => 'int',
        'GUID' => 'string',
        'Code' => 'string',
        'Description' => 'string',
        'Instructions' => 'string',
        'Instructions2' => 'string',
        'Quantity' => 'int',
        'Unit' => 'string',
        'Dosage' => 'int'
    ];
    public $timestamps = false;
}

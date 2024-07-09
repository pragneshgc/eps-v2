<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $table = 'Activity';
    protected $primaryKey = 'ActivityID';
    protected $fillable = [
        'Arguments',
        'UserID',
        'Name',
        'OrderID',
        'Date',
        'Action',
        'Min',
        'Hour',
        'Date2',
        'Status',
        'Type'
    ];
    protected $casts = [
        'ActivityID' => 'int',
        'UserID' => 'int',
        'Name' => 'string',
        'OrderID' => 'int',
        'Date' => 'string',
        'Action' => 'string',
        'Arguments' => 'string',
        'Type' => 'int',
        'Status' => 'int',
        'Date2' => 'string',
        'Hour' => 'int',
        'Min' => 'string',
    ];
    public $timestamps = false;
}

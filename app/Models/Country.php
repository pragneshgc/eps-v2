<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $CountryID
 * @property int    $RegionID
 * @property int    $Status
 * @property string $Name
 * @property string $CodeName2
 * @property string $CodeName3
 * @property float  $Digital
 * @property float  $Physical
 */
class Country extends Model
{
    use HasFactory;
    protected $table = 'Country';
    protected $primaryKey = 'CountryID';
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'Name',
        'RegionID',
        'Status',
        'CodeName2',
        'CodeName3',
        'Digital',
        'Physical'
    ];

    public $timestamps = false;
}

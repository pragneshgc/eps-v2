<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionFile extends Model
{
    use HasFactory;
    protected $fillable = ['prescription_id', 'file_path', 'file_type', 'courier_logs_deleted_at', 'import_logs_deleted_at'];
}

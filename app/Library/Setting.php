<?php

namespace App\Library;

use Illuminate\Support\Facades\DB;
// use Carbon\Carbon;

/**
 * Undocumented class
 */
class Setting
{
    public function getSetting($id)
    {
        return DB::table('Setting')->where('SettingID', $id)->first();
    }
}

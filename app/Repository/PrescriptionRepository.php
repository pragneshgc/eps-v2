<?php

namespace App\Repository;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PrescriptionRepository
{
    /**
     * Find Shipped Order that past by $days
     *
     * @param integer $days
     * @return Collection
     */
    public function findCourierLogsByDay(int $days): Collection
    {
        $currentTimestamp = Carbon::now()
            ->setHour(0)
            ->setMinute(0)
            ->setSecond(0)
            ->subDays($days)
            ->timestamp;
        return DB::table('prescription as p')
            ->join('prescription_files as pf', 'pf.prescription_id', '=', 'p.PrescriptionID')
            ->select('p.PrescriptionID', 'pf.id')
            ->where('p.Status', 8)
            ->where('p.UpdatedDate', '<', $currentTimestamp)
            ->whereNull('pf.courier_logs_deleted_at')
            ->get();
    }

    public function findImportedLogsByDay(int $days): Collection
    {
        $currentTimestamp = Carbon::now()
            ->setHour(0)
            ->setMinute(0)
            ->setSecond(0)
            ->subDays($days)
            ->timestamp;
        return DB::table('prescription as p')
            ->join('prescription_files as pf', 'pf.prescription_id', '=', 'p.PrescriptionID')
            ->select('p.PrescriptionID', 'pf.id', 'pf.file_path')
            ->where('p.Status', 8)
            ->where('p.UpdatedDate', '<', $currentTimestamp)
            ->whereNull('pf.import_logs_deleted_at')
            ->get();
    }
}

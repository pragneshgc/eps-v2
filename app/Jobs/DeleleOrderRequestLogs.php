<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\PrescriptionFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Repository\PrescriptionRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleleOrderRequestLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $prescriptionRepo = new PrescriptionRepository();
        $orders = $prescriptionRepo->findImportedLogsByDay(45);

        if (!empty($orders)) {
            foreach ($orders as $order) {
                Storage::disk('azure')->delete($order->file_path);

                PrescriptionFile::find($order->id)
                    ->update(['import_logs_deleted_at' => Carbon::now()]);
            }
        }
    }
}

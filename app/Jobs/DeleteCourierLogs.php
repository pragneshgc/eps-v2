<?php

namespace App\Jobs;

use App\Models\PrescriptionFile;
use App\Repository\PrescriptionRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteCourierLogs implements ShouldQueue
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
        $orders = $prescriptionRepo->findCourierLogsByDay(30);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                Storage::disk('azure')->delete("dhl_labels/$order->PrescriptionID.zpl");
                Storage::disk('azure')->delete("dhl_xml/validation-response-$order->PrescriptionID.xml");
                Storage::disk('azure')->delete("dhl_xml/validation-request-$order->PrescriptionID.xml");
                Storage::disk('azure')->delete("ups_xml/tracking-code-send-$order->PrescriptionID.xml");

                PrescriptionFile::find($order->id)
                    ->update(['courier_logs_deleted_at' => Carbon::now()]);
            }
        }
    }
}

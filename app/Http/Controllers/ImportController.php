<?php

namespace App\Http\Controllers;

use App\Helpers\Generic;
use App\Library\Order;
// use App\Library\Questionnaire;
use App\Library\Product;
// use App\Library\TestKit;
use Illuminate\Http\Request;
use App\Library\Prescription;
use Illuminate\Http\JsonResponse;
// use App\Services\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Routine for importing prescription XML's
     *
     * @param string $file
     * @return (string|false)[]|(void|array|false)[]
     */
    public function importRoutine($file, $xml = false)
    {
        if (!$xml) {
            if (substr($file, 0, 5) != "<?xml") {
                return [
                    'errors' => ['Malformed XML or not an XML file'],
                    'id' => false
                ];
            }

            $xml = simplexml_load_string($file);
        }

        $prescription = new Prescription($xml); //create the prescription object
        $product = new Product();
        $order = new Order();
        $errors = [];

        try {
            //run import routines and collect errors as you go along
            $prescriptionErrors = $prescription
                ->validate()
                ->mapPrescription()
                ->insert()
                ->saveXml(false, $prescription->getField('ReferenceNumber'), $file)
                ->getErrors();

            if ($prescription->exists) {
                $product->removeByOrderId($prescription->exists->PrescriptionID);
            }

            if (!$prescription->childPrescription) {
                $productErrors = $product->importProductFromXML($prescription->id, $xml->Prescription);
            }

            //prescription generation
            $errors = array_merge(
                isset($productErrors) ? $productErrors : [],
                isset($prescriptionErrors) ? $prescriptionErrors : [],
                // isset($questionnaireErrors) ? $questionnaireErrors : []
            );
        } catch (\Exception $e) {
            //In case of a thrown exception we still want some logs of what happened
            $order->updateOrderStatus($prescription->id, 7);
            $order->updateOrderMessage($prescription->id, $e->getMessage());

            array_push($errors, $e->getMessage());
        }

        if (count($errors) > 0) {
            $order->updateOrderStatus($prescription->id, 7);
            $order->updateOrderMessage($prescription->id, implode(', ', $errors));
        }

        //generate PDF
        // $pdf = new Pdf;
        // $id = $prescription->id;
        // $origin = url('/');
        // $pdf->render($id, "$origin/prescription/$id/view/html?token=TpgfcEjr82pQKaE2dMtsRNIwhMuTyFNt");

        return [
            'errors' => $errors,
            'id' => $prescription->id,
        ];
    }

    /**
     * Import XML file manually by sending an XML file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function manual(Request $request)
    {
        $file = File::get($request->file('file')); // get the XML

        $response = $this->importRoutine($file);

        //get errors from importing prescription,questionnaire and product
        if (count($response['errors']) > 0) {
            return $this->sendError('Import not finished properly, or finished partially', $response['errors']);
        }

        $id = $response['id'];

        return $this->sendResponse($id, "XML successfully uploaded. Inserted prescription with id <a href='#/prescription/$id'> $id </a>");
    }

    /**
     * Import routine used by clients
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function client(Request $request)
    {
        $file = $request->getContent();
        $ipaddress = Generic::getIP();

        Log::channel('import')->info("Import Started from $ipaddress");
        Log::channel('import')->info($file);

        if (!$request->has('receivePrescription')) {
            Log::channel('import')->info('Import error: Not Allowed');
            return $this->sendError('Not Allowed', '', 400);
        }

        if (!$request->filled('USERNAME') || !$request->filled('PASSWORD') || !$request->filled('KEY')) {
            Log::channel('import')->info('Import error: Missing parameters');
            return $this->sendError('Missing parameters', '', 400);
        }

        $username = $request->USERNAME;
        $password = $request->PASSWORD;
        $key = $request->KEY;

        if (!DB::table('Client')->where('APIKey', $key)->where('Username', $username)->where('Password', $password)->exists()) {
            Log::channel('import')->info('Import error: Unauthorized');
            Log::channel('import')->info("Using following details: $key , $username , $password");

            return $this->sendError('Unauthorized', '', 401);
        }

        if (!$ipaddress) {
            Log::channel('import')->info('Import error: Invalid Request');
            return $this->sendError('Invalid Request', '', 400);
        }

        //add ip check here
        if (!DB::table('Client')->where('APIKey', $key)->where('Username', $username)->where('Password', $password)->where('IP', 'LIKE', '%' . $ipaddress . '%')->exists()) {
            Log::channel('import')->info('Import error: Unauthorized');
            Log::channel('import')->info("Using following details: $key , $username , $password with unauthorized ip: $ipaddress");
            return $this->sendError('Unauthorized', '', 401);
        }

        //store xml here

        $response = $this->importRoutine($file);

        if (count($response['errors']) > 0) {
            //xml response
            return $this->sendResponse($response['errors'], 'Prescription recieved partially with errors');
            // return $this->sendError('Prescription recieved partially with errors', $response['errors'], 202);
        }

        $id = $response['id'];

        Log::channel('import')->info("Import finished for $id");

        //xml response
        return $this->sendResponse($id, "Prescription successfully received and validated");
    }
}

<?php

namespace App\Http\Controllers;

use App\Library\Order;
use App\Library\Activity;
use App\Library\Tracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    private $activity;
    private $order;

    private $tracking;

    private $deliveryCompanies = [
        '0' => 'Undefined',
        '3' => 'TNT',
        '4' => 'DPD',
        '5' => 'Royal Mail',
        '7' => 'UPS',
        '8' => 'TNT',
        '10' => 'DHL',
    ];

    /**
     * Order statuses mapping
     *
     * @var array
     */
    private $orderStatuses = [
        '1' => 'NEW',
        '2' => 'APPROVED',
        '4' => 'QUERIED',
        '3' => 'REJECTED',
        '5' => 'POSTPONED',
        '6' => 'CANCELLED',
        '7' => 'AWAITINGSHIPPED',
        '8' => 'SHIPPED',
        '9' => 'SAFETYCHECK',
        '10' => 'ONHOLD',
        '11' => 'CALL',
        '12' => 'QUERIEDDISPENSED',
        '13' => 'QUERIEDNOTDISPENSED',
        '14' => 'QUERIEDNOREPLY',
        '15' => 'QUERIEDARCHIVED',
        '16' => 'RETURNED',
    ];

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->activity = new Activity;
        $this->order = new Order;
        $this->tracking = new Tracking;
    }

    /**
     * Reset the label
     *
     * @param int $id
     * @return boolean
     */
    public function reset($id)
    {
        return $this->order->updateOrder($id, '', '', 7);
    }

    /**
     * Get activity list for order with ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getActivity($id)
    {
        $data = DB::table('Activity')->select(['ActivityID', 'Date', 'Action', 'Name', 'Type', 'Arguments', 'Status'])
            ->where('OrderID', $id)
            ->whereRaw("(Action LIKE '%print label%'
        OR Action LIKE '%request label%'
        OR Action LIKE '%System unresponsive%'
        OR Action LIKE '%Damaged label%'
        OR Action LIKE '%Label applied incorrectly%'
        OR Action LIKE '%Order changed to SHIPPED%'
        OR Action LIKE '%PDF prescription successfully submitted to courrier%'
        OR Action LIKE '%manual print%')")
            ->orderBy('ActivityID', 'DESC')->get();

        return $this->sendResponse($data, 'Activity log for order ' . $id);
    }

    /**
     * Show root page
     *
     * @return JsonResponse
     */
    public function details($id)
    {
        $user = Auth::user();

        $data = DB::table('Prescription')->where('ReferenceNumber', $id);

        if ($user->pharmacy_id != 1) {
            $data = $data->where('PharmacyID', $user->pharmacy_id);
        }

        $data = $data->first();

        if ($data) {
            $products = DB::table('Product AS p')
                ->selectRaw("p.Description as Description, pc.Name, p.Code, p.Quantity, p.Dosage, p.Instructions, p.Unit, p.ProductID, pc.ProductCodeID, pc.Units")
                ->selectRaw("pc.Quantity AS 'SingleProductDosage'")
                // ->selectRaw("(SELECT ii.GTIN FROM InventoryItem ii WHERE pc.ProductCodeID = ii.ProductCodeID LIMIT 1) AS SuggestedBarcode")
                // ->selectRaw("(SELECT COUNT(ii.InventoryItemID) FROM InventoryItem ii WHERE ii.ProductCodeID = pc.ProductCodeID AND ii.ProductID IS NULL) AS StoredProducts")
                ->leftJoin('ProductCode AS pc', 'p.Code', '=', 'pc.Code')
                ->where('p.PrescriptionID', '=', $data->PrescriptionID)->get();

            $data->Products = $products;

            // $notshipped = 0;

            // foreach ($products as $product) {
            //     $product->AttachedProducts = DB::table('InventoryItem')->where('ProductID', $product->ProductID)->where('Status', '!=', 'DESTROYED')
            //         ->orderBy('Quantity', 'DESC')->get();

            //     // Check if the order is already completed
            //     if (!$product->AttachedProducts->isEmpty()) {
            //         foreach ($product->AttachedProducts as $attached) {
            //             if ($attached->Status != 'SHIPPED') {
            //                 $notshipped++;
            //             }
            //         }
            //     } else {
            //         $notshipped++;
            //     }
            // }

            // if ($notshipped > 0) {
            //     $data->Decomissioned = false; // not decomissioned
            // } else {
            //     $data->Decomissioned = true; // decomissioned
            // }

            $data->Decomissioned = true; // decomissioned

            // $data->Printed = $this->activity->alreadyPrinted($id);
            $data->Printed = true;
        } else {
            return $this->sendError('Order not found!');
        }

        return $this->sendResponse($data, 'Prescription details');
    }

    /**
     * Check if the authorization code is matched to a code in the Authorization Code table
     * Or check if there is a user with the code matched in the Authorization Code table
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAuthorizationCode(Request $request)
    {
        $code = $request->input()['code'];
        $response = DB::table('AuthorizationCode')->whereNull('DeletedAt')->where('Code', $code)->first();

        if ($response) {
            return $this->sendResponse('', 'validated');
        }

        $authorizedusers = DB::table('AuthorizationCode')->select('UserID')->whereNull('DeletedAt')->groupBy('UserID')->get();

        if ($authorizedusers) {
            $authorizedusers = $authorizedusers->toArray();
        }

        $response = DB::table('PxpUser')->where('Code', $code)->first();

        if ($response) {
            foreach ($authorizedusers as $user) {
                if ($response->id == $user->UserID) {
                    return $this->sendResponse('', 'validated');
                }
            }
        }

        return $this->sendError('Invalid code');
    }

    /**
     * Search orders
     *
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $data = $this->order->orderActivities();
        $data = $this->order->setSearchParameters($this->f, $request, $data);

        if ($this->q != '') {
            $data = $data->where('p.PrescriptionID', 'LIKE', "%$this->q%");
        }

        if ($this->s != '') {
            $data = $data->orderBy($this->s, $this->o);
        } else {
            $data = $data->orderBy('a.Date', 'DESC');
        }

        $data = $data->paginate($this->l);

        //get id's of items
        $codes = [];
        foreach ($data->items() as $item) {
            $args = json_decode($item->Arguments);

            if (isset($args->code)) {
                array_push($codes, $args->code);
            }
        }

        $users = $this->order->users($codes);

        $data->getCollection()->transform(function ($item) use ($users) {
            $item->User = '';

            $args = json_decode($item->Arguments);

            if (isset($args->code)) {
                $code = $args->code;

                foreach ($users as $user) {
                    if ($user->code == $code) {
                        $item->User = $user->title;
                    }
                }
            }

            return $item;
        });

        return $this->sendResponse($data, 'Successfull query');
    }

    public function orders(Request $request)
    {
        $data = $this->order->order();
        $data = $this->order->setSearchParameters($this->f, $request, $data);

        if ($this->q != '') {
            $data = $data->where('Prescription.ReferenceNuber', 'LIKE', "%$this->q%");
        }

        if ($this->s != '') {
            $data = $data->orderBy($this->s, $this->o);
        } else {
            $data = $data->orderBy('Prescription.CreatedDate', 'DESC');
        }

        $data = $data->paginate($this->l);

        $data->getCollection()->transform(function ($item) {
            $item->Status = $this->orderStatuses[$item->Status];

            return $item;
        });

        return $this->sendResponse($data, 'Successfull query');
    }

    public function cancelOrder($id)
    {
        $data = DB::table('Prescription')->where('ReferenceNumber', $id)->update([
            'Status' => 6,
        ]);

        return $this->sendResponse($data, 'Successfull query');
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function hourly(Request $request)
    {
        // $filters = json_decode($this->f);
        // $strict = json_decode($request->strict);
        // $operator = $strict ? '=' : 'LIKE';

        $data = DB::table('Activity AS a')->selectRaw("Date2")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a1 WHERE a1.Date2 = a.Date2 AND Hour = 8 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '8'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a2 WHERE a2.Date2 = a.Date2 AND Hour = 9 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '9'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a3 WHERE a3.Date2 = a.Date2 AND Hour = 10 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '10'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a4 WHERE a4.Date2 = a.Date2 AND Hour = 11 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '11'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a5 WHERE a5.Date2 = a.Date2 AND Hour = 12 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '12'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a6 WHERE a6.Date2 = a.Date2 AND Hour = 13 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '13'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a7 WHERE a7.Date2 = a.Date2 AND Hour = 14 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '14'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a8 WHERE a8.Date2 = a.Date2 AND Hour = 15 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '15'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a9 WHERE a9.Date2 = a.Date2 AND Hour = 16 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '16'")
            ->selectRaw("(SELECT COUNT(*) FROM Activity a10 WHERE a10.Date2 = a.Date2 AND Hour = 17 AND (Action LIKE '%manual print%' OR Action LIKE '%print label%' OR Action LIKE '%System unresponsive%')) AS '17'")
            ->whereRaw("(Action LIKE '%manual print%'
        OR Action LIKE '%print label%'
        OR Action LIKE '%System unresponsive%')")
            ->whereRaw("Date2 BETWEEN (NOW() - INTERVAL 30 DAY) AND NOW()")
            ->groupBy(['Date2'])->get();

        return $this->sendResponse($data, 'Successfull query');
    }

    /**
     * Stream a CSV with orders for the register
     *
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function csv(Request $request)
    {
        $headers = array(
            "Content-Type" => "data:text/csv;charset=utf-8,\uFEFF",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $data = $this->order->orderActivities(true);
        $data = $this->order->setSearchParameters($this->f, $request, $data);

        if ($this->q != '') {
            $data = $data->where('Prescription.PrescriptionID', 'LIKE', "%$this->q%");
        }

        if ($this->s != '') {
            $data = $data->orderBy($this->s, $this->o);
        } else {
            $data = $data->orderBy('Prescription.UpdatedDate', 'DESC');
        }

        $data = $data->paginate($this->l);

        //get id's of items
        $codes = [];
        foreach ($data->items() as $item) {
            $args = json_decode($item->Arguments);

            if (isset($args->code)) {
                array_push($codes, $args->code);
            }
        }

        $users = $this->order->users($codes);

        $data->getCollection()->transform(function ($item) use ($users) {
            $item->User = '';

            $args = json_decode($item->Arguments);

            if (isset($args->code)) {
                $code = $args->code;

                foreach ($users as $user) {
                    if ($user->code == $code) {
                        $item->User = $user->title;
                    }
                }
            }

            return $item;
        });
        $columns = ['ID', 'Reference Number', 'Date', 'Patient Name', 'Patient Address', 'User'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF); // this allows us to show pound signs. Why? No idea

            fputcsv($file, $columns, ',');

            $i = 0;
            foreach ($data as $order) {
                fputcsv($file, [
                    $order->PrescriptionID,
                    $order->ReferenceNumber,
                    $order->Date,
                    $order->{'Patient Name'},
                    $order->{'Patient Address'},
                    $order->User,
                ], ',');
                $i++;
            }

            fclose($file);
        };
        return response()->streamDownload($callback, 'orders-' . date('d-m-Y-H:i:s') . '.csv', $headers);
    }

    /**
     * Get list of delivery companies
     *
     * @return JsonResponse
     */
    public function deliveryCompanies()
    {
        return $this->sendResponse($this->order->getDeliveryCompanies(), 'delivery companies list');
    }

    /**
     * Import tracking
     * what to do when imported?
     *
     * @param Request $request
     */
    public function importTracking(Request $request)
    {
        $file = File::get($request->file('file')); //->store('files');
        $file = str_replace(' ', '', $file);
        $file = str_replace('"', '', $file);
        $companyID = $request->option;
        $fileArray = explode(PHP_EOL, $file);
        $statuses = [];

        //$time = microtime(true);
        foreach ($fileArray as $line) {
            $data = explode(',', $line);
            $refPre = explode('-', $data[0]);

            //skip empty lines
            if (!isset($refPre[1])) {
                continue;
            }

            $order = $this->order->getOrderDetails($refPre[1]);

            if ($order && $order->Status == 7 && $data[1] != '') {
                if ($companyID == 7 && $data[2] == 'Y') {
                    //we might need to cancel the order here
                    //to be decided
                    $statuses[$refPre[1]] = 'Not shipped';
                    continue;
                } else {
                    $this->order->updateTrackingCode($data[1], $refPre[1]); //$refPre[1] is PrescriptionID
                    $this->tracking->sendTracking($refPre[1]);
                    $this->activity->log($refPre[1], 'Order changed to SHIPPED');
                    $statuses[$refPre[1]] = 'Shipped';
                }
            } else if ($order->Status == 8) {
                $statuses[$refPre[1]] = 'Order already shipped';
            } else {
                $statuses[$refPre[1]] = 'Not shipped';
            }
        }

        return $this->sendResponse($statuses, 'Successfully imported');
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return void
     */
    public function editAddress($id)
    {
        $order = DB::table('Prescription')->select(
            [
                /*'Repeats',*/
                'Name',
                'Surname',
                'DAddress1',
                'DAddress2',
                'DAddress3',
                'DAddress4',
                'DPostcode',
                'DCountryCode',
                'Address1',
                'Address2',
                'Address3',
                'Address4',
                'Postcode',
                'CountryCode',
                'Telephone',
                'Email',
                'Notes' /*,'TokenID*/,
                'TrackingCode',
                'DeliveryID'
            ]
        )->where('PrescriptionID', $id)->first();

        if (!in_array($order->DCountryCode, ['143', '162', '205', '243'])) {
            unset($order->Repeats);
        }

        // $ups = DB::table('UPSAccessPoint')->select(
        //     ['Name','Address1','Address2','Address3','Address4', 'CountryCode', 'Postcode','APNotificationValue']
        // )->where('PrescriptionID', $id)->first();

        $ups = null;

        return $this->sendResponse([
            'order' => $order,
            'oldOrder' => $order,
            'ups' => $ups,
            'oldUPS' => $ups,
        ], 'Order details');
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @param Request $request
     */
    public function updateAddress($id, Request $request)
    {
        $data = $request->input();
        $order = $data['order'];
        $ups = $data['ups'];

        $oldOrder = DB::table('Prescription')->select(
            [
                'Repeats',
                'Name',
                'Surname',
                'DAddress1',
                'DAddress2',
                'DAddress3',
                'DAddress4',
                'DPostcode',
                'DCountryCode',
                'Address1',
                'Address2',
                'Address3',
                'Address4',
                'Postcode',
                'CountryCode',
                'Telephone',
                'Email',
                'Notes',
                'TokenID',
                'TrackingCode',
                'DeliveryID'
            ]
        )->where('PrescriptionID', $id)->first();

        if (!is_null($order)) {
            DB::table('Prescription')->where('PrescriptionID', $id)->update($order);
        }

        // $oldUPS = DB::table('UPSAccessPoint')->select(
        //     ['Name', 'Address1', 'Address2', 'Address3', 'Address4', 'CountryCode', 'Postcode', 'APNotificationValue']
        // )->where('PrescriptionID', $id)->first();

        // if(!is_null($ups)){
        //     DB::table('UPSAccessPoint')->where('PrescriptionID', $id)->update($ups);
        // } else {
        $oldUPS = [];
        // }

        $update = [
            'oldOrder' => $oldOrder,
            'oldUPS' => $oldUPS
        ];

        $this->activity->log($id, 'Updated order details', json_encode($update), 751); //type 751 for update on pxp

        return $this->sendResponse($order, 'Updated successfully');
    }

    /**
     * Check if all the update details on the order are correct
     * Return differences in update details for confirmation
     *
     * @param int $id
     * @param Request $request
     */
    public function checkUpdateDetail($id, Request $request)
    {
        $data = $request->input();
        $order = $data['order'];
        $ups = $data['ups'];

        $oldOrder = DB::table('Prescription')->select(
            [
                'Repeats',
                'Name',
                'Surname',
                'DAddress1',
                'DAddress2',
                'DAddress3',
                'DAddress4',
                'DPostcode',
                'DCountryCode',
                'Address1',
                'Address2',
                'Address3',
                'Address4',
                'Postcode',
                'CountryCode',
                'Telephone',
                'Email',
                'Notes',
                'TokenID',
                'TrackingCode',
                'DeliveryID'
            ]
        )->where('PrescriptionID', $id)->first();

        if (!$oldOrder) {
            $oldOrder = [];
        }

        if (!in_array($oldOrder->DCountryCode, ['143', '162', '205', '243'])) {
            unset($oldOrder->Repeats);
        }

        // $oldUPS = DB::table('UPSAccessPoint')->select(
        //     ['Name', 'Address1', 'Address2', 'Address3', 'Address4', 'CountryCode', 'Postcode', 'APNotificationValue']
        // )->where('PrescriptionID', $id)->first();

        // if (!$oldUPS) {
        $oldUPS = [];
        // }

        // if ($ups) {
        //     $changesUPS = array_diff($ups, (array) $oldUPS);
        // } else {
        $changesUPS = [];
        // }

        $changes = array_diff_assoc((array) $order, (array) $oldOrder);

        return $this->sendResponse(['changes' => $changes, 'old' => (array) $oldOrder, 'changesUPS' => $changesUPS, 'oldUPS' => (array) $oldUPS], 'List of changed');
    }


    /**
     * Undocumented function
     *
     * @param Request $request
     */
    public function logReprint(Request $request)
    {
        $data = $request->input();
        $this->activity->log($data['orderID'], $data['reason'], ['code' => $data['code']], 60); //60 is reprint with reason

        return $this->sendResponse([], 'Success');
    }

    /**
     * Check if a document exists for this order
     *
     * @param int $id
     */
    public function checkDocument($id)
    {
        try {
            $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.pdf');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $th) {
            return $this->sendResponse(false, 'Document not found');
        }

        return $this->sendResponse(true, 'Document found');
    }

    /**
     * Download the document related to this order
     *
     * @param int $id
     * @return void
     */
    public function downloadDocument($id)
    {
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=$id.pdf");
        $file = Storage::disk('azure')->get('dhl_labels/' . $id . '.pdf');
        echo $file;
        die();
    }
}

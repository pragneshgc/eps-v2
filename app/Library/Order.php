<?php

namespace App\Library;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// use Carbon\Carbon;

/**
 * Helper class for getting and filling order data
 */
class Order
{
    /**
     * Get order details from the prescription and country table
     * including the country name
     * @param int $id
     * @return object
     */
    public function getOrderDetails($id)
    {
        return DB::table('Prescription AS p')->selectRaw("p.*, c.CodeName2 AS 'CountryCodeName', c.Name AS 'CountryName', c.RegionID")
            ->where('PrescriptionID', $id)->leftJoin('Country AS c', 'p.DCountryCode', '=', 'c.CountryID')->first();
    }

    /**
     * Receives PharmacyID and returns Pharmacy details
     *
     * @param int $id
     * @return object
     */
    public function getPharmacyDetails($id, $clientId)
    {
        $fields = [
            'p.PharmacyID', 'p.Title', 'p.Location', 'p.Telephone', 'p.Email', 'p.Address1', 'p.Address2',
            'p.Address3', 'p.Address4', 'p.Postcode', 'p.Contents', 'p.CountryCode', 'p.Status', 'p.CreatedAt',
            'pd.AccountNumber', 'pd.BillingAccountNumber', 'pd.VATNumber', 'pd.EORI', 'pd.ShipperName'
        ];

        $pharmacy = DB::table('Pharmacy AS p')->select($fields)
            ->selectRaw("c.CodeName2 AS 'CountryCodeName', c.Name AS 'CountryName', c.RegionID")
            ->leftJoin('Country AS c', 'p.CountryCode', '=', 'c.CountryID')
            ->leftJoin('PharmacyDetail as pd', 'p.PharmacyID', '=', 'pd.PharmacyID')
            ->where('pd.ClientID', $clientId)
            ->where('p.PharmacyID', $id)
            ->first();

        return $pharmacy;
    }

    /**
     * Undocumented function
     *
     * @param int $id
     */
    public function setToImportAwaiting($id)
    {
        return DB::table('Prescription')->where('PrescriptionID', $id)->update(['Exemption' => 3]);
    }

    /**
     * Get first product from the product table by prescription id
     *
     * @param int $id
     * @return array
     */
    public function getProduct($id)
    {
        return DB::table('Product AS p')->selectRaw("p.*")->where('PrescriptionID', $id)->first();
    }

    /**
     * Undocumented function
     *
     * @param object $product
     * @return void
     */
    public function setupProductString($product)
    {
        return '<b>' . $product->Name . ' - ' . $product->Quantity . 'x' . $product->Dosage . ' ' . $product->Unit . '</b>';
    }

    /**
     * Get all of the products related to the prescription
     *
     * @param int $id
     * @return array
     */
    public function getProducts($id, $detailed = false)
    {
        $product = DB::table('Product AS p')->selectRaw("p.*")->where('PrescriptionID', $id);

        if ($detailed) {
            $product->selectRaw("pc.TariffCode")->leftJoin('ProductCode AS pc', 'pc.Code', '=', 'p.Code');
        }

        $product = $product->get();

        return $product;
    }

    /**
     * Get all of the products related to the prescription
     *
     * @param int $id
     */
    public function getAttachedProducts($id)
    {
        $product = DB::table('Product AS p')->select(["p.*", "pc.TariffCode" /*, "i.CountryID", "i.Note"*//*, "c.Name AS CountryName","c.CodeName2 AS CountryCodeName"*/])
            // ->selectRaw("COUNT(i.InventoryItemID) AS Qty")
            ->where('PrescriptionID', $id)
            ->leftJoin('ProductCode AS pc', 'pc.Code', '=', 'p.Code')
            // ->leftJoin('InventoryItem AS i', 'i.ProductID', '=', 'p.ProductID')
            // ->leftJoin('Country AS c', 'i.CountryID', '=', 'c.CountryID')
            // ->groupBy("i.CountryID")
            ->orderBy("p.ProductID", 'DESC');

        return $product->get();
    }

    /**
     * Undocumented function
     *
     * @return object
     */
    public function getDeliveryCompanies()
    {
        return DB::table('Setting')->where('Type', 2)->orderBy('Name', 'desc')->get();
    }

    /**
     * Get shipper data
     * Hardcoded type to 1
     *
     * @return object
     */
    public function getShipperData()
    {
        return DB::table('Client AS p')
            ->selectRaw("p.*, c.CodeName2 AS 'CountryCodeName', c.Name AS 'CountryName', c.RegionID")
            ->leftJoin('Country AS c', 'p.CountryID', '=', 'c.CountryID')
            ->where('Type', 1)
            ->first();
    }

    /**
     * Get return data
     *
     */
    public function getReturnData($id)
    {
        return DB::table('Client AS p')
            ->selectRaw("p.*, c.CodeName2 AS 'CountryCodeName', c.Name AS 'CountryName'")
            ->leftJoin('Country AS c', 'p.CountryID', '=', 'c.CountryID')
            ->where('Type', 1)
            ->first();
    }

    /**
     * @param int $id
     */
    public function getAlternateShipperData($id)
    {
        return DB::table('UPSAccessPoint AS u')->selectRaw("u.*, c.CodeName2 AS 'CountryCodeName', c.Name AS 'CountryName'")
            ->leftJoin('Country AS c', 'u.CountryCode', '=', 'c.CountryID')
            ->where('PrescriptionID', $id)->first();
    }

    /**
     * Update a prescription with tracking info
     *
     * @param int $id
     * @param string $code
     * @param string $message
     */
    public function updateOrder($id, $code, $message, $status = 8, $awb = '')
    {
        $update = [
            'TrackingCode' => str_replace([' ', "\n", "\t", "\r"], '', $code),
            'Status' => $status,
            'Message' => $message,
            'AirwayBillNumber' => $awb,
            'UpdatedDate' => time(),
            'TrackingSent' => 1
        ];

        if ($status == 8) {
            $update['Email'] = '';
            $update['Telephone'] = '';
            $update['Mobile'] = '';
        }

        return DB::table('Prescription')->where('PrescriptionID', $id)->update($update);
    }

    /**
     * Update the message in prescription
     *
     * @param int $id
     * @param string $message
     */
    public function updateOrderMessage($id, $message)
    {
        return DB::table('Prescription')->where('PrescriptionID', $id)->update(
            [
                'Message' => $message
            ]
        );
    }

    /**
     * Updates the tracking code and sets the status to shipped
     *
     * @param string $trackingCode
     * @param int $prescriptionID
     */
    public function updateTrackingCode($trackingCode, $prescriptionID)
    {
        return DB::table('Prescription')->where('PrescriptionID', $prescriptionID)->update(
            [
                'TrackingCode' => str_replace([' ', "\n", "\t", "\r"], '', $trackingCode),
                'Status' => 8,
                'UpdatedDate' => time(),
                'TrackingSent' => 1,
                'Email' => '',
                'Telephone' => '',
                'Mobile' => ''
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @param object $order
     * @return boolean
     */
    public function isCOD($order)
    {
        return $order->PaymentMethod != '0';
    }

    /**
     * Does the order require a commercial invoice
     *
     * @param object $order
     * @return boolean
     */
    public function isCI($order)
    {
        return in_array($order->DCountryCode, ['143', '162', '205', '243']) && $order->Repeats != '0' && $order->Repeats != '';
    }

    /**
     * Do we need to print out a physical commercial invoice
     *
     * @return boolean
     */
    public function isCIPaper($order)
    {
        return in_array($order->DCountryCode, ['143']) && $order->Repeats != '0' && $order->Repeats != '';
    }

    /**
     * Undocumented function
     *
     * @param [type] $order
     * @return boolean
     */
    public function isDutiable($order)
    {
        return in_array($order->DCountryCode, ['143', '162', '205', '243']);
    }

    /**
     * Setup query builder object for order
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function order()
    {
        $columns = ['*'];

        if (Auth::user()->role < 50) {
            $columns =
                ['Prescription.PrescriptionID', 'Prescription.ReferenceNumber'];
        } else {
            $columns =
                ['Prescription.PrescriptionID', 'Prescription.ReferenceNumber'];
        }

        $data = DB::table('Prescription')->select($columns);

        $data = $data
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(Prescription.CreatedDate), '%e %b %Y %H:%i') AS 'Received Date'")
            ->selectRaw("CONCAT('<b>',Prescription.Name, ' ', Prescription.Surname, '</b><br>',
        COALESCE(Prescription.DAddress1, ''), ' ', COALESCE(Prescription.DAddress2,''), ' ', COALESCE(Prescription.DAddress3, ''), ' ', COALESCE(Prescription.DAddress4, ''),
        '<br>', COALESCE(Prescription.DPostcode,''),', ' , c.Name) AS 'Patient Name/Address', IFNULL(p.Title, 'N/A') AS Pharmacy")
            ->selectRaw("Prescription.Status")
            ->leftJoin('Pharmacy AS p', 'p.PharmacyID', '=', 'Prescription.PharmacyID')
            ->leftJoin('Country AS c', 'c.CountryID', '=', 'Prescription.DCountryCode');

        $user = Auth::user();

        if ($user->pharmacy_id != 1) {
            $data = $data->where('Prescription.PharmacyID', '=', $user->pharmacy_id);
        }

        return $data->leftJoin('Client', 'Prescription.ClientID', '=', 'Client.ClientID');
    }

    /**
     * Setup query builder object for order activities
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function orderActivities($csv = false)
    {
        $columns = ['*'];

        if (\Auth::user()->role < 50) {
            $columns =
                ['a.ActivityID', 'Prescription.PrescriptionID', 'Prescription.ReferenceNumber'];
        } else {
            $columns =
                ['a.ActivityID', 'Prescription.PrescriptionID', 'Prescription.ReferenceNumber'];
        }

        $data = DB::table('Prescription')->select($columns);

        $data = $data->selectRaw("a.Date AS Date, a.Arguments");

        if (!$csv) {
            $data = $data->selectRaw("CONCAT('<b>',Prescription.Name, ' ', Prescription.Surname, '</b><br>',
            COALESCE(Prescription.DAddress1, ''), ' ', COALESCE(Prescription.DAddress2,''), ' ', COALESCE(Prescription.DAddress3, ''), ' ', COALESCE(Prescription.DAddress4, ''),
            '<br>', COALESCE(Prescription.Postcode,''),', ' , c.Name) AS 'Patient Name/Address'")
                ->leftJoin('Country AS c', 'c.CountryID', '=', 'Prescription.CountryCode');
        } else {
            $data = $data->selectRaw("
                CONCAT(Prescription.Name, ' ', Prescription.Surname) AS 'Patient Name',
                CONCAT(COALESCE(Prescription.DAddress2,''), ' ', COALESCE(Prescription.DAddress3, ''), ' ', COALESCE(Prescription.DAddress4, ''),
                COALESCE(Prescription.Postcode,''),', ' , c.Name) AS 'Patient Address'
            ")
                ->leftJoin('Country AS c', 'c.CountryID', '=', 'Prescription.CountryCode');
        }

        return $data->leftJoin('Client', 'Prescription.ClientID', '=', 'Client.ClientID')
            ->leftJoin('Activity as a', 'a.OrderID', '=', 'Prescription.PrescriptionID')
            ->where('a.Type', 60)->where('a.Status', 1)->where('a.OrderID', '!=', NULL);
    }

    /**
     * Get product list via PrescriptionID
     *
     * @param [type] $items
     */
    public function products($items)
    {

        return DB::table('Product')->select(['Product.PrescriptionID', 'ProductCode.Name', 'Product.Dosage', 'Product.Quantity', 'Product.Unit'])
            ->leftJoin('ProductCode', 'Product.Code', '=', 'ProductCode.Code')
            ->groupBy('Product.ProductID')
            ->whereIn('PrescriptionID', $items)
            ->get();
    }

    /**
     * Get users lists by codes
     *
     * @param [type] $codes
     */
    public function users($codes)
    {
        $users = DB::table('PxpUser AS u')->selectRaw("CONCAT(u.name, ' ', u.surname) as title, code")->whereIn('code', $codes)->get();

        $authorizationUsers = DB::table('PxpUser AS u')->selectRaw("CONCAT(u.name, ' ', u.surname) as title, ac.Code as code")
            ->leftJoin('AuthorizationCode as ac', 'ac.UserID', '=', 'u.id')
            ->whereIn('ac.Code', $codes)->get();

        $data = $users->merge($authorizationUsers);

        return $data;
    }

    /**
     * Undocumented function
     *
     * @param [type] $f
     * @param [type] $request
     * @param [type] $data
     */
    public function setSearchParameters($f, $request, $data)
    {
        $filters = json_decode($f);
        $strict = json_decode($request->strict);
        $operator = $strict ? '=' : 'LIKE';

        if (isset($filters->timestamp)) {
            $dateFilter = $filters->timestamp == 'recieved_date' ? 'CreatedDate' : 'UpdatedDate';
        } else {
            $dateFilter = 'CreatedDate';
        }

        foreach ($filters as $key => $value) {
            if ($value != '') {
                switch ($key) {
                    case 'start_date':
                        $date = new \DateTime($value);
                        $date->setTime(00, 00, 00);
                        $date = $date->getTimestamp();

                        $data = $data->where("Prescription.$dateFilter", '>', $date);

                        break;
                    case 'end_date':
                        $date = new \DateTime($value);
                        $date->setTime(23, 59, 59);
                        $date = $date->getTimestamp();

                        $data = $data->where("Prescription.$dateFilter", '<', $date);

                        break;
                    case 'order_id':
                        if (preg_match('#^[1-9][0-9]*(,[1-9][0-9]+)*$#', preg_replace('/\s+/', '', $value))) {
                            $valueArray = explode(',', preg_replace('/\s+/', '', $value));
                            $data = $data->whereIn('Prescription.PrescriptionID', $valueArray);
                        } else {
                            $data = $data->where('Prescription.PrescriptionID', $operator, $strict ? $value : "%$value%");
                        }
                        break;
                    case 'country':
                        $data = $data->where('Prescription.CountryCode', '=', $value);
                        break;
                    case 'pharmacy':
                        if (count($value) > 0) {
                            $data = $data->whereIn('Prescription.PharmacyID', $value);
                        }

                        break;
                    case 'status':
                        $data = $data->where('Prescription.Status', '=', $value);
                        break;
                    case 'delivery':
                        $data = $data->where('Prescription.DeliveryID', '=', $value);
                        break;
                    case 'doctor':
                        $data = $data->where('Prescription.DoctorID', '=', $value);
                        break;
                    case 'reference':
                        if (preg_match('#^[1-9][0-9]*(,[1-9][0-9]+)*$#', preg_replace('/\s+/', '', $value))) {
                            $valueArray = explode(',', preg_replace('/\s+/', '', $value));
                            $data = $data->whereIn('Prescription.ReferenceNumber', $valueArray);
                        } else {
                            $data = $data->where('Prescription.ReferenceNumber', $operator, $strict ? $value : "%$value%");
                        }
                        break;
                    case 'name':
                        $data = $data->where('Prescription.Name', $operator, $strict ? $value : "%$value%");
                        break;
                    case 'surname':
                        $data = $data->where('Prescription.Surname', $operator, $strict ? $value : "%$value%");
                        break;
                    case 'client':
                        $data = $data->where('Prescription.ClientID', '=', $value);
                        break;
                    case 'product':
                        $data = $data->whereRaw("Prescription.PrescriptionID IN (SELECT PrescriptionID FROM Product WHERE Product.Code = $value)");
                        break;
                    default:
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Update order to status with number
     *
     * @param [type] $id
     * @param [type] $status
     * @return int|bool
     */
    public function updateOrderStatus($id, $status, $substatus = NULL, $wipeTracking = false)
    {
        $update = [
            'Status' => $status,
            'UpdatedDate' => time() //added because the UpdatedDate was not getting properly set
        ];

        $update['SubStatus'] = $substatus;

        if (in_array($status, [8, 3, 6, 12])) {
            $update['Email'] = '';
            $update['Telephone'] = '';
            $update['Mobile'] = '';
        }

        if ($wipeTracking) {
            $update['TrackingCode'] = '';
        }

        return DB::table('Prescription')->where('PrescriptionID', $id)->update($update);
    }
}

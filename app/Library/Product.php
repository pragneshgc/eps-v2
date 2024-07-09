<?php
namespace App\Library;

use GuzzleHttp;
use Illuminate\Support\Facades\DB;

class Product
{
    /**
     * Undocumented function
     *
     * @param [type] $filters
     * @return \Illuminate\Support\Collection
     */
    public function getProducts($filters)
    {
        $columns = [
            "pc.ProductCodeID",
            "pc.Code",
            "pc.Name",
            "pc.Quantity",
            "pc.Units",
            "pc.Status",
            "pc.JVM AS Pouchable",
            "pc.ProductType AS Product Type",
            "pc.Pack AS Package",
            "pc.OTC AS Reclassification",
            "pc.Fridge",
            "pc.TariffCode",
            "p.Price"
        ];

        $data = DB::table('ProductCode AS pc')
            ->select($columns)
            ->leftJoin('Pricing AS p', 'p.Code', '=', 'pc.Code');

        if (isset($filters->letter) && $filters->letter != 'all') {
            $data = $data->whereRaw("pc.Name REGEXP '^[$filters->letter].*$'");
        }

        if (isset($filters->letter) && $filters->letter == 'number') {
            $data = $data->whereRaw("pc.Name not regexp '[^A-Za-z]'");
        }

        if (isset($filters->name) && $filters->name != '') {
            $data = $data->whereRaw("pc.Name LIKE CONCAT('%', ?, '%')", [$filters->name]);
        }

        if (isset($filters->code) && $filters->code != '') {
            $data = $data->whereRaw("pc.Code LIKE CONCAT('%', ?, '%')", [$filters->code]);
        }

        if (isset($filters->pouchable) && $filters->pouchable != 'all') {
            switch ($filters->pouchable) {
                case 0:
                    $data = $data->where('pc.JVM', 0);
                    break;
                case 1:
                    $data = $data->where('pc.JVM', 1);
                    break;
                case 2:
                    $data = $data->where('pc.JVM', 2);
                    break;
                case 'all':
                    break;
                default:
                    break;
            }
        }

        if (isset($filters->company)) {
            switch ($filters->company) {
                case 'DISCONTINUED':
                    $data = $data->where('pc.Status', 2);
                    break;
                case 'INACTIVE':
                    $data = $data->where('pc.Status', 0);
                    break;
                case 'NOTPRICED':
                    $data = $data->where('pc.Status', 1)->where('p.ClientID', NULL);
                    break;
                default:
                    $data = $data->where('pc.Status', 1)->where('p.ClientID', $filters->company);
                    break;
            }
        }

        if (isset($filters->fridge)) {
            if ($filters->fridge != 'all') {
                $data = $data->where('pc.Fridge', $filters->fridge);
            }
        }

        if (isset($filters->package)) {
            if ($filters->package != 'all') {
                $data = $data->where('pc.Pack', $filters->package);
            }
        }

        if (isset($filters->type)) {
            if ($filters->type != 'all') {
                $data = $data->where('pc.ProductType', $filters->type);
            }
        }

        if (isset($filters->fdb)) {
            if ($filters->fdb == '1') {
                $data = $data->where('pc.FDBID', '=', '0');
            } else if ($filters->fdb == '2') {
                $data = $data->where('pc.FDBID', '!=', '0');
            }
        }

        if (isset($filters->reclassification)) {
            if ($filters->reclassification != 'all') {
                $data = $data->where('pc.OTC', $filters->reclassification);
            }
        }

        return $data->where('pc.Type', 1)
            ->groupBy("pc.ProductCodeID")
            ->orderBy('pc.Name', 'ASC')
            ->get();
    }

    /**
     * Format ProductCode entries for frontend display
     *
     * @param [type] $data
     * @return void
     */
    public function formatProducts($data)
    {
        foreach ($data as $value) {
            // if(isset($value->Name) && isset($value->Quantity) && isset($value->Units)){
            //     $value->Name = "$value->Name<br><b>$value->Quantity $value->Units</b>";
            //     unset($value->Quantity);
            //     unset($value->Units);
            // }

            if (isset($value->{'Print Instruction'})) {
                $value->{'Print Instruction'} = $value->{'Print Instruction'} == 1 ? 'Yes' : 'No';
            }

            if (isset($value->Price)) {
                $value->Price = '£ ' . number_format((float) $value->Price, 2, '.', ',');
            }

            if (isset($value->Reclassification)) {
                $value->Reclassification = $value->Reclassification == 0 ? 'POM' : 'P';
            }

            if (isset($value->Fridge)) {
                $value->Fridge = $value->Fridge == 0 ? 'No' : 'Yes';
            }

            if (isset($value->Pouchable)) {
                switch ($value->Pouchable) {
                    case 0:
                        $value->Pouchable = 'Manual';
                        break;
                    case 1:
                        $value->Pouchable = 'Always Enabled';
                        break;
                    case 2:
                        $value->Pouchable = 'Always Disabled';
                        break;
                    default:
                        break;
                }
            }

            if (isset($value->Package)) {
                $value->Package = $value->Package == 0 ? 'Single' : 'Package';
            }

            if (isset($value->VAT)) {
                $value->VAT = $value->VAT . '%';
            }

            if (isset($value->{'Product Type'})) {
                $value->{'Product Type'} = $value->{'Product Type'} == 1 ? 'Medicine' : 'Test Kit';
            }

            if (isset($value->ClientID)) {
                $name = DB::table('Client')->where('ClientID', $value->ClientID)->value('CompanyName');

                $value->ClientID = $value->ClientID == 0 ? 'DEFAULT' : $name;
            }
        }

        return $data;
    }

    public function getProduct($id)
    {
        return DB::table('ProductCode')->where('ProductCodeID', $id)->first();
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Support\Collection
     */
    public function productList()
    {
        return DB::table('ProductCode AS pc')
            ->select(["pc.ProductCodeID", "pc.Name", "pc.Type", "pc.Quantity", "pc.Code"])
            ->orderBy('pc.Name')
            ->get();
    }

    /**
     * Undocumented function
     *
     * @param int $id
     */
    public function priceList($id)
    {
        $product = DB::table('ProductCode')->select(['Pack', 'Code'])->where('ProductCodeID', $id)->get();

        if (!$product) {
            return [];
        }

        if ($product->first()->Pack != 1) {
            $data = DB::table('Pricing AS p')->select(['p.PricingID', 'p.Code', 'p.ClientID', 'p.Price', 'p.Price as UnformattedPrice', 'p.Quantity', 'pc.Units', 'pc.TariffCode', 'pc.VAT'])
                ->leftJoin('ProductCode AS pc', 'pc.Code', '=', 'p.Code')
                ->leftJoin('Client AS cl', 'cl.ClientID', '=', 'p.ClientID')
                ->where('pc.ProductCodeID', $id)->where('p.Status', 1)->whereRaw("(cl.Status = 1 OR cl.Status IS NULL)")
                ->orderBy('p.ClientID')->get();
        } else {
            $data = DB::table('PackProduct')
                ->select(['PackProductID', 'Code', 'Description', 'Dosage AS Quantity', 'Unit', 'ProductCode', 'Instruction AS Print Instruction'])
                ->where('Code', $product->first()->Code)->get();
        }

        return $data;
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return int
     */
    public function delete($id)
    {
        return DB::table('ProductCode')
            ->where('ProductCodeID', $id)
            ->update(['Status' => '0']);
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return int
     */
    public function deletePricing($id)
    {
        return DB::table('Pricing')->where('PricingID', $id)->delete();
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @return int
     */
    public function deletePackProduct($id)
    {
        return DB::table('PackProduct')->where('PackProductID', $id)->delete();
    }

    /**
     * Undocumented function
     *
     * @param array $input
     * @return bool
     */
    public function savePackProduct($input)
    {
        $productCode = DB::table('ProductCode')->where('ProductCodeID', $input['Description'])->first();

        $input['Description'] = $productCode->Name;
        $input['Unit'] = $productCode->Units;
        $input['ProductCode'] = $productCode->Code;

        return DB::table('PackProduct')->insert($input);
    }

    /**
     * Fetches a list of delivery companies from the Setting table
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDeliveryCompanies()
    {
        return DB::table('Setting')->where('Type', 2)->get();
    }

    /**
     * Fetches a list of clients
     *
     * @return \Illuminate\Support\Collection
     */
    public function getClients()
    {
        return DB::table('Client')->where('Status', 1)->get();
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return int
     */
    public function reactivate($id)
    {
        return DB::table('ProductCode')->where('ProductCodeID', $id)->update(['Status' => '1']);
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @return int
     */
    public function addProduct($input)
    {
        $input['Type'] = 1;

        $pricing = DB::table('Pricing')->insert([
            'Code' => $input['Code'],
            'Price' => $input['Price'],
            'ClientID' => 0,
            'Type' => 1,
            'Status' => 1,
            'Quantity' => $input['Quantity'],
        ]);

        unset($input['Price']);

        return DB::table('ProductCode')->insertGetId($input);
    }

    /**
     * Update a product via input
     *
     * @param int $id
     * @param array $input
     * @return int
     */
    public function updateProduct($id, $input)
    {
        $code = DB::table('ProductCode')->where('ProductCodeID', $id)->value('Code');

        DB::table('Pricing')->where('Code', $code)->update([
            'Code' => $input['Code'],
            'Quantity' => $input['Quantity'],
        ]);

        return DB::table('ProductCode')->where('ProductCodeID', $id)->update($input);
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @return int
     */
    public function addProductPricing($request)
    {
        return DB::table('Pricing')->insert(
            [
                'Code' => $request->productCode['Code'],
                'ClientID' => $request->pricing['client'],
                'Price' => $request->pricing['price'],
                'Type' => $request->productCode['Type'],
                'Status' => 1,
                'Quantity' => $request->productCode['Quantity'],
            ]
        );
    }

    /**
     * Import a product from the FDB database if the product exists
     *
     * @param string $type
     * @param int $code
     */
    public function importProduct($type, $code, $size = false)
    {
        $product = $this->getProductDetails($type, $code);

        if (!$product) {
            return false; //product not found
        }

        //get the product details
        $product = json_decode($product);
        $details = false;
        $pack = false;
        $code = 'FDB';
        // $codeSuffix = '';
        $packArray = false;

        //import the product
        if (count($product->data) > 1) {
            // return false;//what to do if multiple products
            $details = $product->data[0]->Product; //for now this
        } else if (count($product->data) == 0) {
            return false;
        } else {
            $details = $product->data[0]->Product;
        }

        if ($product->data[0]->Drug->DrugClass == 'Brand') {
            $packArray = $product->data[0]->DispensablePacks;
        } else {
            $packArray = $product->data[0]->RelatedPacks;
        }

        if (count($packArray) == 0) {
            return false;
        }

        if ($size) {
            foreach ($packArray as $DispensablePack) {
                if ($size >= $DispensablePack->PackSize) {
                    $pack = $DispensablePack;
                }
            }
        } else {
            if (count($packArray) > 0) {
                foreach ($packArray as $DispensablePack) {
                    if ($type == 'pip' && $DispensablePack->PIPCode == $code) {
                        $pack = $DispensablePack;
                    } else if ($type == 'ean' && $DispensablePack->EANCode == $code) {
                        $pack = $DispensablePack;
                    }
                }
            }
        }

        if (!$pack) {
            $pack = $packArray[0];
        }

        // if(count($packArray) > 1){
        //     $codeSuffix = '/'.$pack->PackSize;
        // }

        if (!$details || !$pack)
            return false;

        //setup the product code
        $newProductCode = [
            'Code' => $code . $details->SingleId . '/' . $pack->PackSize,
            //prefix all the SingleIds with FDB so we know which ones are which
            'Name' => $details->PrimaryPreferredName,
            'FDBID' => $product->data[0]->_id->{'$oid'},
            'Type' => 1,
            'Status' => 1,
            'Quantity' => (int) $pack->PackSize,
            'Units' => ucfirst($details->Formulation),
            'Fridge' => $details->KnownFridgeLine ? 1 : 0,
            'OTC' => 0,
            //over the counter POM (0) OR P (1)
            'Pack' => 0,
            'VAT' => 20,
            'ProductType' => 1,
            //1 - medicine, 2 - test kit
            'TariffCode' => 0, //which one
        ];

        $result = DB::table('ProductCode')->insert($newProductCode);

        return $result;
    }

    /**
     * Query the FDB database for product details
     *
     * @param [type] $type
     * @param [type] $code
     */
    public function getProductDetails($type, $code)
    {
        $uri = "https://api.4smconsulting.co.uk/fdb/import/code?$type=$code";

        $options = [
            'base_uri' => $uri,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
                'Accept' => 'application/json',
                'Authorization' => config('services.fdb.key'),
            ],
        ];

        $http = new GuzzleHttp\Client($options);

        $response = $http->request('POST', '', $options)->getBody()->getContents();

        return $response;
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @return void
     */
    public function fdbProductsList($request)
    {
        $page = 1;
        $limit = 10;
        $q = '';

        if (isset($request->page)) {
            $page = $request->page;
        }

        if (isset($request->q)) {
            $q = $request->q;
        }

        if (isset($request->limit)) {
            $limit = $request->limit;
        }


        $uri = "https://api.4smconsulting.co.uk/fdb?page=$page&q=$q&limit=$limit";

        $options = [
            'base_uri' => $uri,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
                'Accept' => 'application/json',
                'Authorization' => config('services.fdb.key'),
            ],
        ];

        $http = new GuzzleHttp\Client($options);

        $response = $http->request('GET', '', $options)->getBody()->getContents();

        $response = json_decode($response);

        $response->data->data = $this->formatFDBResult($response->data->data);

        return json_encode($response);
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public function formatFDBResult($data)
    {
        foreach ($data as $key => $value) {
            $newValue = [
                'FDB ID' => $value->_id->{'$oid'},
                'Product Name' => $value->Product->PrimaryPreferredName,
                'Product ID' => $value->Product->SingleId,
                'Legal Category' => $value->Product->LegalCategory,
                'Fridge' => $value->Product->KnownFridgeLine,
                'Pack Names' => [],
                'Pack Barcodes' => [],
                'Pack PIP Codes' => [],
                'Pack Sizes' => [],
                'Pack Units' => [],
            ];

            foreach ($value->DispensablePacks as $pack) {
                $newValue['Pack Names'][] = $pack->PrimaryPreferredName;
                $newValue['Pack Barcodes'][] = $pack->EANCode;
                $newValue['Pack PIP Codes'][] = $pack->PIPCode;
                $newValue['Pack Sizes'][] = $pack->PackSize;
                $newValue['Pack Units'][] = $pack->PackUnit;
            }

            $newValue['Pack Names'] = implode(',', $newValue['Pack Names']);
            $newValue['Pack Barcodes'] = implode(',', $newValue['Pack Barcodes']);
            $newValue['Pack PIP Codes'] = implode(',', $newValue['Pack PIP Codes']);
            $newValue['Pack Sizes'] = implode(',', $newValue['Pack Sizes']);
            $newValue['Pack Units'] = implode(',', $newValue['Pack Units']);

            $data[$key] = (object) $newValue;
        }

        return $data;
    }

    /**
     * Import products from prescription XML's
     *
     * @param SimpleXMLElement $xml
     */
    public function importProductFromXML($id, $xml)
    {
        $errors = [];
        $products = $xml->Product;

        try {
            for ($i = 0; $i < count($products); $i++) {
                DB::table('Product')->insert([
                    'PrescriptionID' => $id,
                    'GUID' => $products[$i]->Guid,
                    'Code' => $products[$i]->ProductCode,
                    'Description' => $products[$i]->Description,
                    'Instructions' => $products[$i]->Instructions,
                    'Instructions2' => $products[$i]->Instructions2,
                    'Quantity' => $products[$i]->ProductQuantity->Quantity,
                    'Unit' => $products[$i]->ProductQuantity->Units,
                    'Dosage' => $products[$i]->ProductQuantity->Dosage,

                ]);
            }
        } catch (\Throwable $th) {
            array_push($errors, "Could not insert product");
        }

        return $errors;
    }

    /**
     * Remove products using Order ID
     *
     * @param int $id
     */
    public function removeByOrderId($id)
    {
        return DB::table('Product')->where('PrescriptionID', $id)->delete();
    }

    /**
     * Get a list of alternative names
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public function getAlternativeNames($id)
    {
        return DB::table('ProductNameAlternative AS pna')
            ->select(['pna.ProductNameAlternativeID', 'pna.ProductCodeID', 'c.CompanyName', 'pna.AlternativeName', 'u.name AS Name', 'u.surname AS Surname', 'pna.CreatedAt'])
            ->leftJoin('Client AS c', 'c.ClientID', '=', 'pna.ClientID')
            ->leftJoin('PharmacyUser AS u', 'u.id', '=', 'pna.UserID')
            ->where('pna.ProductCodeID', $id)
            ->whereNull('pna.DeletedAt')
            ->get();
    }

    /**
     * Check alternative names for products
     *
     * @return boolean
     */
    public function checkAlternativeNames($request)
    {
        return DB::table('ProductNameAlternative')
            ->where('ProductCodeID', $request->code)
            ->where('AlternativeName', $request->name)
            ->where('ClientID', $request->client)
            ->whereNull('DeletedAt')
            ->exists();
    }

    /**
     * Approve an alternative name for a product
     *
     * @return boolean
     */
    public function approveAlternativeName($request)
    {
        return DB::table('ProductNameAlternative')->insert([
            'ProductCodeID' => $request->ProductCodeID,
            'ClientID' => $request->ClientID,
            'UserID' => $request->UserID,
            'AlternativeName' => $request->AlternativeName,
        ]);
    }

    public function deleteAlternativeName($id)
    {
        return DB::table('ProductNameAlternative')
            ->where('ProductNameAlternativeID', $id)
            ->update([
                'DeletedAt' => \Carbon\Carbon::now()
            ]);
    }

    public function logs($id)
    {
        return DB::table('SystemActivity')
            ->where('ReferenceID', $id)
            ->orderBy('CreatedAt', 'DESC')
            ->get();
    }
}
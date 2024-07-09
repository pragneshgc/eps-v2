<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data = DB::table('Pharmacy')
            ->selectRaw("PharmacyID, Title, IFNULL(Location, 'Unknown') AS Location, IF(Status = 1, 'Active', 'Inactive') AS Status")
            ->selectRaw("CONCAT('<img alt=\"\" style=\"width: 80px;\" src=\"','/pharmacies/logo/',PharmacyID, '\"/>') AS Logo")
            ->whereNull('DeletedAt');

        if ($this->q != '') {
            $data = $data->where('Title', 'LIKE', '%' . $this->q . '%');
        }

        if ($this->s != '') {
            $data = $data->orderBy($this->s, $this->o);
        }

        $data = $data->paginate($this->l);

        return $this->sendResponse($data, 'Successfull query');
    }

    public function pharmacy($id)
    {
        $data = DB::table('Pharmacy')->where('PharmacyID', '=', $id)->first();

        $data->clients = DB::table('Client')->where('Type', 2)->where('Status', 1)->get();

        $details = [];

        foreach ($data->clients as $client) {
            $details[$client->ClientID] = DB::table('PharmacyDetail')->where('ClientID', '=', $client->ClientID)->where('PharmacyID', '=', $id)->first();

            if ($details[$client->ClientID] == null) {
                $details[$client->ClientID]['ClientID'] = $client->ClientID;
                $details[$client->ClientID]['PharmacyID'] = $id;
                $details[$client->ClientID]['AccountNumber'] = '';
                $details[$client->ClientID]['BillingAccountNumber'] = '';
                $details[$client->ClientID]['VATNumber'] = '';
                $details[$client->ClientID]['EORI'] = '';
            }
        }

        $data->details = $details;

        return $this->sendResponse($data, 'Successfull query');
    }

    public function create(Request $request)
    {
        if (\Auth::user()->role < 50) {
            return $this->sendError('You are not allowed to create a new pharmacy.');
        }

        $input = $request->validate([
            'Title' => 'required|max:255',
        ]);

        $pharmacy = array(
            'Title' => $input['Title'],
        );

        $data = DB::table('Pharmacy')->insert($pharmacy);

        return $this->sendResponse($data, 'New pharmacy added');
    }

    public function list(Request $request)
    {
        $user = \Auth::user();

        $pharmacies = DB::table('Pharmacy')->whereNull('DeletedAt');

        if ($user->pharmacy_id != 1) {
            $pharmacies = $pharmacies->where('PharmacyID', $user->pharmacy_id);
        }

        if (isset($request->filter)) {
            $pharmacies = $pharmacies->where("Title", "LIKE", "%$request->filter%");
        }

        $pharmacies = $pharmacies->get();

        return $this->sendResponse($pharmacies, 'Pharmacy List');
    }

    public function update($id, Request $request)
    {
        if (\Auth::id() != $id && \Auth::user()->role < 50) {
            return $this->sendError('You are not allowed to update this pharmacy information.');
        }

        $details = $request->input()['Details'];
        $banlist = $request->input()['BanList'];

        $input = $request->validate([
            'Title' => 'required|max:255',
            'Location' => 'required|max:255',
            'AccountNumber' => 'nullable|max:255',
            'BillingAccountNumber' => 'nullable|max:255',
            // 'ShipperName' => 'required|max:255',
            'VATNumber' => 'nullable|max:255',
            'EORI' => 'nullable|max:255',
            'Telephone' => 'required|max:255',
            'Email' => 'required|max:255',
            'Address1' => 'required|max:255',
            'Address2' => 'max:255',
            'Address3' => 'max:255',
            'Address4' => 'max:255',
            'Postcode' => 'required|max:255',
            'CountryCode' => 'required|max:255',
            'Contents' => 'required|max:255',
        ]);

        DB::table('PharmacyBanned')->where('PharmacyID', $id)->delete();

        foreach ($banlist as $bannedId) {
            DB::table('PharmacyBanned')->insert([
                'PharmacyID' => $id,
                'TypeID' => $bannedId,
                'Type' => 1
            ]);
        }

        foreach ($details as $detail) {
            if ($existing = DB::table('PharmacyDetail')->where('ClientID', $detail['ClientID'])->where('PharmacyID', $detail['PharmacyID'])->first()) {
                DB::table('PharmacyDetail')->where('ClientID', $detail['ClientID'])->where('PharmacyID', $detail['PharmacyID'])->update([
                    'AccountNumber' => $detail['AccountNumber'],
                    'BillingAccountNumber' => $detail['BillingAccountNumber'],
                    'VATNumber' => $detail['VATNumber'],
                    'EORI' => $detail['EORI'],
                    'ShipperName' => $detail['ShipperName'],
                ]);
            } else {
                DB::table('PharmacyDetail')->where('ClientID', $detail['ClientID'])->where('PharmacyID', $detail['PharmacyID'])->insert([
                    'PharmacyID' => $detail['PharmacyID'],
                    'ClientID' => $detail['ClientID'],
                    'AccountNumber' => $detail['AccountNumber'],
                    'BillingAccountNumber' => $detail['BillingAccountNumber'],
                    'VATNumber' => $detail['VATNumber'],
                    'EORI' => $detail['EORI'],
                    'ShipperName' => $detail['ShipperName'],
                ]);
            }
        }

        $data = DB::table('Pharmacy')->where('PharmacyID', $id)->update($input); // 0 on no changes, 1 on success

        return $this->sendResponse($data, 'Pharmacy information updated.');
    }

    public function banlist($id)
    {
        $list = DB::table('PharmacyBanned')->where('PharmacyID', $id)->get();

        return $this->sendResponse($list, 'Ban List');
    }

    public function delete($id)
    {
        DB::table('Pharmacy')->where('PharmacyID', $id)->update(
            [
                'DeletedAt' => \Carbon\Carbon::now()
            ]
        );

        return $this->sendResponse([], 'Pharmacy deleted');
    }

    /**
     * Get a doctors signature
     *
     * @param int $id
     * @return Illuminate\Http\Response|Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getLogo($id, Request $request)
    {
        $path = '';
        $extension = 'png';

        if (file_exists(storage_path() . "/app/logos/$id.png")) {
            $path = \Storage::path("/logos/$id.png");
        } else if (file_exists(storage_path() . "/app/logos/$id.jpg")) {
            $path = \Storage::path("/logos/$id.jpg");
            $extension = 'jpg';
        } else {
            $path = \Storage::path("/logos/$id.jpeg");
            $extension = 'jpeg';
        }

        $header = "image/$extension";

        try {
            $img = file_get_contents($path);
        } catch (\Throwable $th) {
            $img = '';
        }

        return response($img)->header('Content-type', $header);
    }

    /**
     * Upload prescriber signature
     *
     * @param int $id
     * @param Request $request
     * @return Illuminate\Http\Response|Illuminate\Contracts\Routing\ResponseFactory
     */
    public function logo($id, Request $request)
    {
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $validated = $request->validate([
                    'name' => 'string|max:40',
                    'image' => 'mimes:jpeg,png|max:4096',
                ]);

                $extension = $request->image->extension();
                $path = $id . "." . $extension;

                $newPath = $request->image->storeAs('logos', $path);

                return $this->sendResponse($newPath, '');
            }
        }

        abort(500, 'Could not upload image :(');
    }
}

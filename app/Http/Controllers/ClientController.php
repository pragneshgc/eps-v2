<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\Client;

class ClientController extends Controller
{
    private $client;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->client = new Client();
    }

    /**
     * Get a list of all the clients
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $data = $this->client->getClientsPaginated($this->q, $this->s, $this->o);
        $data = $this->client->setSearchParameters($this->f, $request, $data)->paginate($this->l);

        return $this->sendResponse($data);
    }

    /**
     * Deactivate a client
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deactivate($id)
    {
        return $this->sendResponse($this->client->deactivate($id));
    }

    /**
     * Get details of a client with ID
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function client($id)
    {
        return $this->sendResponse($this->client->getClient($id));
    }

    /**
     * Update client via ID
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $data = $request->validate([
            'Title' => 'nullable|max:255',
            'Name' => 'sometimes|nullable|max:255',
            'Middlename' => 'nullable',
            'Surname' => 'sometimes|max:255',
            'CompanyName' => 'nullable|max:255',
            'Address1' => 'required|max:255',
            'Address2' => 'nullable|max:255',
            'Address3' => 'nullable|max:255',
            'Address4' => 'nullable|max:255',
            'Postcode' => 'required|max:255',
            'CountryID' => 'required',
            'Telephone' => 'nullable',
            'Mobile' => 'nullable',
            'Email' => 'nullable',
            'CreditLimit' => 'required',
            'IP' => 'sometimes',
            'Status' => 'required',
            'Notes' => 'nullable',
            'CompanyNumber' => 'nullable',
            'GPHCNO' => 'nullable',
            'ReturnURL' => 'nullable',
            'Username' => 'required',
            'Password' => 'required',
            'APIKey' => 'required',
            'ITName' => 'required',
            'ITSurname' => 'nullable',
            'ITEmail' => 'required',
            'TradingName' => 'required',
            'AdditionalComment' => 'nullable',
            'ReturnUsername' => 'nullable',
            'ReturnPassword' => 'nullable',
            'VAT' => 'required',
        ]);


        return $this->sendResponse($this->client->update($id, $data), 'Client updated');
    }

    /**
     * Insert a new client
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {
        $data = $request->validate([
            'Title' => 'nullable|max:255',
            'Name' => 'nullable|max:255',
            'Middlename' => 'nullable',
            'Surname' => 'required|max:255',
            'CompanyName' => 'nullable|max:255',
            'Address1' => 'required|max:255',
            'Address2' => 'nullable|max:255',
            'Address3' => 'nullable|max:255',
            'Address4' => 'nullable|max:255',
            'Postcode' => 'required|max:255',
            'CountryID' => 'required',
            'Telephone' => 'nullable',
            'Mobile' => 'nullable',
            'Email' => 'nullable',
            'CreditLimit' => 'required',
            'IP' => 'required',
            'Status' => 'required',
            'Notes' => 'nullable',
            'CompanyNumber' => 'nullable',
            'GPHCNO' => 'nullable',
            'ReturnURL' => 'nullable',
            'Username' => 'required',
            'Password' => 'required',
            'APIKey' => 'required',
            'ITName' => 'required',
            'ITSurname' => 'nullable',
            'ITEmail' => 'required',
            'TradingName' => 'required',
            'AdditionalComment' => 'nullable',
            'ReturnUsername' => 'nullable',
            'ReturnPassword' => 'nullable',
            'VAT' => 'required',
        ]);

        $data['Type'] = 2;

        return $this->sendResponse($this->client->insert($data));
    }
}

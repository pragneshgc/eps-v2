<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelpController extends Controller
{
    /**
     * Index of help entries
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $entries = DB::table('InventoryHelp')
            ->selectRaw("Category, InventoryHelpID, Title, Description, RelatedPage, CreatedAt, UpdatedAt");
        $categories = DB::table('InventoryHelp')
            ->selectRaw("Category, COUNT(1) AS 'CategoryCount', true AS 'IsOpen'");

        if ($this->q != '') {
            $entries = $entries->selectRaw("true AS 'IsOpen'")->where('Title', 'LIKE', '%' . $this->q . '%')
                ->orWhere('Description', 'LIKE', '%' . $this->q . '%')
                ->orWhere('Category', 'LIKE', '%' . $this->q . '%');

            $categories = $categories->where('Title', 'LIKE', '%' . $this->q . '%')
                ->orWhere('Description', 'LIKE', '%' . $this->q . '%')
                ->orWhere('Category', 'LIKE', '%' . $this->q . '%');
        } else {
            $entries = $entries->selectRaw("false AS 'IsOpen'");
        }

        $entries = $entries->get();
        $categories = $categories->groupBy('Category')->get();

        return $this->sendResponse(['entries' => $entries, 'categories' => $categories], 'Help index');
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $input = $request->input();

        $update = [
            'Category' => $input['Category'],
            'Title' => $input['Title'],
            'Description' => $input['Description'],
            'CreatedBy' => \Auth::user()->id
        ];

        DB::table('InventoryHelp')->insert($update);

        return $this->sendResponse([], 'Created');
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $input = $request->input();

        $update = [
            'Category' => $input['Category'],
            'Title' => $input['Title'],
            'Description' => $input['Description'],
            'UpdatedBy' => \Auth::user()->id
        ];

        DB::table('InventoryHelp')->where('InventoryHelpID', $input['InventoryHelpID'])->update($update);

        return $this->sendResponse([], 'Updated');
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return Illuminate\Http\Response
     */
    public function delete($id)
    {
        $response = DB::table('InventoryHelp')->where('InventoryHelpID', $id)->delete();

        return $this->sendResponse($response, 'Deleted');
    }

    /**
     * Returns Pharmacist App Info
     *
     * @return Illuminate\Http\Response
     */
    public function info()
    {
        $response = [
            'version' => env('APP_VERSION') ? env('APP_VERSION') : '1.0.0',
            'environment' => \App::environment() ? \App::environment() : 'demo',
            'changelog' => file_get_contents('changelog.md')
        ];

        return $this->sendResponse($response, 'Inventory Info');
    }
}

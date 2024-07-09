<?php
namespace App\Library;

use Illuminate\Support\Facades\DB;

// use Carbon\Carbon;

/**
 * Undocumented class
 */
class Client
{
    /**
     * Get a paginated list of doctors
     *
     * @param string $q
     * @param string $s
     * @return void
     */
    public function getClientsPaginated($q, $s, $o)
    {
        $data = DB::table('Client AS c')
            ->selectRaw("c.ClientID AS 'ID', c.CompanyName AS 'Company Name', FORMAT(c.VAT, 2) AS 'VAT', c.ClientID AS 'ID',CONCAT(c.Name, ' ', c.Surname) AS 'Contact', c.Email AS 'Email', c.Telephone AS 'Telephone'")
            ->selectRaw("CASE COALESCE(Status, 0) WHEN 1 THEN 'Active' ELSE 'Inactive' END AS 'Status'");

        if ($q != '') {
            $data = $data->where('Name', 'LIKE', '%' . $q . '%')
                ->orWhere('Email', 'LIKE', '%' . $q . '%');
        }

        if ($s != '') {
            $data = $data->orderBy($s, $o);
        }

        return $data;
    }

    /**
     * Set search parameters for doctors
     *
     * @param [type] $f
     * @param [type] $request
     * @param [type] $data
     * @return object
     */
    public function setSearchParameters($f, $request, $data)
    {
        $filters = json_decode($f);
        $strict = json_decode($request->strict);
        $operator = $strict ? '=' : 'LIKE';

        if (!property_exists($filters, 'status')) {
            $data = $data->where('c.Status', 1);
        } else if (property_exists($filters, 'status')) {
            $data = $data->where('c.Status', $filters->status);
        }

        foreach ($filters as $key => $value) {
            if ($value != '') {
                switch ($key) {
                    case 'name':
                        $data = $data->where('c.Name', $operator, $strict ? $value : "%$value%");
                        break;
                    case 'surname':
                        $data = $data->where('c.Surname', $operator, $strict ? $value : "%$value%");
                        break;
                    case 'companyname':
                        $data = $data->where('c.CompanyName', $operator, $strict ? $value : "%$value%");
                        break;
                    case 'status':
                        $data = $data->where('c.Status', $value);
                        break;
                    default:
                        break;
                }
            }
        }

        $data = $data->where('Type', 2);

        return $data;
    }

    /**
     * Deactivate a client with ID
     *
     * @param int $id
     * @return void
     */
    public function deactivate($id)
    {
        return DB::table('Client')
            ->where('ClientID', $id)
            ->update([
                'Status' => 0
            ]);
    }

    /**
     * Get client by id
     *
     * @param [type] $id
     * @return array
     */
    public function getClient($id)
    {
        return DB::table('Client')->where('ClientID', $id)->first();
    }

    /**
     * Get all clients with status
     *
     * @param int $status
     * @return array
     */
    public function getClients($status = 1)
    {
        return DB::table('Client')->where('Type', 2)->where('Status', 1)->get();
    }

    /**
     * Update client with ID
     *
     * @param int $id
     * @param array $data
     */
    public function update($id, $data)
    {
        return DB::table('Client')->where('ClientID', $id)->update($data);
    }

    /**
     * Insert new data
     *
     * @param array $data
     */
    public function insert($data)
    {
        return DB::table('Client')->insert($data); // 0 on no changes, 1 on success
    }
}
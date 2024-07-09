<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrescriptionFile;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function testAzure()
    {
        try {
            echo "test";
            $xml = Storage::disk('azure')->url("xml/854840-Ref-267-1--1718970682.xml");
        } catch (Exception $ex) {
            dd($ex->getMessage());
        }

        dd($xml);
    }

    public function upload(Request $request)
    {
        $file = $request->getContent();
        $filename = date('Ymdhis') . '.json';
        try {
            Storage::disk('azure')->put($filename, $file);
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
                'data' => \App\Helpers\Generic::getIP()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'success',
            'data' => \App\Helpers\Generic::getIP()
        ]);
    }
}

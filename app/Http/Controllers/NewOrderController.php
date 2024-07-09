<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\Generic;
use Illuminate\Http\Request;
use App\Exceptions\OrderException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Services\OrderProcessingService;
use Symfony\Component\HttpFoundation\Response;

class NewOrderController extends Controller
{
    public function newOrder(Request $request)
    {
        $ipaddress = Generic::getIP();
        $file = $request->getContent();

        Log::channel('import')->info("Import Started from $ipaddress");
        Log::channel('import')->info($file);

        try {
            $orderService = new OrderProcessingService($request);
            $response = $orderService->validateOrderRequest()
                ->extractData()
                ->process();

            return $this->sendResponse($response['id'], $response['message']);
        } catch (OrderException $ex) {
            return $this->sendError($ex->getMessage(), [], $ex->getCode());
        } catch (QueryException $qe) {
            return $this->sendError('SQL error ', [], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], Response::HTTP_BAD_REQUEST);
        }
    }
}

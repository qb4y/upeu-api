<?php

namespace App\Http\Controllers\Report\Treasury;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Data\Report\Treasury\MyVouchersData;
use Carbon\Carbon;
class MyVouchersController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function myValesList(Request $request)
    {
        $jResponse=[];
        try {
            $data = MyVouchersData::myValesList($request);
            if (count($data)>0) {
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "The item does not exist";
                $jResponse['data'] = [];
                $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "400";
        }
        return response()->json($jResponse, $code);
    }
}
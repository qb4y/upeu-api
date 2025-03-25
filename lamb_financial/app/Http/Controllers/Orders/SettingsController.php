<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/22/19
 * Time: 5:05 PM
 */

namespace App\Http\Controllers\Orders;

use App\Http\Data\GlobalMethods;
use App\Http\Data\Orders\SettingsData;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function listAreas(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $department_id = $request->query('department_id');
        if ($valida == 'SI') {
            $jResponse = [];
            $data = SettingsData::listAreas($id_entidad, $department_id);
            $pages_array = (object)array('slug' => 'xxx', 'title' => 'etc');
            foreach ($data as &$valor) {
                if ($valor->id_tipo_area) {
                    $temp = explode(',', $valor->id_tipo_area);
                    $valor->id_tipo_area = $temp;
                } else {
                    $valor->id_tipo_area = [];
                }
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listAreasSearch(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $department = $request->query('department');
        if ($valida == 'SI') {
            $jResponse = [];
            $data = SettingsData::listAreasSearch($id_entidad, $department);
            $pages_array = (object)array('slug' => 'xxx', 'title' => 'etc');
            foreach ($data as &$valor) {
                if ($valor->id_tipo_area) {
                    $temp = explode(',', $valor->id_tipo_area);
                    $valor->id_tipo_area = $temp;
                } else {
                    $valor->id_tipo_area = [];
                }
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

}
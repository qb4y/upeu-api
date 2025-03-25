<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/24/19
 * Time: 4:57 PM
 */

namespace App\Http\Controllers\Setup\Organization;

use App\Http\Data\Setup\Organization\ManagerData;
use App\Http\Data\Setup\Organization\SedeAreaData;
use App\Http\Data\Setup\Organization\Utils;
use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;


class SedeAreaController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createSedeArea()
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_depto = Input::get("id_depto");
            $id_area = Input::get("id_area");
            $id_sede = Input::get("id_sede");
            $id_persona = '7668';
            $estado = Input::get("estado");
            try {
                $id_sede_area = Utils::generateProgresiveNumericalId('ORG_SEDE_AREA', 'ID_SEDEAREA');
                $areaResp = [
                    'id_sedearea' => $id_sede_area,
                    'id_area' => $id_area,
                    'id_persona' => $id_persona,
                    'id_sede' => $id_sede,
                    'id_depto' => $id_depto,
                    'id_entidad' => $id_entidad,
                    'estado' => $estado,
                ];
                $result = SedeAreaData::addSedeArea($areaResp);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'Responsable asignado o datos inválidos';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getSedeArea($idSedeArea)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $datos = SedeAreaData::getSedeArea($idSedeArea);
                if ($datos) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $datos;
                    $code = "200";
                }else{
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'this data is not exit';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateSedeArea($idSedeArea)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_depto = Input::get("id_depto");
            $id_area = Input::get("id_area");
            $id_sede = Input::get("id_sede");
            $id_persona = '7668';
            $estado = Input::get("estado");
            try {
                $data = [
//                    'id_area' => $id_area,
                    'id_persona' => $id_persona,
                    'id_sede' => $id_sede,
                    'id_depto' => $id_depto,
                    'id_entidad' => $id_entidad,
                    'estado' => $estado,
                ];
                $result = SedeAreaData::updateSedeArea($idSedeArea, $data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'Responsable asignado o datos inválidos';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function deleteSedeArea($idSedeArea){
//        dd('incontrollter');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SedeAreaData::deleteSedeArea($idSedeArea);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function findSedeArea(Request $request)
    {
//        dd('jejje');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $txt_search = $request->query('text_search');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
//                $data = OrganizationController::recursiveOrganization(1,0);
                $data = SedeAreaData::findSedeArea($id_entidad ,$txt_search);
//                dd('HEREEEEEE');
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

}
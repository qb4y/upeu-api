<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/11/19
 * Time: 11:28 AM
 */

namespace App\Http\Controllers\Setup\Organization;

use App\Http\Data\Setup\Organization\ManagerData;
use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;

class ResponsableController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function listResponsables($id_org)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
//                $data = OrganizationController::recursiveOrganization(1,0);
                $data = ManagerData::getResponsables($id_org);
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

    public function createAreaResponsables($idSedeArea)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_anho = Input::get("id_anho");
            $id_nivel = Input::get("id_nivel");
            $id_persona = Input::get("id_persona");
            $activo = Input::get("activo");
            
            $person = ManagerData::validateExistResponsableAnho($idSedeArea,$id_anho,$id_persona);
            if(count($person) == 0){
                try {
    //                $idOrgSedeArea = ManagerData::generateIdValue('ORG_SEDE_AREA', 'ID_SEDEAREA');
                    $idOrgAreaResp = ManagerData::generateIdValue('ORG_AREA_RESPONSABLE', 'ID_RESPONSABLE');
    //                $areaSede = [
    //                    'id_sedearea' => $idOrgSedeArea,
    //                    'id_entidad' => $id_entidad,
    //                    'id_depto' => $id_depto,
    //                    'id_sede' => $id_sede,
    //                    'id_area' => $id_sedearea,
    //                    'estado' => $estado,
    //                    'id_persona' => $id_persona
    //                ];
                    $now = date('Y-m-d H:i:s');
                    $areaResp = [
                        'id_sedearea' => $idSedeArea,
                        'id_nivel' => $id_nivel,
                        'id_anho' => $id_anho,
                        'id_persona' => $id_persona,
                        'activo' => $activo,
                        'fecha' => $now
                    ];
                    $result = ManagerData::createResponsable($idSedeArea, $areaResp);
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
            }else{
                $jResponse['success'] = False;
                $jResponse['message'] = 'ERROR: La Persona ya esta Asignada';
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse, $code);
    }

    public function getAreaResponsable($id_area, $id_AreaResponsables)
    {
//        dd('getting');
//        dd('in hereee', $id_responsable,$id_responsables);
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ManagerData::getResponsable($id_AreaResponsables);
                $jResponse['success'] = true;
                if ($data) {
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

    public function updateAreaResponsable($idOganizacion, $idAreaResp)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_anho = Input::get("id_anho");
            $id_nivel = Input::get("id_nivel");
            $id_persona = Input::get("id_persona");
            $activo = Input::get("activo");
//            $id_depto = Input::get("id_depto");
//            $estado = Input::get("estado");
//            $id_sede = Input::get("id_sede");
            $id_sedearea = Input::get("id_sedearea");
            try {

                $data = [
//                    'id_responsable' => $idAreaResp,
                    'id_nivel' => $id_nivel,
                    'id_anho' => $id_anho,
                    'id_persona' => $id_persona,
                    'activo' => $activo
                ];

                $result = ManagerData::updateResponsable($idAreaResp, $data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $result;
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'No se pudo asignar responsable';
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

    public function deleteResponsables($id_responsable){
//        dd('incontrollter');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagerData::deleteResponsables($id_responsable);
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
    public function findWorker(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $txt_search = $request->query('text_search');
        $estado = $request->query('estado');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
//                $data = OrganizationController::recursiveOrganization(1,0);
                if($id_entidad === 7124) {
                    $data = ManagerData::findWorker($id_entidad ,$txt_search, $estado);
                } else {
                    $data = ManagerData::findPersonaNatural($txt_search);
                }
//                dd('HEREEEEEE');
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
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
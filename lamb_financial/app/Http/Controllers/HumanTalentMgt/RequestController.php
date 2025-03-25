<?php

namespace App\Http\Controllers\HumanTalentMgt;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\RequestData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{

    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getSolicitudes(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                /*$id_depto = $request->id_depto;
                $id_entidad = $request->id_entidad;
                $id_sedearea = $request->id_sedearea;*/
                //$id_estado_req = $request->id_estado_req;
                $anho = $request->anho;
                $per_page = $request->per_page;
                $object = RequestData::getSolicitudes($request, $id_user, $anho, $per_page);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getSolicitud(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_solicitud = $request->id_solicitud;
                $anho = $request->anho;
                $object = RequestData::getSolicitud($id_solicitud, $anho, url(''));
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getSugerenciasBySolicitud(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_solic_req = $request->id_solic_req;
                $anho = $request->anho;
                $object = RequestData::getSugerenciasBySolicitud($id_solic_req, url(''));
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getCantidadSolicitud(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_perfil_puesto = $request->id_perfil_puesto;
                $object = RequestData::getCantidadSolicitud($id_perfil_puesto);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function regRequest(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = RequestData::regRequest(json_encode($request->data), $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['id_solic_reque'] = $response['id_solic_reque'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function updRequest($id_solic_reque, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = RequestData::updRequest($id_solic_reque, json_encode($request->data), $request->comentario, $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function regSuggestRequest(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = RequestData::regSuggestRequest($request, $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function updSuggestRequest($id_solic_req_candidato, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = RequestData::updSuggestRequest($id_solic_req_candidato, json_encode($request->data), $request->comentario, $id_user);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function deleteSuggestRequest($id_solic_req_candidato)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = RequestData::deleteSuggestRequest($id_solic_req_candidato);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


    /**  */

    public function listApproved(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = RequestData::listApproved($request);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $jResponse['status'] = RequestData::listStatusContract($request);
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listStatusContract(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = RequestData::listStatusContract($request);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function selectRequest(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = RequestData::selectRequest($request);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Data Found";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
}

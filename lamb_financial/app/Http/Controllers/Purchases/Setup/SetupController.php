<?php

namespace App\Http\Controllers\Purchases\Setup;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Controllers\Purchases\Validations\PurchasesValidation;
use App\Http\Controllers\Purchases\Utils\PurchasesUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Data\GlobalMethods;
use Carbon\Carbon;
use PDO;
use Response;
use App\Http\Data\FinancesStudent\ComunData;
class SetupController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /* TYPE PROJECTS */
    public function listTypeProject()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listTypeProject($id_depto, $id_entidad);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    /* PROJECTS */
    public function listProject()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listProject($id_depto, $id_entidad);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addProject()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultFile = 'Sin file';
                $archivo = Input::file('proj_sustento');
                $carpeta = Input::get('carpeta');
                if ($archivo) {
                    // $resultFile = $this->saveFileVale(Input::get('id_proyect'), $params, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                    $fileAdjunto = ComunData::uploadFile($archivo, $carpeta);
                    Input::merge(['proj_sustento' => $fileAdjunto['filename']]);
                    Input::merge(['proj_extencion' => $archivo->getClientOriginalExtension()]);
                }
                $dataProject = PurchasesUtil::dataProject();
                if ($dataProject->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataProject->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddTypesTemplates;
                }
                $dataProject->data['id_entidad'] = $id_entidad;
                $dataProject->data['id_depto'] = $id_depto;
                $dataProject->data['estado'] = '1';
                $dataProject->data['id_proyecto'] = PurchasesData::getMax('proyecto', 'id_proyecto') + 1;
                $dataPjt = PurchasesData::addProject($dataProject->data);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [$dataProject->data];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddTypesTemplates:
        return response()->json($jResponse, $code);
    }
    public function showProject($id_project)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showProject($id_project)[0];
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function changeStateProject($idProject)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataPjt = PurchasesData::updateStateProject($idProject);

                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddTypesTemplates:
        return response()->json($jResponse, $code);
    }
    public function updateProject($idProject)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $resultFile = 'Sin file';
                // $archivo = Input::file('archivo_pdf');
                // $carpeta = Input::get('carpeta');
                // if ($archivo) {
                //     // $resultFile = $this->saveFileVale(Input::get('id_proyect'), $params, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                //     $fileAdjunto = ComunData::uploadFile($archivo, $carpeta);
                //     Input::merge(['sustento' => $fileAdjunto['filename']]);
                //     Input::merge(['extencion' => $archivo->getClientOriginalExtension()]);
                // }
                $dataProject = PurchasesUtil::dataProject();
                if ($dataProject->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataProject->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddTypesTemplates;
                }
                $dataProject->data['id_entidad'] = $id_entidad;
                $dataProject->data['id_depto'] = $id_depto;
                // $dataProject->data['estado'] = '1';
                // $dataProject->data['id_proyecto'] = PurchasesData::getMax('proyecto', 'id_proyecto') + 1;
                $dataPjt = PurchasesData::updateProject($dataProject->data,$idProject);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [$dataProject->data];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddTypesTemplates:
        return response()->json($jResponse, $code);
    }
/* PROJECTS ACUERDOS */
    public function listAcuerdos($idProject)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listProjectAcuerdos($idProject);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addAcuerdo()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $resultFile = 'Sin file';
                $archivo = Input::file('archivo_pdf');
                $carpeta = Input::get('carpeta');
                if ($archivo) {
                    // $resultFile = $this->saveFileVale(Input::get('id_proyect'), $params, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                    $fileAdjunto = ComunData::uploadFile($archivo, $carpeta);
                }
                $data = [];
                 
                $data['NRO_ACUERDO'] = Input::get('acuerdo_nro');
                $data['FECHA_ACUERDO'] = Input::get('acuerdo_fecha');
                $data['PRESUPUESTO'] = Input::get('acuerdo_presupuesto');
                $data['SUSTENTO'] = $fileAdjunto['filename'];
                $data['ID_PROYECTO'] = Input::get('id_proyect');
                $data['ID_PDETALLE'] = PurchasesData::getMax('proyecto_acuerdo', 'ID_PDETALLE') + 1;
                $data['EXTENCION'] = $archivo->getClientOriginalExtension();
                $dataPjt = PurchasesData::addAcuerdo($data);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [ $data];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [$data];
                $code = "400";
            }
        }
        end_AddTypesTemplates:
        return response()->json($jResponse, $code);
    }
    public function listComprasByProyecto($idProyecto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listComprasByProyecto($idProyecto);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    /* TIPO_PLANTILLA */
    public function listTypesTemplates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listTypesTemplates();
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showTypesTemplates($id_tipoplantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showTypesTemplates($id_tipoplantilla);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addTypesTemplates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $validTemplate = PurchasesValidation::validationTypesTemplates();
                if ($validTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddTypesTemplates;
                }
                $dataTemplate = PurchasesUtil::dataTypesTemplates();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddTypesTemplates;
                }
                $this->privateAddTypesTemplates($dataTemplate->data);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddTypesTemplates:
        return response()->json($jResponse, $code);
    }
    public function updateTypesTemplates($id_tipoplantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $validTemplate = PurchasesValidation::validationTypesTemplates();
                if ($validTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdateTypesTemplates;
                }
                $dataTemplate = PurchasesUtil::dataTypesTemplates();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdateTypesTemplates;
                }
                PurchasesData::updateTypesTemplates($dataTemplate->data, $id_tipoplantilla);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_UpdateTypesTemplates:
        return response()->json($jResponse, $code);
    }
    public function deleteTypesTemplates($id_tipoplantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deleteTypesTemplates($id_tipoplantilla);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    private function privateAddTypesTemplates($data)
    {
        try {
            $id_tipoplantilla = PurchasesData::getMax('tipo_plantilla', 'id_tipoplantilla') + 1;
            $data = array_merge(array("id_tipoplantilla" => $id_tipoplantilla), $data);
            $success = PurchasesData::addTypesTemplates($data);
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }
    /* COMPRA_PLANTILLA */
    public function listPurchasesTemplates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::listPurchasesTemplates($id_entidad, $id_depto, $id_user);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showPurchasesTemplates($id_plantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchasesTemplates($id_plantilla, $id_entidad, $id_depto, $id_user);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addPurchasesTemplates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $validTemplate = PurchasesValidation::validationPurchasesTemplates();
                if ($validTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddPurchasesTemplates;
                }
                $dataTemplate = PurchasesUtil::dataPurchasesTemplates();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddPurchasesTemplates;
                }
                $data = $dataTemplate->data;
                $data["id_entidad"] = $id_entidad;
                $this->privateAddPurchasesTemplates($data);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddPurchasesTemplates:
        return response()->json($jResponse, $code);
    }
    public function updatePurchasesTemplates($id_plantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $validTemplate = PurchasesValidation::validationPurchasesTemplates();
                if ($validTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdatePurchasesTemplates;
                }
                $dataTemplate = PurchasesUtil::dataPurchasesTemplates();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdatePurchasesTemplates;
                }
                $data = $dataTemplate->data;
                unset($data['fecha']);
                PurchasesData::updatePurchasesTemplates($data, $id_plantilla);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_UpdatePurchasesTemplates:
        return response()->json($jResponse, $code);
    }
    public function deletePurchasesTemplates($id_plantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deletePurchasesTemplates($id_plantilla);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    private function privateAddPurchasesTemplates($data)
    {
        try {
            $id_plantilla = PurchasesData::getMax('compra_plantilla', 'id_plantilla') + 1;
            $data = array_merge(array("id_plantilla" => $id_plantilla), $data);
            $success = PurchasesData::addPurchasesTemplates($data);
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }
    /* COMPRA_PLANTILLA_DETALLE */
    public function listPurchasesTemplateDetails()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_plantilla = Input::get("id_plantilla");
                $data = PurchasesData::listPurchasesTemplateDetails($id_plantilla);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showPurchasesTemplateDetails($id_pdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchasesTemplateDetails($id_pdetalle);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addPurchasesTemplateDetails()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dataTemplate = PurchasesUtil::dataPurchasesTemplateDetails();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddPurchasesTemplateDetails;
                }
                $data = $dataTemplate->data;
                $data["id_entidad"] = $id_entidad;
                $result = $this->privateAddPurchasesTemplateDetails($data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "OK";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddPurchasesTemplateDetails:
        return response()->json($jResponse, $code);
    }
    public function updatePurchasesTemplateDetails($id_pdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $validTemplate = PurchasesValidation::validationPurchasesTemplateDetails();
                if ($validTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdatePurchasesTemplateDetails;
                }
                $dataTemplate = PurchasesUtil::dataPurchasesTemplateDetails();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_UpdatePurchasesTemplateDetails;
                }
                PurchasesData::updatePurchasesTemplateDetails($dataTemplate->data, $id_pdetalle);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_UpdatePurchasesTemplateDetails:
        return response()->json($jResponse, $code);
    }
    public function deletePurchasesTemplateDetails($id_pdetalle)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deletePurchasesTemplateDetails($id_pdetalle);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    private function privateAddPurchasesTemplateDetails($data)
    {
        try {
            $id_pdetalle = PurchasesData::getMax('compra_plantilla_detalle', 'id_pdetalle') + 1;
            $data = array_merge(array("id_pdetalle" => $id_pdetalle), $data);
            $result = PurchasesData::addPurchasesTemplateDetails($data);
            if ($result) {
                return $data;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    /* COMPRA_ENTIDAD_DEPTO_PLANTILLA */
    public function listPurchasesEntityDeptoTemplates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_plantilla = Input::get("id_plantilla");
                $data = PurchasesData::listPurchasesEntityDeptoTemplates($id_plantilla, $id_entidad);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showPurchasesEntityDeptoTemplates($id_edplantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PurchasesData::showPurchasesEntityDeptoTemplates($id_edplantilla, $id_entidad);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addPurchasesEntityDeptoTemplates()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $validTemplate = PurchasesValidation::validationPurchasesEntityDeptoTemplates();
                if ($validTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $validTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddPurchasesEntityDeptoTemplates;
                }
                $dataTemplate = PurchasesUtil::dataPurchasesEntityDeptoTemplates();
                if ($dataTemplate->invalid) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataTemplate->message;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end_AddPurchasesEntityDeptoTemplates;
                }
                $data = $dataTemplate->data;
                $data["id_entidad"] = $id_entidad;
                $this->privateAddPurchasesEntityDeptoTemplates($data);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end_AddPurchasesEntityDeptoTemplates:
        return response()->json($jResponse, $code);
    }
    public function deletePurchasesEntityDeptoTemplates($id_edplantilla)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PurchasesData::deletePurchasesEntityDeptoTemplates($id_edplantilla);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    private function privateAddPurchasesEntityDeptoTemplates($data)
    {
        try {
            $id_edplantilla = PurchasesData::getMax('compra_entidad_depto_plantilla', 'id_edplantilla') + 1;
            $data = array_merge(array("id_edplantilla" => $id_edplantilla), $data);
            $success = PurchasesData::addPurchasesEntityDeptoTemplates($data);
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }
}

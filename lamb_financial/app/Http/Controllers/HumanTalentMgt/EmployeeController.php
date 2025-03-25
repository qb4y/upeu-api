<?php

namespace App\Http\Controllers\HumanTalentMgt;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\EmployeeData;
use App\Http\Data\HumanTalentMgt\ComunData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Support\Facades\DB;
use Session;
use DOMPDF;

class EmployeeController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    // PARA LA PARTE LOGICA
    // ------------------------------------------------------
    public  function personalInformation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];

            DB::beginTransaction();
            try {
                $return  =  EmployeeData::personalInformation($request);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['id_persona'] = $return['id_persona'];
                    $jResponse['abc'] = $return['message'];
                    $jResponse['id_tipoestadocivil'] = $return['id_tipoestadocivil'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    // Para registrar en el paso 2 del formulario
    public  function academicoSocial(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            DB::beginTransaction();
            try {
                $return  =  EmployeeData::academicoSocial($request);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    // para registrar los datos de la persona p3
    public  function dataFamily(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];

            DB::beginTransaction();
            try {
                $return = null;
                if ($request->regOrUpdate == "R") {
                    $return  =  EmployeeData::dataFamily($request);
                } else {
                    $return = EmployeeData::updateDataFamily($request);
                }
                // dd($return);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    // AREA DE LISTAS Y CARGADO DE DATA
    // lista de los trbaajadores
    public function listWorker(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $nombre = $request->nombre;
                $per_page = $request->per_page;
                $data = EmployeeData::listWorker($nombre, $per_page);
                // dd($data);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    /// buscar Persona en la primera vez
    public function searchFirsPerson(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            //$id_persona = $request->query('id_persona');
            $id_persona = $request->id_persona;
            if ($id_persona) {
                // $url = secure_url('');//Prod 
                $url = url(''); //Dev
                $data = EmployeeData::searchFirsPerson($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                //$jResponse['data'] = ['items' => $data];
                $jResponse['data'] = ['items' => $data];
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

    public function listDirecction(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->query('id_persona');
            if ($id_persona) {
                $data = EmployeeData::searchDireccion($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function addDireccion(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = EmployeeData::addDireccion($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
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
    public function deleteDireccion($id_direccion)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = EmployeeData::deleteDireccion($id_direccion);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Error al eliminar';
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

    public function deleteParentesco($id_vinculo_familiar)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = EmployeeData::deleteParentesco($id_vinculo_familiar);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
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


    public function updateDireccion($id_direccion, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = EmployeeData::updateDireccion($id_direccion, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
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



    public function updateAcademicoSocial($dni, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicoSocial($dni, $request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
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

    // para el paseo de academico y social
    // public function listWorkerSocial(Request $request)
    public function listWorkerAcademic(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $data = EmployeeData::listWorkerAcademic($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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


    // para lista de parentesco
    public function parentWorker(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            //$dni = $request->query('dni');
            $id_persona = $request->id_persona;
            if ($id_persona) {
                // $url = secure_url('');//Prod 
                $url = url(''); //Dev
                $data = EmployeeData::parentWorker($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function superiorWorker(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                // $url = secure_url('');//Prod 
                $url = url(''); //Dev
                $data = EmployeeData::parentSuperiorSonWorker($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function getParents(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $data = EmployeeData::getParents($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function updateParents(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $return  =  EmployeeData::updateParents($request);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function saveFile(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        //$json = json_decode($request);
        //$params = json_decode($request);

        /*$nombre = $params->nombre;*/
        //$nombreArchivo = $request->nombreArchivo;
        /*$archivo = $request->base64textString;
        $archivo = base64_decode($archivo);*/
        $code = "202";
        $valida = $jResponse["valida"];
        //$filePath = $_SERVER['DOCUMENT_ROOT'] . "lamb_financial/public/gthFiles/";
        $filePath = $_SERVER;
        /*$data = base64_encode(file_get_contents($filePath));*/
        //file_put_contents($filePath, $archivo);
        if ($valida == 'SI') {
            try {
                $jResponse = [];
                /*$jResponse['HOLA'] = $filePath;
                $jResponse['abv'] = $data;*/
                //$jk = $request->data;
                $jResponse['j'] = $filePath;
            } catch (Exception $e) {
                $jResponse['success'] = false;
            }
        }
        return response()->json($jResponse, $code);
    }

    public function deleteSuperior($id_item)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = EmployeeData::deleteSuperior($id_item);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Error al eliminar';
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

    public function hijoMayorSuperior(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $data = EmployeeData::hijoMayorSuperior($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public  function addSuperiorNivel(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];

            DB::beginTransaction();
            try {
                $return  =  EmployeeData::addSuperiorNivel($request);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function updateSuperiro(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::updateSuperiro($request);
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

    /// para actualizar laroal
    public function updateLabor(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::updateLabor($request);
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

    // para lista de aspecto laboral
    public function getAspectoLaboral(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->query('id_persona');
            if ($id_persona) {
                // $url = secure_url('');//Prod 
                $url = url(''); //Dev
                $data = EmployeeData::getAspectoLaboral($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    // trae lista de las cuentas bancarias
    public function getAccountBank(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->query('id_persona');
            if ($id_persona) {
                // $url = secure_url('');//Prod 
                $url = url(''); //Dev
                $data = EmployeeData::getAccountBank($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function addCtaBank(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $cuentas = $request->cuentas;
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $return  =  EmployeeData::addCtasBank($cuentas);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public  function addBank(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];

            DB::beginTransaction();
            try {
                $return  =  EmployeeData::addBank($request);
                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }


    public function deleteBank($id_pbancaria, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = EmployeeData::deleteBank($id_pbancaria, $request);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'error al eliminar';
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

    public function updateBank(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::updateBank($request);
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

    public function getInformationAcademic(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            $data = null;
            $info = null;
            if ($id_persona) {
                $url = url(''); //Dev
                $data = EmployeeData::getInformationAcademic($id_persona, $url);
                $info = EmployeeData::getInfoSitPN($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
                $jResponse['info'] = [$info];
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


    public function addInformationAcademic(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::addInformationAcademic($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                    DB::commit();
                    EmployeeData::setSituacionEducativa($request->id_persona);
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

    public function updateInformationAcademic(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::updateInformationAcademic($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $code = "200";
                    DB::commit();
                    EmployeeData::setSituacionEducativa($request->id_persona);
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

    public function getBasicFormation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $data = EmployeeData::getBasicFormation($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createBasicFormation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createBasicFormation($request->data);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $code = "200";
                    DB::commit();
                    EmployeeData::setSituacionEducativa($request->id_persona);
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

    public function updateBasicFormation($id_basic_formation, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateBasicFormation($id_basic_formation, $request->data);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
                    $code = "200";
                    DB::commit();
                    EmployeeData::setSituacionEducativa($request->id_persona);
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

    public function uploadFileAcademic(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::uploadFileAcademic($request);
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

    public function getAcademicTraining(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicTraining($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicTraining(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicTraining(json_encode($request->data));
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

    public function updateAcademicTraining($id_capacitacion, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicTraining($id_capacitacion, json_encode($request->data));
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

    public function getAcademicArticle(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicArticle($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicArticle(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicArticle(json_encode($request->data));
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

    public function updateAcademicArticle($id_articulo, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicArticle($id_articulo, json_encode($request->data));
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

    public function getAcademicProyection(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicProyection($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicProyection(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicProyection(json_encode($request->data));
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

    public function updateAcademicProyection($id_proyecto, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicProyection($id_proyecto, json_encode($request->data));
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

    public function getAcademicBook(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicBook($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicBook(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicBook(json_encode($request->data));
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

    public function updateAcademicBook($id_libro, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicBook($id_libro, json_encode($request->data));
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

    public function getAcademicAsesoria(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicAsesoria($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicAsesoria(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicAsesoria(json_encode($request->data));
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

    public function updateAcademicAsesoria($id_asesoria, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicAsesoria($id_asesoria, json_encode($request->data));
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

    public function getAcademicMembership(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicMembership($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicMembership(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicMembership(json_encode($request->data));
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

    public function updateAcademicMembership($id_membership, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicMembership($id_membership, json_encode($request->data));
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

    public function getAcademicJury(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicJury($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicJury(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicJury(json_encode($request->data));
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

    public function updateAcademicJury($id_jury, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicJury($id_jury, json_encode($request->data));
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


    public function getAcademicCategory(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicCategory($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicCategory(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicCategory(json_encode($request->data));
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

    public function updateAcademicCategory($id_categoria, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicCategory($id_categoria, json_encode($request->data));
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

    public function getAcademicRegime(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicRegime($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicRegime(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicRegime(json_encode($request->data));
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

    public function updateAcademicRegime($id_regimen, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicRegime($id_regimen, json_encode($request->data));
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


    public function getAcademicHour(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicHour($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicHour(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicHour(json_encode($request->data));
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

    public function updateAcademicHour($id_hour, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicHour($id_hour, json_encode($request->data));
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

    public function getAcademicPrize(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicPrize($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicPrize(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicPrize(json_encode($request->data));
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

    public function updateAcademicPrize($id_prize, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicPrize($id_prize, json_encode($request->data));
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

    public function getAcademicProfesional(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicProfesional($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicProfesional(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicProfesional(json_encode($request->data));
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

    public function updateAcademicProfesional($id_profesional, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicProfesional($id_profesional, json_encode($request->data));
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

    public function getAcademicExperience(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicExperience($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicExperience(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicExperience(json_encode($request->data));
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

    public function updateAcademicExperience($id_experience, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicExperience($id_experience, json_encode($request->data));
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

    public function getAcademicAdmin(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url('');
                $data = EmployeeData::getAcademicAdmin($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicAdmin(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicAdmin(json_encode($request->data));
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

    public function updateAcademicAdmin($id_admin, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicAdmin($id_admin, json_encode($request->data));
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

    public function getPersonData(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $url = url(''); //Dev
                $data = EmployeeData::getPersonData($id_persona, $url);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function regPersona(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::regPersona($request);
                if ($response['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
                    $jResponse['persona'] = $response['persona'];
                    $code = "200";
                    DB::commit();
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['persona'] = [];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['error_db'] = $e->getCode() . '---' . $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getAcademicLanguage(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            if ($id_persona) {
                $data = EmployeeData::getAcademicLanguage($id_persona);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
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

    public function createAcademicLanguage(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $response = EmployeeData::createAcademicLanguage(json_encode($request->data));
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

    public function updateAcademicLanguage($id_language, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            DB::beginTransaction();
            $jResponse = [];
            try {
                $response = EmployeeData::updateAcademicLanguage($id_language, json_encode($request->data));
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

    public function autorizathionDiezmoPdf(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];
            try {
                //$request->id_persona
                $firma_trabajador = '';
                $traba = ParameterData::getFirmaTrabajador($request->id_persona);
                if ($traba and $traba['nombre_firma'] and $traba['urls_dw']) {
                    $firma_trabajador = $traba['urls_dw'];
                }
                $pdf = DOMPDF::loadView('pdf.mgt.diezmo', [
                    'nombre_trabajador' => $request->nombre_trabajador,
                    'num_doc' => $request->num_doc,
                    'firma_trabajador' => $firma_trabajador,
                    'domicilio' => $request->domicilio,
                    'fecha' => $request->fecha
                ])->setPaper('a4', 'portrait');

                $doc =  base64_encode($pdf->stream('print.pdf'));
                if ($doc) {
                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items' => $doc]
                    ];
                } else {
                    $jResponse = [
                        'success' => false,
                        'message' => "Sin resultados",
                        'data' => ['items' => '']
                    ];
                }

                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'portrait');
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => false,
            'message' => $mensaje,
            'data' => ['items' => '']
        ];

        return response()->json($jResponse);
    }

    public function autorizathionCtaPdf(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];
            try {
                //$request->id_persona
                $firma_trabajador = '';
                $traba = ParameterData::getFirmaTrabajador($request->id_persona);
                if ($traba and $traba['nombre_firma'] and $traba['urls_dw']) {
                    $firma_trabajador = $traba['urls_dw'];
                }
                $pdf = DOMPDF::loadView('pdf.mgt.cuenta_sueldo', [
                    'nombre_trabajador' => $request->nombre_trabajador,
                    'num_doc' => $request->num_doc,
                    'firma_trabajador' => $firma_trabajador,
                    'domicilio' => $request->domicilio,
                    'fecha' => $request->fecha,
                    'banco' => $request->banco
                ])->setPaper('a4', 'portrait');

                $doc =  base64_encode($pdf->stream('print.pdf'));
                if ($doc) {
                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items' => $doc]
                    ];
                } else {
                    $jResponse = [
                        'success' => false,
                        'message' => "Sin resultados",
                        'data' => ['items' => '']
                    ];
                }

                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'portrait');
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => false,
            'message' => $mensaje,
            'data' => ['items' => '']
        ];
        return response()->json($jResponse);
    }
}

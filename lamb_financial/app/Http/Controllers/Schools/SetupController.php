<?php
namespace App\Http\Controllers\Schools;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Schools\Util\SetupUtil;
use App\Http\Data\Schools\SetupData;
use App\Http\Data\Schools\SchoolsData;
use App\Http\Data\Schools\GlobalMethodsInstitucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;
use Response;

class SetupController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function listPersonsSearch()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $texto = Input::get('texto');
                $data = SetupData::listPersonsSearch($texto);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listPersonsParentesco()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_persona = Input::get("id_persona");
                $data = SetupData::listPersonsParentesco($id_persona);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listPersonsEmergency()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_persona = Input::get('id_persona');
                $data = SetupData::listPersonsEmergency($id_persona);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPersonsEmergencyNone()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_persona = Input::get('id_persona');
                $texto = Input::get('texto');
                $data = SetupData::listPersonsEmergencyNone($id_persona, $texto);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPersonsEmergency()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsEmergency())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_pemergencia" => ''), SetupUtil::getData());
                $result = SetupData::addPersonsEmergency($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = array('id_pemergencia' => $result);
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePersonsEmergency($id_pemergencia)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $one = SetupData::deletePersonsEmergency($id_pemergencia);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not deleted.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listPersonsMobility()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_persona = Input::get('id_persona');
                $data = SetupData::listPersonsMobility($id_persona);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPersonsMobility()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsMobility())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_pmovilidad" => ''), SetupUtil::getData());
                $one = SetupData::addPersonsMobility($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePersonsMobility($id_pmovilidad)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $one = SetupData::deletePersonsMobility($id_pmovilidad);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not deleted.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPersonsdriversNone()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_persona = Input::get('id_persona');
                $texto = Input::get('texto');
                $data = SetupData::listPersonsdriversNone($id_persona, $texto);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPersonsDrivers()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pconductor = Input::get('id_pconductor');
                $data = [
                    'id_pconductor' => $id_pconductor,
                    'nro_licencia' => Input::get('nro_licencia'),
                    'placa' => Input::get('placa')
                ];
                $result = SetupData::addPersonsDrivers($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ['id_pconductor' => $id_pconductor];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function showRecordMedical($id_fmedica)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $data = SetupData::showRecordMedical($id_fmedica);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addRecordMedical()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validRecordMedical())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_fmedica" => ''), SetupUtil::getData());
                $result = SetupData::addRecordMedical($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = array('id_fmedica', $result);
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updateRecordMedical($id_fmedica)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validRecordMedical())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = SetupUtil::getData();
                $one = SetupData::updateRecordMedical($data, $id_fmedica);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function showPersonsAll($id_persona)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $data = SetupData::showPersonsAll($id_persona);
                if ($data)
                {
                    $direcciones = SetupData::listPersonsAddress($id_persona);
                    $documentos = SetupData::listPersonsDocument($id_persona);
                    $natural = SetupData::showPersonsNatural($id_persona);
                    $religion = SetupData::showPersonsNaturalReligion($id_persona);
                    $telefonos = SetupData::listPersonsTelephone($id_persona);
                    $virtuales = SetupData::listPersonsVirtual($id_persona);
                    $responsible = SetupData::showPersonsResponsible($id_persona);
                    $alumno = SetupData::showPersonsNaturalSchool($id_persona);
                    $data->direcciones = $direcciones;
                    $data->documentos = $documentos;
                    $data->natural = $natural;
                    $data->religion = $religion;
                    $data->telefonos = $telefonos;
                    $data->virtuales = $virtuales;
                    $data->responsible = $responsible;
                    $data->alumno = $alumno;
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPersons()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersons())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_persona" => ''), SetupUtil::getData());
                $one = SetupData::addPersons($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersons($id_persona)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersons())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersons(SetupUtil::getData(), $id_persona);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPersonsAddress()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsAddress())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_direccion" => ''), SetupUtil::getData());
                $one = SetupData::addPersonsAddress($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsAddress($id_direccion)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsAddress())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsAddress(SetupUtil::getData(), $id_direccion);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPersonsDocument()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsDocument())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = SetupUtil::getData();
                $one = SetupData::addPersonsDocument($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsDocument($num_docum)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsDocument())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsDocument(SetupUtil::getData(), $num_docum);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPersonsNatural()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsNatural())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $id_persona = Input::get('id_persona');
                $data = array_merge(array("id_persona" => $id_persona), SetupUtil::getData());
                $one = SetupData::addPersonsNatural($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsNatural($id_persona)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsNatural())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsNatural(SetupUtil::getData(), $id_persona);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPersonsNaturalReligion()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsNaturalReligion())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $id_persona = Input::get('id_persona');
                $data = array_merge(array("id_persona" => $id_persona), SetupUtil::getData());
                $one = SetupData::addPersonsNaturalReligion($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsNaturalReligion($id_persona)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsNaturalReligion())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsNaturalReligion(SetupUtil::getData(), $id_persona);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPersonsPhone()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsPhone())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_telefono" => ''), SetupUtil::getData());
                $one = SetupData::addPersonsPhone($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsPhone($id_telefono)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsPhone())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsPhone(SetupUtil::getData(), $id_telefono);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPersonsVirtual()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsVirtual())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_virtual" => ''), SetupUtil::getData());
                $one = SetupData::addPersonsVirtual($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsVirtual($id_telefono)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsVirtual())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsVirtual(SetupUtil::getData(), $id_telefono);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    /* PERSONA_NATURAL_SCHOOL */
    public function addPersonsNaturalSchool()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsNaturalSchool())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = array_merge(array("id_persona" => Input::get('id_persona')), SetupUtil::getData());
                $one = SetupData::addPersonsNaturalSchool($data);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePersonsNaturalSchool($id_persona)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                if(!SetupUtil::validPersonsNaturalSchool())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $one = SetupData::updatePersonsNaturalSchool(SetupUtil::getData(), $id_persona);
                if ($one)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $one;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_PERIODO */
    public function listPeriods()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_empresa = Input::get('id_empresa');
                $estado = Input::get('estado');
                $plan_confirmado = Input::get('plan_confirmado');
                $data = SetupData::listPeriods($id_empresa, $estado, $plan_confirmado);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showPeriods($id_periodo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = SetupData::showPeriods($id_periodo);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPeriods()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_empresa = Input::get('id_empresa');
                $data = [];
                $data['id_entidad'] = $id_entidad;
                $data['id_depto'] = $id_depto;
                $data['estado'] = 'P';
                $data['id_user'] = $id_user;
                $data['fecha_open'] = SchoolsData::getSysdate();
                $data['anho_periodo'] = SetupData::getNextYearPeriod($id_empresa);
                $data['id_empresa'] = $id_empresa;

                $result = SetupData::addPeriods($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result['id_periodo'];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePeriods($id_periodo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $estado = Input::get('estado');
                $data = [];
                $data['estado'] = $estado;
                $data['id_persona_close'] = $id_user;
                $data['fecha_close'] = SchoolsData::getSysdate();
                $data['id_persona_reopen'] = $id_user;

                $result = SetupData::updatePeriods($data, $id_periodo);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePeriodsMatricula($id_periodo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $es_matricula = Input::get('es_matricula');
                $data = [];
                $data['es_matricula'] = $es_matricula;
                if($es_matricula=='N'){
                    $data['id_persona_close_mat'] = $id_user;
                    $data['fecha_close_mat'] = SchoolsData::getSysdate();
                }else{
                    $data['id_persona_open_mat'] = $id_user;
                    $data['fecha_open_mat'] = SchoolsData::getSysdate();
                }
                $result = SetupData::updatePeriodsMatricula($data, $id_periodo);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePeriods($id_periodo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $one = SetupData::showPeriods($id_periodo);
                if(!$one)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $result = SetupData::deletePeriods($id_periodo);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not deleted.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function periodsConfirm($id_periodo)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $result = SetupData::periodsConfirm($id_periodo);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['msg_error'];
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function listPeriodsCalendar()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_periodo = Input::get('id_periodo');
                $data = SetupData::listPeriodsCalendar($id_periodo);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updatePeriodsCalendar($id_pcalendario)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $fecha_desde = Input::get('fecha_desde');
                $fecha_hasta = Input::get('fecha_hasta');
                $tipo = Input::get('tipo');
                if($tipo == 'desde')
                {
                    $data = [
                        'fecha_desde' => $fecha_desde,
                        'nro_semanas' => DB::raw("round((trunc(fecha_hasta)-trunc(to_date('$fecha_desde 00:00:01','YYYY/MM/DD hh24:mi:ss')))/7)")
                    ];
                }
                else
                {
                    $data = [
                        'fecha_hasta' => $fecha_hasta,
                        'nro_semanas' => DB::raw("round((trunc(to_date('$fecha_hasta 00:00:01','YYYY/MM/DD hh24:mi:ss'))-trunc(fecha_desde))/7)")
                    ];
                }
                $result = SetupData::updatePeriodsCalendar($data, $id_pcalendario);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function listPeriodsArea()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_periodo = Input::get('id_periodo');
                $data = SetupData::listPeriodsArea($id_periodo);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPeriodsArea()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = [
                    'id_curso' => Input::get('id_curso'),
                    'id_periodo' => Input::get('id_periodo'),
                    'id_cparent' => Input::get('id_cparent')
                ];
                $result = SetupData::addPeriodsArea($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result['id_pcurso'];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['msg_error'];
                    $jResponse['data']    = false;
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePeriodsArea($id_pcurso)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $result = SetupData::deletePeriodsArea($id_pcurso);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not deleted.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function excePAImportYearBefore()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_periodo = Input::get('id_periodo');
                $periodo = SetupData::showPeriods($id_periodo);
                if(!$periodo)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Periodo no encontrado.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $periodoBefore = SetupData::showBeforePeriods($id_periodo);
                if(!$periodoBefore)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe periodo anterior.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $periodoAreas = SetupData::listPeriodsArea($periodoBefore->id_periodo);
                $successs = 0;
                foreach($periodoAreas as $periodoArea)
                {
                    $data = [
                        'id_curso' => $periodoArea->id_curso,
                        'id_periodo' => $id_periodo,
                        'id_cparent' => $periodoArea->id_cparent
                    ];
                    $result = SetupData::addPeriodsArea($data);
                    if($result)
                    {
                        $successs++;
                    }
                }
                if ($successs > 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePlansStageGrade($id_pngrado)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = [
                    "tipo_nota" => Input::get('tipo_nota')
                ];
                $result = SetupData::updatePlansStageGrade($data, $id_pngrado);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was updated';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Item was not created.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPlansStageGradeArea()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pngrado = Input::get('id_pngrado');
                $id_curso = Input::get('id_curso');
                $id_cparent = Input::get('id_cparent');
                $horas = Input::get('horas');
                $data = [
                    'id_pngrado' => $id_pngrado,
                    'id_curso' => $id_curso,
                    'id_cparent' => $id_cparent,
                    'horas' => $horas,
                    'id_user' => $id_user
                ];
                $result = SetupData::addPlansStageGradeArea($data);
                if ($result['error'] == '0')
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ['id_pngcurso', $result['id_pngcurso']];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['msg_error'];
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePlansStageGradeArea($id_pngcurso)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = [
                    'id_pngcurso' => $id_pngcurso,
                    'horas' => Input::get('horas')
                ];
                $result = SetupData::updatePlansStageGradeArea($data);
                if ($result['error'] == '0')
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was updated';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['msg_error'];
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deletePlansStageGradeArea($id_pngcurso)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $pngcurso = SetupData::showPlansStageGradeArea($id_pngcurso);
                if(!$pngcurso)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Curso no existe.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = [
                    "horas" => Input::get('horas')
                ];
                $result = SetupData::deletePlansStageGradeArea($id_pngcurso);
                if ($result)
                {
                    $result_ = SetupData::updateSumPlansStageGrade($pngcurso->id_pngrado);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was deleted';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Item was not deleted.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function listPlansStageConfigEval()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_pnivel = Input::get('id_pnivel');
                $data = SetupData::listPlansStageConfigEval($id_pnivel);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $data;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPlansStageConfigEval()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $tipo_nota = Input::get('tipo_nota');
                $id_pnivel = Input::get('id_pnivel');
                $desde = Input::get('desde');
                $hasta = Input::get('hasta');
                $estado_nota = Input::get('estado_nota');
                $detalle = Input::get('detalle');
                $data = [
                    'tipo_nota' => $tipo_nota,
                    'id_pnivel' => $id_pnivel,
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'estado_nota' => $estado_nota,
                    'detalle' => $detalle,
                    'id_user' => $id_user,
                    'fecha_reg' => DB::raw("SYSDATE")
                ];
                $result = SetupData::addPlansStageConfigEval($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePlansStageConfigEval($id_pncnota)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $desde = Input::get('desde');
                $hasta = Input::get('hasta');
                $estado_nota = Input::get('estado_nota');
                $detalle = Input::get('detalle');
                $data = [
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'estado_nota' => $estado_nota,
                    'detalle' => $detalle
                ];
                $result = SetupData::updatePlansStageConfigEval($data, $id_pncnota);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was created';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Reservation was not created.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPlansStagesAll()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_periodo = Input::get('id_periodo');
                $list = SetupData::listPlansStages($id_periodo);
                if ($list)
                {
                    foreach ($list as $key => $row)
                    {
                        $list_ = SetupData::listPlansNGrade($row->id_pnivel);
                        $row->grados = $list_;
                        $list__ = SetupData::listPlansSAreas($row->id_pnivel);
                        foreach ($list__ as $key_ => $row_)
                        {
                            $list_ = SetupData::listPlansSGAreas_($row->id_pnivel, $row_->id_curso);
                            $list__[$key_]->cursos = $list_;
                        }
                        $row->cursos = $list__;
                        $list[$key] = $row;
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPlansSANone()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_pnivel = Input::get('id_pnivel');
                $one = SetupData::showPlansStages($id_pnivel);
                $list = SetupData::listPlansSANone($id_pnivel,  $one->id_periodo);
                if ($list)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPlansSAreas()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pnivel = Input::get('id_pnivel');
                $id_curso = Input::get('id_curso');
                $id_cparent = Input::get('id_cparent');
                $parent = Input::get('parent');
                $data = [
                    'id_pnivel' => $id_pnivel,
                    'id_curso' => $id_curso,
                    'id_cparent' => $id_cparent,
                    'parent' => $parent,
                    'id_user' => $id_user,
                    'fecha_reg' => DB::raw("SYSDATE")
                ];
                $result = SetupData::addPlansSAreas($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = 0;
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePlansSAreas($id_pncurso)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $row = SetupData::showPlansSAreas($id_pncurso);
                $result = SetupData::deletePlansSAreas($id_pncurso);
                if ($result)
                {
                    $list = SetupData::listPlansNGrade($row->id_pnivel);
                    foreach($list as $key => $value)
                    {
                        $result_ = SetupData::deletePlansStageGradeArea_($value->id_pngrado, $row->id_curso);
                        $result__ = SetupData::updateSumPlansStageGrade($value->id_pngrado);
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not deleted.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function listPeriodsStages()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_periodo = SetupData::showPeriodsOpen();
                $row = SchoolsData::getIdInstitucionByEntyDepto($id_entidad, $id_depto);
                $listPN = SetupData::listPeriodsStages($id_periodo, $row->id_institucion);
                if ($listPN)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $listPN;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPeriodsSGrades()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_pnivel = Input::get('id_pnivel');
                $list = SetupData::listPeriodsSGrades($id_pnivel);
                if ($list)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPeriodsSGSections()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_pngrado = Input::get('id_pngrado');
                $list = SetupData::listPeriodsSGSections($id_pngrado);
                if ($list)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPeriodsStagesAll()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_periodo = Input::get('id_periodo');
                $row = SchoolsData::getIdInstitucionByEntyDepto($id_entidad, $id_depto);
                $listPN = SetupData::listPeriodsStages($id_periodo, $row->id_institucion);
                if ($listPN)
                {
                    foreach ($listPN as $keyPN => $rowPN)
                    {
                        $listPNG = SetupData::listPeriodsNGrade($rowPN->id_pnivel);
                        foreach ($listPNG as $keyPNG => $rowPNG)
                        {
                            $listPNGS = SetupData::listPeriodsNGSection($rowPNG->id_pngrado);
                            $rowPNG->secciones = $listPNGS;
                            $listPNG[$keyPNG] = $rowPNG;
                        }
                        $rowPN->grados = $listPNG;
                        $listPN[$keyPN] = $rowPN;
                    }
                    $institucion = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad, $id_depto);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [ 'lista' => $listPN, 'institucion' => $institucion ];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [ 'lista' => [], 'institucion' => [ 'es_taller_electivo' => 'N'] ];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPeriodsNGNone()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_pnivel = Input::get('id_pnivel');
                $onePNivel = SetupData::showPeriodsStages($id_pnivel);
                $list = SetupData::listPeriodsNGNone($onePNivel->id_nivel, $onePNivel->id_periodo);
                if ($list)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPeriodsSGrades()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pnivel = Input::get('id_pnivel');
                $id_grado = Input::get('id_grado');
                $tipo_nota = Input::get('tipo_nota');
                $thoras = Input::get('thoras');
                $turno = Input::get('turno');
                $data = [
                    'id_pnivel' => $id_pnivel,
                    'id_grado' => $id_grado,
                    'tipo_nota' => $tipo_nota,
                    'thoras' => $thoras,
                    'turno' => $turno,
                    'id_user' => $id_user,
                    'nota_min_aprob' => 0,
                    'tnro_cupo' => 0,
                    'sincronizado' => '0'
                ];
                $result = SetupData::addPeriodsSGrades($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePeriodsSGrades($id_pngrado)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $result = SetupData::deletePeriodsNGSectionsByGrade($id_pngrado);
                $result = SetupData::deletePeriodsSGrades($id_pngrado);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function listPeriodsNGAreas()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $id_pngrado = Input::get('id_pngrado');
                $list = SetupData::listPeriodsNGAreas($id_pngrado);
                if ($list)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [];
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPeriodsNGAreaSync()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pngrado = Input::get('id_pngrado');
                $data = [
                    'id_pngrado' => $id_pngrado,
                    'id_user' => $id_user,
                    'id_entidad' => $id_entidad,
                    'id_depto' => $id_depto
                ];
                $result = SetupData::addPeriodsNGAreaSync($data);
                if ($result['error'] == '0')
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function addPeriodsStagesGradesSections()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pngrado = Input::get('id_pngrado');
                $id_seccion = Input::get('id_seccion');
                $data = [
                    'id_pngrado' => $id_pngrado,
                    'id_seccion' => $id_seccion,
                    'nro_cupo' => 0,
                    'id_user' => $id_user
                ];
                $result = SetupData::addPeriodsStagesGradesSections($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result['id_pngseccion'];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data']    = false;
                    $code                 = "202";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updatePeriodsStagesGradesSections($id_pngseccion)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = [
                    'nro_cupo' => Input::get('nro_cupo'),
                ];
                $result = SetupData::updatePeriodsNGSections($data, $id_pngseccion);
                if ($result)
                {
                    $one = SetupData::showPeriodsNGSection($id_pngseccion);
                    $suma = SetupData::sumPeriodsNGSectionsCupos($one->id_pngrado);
                    $dataPNG = [
                        'tnro_cupo' => $suma
                    ];
                    $resultOne = SetupData::updatePeriodsNGrades($dataPNG, $one->id_pngrado);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function deletePeriodsStagesGradesSections($id_pngseccion)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $one = SetupData::showPeriodsNGSection($id_pngseccion);
                $result = SetupData::deletePeriodsNGSections($id_pngseccion);
                if ($result)
                {
                    $suma = SetupData::sumPeriodsNGSectionsCupos($one->id_pngrado);
                    $dataPNG = [
                        'tnro_cupo' => $suma
                    ];
                    $resultOne = SetupData::updatePeriodsNGrades($dataPNG, $one->id_pngrado);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not updated.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data']    = [];
                $code                 = "400";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
}

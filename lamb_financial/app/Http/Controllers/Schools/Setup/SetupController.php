<?php
namespace App\Http\Controllers\Schools\Setup;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Schools\Setup\Util\SetupUtil;
use App\Http\Data\Schools\Setup\SetupData;
use App\Http\Data\Schools\SchoolsData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Data\GlobalMethods;
// use Carbon\Carbon;
use Response;

class SetupController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /* SCHOOL_CONFIG */
    public function listConfigs()
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
                $data = SetupData::listConfigs($id_entidad, $id_depto);
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
    public function showConfigs($id_config)
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
                $data = SetupData::showConfigs($id_entidad, $id_depto, $id_config);
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
    public function addConfigs()
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
                if(!SetupUtil::validConfigs())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_config" => ''), SetupUtil::getData());
                $data['id_entidad'] = $id_entidad;
                $data['id_depto'] = $id_depto;
                $result = SetupData::addConfigs($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateConfigs($id_config)
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
                if(!SetupUtil::validConfigs())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateConfigs(SetupUtil::getData(), $id_config, $id_entidad, $id_depto);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_NIVEL */
    public function listStages()
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
                $data = SetupData::listStages();
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
    public function showStages($id_nivel)
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
                $data = SetupData::showStages($id_nivel);
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
    public function addStages()
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
                if(!SetupUtil::validStages())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_nivel" => ''), SetupUtil::getData());
                $one = SetupData::addStages($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateStages($id_nivel)
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
                if(!SetupUtil::validStages())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $one = SetupData::updateStages(SetupUtil::getData(), $id_nivel);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_GRADO */
    public function listGrades()
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
                $data = SetupData::listGrades();
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
    public function showGrades($id_grado)
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
                $data = SetupData::showGrades($id_grado);
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
    public function addGrades()
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
                if(!SetupUtil::validGrades())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_grado" => ''), SetupUtil::getData());
                $result = SetupData::addGrades($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateGrades($id_grado)
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
                if(!SetupUtil::validGrades())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateGrades(SetupUtil::getData(), $id_grado);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_TIPODESCUENTO */
    public function listTypeDiscounts()
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
                $data = SetupData::listTypeDiscounts();
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
    public function showTypeDiscounts($id_tipodescuento)
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
                $data = SetupData::showTypeDiscounts($id_tipodescuento);
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
    public function addTypeDiscounts()
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
                if(!SetupUtil::validTypeDiscounts())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_tipodescuento" => ''), SetupUtil::getData());
                $one = SetupData::addTypeDiscounts($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateTypeDiscounts($id_tipodescuento)
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
                if(!SetupUtil::validTypeDiscounts())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateTypeDiscounts(SetupUtil::getData(), $id_tipodescuento);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_TIPOPAGO */
    public function listTypePayments()
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
                $data = SetupData::listTypePayments();
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
    public function showTypePayments($id_tipopago)
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
                $data = SetupData::showTypePayments($id_tipopago);
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
    public function addTypePayments()
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
                if(!SetupUtil::validTypePayments())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_tipopago" => ''), SetupUtil::getData());
                $result = SetupData::addTypePayments($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateTypePayments($id_tipopago)
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
                if(!SetupUtil::validTypePayments())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateTypePayments(SetupUtil::getData(), $id_tipopago);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_NIVEL_GRADO */
    public function listStagesGrades()
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
                $id_config = Input::get('id_config');
                $data = SetupData::listStagesGrades($id_entidad, $id_depto, $id_config);
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
    public function showStagesGrades($id_ngrado)
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
                $data = SetupData::showStagesGrades($id_entidad, $id_depto, $id_ngrado);
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
    public function addStagesGrades()
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
                if(!SetupUtil::validStagesGrades())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_ngrado" => ''), SetupUtil::getData());
                $one = SetupData::showConfigs($id_entidad, $id_depto, $data['id_config']);
                if(!$one)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Nivel no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::addStagesGrades($data, $id_entidad, $id_depto);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateStagesGrades($id_ngrado)
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
                if(!SetupUtil::validStagesGrades())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = SetupUtil::getData();
                $one = SetupData::showConfigs($id_entidad, $id_depto, $data['id_config']);
                if(!$one)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Nivel no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateStagesGrades($data, $id_ngrado, $id_entidad, $id_depto);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_VACANTE */
    public function listVacants()
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
                $one = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if($one)
                {
                    $id_config = Input::get('id_config');
                    $data = SetupData::listVacants($one->id_periodo, $id_config);
                }
                else
                {
                    $data = null;
                }
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
    public function showVacants($id_vacante)
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
                $one = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if($one)
                {
                    $data = SetupData::showVacants($id_vacante, $one->id_periodo);
                }
                else
                {
                    $data = null;
                }
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
    public function addVacants()
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
                if(!SetupUtil::validVacants())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_vacante" => ''), SetupUtil::getData());
                $oneP = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if(!$oneP)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Periodo no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data['id_periodo'] = $oneP->id_periodo;
                $oneNG = SetupData::showStagesGrades($id_entidad, $id_depto, $data['id_ngrado']);
                if(!$oneNG)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Grado no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::addVacants($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateVacants($id_vacante)
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
                if(!SetupUtil::validVacants())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = SetupUtil::getData();
                $oneP = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if(!$oneP)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Periodo no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $oneV = SetupData::showVacants($id_vacante, $oneP->id_periodo);
                if(!$oneV)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Vacante no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateVacants($data, $id_vacante, $oneP->id_periodo);
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
        end:
        return response()->json($jResponse,$code);
    }
    /* SCHOOL_CRITERIO */
    public function listCriterions()
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
                $one = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if($one)
                {
                    $id_config = Input::get('id_config');
                    $data = SetupData::listCriterions($one->id_periodo, $id_config);
                }
                else
                {
                    $data = null;
                }
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
    public function showCriterions($id_criterio)
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
                $one = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if($one)
                {
                    $data = SetupData::showCriterions($id_criterio, $one->id_periodo);
                }
                else
                {
                    $data = null;
                }
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
    public function addCriterions()
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
                if(!SetupUtil::validCriterions())
                { 
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = array_merge(array("id_criterio" => ''), SetupUtil::getData());
                $oneP = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if(!$oneP)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Periodo no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data['id_periodo'] = $oneP->id_periodo;
                $oneNG = SetupData::showStagesGrades($id_entidad, $id_depto, $data['id_ngrado']);
                if(!$oneNG)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Grado no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::addCriterions($data);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateCriterions($id_vacante)
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
                if(!SetupUtil::validVacants())
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = SetupUtil::getMessage();
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $data = SetupUtil::getData();
                $oneP = SchoolsData::getPeriodPostulant($id_entidad, $id_depto);
                if(!$oneP)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Periodo no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $oneV = SetupData::showVacants($id_vacante, $oneP->id_periodo);
                if(!$oneV)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Vacante no configurado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $result = SetupData::updateVacants($data, $id_vacante, $oneP->id_periodo);
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
        end:
        return response()->json($jResponse,$code);
    }
}

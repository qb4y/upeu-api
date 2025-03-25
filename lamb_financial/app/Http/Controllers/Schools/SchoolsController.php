<?php
namespace App\Http\Controllers\Schools;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Schools\Util\SchoolsUtil;
use App\Http\Data\Schools\SchoolsData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Schools\GlobalMethodsInstitucion; 
use Illuminate\Support\Facades\Storage;
use Response;

use Barryvdh\DomPDF\Facade as PDF;

class SchoolsController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function datosInstitution()
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
                $institucion  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad, $id_depto);
                if(!$institucion) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = '';
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data']    = ["institucion_id" => $institucion->id_institucion, 
                                         "institucion_nombre" => $institucion->nombre, 
                                        ];
                $code = "202";
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
    public function listAreas()
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
                $nombre = Input::get("nombre");
                $nombre_eq = Input::get("nombre_eq");
                $list = SchoolsData::listAreas($nombre, $nombre_eq);
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
    public function addAreas()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = [
                    'nombre' => Input::get('nombre'),
                    'abreviatura' => Input::get('abreviatura')
                ];
                $result = SchoolsData::addAreas($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result['id_curso'];
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
        return response()->json($jResponse,$code);
    }
    public function updateAreas($id_curso)
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
                    'nombre' => Input::get('nombre'),
                    'abreviatura' => Input::get('abreviatura')
                ];
                $result = SchoolsData::updateAreas($data, $id_curso);
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
    public function listPeriodsCheck()
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
                $id_alumno = Input::get('id_alumno');
                $id_periodo = Input::get('id_periodo');
                $data = [
                    'id_alumno' => $id_alumno,
                    'id_periodo' => $id_periodo
                ];
                $result = SchoolsData::listPeriodsCheck($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [ 'saldo' => $result['saldo'] ];
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
    public function listPeriodsPO()
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
                $plan_confirmado = Input::get('plan_confirmado');
                $data = SchoolsData::listPeriodsPO($plan_confirmado);
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
    public function listPeriodsOfEnrollments()
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
                $data = SchoolsData::listPeriodsOfEnrollments();
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
    public function listPeriodsAreasMissing()
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
                $id_periodo = Input::get("id_periodo");
                $nombre = Input::get("nombre");
                $list = SchoolsData::listPeriodsAreasMissing($id_periodo, $nombre);
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
    public function listMyPeriodsStages()
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
                $id_periodo = Input::get("id_periodo");
                $list = SchoolsData::listMyPeriodsStages($id_user, $id_periodo, $id_entidad, $id_depto);
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
    public function listMyPeriodsStagesGrades()
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
                $id_pnivel = Input::get("id_pnivel");
                $list = SchoolsData::listMyPeriodsStagesGrades($id_pnivel);
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
    public function listMyPeriodsStagesGradesSections()
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
                $id_pngrado = Input::get("id_pngrado");
                $list = SchoolsData::listMyPeriodsStagesGradesSections($id_pngrado);
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
    public function listMyPeriodsStagesGradesAreas()
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
                $id_pngrado = Input::get("id_pngrado");
                $id_pngseccion = Input::get("id_pngseccion");
                $list = SchoolsData::listMyPeriodsStagesGradesAreas($id_pngrado, $id_pngseccion);
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
    public function addPeriodsStagesGradesAreas()
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
                $data = [
                    'nombre' => Input::get('nombre'),
                    'id_pngrado' => Input::get('id_pngrado'),
                    'parent' => Input::get('parent'),
                    'id_user' => $id_user
                ];
                $result = SchoolsData::addPeriodsStagesGradesAreas($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [ 'id_pngcurso' => $result['id_pngcurso'] ];
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
    public function patchPeriodsStagesGradesAreasNroCupo($id_pngcurso)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $data = [
                    'nro_cupo' => Input::get('nro_cupo'),
                    'id_pngcurso' => $id_pngcurso
                ];
                $result = SchoolsData::patchPeriodsStagesGradesAreasNroCupo($data);
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
    public function patchPeriodsStagesGradesSectionsSave($id_pngseccion)
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
                $nombre = Input::get('nombre');
                $data = [
                    'id_pngseccion' => $id_pngseccion,
                    'nombre' => $nombre
                ];
                $result = SchoolsData::patchPeriodsStagesGradesSectionsSave($data);
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
    public function listPeriodsStudentsAreasBoth()
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
                $id_reserva = Input::get('id_reserva');
                $list = SchoolsData::listPeriodsStudentsAreasBoth($id_reserva);
                if ($list)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $list;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No hay cursos.';
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
    public function addPeriodsStudentsAreas()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_pngcurso = Input::get('id_pngcurso');
                $id_alumno = Input::get('id_alumno');
                $data = [
                    'id_pngcurso' => $id_pngcurso,
                    'id_alumno' => $id_alumno,
                ];
                $result = SchoolsData::addPeriodsStudentsAreas($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Ok.';
                    $jResponse['data']    = [];
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
    public function listPmesAreasTeachers()
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
                $id_pngseccion = Input::get("id_pngseccion");
                $id_pngcurso = Input::get("id_pngcurso");
                $list = SchoolsData::listPmesAreasTeachers($id_pngseccion, $id_pngcurso);
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
    public function listUnits()
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
                $id_pngcurso = Input::get("id_pngcurso");
                $id_pmes = Input::get("id_pmes");
                $parent = Input::get("parent");
                $list = SchoolsData::listUnits($id_pngcurso, $id_pmes, $parent);
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
    public function listSessions()
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
                $id_unidad = Input::get("id_unidad");
                $list = SchoolsData::listSessions($id_unidad);
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
    public function addSessions()
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
                    'id_unidad' => Input::get('id_unidad'),
                    'fecha' => Input::get('fecha'),
                    'hora_ini' => Input::get('hora_ini'),
                    'hora_fin' => Input::get('hora_fin'),
                    'titulo' => Input::get('titulo'),
                    'tema' => Input::get('tema'),
                    'id_user' => $id_user
                ];
                $data['hora_ini'] = str_replace(":", "", $data['hora_ini']);
                $data['hora_fin'] = str_replace(":", "", $data['hora_fin']);
                $result = SchoolsData::addSessions($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Session was created';
                    $jResponse['data']    = ['id_sesion' => $result['id_sesion']];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Session was not created.';
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
    public function listSessionsItems()
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
                $id_sesion = Input::get("id_sesion");
                $list = SchoolsData::listSessionsItems($id_sesion);
                if ($list)
                {
                    $listTree = recursivaItems(null, $list);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $listTree;
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
    public function addSessionsItems()
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
                    'descripcion' => Input::get('descripcion'),
                    'parent' => Input::get('parent'),
                    'id_sesion' => Input::get('id_sesion'),
                    'id_user' => $id_user
                ];
                $result = SchoolsData::addSessionsItems($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Session was created';
                    $jResponse['data']    = ['id_sitem' => $result['id_sitem']];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Session was not created.';
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
    public function updateSessionsItems($id_sitem)
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
                    'descripcion' => Input::get('descripcion')
                ];
                $result = SchoolsData::updateSessionsItems($data, $id_sitem);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Session was updated';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Session was not updated.';
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
    public function deleteSessionsItems($id_sitem)
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
                $data = SchoolsData::deleteSessionsItems($id_sitem);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
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
    public function listSessionsInstruments()
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
                $id_sesion = Input::get("id_sesion");
                $list = SchoolsData::listSessionsInstruments($id_sesion);
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
    public function listSessionsInstrumentsMissing()
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
                $id_sesion = Input::get("id_sesion");
                $list = SchoolsData::listSessionsInstrumentsMissing($id_sesion);
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
    public function addSessionsInstruments()
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
                    'id_sesion' => Input::get('id_sesion'),
                    'id_instrumento' => Input::get('id_instrumento')
                ];
                $result = SchoolsData::addSessionsInstruments($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Intrumento was created';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Intrumento was not created.';
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
    public function deleteSessionsInstruments($id_sesion, $id_instrumento)
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
                $data = SchoolsData::deleteSessionsInstruments($id_sesion, $id_instrumento);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
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
    public function listSessionsCriteria()
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
                $id_sesion = Input::get("id_sesion");
                $list = SchoolsData::listSessionsCriteria($id_sesion);
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
    public function listSessionsCriteriaMissing()
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
                $id_sesion = Input::get("id_sesion");
                $list = SchoolsData::listSessionsCriteriaMissing($id_sesion);
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
    public function addSessionsCriteria()
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
                    'id_sesion' => Input::get('id_sesion'),
                    'id_ucriterio' => Input::get('id_ucriterio')
                ];
                $result = SchoolsData::addSessionsCriteria($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Criterio was created';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Criterio was not created.';
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
    public function deleteSessionsCriteria($id_sesion, $id_ucriterio)
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
                $data = SchoolsData::deleteSessionsCriteria($id_sesion, $id_ucriterio);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
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
    public function listUnitsCompetencysEvaluations()
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
                $id_unidad = Input::get("id_unidad");
                $list = SchoolsData::listUnitsCompetencysEvaluations($id_unidad);
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
    public function listUnitsCompetencysByEvalsPeriods()
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
                $id_pngseccion = Input::get("id_pngseccion");
                $id_pngcurso = Input::get("id_pngcurso");
                $id_pmes = Input::get("id_pmes");
                $list = SchoolsData::listUnitsCompetencysByEvalsPeriods($id_pngseccion, $id_pngcurso, $id_pmes);
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
    public function listUnitsLearningsPrecisesEvaluations()
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
                $id_ucompetencia = Input::get("id_ucompetencia");
                $id_pngseccion = Input::get("id_pngseccion");
                $result = SchoolsData::listUnitsLearningsPrecisesEvaluations($id_ucompetencia, $id_pngseccion);
                if ($result)
                {
                    $alumnos = [];
                    foreach($result['alumnos'] as $key => $value)
                    {
                        $aprecisados = [];
                        $competencia = [];
                        foreach($result['lista'] as $key_lista => $value_lista)
                        {
                            if($value_lista['id_alumno'] == $value['id_alumno'])
                            {
                                if($value_lista['tipo'] == '1')
                                {
                                    $aprecisados[] = $value_lista;
                                }
                                else
                                {
                                    $competencia = $value_lista;
                                }
                            }
                        }
                        $value['aprecisados'] = $aprecisados;
                        $value['competencia'] = $competencia;
                        $alumnos[] = $value;
                    }
                    $resp_result = [
                        'aprecisados' => $result['aprecisados'],
                        'alumnos' => $alumnos
                    ];
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $resp_result;
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
    public function addUnitsLearningsPrecisesEvaluations()
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
                $data = [
                    'nota'=> Input::get("nota"),
                    'comentario'=> Input::get("comentario"),
                    'id_uaprecisado'=> Input::get("id_uaprecisado"),
                    'tipo'=> Input::get("tipo"),
                    'id_ucompetencia'=> Input::get("id_ucompetencia"),
                    'id_alumno'=> Input::get("id_alumno"),
                    'id_user'=> $id_user
                ];
                $result = SchoolsData::addUnitsLearningsPrecisesEvaluations($data);
                if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [ 'id_eunidad' => $result['id_eunidad'], 'evaluado' => $result['evaluado'] ];
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
    public function updateUnitsLearningsPrecisesEvaluations($id_eunidad)
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
                $data = [
                    'nota'=> Input::get("nota"),
                    'comentario'=> Input::get("comentario"),
                    'id_eunidad'=> $id_eunidad
                ];
                $result = SchoolsData::updateUnitsLearningsPrecisesEvaluations($data);
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
    public function uploadFile()
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
                $file = Input::file('file');
                if($file == null)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Fichero no encontrado.';
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                $nombreOriginal = $file->getClientOriginalName();
                $destinationPath = 'school/evidencias'; // purchases_files // school/evidencias // school/files // school\evidencias
                $nameRandon = getGenereNameRandom(17);
                $nombreDinamico = $nameRandon.".".$file->getClientOriginalExtension();
                $formato = strtoupper($file->getClientOriginalExtension());
                $size = $file->getSize();
                $ruta = $destinationPath."/".$nombreDinamico;
                $file->move($destinationPath, $nombreDinamico);

                $data = [
                    'id_file'=> 0,
                    'nombre'=> $nombreOriginal,
                    'formato'=> $formato,
                    'tamanho'=> $size,
                    'ruta'=> $ruta,
                    'id_user'=> $id_user,
                ];
                $result = SchoolsData::addFile($data);
                if ($result)// if ($result['error'] == 0)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [ 'id_file' => $result]; // [ 'id_file' => $result['id_file'] ];
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The items does not exist';
                    $jResponse['data']    = [ 'id_file' => 0];
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

    public function listReservations()
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
                $id_persona = Input::get("id_persona");
                $data = SchoolsData::listReservations($id_persona);
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
    public function listReservationsMyStudents()
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
                $one = SchoolsData::getPeriodPostulant();
                if(!$one)
                {
                    $id_periodo = 0;
                }
				
                else $id_periodo = $one->id_periodo;
                $data = SchoolsData::listReservationsMyStudents($id_user, $id_periodo);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listReservationsStudentsBoth()
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
                // $one = SchoolsData::getPeriodPostulant(); // DEPRECIADO - TALVEZ ????
                $texto = Input::get('texto');
                $data = SchoolsData::listReservationsStudentsBoth($id_entidad, $id_depto, $texto);
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
    public function listReservationsStudentsParent()
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
                $texto = Input::get('texto');
                $data = SchoolsData::listReservationsStudentsParent($id_entidad, $id_depto, $texto,$id_user);
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
    public function addReservations()
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
                $id_periodo = Input::get("id_periodo");
                // $one = SchoolsData::getPeriodPostulant();
                // if(!$one)
                // {
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = 'Periodo postulante sin abrir.';
                //     $jResponse['data']    = [];
                //     $code                 = "400";
                //     goto end;
                // }
                $id_alumno = Input::get("id_alumno");
                $alumno = SchoolsData::showStudents($id_alumno);
                if(!$alumno)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Alumno no existe.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $grado = SchoolsData::showPeriodsSGrades_($id_periodo, $alumno->id_nivel, $alumno->id_grado); // $one->id_periodo
                if(!$grado)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Grado no existe.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $hasVacant = SchoolsData::existsVacantGrade($grado->id_pngrado);
                if(!$hasVacant)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No hay vacante.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = [
                    "id_reserva" => "",
                    "id_periodo" => $id_periodo, // $one->id_periodo,
                    "id_alumno" => $id_alumno,
                    "id_institucion" => $alumno->id_institucion,
                    "fecha_reg" => SchoolsData::getSysdate(),
                    "estado" => 'FIC',
                    "id_persona" => Input::get("id_persona"),
                    "tipo" => '1',
                    "id_pngrado" => $grado->id_pngrado,
                    "id_resp_financiero" => $alumno->id_resp_financiero
                ];
                $result = SchoolsData::addReservations($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Reservation was created';
                    $jResponse['data']    = array('id_reserva' => $result);
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
        end:
        return response()->json($jResponse,$code);
    }
    public function patchReservations($id_reserva)
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
                $data = [];
                $tipo_pago = Input::get('tipo_pago');
                if($tipo_pago)
                {
                    $data['tipo_pago'] = $tipo_pago;
                }
                $estado = Input::get('estado');
                if($estado)
                {
                    $data['estado'] = $estado;
                }
                $caso_movilidad = Input::get('caso_movilidad');
                if($caso_movilidad)
                {
                    $data['caso_movilidad'] = $caso_movilidad;
                }
                $autoriza_foto = Input::get('autoriza_foto');
                if($autoriza_foto)
                {
                    $data['autoriza_foto'] = $autoriza_foto;
                }
                $result = SchoolsData::updateReservations($data, $id_reserva);
                if ($result)
                {
                    // if($estado == 'DOC') ????? VER TODO
                    // {
                    //     $reserva = SchoolsData::showReservations($id_reserva);
                    //     $dataMatricula = [
                    //         'id_alumno' => $reserva->id_alumno,
                    //         'id_pngrado' => $reserva->id_pngrado,
                    //         'id_periodo' => $reserva->id_periodo,
                    //         'id_resp_financiero' => $reserva->id_persona,
                    //         'id_pngseccion' => '1',
                    //         'estado' => '0'
                    //     ];
                    //     $result = SchoolsData::addMatricula($dataMatricula);
                    // }
                    $data2 = SchoolsData::showReservations2($id_reserva);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Reservation was updated.';
                    $jResponse['data']    = $data2;
                    $code                 = "200";
                }
                else
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Reservation was not updated.';
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
    public function patchReservationsPasoNext($id_reserva)
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
                $result = SchoolsData::patchReservationsPasoNext($id_reserva);
                if ($result['error'] == 0)
                {
                    $data = SchoolsData::showReservations2($id_reserva);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Ok.';
                    $jResponse['data']    = $data; // [ 'estado' => $result['estado'] ];
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
    public function listHospitalEssalud()
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
                $data = SchoolsData::listHospitalEssalud();
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listAllergys()
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
                $data = SchoolsData::listAllergys();
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
        end:
        return response()->json($jResponse,$code);
    }
    // ***** HOY UP
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
                $opc = Input::get("id_persona");
                if(!$opc)
                {
                    $opc = 'P';
                }
                $data = SchoolsData::listPersonsParentesco($id_persona, $opc);
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
    public function listEmployees()
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
                $dni = Input::get('dni');
                $data = SchoolsData::listEmployeesSearch($dni);
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
                    $jResponse['message'] = 'The items does not exist.';
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
    public function showPersonsManager($id_persona,$tipo_parentesco)
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
                $data = SchoolsData::showPersonsManager($id_persona,$tipo_parentesco);
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
                    $jResponse['message'] = 'The item does not exist';
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
    public function personaAdmision($id_persona)
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
                //verifica si existe en school_persona_familia
                if(SchoolsData::familiaByIdPersonExist($id_persona)){
                    $data = SchoolsData::familiaByIdPerson($id_persona);
                }else{
                    $data = SchoolsData::showPersonsManagerNotSchool($id_persona);
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
                    $jResponse['message'] = 'The item does not exist';
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
    public function personAdmisioShow($id_persona)
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
                $data = SchoolsData::showPersonAdmision($id_persona);
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
                    $jResponse['message'] = 'The item does not exist';
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
    public function listPersonsManager()
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
                $data = SchoolsData::listPersonsManager();
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
    public function listPersonsManagerSearch()
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
                $text = Input::get("text");
                $data = SchoolsData::listPersonsManagerSearch($text);
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
    public function addPersonsManager()
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
                $dataUtil = SchoolsUtil::dataPersonsNatural();
                if(!$dataUtil->valid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataUtil->message;
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $dataPDocumento = $dataUtil->dataPDocumento;
                    $validaDataUtilDocumento = SchoolsUtil::validationPersonDocument($dataPDocumento["id_tipodocumento"]);

                    if(!$validaDataUtilDocumento){
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Ingresó un numero de documento incorrecto. DNI: 8 dígitos,Carnet Extranjeria: 9 dígitos, Pasaporte: 9 dígitos";
                        $jResponse['data']    = [];
                        $code                 = "411";
                        goto end;
                    }else{
                        $existsFamiliaHijo=false;
                        //Buscar por DNI y retorna el ID_PERSONA
                        $idpersona=SchoolsData::idPersonByDocumentNum($dataPDocumento["id_tipodocumento"], $dataPDocumento["num_documento"]);
                        //verifica si existe en school_persona_familia como hijo
                        if(!empty($idpersona)){
                            $existsFamiliaHijo=SchoolsData::familiaHijoByIdPersonExist($idpersona->id_persona);
                        }
                        if($existsFamiliaHijo)//SI EXISTE PERSONA COMO HIJO RETORNARA EL MSG
                        {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "La persona ya esta registrado como hijo";
                            $jResponse['data']    = [];
                            $code                 = "405";
                            goto end;
                        }else{//SI NO EXISTE COMO HIJO CONTINUA
                            $dataPersona = $dataUtil->dataPersona;
                            //VALIDA SI EXISTE LA PERSONA DIFERENTE A HIJO, PUEDE SER PADRE, MADRE, APODERADO DE SCHOOL
                            $existsPersonDocument = SchoolsData::existsPersonsDocumentNum($dataPDocumento["id_tipodocumento"], $dataPDocumento["num_documento"],"");
                            if($existsPersonDocument){
                                $id_persona = $idpersona->id_persona;
                            }else{
                                //PARA NUEVA PERSONA QUE NO EXISTE EN MOISES.PERSONA NI EN SCHOOL
                                $result = SchoolsData::addPersons($dataPersona);
                                if($result){
                                    $id_persona = SchoolsData::getIdPersona();
                                }else{
                                    $jResponse['success'] = false;
                                    $jResponse['message'] = "Error.";
                                    $jResponse['data']    = [];
                                    $code                 = "202";
                                    goto end;
                                }
                            }
                            if(empty($id_persona))
                            {
                                $jResponse['success'] = false;
                                $jResponse['message'] = "Error.";
                                $jResponse['data']    = [];
                                $code                 = "202";
                                goto end;
                            }else{
                                $tipo_parentesco = Input::get("tipoparentesco_id");
                                $numero = Input::get("fam_numero");//VIENEN DEL FORMLULARIO DATOS EXISTE NUMERO DE FAMILIA
                                $dataSchoolDatoAdicional = [];
                                $dataSchoolDatoAdicional['id_hijo_hija']=Input::get("id_hijo");
                                $dataSchoolDatoAdicional["tipo_parentesco"]=$tipo_parentesco;
                                //NUEVO APODERADO DE LA FAMILIA A
                                $dataPVirtual = [];
                                $dataPTelefono = [];
                                $id_nivel_instruccion="";
                                $vive_con_estudiante="";
                                $da_exalumno="";
                                $da_exalumno_anho="";
                                $vive_con_padre="";
                                $vive_con_madre="";
                                $vive_con_apoderado="";
                                $id_resp_pago="";
                                $id_hijo="";
                                $id_hijo=Input::get("id_hijo");
                                if(!empty($id_hijo)){$id_hijo=$id_hijo;}


                                if(!$existsPersonDocument){
                                    $dataPNatural = array_merge(array("id_persona" => $id_persona), $dataUtil->dataPNatural);
                                    $resultPN = SchoolsData::addPersonsNatural($dataPNatural);

                                    $dataPDocumento = array_merge(array("id_persona" => $id_persona), $dataPDocumento);
                                    $resultPD = SchoolsData::addPersonsDocument($dataPDocumento);

                                    //addPersonsNaturalIdioma
                                    $dataPNaturalIdioma = array_merge(array("ID_PERSONA"=>$id_persona), $dataUtil->dataPNaturalIdioma);
                                    $dataPNaturalIdioma2 = array_merge(array("ID_PERSONA"=>$id_persona), $dataUtil->dataPNaturalIdioma2);
                                    if(!empty($dataPNaturalIdioma['id_tipoidioma'])){
                                        SchoolsData::personsNaturalIdiomaDeleteByIdPersona($id_persona);
                                        SchoolsData::addPersonsNaturalIdioma($dataPNaturalIdioma);
                                        SchoolsData::addPersonsNaturalIdioma($dataPNaturalIdioma2);
                                    }
                                }
                                $existsFamilia=SchoolsData::familiaByIdPersonExist($id_persona);
                                
                                $dataPSchoolReligion = array_merge(array("id_persona" => $id_persona), $dataUtil->dataPSchoolReligion);
                                if($dataPSchoolReligion['id_religion']!=""){
                                    if(!$existsFamilia){
                                        $resultPSchoolReligion = SchoolsData::addSchoolPersonsReligion($dataPSchoolReligion);
                                    }
                                }
                                if($tipo_parentesco!='03'){
                                    //addSchoolDatoAdicional
                                    $dataSchoolDatoAdicional['id_hijo_hija']=$id_hijo;
                                    $dataSchoolDatoAdicional['id_pmo']=$id_persona;
                                    $id_nivel_instruccion=Input::get('id_nivel_instruccion');
                                    $dataSchoolDatoAdicional["id_nivel_instruccion"]=$id_nivel_instruccion;
                                    $vive_con_estudiante=Input::get('da_viveconestudiante');
                                    $dataSchoolDatoAdicional["vive_con_estudiante"]=$vive_con_estudiante;
                                    $da_exalumno=Input::get('da_exalumno');
                                    $dataSchoolDatoAdicional["ex_alumno"]=$da_exalumno;
                                    $da_exalumno_anho=Input::get('da_exalumno_anho');
                                    $dataSchoolDatoAdicional["ex_alumno_anho"]=$da_exalumno_anho;

                                    if(!SchoolsData::existSchoolDatoAdicionalHijoPadre($id_hijo,$id_persona)){
                                        SchoolsData::addSchoolDatoAdicional($dataSchoolDatoAdicional);
                                    } 
                                    
                                        //CORREOS DESDE EL PADRE,MADRE,APODERADO
                                        //BORRA PARA LOS REGISTROS ACTUALES
                                        SchoolsData::deletePersonsVirtual($id_persona);

                                        $id_tipovirtual = Input::get('id_tipovirtual');
                                        $correo1 = Input::get('correo1');
                                        $correo2 = Input::get('correo2');
                                        $correo3 = Input::get('correo3');
                                        if(!empty($correo1)){
                                            $dataPVirtual = array("id_persona" => $id_persona,"direccion" =>$correo1,"id_tipovirtual"=>$id_tipovirtual);
                                            if(!$existsPersonDocument){
                                                SchoolsData::addPersonsVirtual($dataPVirtual);
                                            }
                                        }
                                        if(!empty($correo2)){
                                            $dataPVirtual = array("id_persona" => $id_persona,"direccion" =>$correo2,"id_tipovirtual"=>$id_tipovirtual);
                                            if(!$existsPersonDocument){
                                                SchoolsData::addPersonsVirtual($dataPVirtual);
                                            }
                                        }
                                        if(!empty($correo3)){
                                            $dataPVirtual = array("id_persona" => $id_persona,"direccion" =>$correo3,"id_tipovirtual"=>$id_tipovirtual);
                                            if(!$existsPersonDocument){
                                                SchoolsData::addPersonsVirtual($dataPVirtual);
                                            }
                                        }

                                        //TELEFONOS DESDE EL PADRE,MADRE,APODERADO
                                        //BORRA PARA LOS REGISTROS ACTUALES
                                        SchoolsData::personsTelefonoDeleteByIdPersona($id_persona);

                                        $movil1 = Input::get('movil1');
                                        $movil2 = Input::get('movil2');
                                        $operador_movil = Input::get('operador_movil');
                                        $operador_movil2 = Input::get('operador_movil2');
                                        if(!empty($movil1)){
                                        $dataPTelefono = array("id_telefono" => "", "id_persona" => $id_persona,"num_telefono"=>$movil1,"operador_movil"=>$operador_movil);
                                            if(!$existsPersonDocument){
                                                SchoolsData::addPersonsTelefono($dataPTelefono); 
                                            }
                                        }
                                        if(!empty($movil2)){
                                            $dataPTelefono = array("id_telefono" => "", "id_persona" => $id_persona,"num_telefono"=>$movil2,"operador_movil"=>$operador_movil2);
                                            if(!$existsPersonDocument){
                                                SchoolsData::addPersonsTelefono($dataPTelefono);
                                            }
                                        }
                                        //VIVIENDA DESDE EL PADRE,MADRE,APODERADO

                                        //LABORAL DESDE EL PADRE,MADRE,APODERADO
                                        //SI NO EXISTE EN SCHOOL FAMILIA
                                        
                                        $dataPSchoolLaboral =  array_merge(array("id_persona" => $id_persona), $dataUtil->dataPSchoolLaboral);
                                        if(!SchoolsData::existSchoolPersonsLaboral($id_persona)){
                                            $resultPSchoolLaboral = SchoolsData::addSchoolPersonsLaboral($dataPSchoolLaboral);
                                        }else{
                                            SchoolsData::updateSchoolPersonsLaboral($dataPSchoolLaboral,$id_persona);
                                        }
                                        
                                    
                                        //VIVIENDA DESDE EL PADRE,MADRE,APODERADO
                                        $dataPSchoolVivienda = array_merge(array("id_persona" => $id_persona,"id_pais"=>(int)Input::get('v_id_pais')), $dataUtil->dataPSchoolVivienda);
                                        if(!SchoolsData::existSchoolPersonsVivienda($id_persona)){
                                            $resultPSchoolVivienda = SchoolsData::addSchoolPersonsVivienda($dataPSchoolVivienda);
                                        }
                                        $id_persona_hijo_familia=Input::get('id_persona_hijo_familia');
                                        if(!empty($id_persona_hijo_familia)){$id_persona_hijo_familia=$id_persona_hijo_familia;}else{$id_persona_hijo_familia="";}
                                        $dataPSchoolFamily = array_merge(array("id_persona_hijo" =>$id_persona_hijo_familia,"id_persona" => $id_persona,"codigo"=>"FAM","numero"=>$numero,"fecha_registro"=>SchoolsData::getSysdate(),"tipoparentesco_id"=>$tipo_parentesco), $dataUtil->dataPSchoolFamily);
                                        $resultPSchoolFamily = SchoolsData::addSchoolPersonsFamily($dataPSchoolFamily);

                                        $dataPSchoolParentesco = array("id_persona" => $id_persona,"parent"=>null,"tipoparentesco_id"=>$tipo_parentesco);
                                        $resultPV = SchoolsData::addSchoolPersonsParentesco($dataPSchoolParentesco);                 
                                        //TRAEMOS TODOS LOS HIJOS DE LA FAMILIA A 
                                    
                                        $listaHijos=SchoolsData::listFamilySon($numero);
                                        foreach($listaHijos as $row){
                                            $dataPSchoolParentescoSon = array("id_persona" => $row->id_persona,"parent"=>$id_persona,"tipoparentesco_id"=>$row->tipoparentesco_id);
                                            //INSERTAMOS LOS HIJOS DE LA FAMILIA A AL PADRE DE LA FAMILIA A DEL TIPO HIJO
                                            $resultPV = SchoolsData::addSchoolPersonsParentesco($dataPSchoolParentescoSon);
                                        }
                                        
                                }else{
                                    //addSchoolDatoAdicional
                                    $vive_con_padre=Input::get('vive_con_padre');
                                    $dataSchoolDatoAdicional["vive_con_padre"]= $vive_con_padre;
                                    $vive_con_madre=Input::get('vive_con_madre');
                                    $dataSchoolDatoAdicional["vive_con_madre"]=$vive_con_madre;
                                    $vive_con_apoderado=Input::get('vive_con_apoderado');
                                    $dataSchoolDatoAdicional["vive_con_apoderado"]=$vive_con_apoderado;
                                    $id_resp_pago=Input::get('id_resp_pago');
                                    $dataSchoolDatoAdicional["id_resp_pago"]=$id_resp_pago;
                                    $dataSchoolDatoAdicional["id_hijo_hija"]=$id_persona;
                                    $resultPNaturalSchool = SchoolsData::addSchoolDatoAdicional($dataSchoolDatoAdicional);
                                    //CORREO DESDE EL HIJO

                                    //BORRA PARA LOS REGISTROS ACTUALES
                                    SchoolsData::deletePersonsVirtual($id_persona);

                                    $id_tipovirtual = Input::get('id_tipovirtual');
                                    $edireccion = Input::get('edireccion');
                                    
                                    $dataPVirtual = array("id_virtual" => "","id_persona" => $id_persona,"direccion" =>$edireccion,"id_tipovirtual"=>$id_tipovirtual);
                                    if(!empty($edireccion)){
                                        if(!SchoolsData::existPersonsVirtualByEmail($edireccion)){
                                            $resultPV = SchoolsData::addPersonsVirtual($dataPVirtual); 
                                        } 
                                    }
                                    //VIVIENDA DESDE EL HIJO
                                    $dataPSchoolVivienda["direccion"] = Input::get('v_direccion');
                                    $dataPSchoolVivienda = array_merge(array("id_persona" => $id_persona), $dataUtil->dataPSchoolVivienda);
                                    $resultPSchoolVivienda = SchoolsData::addSchoolPersonsVivienda($dataPSchoolVivienda);

                                    $dataPSchoolFamily = array_merge(array("id_persona" => $id_persona,"codigo"=>"FAM","fecha_registro"=>SchoolsData::getSysdate(),"tipoparentesco_id"=>$tipo_parentesco), $dataUtil->dataPSchoolFamily);
                                   
                                    if(!empty($numero)){
                                        $resultPSchoolFamily = SchoolsData::addSchoolPersonsFamilySon($dataPSchoolFamily,$numero);
                                        $dataPersona["codigo_familia"] = $numero;
                                    }else{
                                        $resultPSchoolFamily = SchoolsData::addSchoolPersonsFamilySon($dataPSchoolFamily, null);
                                        $dataPersona["codigo_familia"] = SchoolsData::getNumero();
                                    } 
                                }
                                
                                $dataPersona["id_resp_pago"] = $id_resp_pago;
                                $dataPersona["id_persona"] = $id_persona;
                                $jResponse['success'] = true;
                                $jResponse['message'] = 'OK';
                                $jResponse['data']    = $dataPersona;
                                $code                 = "200";
                            }
                        }
                    }
                    
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
    public function editPersonsManager($id_persona)
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
                $dataUtil = SchoolsUtil::dataPersonsNatural();
                if(!$dataUtil->valid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataUtil->message;
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $dataPDocumento = $dataUtil->dataPDocumento;//DOCUMENTO
                
                    $dataPersona = $dataUtil->dataPersona;
                    $result = SchoolsData::updatePersons($dataPersona,$id_persona);
                    if(!$result)
                    {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Error.";
                        $jResponse['data']    = [];
                        $code                 = "202";
                        goto end;
                    }else{
                        $tipo_parentesco = Input::get("tipoparentesco_id");

                        $dataPNatural = $dataUtil->dataPNatural;
                        $resultPN = SchoolsData::updatePersonsNatural($dataPNatural,$id_persona);
                        if(!$resultPN)
                        {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "Error.";
                            $jResponse['data']    = [];
                            $code                 = "202";
                            goto end;
                        }
                        $dataSchoolDatoAdicional = [];
                        $resultDatoAdicional="";
                        $id_padre ="";
                        $id_hijo="";
                        
                        $id_nivel_instruccion="";
                        $da_viveconestudiante="";
                        $da_exalumno="";
                        $vive_con_padre="";
                        $vive_con_madre="";
                        $vive_con_apoderado="";
                        $id_resp_pago="";


                        $dataPDocumento =  $dataUtil->dataPDocumento;
                        $resultPD = SchoolsData::updatePersonsDocument($dataPDocumento,$id_persona);
                        $numero = Input::get("fam_numero");//VIENEN DEL FORMLULARIO DATOS EXISTE NUMERO DE FAMILIA
                        
                        
                        $dataPNaturalIdioma = array_merge(array("ID_PERSONA"=>$id_persona), $dataUtil->dataPNaturalIdioma);
                        $dataPNaturalIdioma2 = array_merge(array("ID_PERSONA"=>$id_persona), $dataUtil->dataPNaturalIdioma2);
                        if(!empty($dataPNaturalIdioma['id_tipoidioma'])){
                            SchoolsData::personsNaturalIdiomaDeleteByIdPersona($id_persona);
                            SchoolsData::addPersonsNaturalIdioma($dataPNaturalIdioma);
                            SchoolsData::addPersonsNaturalIdioma($dataPNaturalIdioma2);
                        }
                        $id_hijo=Input::get("id_hijo");
                        if(empty($id_hijo)){$id_hijo=$id_persona;}else{$id_hijo=$id_hijo;}

                        $id_nivel_instruccion=Input::get('id_nivel_instruccion');
                        $da_viveconestudiante=Input::get('da_viveconestudiante');
                        $da_exalumno=Input::get('da_exalumno');

                        $vive_con_padre=Input::get('vive_con_padre');
                        $vive_con_madre=Input::get('vive_con_madre');
                        $vive_con_apoderado=Input::get('vive_con_apoderado');
                        $id_resp_pago=Input::get('id_resp_pago');
                        
                        $dataSchoolDatoAdicional["tipo_parentesco"]=$tipo_parentesco;
                        
                        $dataPSchoolReligion =  $dataUtil->dataPSchoolReligion;
                        $schoolPersonsReligionByIdPersona=SchoolsData::schoolPersonsReligionByIdPersona($id_persona,$dataPSchoolReligion['id_religion']);
                        if($schoolPersonsReligionByIdPersona){
                            $resultPSchoolReligion = SchoolsData::updateSchoolPersonsReligion($dataPSchoolReligion,$id_persona);
                        }else{
                            $dataPSchoolReligion = array_merge(array("id_persona" => $id_persona), $dataUtil->dataPSchoolReligion);
                            if($dataPSchoolReligion['id_religion']!=""){
                                $resultPSchoolReligion = SchoolsData::addSchoolPersonsReligion($dataPSchoolReligion);
                            }
                        }
                        
                        //UPDATE APODERADO DE LA FAMILIA A
                        $dataPVirtual = [];
                        if($tipo_parentesco!='03'){
                            //addSchoolDatoAdicional
                            $id_padre=$id_persona;
                            
                            $dataSchoolDatoAdicional["id_nivel_instruccion"]=$id_nivel_instruccion;
                            $dataSchoolDatoAdicional["vive_con_estudiante"]=$da_viveconestudiante;
                            $dataSchoolDatoAdicional["ex_alumno"]=$da_exalumno;

                            if(SchoolsData::existSchoolDatoAdicionalHijoPadre($id_hijo,$id_padre)){
                                $resultDatoAdicional = SchoolsData::updateSchoolDatoAdicional($dataSchoolDatoAdicional,$id_hijo,$id_padre);  
                             }else if(!empty($id_hijo) && !empty($id_padre)){
                                 $dataSchoolDatoAdicional["id_hijo_hija"]=$id_hijo;
                                 $dataSchoolDatoAdicional["id_pmo"]=$id_padre;
                                 $resultDatoAdicional = SchoolsData::addSchoolDatoAdicional($dataSchoolDatoAdicional);  
                            }

                            if(!$resultDatoAdicional)
                            {
                                $jResponse['success'] = false;
                                $jResponse['message'] = "Error.";
                                $jResponse['data']    = [];
                                $code                 = "202";
                                goto end;
                            }

                           if(!empty($id_persona)){
                                //BORRA PARA LOS REGISTROS ACTUALES
                                SchoolsData::deletePersonsVirtual($id_persona);
                                //CORREOS DESDE EL PADRE,MADRE,APODERADO
                                $id_tipo_virtual = Input::get('id_tipovirtual');
                                $correo1 = Input::get('correo1');
                                $correo2 = Input::get('correo2');
                                $correo3 = Input::get('correo3');

                                $dataPVirtual1 = array("id_persona"=>$id_persona,"id_virtual"=>$id_tipo_virtual,"direccion" =>$correo1);
                                if(!empty($correo1)){
                                    SchoolsData::addPersonsVirtual($dataPVirtual1);
                                }
                                $dataPVirtual2 = array("id_persona"=>$id_persona,"id_virtual"=>$id_tipo_virtual,"direccion" =>$correo2);
                                if(!empty($correo2)){
                                    SchoolsData::addPersonsVirtual($dataPVirtual2);
                                }
                                $dataPVirtual3 = array("id_persona"=>$id_persona,"id_virtual"=>$id_tipo_virtual,"direccion" =>$correo3);
                                if(!empty($correo3)){
                                    SchoolsData::addPersonsVirtual($dataPVirtual3);
                                }
                                //TELEFONOS DESDE EL PADRE,MADRE,APODERADO
                                SchoolsData::personsTelefonoDeleteByIdPersona($id_persona);
                                    
                                $operador_movil = Input::get('operador_movil1');
                                //$id_telefono1 = Input::get('id_telefono1');
                                $movil1 = Input::get('movil1');
                                $operador_movil2 = Input::get('operador_movil2');
                                // $id_telefono2 = Input::get('id_telefono2');
                                $movil2 = Input::get('movil2');
                                
                                $dataPTelefono = array("id_persona"=>$id_persona,"id_tipotelefono"=>9,"num_telefono"=>$movil1,"operador_movil"=>$operador_movil);
                                SchoolsData::addPersonsTelefono($dataPTelefono);
                                
                                $dataPTelefono = array("id_persona"=>$id_persona,"id_tipotelefono"=>5,"num_telefono"=>$movil2,"operador_movil"=>$operador_movil2);
                                SchoolsData::addPersonsTelefono($dataPTelefono);
                                
                            }
                            //LABORAL DESDE EL PADRE,MADRE,APODERADO
                            
                            if(SchoolsData::existSchoolPersonsLaboral($id_persona)){
                                $dataPSchoolLaboral =  $dataUtil->dataPSchoolLaboral;
                                $resultPSchoolLaboral = SchoolsData::updateSchoolPersonsLaboral($dataPSchoolLaboral,$id_persona);
                            }else{
                                $dataPSchoolLaboral=array_merge(array("id_persona"=>$id_persona),$dataUtil->dataPSchoolLaboral);
                                $resultPSchoolLaboral = SchoolsData::addSchoolPersonsLaboral($dataPSchoolLaboral);
                            }

                            //VIVIENDA DESDE EL PADRE,MADRE,APODERADO 
                            
                            if(SchoolsData::existSchoolPersonsVivienda($id_persona)){
                                $dataPSchoolVivienda = array_merge(array("id_pais"=>(int)Input::get('v_id_pais')), $dataUtil->dataPSchoolVivienda);
                                $resultPSchoolVivienda = SchoolsData::updateSchoolPersonsVivienda($dataPSchoolVivienda,$id_persona); 
                            }else{
                                $dataPSchoolVivienda = array_merge(array("id_persona"=>$id_persona,"id_pais"=>(int)Input::get('v_id_pais')), $dataUtil->dataPSchoolVivienda);
                                $resultPSchoolVivienda = SchoolsData::addSchoolPersonsVivienda($dataPSchoolVivienda);
                            }
                            //VALIDA: SI EL PARIENTE EXISTE UPDATE NOMBRE FAMILIA
                            $datosFamilia=SchoolsData::familiaByIdPersonaAndNumero($id_persona,$numero);
                            if(!$datosFamilia){
                            //VALIDA: SI EL PARIENTE NO EXISTE INSERT FAMILIA
                                $id_persona_hijo_familia=Input::get('id_persona_hijo_familia');

                                if(!empty($id_persona_hijo_familia)){$id_persona_hijo_familia=$id_persona_hijo_familia;}else{$id_persona_hijo_familia=$id_hijo;}

                                $dataPSchoolFamily = array_merge(array("id_persona_hijo" =>$id_persona_hijo_familia,"codigo"=>"FAM","numero"=>$numero,"fecha_registro"=>SchoolsData::getSysdate(),"tipoparentesco_id"=>$tipo_parentesco), $dataUtil->dataPSchoolFamily);
                                $resultPSchoolFamily = SchoolsData::addSchoolPersonsFamily($dataPSchoolFamily);

                                $dataPSchoolParentesco = array("id_persona" => $id_persona,"parent"=>null,"tipoparentesco_id"=>$tipo_parentesco);
                                $resultPV = SchoolsData::addSchoolPersonsParentesco($dataPSchoolParentesco);

                                //TRAEMOS TODOS LOS HIJOS DE LA FAMILIA A 
                                $listaHijos=SchoolsData::listFamilySon($numero);
                                foreach($listaHijos as $row){
                                    $dataPSchoolParentescoSon = array("id_persona" => $row->id_persona,"parent"=>$id_persona,"tipoparentesco_id"=>$row->tipoparentesco_id);
                                    //INSERTAMOS LOS HIJOS DE LA FAMILIA A AL PADRE DE LA FAMILIA A DEL TIPO HIJO
                                    $resultPV = SchoolsData::addSchoolPersonsParentesco($dataPSchoolParentescoSon);
                                }
                            } 
                        }else{
                            $dataSchoolDatoAdicional["vive_con_padre"]=$vive_con_padre;
                            $dataSchoolDatoAdicional["vive_con_madre"]=$vive_con_madre;
                            $dataSchoolDatoAdicional["vive_con_apoderado"]=$vive_con_apoderado;
                            $dataSchoolDatoAdicional["id_resp_pago"]=$id_resp_pago;

                            if(SchoolsData::existSchoolDatoAdicionalHijo($id_hijo) && empty($id_padre)){
                                $resultDatoAdicional = SchoolsData::updateSchoolDatoAdicional($dataSchoolDatoAdicional,$id_hijo,"");  
                            }else if(!empty($id_hijo) && empty($id_padre)){
                                $dataSchoolDatoAdicional["id_hijo_hija"]=$id_hijo;
                                $resultDatoAdicional = SchoolsData::addSchoolDatoAdicional($dataSchoolDatoAdicional);  
                            }
                            
                            if(!$resultDatoAdicional)
                            {
                                $jResponse['success'] = false;
                                $jResponse['message'] = "Error.";
                                $jResponse['data']    = [];
                                $code                 = "202";
                                goto end;
                            }
                            //CORREO DESDE EL HIJO
                            SchoolsData::deletePersonsVirtual($id_persona);

                            $id_tipovirtual = Input::get('id_tipovirtual');
                            $id_virtual1 = Input::get('id_virtual1');
                            $edireccion = Input::get('edireccion');

                            $dataPVirtual = array("id_persona"=>$id_persona,"id_tipovirtual"=>$id_tipovirtual,"id_virtual" => $id_virtual1,"direccion" =>$edireccion);
                            if(!empty($dataPVirtual)){
                                SchoolsData::addPersonsVirtual($dataPVirtual);
                            }
                            //VIVIENDA DESDE EL HIJO
                            $dataPSchoolVivienda["direccion"] = Input::get('v_direccion');
                            $dataPSchoolVivienda = array_merge(array("id_persona" => $id_persona), $dataUtil->dataPSchoolVivienda);
                            $resultPSchoolVivienda = SchoolsData::updateSchoolPersonsVivienda($dataPSchoolVivienda,$id_persona);

                            $dataPSchoolFamily = array_merge(array("fecha_update"=>SchoolsData::getSysdate()), $dataUtil->dataPSchoolFamily);
                            $resultPSchoolFamily = SchoolsData::updateSchoolPersonsFamilySon($dataPSchoolFamily,$id_persona);
                            $dataPersona["codigo_familia"] = $numero;
                        }
                        $dataPersona["id_resp_pago"] = $id_resp_pago;
                        $dataPersona["id_persona"] = $id_persona;
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data']    = $dataPersona;
                        $code                 = "200";
                    }
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
    public function listPersonsFamily()
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
                $codigo = Input::get("codigo");
                $numero = Input::get("numero");
                $data = SchoolsData::listPersonsFamily($codigo, $numero);
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
    public function addProformas()
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
                $dataUtil = SchoolsUtil::dataProformas();
                if(!$dataUtil->valid)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $dataUtil->message;
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }
                
                $datoProforma = $dataUtil->data;
               
                $datoProforma['fecha_reg'] = SchoolsData::getSysdate();
                $datoProforma['estado'] = '1';
                $dato_by_proforma= SchoolsData::proformaByDni($datoProforma['dni']);
               
                if(!empty($dato_by_proforma->id_proforma)){
                    $result = SchoolsData::updateProformas($datoProforma,(int)$dato_by_proforma->id_proforma);
                }else{
                    $result = SchoolsData::addProformas($datoProforma);
                }
                
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
    /* SCHOOL_HORARIO_CITA */
    public function listSchedulesMeet()
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
                $id_personal = Input::get("id_personal");
                $fecha = Input::get("fecha");
                $data = SchoolsData::listSchedulesMeet($id_personal,$fecha);
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
    public function addSchedulesMeet()
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
                $periodo = SchoolsData::getPeriodPostulant();
                if(!$periodo) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe periodo.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $exist = SchoolsData::existSchedulesMeet('', '');
                if($exist) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Cruce fechas.';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    goto end;
                }
                $data = [
                    "id_hcita" => "",
                    "id_periodo" => $periodo->id_periodo,
                    "hora_inicio" => Input::get("hora_inicio"),
                    "hora_fin" => Input::get("hora_fin"),
                    "fecha" => Input::get("fecha"),
                    "estado" => "0",
                    "id_personal" => Input::get("id_personal")
                ];
                $result = SchoolsData::addSchedulesMeet($data);
                if ($result)
                {
                    $data["id_reserva"] = SchoolsData::getIdReserva();
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Reservation was created';
                    $jResponse['data']    = ['id_hcita' => $result];
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteSchedulesMeet($id_hcita)
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
                $data = SchoolsData::deleteSchedulesMeet($id_hcita);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
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
    public function listMeetS()
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
                $id_persona = Input::get("id_persona");
                $listPersona = SchoolsData::listPersonsParentesco($id_persona, 'H');
                $ids = [$id_persona];
                foreach($listPersona as $key => $value)
                {
                    $ids[] = $value->id_alumno;
                }
                $data = SchoolsData::listMeetS($ids);
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
    public function addMeets()
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
                    "id_cita" => "",
                    "id_hcita" => Input::get("id_hcita"),
                    "id_persona" => Input::get("id_persona")
                ];
                $result = SchoolsData::addMeets($data);
                if ($result)
                {
                    $data["id_reserva"] = SchoolsData::getIdReserva();
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Reservation was created';
                    $jResponse['data']    = $data;
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
    public function deleteMeets($id_cita)
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
                $data = SchoolsData::deleteMeets($id_cita);
                if ($data)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
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
    public function listAgreements()
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
                $id_reserva = Input::get("id_reserva");
                $data = SchoolsData::listAgreements($id_reserva);
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
    public function addAgreements()
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
                    "id_acuerdo" => "",
                    "importe" => Input::get("fecha"),
                    "detalle" => Input::get("tipo"),
                    "id_reserva" => Input::get("id_reserva")
                ];
                $result = SchoolsData::addAgreements($data);
                if ($result)
                {
                    $data["id_reserva"] = SchoolsData::getIdReserva();
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Reservation was created';
                    $jResponse['data']    = $data;
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
    public function listTypesPhone()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $data = SchoolsData::listTypesPhone();
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
    public function listTypesAddress()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $data = SchoolsData::listTypesAddress();
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
    public function listTypesVirtual()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida =='SI')
        {
            $jResponse = [];
            try
            {
                $data = SchoolsData::listTypesVirtual();
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

    public function listRetirements()
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
                $id_alumno = Input::get('id_alumno');
                $list = SchoolsData::listRetirements($id_alumno);
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
    public function addRetirements()
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
                    "id_retiro" => "",
                    "id_periodo" => null,
                    "id_alumno" => Input::get('id_alumno'),
                    "id_user" => $id_user,
                    "estado" => 'I',
                    "fecha_reg" => SchoolsData::getSysdate()
                ];
                $result = SchoolsData::addRetirements($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was created';
                    $jResponse['data']    = $result;
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
    public function updateRetirements($id_retiro)
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
                    "estado" => Input::get('estado')
                ];
                $result = SchoolsData::updateRetirements($data, $id_retiro);
                if ($result)
                {
                    $retiro = SchoolsData::showRetirements($id_retiro);
                    $data = [
                        "id_institucion" => null
                    ];
                    $result = SchoolsData::updateStudents($data, $retiro->id_alumno);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was created';
                    $jResponse['data']    = $result;
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
    public function listTransfers()
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
                $id_alumno = Input::get('id_alumno');
                $ocp = Input::get('ocp');
                $list = SchoolsData::listTransfers($id_alumno, $ocp);
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
    public function addTransfers()
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
                $id_alumno = Input::get('id_alumno');
                $data = [
                    "id_traslado" => "",
                    "id_periodo" => null,
                    "id_alumno" => $id_alumno,
                    'id_origen' => Input::get('id_origen'),
                    'id_destino' => Input::get('id_destino'),
                    'tipo' => Input::get('tipo'),
                    "estado" => $estado,
                    "id_user" => $id_user,
                    "fecha_reg" => SchoolsData::getSysdate()
                ];

                $result = SchoolsData::addTransfers($data);
                if ($result)
                {
                    if($estado == 'T')
                    {
                        $row = SchoolsData::getIdInstitucionByEntyDepto($id_entidad, $id_depto);
                        $data = [
                            "id_institucion" => $row->id_institucion
                        ];
                        $result = SchoolsData::updateStudents($data, $id_alumno);
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was created';
                    $jResponse['data']    = $result;
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
    public function updateTransfers($id_traslado)
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
                    "estado" => Input::get('estado')
                ];
                $result = SchoolsData::updateTransfers($data, $id_traslado);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was created';
                    $jResponse['data']    = $result;
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
    public function patchTransfers($id_traslado)
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
                    "estado" => 'T'
                ];
                $result = SchoolsData::updateTransfers($data, $id_traslado);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Succes was created';
                    $jResponse['data']    = $result;
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
    public function listStudentsSearch()
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
                $data = SchoolsData::listStudentsSearch($texto);
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
    public function showQuestionnaires($id_cuestionario)
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
                $data = SchoolsData::showQuestionnaires($id_cuestionario);
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
    public function downQuestionnairesDownload($id_cuestionario)
    {
        $carpeta = 'inicial';
        $file_name = 'Entrevista Inical_CU.docx';
        $path = public_path('school/'.$carpeta.'/'.$file_name) ;
        if(!File::exists($path)){
            abort(405);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        
        return $response;
    }
    public function listQuestionnairesQuestionsEvaluations()
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
                // $id_cuestionario = Input::get('id_cuestionario');
                // showPersonAdmision($id_persona)
                $personaAdmision = SchoolsData::showPersonAdmision($id_persona);
                $data = SchoolsData::listQuestionnairesQuestionsEvaluations($id_persona, $personaAdmision->id_nivel); // ($id_persona, $id_cuestionario);
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
    public function saveQuestionnairesEvaluationsApproved()
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
                $id_persona = Input::get('id_persona');
                $row = SchoolsData::getIdInstitucionByEntyDepto($id_entidad, $id_depto);
                $personaAdmin = SchoolsData::showPersonAdmision($id_persona);
                $data = [
                    'id_alumno' => $id_persona,
                    'id_nivel' => $personaAdmin->id_nivel,
                    'id_grado' => $personaAdmin->id_grado,
                    'id_institucion' => $row->id_institucion,
                    'id_resp_financiero' => $personaAdmin->id_resp_financiero
                ];
                $result = SchoolsData::addStudents($data);
                if ($result)
                {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [];
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
    public function execStudentsDownload()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if(true)
        {
            $jResponse = [];
            $code = 200;
            try
            {
                $titulo = 'Documento de Infomacion';
                $accion = Input::get('accion');
                if($accion == '1') $descripcion = 'Boleta de Notas';
                else if($accion == '2') $descripcion = 'Documento de Indicisplina';
                else if($accion == '3') $descripcion = 'Estado de Cuenta';
                else if($accion == '4') $descripcion = 'Constancia de Estudios';
                else if($accion == '5') $descripcion = 'Descargar Todo';
                else if($accion == 'DOC_CM') $descripcion = 'Constancia matricula';
                else if($accion == 'DOC_FD') $descripcion = 'Ficha de datos';
                else if($accion == 'DOC_M') $descripcion = 'Movilidad';
                else if($accion == 'DOC_E') $descripcion = 'Emergencia';
                else if($accion == 'DOC_LU') $descripcion = 'Lista de Utiles';
                else if($accion == 'DOC_CC') $descripcion = 'Carta de Compromiso';
                else if($accion == 'DOC_FA') $descripcion = 'Formato de Autorizacion';
                $data =  [
                    'titulo' => $titulo,
                    'descripcion' => $descripcion
                ];
                $view =  \View::make('pdf/schools/document', compact('data'))->render();
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML($view);
                return $pdf->download('informacion.pdf');

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
    public function execStudentsjeje()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if(true)
        {
            $jResponse = [];
            $code = 200;
            try
            {
                $motivo = Input::get('motivo');
                $file = Input::file('archivo');
                dd($motivo, $file);
                echo $motivo;
                exit;
                if($file == null)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'VACIO';
                    $jResponse['data']    = [];
                    $code                 = "400";
                    return response()->json($jResponse, $code);
                }
                dd($file);
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
    public function sze()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        if(true)
        {
            $jResponse = [];
            $code = 200;
            try
            {
                $data =  [
                    'quantity'      => '1' ,
                    'description'   => 'some ramdom text',
                    'price'   => '500',
                    'total'     => '500'
                ];

                $data = $data;
                $date = date('Y-m-d');
                $invoice = "2222";
                $view =  \View::make('welcome', compact('data', 'date', 'invoice'))->render();
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML($view);
                return $pdf->download('invoice.pdf');

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
    // NEO-52
     public function listPais()
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
                 $data = SchoolsData::listPais();
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

     public function listDep()
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
                 $id_pais = Input::get('pai_id');
                 $data = SchoolsData::listDepartamento($id_pais);
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

     public function listProv()
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
                 $id_dep = Input::get('dep_id');
                 $data = SchoolsData::listProvincia($id_dep);
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

     public function listDist()
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
                 $id_pro = Input::get('pro_id');
                 $data = SchoolsData::listDistrito($id_pro);
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
    public function listPeriodoEscolar()
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
                $data = SchoolsData::listPeriodoEscolar($id_entidad,$id_depto);
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
    public function listReligion()
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
                $data = SchoolsData::listReligion();
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
    public function listTipoIdioma()
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
                $data = SchoolsData::listTipoIdioma();
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
    public function listLevelInstruction()
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
                $data = SchoolsData::listLevelInstruction();
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
    public function listStatusCivil()
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
                $data = SchoolsData::listStatusCivil();
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
    public function listOperatorMovil()
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
                $data = SchoolsData::listOperatorMovil();
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
    public function listLocalization()
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
                $data = SchoolsData::listLocalization();
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
    public function listTipoParentesco()
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
                $data = SchoolsData::listTipoParentesco();
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
    public function numeroFamiliaByDni()
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
                $f_dni = Input::get("f_dni");
                $id_persona = Input::get("id_persona");
                $numero_familia = SchoolsData::numeroFamiliaByDni($f_dni, $id_persona);
                if(!empty($numero_familia)){
                    $data = SchoolsData::numeroFamilia($numero_familia->numero);  
                }else{
                    $data="";
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
    public function datosByCodFamilia()
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
                $codigo_familia = Input::get("f_codigo_familia");
                if(!empty($codigo_familia)){
                    $data = SchoolsData::numeroFamilia($codigo_familia);  
                }else{
                    $data="";
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
    public function datosByCodFamiliaAndHijo()
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
                $codigo_familia = (int)Input::get("f_codigo_familia");
                if(!empty($codigo_familia)){
                    $data = SchoolsData::numeroFamiliaAndHijo($codigo_familia);  
                }else{
                    $data="";
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
    public function datosByCodFamAndIdPerson()
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
                $id_persona = Input::get("f_id_persona");
                $codigo_familia = Input::get("f_codigo_familia");
                if(!empty($id_persona) && !empty($codigo_familia)){
                    $data = SchoolsData::datosByCodFamAndIdPerson($codigo_familia,(int)$id_persona);  
                }else{
                    $data="";
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
    public function datosByCodFamAndIdHijo()
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
                $f_tipo_parentesco = Input::get("f_tipo_parentesco");
                $codigo_familia = Input::get("f_codigo_familia");
                if(!empty($codigo_familia) && !empty($f_tipo_parentesco)){
                    $data = SchoolsData::datosByCodFamAndIdHijo($codigo_familia,$f_tipo_parentesco); 
                }else{
                    $data="";
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
    public function idiomasByIdPersona()
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
                $id_persona = Input::get("f_id_persona");
                if(!empty($id_persona)){
                    $data = SchoolsData::idiomasByIdPersona($id_persona);  
                }else{
                    $data="";
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
    public function correosByIdPersona()
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
                $id_persona = Input::get("f_id_persona");
                if(!empty($id_persona)){
                    $data = SchoolsData::correosByIdPersona($id_persona);  
                }else{
                    $data="";
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
    public function telefonosByIdPersona()
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
                $id_persona = Input::get("f_id_persona");
                if(!empty($id_persona)){
                    $data = SchoolsData::telefonosByIdPersona($id_persona);  
                }else{
                    $data="";
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
    public function datoRespFinanByIdPerson()
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
                $id_persona = Input::get("f_id_persona");
                if(!empty($id_persona)){
                    $data = SchoolsData::datoRespFinanByIdPerson($id_persona);  
                }else{
                    $data="";
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
    public function listPersonsAdmisionSearch()
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
                $data = SchoolsData::listPersonsAdmisionSearch($texto);
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
    public function addPersonAdmision()
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
                $id_persona = Input::get('id_persona');
                $id_periodo = Input::get('id_periodo');
                $id_pnivel = Input::get('id_pnivel');
                $id_pngrado = Input::get('id_pngrado');
                // $id_pngseccion = Input::get('id_pngseccion');
                $idpmo = "";
                $dataAdmision = array(
                    "id_persona" => $id_persona,
                    "id_periodo" => $id_periodo,
                    "id_pnivel" => $id_pnivel,
                    "id_pngrado" => $id_pngrado,
                    // "id_pngseccion" => $id_pngseccion,
                    'id_user' => $id_user
                );
                $idrespuestapago=SchoolsData::firstSchoolDatoAdicionalIdTipoParentesco($id_persona,'03');
                if(!empty($idrespuestapago)){
                    if(!empty($idrespuestapago->id_resp_pago)){//TIPO_PARENTESCO
                        $idpmo=SchoolsData::firstSchoolDatoAdicionalIdTipoParentesco($id_persona,'01');
                        if(!empty($idpmo)){
                          $dataAdmision['id_resp_financiero']=$idpmo->id_pmo;  
                        }
                    }
                }
                if(SchoolsData::existPersonAdmision($id_persona)){
                    $dataAdmision['fecha_update']=SchoolsData::getSysdate();
                    $result = SchoolsData::editPersonAdmision($dataAdmision, $id_persona);   
                }else{
                    $dataAdmision['fecha_registro']=SchoolsData::getSysdate();
                    $dataAdmision['realizo_pago']='S'; // 'N';
                    $dataAdmision['estado']='1';
                    $result = SchoolsData::addPersonAdmision($dataAdmision);
                }
                
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function admisionRealizoPago()
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
                $id_persona = Input::get('id_persona');
                $result = SchoolsData::admisionRealizoPago($id_persona);

                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }

    public function addSchoolInstitucion()
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
                $id_union = Input::get('union');
                $id_campo = Input::get('campo');
                $id_depto = Input::get('depto');
                $nombre = Input::get('nombre');
                $codigo_ugel = Input::get('codigo_ugel');
                $ugel = Input::get('ugel');
                $direccion = Input::get('direccion');
                $telefono = Input::get('telefono');
                $id_pais = Input::get('pais');
                $id_departamento = Input::get('departamento');
                $id_provincia = Input::get('provincia');
                $id_distrito = Input::get('distrito');
                $estado = Input::get('estado');
                

                $dataInstitucion = array(
                    "id_union" => $id_union,
                    "id_campo" => $id_campo,
                    "id_depto" => $id_depto,
                    "nombre" => $nombre,
                    "codigo_ugel" => $codigo_ugel,
                    "ugel" => $ugel,
                    "direccion" => $direccion,
                    "telefono" => $telefono,
                    "id_pais" => (int)$id_pais,
                    "id_departamento" => $id_departamento,
                    "id_provincia" => $id_provincia,
                    "id_distrito" => $id_distrito,
                    "estado" => $estado
                );
                $exist=SchoolsData::existeSchoolInstitucion($id_union,$id_campo,$id_depto);
                if(!$exist){
                    $result = SchoolsData::addSchoolInstitucion($dataInstitucion);
                    if(!$result)
                    {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Error.";
                        $jResponse['data']    = [];
                        $code                 = "202";
                        goto end;
                    }else{
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data']    = $result;
                        $code                 = "200";
                    } 
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Ya existe una institucion con el mismo departamento, seleccione otro.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
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

    public function addSchoolInstitucionEntradas($id_institucion)
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_ientrada = Input::get('id_ientrada');
                $nombre = Input::get('nombre');
                $estado = Input::get('estado');

                $dataInstitucionEntrada = array(
                    "id_institucion" => $id_institucion,
                    "nombre" => $nombre,
                    "estado" => $estado,
                );
                if($id_ientrada === 0) {
                    $result = SchoolsData::addSchoolInstitucionEntrada($dataInstitucionEntrada);
                } else {
                    $result = SchoolsData::updateSchoolInstitucionEntrada($id_ientrada, $dataInstitucionEntrada);
                }
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }

    public function editSchoolInstitucion($id_institucion)
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
                
                $id_union = Input::get('union');
                $id_campo = Input::get('campo');
                $id_depto = Input::get('depto');
                $nombre = Input::get('nombre');
                $codigo_ugel = Input::get('codigo_ugel');
                $ugel = Input::get('ugel');
                $direccion = Input::get('direccion');
                $telefono = Input::get('telefono');
                $id_pais = Input::get('pais');
                $id_departamento = Input::get('departamento');
                $id_provincia = Input::get('provincia');
                $id_distrito = Input::get('distrito');
                $estado = Input::get('estado');

                $dataInstitucion = array(
                    "id_union" => (int)$id_union,
                    "id_campo" => (int)$id_campo,
                    "id_depto" => (int)$id_depto,
                    "nombre" => $nombre,
                    "codigo_ugel" => $codigo_ugel,
                    "ugel" => $ugel,
                    "direccion" => $direccion,
                    "telefono" => $telefono,
                    "id_pais" => (int)$id_pais,
                    "id_departamento" => $id_departamento,
                    "id_provincia" => $id_provincia,
                    "id_distrito" => $id_distrito,
                    'estado' => $estado
                );
                $result = SchoolsData::updateSchoolInstitucion($dataInstitucion,$id_institucion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function editSchoolInstitucionEstado($id_institucion)
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

                $dataInstitucion = array(
                    "estado" => $estado
                );
                $result = SchoolsData::updateSchoolInstitucionEstado($dataInstitucion,$id_institucion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteSchoolInstitucion($id_institucion)
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
                $result = SchoolsData::deleteSchoolInstitucion($id_institucion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listInstitucionParametros()
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
                $union = Input::get('union');
                $campo = Input::get('campo');
                $departamento = Input::get('depto');
                $institucion = Input::get('institucion');

                $result = SchoolsData::listInstitucionParametros((int)$union,(int)$campo,(int)$departamento,(int)$institucion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listInstitucion()
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
                
                $result = SchoolsData::listInstitucion();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listPersonasCampo()
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
                $result = SchoolsData::listPersonasCampo($id_entidad);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addCategoriaTrabajador()
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
                $categoria = Input::get('nombre');

                $dataCategoria = array(
                    "nombre" => $categoria
                );
                $result = SchoolsData::addCategoriaTrabajador($dataCategoria);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listCategoriaTrabajador()
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
                $result = SchoolsData::listCategoriaTrabajador();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addTrabajador()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
       
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
        $id_institucion = $jResponse2->id_institucion;
        $nombre_institucion = $jResponse2->nombre;
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_personas = Input::get('id_personas');
                $id_categoria = Input::get('id_categoria');
                $lista = array();
                foreach($id_personas as $dato){
                   $lista[]=$dato['id_persona'];
                }
                
                $dataCategoria = array(
                    "id_persona" =>null,
                    "id_persona_session"=>$id_user,
                    "id_categoria" => $id_categoria,
                    "id_institucion" => $id_institucion
                );
                $result = SchoolsData::addTrabajador($dataCategoria, $lista);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteTrabajador($id_trabajador,$id_categoria)
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
                
                $result = SchoolsData::deleteTrabajador((int)$id_trabajador,(int)$id_categoria);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteTrabajadores()
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
                
                $id_trabajadores = Input::get('id_personas');
                $lista = array();
                foreach($id_trabajadores as $datos){
                    foreach($datos as $dato){
                        if(!empty($dato)){
                           $lista[]=$dato; 
                        } 
                    }
                }
                $lista2 = array();
                foreach($lista as $list){
                    foreach($list as $li){
                        if(!empty($li)){
                           $lista2[]=$li; 
                        } 
                    }
                }
                $result = SchoolsData::deleteTrabajadores($lista2);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listTrabajador()
    {
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
        $id_institucion = $jResponse2->id_institucion;
        $nombre_institucion = $jResponse2->nombre;
        if($valida == 'SI')
        {
            $jResponse = [];
            try
            {
                $id_categoria = Input::get('id_categoria');
        
                $result = SchoolsData::listTrabajador($id_categoria);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listUnion()
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
                $result = SchoolsData::listUnion();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listCampo()
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
                $id_union = Input::get('id_union');
                $result = SchoolsData::listCampo($id_union);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listDeparment()
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
                $id_campo = Input::get('id_campo');
                $result = SchoolsData::listDeparment($id_campo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listBimestreOpenClose()
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
                $result = SchoolsData::listBimestreOpenClose($id_periodo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addBimestreOpenClose()
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
                $id_bimestre_oc = Input::get('id_bimestre_oc');
                $id_periodo = Input::get('id_periodo');
                $id_bimestre = (int)Input::get('id_bimestre');
                $id_persona_open = Input::get('id_persona_open');
                $id_persona_close = Input::get('id_persona_close');
                $id_persona_reabierto = Input::get('id_persona_reabierto');
                $estado_automatico = (int)Input::get('estado_automatico');
                $iteracion = (int)Input::get('iteracion');
                $estado_opcion = (int)Input::get('estado_opcion');
                $auto_opcion = (int)Input::get('auto_opcion');//1=automatico, 2=opcion
                
                $bimestre_oc=SchoolsData::existBimestreOpenClose((int)$id_bimestre_oc);
                if($bimestre_oc){
                    if($auto_opcion==1){
                        if($estado_automatico==0 && $iteracion>1){
                            if($estado_automatico==1){
                                $estado_automatico=0;
                            }else{
                                $estado_automatico=1;
                            }
                        }else{
                            if($estado_automatico==1){
                                $estado_automatico=0;
                            }else{
                                $estado_automatico=1;
                            }
                        }
                        $dataBimestreOpenClose = array(
                            "id_periodo" => (int)$id_periodo,
                            "id_bimestre"=> $id_bimestre,
                            "estado_automatico" =>$estado_automatico
                        );
                    }
                    if($auto_opcion==2){
                        if($estado_opcion==0 && $iteracion==1){
                            $fecha_open=SchoolsData::getSysdate();
                            $estado_opcion=1;

                            $dataBimestreOpenClose = array(
                                "id_periodo" => (int)$id_periodo,
                                "id_bimestre"=> $id_bimestre,
                                "fecha_open" => $fecha_open,
                                "id_persona_open" =>$id_user,
                                "estado_opcion" =>  $estado_opcion
                            );
                        }else if($estado_opcion==1 && $iteracion>1 && $iteracion<4){
                            $fecha_close=SchoolsData::getSysdate();
                            $estado_opcion=0;
                            $estado_automatico=1;
                            $dataBimestreOpenClose = array(
                                "id_periodo" => (int)$id_periodo,
                                "id_bimestre"=> $id_bimestre,
                                "fecha_close" =>$fecha_close,
                                "id_persona_close" => $id_user,
                                "estado_automatico" => $estado_automatico,
                                "estado_opcion" =>  $estado_opcion
                            );  
                        }else if($estado_opcion==0 && $iteracion > 3){
                            $estado_opcion=1;
                            $dataBimestreOpenClose = array(
                                "id_periodo" => (int)$id_periodo,
                                "id_bimestre"=> $id_bimestre,
                                "id_persona_reabierto" =>$id_user,
                                "estado_opcion" =>  $estado_opcion
                            );
                            
                        }else if($estado_opcion==1 && $iteracion > 3){
                            $dataBimestreOpenClose = array(
                                "id_periodo" => (int)$id_periodo,
                                "id_bimestre"=> $id_bimestre,
                                "id_persona_reabierto" =>$id_user,
                                "estado_opcion" =>  0,
                                "estado_automatico" => 1
                            );
                        }  
                    }
                    
                    $dataBimestreOpenClose["id_bimestre_oc"]=$id_bimestre_oc;
                    SchoolsData::updateBimestreOpenClose($dataBimestreOpenClose,$id_bimestre_oc,$id_bimestre);
                    $result = (int)$id_periodo;
                }else{
                    if($estado_automatico=="0"){
                        $estado_automatico=0;
                    }else{
                        $estado_automatico=1;
                    }
                    $dataBimestreOpenClose = array(
                        "id_periodo" => (int)$id_periodo,
                        "id_bimestre"=> (int)$id_bimestre,
                        "estado_automatico" => $estado_automatico,
                        "estado_opcion" => $estado_opcion,
                        "iteracion" => 1
                    );
                    $result = SchoolsData::addBimestreOpenClose($dataBimestreOpenClose);
                }
                
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function updateBimestreOpenClose($dataBimestreOpenClose,$id_bimestre_oc)
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
                $result = SchoolsData::updateBimestreOpenClose($dataBimestreOpenClose,$id_bimestre_oc);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    } 
    public function listPeriodoOpenClose()
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
                $result = SchoolsData::listPeriodoOpenClose();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listPeriodoNivel()
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
                $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad, $id_depto);
                $id_institucion = $jResponse2->id_institucion;

                $id_periodo = Input::get('id_periodo');
                $result = SchoolsData::listPeriodoNivel($id_periodo,$id_institucion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listPeriodoNGrado()
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
                $id_pnivel = Input::get('id_pnivel');
                if(!empty($id_periodo)){
                    //LISTA DE GRADOS QUE NECESITA COMO PARAMETRO ID_PERIODO
                    $result = SchoolsData::listPeriodoNGradoByPeriodo($id_periodo,$id_pnivel);
                }else{
                     //LISTA DE GRADOS QUE NECESITA COMO PARAMETRO ID_PNIVEL
                    $result = SchoolsData::listPeriodoNGrado($id_pnivel);
                }
                
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function periodoNGrado($id_pngrado)
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
                //$id_pngrado = Input::get('id_pngrado');
                $result = SchoolsData::periodoNGrado($id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listPeriodoNGSeccion()
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
                $result = SchoolsData::listPeriodoNGSeccion($id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listPeriodoNGNotSeccion()
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
                $id_pngrado = (int)Input::get('id_pngrado');
                $id_pngseccion =  (int)Input::get('id_pngseccion');
                $result = SchoolsData::listPeriodoNGNotSeccion($id_pngrado,$id_pngseccion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function searchEmployee()
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
                $person = Input::get('person');
                $categoria = Input::get('categoria');
                $result = SchoolsData::searchEmployee($person,$categoria);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addSeccionPersonal()
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
                $id_pnivel = Input::get('id_pnivel');
                $id_pngrado = Input::get('id_pngrado');
                $id_pngseccion = Input::get('id_pngseccion');
                $id_persona = Input::get('id_persona');
                $id_cat_trabajador = Input::get('id_cat_trabajador');
                $tipo = Input::get('tipo');
                $electivo = Input::get('en_electivo');
                $data = array(
                    "id_periodo" =>$id_periodo,
                    "id_pnivel" =>$id_pnivel,
                    "id_pngrado" =>$id_pngrado,
                    "id_pngseccion" =>$id_pngseccion,
                    "id_persona"=>$id_persona,
                    "fecha_registro" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    "id_cat_trabajador" =>$id_cat_trabajador,
                    'tipo' => $tipo,
                    'en_electivo' => $electivo
                );
                $result = SchoolsData::addSeccionPersonal($data);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function editSeccionPersonal($id_spersonal)
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
                //$id_spersonal = Input::get('id_spersonal');
                $id_periodo = Input::get('id_periodo');
                $id_pnivel = Input::get('id_pnivel');
                $id_pngrado = Input::get('id_pngrado');
                $id_pngseccion = Input::get('id_pngseccion');
                $id_persona = Input::get('id_persona');
                $tipo = Input::get('tipo');
                $data = array(
                    "id_periodo" =>$id_periodo,
                    "id_pnivel" =>$id_pnivel,
                    "id_pngrado" =>$id_pngrado,
                    "id_pngseccion" =>$id_pngseccion,
                    "id_persona"=>$id_persona,
                    "fecha_update" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    'tipo' => $tipo
                );
                $result = SchoolsData::editSeccionPersonal($data,$id_spersonal);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function PersonalSeccionById($id_pngseccion)
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
                $result = SchoolsData::PersonalSeccionById($id_pngseccion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function PersonalSeccionTipoById()
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
                $id_pngseccion = Input::get('id_pngseccion');
                $categoria = Input::get('id_cat_trabajador');
                $tipo = Input::get('tipo');
                $result = SchoolsData::PersonalSeccionTipoById($id_pngseccion,$categoria,$tipo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function existPersonalSeccionFirst()
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
            {//falta a qui parametros
                $id_periodo = Input::get('id_periodo');
                $id_pnivel = Input::get('id_pnivel');
                $id_pngrado = Input::get('id_pngrado');
                $id_pngseccion = Input::get('id_pngseccion');
                $id_persona = Input::get('id_persona');
                $tipo = Input::get('tipo');

                $result = SchoolsData::existPersonalSeccionFirst($id_periodo, $id_pnivel, $id_pngrado, $id_pngseccion, $id_persona, $tipo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function existPersonalGradoFirst()
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
            {//falta a qui parametros
                $id_periodo = Input::get('id_periodo');
                $id_pnivel = Input::get('id_pnivel');
                $id_pngrado = Input::get('id_pngrado');
                $id_persona = Input::get('id_persona');
                $tipo = Input::get('tipo');

                $result = SchoolsData::existPersonalGradoFirst($id_periodo, $id_pnivel, $id_pngrado, $id_persona, $tipo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listSPersonal()
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
                $result = SchoolsData::listSPersonal();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listCursosByPngseccion()
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
                // $id_pngrado = Input::get('id_pngrado');
                $id_pngseccion = Input::get('id_pngseccion'); 
                $result = SchoolsData::listCursosByPngseccion($id_pngseccion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listCursosByPNGradoElectivo()
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
                $result = SchoolsData::listCursosByPNGradoElectivo($id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listCursosBySDocente()
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
                $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
                $id_institucion = $jResponse2->id_institucion;

                $id_sdocente = Input::get('id_sdocente');
                $result = SchoolsData::listCursosBySDocente($id_sdocente,$id_institucion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addCursoDocente()
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
                $cursos = Input::get('cursos');
                $id_sdocente = Input::get('id_sdocente');
                $id_pngseccion = Input::get('id_pngseccion');
                $lista = array();
                foreach($cursos as $dato){
                   $lista[]=$dato['id_pngcurso'];
                }
                $data = array(
                    "fecha_registro" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    "id_spersonal" =>$id_sdocente,
                    "id_pngseccion" =>$id_pngseccion,
                );
                $result = SchoolsData::addCursoDocente($data,$lista);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addSubCursoDocente()
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
                $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
                $id_institucion = $jResponse2->id_institucion;

                //$ids_pmcprofesors = Input::get('ids_pmcprofesor');
                $id_pngcurso = Input::get('id_pngcurso');
                $id_parent = Input::get('id_parent');
                $bimestres = Input::get('bimestres');//se actualizara la tabla SCHOOL_PMES_CURSO_PROFESOR
                $id_sdocente = Input::get('id_sdocente');
                
                $result2 = SchoolsData::updateBimestresPMesCursoProf($id_sdocente,$id_institucion,$bimestres);

                $data = array(
                    "id_pngcurso" => $id_pngcurso,
                    //"id_bimestre_oc" => $id_bimestre,
                    "id_dcparent" =>$id_parent,
                    "fecha_registro" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    "id_spersonal" =>$id_sdocente
                );
                $result = SchoolsData::addSubCursoDocente($data);
                
                if(!$result || !$result2)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addSubCursoDocenteElectivo()
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
                $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
                $id_institucion = $jResponse2->id_institucion;

                //$ids_pmcprofesors = Input::get('ids_pmcprofesor');
                $id_pngcurso = Input::get('id_pngcurso');
                $id_parent = Input::get('id_parent');
                $id_sdocente = Input::get('id_sdocente');

                $data = array(
                    "id_pngcurso" => $id_pngcurso,
                    "id_dcparent" =>$id_parent,
                    "fecha_registro" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    "id_spersonal" =>$id_sdocente
                );
                $result = SchoolsData::addSubCursoDocente($data);
                
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function editSubCursoDocente($id_dcurso)
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
                $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
                $id_institucion = $jResponse2->id_institucion;

                $id_pngcurso = Input::get('id_pngcurso');
                $id_parent = Input::get('id_parent');
                $bimestres = Input::get('bimestres');//se actualizara la tabla SCHOOL_PMES_CURSO_PROFESOR
                $id_sdocente = Input::get('id_sdocente');
                
                $result2 = SchoolsData::updateBimestresPMesCursoProf($id_sdocente,$id_institucion,$bimestres);

                $id_pngcurso = Input::get('id_pngcurso');
                $id_parent = Input::get('id_parent');
               // $id_bimestre = Input::get('id_bimestre');
                $id_sdocente = Input::get('id_sdocente');
                
                $data = array(
                    "id_pngcurso" => $id_pngcurso,
                   // "id_bimestre_oc" => $id_bimestre,
                    "id_dcparent" =>$id_parent,
                    "fecha_update" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    "id_spersonal" =>$id_sdocente
                );
                $result = SchoolsData::editSubCursoDocente($data, $id_dcurso);
                if(!$result || !$result2) 
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function editSubCursoDocenteElectivo($id_dcurso)
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
                $jResponse2  = GlobalMethodsInstitucion::datosInstitucionByIdDepto($id_entidad,$id_depto);
                $id_institucion = $jResponse2->id_institucion;

                $id_pngcurso = Input::get('id_pngcurso');
                $id_parent = Input::get('id_parent');
               // $id_bimestre = Input::get('id_bimestre');
                $id_sdocente = Input::get('id_sdocente');
                
                $data = array(
                    "id_pngcurso" => $id_pngcurso,
                    "id_dcparent" =>$id_parent,
                    "fecha_update" => SchoolsData::getSysdate(),
                    "id_user" =>$id_user,
                    "id_spersonal" =>$id_sdocente
                );
                $result = SchoolsData::editSubCursoDocente($data, $id_dcurso);
                if(!$result) 
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    
    public function existSubCursoByIdPersIdPNGCurso()
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
                $id_dcurso = Input::get('id_dcurso');
                $result = SchoolsData::existSubCursoByIdPersIdPNGCurso($id_dcurso);
                if(!$result) 
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteCursosByIdSDocente()
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
                $id_dcursos = Input::get('id_dcursos');
                $id_sdocente = Input::get('id_sdocente');
                $lista = array();
                foreach($id_dcursos as $dato){
                   $lista[]=$dato['id_dcurso'];
                }
                $result = SchoolsData::deleteCursosByIdSDocente($lista,$id_sdocente);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listCursosByIdCParent()
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
                $id_cparent = Input::get('id_cparent');
                $result = SchoolsData::listCursosByIdCParent($id_cparent);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function gradoSeccionById($id_pngseccion)
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
                $result = SchoolsData::gradoSeccionById($id_pngseccion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listBimestreByEstadoAutomatico()
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
                $result = SchoolsData::listBimestreByEstadoAutomatico();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function ngSeccionById($id_pngseccion)
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
                $result = SchoolsData::ngSeccionById($id_pngseccion);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listAlumnosBySeccion()
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
                $id_pngseccion = Input::get('id_pngseccion');
                $id_periodo = Input::get('id_periodo');
                $id_pngcurso = Input::get('id_pngcurso');
                $result = SchoolsData::listAlumnosBySeccion($id_pngseccion,$id_periodo,$id_pngcurso);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function cambioAlumnoSeccion()
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
                $ids_matricula = Input::get('ids_matricula');
                $id_pngseccion = (int)Input::get('id_pngseccion');
                $idsMatricula = array();
                foreach($ids_matricula as $idMatricula){
                   $idsMatricula[]= (int)$idMatricula['id_matricula'];
                }
                $data = array(
                    "id_pngseccion" => $id_pngseccion
                );
                $result = SchoolsData::updateAlumnoSeccion($data,$idsMatricula);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function addIncidencia()
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
                $id_incidencia = (int)Input::get('id_incidencia');
                $id_alumno = (int)Input::get('id_alumno');
                $descripcion = Input::get('descripcion');
                $hora_registro = Input::get('hora_registro');
                $hora_ocurrio = Input::get('hora_ocurrio');
                $nivel = Input::get('nivel');
                $estado = Input::get('estado');
                $imagenes = Input::file('imagenes');
                $tipo_evidencia = Input::get('tipo_evidencia');
                $id_periodo = Input::get('id_periodo');

                $data = array(
                    'id_periodo'=> $id_periodo,
                    'id_alumno'=> $id_alumno,
                    "descripcion" => $descripcion,
                    "hora_registro" => date("Y-m-d h:m"),
                    "hora_ocurrio" => date("Y-m-d $hora_ocurrio"),
                    "nivel" => $nivel,
                    "estado" => $estado,
                    "id_user" => $id_user
                );
                if(!empty($id_incidencia)){
                    $result = SchoolsData::editIncidencia($tipo_evidencia,$imagenes,$data,$id_incidencia);
                }else{
                    $result = SchoolsData::addIncidencia($tipo_evidencia,$imagenes,$data);
                }
                
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function incidenciaJustificacion()
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
                $id_incidencia = (int)Input::get('id_incidencia');
                $id_alumno = Input::get('id_alumno');
                $estado = Input::get('estado');
                $imagenes = Input::file('imagenes');
                $tipo_evidencia = Input::get('tipo_evidencia');
                $justificacion = Input::get('justificacion');
                $data = array(
                    "estado" => $estado,
                    "id_user" => $id_user,
                    "justificacion" => $justificacion
                );
                $result = SchoolsData::incidenciaJustificacion($id_incidencia,$tipo_evidencia,$imagenes,$data,$id_alumno);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function matriculaByIdPersonaPeriodo()
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
                $id_periodo = Input::get('id_periodo');
                $data = SchoolsData::matriculaByIdPersonaPeriodo($id_persona,$id_periodo);
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
    public function searchAlumnoByPeriodo()
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
                $person = Input::get('person');
                $id_periodo = (int)Input::get('id_periodo');
                $result = SchoolsData::searchAlumnoByPeriodo($person,$id_periodo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function searchAlumnoReserva($id_persona)
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
                //$id_persona = Input::get('id_persona');
                $result = SchoolsData::searchAlumnoReserva($id_persona);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function listIncidenciaAlumno()
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
                $id_alumno = Input::get('id_alumno');
                $id_periodo = Input::get('id_periodo');
                $nivel = Input::get('nivel');
                $result = SchoolsData::listIncidenciaAlumno($id_alumno,$id_periodo,$nivel);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function evidencia_img($filename)
    {
        //$path = storage_path('app/') . $filename;//esta correcto
        $path = public_path('school/evidencias/') . $filename;
        if(!File::exists($path)){
            abort(405);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        
        return $response;
      
       // $file = Storage::disk('public')->get( $filename);
        //return Response($file, 200)->header('Content-Type', $type);
    }
    public function evidenciaByIdIncidencia()
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
                $id_incidencia = Input::get('id_incidencia');
                $result = SchoolsData::evidenciaByIdIncidencia($id_incidencia);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteIncidenciaEvidencia($id_incidencia)
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
                $result = SchoolsData::deleteIncidenciaEvidencia((int)$id_incidencia);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function retirarAlumnoFaltasAltas()
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
                $id_alumno = (int)Input::get('id_alumno');
                $id_periodo = Input::get('id_periodo');
                $result = SchoolsData::retirarAlumnoFaltasAltas($id_alumno,$id_periodo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    //EXONERACION
    public function exoneracionAddEstudiantes()//AGREGANDO ESTUDIANTES AL CURSO
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
                $id_pngcurso = Input::get('id_pngcurso');
                $datosAlumnos = Input::get('datoslumno');
                 $data = array(
                     "id_periodo" =>$id_periodo,
                     "id_pngcurso"=>$id_pngcurso,
                     "id_user" =>$id_user,
                     "fecha_registro" => date("Y-m-d h:m")
                 );
                $result = SchoolsData::exoneracionAddEstudiantes($data,$datosAlumnos);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionListCursosG()//CURSOS POR GRADO 
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
                $result = SchoolsData::exoneracionListCursosG($id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionListEstudiantesCursoPG()// LISTADO DE ALUMNOS EXONERADOS Ó RETIRAR DE LA EXONERACION
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
                $id_pngcurso = Input::get('id_pngcurso');
               
                $result = SchoolsData::exoneracionListEstudiantesCursoPG($id_periodo,$id_pngcurso);
                
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionDelEstudiantesCursoPGA()// LISTADO DE ALUMNOS EXONERADOS Ó RETIRAR DE LA EXONERACION
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
                //$id_periodo = Input::get('id_periodo');
                //$id_pngcurso = Input::get('id_pngcurso');
                $ids_ecurso = Input::get('ids_ecurso');//solo para eliminar
                $result = SchoolsData::exoneracionDelEstudiantesCursoPGA($ids_ecurso);
            
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionEstudiantesBySeccionCurso()
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
                $id_pngseccion = (int)Input::get('id_pngseccion');
                $id_pngcurso = (int)Input::get('id_pngcurso');
                $result = SchoolsData::exoneracionEstudiantesBySeccionCurso($id_pngseccion,$id_pngcurso);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionDesEvi()
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
                $id_ecurso = (int)Input::get('id_ecurso');
                $id_alumno = (int)Input::get('id_alumno');
                $descripcion = Input::get('descripcion');
                $imagenes = Input::file('imagenes');
                $tipo_evidencia = Input::get('tipo_evidencia');

                $data_ecursodescripcion = array(
                    'id_ecurso'=> $id_ecurso,
                    "descripcion" => $descripcion,
                    "fecha_registro" => date("Y-m-d h:m")
                );
                $data_ecursoevidencia = array(
                    'id_ecurso'=> $id_ecurso,
                    'tipo'=> $tipo_evidencia
                );

                if($tipo_evidencia=='U'){
                    $result = SchoolsData::exoneracionEditDescripcionEvidencia($data_ecursodescripcion,$data_ecursoevidencia,$imagenes,$id_alumno);
                }else{
                    $result = SchoolsData::exoneracionAddDescripcionEvidencia($data_ecursodescripcion,$data_ecursoevidencia,$imagenes,$id_alumno);
                }
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionDescripcionById($id_ecurso)
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
                $result = SchoolsData::exoneracionDescripcionById($id_ecurso);
               
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function exoneracionIvidenciaById($id_ecurso)
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
                $result = SchoolsData::exoneracionIvidenciaById($id_ecurso);
               
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function cambioDocenteCursolist()
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
                $id_persona = Input::get('id_persona');
                
                $result = SchoolsData::cambioDocenteCursolist($id_periodo,$id_persona);
               
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function cambioDocenteCursoUpdate()
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
                $cursosdatos = Input::get('cursosdatos');
                $result = SchoolsData::cambioDocenteCursoUpdate($cursosdatos,$id_user);
               
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function sexoTipo()
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
                $result = SchoolsData::sexoTipo();
               
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
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
                $data = SchoolsData::listPersonsSearch($texto);
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
    public function listBimestresPMesCursoProf(){
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
                $id_pngseccion = Input::get('id_pngseccion');
                $id_pngcurso = Input::get('id_pngcurso');
                $result = SchoolsData::listBimestresPMesCursoProf($id_pngseccion,$id_pngcurso);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function periodoMesHijos(){
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
                $id_parent = Input::get('id_parent');
                $result = SchoolsData::periodoMesHijos($id_parent);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function unitsAdd()
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
                $unidades = Input::get('unidades');
                $id_periodo = Input::get('id_periodo');
                $id_pngrado = Input::get('id_pngrado');
                $result = SchoolsData::unitsAdd($unidades,$id_periodo,$id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function unitsByIdPNGrado()
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
                $id_periodo = Input::get('id_periodo');
                $result = SchoolsData::unitsByIdPNGrado($id_pngrado,$id_periodo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function unitsLista()
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
                $id_periodo = Input::get('id_periodo');
                $result = SchoolsData::unitsLista($id_pngrado,$id_periodo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function unitsByIdPNGradoGroupHijo()
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
                $result = SchoolsData::unitsByIdPNGradoGroupHijo($id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function unitsByIdPmesHijo()
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
                $id_pmes_hijo = Input::get('id_pmes_hijo');
                $result = SchoolsData::unitsByIdPmesHijo($id_pmes_hijo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function thematicAdd()
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

                $id_unidad = Input::get('id_unidad');
                $descripcion = Input::get('descripcion');
                // $s_descripcion = Input::get('s_descripcion');
                // $s_planeamiento = Input::get('s_planeamiento');
                // $s_intencion = Input::get('s_intencion');
                $data = array();
                $data['DESCRIPCION'] =  $descripcion;
                // $data['SSIG_DESC_CONTEXTO_REALIDAD'] = $s_descripcion;
                // $data['SSIG_PLAN_RETO_DESAFIO'] =  $s_planeamiento;
                // $data['SSIG_INTE_PEDAGOGICA'] =  $s_intencion;

                $result = SchoolsData::thematicAdd($data,$id_unidad);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function thematicEdit($id_tematica)
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
                $id_unidad = Input::get('id_unidad');
                $descripcion = Input::get('descripcion');
                $data = array();
                $data['DESCRIPCION'] =  $descripcion;

                $result = SchoolsData::thematicEdit($id_tematica,$data,$id_unidad);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function thematicById($id_tematica)
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
                $result = SchoolsData::thematicById($id_tematica);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function thematicDelete($id_tematica, $id_unidad)
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
                $result = SchoolsData::thematicDelete($id_tematica, $id_unidad);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function thematicGrado()
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
                $result = SchoolsData::thematicGrado($id_pngrado);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function pmdeList()
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
                $result = SchoolsData::pmdeList();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function pmdeListPeriodo()
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
                $id_unidad = Input::get('id_unidad');
                $result = SchoolsData::pmdeListPeriodo($id_periodo,$id_unidad);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function pmdeAdd()
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
                $pmdes = Input::get('objetos');
                $id_periodo = Input::get('id_periodo');
                $result = SchoolsData::pmdeAdd($pmdes,$id_periodo);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function agendaList()
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
                // $pmdes = Input::get('objetos');
                // $id_periodo = Input::get('id_periodo');
                $result = SchoolsData::agendaList();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function agendaAdd()
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
                $id_agenda = Input::get('id_agenda');
                $id_categoria = Input::get('id_categoria');
                $id_periodo = Input::get('id_periodo');
                $descripcion = Input::get('descripcion');
                $fecha_inicio = Input::get('fecha_inicio');
                $fecha_final = Input::get('fecha_final');
                $data = array();
                $data['id_categoria'] =  (int)$id_categoria;
                $data['id_periodo'] =  1;
                $data['descripcion'] =  $descripcion;
                $data['fecha_inicio'] =  date('Y-m-d H:m:s',strtotime($fecha_inicio));
                $data['fecha_final'] =  date('Y-m-d',strtotime($fecha_final));
                // $data['id_entidad'] =  $id_entidad;
                // $data['id_depto'] =  $id_depto;
                $result = SchoolsData::agendaAdd($data, $id_agenda);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function agendaEdit($id_agenda)
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
                
                $id_categoria = Input::get('id_categoria');
                $id_periodo = Input::get('id_periodo');
                $descripcion = Input::get('descripcion');
                $fecha_inicio = Input::get('fecha_inicio');
                $fecha_final = Input::get('fecha_final');
                $data = array();
                $data['id_categoria'] =  (int)$id_categoria;
                $data['descripcion'] =  $descripcion;
                $data['fecha_inicio'] =  date('Y-m-d H:m:s',strtotime($fecha_inicio));
                $data['fecha_final'] =  date('Y-m-d',strtotime($fecha_final));
                
                $result = SchoolsData::agendaEdit($data,$id_agenda);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function deleteAgenda($id_agenda)
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
                
                // $id_categoria = Input::get('id_categoria');
                // $id_periodo = Input::get('id_periodo');
                // $descripcion = Input::get('descripcion');
                // $fecha_inicio = Input::get('fecha_inicio');
                // $fecha_final = Input::get('fecha_final');
                // $data = array();
                // $data['id_categoria'] =  (int)$id_categoria;
                // $data['descripcion'] =  $descripcion;
                // $data['fecha_inicio'] =  date('Y-m-d H:m:s',strtotime($fecha_inicio));
                // $data['fecha_final'] =  date('Y-m-d',strtotime($fecha_final));
                
                $result = SchoolsData::deleteAgenda($id_agenda); // agendaEdit
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
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
        end:
        return response()->json($jResponse,$code);
    }
    public function confirmationDocument()
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
                $id_alumno = (int)Input::get('id_alumno');

                $compromiso_honor_check = Input::get('compromiso_honor_check');
                    if($compromiso_honor_check){$compromiso_honor_check = 'S';}else{$compromiso_honor_check ='N';}
                $ficha_medica_check = Input::get('ficha_medica_check');
                    if($ficha_medica_check){$ficha_medica_check = 'S';}else{$ficha_medica_check ='N';}
                $confirmacion_movilidad_check = Input::get('confirmacion_movilidad_check');
                    if($confirmacion_movilidad_check){$confirmacion_movilidad_check = 'S';}else{$confirmacion_movilidad_check ='N';}
                $autorizacion_foto_check = Input::get('autorizacion_foto_check');
                    if($autorizacion_foto_check){$autorizacion_foto_check = 'S';}else{$autorizacion_foto_check ='N';}
                $file_ch = Input::file('file_ch');
                $file_fm = Input::file('file_fm');
                $file_cm = Input::file('file_cm');
                $file_af = Input::file('file_af');
                $data = array();
                
                $data['compromiso_honor_check'] = $compromiso_honor_check;
                $data['ficha_medica_check'] = $ficha_medica_check;
                $data['confirmacion_movilidad_check'] = $confirmacion_movilidad_check;
                $data['autorizacion_foto_check'] = $autorizacion_foto_check;
                $data['file_ch'] = $file_ch;
                $data['file_fm'] = $file_fm;
                $data['file_cm'] = $file_cm;
                $data['file_af'] = $file_af;
                
                $result = SchoolsData::confirmationDocumentEdit($id_alumno,$data,$id_user);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function feligresiaListByPeriodOperativo()
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
                $result = SchoolsData::feligresiaListByPeriodOperativo();
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function dowloadFile($carpeta,$file_name)
    {
       
        $path = public_path('school/'.$carpeta.'/'.$file_name) ;
        if(!File::exists($path)){
            abort(405);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
    
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        
        return $response;
    }
    public function feligresiaConfirmar()
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
                $feligresia_vob = Input::get('feligresia_vob');
                $id_reserva = (int)Input::get('id_reserva');

                $data = array();
                $data['feligresia_vob'] = $feligresia_vob;
                $result = SchoolsData::feligresiaConfirmar($data,$id_reserva);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function feligresiaSearchFamiliaHijosByIdUser()
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
                $result = SchoolsData::feligresiaSearchFamiliaHijosByIdUser($id_user);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function uploadFileReservaFeligresia()
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
                $id_alumno = (int)Input::get('id_alumno');
                $file = Input::file('file');
                
                $result = SchoolsData::uploadFileReservaFeligresia($id_alumno,$file,$id_user);
                if(!$result)
                {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error.";
                    $jResponse['data']    = [];
                    $code                 = "202";
                    goto end;
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = $result;
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
        end:
        return response()->json($jResponse,$code);
    }
    public function personaById($id_persona)
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
               
                $data = SchoolsData::showPersonsManagerNotSchool($id_persona);
                
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
                    $jResponse['message'] = 'The item does not exist';
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
    
}

function recursivaItems($parent, $lista)
{
    $listChild = [];
    foreach($lista as $key => $value)
    {
        if($parent == $value->parent) // id_sitem parent
        {
            $value->items = recursivaItems($value->id_sitem, $lista);
            $listChild[] = $value;
        }
    }
    return $listChild;
}
function getGenereNameRandom($length)
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $name = substr(str_shuffle($characters), 0, $length);
    return $name; 
}
<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 5/29/19
 * Time: 6:59 PM
 */

namespace App\Http\Controllers\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Data\FinancesStudent\StudentData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Setup\PersonData;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DOMPDF;
use Illuminate\Support\Facades\DB;
use Mail;
use Excel;
use App\Http\Data\HumanTalent\PaymentsData;
use App\Http\Data\Sales\SalesData;
use App\Http\Data\FinancesStudent\ParameterData;
use App\Http\Data\FinancesStudent\ComunData;
use Exception;
class StudentsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function showStudents($idStuden)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getStudent($idStuden);
//                $data = PersonData::showStudentsPersons($idStuden);
//                dd($data);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listStudents(Request $request)
    {

        $id_depto = $request->id_depto;
        $id_entidad = $request->id_entidad;
        $sql = "select to_char(sysdate,'DD-MM-YYYY D HH24:MI') from dual";
        return view('asistencia.asientos');
    }
    public function pagosDebiCredi(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $id_alumno_contrato =  $request->id_alumno_contrato;
            // $id_semestre_programa =  $request->id_semestre_programa;
            $dc = $request->dc;
            try {
                $data = StudentData::pagosDC($id_alumno_contrato, $dc);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    // $jResponse['data'] =  collect($data)->groupBy('dc');
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
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function contratoAlumn(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $id_alumno_contrato =  $request->id_alumno_contrato;
            // $id_semestre_programa =  $request->id_semestre_programa;
            try {
                $data = StudentData::contratoAlumn($id_alumno_contrato);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function planPagoSemestre(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $id_planpago_semestre =  $request->id_planpago_semestre;
            try {
                $data = StudentData::planPagoSemestre($id_planpago_semestre);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function planPago(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $id_planpago_semestre =  $request->id_planpago_semestre;
            try {
                $data = StudentData::planPago($id_planpago_semestre);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addProrroga(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse['id_user'];

        $date = Carbon::now();
        $fecha_reg = $date->format('Y/m/d H:m:s');

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::addProrroga($request,  $fecha_reg, $id_user_reg);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function validarCodigoProrroga(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::validarCodigoProrroga($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addProrrogaMasivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse['id_user'];

        $date = Carbon::now();
        $fecha_reg = $date->format('Y/m/d H:m:s');

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::addProrrogaMasivo($request,  $fecha_reg, $id_user_reg);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function searchStudentGlobal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $search = $request->query('search');
            if ($search) {
                $data = StudentData::searchStudentGlobal($search);
            } else {
               $data = null;
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
    public function addDescVicerectorado(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];

        $date = Carbon::now();
        $fecha_registro = $date->format('Y/m/d H:m:s');

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::addDescVicerectorado($request, $id_usuario_reg, $fecha_registro);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function addDescVicerectoradoMultiple(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];

        $date = Carbon::now();
        $fecha_registro = $date->format('Y/m/d H:m:s');
        $completed = array();
        if($valida=='SI'){
            $jResponse=[];
            $data = collect($this->request->descuentos)->map(function($item) {
                $tipo_ensen = '';
                $ensenanza = null;
                $tipo_mat = '';
                $matricula = null;
                if($item['ensen_importe']) {
                    $tipo_ensen = 'I';
                    $ensenanza = $item['ensen_importe'];
                }else if($item['ensen_porcent']) {
                    $tipo_ensen = 'P';
                    $ensenanza = $item['ensen_porcent'];
                }
                if($item['mat_importe']) {
                    $tipo_mat = 'I';
                    $matricula = $item['mat_importe'];
                }else if($item['mat_porcent']) {
                    $tipo_mat = 'P';
                    $matricula = $item['mat_porcent'];
                }

                return (object)array(
                    'descripcion' => $item['descripcion'],
                    'id_persona' => $item['codigo'],
                    'ensenanza' => $ensenanza,
                    'tipo_ense' => $tipo_ensen,
                    'matricula' => $matricula,
                    'tipo_mat' => $tipo_mat,
                    'tipo_dscto' => $item['tipo_dscto'],
                    'estado' => '1',
                    'id_semestre' => $item['semestre'],
                );
            });;
            try{
                foreach ($data as $item) {
                    $completed[] = StudentData::addDescVicerectorado($item, $id_usuario_reg, $fecha_registro);
                }
                if(count($completed)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'success';
                    $jResponse['data'] = $completed;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'error';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listDescVicerectorado(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $per_page = $request->query('per_page');
            $student = $request-> query('student');
            $tipo_dscto = $request-> query('tipo_dscto');
            $id_semestre = $request-> query('id_semestre');
            $id_sede = $request-> query('id_sede');
            try {
                $data = StudentData::listDescVicerectorado($student, $tipo_dscto,$id_semestre, $per_page,  $id_sede);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listDescVicerectoradoxls(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $archivo = 'excel';

        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $per_page = 0;
            $student = $request-> query('student');
            $tipo_dscto = $request-> query('tipo_dscto');
            $id_semestre = $request-> query('id_semestre');
            $id_sede = $request-> query('id_sede');
            try {
                $data = StudentData::listDescVicerectorado($student, $tipo_dscto,$id_semestre, $per_page,  $id_sede,'S');
                $excel = Excel::create($archivo, function($excel) use($data){

                    $excel->sheet('descuentoespecial', function($sheet) use($data) {
                        $sheet->loadView("xls.finanzas.descuentoespecial")->with('data',$data);
                        $sheet->setOrientation('landscape');
                    });

                });
                
                $archivo= ($excel->string('xls'));

                $doc  = base64_encode($archivo);
                $jResponse = [
                             'success' => true,
                             'message' => "OK",
                             'data' => ['items'=>$doc]
                         ];

                return response()->json($jResponse);

            } catch (Exception $e) {
                $$mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            }
        }
        $excel = Excel::create($archivo, function($excel) use($mensaje){

            $excel->sheet('Error', function($sheet) use($mensaje) {
               $sheet->loadView("xls.error")->with('mensaje',$mensaje);
               $sheet->setOrientation('landscape');
           });

        });

        $file = $excel->string('xls');
        $doc  = base64_encode($file);

        $jResponse = [
                'success' => true,
                'message' => "OK",
                'data' => ['items'=>$doc]
            ];

        return response()->json($jResponse);
    }
    public function deleteDescVicerectorado($id_alumno_descuento_vice) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
     
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = StudentData::deleteDescVicerectorado($id_alumno_descuento_vice);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $message;
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
    public function showDescVicerectorado($id_alumno_descuento_vice){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $data = StudentData::showDescVicerectorado($id_alumno_descuento_vice);  
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] =  $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateDescVicerectorado($id_alumno_descuento_vice, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];

        $date = Carbon::now();
        $fecha_registro = $date->format('Y/m/d H:m:s');

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::updateDescVicerectorado($request, $id_alumno_descuento_vice, $id_usuario_reg, $fecha_registro);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function situacionMatricula(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::situacionMatricula($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('cantidad')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function situacionMatriculaExcel(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $params = array(
        'id_entidad' => $jResponse["id_entidad"],
        'id_depto' => $jResponse["id_depto"]);
        $jResponse = base64_encode(StudentData::getSituacionMatriculaExcel($request, $params));
        return response()->json($jResponse);
    }

    public function situacionMatriculaDetalle(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::situacionMatriculaDetalle($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $query  = collect($data)->groupBy('nombre_facultad');
                    $datar = array();
                    foreach ($query as $key => $value) {
                        array_push($datar, ['facultad' => $key, 'data' => $value]);
                    }
                    $jResponse['data'] = $datar ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function facultadSituacionMatriculaDetalle(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::facultadSituacionMatriculaDetalle($request);
                if (count($data) > 0) {
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
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function facultades(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::facultades($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('cantidad')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function facultadesDetalle(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::facultadesDetalle($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $query  = collect($data)->groupBy('nombre_escuela');
                    $datar = array();
                    foreach ($query as $key => $value) {
                        array_push($datar, ['escuela' => $key, 'data' => $value]);
                    }
                    $jResponse['data'] = $datar ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function escuelaEstadistica(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::escuelaEstadistica($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('cantidad')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function escuelaEstadisticaDetalle(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::escuelaEstadisticaDetalle($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $query  = collect($data)->groupBy('ciclo');
                    $datar = array();
                    foreach ($query as $key => $value) {
                        array_push($datar, ['ciclo' => $key, 'data' => $value]);
                    }
                    $jResponse['data'] = $datar ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function vivienda(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::vivienda($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('cantidad')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function semestre()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::semestre();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listTransferDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $items = StudentData::listTransferDetails($id_voucher);
                $total = StudentData::listTransferDetailsTotal($id_voucher);
                if ($items){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = ['items' => $items,'total'=> $total];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listTransferResumen(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $resumen = StudentData::listTransferResumen($id_voucher);
                $total = StudentData::listTransferResumenTotal($id_voucher); 
                if ($resumen){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [
                        'items' => $resumen,
                        'total'=>$total,
                    ];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse);
    }

     // para generar el pdf
     public function listTransferResumenPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $username   =  $jResponse["email"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $numero = $request->query('numero');
                $lote = $request->query('lote');
                $fecha = $request->query('fecha');

                $resumen = StudentData::listTransferResumen($id_voucher);
                $total = StudentData::listTransferResumenTotal($id_voucher); 
                if ($resumen){
                    $pdf = DOMPDF::loadView('pdf.finances.transferSummary',[
                        'resumen'=>$resumen,
                        'total'=>$total,
                        'id_depto'=>$id_depto,
                        'numero'=>$numero,
                        'fecha'=>$fecha,
                        'lote'=>$lote,
                        'username'=>$username // OBLIGATORIO
                        ])->setPaper('a4', 'portrait'); 

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            }
        } else {
            $mensaje=$jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error',[
            'mensaje'=>$mensaje
            ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
        ];
        return response()->json($jResponse);
    }

    public function listTransferDetailsPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $username   =  $jResponse["email"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse =[];
            try{
                $id_voucher = $request->query('id_voucher');
                $numero = $request->query('numero');
                $lote = $request->query('lote');
                $fecha = $request->query('fecha');
                $items = StudentData::listTransferDetails($id_voucher);
                $total = StudentData::listTransferDetailsTotal($id_voucher);
                if ($items){
                    $pdf = DOMPDF::loadView('pdf.finances.transferDetails',[
                        'items'=>$items,
                        'total'=>$total,
                        'id_depto'=>$id_depto,
                        'numero'=>$numero,
                        'fecha'=>$fecha,
                        'lote'=>$lote,
                        'username'=>$username // OBLIGATORIO
                        ])->setPaper('a4', 'portrait');

                    $doc =  base64_encode($pdf->stream('print.pdf'));

                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc],
                        'code' => "200",
                    ];
                }
                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            }
        } else {
            $mensaje=$jResponse["message"];
        }
        $pdf = DOMPDF::loadView('pdf.error',[
            'mensaje'=>$mensaje
            ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$doc]
        ];
        return response()->json($jResponse);
    }

    public function seguimientoAlumno(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::seguimientoAlumno($request, $id_entidad, $id_depto);
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function seguimientoAlumnoExcel(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::seguimientoAlumnoExcel($request, $id_entidad, $id_depto);
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function llamadaAlumno($id_persona, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::llamadaAlumno($id_persona, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function mensajeAlumno($id_persona, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::mensajeAlumno($id_persona, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function getFacultad(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getFacultad($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getEscuela(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getEscuela($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function llamadaAlumnoFinancial(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha = $date->format('Y-m-d H:i:s');
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::llamadaAlumnoFinancial($id_user, $request, $fecha);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function mensajeAlumnoFinancial(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::mensajeAlumnoFinancial($id_user, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function bloqueoAlumno(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";

            $id_persona = $request->id_persona;
            try {
                $data = StudentData::bloqueoAlumno($id_persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function saldoAlumno(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            $id_persona = $request->id_persona;
            $id_anho = $request->id_anho;
            try {
                $data = StudentData::saldoAlumno($id_persona, $id_entidad, $id_depto, $id_anho);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function tipoContrato(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::tipoContrato($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function anticiposAlumno(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::anticiposAlumno($request, $id_entidad, $id_depto);
                $total = StudentData::totalAnticiposAlumno($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['data' => $data, 'total' => $total];
                    
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function situacionCreditoMatricula(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::situacionCreditoMatricula($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('creditos')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function facultadCreditos(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::facultadCreditos($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('creditos')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function escuelaCreditos(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::escuelaCreditos($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data , 'total' => collect($data)->sum('creditos')] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function studentCroussing(Request $request)
    {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";

       
            try {
                $data = StudentData::studentCroussing($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function detalleDeLlamada(Request $request)
    {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::detalleDeLlamada($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listaDeEscuelas(Request $request)
    {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listaDeEscuelas($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function refinaciamientoEscuelaDetalle(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse['id_user'];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::refinaciamientoEscuelaDetalle($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['data' => $data, 'user' => $id_user];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function noMatriculadosRefinaciamientoEscuelaDetalle(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse['id_user'];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::noMatriculadosRefinaciamientoEscuelaDetalle($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['data' => $data, 'user' => $id_user];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function alumnoRefinanciamiento(Request $request)
    {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::alumnoRefinanciamiento($request);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listaDocumentos(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listaDocumentos($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['data' => $data, 'total_data' => collect($data)->sum('total')];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function inserConvenio(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse['id_user'];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');

        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::inserConvenio($request, $id_user_reg, $fecha_reg);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listConvenio(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listConvenio($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['data' => $data, 'importe' => collect($data)->sum('importe')];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateConvenioCumplio($id_cdetalle, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::updateConvenioCumplio($id_cdetalle, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listPrincipalConvenio(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listPrincipalConvenio($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listPrincipalConvenioStateCta(Request $request) {
        
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try { 
                $data = StudentData::listPrincipalConvenioStateCta($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateConvenioAnular($id_convenio, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::updateConvenioAnular($id_convenio, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateDetalleConvenio($id_cdetalle, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::updateDetalleConvenio($id_cdetalle, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit(); 
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteDetalleConvenio($id_convenio, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::deleteDetalleConvenio($id_convenio, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteConvenio($id_convenio){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::deleteConvenio($id_convenio);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function nuevoDetalleConvenio(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::nuevoDetalleConvenio($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function refinanciamientoPDf(Request $request){
    
    
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        // $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];
     
        if($valida == 'SI')
        {
            $mensaje = '';
            $jResponse = [];
            try
            {
                $id_persona =                    $request->id_persona;
                $id_escuela =                    $request->id_escuela;
                $id_convenio =                   $request->id_convenio;
                $id_contrato =                   $request->id_contrato;
                // $id_periodo_vac =       $request->id_periodo_vac;
                // $id_rol_vacacion =      $request->id_rol_vacacion;

                // $data = ParameterData::showTrabajadorHolidays($id_entidad, $id_depto, $id_periodo_vac, $id_persona); 
                $datosContrato = DB::table('DAVID.vw_acad_alumno_contrato as a')
                ->where('a.id_alumno_contrato',$id_contrato)
                ->first(); 
                // dd($datosContrato->id_resp_financiero);
                $datosRespFin = DB::table('moises.persona as p')
                ->leftJoin('MOISES.persona_documento as pdc', 
                function($join) { $join->on('pdc.id_persona', '=', 'p.id_persona')->where('pdc.id_tipodocumento','=','1');})
                ->leftJoin('MOISES.persona_direccion as pdi', 'pdi.id_persona', '=', 'p.id_persona')
                ->leftJoin('MOISES.VW_UBIGEO as ub','ub.id_ubigeo','=','pdi.id_ubigeo') 
                ->leftJoin('MOISES.persona_telefono as pt', function($join) {
                    $join->on('pt.id_persona', '=', 'p.id_persona')
                         ->where('pt.es_activo', '=', '1');
                }) 
                ->where('p.id_persona', '=', $datosContrato->id_resp_financiero)
                ->select([
                    DB::raw("fc_nombre_persona(p.id_persona) as nombre"),
                    DB::raw("nvl(pdc.num_documento,'_ _ _ _ _ _ _ _ _ _ _ _ _') as num_documento"), 
                    DB::raw("nvl(pt.num_telefono, '_ _ _ _ _ _ _ _ _ _ _ _ _') as num_telefono"),
                    DB::raw("nvl(pdi.direccion, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _') as direccion"),
                    DB::raw("nvl(ub.distrito , '_ _ _ _ _ _ _ _ _ _ _ _ _') as distrito"),
                    DB::raw("nvl(ub.provincia, '_ _ _ _ _ _ _ _ _ _ _ _ _') as provincia")
                ])
                ->first();  

                $datosPersonales = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO as a')
                                            ->where('a.id_persona', $id_persona)
                                            ->select('a.id_persona', 'a.nom_persona', 'a.num_documento', 'a.codigo',
                                            DB::raw("(SELECT MAX(X.NUM_TELEFONO) FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = a.ID_PERSONA AND X.ID_TIPOTELEFONO = 5 AND X.ES_ACTIVO = 1) AS CELULAR"))
                                            ->first();

                $celular = 'No tiene registrado';
                if ($datosPersonales and $datosPersonales->celular) {
                    $celular =  $datosPersonales->celular;
                }

                $escuela = DB::table('david.VW_ACAD_PROGRAMA_ESTUDIO as a')
                                    ->where('a.id_escuela', $id_escuela)
                                    ->select('a.id_escuela', 'a.nombre')
                                    ->first();

                $convenio = DB::table('eliseo.fin_convenio')
                                    ->where('id_convenio', $id_convenio)
                                    ->select('id_convenio', 'total', DB::raw("to_char(fecha, 'YYYY-MM-DD') as fecha"), 'observaciones')
                                    ->first();
                $convenioDetalle = DB::table('eliseo.fin_convenio_detalle')
                                    ->where('id_convenio', $convenio->id_convenio)
                                    ->select('cuota',  DB::raw("to_char(fecha, 'YYYY-MM-DD') as fecha"), 'importe')
                                    ->get();
                $pdf = DOMPDF::loadView('pdf.finances-student.refinanciamiento',[
                    // 'datos_alumno'      => $datosPersonales,
                    'celular'           => $celular,
                    'escuela'           => $escuela,
                    'convenio'          => $convenio,
                    'convenio_detalle'  => $convenioDetalle,
                    'datos_resp_fin'  => $datosRespFin,
                    'datos_contrato' => $datosContrato

                    ])->setPaper('a4', 'portrait');
                // return $pdf->stream('refinanciamiento.pdf');
                $doc =  base64_encode($pdf->stream('print.pdf'));
                if ($doc) {
                    // dd($doc);
                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc]
                    ];
                } else  {
                    $jResponse = [
                        'success' => false,
                        'message' => "Sin resultados",
                        'data' => ['items'=> '']
                    ];
                }
        
                return response()->json($jResponse);
            }
            catch(Exception $e)
            {
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
    
            }
        }else{
            $mensaje=$jResponse["message"];
        }
            
        $pdf = DOMPDF::loadView('pdf.error',[
                    'mensaje'=>$mensaje
                    ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => false,
                    'message' => "No se encontro resultados",
                    'data' => ['items'=> '']
                ];
        return response()->json($jResponse);
            
    }

    public function emailRefinanciacionConvenio(Request $request){
        // dd('ffff');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];
            try{

                $email = $request->correo;
                $id_persona = $request->id_persona;
                $datosPersonales = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO as a')
                                            ->where('a.id_persona', $id_persona)
                                            ->select('a.id_persona', 'a.nom_persona', 'a.num_documento', 'a.codigo',
                                            DB::raw("(SELECT MAX(X.NUM_TELEFONO) FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = a.ID_PERSONA AND X.ID_TIPOTELEFONO = 5 AND X.ES_ACTIVO = 1) AS CELULAR"))
                                            ->first();
                
                if($email) {
                   
                    $data = array('nombres'=> $datosPersonales->nom_persona, 'num_documento' => $datosPersonales->num_documento);
                    $file = $request->file('refinanciacion_pdf');
                    $filename = $file->getClientOriginalName();
                  
                    Mail::send('emails.refinanciacionConvenio', $data, function($message) use($file,$filename,$email){
                           $message->subject('Refinanciamiento de pago en cuotas - Universidad Peruana Unión');
                           $message->to($email);
                           $message->attach($file, [
                            'as' =>  $filename,
                            'mime' => 'application/pdf',
                            ]);
                        });
                $jResponse['success'] = true;
                $jResponse['message'] = 'La refinanciacion se envio satisfactoriamente';
                $jResponse['data'] = $email;
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No tiene correo";
                $jResponse['data'] = [];
                $code = "202";
                    }
        }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function convenioPDf(Request $request){
    
    
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        // $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];
     
        if($valida == 'SI')
        {
            $mensaje = '';
            $jResponse = [];
            try
            {
                $id_persona =                    $request->id_persona;
                $id_escuela =                    $request->id_escuela;
                $id_convenio =                   $request->id_convenio;

                  $datosPersonales = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO as a')
                                            ->where('a.id_persona', $id_persona)
                                            ->select('a.id_persona', 'a.nom_persona', 'a.num_documento', 'a.codigo',
                                            DB::raw("(SELECT MAX(X.NUM_TELEFONO) FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = a.ID_PERSONA AND X.ID_TIPOTELEFONO = 5 AND X.ES_ACTIVO = 1) AS CELULAR"))
                                            ->first();
                                         
                 $convenio = DB::table('eliseo.fin_convenio')
                                    ->where('id_convenio', $id_convenio)
                                    ->select('id_convenio', 'total', DB::raw("to_char(fecha, 'YYYY-MM-DD') as fecha"), 'observaciones', 'id_empleado', 'numero')
                                    ->first();

                $convenioDetalle = DB::table('eliseo.fin_convenio_detalle')
                                    ->where('id_convenio', $convenio->id_convenio)
                                    ->select('cuota',  DB::raw("to_char(fecha, 'YYYY-MM-DD') as fecha"), 'importe')
                                    ->get();
                $datosResponsable = DB::table('moises.persona as a')
                                    ->join('moises.persona_documento as b', 'a.id_persona', '=', 'b.id_persona')
                                    ->where('a.id_persona', $convenio->id_empleado)
                                    ->where('b.id_tipodocumento', 1)
                                    ->select(DB::raw("(a.nombre|| ' ' ||a.paterno|| ' ' ||a.materno) as nombre_empleado"), 'b.num_documento')
                                    ->first();
                // dd($datosResponsable);
                $pdf = DOMPDF::loadView('pdf.finances-student.convenio',[
                    'datos_alumno'      => $datosPersonales,
                    'datos_empleado'    => $datosResponsable,
                    // 'escuela'           => $escuela,
                    'convenio'          => $convenio,
                    'convenio_detalle'  => $convenioDetalle,
                    'nombre_trabajador' => 'Cristian',
                    ])->setPaper('a4', 'portrait');
                 
                $doc =  base64_encode($pdf->stream('print.pdf'));
                
                if ($doc) {
                    // dd($doc);
                    $jResponse = [
                        'success' => true,
                        'message' => "OK",
                        'data' => ['items'=>$doc]
                    ];
                } else  {
                    $jResponse = [
                        'success' => false,
                        'message' => "Sin resultados",
                        'data' => ['items'=> '']
                    ];
                }
        
                return response()->json($jResponse);
                
            }
            catch(Exception $e)
            {
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
    
            }
        }else{
            $mensaje=$jResponse["message"];
        }
            
        $pdf = DOMPDF::loadView('pdf.error',[
                    'mensaje'=>$mensaje
                    ])->setPaper('a4', 'portrait');
        // $pdf->save($ruta);
                        
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
                    'success' => false,
                    'message' => "No se encontro resultados",
                    'data' => ['items'=> '']
                ];
        return response()->json($jResponse);
            
    }
    public function getSede(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
               
                $data = StudentData::getSede($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getResidencia(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getResidencia($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getListaAlumnoInterno(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getListaAlumnoInterno($request);
                if (count($data) > 0) {
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
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public  function generarIndiceMorosidad(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
               
                $return  =  StudentData::generarIndiceMorosidad($request, $id_user, $id_entidad, $id_depto); 
                // dd($return, 'ssss');
                if ($return['nerror']==0) {
                    $data  =  StudentData::indiceMorosidad($request, $id_entidad, $id_depto,$id_user);
                    // dd($data);
                  if (count($data)>0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = "The item was created successfully";                    
                      $jResponse['data'] = $data;
                      $code = "200";  
                  } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe items";
                    $jResponse['data'] = [];
                    $code = "202";
                  }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                  }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function indiceMorosidadDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::indiceMorosidadDetalle($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addNotasFinancieras(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];

        $date = Carbon::now();
        $fecha_registro = $date->format('Y/m/d H:m:s');

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::addNotasFinancieras($request, $id_usuario_reg, $fecha_registro);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listNotasFinancieras(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listNotasFinancieras($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function updateNotasFinancieras($id_compromiso, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::updateNotasFinancieras($id_compromiso, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listProrroga(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listProrroga($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function listFinancista(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                /*
                if ($request->id_sede) {
                    $id_depto = $request->id_sede;
                }
                */

                if ($request->id_sede=='1') {
                    $id_depto = '1';
                } else if ($request->id_sede=='2') {
                    $id_depto = '5';
                } else if ($request->id_sede=='3') {
                    $id_depto = '6';
                }else {
                    $id_depto = '1';
                }


                $data = StudentData::listFinancista($id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function addFinancistaMasivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $date = Carbon::now();
        $fecha_create = $date->format('Y/m/d H:m:s');
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::addFinancistaMasivo($request, $id_entidad, $id_depto, $id_usuario_reg, $fecha_create);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public  function generarRecuperacion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
               
                $return  =  StudentData::generarRecuperacion($request, $id_user, $id_entidad, $id_depto); 
                // dd($return, 'ssss');
                if ($return['nerror']==0) {
                    $data  =  StudentData::indiceRecuperacion($request, $id_entidad, $id_depto);
                    // dd($data);
                  if (count($data)>0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = "The item was created successfully";                    
                      $jResponse['data'] = $data;
                      $code = "200";  
                  } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe items";
                    $jResponse['data'] = [];
                    $code = "202";
                  }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                  }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function indiceRecuperacionDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::indiceRecuperacionDetalle($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function verificarExelAlumnos(Request $request){
        // dd($request->excel);
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[]; 
            try{
                $dc    =     $request->dc;
                $id_anho    =     $request->id_anho;
                $datin =  \Excel::load($request->excel);
                $data = $datin->toArray();
                $lista = array();
                // $i = 0;
                foreach($data as $d){
                    // if($i>=0){

                        $id_cliente = StudentData::ShowIDAlumno($d['codigo']);

                        $obj = [
                            'codigo'             =>    $d['codigo'],
                            'importe'            =>    $d['importe'],
                            'importe_me'         =>    $d['importe_me'],
                            'id_cliente'         =>    $id_cliente,
                        ];
                            array_push($lista, $obj);
                            // $lista[] =$obj;
                    // }
                    // $i++;
                }
                
                if ($dc == 'C') {
                    $data = StudentData::verificarExelAlumnosCredito($lista, $id_entidad, $id_depto, $id_anho);
                } else {
                    $data = StudentData::verificarExelAlumnos($lista);
                }
                if (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Registros verificados';                 
                    $jResponse['data'] = $data;
                    $code = "200";  
                } else {
                  $jResponse['success'] = false;
                  $jResponse['message'] = 'Ocurrio un error';
                  $jResponse['data'] = [];
                  $code = "202";
                }
          }catch(Exception $e){
              $jResponse['success'] = false;
              $jResponse['message'] = $e->getMessage();
              $jResponse['data'] = [];
              $code = "202";
          }  
        }        
        return response()->json($jResponse,$code);
    }
    public function addSalesImports(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y/m/d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = "";
                $tiene_params = "N";

                $id_comprobante = $request->id_comprobante;
                $id_tipoventa = $request->id_tipoventa;
                $id_moneda = $request->id_moneda;
                $glosa = $request->glosa;
                $dc = $request->dc;
                $id_tiponota = null;
                if($id_comprobante != "03"){
                    $id_tiponota = $request->id_tiponota;
                }
                $alumnos = json_decode($request->alumno);// Array de Alumnos
                $asientos = json_decode($request->asiento);// Array de Asientos

                $archivo        = $request->file('file_archivo');
                $carpeta         = $request->carpeta;
                $fileName = '';
                $parametros = $request;
                // $fileAdjunto = ['nerror' => 1, 'message' => '', 'filename' => ''];

                //VALIDA AÑO, MES, TC, Y PARAMETROS
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, $id_moneda, $tiene_params, $params);
                //$rpta["nerror"] == 0;
                if ($rpta["nerror"] == 0) {
                    DB::beginTransaction();

                    if ($id_comprobante == "99") {

                        // $fileAdjunto = ComunData::uploadFile($archivo, $carpeta);
                        // if ($fileAdjunto['nerror']==0) {
                        //     $fileName = $fileAdjunto['filename']; 
                        // }

                        // Subir archivo aquí
                        $storage = new StorageController(); 
                        $file_data = $storage->postFile($archivo, $carpeta);

                    }

                    foreach($alumnos as $items){
                        // dd($items);
                        $id_parent = null;
                        $id_comprobante_ref = null;
                        $serie_ref = null;
                        $numero_ref = null;
                        $fecha_ref = null;

                        if($id_comprobante == "03"){
                            $data = StudentData::addSalesImports($id_entidad,$id_depto,$id_user,$id_comprobante,$id_tipoventa,$id_moneda,$glosa,$id_tiponota,$items->id_persona,$items->importe,$items->importe_me,$id_parent,$id_comprobante_ref,$serie_ref,$numero_ref,$fecha_ref);
                            if($data['id_venta'] && $data['id_vdetalle']){
                                foreach($asientos as $rows){
                                    StudentData::addSalesSeatsImports('V',$data['id_venta'],$data['id_vdetalle'],$rows->id_cuentaaasi,$rows->id_restriccion,$rows->id_ctacte,$rows->id_fondo,$rows->id_depto,$rows->dc,$rows->es_eap,$rows->porcentaje);
                                }
                                $total = StudentData::showSeatsSales(1,$data['id_vdetalle']);
                                if($total == 0){
                                    DB::commit(); 
                                }else{
                                    DB::rollback();
                                }
                            }
                            $jResponse['success'] = true;
                            $jResponse['message'] = "The item was inserted successfully";
                            $jResponse['data'] = [];
                            $code = "200"; 
                        }elseif ($id_comprobante == "99") {
                            // dd($items->id_persona,$items->importe,$items->importe_me);
                            $data = StudentData::addTransferImports($id_entidad,$id_depto,$id_user,$id_tipoventa,$id_moneda,$glosa,$items->id_persona,$items->importe,$items->importe_me, $dc);
                            if($data['id_transferencia']){

                                $parametros['tipo'] = 'T'; // Transferencia
                                $parametros['id'] = $data['id_transferencia'];
                                $result = ParameterData::saveDatosFile($parametros, $fecha_reg, explode('/',$file_data['data'],4)[3], $file_data['data']); // el servicio se usa en otros lugares

                                foreach($asientos as $rows){
                                    StudentData::addSalesSeatsImports('T',$data['id_transferencia'],$data['id_transferencia'],$rows->id_cuentaaasi,$rows->id_restriccion,$rows->id_ctacte,$rows->id_fondo,$rows->id_depto,$rows->dc,$rows->es_eap,$rows->porcentaje);
                                }
                                $total = StudentData::showSeatsSales(2,$data['id_transferencia']);
                                if($total == 0){
                                    DB::commit(); 
                                }else{
                                    DB::rollback();
                                }
                            }
                            $jResponse['success'] = true;
                            $jResponse['message'] = "The item was inserted successfully";
                            $jResponse['data'] = [];
                            $code = "200";
                        }else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "Alto: Proceso en Construccion";
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
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

    public function metaLlamadas(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";  
            try {
                $data = StudentData::metaLlamadas($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaLlamadasDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaLlamadasDetalle($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function respondioLlamadasDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::respondioLlamadasDetalle($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaLlamadasDetalleAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaLlamadasDetalleAcumulado($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function respondioLlamadasDetalleAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::respondioLlamadasDetalleAcumulado($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    
    public function metaLlamadasAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaLlamadasAcumulado($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaDeuda(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaDeuda($request,  $id_entidad );
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaDeudaAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaDeudaAcumulado($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaPromesadePago(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaPromesadePago($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaPromesadePagoDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaPromesadePagoDetalle($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaPromesadePagoDetalleCumplio(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaPromesadePagoDetalleCumplio($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    
    public function metaPromesadePagoAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaPromesadePagoAcumulado($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaPromesadePagoDetalleAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaPromesadePagoDetalleAcumulado($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaPromesadePagoDetalleAcumuladoCumplido(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaPromesadePagoDetalleAcumuladoCumplido($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaFinanciamiento(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaFinanciamiento($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaFinanciamientoDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaFinanciamientoDetalle($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    
    public function metaFinanciamientoAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaFinanciamientoAcumulado($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function metaFinanciamientoDetalleAcumulado(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::metaFinanciamientoDetalleAcumulado($request, $id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function obtenerPDFDoc(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $tipo_documento = $request->tipo_documento;
                $numero_legal = $request->numero_legal;
                // $data = StudentData::venta($id_venta);
                $tipo='';
                $file='';
                $legal= '';
                $ruc='20138122256';
                $usuario='Wilder+Marlo';
                if($tipo_documento and $numero_legal){
                    // foreach($data as $row){
                        $tipo=$tipo_documento;
                        $legal=$numero_legal;
                    // }
                    $file=$ruc.'-'.$tipo.'-'.$legal.'.txt';
             
                }else{
                    dd('No hay datos para descargar');
                }
                $url= 'http://efac.upeu.edu.pe:8080/rest/api/v1/document/pdf';
           
                $params ='documentTypeCode='.$tipo.'&fileName='.$file.'&legalNumber='.$legal.'&ruc='.$ruc.'&usuario='.$usuario;
                // $params ='documentTypeCode=03&fileName=20138122256-03-B102-00060754.txt&legalNumber=B102-00060754&ruc=20138122256&usuario=Wilder+Marlo';
        
                // dd($params, $params1);
                $url.='?'.$params;
          
                $cliente = curl_init();
        
                curl_setopt($cliente, CURLOPT_URL, $url);
                curl_setopt($cliente, CURLOPT_HEADER, false);
                curl_setopt($cliente, CURLOPT_RETURNTRANSFER, 1);
        
                $respuesta = curl_exec($cliente);
                
        
                $err = curl_error($cliente);
                curl_close($cliente);
                $file=$legal.'.pdf';
        
                if (strlen($err)==0 and $respuesta) {
                    $doc =  base64_encode($respuesta);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =['items'=>$doc, 'legal' => $ruc.'-'.$legal];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = ['items'=> '', 'legal' => $ruc.'-'.$legal];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
     
    }


    public function reporteDescuentoBecas(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::reporteDescuentoBecas($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function AgregarFinancista(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::AgregarFinancista($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listFinancistaAnexo(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $id_entidad = $request->id_entidad;
                $id_depto = $request->id_depto;
                $data = StudentData::listFinancistaAnexo($id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function saveAnexo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::saveAnexo($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = $response['data'];          
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteAnexo($id_anexo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::deleteAnexo($id_anexo);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateFinancista($id_financista, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::updateFinancista($id_financista, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteFinancista($id_financista){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::deleteFinancista($id_financista);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function getStudentFinancier($id_alumno) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                

                $data = StudentData::getStudentFinancier($id_alumno);

                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public function listSalesContract(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $id_cliente = $request->id_persona;
                $id_alumno_contrato = $request->id_alumno_contrato;
                $estado_vnt = "";
                $contract = StudentData::showContractStatus($id_cliente, $id_alumno_contrato);
                if (count($contract) > 0) {
                    foreach ($contract as $key => $item){
                        $id_comprobante = $item->id_comprobante;
                        $estado = $item->estado;
                    }
                    if($estado == "1"){//Alumno Matriculado Academicamente
                        if($id_comprobante == "03" || $id_comprobante == "01"){//Matricula con Venta
                            $data = StudentData::listSalesContract($id_cliente, $id_alumno_contrato);
                            if (count($data) > 0) {//Anular el contrato
                                foreach ($data as $key => $item){
                                    $id_venta = $item->id_venta;
                                    $activo = $item->activo;
                                    $lote = $item->lote;
                                }
                                if($activo == "S"){//Asiento sin Contabilizar
                                    $deposito = StudentData::showSalesDeposit($id_venta);
                                    if (count($deposito) == 0) {

                                        $sales = SalesData::showSalesStatus($id_venta);
                                        foreach ($sales as $key => $value){
                                            $id_comprobante = $value->id_comprobante;
                                            $numero_legal = $value->numero_legal;
                                        }
                                        //Usar cx a efac: 192.168.13.235, para ver el estado del comprobante
                                        $sale_efac = SalesData::showSaleEfac($id_comprobante,$numero_legal,'T');
                                        foreach ($sale_efac as $key => $value){
                                            $estado_vnt = $value->estado;
                                        }
                                        if($estado_vnt == "" || $estado_vnt == null){
                                            $estado_vnt = "X";
                                        }
                                        if($estado_vnt == "PD" || $estado_vnt == "ER" || $estado_vnt == "PB" || $estado_vnt == "DB" || $estado_vnt == "RH" || $estado_vnt == "X" || $estado_vnt == "AN"){
                                            $jResponse['success'] = true;
                                            $jResponse['message'] = 'OK, Anular Contrato y la Venta';
                                            $jResponse['data'] = $data;
                                            $jResponse['oper'] = 0;
                                            $jResponse['id_venta'] = $id_venta;
                                            $code = "200";
                                        }else{
                                            $jResponse['success'] = false;
                                            $jResponse['message'] = "ALTO: NO SE PUEDE ANULAR LA VENTA, SU ESTADO ES: " .$estado.", Anular Solo el Contrato";
                                            $jResponse['data'] = $data;
                                            $jResponse['oper'] = 1;
                                            $code = "200";
                                        }
                                    }else{
                                        $jResponse['success'] = true;
                                        $jResponse['message'] = 'OK, Anular SOLO el Contrato, la Venta tiene un Deposito, Realice una Nota de Credito';
                                        $jResponse['data'] = $data;
                                        $jResponse['oper'] = 1;
                                        $code = "200";
                                    }
                                }else{//Asiento Contabilizado
                                    $jResponse['success'] = true;
                                    $jResponse['message'] = 'OK, Anular SOLO el Contrato, el ASIENTO de la Venta ya esta CONTABILIZADO :'.$lote;
                                    $jResponse['data'] = $data;
                                    $jResponse['oper'] = 1;
                                    $code = "200";
                                }
                            }else{// Anular solo el Contrato
                                $jResponse['success'] = true;
                                $jResponse['message'] = 'OK, Anular SOLO el Contrato, la Venta ya esta ANULADA';
                                $jResponse['data'] = $data ;
                                $jResponse['oper'] = 1;
                                $code = "200";
                            }
                        }elseif($id_comprobante == "99"){//Matricula con Transferencia
                            $data = StudentData::listSalesTransferContract($id_cliente, $id_alumno_contrato);
                            if (count($data) > 0) {//Anular el contrato
                                foreach ($data as $key => $item){
                                    $id_transferencia = $item->id_transferencia;
                                    $activo = $item->activo;
                                    $lote = $item->lote;
                                }
                                if($activo == "S"){//Asiento sin Contabilizar
                                    $jResponse['success'] = true;
                                    $jResponse['message'] = 'OK, Anular Contrato y la Transferencia';
                                    $jResponse['data'] = $data;
                                    $jResponse['oper'] = 2;
                                    $jResponse['id_venta'] = $id_transferencia;
                                    $code = "200";
                                }else{//Asiento Contabilizado
                                    $jResponse['success'] = true;
                                    $jResponse['message'] = 'OK, Anular SOLO el Contrato, el ASIENTO de la Transferencia ya esta CONTABILIZADO :'.$lote;
                                    $jResponse['data'] = $data;
                                    $jResponse['oper'] = 1;
                                    $code = "200";
                                }
                            }else{// Anular solo el Contrato
                                $jResponse['success'] = true;
                                $jResponse['message'] = 'OK, Anular SOLO el Contrato, la Transferencia ya esta ANULADA';
                                $jResponse['data'] = $data ;
                                $jResponse['oper'] = 1;
                                $code = "200";
                            }
                        }else{
                            $jResponse['success'] = false;
                            $jResponse['message'] = 'Contrato con Estado = '.$estado.", Pero sin Venta";
                            $jResponse['data'] = [];
                            $jResponse['oper'] = 1;
                            $code = "202";
                        }
                    }else{//Alumno en Proceso Confirmado Academicamente
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'Contrato en Proceso, Anule la venta de manera manual';
                        $jResponse['data'] = [];
                        $jResponse['oper'] = 5;
                        $code = "200";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    } 
    public  function CancelContract(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_alumno_contrato = $request->id_alumno_contrato;
                $id_venta = $request->id_venta;
                $oper = $request->oper;
                if($oper == "0"){
                    $sales = SalesData::showSalesStatus($id_venta);
                    foreach ($sales as $key => $value){
                        $id_comprobante = $value->id_comprobante;
                        $numero_legal = $value->numero_legal;
                    }
                    //Usar cx a efac: 192.168.13.235, para ver el estado del comprobante
                    $sale_efac = SalesData::showSaleEfac($id_comprobante,$numero_legal,'T');
                    foreach ($sale_efac as $key => $value){
                        $estado = $value->estado;
                    }
                    if($estado == "" || $estado == null){
                        $estado = "X";
                    }
                    if($estado == "PD"  || $estado == "ER"){ // SE TIENE QUE ANALIZAR PARA ANULAR DIREECTAMENTE , POR AHORA SE ANULA EN EL EFAC
                        //ANULA EN EL EFAC
                        SalesData::updateSaleEfac($id_comprobante,$numero_legal,$estado);
                    }
                }

                $data  =  StudentData::CancelContract($id_alumno_contrato,$id_venta,$id_user,$oper);
                if (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = $data;
                    $code = "200";  
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe items";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function saveMetas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y/m/d H:m:s');
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::saveMetas($request, $id_user, $fecha_reg);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];          
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function getMetasSedes(Request $request) {
 
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getMetasSedes($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['data' =>  $data, 'total_creditos' => collect($data)->sum('cantidad_creditos'), 'total_alumnos' => collect($data)->sum('cantidad_alumnos')];

                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteMetas($id_meta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::deleteMetas($id_meta);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateMetas($id_semestre, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::updateMetas($id_semestre, $request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function getSemestreSegui(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getSemestreSegui($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getSedeSegui(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getSedeSegui($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getCicloSegui(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getCicloSegui($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getDocenteSegui(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::getDocenteSegui($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function saveDocentes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y/m/d H:m:s');
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = StudentData::saveDocentes($request, $id_user, $fecha_reg, $id_entidad, $id_depto);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];          
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
 
    public function situacionSedesMatricula(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::situacionSedesMatricula($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  ['dataset' => $data] ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
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
   
    
    public function listTipoEvidencia(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listTipoEvidencia($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =    $data  ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listPlanAlumno($id_persona)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::listPlanAlumno($id_persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =    $data  ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deptoProgramStudie($id_programa_estudio)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::deptoProgramStudie($id_programa_estudio);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =    $data  ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function saldoSedes(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = StudentData::saldoSedes($request, $id_entidad);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =    $data  ;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function finishTramiteRegistro(Request $request){
       $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad   = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user_reg = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:i:s');
        if($valida=='SI'){
            $jResponse=[];
            try{
                DB::beginTransaction();
                $response = StudentData::finishTramiteRegistro($request, $id_user_reg, $id_entidad, $id_depto);
                if ($response['nerror']==0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addFinancistaGlobal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $date = Carbon::now();
        $fecha_create = $date->format('Y/m/d H:m:s');
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = StudentData::addFinancistaGlobal($request, $id_entidad, $id_depto, $id_usuario_reg, $fecha_create);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];       
                    $jResponse['data'] = [];             
                    $code = "200";
                    DB::commit();  
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function checkStudentDiscountExcel(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $params = $request->all();
        //$file = $params['file-excel'];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";

            $data = StudentData::checkStudentDiscountExcel($params);
            if ($data['success']) {
                $jResponse['success'] = $data['success'];
                $jResponse['message'] = $data['message'];
                $jResponse['data'] =  $data['data'];
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

    public function sendMail(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $response = StudentData::sendMail($request);
                if($response['success']){
                    $jResponse['success'] = true;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $code = "202";
                }
                $jResponse['message'] = $response['message'];
                $jResponse['data'] = [];
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);

    }
    public function sendGroupMail() {
        $data = StudentData::sendGroupMail();

       /* $jResponse['success'] = $data['success'];
        $jResponse['message'] = $data['message'];
        $jResponse['data'] =  $data['data'];
        $code = "200";
        return response()->json($jResponse, $code);*/
    }
    public function getDinamic(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_usuario_reg = $jResponse['id_user'];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $data = StudentData::getDinamic($request, $id_entidad, $id_depto);  
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Lista exitosa';       
                    $jResponse['data'] = $data;             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe registros';                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function validaPago(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_usuario_reg = $jResponse['id_user'];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $count = StudentData::validaPago($id_usuario_reg);  
                if($count>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Válido';       
                    $jResponse['data'] =$count;             
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe';                        
                    $jResponse['data'] = $count;
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

}
// #sñññññ

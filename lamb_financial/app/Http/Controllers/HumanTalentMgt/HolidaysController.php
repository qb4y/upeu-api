<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\HolidaysData;
use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;

use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DOMPDF;
use Mail;
class HolidaysController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public  function addProceGeneratePerioVac(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $return  =  HolidaysData::addProceGeneratePerioVac($request);  
                  if ($return['nerror']==0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = "The item was created successfully";                    
                      $jResponse['data'] = [];
                      $code = "200";  
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
        public function getGeneratePerioVac(Request $request) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $id_periodo_vac = $request->id_periodo_vac;
                    $id_persona = $request->id_persona;
                    $data = HolidaysData::getGeneratePerioVac($id_periodo_vac,$id_persona);  
                        //    dd('sss', $data);
                    if(!empty($data)){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = ['object' => $data];
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
        public function saveProgramaming(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::saveProgramaming($id_user,$request);  
                    if($response['nerror']==0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";                    
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
        public function listProgramingVacation(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    $data = HolidaysData::listProgramingVacation($id_periodo_vac_trab);  
                    $jResponse['success'] = true;
                    if(!empty($data)){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";                    
                        $jResponse['data'] = ['items' => $data];
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
        public function deleteProgramingVacation($id_rol_vacacion) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
         
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $response = HolidaysData::deleteProgramingVacation($id_rol_vacacion);
                    if($response['nerror']==0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was deleted successfully";
                        $jResponse['data'] = [];
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
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            return response()->json($jResponse, $code);
        }
        public function updateProgramingVacation($id_rol_vacacion,Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            // dd();
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::updateProgramingVacation($id_user,$id_rol_vacacion, $request);  
                    if($response['nerror']==0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";                    
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
        public function listAprobeHeader(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                   
                    // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    $data = HolidaysData::listAprobeHeader($request);  
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = ['items' => $data];
                        $jResponse['success'] = true;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
                        $jResponse['data'] = [];
                        $jResponse['success'] = false;
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
        public function updateAprobeHeaderChild($id_periodo_vac_trab, Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user_apru = $jResponse["id_user"];
            $date = Carbon::now();
            $fecha_apru = $date->format('Y-m-d H:i:s');
            // dd('user', $id_user_apru, 'fecha', $fecha_apru, 'ids', $id_periodo_vac_trab, $request);
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::updateAprobeHeaderChild($id_periodo_vac_trab, $id_user_apru,  $fecha_apru, $request);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";                    
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
        
        public function rescheduleVacation(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            // dd();
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::rescheduleVacation($id_user, $request);  
                    if($response['nerror']==0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";                    
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $response['message'];                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage().' line: '.$e->getLine();
                    $code = "202";
                } 
            }        
            return response()->json($jResponse,$code);
        }
        public function getRescheduleVacation($id_parent){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
            if ($valida == 'SI') {
                $jResponse = [];
                // $id_parent = $request->query('id_parent');
                if ($id_parent) {
                    $data = HolidaysData::getRescheduleVacation($id_parent);
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

        public function listPeriodHolidays(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $per_page = $request->per_page;
                    $nombre = $request->nombre;
                    $id_entidad = $request->id_entidad;
                    // $id_periodo_vac_trab = $request->id_periodo_vac_trab;
                    $data = HolidaysData::listPeriodHolidays($id_entidad, $nombre, $per_page);  
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";                    
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";                        
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
        public function addPeriodHolidays(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::addPeriodHolidays($request);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";                    
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
        public function updatePeriodHolidays($id_periodo_vac,Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::updatePeriodHolidays($id_periodo_vac,$request);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";                    
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $response['message'];                        
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'on';//$e->getMessage();
                    $code = "202";
                } 
            }        
            return response()->json($jResponse,$code);
        }
        public function deletePeriodHolidays($id_periodo_vac) {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];
            if ($valida == 'SI') {
                $jResponse = [];
                try {
                    $result = HolidaysData::deletePeriodHolidays($id_periodo_vac);
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
                    $error = $e->getcode();
                    if($error == '2292') {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'El periodo vacacional se encuentra';
                        $jResponse['data'] = [];
                        $code = "202";
                    } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }
                }
            }
            return response()->json($jResponse, $code);
        }
        public  function agregarProceGeneratePerioVacMasivo(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                DB::beginTransaction();
                $jResponse=[];                        
                // dd('hola', $request);
                try{   
                    $return  =  HolidaysData::agregarProceGeneratePerioVacMasivo($request);  
                      if ($return['nerror']==0) {
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
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                    DB::rollback();
                } 
            }        
            return response()->json($jResponse,$code);
        }

        public function updateVacacionesConfirm($id_rol_vacacion, Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $user_confirmacion = $jResponse["id_user"];
            $date = Carbon::now();
            $fecha_confirmacion = $date->format('Y-m-d H:i:s');
            // dd('user', $id_user_apru, 'fecha', $fecha_apru, 'ids', $id_periodo_vac_trab, $request);
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::updateVacacionesConfirm($id_rol_vacacion,  $fecha_confirmacion,  $user_confirmacion, $request);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
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

        public function updateRetornoVacacionesConfirm($id_rol_vacacion, Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $user_confirmacion = $jResponse["id_user"];
            $date = Carbon::now();
            $fecha_confirmacion = $date->format('Y-m-d H:i:s');
            // dd('user', $id_user_apru, 'fecha', $fecha_apru, 'ids', $id_periodo_vac_trab, $request);
            if($valida=='SI'){
                $jResponse=[];                        
                try{   
                    $response = HolidaysData::updateRetornoVacacionesConfirm($id_rol_vacacion,  $fecha_confirmacion,  $user_confirmacion, $request);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was created successfully";
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
        public function myPapeletaHolidays(Request $request){
    
    
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
      
                    $id_entidad =           $request->id_entidad;
                    $id_depto =             $request->id_depto;
                    $id_periodo_vac =       $request->id_periodo_vac;
                    $id_persona =           $request->id_persona;
                    $id_rol_vacacion =      $request->id_rol_vacacion;

                    $data = ParameterData::showTrabajadorHolidays($id_entidad, $id_depto, $id_periodo_vac, $id_persona); 
                    
             
                    $nombres =          $data->nombres;
                    $num_documento =    $data->num_documento;
                    $area =             $data->nombre_area;
                    $puesto =           $data->nombre_puesto;
                    $id_sedearea =      $data->id_sedearea;

                    $query = DB::table('eliseo.plla_rol_vacacional')
                                        ->where('id_rol_vacacion', $id_rol_vacacion)
                                        ->select(DB::raw("to_char(fecha_confirmacion_salida, 'YYYY-MM-DD') as fecha_confirmacion"),
                                                 DB::raw("to_char(fecha_ini, 'YYYY-MM-DD') as fecha_ini"),
                                                 DB::raw("to_char(fecha_fin, 'YYYY-MM-DD') as fecha_fin"),
                                                 'dias')
                                        ->first();
                                 
                    $fecha_confirmacion_salida =    $query->fecha_confirmacion;
                    $fecha_ini =                    $query->fecha_ini;
                    $fecha_fin =                    $query->fecha_fin;
                    $dias =                         $query->dias;

                    $jefeArea = DB::table('ELISEO.PLLA_PERFIL_PUESTO as a')
                                          ->join('moises.VW_TRABAJADOR as b', 'a.id_puesto', '=', DB::raw("b.id_puesto and a.ID_SEDEAREA = b.ID_SEDEAREA"))
                                          ->select(DB::raw("(b.nombre|| ' ' ||b.paterno|| ' ' ||b.materno) as nombre_jefe"), 'b.id_persona')
                                          ->where('a.id_sedearea', $id_sedearea)
                                          ->where('a.nivel', 0)
                                          ->first();
                                    
                    $nombreJefeArea =  $jefeArea->nombre_jefe;
                    $id_persona_jefe =  $jefeArea->id_persona;
            
                    $sede = DB::table('ELISEO.ORG_SEDE_AREA')
                                        ->where('ID_SEDEAREA', $id_sedearea)
                                        ->select('id_sede')
                                        ->first();
                                  
                    $idSede = $sede->id_sede;

                    $jefeDTH = DB::table('ELISEO.PLLA_PERFIL_PUESTO as a')
                                        ->join('ELISEO.ORG_SEDE_AREA as b', 'a.id_sedearea', '=', 'b.id_sedearea')
                                        ->join('ELISEO.org_area as c', 'b.id_area', '=', 'c.id_area')
                                        ->join('moises.PERSONA as d', 'b.ID_PERSONA', '=', 'd.ID_PERSONA')
                                        ->where('b.id_entidad', $id_entidad)
                                        ->where('b.id_sede', $idSede)
                                        ->where('a.nivel', 0)
                                        ->where('c.codigo', '=', 'GTH')
                                        ->select(DB::raw("(d.nombre|| ' ' ||d.paterno|| ' ' ||d.materno) as nombre_dth"), 'd.id_persona')
                                        ->first();
                                      
                    $nombreDTH =  $jefeDTH->nombre_dth;
                    $id_persona_gth =  $jefeDTH->id_persona;

                    $firma_trabajador = '';
                    $traba = ParameterData::getFirmaTrabajador($id_persona);
                    if ($traba and $traba['nombre_firma'] and $traba['urls_dw']) {
                        $firma_trabajador = $traba['urls_dw'];
                    }
                    // dd($firma_trabajador);

                    $firma_jefe = '';
                    $jefe = ParameterData::getFirmaTrabajador($id_persona_jefe);
                    if ($jefe and $jefe['nombre_firma'] and $jefe['urls_dw']) {
                        $firma_jefe = $jefe['urls_dw'];
                    }

                    $firma_gth= '';
                    $gth = ParameterData::getFirmaTrabajador($id_persona_gth);
                    if ($gth and $gth['nombre_firma'] and $gth['urls_dw']) {
                        $firma_gth = $traba['urls_dw'];
                    }
                 
                    $pdf = DOMPDF::loadView('pdf.mgt.papeleta',[
                        'nombre_trabajador'=>$nombres,
                        'documento_trabajador'=>$num_documento,
                        'area_trabajador'=>$area,
                        'fecha_inicio_programacion'=>$fecha_ini,
                        'fecha_fin_programacion'=>$fecha_fin,
                        'dias_programadas'=>$dias,
                        'fecha_confirmacion' => $fecha_confirmacion_salida,
                        'puesto' => $puesto,
                        'nombre_jefe' => $nombreJefeArea,
                        'nombre_jefe_dth' => $nombreDTH,
                        'firma_trabajador' => $firma_trabajador,
                        'firma_jefe' => $firma_jefe,
                        'firma_gthr' => $firma_gth,
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
      
        public function emailPapeletaSalida(Request $request){
            // dd('ffff');
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
    
            if($valida=='SI'){
                $jResponse=[];
                try{
                    $id_entidad =           $request->id_entidad;
                    $id_depto =             $request->id_depto;
                    $id_periodo_vac =       $request->id_periodo_vac;
                    $id_persona =           $request->id_persona;
                    $id_rol_vacacion =      $request->id_rol_vacacion;

                    $data = ParameterData::showTrabajadorHolidays($id_entidad, $id_depto, $id_periodo_vac, $id_persona); 
                    
                    // dd($request);
                    $nombres =          $data->nombres;
                    $num_documento =    $data->num_documento;
                    $area =             $data->nombre_area;
                    $puesto =           $data->nombre_puesto;

                  

                    $query = DB::table('eliseo.plla_rol_vacacional')
                                        ->where('id_rol_vacacion', $id_rol_vacacion)
                                        ->select(DB::raw("to_char(fecha_confirmacion_salida, 'DD/MM/YYYY') as fecha_confirmacion"),
                                                 DB::raw("to_char(fecha_ini,  'DD/MM/YYYY') as fecha_ini"),
                                                 DB::raw("to_char(fecha_fin,  'DD/MM/YYYY') as fecha_fin"),
                                                 'dias')
                                        ->first();
                    $fecha_confirmacion_salida =    $query->fecha_confirmacion;
                    $fecha_ini =                    $query->fecha_ini;
                    $fecha_fin =                    $query->fecha_fin;
                    $dias =                         $query->dias;


                    $email = $request->correo;
                    if($email) {
                       
                        $data = array('nombres'=> $nombres, 'area'=>$area,
                                      'num_documento'=> $num_documento, 'puesto'=>$puesto,
                                      'fecha_confirmacion_salida'=> $fecha_confirmacion_salida,
                                      'fecha_ini'=> $fecha_ini, 'fecha_fin'=>$fecha_fin, 'dias'=>$dias);

                        $file = $request->file('papeleta_pdf');
                        $filename = $file->getClientOriginalName();
                        Mail::send('emails.envioPapeleta', $data, function($message) use($file,$filename,$email){
                               $message->subject('Papeleta de salida de vacaciones - Universidad Peruana Unión');
                               $message->to($email);
                               $message->attach($file, [
                                'as' =>  $filename,
                                'mime' => 'application/pdf',
                                ]);
                            });
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'La papeleta se envio satisfactoriamente';
                    $jResponse['data'] = $email;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se pudo enviar";
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
        public function addSolicitudHolidays(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user_reg = $jResponse["id_user"];
            $date = Carbon::now();
            $fecha_reg = $date->format('Y-m-d H:i:s');
            if($valida=='SI'){
                $jResponse=[];
                DB::beginTransaction();               
                try{ 
                    $response = HolidaysData::addSolicitudHolidays($request, $id_user_reg, $fecha_reg);  
                    if($response['success']){
                        $jResponse['success'] = true;
                        $jResponse['message'] = $response['message'];                     
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
        public function listReques(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                
                    $data = HolidaysData::listReques($request, $id_user);  
                    $jResponse['success'] = true;
                    if(!empty($data)){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";                    
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['success'] = true;
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
        public function showRequest(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            
            if($valida=='SI'){
                $jResponse=[];                        
                try{ 
                    $data = HolidaysData::showRequest($request);  
                    $jResponse['success'] = true;
                    if(!empty($data)){
                        // dd($data);
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Success";                    
                        $jResponse['data'] =$data;
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
            return response()->json($jResponse, $code);
    }
    public function listAdelantoDetalle($id_sol_vac_adel){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
               
                $data = HolidaysData::listAdelantoDetalle($id_sol_vac_adel);  
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
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
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function updateSolicitudHolidays($id_sol_vac_adel, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:i:s');
        if($valida=='SI'){
            $jResponse=[];
            DB::beginTransaction();               
            try{ 
                $response = HolidaysData::updateSolicitudHolidays($id_sol_vac_adel, $request, $id_user_reg, $fecha_reg);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $response['message'];                     
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
    public function deleteSolDetalle($id_sol_vac_adel_det){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = HolidaysData::deleteSolDetalle($id_sol_vac_adel_det);  
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
    public function deleteSolicitud($id_sol_vac_adel){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            try{
                $response = HolidaysData::deleteSolicitud($id_sol_vac_adel);  
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
    public function refusedAlularSolicitud($id_sol_vac_adel, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:i:s');
        if($valida=='SI'){
            $jResponse=[];                        
            try{
                $response = HolidaysData::refusedAlularSolicitud($id_sol_vac_adel, $request, $id_user_reg, $fecha_reg);  
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
    public  function agregarAdelantoVacacional(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user_reg = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:i:s');
        if($valida=='SI'){
            DB::beginTransaction();
            $jResponse=[];                        
            // dd('hola', $request);
            try{   
                $return  =  HolidaysData::agregarAdelantoVacacional($request, $id_user_reg, $fecha_reg);  
                  if ($return['nerror']==0) {
                      $jResponse['success'] = true;
                      $jResponse['message'] = $return['msgerror'];                 
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
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function listTrabajadorHolidays(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{ 
                
                $data = HolidaysData::listTrabajadorHolidays($request, $id_user);  
                $jResponse['success'] = true;
                if(!empty($data)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = ['items' => $data];
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
}

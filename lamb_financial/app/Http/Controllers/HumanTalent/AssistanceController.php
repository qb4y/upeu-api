<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\AssistanceData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Modulo\ModuloData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\HumanTalent\AssistanceDevice\Manager as ADManager;

class AssistanceController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function asistencia(Request $request){
        
        $id_depto   = $request->id_depto;
        $id_entidad = $request->id_entidad;
        $sql="select to_char(sysdate,'DD-MM-YYYY D HH24:MI') from dual";
        
        
        return view('asistencia.asientos');
    }
    public function listAssistsControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                //$id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $rpta = AssistanceData::asistencia($id_entidad,$id_depto,$id_user);
                if($rpta["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $rpta["data"];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["mensaje"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function updateAssistsControl($id_control_culto){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $asistencia = $params->asistencia;
                AssistanceData::actualizarControl($id_control_culto,$asistencia,$id_user);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' linea:'.$e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    
    public function listAssists(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_depto = $request->query('id_depto');
                $fecha = $request->query('fecha');
                $date = AssistanceData::showFecha($fecha);
                
                
                foreach ($date as $item){
                    $fecha_a = $item->fecha;               
                }
                $data = AssistanceData::listPersonal($id_entidad,$id_depto,$id_user);
                foreach ($data as $key => $value){  
                    $asisst = AssistanceData::listPersonalAssists($value->id_persona,$fecha,$fecha_a);
                    $parent[] = [
                                    'id_persona' => $value->id_persona, 
                                    'id_entidad' => $value->id_entidad,
                                    'id_depto' => $value->id_depto,
                                    'nombres' => $value->nombres,
                                    'letra' => $value->letra,
                                    'numero' => $value->numero,
                                    'confianza' => $value->confianza,
                                    'dni' => $value->dni,
                                    'asisst' => $asisst
                                ];            
                }
                if(count($parent)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $parent;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' linea:'.$e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }
    public function listSemana(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $fecha = $request->query('fecha');
                $data = explode("/", $fecha);
                $dia = intval($data[0]);
                $mes = intval($data[1]);
                $anho = intval($data[2]);
                $fecha_f= $anho."/".$mes."/".$dia;
                //for($x=0;$x<=6;$x++){
                    //$date = AssistanceData::showSemana($fecha);
                    /*foreach ($date as $item){
                        $fecha = $item->fecha; 
                        $ndia = $item->ndia;
                    }
                    if($ndia>1 && $ndia < 7){
                        $parent[] = [
                            'fecha' => $date[0]
                        ];
                    }*/
                //}
                $date = AssistanceData::showSemana($fecha_f);
                if(count($date)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $date;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' linea:'.$e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    public function polygonUserAssis(Request $request){
        /*
        Funcion para dibujar los poligonos del usuario (APP MOVIL)
        */

        $resp = GlobalMethods::authorizationLamb($this->request);

        $resp['persona'] = null;

        if($resp["valida"]=='SI'){

            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            $person = AssistanceData::getPersonForPolygon($resp['id_user']);

            $query = AssistanceData::polygonsUser($resp['id_user']);

            // remover el get User Device  este servicio ya no se necesita
            // $user_device = AssistanceData::getUserDevice($resp['id_user']);

            $collection = collect($query)->transform(function($item, $index) {
                $item->lat = (float)$item->lat;
                $item->lng = (float)$item->lng;
                return $item;
            });

            $resp['data'] = $collection->groupBy('id_mapa');
            $resp['persona'] = $person;

            // $resp['user_device'] = $user_device;
            
        }

        return response()->json($resp, $resp["code"]);
        
    }



    public function PolygonsByDepartParent(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI'){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            if ($request->has('id_depto')){

                $query = AssistanceData::polygonsByEntity(
                    $resp["id_entidad"],
                    $request->query('id_depto'));

                $super_data = array();
                $data_query = collect($query)->groupBy('id_mapa');

                $properties = [
                    'id'=>null,
                    'letter'=>null,
                    'draggable'=>false,
                    'editable'=>false,
                    'fillColor'=>'#800052',
                    'fillOpacity'=>0.45,
                    'strokeWeight'=>2,
                    'strokeColor'=>'#800052',
                    'clickable'=>true
                ];
                
                foreach($data_query as $key => $item) {

                    $properties['id'] = $key;
                    $properties['letter'] = $item->first()->nombre;
                    

                    $coordinates = $item->transform(function($item, $index) {
                                return array((float)$item->lng, (float)$item->lat);
                            })->toArray();

                    array_push($coordinates,$coordinates[0]);                    

                    $r = [
                        'type' => 'Feature',
                        'properties' => $properties,
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' =>array($coordinates)
                        ]];

                    array_push($super_data, $r);                    
                };

                $resp['data'] = [
                    "type" => "FeatureCollection",
                    "features" => $super_data];

            }
            
        }
        return response()->json($resp, $resp["code"]);
    }

    public function PolygonsSave(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            $id_depto = $request->input('id_depto');
            $features = $request->input('data.features');

            $polygons = AssistanceData::idPolygonsExist(
                    $resp["id_entidad"],
                    $id_depto);

            $input_polygons = array();

            foreach ($features as $key => $item) {

                $id_mapa = $item['properties']['id'];
                $nombre = $item['properties']['letter'];

                if ($polygons->contains($id_mapa)) {

                    $coordinates = collect($item['geometry']['coordinates'][0])
                    ->map(function($coord, $index) use ($id_mapa) {
                        return [
                            'lng'=>$coord[0],
                            'lat'=>$coord[1],
                            'orden'=>$index,
                            'id_mapa'=>$id_mapa];
                    });


                    $unique = $coordinates->unique('lng')->toArray();


                    AssistanceData::deleteCoordinates($id_mapa);

                    $state_create = AssistanceData::insertCoordinates($unique);

                    array_push($input_polygons, $id_mapa);

                    // ELIMINAR COORDENADAS
                    // CREA COORDENADAS


                }else {

                    $postData = array(
                        'ID_ENTIDAD'=>$resp["id_entidad"],
                        'ID_DEPTO'=>$id_depto,
                        'NOMBRE'=>$nombre);

                    $id_mapa_create = AssistanceData::insertPolygon($postData);

                    if ($id_mapa_create) {

                        $coordinates = collect($item['geometry']['coordinates'][0])
                        ->map(function($coord, $index) use ($id_mapa_create) {
                            return [
                                'lng'=>$coord[0],
                                'lat'=>$coord[1],
                                'orden'=>$index,
                                'id_mapa'=>$id_mapa_create];
                        });

                        $unique = $coordinates->unique('lng')->toArray();

                        $state_create = AssistanceData::insertCoordinates(
                            $unique);
                    } else {
                        // roolback
                    }


                    // CREA POLYGONO
                    // CREA COORDENADAS


                }

            }


            foreach ($polygons as $id) {
                if (!collect($input_polygons)->contains($id)) {
                    AssistanceData::deleteCoordinates($id);
                    AssistanceData::deletePolygon($id);
                }          

            }
                
            


            
            

            
        }
        return response()->json($resp, $resp["code"]);
    }


    public function DepartmentsByEntity(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI'){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            $resp['data'] = AssistanceData::departmentsByEntity(
                $resp['id_entidad'], $resp['id_user']);
        }
        return response()->json($resp, $resp["code"]);
    }


    public function DepartmentsPolygon(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);
        if($resp["valida"]=='SI'){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            $q = $request->query('id_mapa');
            if ($q) {
                $resp['data'] = AssistanceData::selectDeptosPolygon($q);
            }
            
        }
        return response()->json($resp, $resp["code"]);
    }


    public function PersonsPolygon(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);
        if($resp["valida"]=='SI'){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            $q = $request->query('id_mapa');
            if ($q) {
                $resp['data'] = AssistanceData::selectPersonsPolygon($q);
            }
            
        }
        return response()->json($resp, $resp["code"]);
    }


    public function SelectDepOrPerPolygon(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);
        if($resp["valida"]=='SI'){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            if ($request->has('query')) {
                $q = $request->query('query');
                $id_mapa = $request->query('id_mapa');

                if ($q) {
                    if ($request->query('type')=='PERSON') {
                        $resp['data'] = AssistanceData::searchPersonForPolygon($q, $id_mapa);    

                    } else if ($request->query('type')=='DEPTO') {
                        $resp['data'] = AssistanceData::departmentsForPolygon(
                            $resp['id_entidad'], $q, $id_mapa);
                    }
                }

            }            
            
        }
        return response()->json($resp, $resp["code"]);
    }


    public function DepartmentsPolygonSave(Request $request) {
        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Se guardo correctamente los deptos !!!';

            $bulkData = array();
            
            foreach ($request->input('id_depto') as $id_depto) {

                $dataDepto = [
                    'ID_ENTIDAD'=>$resp['id_entidad'],
                    'ID_DEPTO'=>$id_depto,
                    'ID_MAPA'=>$request->input('id_mapa')
                ];
                array_push($bulkData, $dataDepto);

            }

            AssistanceData::saveDeptoPolygon($bulkData);

        }

        return response()->json($resp, $resp["code"]);
    }


    public function PersonsPolygonSave(Request $request) {

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';


            $bulkData = array();

            foreach ($request->input('id_persona') as $id_persona) {
                $data = [
                    'ID_MAPA'=>$request->input('id_mapa'),
                    'ID_PERSONA'=>$id_persona
                ];
                array_push($bulkData, $data);

            }

            AssistanceData::savePersonPolygon($bulkData);
        }

        return response()->json($resp, $resp["code"]);
    }


    public function PersonsPolygonDelete(Request $request) {

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('delete')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            AssistanceData::deletePersonPolygon(
                $request->query('id_mapa'),
                $request->query('id_persona'));

            
        }

        return response()->json($resp, $resp["code"]);
    }

    public function DepartmentsPolygonDelete(Request $request) {

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('delete')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            AssistanceData::deleteDeptoPolygon(
                $request->query('id_depto'),
                $request->query('id_entidad'),
                $request->query('id_mapa'));            
        }

        return response()->json($resp, $resp["code"]);
    }


    public function PolygonConfigUpdate(Request $request) {

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('put')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            AssistanceData::updatePolygonConfig(
                $request->query('id_mapa'),
                $resp['id_entidad'],
                $request->query('id_depto'),
                [
                    'NOMBRE'=>$request->input('nombre')
                ]);
        }

        return response()->json($resp, $resp["code"]);
    }


    public function PolygonConfigDelete(Request $request) {

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('delete')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            AssistanceData::deletePolygonConfig(
                $request->query('id_mapa'));

        }

        return response()->json($resp, $resp["code"]);
    }


    public function SaveAssistance(Request $request) { // DEPRECATED 

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){

            $resp["code"] = '200';

            $dni = $request->input('dni');
            $token_device = $request->input('token');
            $id_mapa = $request->input('id_mapa');
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            $extra = array(
                'id_mapa' => $id_mapa,
                'id_persona' => $resp['id_user'],
                'lat' => $lat,
                'lng' => $lng,
            );


            $person = AssistanceData::ExistPerson($dni);

            if ($person) {

                // valida si debe insertar asistencia; puede 50 minutos despues de su ultimo registro de control de asistencia
                $status = AssistanceData::CanInsertAssist($person);

                if ($status->status == 'true') {


                    // debe de tener el dispositivo registrado para realizar el control de asistencia

                    // Puede que un usuario tenga mas de un dispositivo

                    $deviceExist = DB::table('APS_USER_DEVICE')
                    ->select(
                        'APS_USER_DEVICE.ID_PERSONA',
                        'APS_USER_DEVICE.ID_USERDEVICE')
                    ->where('APS_USER_DEVICE.UUID','=',$token_device)
                    ->get()
                    ->first();


                    if ($deviceExist->id_persona == $resp['id_user']) {
                        
                        $extra['id_userdevice'] = $deviceExist->id_userdevice;

                        $save = AssistanceData::SaveAssistanceUser($person, $extra);

                        if ($save) {

                            $resp['message'] = 'Se registro su asistencia';
                            $resp['success'] = true;

                        } else {
                            $resp['message'] = 'Error en servidor';
                            $resp['success'] = false;
                        }

                    } else {
                        $resp['success'] = false;
                        $resp['message'] = 'No intente usar otro dispositivo movil para realizar su asistencia';

                    }


                    // $device = AssistanceData::getUserDevice($resp['id_user']); 

                    // if ($token_device == $device->uuid) {

                    //     $extra['id_userdevice'] = $device->id_userdevice;

                    //     $save = AssistanceData::SaveAssistanceUser($person, $extra);

                    //     if ($save) {

                    //         $resp['message'] = 'Se registro su asistencia';
                    //         $resp['success'] = true;

                    //     } else {
                    //         $resp['message'] = 'Error en servidor';
                    //         $resp['success'] = false;
                    //     }

                    // } else {

                    //     $resp['success'] = false;
                    //     $resp['message'] = 'No intente usar otro dispositivo movil para realizar su asistencia';
                    // }

                    
                } else {
                    $resp['message'] = 'Ya tiene registrado su asistencia, espere su siguiente horario';
                    $resp['success'] = false;

                }





                // $status = AssistanceData::ExistAssistance($dni);

                // if (count($status) == 1) {

                //     $valido = last($status)->status;
                //     $registros = (int)last($status)->registros;

                //     $user_device = AssistanceData::getUserDevice($resp['id_user']);

                //     $extra = array(
                //         'id_mapa' => $id_mapa,
                //         'id_persona' => $resp['id_user'],
                //         'lat' => $lat,
                //         'lng' => $lng,
                //     );

                //     if (count($user_device) == 1 && $token_device) {

                //         $extra['id_userdevice'] = $user_device->id_userdevice;

                //         if ($token_device == $user_device->uuid && $resp['id_user'] == $user_device->id_persona) {

                //             if ($registros > 0) {

                //                 if ($valido == 'true') {

                //                     $stat = AssistanceData::SaveAssistanceUser($person, $extra);

                //                     if ($stat) {

                //                         $resp['message'] = 'Se registro su asistencia';
                //                         $resp['success'] = true;

                //                     } else {
                //                         $resp['message'] = 'Error en servidor';
                //                         $resp['success'] = false;
                //                     }
                                                            

                //                 } else {
                //                     $resp['message'] = 'Ya tiene registrado su asistencia, espere su siguiente horario';
                //                     $resp['success'] = false;
                //                 }
                                
                //             } else {
                //                 $stat = AssistanceData::SaveAssistanceUser($person, $extra);

                //                 if ($stat) {

                //                     $resp['message'] = 'Se registro su primer control de asistencia del día';
                //                     $resp['success'] = true;

                //                 } else {

                //                     $resp['message'] = 'Error en servidor';
                //                     $resp['success'] = false;
                //                 }
                                
                //             }
                            
                //         } else {
                //             $resp['success'] = false;
                //             $resp['message'] = 'No intente usar otro dispositivo movil para realizar su asistencia';

                //         }



                //     } else {

                //         // no cuenta con dispositivo touch


                //         if ($registros > 0) {

                //             if ($valido == 'true') {

                //                 $stat = AssistanceData::SaveAssistanceUser($person, $extra);

                //                 if ($stat) {
                //                     $resp['message'] = 'Se registro su asistencia de forma manual';
                //                     $resp['success'] = true;
                //                 } else {
                //                     $resp['message'] = 'Error en servidor';
                //                     $resp['success'] = false;
                //                 }
                                                        

                //             } else {
                //                 $resp['message'] = 'Ya tiene registrado su asistencia, espere su siguiente horario';
                //                 $resp['success'] = false;
                //             }
                            
                //         } else {

                //             $stat = AssistanceData::SaveAssistanceUser($person, $extra);

                //             if ($stat) {
                                
                //                 $resp['message'] = 'Se registro su primer control de asistencia del día';
                //                 $resp['success'] = true;

                //             } else {
                                
                //                 $resp['message'] = 'Error en servidor';
                //                 $resp['success'] = false;

                //             }
                            
                //         }

                //     }


                    

                // } else {
                //     $resp['message'] = 'No tiene ninguna asistencia (No se pudo comprobar su usuario con su asistencia)';
                //     $resp['success'] = false;
                // }


            } else {

                $resp['success'] = false;
                $resp['message'] = 'El documento de identidad es invalido';

            }

        }

        return response()->json($resp, $resp["code"]);
    }


    


    public function SaveUserDevice(Request $request) {

        // Metodo para guardar la informacion del dispositivo movil de la persona

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){

            $resp["code"] = '200';

            $resp['success'] = true;
            
            $user_device = AssistanceData::getUserDevice($resp['id_user'], $request->input('uuid'));

            if ($user_device) {
                $resp['data'] = $user_device;
                $resp['message'] = 'Ya existe una informacion del dispositivo';

            } else {
                $resp['message'] = 'Se guardo correctamente la informacion del dispositivo';
                $resp['data'] = AssistanceData::saveUserDevice([
                    'ID_PERSONA'=>$resp['id_user'],
                    'MODEL'=>$request->input('model'),
                    'PLATFORM'=>$request->input('platform'),
                    'UUID'=>$request->input('uuid'),
                    'VERSION'=>$request->input('version'),
                    'MANUFACTURER'=>$request->input('manufacturer'),
                    'ISVIRTUAL'=>$request->input('isvirtual'),
                    'SERIAL'=>$request->input('serial'),
                    'CAN_RESET_TOUCH_ID'=>'0',
                    'STATE'=>'1',
                    'NOT_ASSIT'=>'0',
                    'RE_ASIGN'=>'0'
                ]);

            }

            
        }

        return response()->json($resp, $resp["code"]);
    }




    public function AssistanceByUserTest(Request $request) {

        // Metodo para ver las asistencias de today por usuario (SOLO PARA TEST)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            $dni = $request->input('dni');
            $resp['data'] = AssistanceData::AssistanceByUser($dni);
        }

        return response()->json($resp, $resp["code"]);
    }


    public function ResetAsisTest(Request $request) {
        // Metodo para eliminar una asistencia (SOLO PARA TEST)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            $id = $request->input('id');
            $status = $request->input('eliminar');
            $resp['data'] = AssistanceData::ResetAsis($id, $status);
        }

        return response()->json($resp, $resp["code"]);
    }


    public function UserDevice(Request $request) {

        // Metodo para obtener validaciones de device de usuario

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('get')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            $persona_id = $resp['id_user'];
            $token_device = $this->request->query('token');

            $device = AssistanceData::getUUIDDevicesUser($persona_id, $token_device);

            $person = AssistanceData::getPersonForPolygon($persona_id);

            $resp['data'] = $person;

            $resp['device'] = $device;
        }

        return response()->json($resp, $resp["code"]);
    }


    public function AssistanceUser(Request $request) {

        // Metodo que retorna asistencias por usuario (MOVIL)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('get')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            $fecha = $request->query('fecha');
            if ($fecha) {

                $person = AssistanceData::getPersonForPolygon($resp['id_user']);
                $data = AssistanceData::AssistanceUser($person->num_documento, $fecha);

                $ids = collect($data)->map(function ($obj) {return $obj->id;});

                $assistances = AssistanceData::AssistPolyUser($resp['id_user'], $ids);

                $resp['data'] = collect($data)->map(function ($obj) use($assistances) {
                    $obj->data = collect($assistances)->where('id','=',$obj->id)->first();
                    return $obj;
                });
                
            }
            
        }

        return response()->json($resp, $resp["code"]);
    }

    public function DevicesUser(Request $request) {

        // Metodo que lista dispositivos de usuario (WEB)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('get')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';
            $query = $request->query('query');
            $resp['data'] = AssistanceData::DevicesUserData($query);
            
        }

        return response()->json($resp, $resp["code"]);

    }

    public function DevicesUserUpdateKey(Request $request) {

        // Metodo que lista dispositivos de usuario (WEB)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('put')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Se actualizo el permiso de restablecer llave';
            $id = $request->input('id');
            $can_reset_touch_id = $request->input('reset_touch');
            $data = [
                'can_reset_touch_id' => $can_reset_touch_id
            ];
            $resp['data'] = AssistanceData::DevicesUserUpdate($id, $data);
            
        }

        return response()->json($resp, $resp["code"]);

    }

    public function reportsAssistances(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_depto = $request->query('id_depto');
                $fecha_de = $request->query('fecha_de');
                $fecha_a = $request->query('fecha_a');
                $data = AssistanceData::reportsAssistances($id_entidad,$id_depto,$fecha_de,$fecha_a);
                if(count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' linea:'.$e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    public function UsersActiveBackGeo() {

        // Para test de usuarios a notificar conectando con sockets
        // libreria https://github.com/Textalk/websocket-php

        $resp = GlobalMethods::authorizationLamb($this->request);
        
        if($resp["valida"]=='SI' && $this->request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Usuarios segun area recibiendo ubicacion';
            $id_user = $resp['id_user'];

            $params['id_depto'] = $this->request->input('id_depto');
            $params['radio'] = $this->request->input('radio');
            $params['id_user'] = $id_user;

            if ($params['id_depto']) {
                $users = AssistanceData::UsersBackGeo($params);
                $resp['data'] = $users;
            }



            // $sp = AccountingData::AccountingYearMonthTC('7124','7','N',null);
            // $resp['data'] = $sp;


            // $users = AssistanceData::UsersBackGeo($params);




            // $active_users = $users->filter(function ($value, $key) {
            //     return $value->token != null;
            // });

            // $collection = collect($active_users)->map(function($item, $index) {
            //     $item->type = 'action';
            //     $item->method = 'ActiveBackGeo';
            //     return $item;
            // });

            // $client = new Client("ws://erpsockets-dev.upeu/$id_user/lamb/");
            // $payload['data'] = $collection; 
            // $data['stream'] = "lamb";
            // $data['payload'] = $payload;
            // $data = json_encode($data);
            // $client->send($data);
            // $resp['data'] = json_decode($client->receive());
            // $client->close();

            // $resp['data'] = $users;

        }

        return response()->json($resp, $resp["code"]);
    }


    public function AreasResponsable() {

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('get')){

            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Areas según resposable';
            $params['id_persona'] = $resp['id_user'];
            $params['id_entidad'] = $resp['id_entidad'];

            $data = AssistanceData::AreasResp($params);
            $resp['data'] = $data;

            if ($data->count() == 0) {
                $resp['message'] = 'Usted no es resposable de ningún Área';
            }

        }

        return response()->json($resp, $resp["code"]);

    }


    public function AssistPersonDevice(Request $request){

        // lista de personas por departamento (APP MOVIL) (Asistencia a culto)


        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                //$id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $rpta = AssistanceData::AssistancePersonDevice($id_entidad,$id_depto,$id_user);
                if($rpta["nerror"]==0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $rpta["data"];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["mensaje"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage().' line: '.$e->getLine().'  File:'.$e->getFile();
                $jResponse['data'] = [];
                $code = "400";
            }
        }        
        return response()->json($jResponse,$code);
    }

    public function UpdateNotifAssist(){

        // actualiza estado de enviar notificacion de 
        // asistencia a personas segun depto (MOVIL)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('get')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Personas con dispositivos registrados';
            $id_depto = $this->request->query('id_depto');
            $active = $this->request->query('active');
            $id_persona = $this->request->query('id_persona');
            $id_entidad = $resp["id_entidad"];
            $id_user = $resp["id_user"];

            if (!$id_persona && $this->request->has('active') && $this->request->has('id_depto')) {
                $personas = AssistanceData::AssistancePersonForNotAssit($id_entidad,$id_depto,$id_user);
                $resp['data'] = AssistanceData::updateNotifAssit($personas,['NOT_ASSIT'=>$active]);
            }

            if ($id_persona && $this->request->has('active')) {
                $resp['data'] = AssistanceData::updateNotifAssit([$id_persona],['NOT_ASSIT'=>$active]);                
            }

        }
        return response()->json($resp, $resp["code"]);

    }



    public function ValidateDevice(){

        $resp = GlobalMethods::authorizationLamb($this->request);

        $uuid = $this->request->query('uuid');

        if($resp["valida"]=='SI' && $uuid){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'ValidateDevice';
            $resp['data'] = ADManager::validateDevice($resp['id_user'], $uuid);            
            $resp['person'] = AssistanceData::getPersonForPolygon($resp['id_user']);

        }

        return response()->json($resp, $resp["code"]);

    }




    public function SaveAssistanceDevice(){

        /*

        DEPENDENCIA: ValidateDevice()

        Para realizar un insert de asistencia necesita ValidateDevice() para guardar con que dispositivo realizo su asistencia

        Puede realizar nuevamente su asistencia despues de 45 minutos

        */

        $resp = GlobalMethods::authorizationLamb($this->request);

        $dni = $this->request->input('dni');
        $uuid = $this->request->input('token');
        $id_mapa = $this->request->input('id_mapa');
        $lat = $this->request->input('lat');
        $lng = $this->request->input('lng');
        
        if ($resp["valida"]=='SI' && $dni && $uuid && $id_mapa && $lat && $lng) {

            $resp["code"] = '200';
            $resp['success'] = true;

            $extra = array(
                'id_persona' => $resp['id_user'],
                'id_mapa' => $id_mapa,            
                'lat' => $lat,
                'lng' => $lng,
            );

            $status = ADManager::saveAssist($dni, $extra, $uuid);
            $resp['message'] = $status['message'];
            $resp['success'] = $status['insertAssistance'];            

        } else {
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'No se pudo registrar su asistencia, no cumple con parametros.';
        }

        return response()->json($resp, $resp["code"]);

    }



    public function ResetDeviceUser(){

        $resp = GlobalMethods::authorizationLamb($this->request);
        
        if ($resp["valida"]=='SI') {
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] ='OK';

            $uuid = $this->request->input('uuid');
            if ($uuid) {
                ADManager::resetDevice($resp['id_user'], $uuid);
                $resp['message'] = 'Se asigno una nueva persona al dispositivo';
            }           
            

        }
        return response()->json($resp, $resp["code"]);

    }



    protected static function checkPasswordDefault($access_token) {

        // verifica si un usuario cambio la contraseña por defecto lamb2019

        $data = false;
        $url = 'https://oauth.upeu.edu.pe/api/change_password_default/';        

        $curl_dj = curl_init($url);
        curl_setopt($curl_dj, CURLOPT_POST, true);
        curl_setopt($curl_dj, CURLOPT_POSTFIELDS, http_build_query([]));
        curl_setopt($curl_dj, CURLOPT_HTTPHEADER,
            array('Authorization: Bearer '.$access_token.''));
        curl_setopt($curl_dj, CURLOPT_RETURNTRANSFER, true);
        $result_data = curl_exec($curl_dj);
        $result_data = json_decode($result_data, true);
        
        $resp['code'] = curl_getinfo($curl_dj, CURLINFO_HTTP_CODE);
        if ($resp['code'] == 200) {
            $data = $result_data['change_password_default'];
        }

        curl_close($curl_dj);
        return $data;
    }


    protected static function UpdateOrSetChangePassordDefault($token, $device) {

        if ($token) {

            if ($device) {

                if ($device->change_password_default == null) {

                    $status = self::checkPasswordDefault($token);

                    $dataSet = [
                        'CHANGE_PASSWORD_DEFAULT'=>$status
                    ];

                    DB::table('APS_USER_DEVICE')
                    ->where('APS_USER_DEVICE.UUID','=',$device->uuid)
                    ->where('APS_USER_DEVICE.ID_PERSONA','=',$device->id_persona)
                    ->update($dataSet);

                    return $status;
                } else {
                    return $device->change_password_default == '1'?true: false;
                }

            } else {

                return self::checkPasswordDefault($token);
            }
           
        } else {

            return false;
        }


    }




    public function DeviceUser(){

        $resp = GlobalMethods::authorizationLamb($this->request);

        $uuid = $this->request->query('uuid');
        $check = $this->request->query('check');

        if($resp["valida"]=='SI' && $uuid){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'ValidateDevice';
            $resp['data'] = ADManager::validateDevice($resp['id_user'], $uuid);            
            $resp['person'] = AssistanceData::getPersonForPolygon($resp['id_user']);

            if ($resp['data']['deviceLost']) {

                $resp['device'] =DB::table('APS_USER_DEVICE')
                ->select(
                    'APS_USER_DEVICE.ID_PERSONA',
                    'APS_USER_DEVICE.UUID',
                    'APS_USER_DEVICE.CAN_RESET_TOUCH_ID',
                    'APS_USER_DEVICE.NOT_ASSIT',
                    'APS_USER_DEVICE.RE_ASIGN',
                    'APS_USER_DEVICE.CHANGE_PASSWORD_DEFAULT')
                ->where('APS_USER_DEVICE.ID_PERSONA','=',$resp['id_user'])
                ->where('APS_USER_DEVICE.STATE','=',1)
                ->first();

                $resp['device']->uuid = $uuid;


            } else {

                $resp['device'] = DB::table('APS_USER_DEVICE')
                ->select(
                    'APS_USER_DEVICE.ID_PERSONA',
                    'APS_USER_DEVICE.UUID',
                    'APS_USER_DEVICE.CAN_RESET_TOUCH_ID',
                    'APS_USER_DEVICE.NOT_ASSIT',
                    'APS_USER_DEVICE.RE_ASIGN',
                    'APS_USER_DEVICE.CHANGE_PASSWORD_DEFAULT')
                ->where('APS_USER_DEVICE.UUID','=',$uuid)
                ->where('APS_USER_DEVICE.STATE','=',1)
                ->first();
            }
            

            $resp['area_resp'] = DB::table('APS_TRABAJADOR')
            ->select(
                'ORG_AREA_RESPONSABLE.ID_PERSONA',
                'APS_USER_DEVICE.UUID')
            ->join('ORG_SEDE_AREA', 'APS_TRABAJADOR.ID_DEPTO', '=', 'ORG_SEDE_AREA.ID_DEPTO')
            ->join('ORG_AREA_RESPONSABLE', 'ORG_SEDE_AREA.ID_SEDEAREA', '=', 'ORG_AREA_RESPONSABLE.ID_SEDEAREA')
            ->leftJoin('APS_USER_DEVICE', 'ORG_AREA_RESPONSABLE.ID_PERSONA', '=', 'APS_USER_DEVICE.ID_PERSONA')
            ->where('APS_TRABAJADOR.ID_PERSONA','=',$resp['id_user'])
            ->where('APS_TRABAJADOR.ID_ENTIDAD','=',$resp['id_entidad'])
            ->where('ORG_SEDE_AREA.ID_ENTIDAD','=',$resp['id_entidad'])
            ->get();

            $resp['change_password_default'] = true;//self::UpdateOrSetChangePassordDefault($check, $resp['device']);


        }

        return response()->json($resp, $resp["code"]);

    }





    public function DeviceUserChangeDevice(Request $request) {

        // Metodo para activar o desactivar el permiso de cambiar de dispositivo (WEB)

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('put')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'Se actualizo el permiso de cambio de dispositivo';
            $id = $request->input('id');
            $resp['data'] = AssistanceData::DevicesUserUpdate($id, [
                're_asign' => $request->input('re_asign')
            ]);
            
        }

        return response()->json($resp, $resp["code"]);

    }



    public function DiasPolygon(Request $request) {

        // lista de dias de la semana para restringir poligono por dia

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $request->isMethod('get')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'dias';
            $resp['data'] = DB::table('CONTA_DIA')->get();
            
        }

        return response()->json($resp, $resp["code"]);

    }


    public function SavePolygonEnableDay() {

        // guarda a la tabla APS_MAPA_POLIGONO_ENABLE_DAY

        $canInsert = true;
        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('post')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'dias';

            $data = $this->request->input();
            $data['registrado'] = DB::raw('sysdate');

            DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')
            ->updateOrInsert(
                ['id_mapa_poligono_enable_day'=>$data['id_mapa_poligono_enable_day']],
                $data);

            // DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')->insert($data);
            
        }

        return response()->json($resp, $resp["code"]);

    }

    

    public function GetPolygonEnableDay() {

        // lista la tabla APS_MAPA_POLIGONO_ENABLE_DAY por poligono

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('get')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            $id_mapa = $this->request->query('id_mapa');
            $collection = DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')
            ->join('CONTA_DIA', 'APS_MAPA_POLIGONO_ENABLE_DAY.ID_DIA', '=', 'CONTA_DIA.ID_DIA')
            ->select(
                'APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA_POLIGONO_ENABLE_DAY',
                'CONTA_DIA.NOMBRE',
                'APS_MAPA_POLIGONO_ENABLE_DAY.HORA_INICIO',
                'APS_MAPA_POLIGONO_ENABLE_DAY.HORA_FIN',
                'APS_MAPA_POLIGONO_ENABLE_DAY.RESTRINGE_HORA',
                'APS_MAPA_POLIGONO_ENABLE_DAY.ESTADO',
                'CONTA_DIA.ID_DIA')
            ->where('APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA','=',$id_mapa)
            ->orderBy('CONTA_DIA.ID_DIA')
            ->get();

            $collection = $collection->transform(function ($item, $key) {
                $item->estado = (boolean) $item->estado;
                $item->restringe_hora = (boolean) $item->restringe_hora;
                return $item;
            });

            $resp['data'] = $collection;


            
        }

        return response()->json($resp, $resp["code"]);

    }


    public function DestroyPolygonEnableDay() {

        // elimina un registro la tabla APS_MAPA_POLIGONO_ENABLE_DAY por poligono

        $resp = GlobalMethods::authorizationLamb($this->request);

        if($resp["valida"]=='SI' && $this->request->isMethod('delete')){
            $resp["code"] = '200';
            $resp['success'] = true;
            $resp['message'] = 'OK';

            $id = $this->request->query('id');
            $resp['data'] = DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')
            ->where('APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA_POLIGONO_ENABLE_DAY','=',$id)
            ->delete();
            
        }

        return response()->json($resp, $resp["code"]);

    }







  


    



}

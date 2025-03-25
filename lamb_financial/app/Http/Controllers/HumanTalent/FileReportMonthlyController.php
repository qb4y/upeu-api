<?php
/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/01/2019
 * Time: 9:12 AM
 */

namespace App\Http\Controllers\HumanTalent;


use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\FileReportMonthlyData;
use App\Http\Data\SetupData;
use App\Http\Data\Modulo\ModuloData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use PDF;
use DOMPDF;
use DateTime;
use App\Http\Data\GlobalMethods;

class FileReportMonthlyController extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function base64_encode_image ($url) {
        $urL_default= Null;
        $valid=false;
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            $valid=false;
        }else{
            $valid=true;
        }
        if ($valid !== false){
            $image = file_get_contents($url);
            return 'data:image/jpg;base64,'.base64_encode($image);
        }else{
            $path = public_path() . "/" . $url;
            if (file_exists($path)) {
                $image = File::get($path);
                return 'data:image/jpg;base64,'.base64_encode($image);
            } else {
                if (filter_var($urL_default, FILTER_VALIDATE_URL) === FALSE) {
                   return false;
                }else{
                    $image = file_get_contents($urL_default);
                    return 'data:image/jpg;base64,'.base64_encode($image);
                }
            }
            
        }
    }
    public function formatPDFJSON($info = null, $content, $styles, $pageSize, $pageOrientation = 'portrait', $pageMargins){
        $result = array(
            'info' => $info,
            'content' => $content,
            'styles' => $styles,
            'pageSize' => $pageSize,
            'pageOrientation' => $pageOrientation,
            'pageMargins' => $pageMargins,
        );

        return $result;
    }
    private function uploadFile($archivo, $nombre, $path,$folder=null)
    {
        $response=[];
        try
        {
            $data=[];
            $otherFolder='';
            if(is_object($archivo))
            {
                $file = $archivo;
            }
            else
            {
                $file = Input::file($archivo);
            }
            if($file == null)
            {
                $response['success']=false;
                $response['message']='Archivo no encontrado';
                return $response;
            }
            if($folder){
                $otherFolder=$folder."/";
            }
            $filename=$nombre.".".$file->getClientOriginalExtension();
            if($nombre==null){
                $filename = $file->getClientOriginalName();
            }
            $destinationPath=$path."/".$otherFolder;
            $format = strtoupper($file->getClientOriginalExtension());
            $size = $file->getSize();
            $url = $destinationPath.$filename;
            $file->move($destinationPath, $filename);
            $data['url']=$url;
            $data['filename']=$filename;
            $data['size']=$size;
            $data['format']=$format;
            $response['success']=true;
            $response['message']='El archivo se subio correctamente';
            $response['data']=$data;
            return $response;
        }
        catch(Exception $e)
        {
            $response['success']=false;
            $response['message']=$e->getMessage();
            return $response;
        }
    }
    private function deleteFile($path)
    {
        $response=[];
        try{
            if($path == null){
                $response['success']=false;
                $response['message']='Archivo no encontrado';
                return $response;
            }
            File::delete($path);
            $response['success']=true;
            $response['message']='El archivo se eliminó correctamente';
            return $response;
        }
        catch(Exception $e)
        {
            $response['success']=false;
            $response['message']=$e->getMessage();
            return $response;
        }
    }
    
    public function getTypeEntity(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = FileReportMonthlyData::getTypeEntity();
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
                $code = "200"; 
            }else{
                $jResponse['success'] = false;                    
                $jResponse['message'] = "";
                $jResponse['data'] = [];
                $code = "200";
            }		
	    }
        return response()->json($jResponse,$code);
    }  
    public function getTipoArchivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = FileReportMonthlyData::getTipoArchivo($this->request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
                $code = "200"; 
            }else{
                $jResponse['success'] = false;                    
                $jResponse['message'] = "";
                $jResponse['data'] = [];
                $code = "200";
            }		
	    }
        return response()->json($jResponse,$code);
    }
    public function addTipoArchivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::addTipoArchivo($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $data;
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function getConfigMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_empresa = $this->request->id_empresa;
                $id_entidad = $this->request->id_entidad;
                $array_empr=SetupData::companyByUser($id_user);
                $filter_empr = array_column($array_empr,'id_empresa');
                $empr = implode(",",$filter_empr);
                if(!$empr){
                    $empr='null';
                }
                if($id_empresa!==null and $id_empresa!=='*' and $id_empresa!=='all'){
                    $array_entities=SetupData::listEntitiesEnterpriseByUser($id_empresa, $id_user, 1);
                    $filter_entities = array_column($array_entities,'id');
                }else{
                    $array_entities=ModuloData::listMyEntities($id_user);
                    $filter_entities = array_column($array_entities,'id_entidad');
                }
                $entities = implode(",",$filter_entities);
                if(!$entities){
                    $entities='null';
                }
                if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                    if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                        unset($array_deptos[0]);
                    }
                }else{
                    $array_deptos=FileReportMonthlyData::listMyDepartment($id_user);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                } 
                $data = FileReportMonthlyData::getConfigMonthlyControl($this->request,$empr,$entities,$deptos);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = '';
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function addConfigMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::addConfigMonthlyControl($this->request);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $data;
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function editConfigMonthlyControl($id_archivo_mensual,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::editConfigMonthlyControl($id_archivo_mensual,$this->request);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = '';
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function deleteConfigMonthlyControl($id_archivo_mensual,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::deleteConfigMonthlyControl($id_archivo_mensual);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = '';
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function getFileGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::getFileGroup();
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = '';
                    $jResponse['data'] = [];
                    $code = "200";
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
    
    public function addFileGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::addFileGroup($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $data;
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function getTipoArchivoAnhoMes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::getTipoArchivoAnhoMes($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $data;
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function getMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $this->request->id_entidad;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $empresa=FileReportMonthlyData::getEntityById($id_entidad);
                $data = FileReportMonthlyData::getMonthlyControl($this->request,$empresa->id_empresa);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = '';
                    $jResponse['data'] = [];
                    $code = "200";
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
    public function uploadMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $path = $this->request->url;
                if($path){
                    $deleteFile = $this->deleteFile($path);
                }
                $file = $this->request->file('archivo');
                $id_grupoarchivo = $this->request->id_grupoarchivo;
                $id_tipoarchivo = $this->request->id_tipoarchivo;
                $id_entidad = $this->request->id_entidad;
                $fecha = new DateTime();
                $fileUpload = $this->uploadFile($file,null,'monthly-control',$id_entidad.'-'.$id_grupoarchivo.'-'.$id_tipoarchivo.'-'.$fecha->getTimestamp());
                if($fileUpload['success']==true){
                    $data = FileReportMonthlyData::uploadMonthlyControl($this->request,$fileUpload['data'],$id_user);
                    if($data=="OK"){
                        $response['success'] = true;
                        $response['message'] = "El archivo fue subido correctamente";
                        $response['data'] = [];
                        $code = "200";
                    }else{
                        $response['success'] = false;
                        $response['message'] = $data;
                        $response['data'] = [];
                        $code = "202";
                    }
                }
                else{
                    $jResponse=$fileUpload;
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
	    }
        return response()->json($jResponse);
    }
    public function deleteFileMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $path = $this->request->url;
                $deleteFile = $this->deleteFile($path);
                if($deleteFile['success']==true){
                    $data = FileReportMonthlyData::deleteFileMonthlyControl($this->request,$id_user);
                    if($data=="OK"){
                        $response['success'] = true;
                        $response['message'] = "El archivo fue subido correctamente";
                        $response['data'] = [];
                        $code = "200";
                    }else{
                        $response['success'] = false;
                        $response['message'] = $data;
                        $response['data'] = [];
                        $code = "202";
                    }
                }
                else{
                    $jResponse=$deleteFile;
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
	    }
        return response()->json($jResponse);
    }
    public function getMonthlyControlPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $this->request->id_entidad;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=(object) ['ruc' => '','nombre_legal'=>''];
                $datos['entidad']=(object) ['materno' => ''];
                if($id_entidad and $id_entidad!=='*'){
                    $datos['entidad']=FileReportMonthlyData::getEntityById($id_entidad);
                }
                if($datos['entidad']->id_empresa){
                $datos['empresa']=FileReportMonthlyData::getCompanyById($datos['entidad']->id_empresa);
                }
                $empresa=FileReportMonthlyData::getEntityById($id_entidad);
                $items = FileReportMonthlyData::getMonthlyControl($this->request,$empresa->id_empresa);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateMonthlyControlPdf($data);
                $code = "200"; 
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
	    }
        return response()->json($jResponse,$code);
    }
    public function generateMonthlyControlPdf($data){
        $mes = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $body_table = [];
        $headerTable = array(
            array('text' => 'Mes', 'style' =>  ["tableHeader", "left"]),
            array('text' => 'Tipo documento', 'style' =>  ["tableHeader", "left"]),
            array('text' => 'Documento', 'style' =>  ["tableHeader", "left"]),
            array('text' => "Fecha límite", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Fecha creación", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Fecha modificación", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Usuario", 'style' =>  ["tableHeader", "left"]),
        );

        $body_table[] = $headerTable;
        
        $i = 0;
        $parentNew=null;
        $parentOld=null;
        foreach ($data['items'] as $key => $value){
            $parentNew=$value->id_grupoarchivo;
            if($i===0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->grupoarchivo, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            }
            if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->grupoarchivo, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            }
            $date_limite = new DateTime($value->fecha_limite);
            $date_limite=$date_limite->format('d/m/Y');
            $date_creacion = new DateTime($value->fecha_creacion);
            $date_creacion=$date_creacion->format('d/m/Y');
            $date_modificacion = new DateTime($value->fecha_modificacion);
            $date_modificacion=$date_modificacion->format('d/m/Y');
            if($value->fecha_limite===null) {
                $date_limite='';
            }
            if($value->fecha_creacion===null) {
                $date_creacion='';
            }
            if($value->fecha_modificacion===null) {
                $date_modificacion='';
            }
            $body_table_array=[];
            $body_table_array[]=array('text' => $mes[$value->id_mes], 'style' => ["tableBody","left"]);
            $body_table_array[]=array('text' => $value->tipoarchivo, 'style' => ["tableBody","left"]);
            if($value->file_url){
                $body_table_array[]=array('text' => $value->file_nombre,'link'=>url('/').'/'.$value->file_url, 'style' => ["tableBody","left"]);
            }else{
                $body_table_array[]=array('text' => null, 'style' => ["tableBody","left"]);
            }
            $body_table_array[]=array('text' => $date_limite, 'style' => ["tableBody","center"]);
            $body_table_array[]=array('text' => $date_creacion, 'style' => ["tableBody","center"]);
            $body_table_array[]=array('text' => $date_modificacion, 'style' => ["tableBody","center"]);
            $body_table_array[]=array('text' => $value->user_name, 'style' => ["tableBody","left"]);
            $body_table[] = $body_table_array;
            $i++;
            $parentOld=$value->id_grupoarchivo;
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }
        $info = array(
            'title' => 'CONTROL MENSUAL',
            'author' => $empresaUser->nombre,
        );
        $array_content=[];
        $array_content[]=array('text' => 'CONTROL MENSUAL', 'style' => ["br","title", 'center']);
        $array_content[]=array('text' => 'RUC: '.mb_strtoupper($data['datos']['entidad']->ruc, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'RAZON SOCIAL: '.mb_strtoupper($data['datos']['entidad']->nombre_legal, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'ENTIDAD: '.$data['datos']['entidad']->entidad, 'style' => ["subtitle", 'left']);
        if($data['datos']['entidad']->logo && $data['datos']['entidad']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['entidad']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[]=array('text' => '', 'style' => ["br"]);
        $array_content[]=array(
            'style' => 'tableExample', 'color' => '#444', 
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['10%','19%','24%','10%','10%','10%','17%'],
                'body' => $body_table
            )
        );
        $content[]=$array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 12, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'right','margin' => [0, -65, 0, 0])
        );
        $pageMargins = [30, 30, 30, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }
    public function getMonthlyControlSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FileReportMonthlyData::getMonthlyControlSummary($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200"; 
                }else{
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = '';;
                    $jResponse['data'] = [];
                    $code = "200";
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

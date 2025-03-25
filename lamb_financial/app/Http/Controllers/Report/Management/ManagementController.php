<?php
/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/01/2019
 * Time: 9:12 AM
 */

namespace App\Http\Controllers\Report\Management;


use App\Http\Controllers\Controller;
use App\Http\Data\Report\ManagementData;
use App\Http\Data\SetupData;
use App\Http\Data\Modulo\ModuloData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use PDF;
use DOMPDF;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Excel;
use App\Http\Data\Storage\StorageData;

use App\Http\Resources\MonthlyControlResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ManagementController extends Controller{
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
    public function formatPDFJSONARRAY($content_array, $styles, $pageSize, $pageOrientation = 'portrait', $pageMargins){

        $content_data = [];
        $empty_space = ['text' => '', 'style' => ['br']];

        foreach ($content_array as $key => $content) {
            if ($key > 0) {
                array_unshift($content, $empty_space);
            }
            $content_data = array_merge($content_data, $content);
        }

        $result = array(
            'info' => array('title' => 'CONTROL MENSUAL', 'author' => 'Default'),
            'content' => $content_data,
            'styles' => $styles,
            'pageSize' => $pageSize,
            'pageOrientation' => $pageOrientation,
            'pageMargins' => $pageMargins,
        );

        return $result;
    }


    private function uploadFile($archivo, $nombre, $path, $token)
    {
        $response=[];


        try
        {
            $data=[];
            $file = null;
            // $otherFolder='';
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
/*             if($folder){
                $otherFolder=$folder."/";
            } */
            $filename=$nombre.".".$file->getClientOriginalExtension();
            if($nombre==null){
                $filename = $file->getClientOriginalName();
            }
            // $destinationPath=$path."/".$otherFolder;
            $format = strtoupper($file->getClientOriginalExtension());
            $size = $file->getSize();
            // $url = $destinationPath.$filename;
            $dateYear = date("Y");
            $dateMonth = intval(date("m"));
            $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $mes = $meses[$dateMonth];
            $directory = "$path/$dateYear/$mes";
            $result = StorageData::saveFileShell($archivo, $directory, $token);
            // $file->move($destinationPath, $filename);
            if ($result['success'] == true) {
                $data['url']=$result['data'];
                $data['filename']=$filename;
                $data['size']=$size;
                $data['format']=$format;
                $response['success']=true;
                $response['message']='El archivo se subio correctamente';
                $response['data']=$data;
            } else {
                $response['success']=false;
                $response['message']='El archivo no se subio';
            }

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

    public function getAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getAccounts($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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


    public function getCtactes(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getCtactes($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getCheckingBalance(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getCheckingBalance($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function getCheckingBalanceLegal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getCheckingBalanceLegal($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function getDeparmentBalance(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ManagementData::getDeparmentBalance($this->request);
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
    public function getDeparment(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ManagementData::getDeparment($this->request);
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
            $data = ManagementData::getTipoArchivo($this->request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
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
    public function getTypeEntity(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ManagementData::getTypeEntity();
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
    public function getAccountingEntries(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                /*$id_entidad = $this->request->id_entidad;
                if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all' and $id_entidad!=='0'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                    if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                        unset($array_deptos[0]);
                    }
                }else{
                    $array_deptos=ManagementData::listMyDepartment($id_user);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                } */
                $data = ManagementData::getAccountingEntries($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function uploadAccountingEntry(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $session_token = $jResponse["token"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $path = $this->request->url;
                if($path){
                    $deleteFile = $this->deleteFile($path);
                }
                $file = $this->request->file('archivo');
                $fecha = new DateTime();
                $fileUpload = $this->uploadFile($file,null,'conta-diario-files/accounting-entry', $session_token);
                if($fileUpload['success']==true){
                    $data = ManagementData::uploadAccountingEntry($this->request,$fileUpload['data']);
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
    public function deleteFileAccountingEntry(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $path = $this->request->url;
                $deleteFile = $this->deleteFile($path);
                if($deleteFile['success']==true){
                    $data = ManagementData::deleteFileAccountingEntry($this->request);
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
    public function getSeniorAccountant(Request $request){
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
                /* if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                    if($array_deptos and $array_deptos[0] and $array_deptos[0]->id_depto==='*'){
                        unset($array_deptos[0]);
                    }
                }else{
                    $array_deptos=ManagementData::listMyDepartment($id_user);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                } */
                $data = ManagementData::getSeniorAccountant($this->request,false,$empr,$entities);
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
    public function addTipoArchivo(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::addTipoArchivo($this->request);
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

    public function editTipoArchivo($id_tipoarchivo,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::editTipoArchivo($id_tipoarchivo,$this->request);
                if ($data =='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = [];
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

    public function deleteTipoArchivo($id_tipoarchivo,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::deleteTipoArchivo($id_tipoarchivo);
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

    public function getFinancialStatements(Request $request){
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
                /* if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                    if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                        unset($array_deptos[0]);
                    }
                }else{
                    $array_deptos=ManagementData::listMyDepartment($id_user);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                } */
                $data = ManagementData::getFinancialStatements($this->request,$empr,$entities);
                // $data['empresa'] = []
                $data['empresa']=[ManagementData::getCompanyById($id_empresa)];
                $data['entidad']= [];
                if($id_entidad && $id_entidad!=='*'){
                    $data['entidad']=[ManagementData::getEntityById($id_entidad)];
                }

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
    public function getFinancialStatementsLegal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_empresa = $this->request->id_empresa;
                $id_entidad = $this->request->id_entidad;

                $data = ManagementData::getFinancialStatementsLegal($this->request);
                $data['empresa']=[ManagementData::getCompanyById($id_empresa)];
                $data['entidad']= [];
                if($id_entidad && $id_entidad!=='*'){
                    $data['entidad']=[ManagementData::getEntityById($id_entidad)];
                }
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
    public function addConfigMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::addConfigMonthlyControl($this->request);
                if ($data['error']=='0') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $data['message'];
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
                $id_entidades = $this->request->id_entidades;
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

/*                 if($id_entidades!==null and $id_entidades!=='*' and $id_entidades!=='all'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidades, $id_user, 1);
                }else{
                    $array_deptos=ManagementData::listMyDepartment($id_user);
                } */
                $deptos='null';
                /* $filter_deptos = array_column($array_deptos,'id_depto');
                $search_index = array_search('*', $filter_deptos);
                array_splice($filter_deptos, $search_index, 1);
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                }  */

                $data = ManagementData::getConfigMonthlyControl($this->request,$empr,$entities,$deptos);
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
    public function editConfigMonthlyControl($id_archivo_mensual,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::editConfigMonthlyControl($id_archivo_mensual,$this->request);
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
                $data = ManagementData::deleteConfigMonthlyControl($id_archivo_mensual);
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

    public function addFileGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::addFileGroup($this->request);
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

    public function getFileGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getFileGroup($this->request);
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

    public function editFileGroup($id_grupoarchivo,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::editFileGroup($id_grupoarchivo,$this->request);
                if ($data =='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = [];
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

    public function deleteFileGroup($id_grupoarchivo,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::deleteFileGroup($id_grupoarchivo);
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

    public function getMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $session_token = $jResponse["token"];
        // $id_entidad = $this->request->id_entidad;

        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_grupoarchivo = $request->id_grupoarchivo;

        $entidades = $request->query('entidades', []);
        $data = [];

        if($valida=='SI'){
            $jResponse=[];
            try{
                foreach ($entidades as $id_entidad => $deptos) {
                    $empresa=ManagementData::getEntityById($id_entidad);
                    $item = ManagementData::getMonthlyControl($id_grupoarchivo, $id_anho, $id_mes, $id_entidad, $deptos, $empresa->id_empresa);
                    $data = array_merge($data, $item);
                }

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
        $session_token = $jResponse["token"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $path = $this->request->url;
/*                  if($path){

                    $separada = explode('/', $path);
                    $total = count($separada);
                    if ($total > 0) {
                        $deleteFile = StorageData::deleteFileShell($path, $session_token);
                    }
                    // $deleteFile = $this->deleteFile($path);
                } */


                $file = $this->request->file('archivo');
                $id_grupoarchivo = $this->request->id_grupoarchivo;
                $id_tipoarchivo = $this->request->id_tipoarchivo;
                $id_entidad = $this->request->id_entidad;
                $fecha = new DateTime();


                $filename = $file->getClientOriginalName();
                $format = strtoupper($file->getClientOriginalExtension());
                $size = $file->getSize();
                // $fileUpload = $this->uploadFile($file,null,'archivo-mensuales/monthly-control',$id_entidad.'-'.$id_grupoarchivo.'-'.$id_tipoarchivo.'-'.$fecha->getTimestamp());
                // $fileUpload = $this->uploadFile($file,null,'archivo-mensuales', $session_token);
                $fileData = array(
                    'url' => $path,
                    'filename' => $filename,
                    'size'=>$size,
                    'format'=>$format,
                );


                //if($fileUpload['success']==true){
                    $data = ManagementData::uploadMonthlyControl($this->request,$fileData,$id_user);
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
                /*}
                 else{
                    $jResponse=$fileUpload;
                    $code = "202";
                } */
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
/*                 $deleteFile = $this->deleteFile($path);
                if($deleteFile['success']==true){ */
                    $data = ManagementData::deleteFileMonthlyControl($this->request,$id_user);
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
                /* }
                else{
                    $jResponse=$deleteFile;
                    $code = "202";
                } */
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
	    }
        return response()->json($jResponse);
    }

    public function getMonthlyControlSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getMonthlyControlSummary($this->request);
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
    public function getFinancialAnalysis(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getFinancialAnalysis($this->request);
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
    public function getPerformanceReport(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getPerformanceReport($this->request);
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
    public function getCheckingBalancePdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $this->request->id_entidad;
        $id_empresa = $this->request->id_empresa;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad!=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                }
                $items = ManagementData::getCheckingBalance($this->request);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateCheckingBalancePdf($data);
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
    public function generateCheckingBalancePdf($data){

        $body_table = [];
        $headerTable = array(
            array('text' => 'Código', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Cuenta', 'style' =>  ["tableHeader", "left"]),
            array('text' => "Cta. Cte.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Nombre", 'style' =>  ["tableHeader", "left"]),
            array('text' => "Debe", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Haber", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Saldo", 'style' =>  ["tableHeader", "right"]),
        );

        $body_table[] = $headerTable;
        foreach ($data['items'] as $key => $value){
            if($value->es_grupo==='1' and $value->codigo){
                $body_table[] = array(
                    array('colSpan' => 4, 'text' => $value->codigo.'. '.$value->cuenta, 'style' => ["tableBody","left","bold","subheader"]),"","","",
                    array('text' => number_format($value->debe, 2), 'style' => ["tableBody","right","bold","subheader"],'color'=>$value->debe<0?'red':'black'),
                    array('text' => number_format($value->haber,2), 'style' => ["tableBody","right","bold","subheader"],'color'=>$value->haber<0?'red':'black'),
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right","bold","subheader"],'color'=>$value->saldo<0?'red':'black'),
                );
            }
            if(!$value->codigo){
                $body_table[] = array(
                    array('colSpan' => 4, 'text' => 'TOTALES', 'style' => ["tableBody","right","bold","subheader"]),"","","",
                    array('text' => number_format($value->debe, 2), 'style' => ["tableBody","right","bold","subheader"],'color'=>$value->debe<0?'red':'black'),
                    array('text' => number_format($value->haber,2), 'style' => ["tableBody","right","bold","subheader"],'color'=>$value->haber<0?'red':'black'),
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right","bold","subheader"],'color'=>$value->saldo<0?'red':'black'),
                );
            }
            if($value->es_grupo==='0'){
                $body_table[] = array(
                    array('text' => $value->codigo, 'style' => ["tableBody","center"]),
                    array('text' => $value->cuenta, 'style' => ["tableBody","left"]),
                    array('text' => $value->id_ctacte, 'style' => ["tableBody","center"]),
                    array('text' => $value->nombre, 'style' => ["tableBody","left"]),
                    array('text' => $value->debe==0?'-':number_format($value->debe, 2), 'style' => ["tableBody","right"],'color'=>$value->debe<0?'red':'black'),
                    array('text' => $value->haber==0?'-':number_format($value->haber,2), 'style' => ["tableBody","right"],'color'=>$value->haber<0?'red':'black'),
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"],'color'=>$value->saldo<0?'red':'black'),
                );
            }
        }
        if(count($data['items'])===0){
            $body_table[] = array(
                array('colSpan' => 7, 'text' => 'No hay registros para mostrar', 'style' => ["tableBody","center","bold","subheader"]),"","","","","","",
            );
        }
        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }
        $info = array(
            'title' => 'BALANCE DE COMPROBACIÓN',
            'author' => $empresaUser->nombre,
        );
        $array_content=[];
        $array_content[]=array('text' => 'BALANCE DE COMPROBACIÓN', 'style' => ["br","title", 'center']);
        $array_content[]=array('text' => 'RUC: '.mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'RAZON SOCIAL: '.mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'ENTIDAD: '.mb_strtoupper($data['datos']['entidad']->materno, 'UTF-8'), 'style' => ["subtitle", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[]=array('text' => '', 'style' => ["br"]);
        $array_content[]=array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['6%','25%','10%','20%','13%','13%','13%'],
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
            'logo' => array('alignment' => 'right','margin' => [0, -50, 0, 0])
        );

        $pageMargins = [30, 30, 30, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getCheckingBalanceLegalPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $id_empresa = $this->request->id_empresa;
        $id_anho = $this->request->id_anho;
        $id_mes = $this->request->id_mes;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $ejercicio='Todos los años';
                if($id_mes!==null and $id_mes!=='*' and $id_anho!==null and $id_anho!=='*'){
                    $ejercicio= $meses[$id_mes-1].' del '.$id_anho;
                }
                if($id_anho!==null and $id_anho!=='*' and ($id_mes===null or $id_mes==='*')){
                    $ejercicio= $id_anho;
                }
                $datos['ejercicio']=$ejercicio;
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getCheckingBalanceLegal($this->request);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateCheckingBalanceLegalPdf($data);
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
    public function generateCheckingBalanceLegalPdf($data){
        $body_table = [];
        $headerTable = array(
            array('colSpan' => 2,'text' => 'Cuenta', 'style' =>  ["tableHeader", "center"]),"",
            array('colSpan' => 2,'text' => 'Saldos iniciales', 'style' =>  ["tableHeader", "center"]),"",
            array('colSpan' => 2,'text' => 'Movimientos', 'style' =>  ["tableHeader", "center"]),"",
            array('colSpan' => 2,'text' => "Saldos finales", 'style' =>  ["tableHeader", "center"]),"",
            array('colSpan' => 2,'text' => "Inventario", 'style' =>  ["tableHeader", "center"]),"",
            array('colSpan' => 2,'text' => "Resultado por función", 'style' =>  ["tableHeader", "center"]),"",
        );
        $body_table[] = $headerTable;

        $headerTable = array(
            array('text' => 'Código', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Denominación', 'style' =>  ["tableHeader", "left"]),
            array('text' => 'Deudor', 'style' =>  ["tableHeader", "right"]),
            array('text' => "Acreedor", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Debe", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Haber", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Deudor", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Acreedor", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Activo", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Pasivo y Patrominio", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Pérdidas", 'style' =>  ["tableHeader", "right"]),
            array('text' => "Ganancias", 'style' =>  ["tableHeader", "right"]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        $totalDeudorI=0;
        $totalAcreedorI=0;
        $totalDebe=0;
        $totalHaber=0;
        $totalDeudor=0;
        $totalAcreedor=0;
        $totalActivo=0;
        $totalPasivo=0;
        $totalPerdida=0;
        $totalGanancia=0;
        foreach ($data['items'] as $key => $value){
            $body_table[] = array(
                array('text' => $value->id_cuentaempresarial, 'style' => ["tableBody","center"]),
                array('text' => $value->nombre, 'style' => ["tableBody","left"]),
                array('text' =>$value->deudori==0?'-':number_format($value->deudori, 2), 'style' => ["tableBody","right"],'color'=>$value->deudori<0?'red':'black'),
                array('text' =>$value->acreedori==0?'-':number_format($value->acreedori, 2), 'style' => ["tableBody","right"],'color'=>$value->acreedori<0?'red':'black'),
                array('text' =>$value->debe==0?'-':number_format($value->debe, 2), 'style' => ["tableBody","right"],'color'=>$value->debe<0?'red':'black'),
                array('text' =>$value->haber==0?'-':number_format($value->haber, 2), 'style' => ["tableBody","right"],'color'=>$value->haber<0?'red':'black'),
                array('text' =>$value->deudor==0?'-':number_format($value->deudor, 2), 'style' => ["tableBody","right"],'color'=>$value->deudor<0?'red':'black'),
                array('text' =>$value->acreedor==0?'-':number_format($value->acreedor, 2), 'style' => ["tableBody","right"],'color'=>$value->acreedor<0?'red':'black'),
                array('text' =>$value->activo==0?'-':number_format($value->activo, 2), 'style' => ["tableBody","right"],'color'=>$value->activo<0?'red':'black'),
                array('text' =>$value->pasivo==0?'-':number_format($value->pasivo, 2), 'style' => ["tableBody","right"],'color'=>$value->pasivo<0?'red':'black'),
                array('text' =>$value->perdida==0?'-':number_format($value->perdida, 2), 'style' => ["tableBody","right"],'color'=>$value->perdida<0?'red':'black'),
                array('text' =>$value->ganancia==0?'-':number_format($value->ganancia, 2), 'style' => ["tableBody","right"],'color'=>$value->ganancia<0?'red':'black'),
            );
            $i++;
            $totalDeudorI=$totalDeudorI+$value->deudori;
            $totalAcreedorI=$totalAcreedorI+$value->acreedori;
            $totalDebe=$totalDebe+$value->debe;
            $totalHaber=$totalHaber+$value->haber;
            $totalDeudor=$totalDeudor+$value->deudor;
            $totalAcreedor=$totalAcreedor+$value->acreedor;
            $totalActivo=$totalActivo+$value->activo;
            $totalPasivo=$totalPasivo+$value->pasivo;
            $totalPerdida=$totalPerdida+$value->perdida;
            $totalGanancia=$totalGanancia+$value->ganancia;
        }
        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'Sub totales', 'style' => ["tableBody","right","bold"]),"",
            array('text' =>number_format($totalDeudorI, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalDeudorI<0?'red':'black'),
            array('text' =>number_format($totalAcreedorI, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalAcreedorI<0?'red':'black'),
            array('text' =>number_format($totalDebe, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalDebe<0?'red':'black'),
            array('text' =>number_format($totalHaber, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalHaber<0?'red':'black'),
            array('text' =>number_format($totalDeudor, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalDeudor<0?'red':'black'),
            array('text' =>number_format($totalAcreedor, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalAcreedor<0?'red':'black'),
            array('text' =>number_format($totalActivo, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalActivo<0?'red':'black'),
            array('text' =>number_format($totalPasivo, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalPasivo<0?'red':'black'),
            array('text' =>number_format($totalPerdida, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalPerdida<0?'red':'black'),
            array('text' =>number_format($totalGanancia, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalGanancia<0?'red':'black'),
        );
        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'Resultado', 'style' => ["tableBody","right","bold"]),"",
            array('text' =>'', 'style' => ["tableBody","right","bold"]),
            array('text' =>($totalDeudorI-$totalAcreedorI)==0?'-':number_format($totalDeudorI-$totalAcreedorI, 2), 'style' => ["tableBody","right","bold"],'color'=>($totalDeudorI-$totalAcreedorI)<0?'red':'black'),
            array('text' =>'', 'style' => ["tableBody","right","bold"]),
            array('text' =>($totalDebe-$totalHaber)==0?'-':number_format($totalDebe-$totalHaber, 2), 'style' => ["tableBody","right","bold"],'color'=>($totalDebe-$totalHaber)<0?'red':'black'),
            array('text' =>'', 'style' => ["tableBody","right","bold"]),
            array('text' =>($totalDeudor-$totalAcreedor)==0?'-':number_format($totalDeudor-$totalAcreedor, 2), 'style' => ["tableBody","right","bold"],'color'=>($totalDeudor-$totalAcreedor)<0?'red':'black'),
            array('text' =>'', 'style' => ["tableBody","right","bold"]),
            array('text' =>($totalActivo-$totalPasivo)==0?'-':number_format($totalActivo-$totalPasivo, 2), 'style' => ["tableBody","right","bold"],'color'=>($totalActivo-$totalPasivo)<0?'red':'black'),
            array('text' =>($totalGanancia-$totalPerdida)==0?'-':number_format($totalGanancia-$totalPerdida, 2), 'style' => ["tableBody","right","bold"],'color'=>($totalGanancia-$totalPerdida)<0?'red':'black'),
            array('text' =>'', 'style' => ["tableBody","right","bold"]),
        );
        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'TOTALES', 'style' => ["tableBody","right","bold"]),"",
            array('text' =>number_format($totalDeudorI, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalDeudorI<0?'red':'black'),
            array('text' =>number_format($totalAcreedorI+($totalDeudorI-$totalAcreedorI), 2), 'style' => ["tableBody","right","bold"],'color'=>($totalAcreedorI+($totalDeudorI-$totalAcreedorI))<0?'red':'black'),
            array('text' =>number_format($totalDebe, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalDebe<0?'red':'black'),
            array('text' =>number_format($totalHaber+($totalDebe-$totalHaber), 2), 'style' => ["tableBody","right","bold"],'color'=>($totalHaber+($totalDebe-$totalHaber))<0?'red':'black'),
            array('text' =>number_format($totalDeudor, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalDeudor<0?'red':'black'),
            array('text' =>number_format($totalAcreedor+($totalDeudor-$totalAcreedor), 2), 'style' => ["tableBody","right","bold"],'color'=>($totalAcreedor+($totalDeudor-$totalAcreedor))<0?'red':'black'),
            array('text' =>number_format($totalActivo, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalActivo<0?'red':'black'),
            array('text' =>number_format($totalPasivo+($totalActivo-$totalPasivo), 2), 'style' => ["tableBody","right","bold"],'color'=>($totalPasivo+($totalActivo-$totalPasivo))<0?'red':'black'),
            array('text' =>number_format($totalPerdida+($totalGanancia-$totalPerdida), 2), 'style' => ["tableBody","right","bold"],'color'=>($totalPerdida+($totalGanancia-$totalPerdida))<0?'red':'black'),
            array('text' =>number_format($totalGanancia, 2), 'style' => ["tableBody","right","bold"],'color'=>$totalGanancia<0?'red':'black'),
        );
        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }
        $info = array(
            'title' => 'FORMATO 3.17: LIBRO DE INVENTARIOS Y BALANCES - BALANCE DE COMPROBACIÓN',
            'author' => $empresaUser->nombre,
        );
        $array_content=[];
        $array_content[]=array('text' => 'FORMATO 3.17: LIBRO DE INVENTARIOS Y BALANCES - BALANCE DE COMPROBACIÓN', 'style' => ["br","title", 'center']);
        $array_content[]=array('text' => 'EJERCICIO O PERIODO: '.mb_strtoupper($data['datos']['ejercicio'], 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'RUC: '.mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'RAZON SOCIAL: '.mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["subtitle", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[]=array('text' => '', 'style' => ["br"]);
        $array_content[]=array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 2,
                'widths'=> ['5%','15%','8%','8%','8%','8%','8%','8%','8%','8%','8%','8%'],
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

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }
    public function getAccountingEntriesPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $this->request->id_entidad;
        if($valida=='SI'){
            $jResponse=[];
            try{
                /* $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                    unset($array_deptos[0]);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos); */
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['entidad']=ManagementData::getEntityById($id_entidad);
                $items = ManagementData::getAccountingEntries($this->request);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateAccountingEntriesPdf($data);
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
    public function generateAccountingEntriesPdf($data){

        $body_table = [];
        $headerTable = array(
            array('text' => 'Fecha', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Lote', 'style' =>  ["tableHeader", "left"]),
            array('text' => "PDF", 'style' =>  ["tableHeader", "left"]),
            array('text' => "Contabilizado por", 'style' =>  ["tableHeader", "left"]),
            array('text' => "Fecha Contabilización", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Glosa", 'style' =>  ["tableHeader", "left"]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        $parentNew=null;
        $parentOld=null;
        foreach ($data['items'] as $key => $value){
            $parentNew=$value->id_tipoasiento;
            if($i===0){
                $body_table[] = array(
                    array('colSpan' => 6, 'text' => $value->tipoasiento, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }
            if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 6, 'text' => $value->tipoasiento, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }

            $body_table[] = array(
                array('text' => $value->fec_asiento, 'style' => ["tableBody","center"]),
                array('text' => $value->lote, 'style' => ["tableBody","left"]),
                array('text' => $value->file,'link'=>url('/').'/'.$value->file_url, 'style' => ["tableBody","left"]),
                array('text' => $value->nom_contador, 'style' => ["tableBody","left"]),
                array('text' => $value->fec_contabilizado, 'style' => ["tableBody","center"]),
                array('text' => $value->glosa, 'style' => ["tableBody","left"]),
            );
            $i++;
            $parentOld=$value->id_tipoasiento;
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }
        $info = array(
            'title' => 'ASIENTOS CONTABLES',
            'author' => $empresaUser->nombre,
        );
        $array_content=[];
        $array_content[]=array('text' => 'ASIENTOS CONTABLES', 'style' => ["br","title", 'center']);
        $array_content[]=array('text' => 'RUC: '.mb_strtoupper($data['datos']['entidad']->ruc, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'RAZON SOCIAL: '.mb_strtoupper($data['datos']['entidad']->nombre_legal, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'ENTIDAD: '.mb_strtoupper($data['datos']['entidad']->entidad, 'UTF-8'), 'style' => ["subtitle", 'left']);
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
                'widths'=> ['10%','10%','13%','25%','10%','30%'],
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

    public function getSeniorAccountantPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $this->request->id_entidad;
        $id_empresa = $this->request->id_empresa;
        $id_anho = $this->request->id_anho;
        $id_mes = $this->request->id_mes;
        $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        if($valida=='SI'){
            $jResponse=[];
            try{
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
                /* if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                    if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                        unset($array_deptos[0]);
                    }
                }else{
                    $array_deptos=ManagementData::listMyDepartment($id_user);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                } */
                $ejercicio='Todos los años';
                if($id_mes!==null and $id_mes!=='*' and $id_anho!==null and $id_anho!=='*'){
                    $ejercicio= $meses[$id_mes].' del '.$id_anho;
                }
                if($id_anho!==null and $id_anho!=='*' and ($id_mes===null or $id_mes==='*')){
                    $ejercicio= $id_anho;
                }
                $datos['ejercicio']=$ejercicio;
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad!=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                }
                $items = ManagementData::getSeniorAccountant($this->request,true,$empr,$entities);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateSeniorAccountantPdf($data,$id_mes);
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
    public function generateSeniorAccountantPdf($data,$id_mes){
        $body_table = [];
        $headerTable = array(
            array('colSpan' => 4,'text' => 'Fecha', 'style' =>  ["tableHeader", "center"]),"","","",
            array('text' => 'Lote', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Depto.', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Fondo', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Glosa', 'style' =>  ["tableHeader", "left"]),
            array('text' => 'Debe', 'style' =>  ["tableHeader", "right"]),
            array('text' => 'Haber', 'style' =>  ["tableHeader", "right"]),
            array('text' => 'Saldo', 'style' =>  ["tableHeader", "right"]),
        );
        $body_table[] = $headerTable;
        $i = 0;
        $parentNew=(object) ['id_mes' => null,'id_cuentaaasi' => null,'id_persona' => null];
        $parentOld=(object) ['id_mes' => null,'id_cuentaaasi' => null,'id_persona' => null];
        $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        foreach ($data['items']['data'] as $key => $value){
            $body_table[] = array(
                array('colSpan' => 11, 'text' => $value->data->nombre, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","","","","","",
            );
            foreach ($value->children as $key => $value1){
                $body_table[] = array(
                    array('text' => '', 'style' =>  ["tableBody","left","bold","subheader"]),
                    array('colSpan' => 10, 'text' => $value1->data->nombre, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","","","","",
                );
                foreach ($value1->children as $key => $value2){
                    $saldo_anterior=null;
                    if($value2->data->saldo_ini!==0 and $value2->data->saldo_ini!==null and $value2->data->saldo_ini!=='-'){
                        $saldo_anterior="Saldo anterior:: ".number_format($value2->data->saldo_ini,2);
                    }
                    $body_table[] = array(
                        array('text' => '', 'style' =>  ["tableBody","left","bold","subheader"]),
                        array('text' => '', 'style' =>  ["tableBody","left","bold","subheader"]),
                        array('colSpan' => 6, 'text' => $value2->data->nombre, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                        array('colSpan' => 3,'text' => $saldo_anterior, 'style' =>  ["tableBody","right","bold","subheader"],'color'=>$value2->data->saldo_ini<0?'red':'black'),"","",
                    );
                    foreach ($value2->children as $key => $value3){
                        $fecha = new DateTime($value3->data->fec_asiento);
                        $body_table[] = array(
                            array('text' => '', 'style' =>  ["tableBody","center"]),
                            array('text' => '', 'style' =>  ["tableBody","center"]),
                            array('text' => '', 'style' =>  ["tableBody","center"]),
                            array('text' => $fecha->format('d/m'), 'style' => ["tableBody","center"]),
                            array('text' => $value3->data->lote, 'style' => ["tableBody","center"]),
                            array('text' => $value3->data->id_depto, 'style' => ["tableBody","center"]),
                            array('text' => $value3->data->id_fondo, 'style' => ["tableBody","center"]),
                            array('text' => $value3->data->comentario, 'style' => ["tableBody","left"]),
                            array('text' =>$value3->data->debe==0?'-':number_format($value3->data->debe, 2), 'style' => ["tableBody","right"],'color'=>$value3->data->saldo<0?'red':'black'),
                            array('text' =>$value3->data->haber==0?'-':number_format($value3->data->haber, 2), 'style' => ["tableBody","right"],'color'=>$value3->data->haber<0?'red':'black'),
                            array('text' =>number_format($value3->data->saldo_calculado, 2), 'style' => ["tableBody","right"],'color'=>$value3->data->saldo_calculado<0?'red':'black'),
                        );
                    }
                }
            }
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }
        $info = array(
            'title' => 'MAYOR CONTABLE',
            'author' => $empresaUser->nombre,
        );
        $array_content=[];
        $array_content[]=array('text' => 'MAYOR CONTABLE', 'style' => ["br","title", 'center']);
        $array_content[]=array('text' => 'EJERCICIO O PERIODO: '.mb_strtoupper($data['datos']['ejercicio'], 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'RUC: '.mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'DENOMINACIÓN O RAZON SOCIAL: '.mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["subtitle", 'left']);
        $array_content[]=array('text' => 'ENTIDAD: '.mb_strtoupper($data['datos']['entidad']->materno, 'UTF-8'), 'style' => ["subtitle", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }

        }
        $array_content[]=array('text' => '', 'style' => ["br"]);
        $array_content[]=array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['1%','1%','1%','5%','8%','8%','2%','31%','12%','12%','18%'],
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

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }
    public function getFinancialStatementsPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $this->request->id_entidad;
        $id_empresa = $this->request->id_empresa;
        $id_mesfirst = $this->request->id_mesfirst;
        $id_anhofirst = $this->request->id_anhofirst;
        $id_messecond = $this->request->id_messecond;
        $id_anhosecond = $this->request->id_anhosecond;
        if($valida=='SI'){
            $jResponse=[];
            try{
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
                /* if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all'){
                    $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                    if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                        unset($array_deptos[0]);
                    }
                }else{
                    $array_deptos=ManagementData::listMyDepartment($id_user);
                }
                $filter_deptos = array_column($array_deptos,'id_depto');
                $deptos = implode(",",$filter_deptos);
                if(!$deptos){
                    $deptos='null';
                } */
                $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $datos['titulomes']='A '.$meses[$id_mesfirst].' de '.$id_anhofirst. ' y '.$id_anhosecond;
                $datos['titulomes1']=$meses[$id_mesfirst].' '.$id_anhofirst;
                $datos['titulomes2']=$meses[$id_messecond].' '.$id_anhosecond;
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $datos['entidad']=(object) ['materno' => ''];
                if($id_entidad && $id_entidad!=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                }
                $items = ManagementData::getFinancialStatements($this->request,$empr,$entities);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items;
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateFinancialStatementsPdf($data);
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
    public function generateFinancialStatementsPdf($data) {
        $body_table = [];
        $body_table1 = [];
        $body_table2 = [];
        $body_table1[] = array(
            array('text' => '', 'style' =>  ["tableBody2", "center","bold"],'border'=> [false, false, false, true]),
            array('text' => $data['datos']['titulomes1'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
            array('text' => $data['datos']['titulomes2'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
        );
        $body_table2[] = array(
            array('text' => '', 'style' =>  ["tableBody2", "center","bold"],'border'=> [false, false, false, true]),
            array('text' => $data['datos']['titulomes1'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
            array('text' => $data['datos']['titulomes2'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
        );
        foreach ($data['items']['financial_situation'] as $key => $value){
            if($value->level1==='1'){
                if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1'){
                    $body_table1[] = array(
                        array('text' => $value->cuenta, 'style' =>  ["tableBody1", "left"],'border'=> [false, true, false, true]),
                        array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, true, false, true]),
                        array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, true, false, true]),
                    );
                }
                if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='1'){
                    $body_table1[] = array(
                        array('text' => $value->cuenta, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    );
                }
                if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='0'){
                    $body_table1[] = array(
                        array('text' => $value->cuenta, 'style' =>  ["tableBody", "left"],'border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    );
                }
                if($value->es_grupo1==='1' and $value->es_grupo2==='1' and $value->es_grupo3==='1'){
                    $body_table1[] = array(
                        array('text' => 'TOTAL ACTIVO', 'style' =>  ["tableBody0", "left"],'border'=> [false, true, false, true]),
                        array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, true, false, true]),
                        array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, true, false, true]),
                    );
                }
            }
        }
        foreach ($data['items']['financial_situation'] as $key => $value){
            if($value->level1==='2'){
                if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1'){
                    $body_table2[] = array(
                        array('text' => $value->cuenta, 'style' =>  ["tableBody1", "left"],'border'=> [false, true, false, true]),
                        array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, true, false, true]),
                        array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, true, false, true]),
                    );
                }
                if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='1'){
                    $body_table2[] = array(
                        array('text' => $value->cuenta, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    );
                }
                if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='0'){
                    $body_table2[] = array(
                        array('text' => $value->cuenta, 'style' =>  ["tableBody", "left"],'border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                        array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    );
                }
                if($value->es_grupo1==='1' and $value->es_grupo2==='1' and $value->es_grupo3==='1'){
                    $body_table2[] = array(
                        array('text' => 'TOTAL PASIVO Y PATRIMONIO', 'style' =>  ["tableBody0", "left"],'border'=> [false, true, false, true], 'border-color'=>'#023246'),
                        array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, true, false, true]),
                        array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, true, false, true]),
                    );
                }
            }
        }
        $body_table[] = array(
            array(
                'style' => 'tableExample',
                'color' => '#444',
                'table' => array(
                    'headerRows' => 0,
                    'widths'=> ['48%','26%','26%'],
                    'body' => $body_table1
                ),
             'border'=>[false, false, false, false]
            ),
            array(
                'style' => 'tableExample',
                'color' => '#444',
                'table' => array(
                    'headerRows' => 0,
                    'widths'=> ['48%','26%','26%'],
                    'body' => $body_table2,
                ),
             'border'=>[false, false, false, false]
            )
        );
        $financial_situation=$body_table;

        $body_table = [];
        // head initial
        $body_table[] = array(
            array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
            array('text' => '', 'style' =>  ["tableBody2", "center","bold"],'border'=> [false, false, false, true]),
            array('text' => $data['datos']['titulomes1'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
            array('text' => $data['datos']['titulomes2'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
            array('text' => 'Variación', 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
            array('text' => '', 'style' =>  ["tableBody0", "center"],'border'=> [false, false, false, false]),
        );
        foreach ($data['items']['results'] as $key => $value){
            if(!$value->level1 and !$value->level2 and !$value->level3){
                $body_table[] = array(
                    array('text' => '', 'style' =>  ["tableBody1", "center"],'border'=> [false, false, false, false]),
                    array('text' => $value->cuenta, 'style' =>  ["tableBody1", "left"],'border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody1", "center"],'border'=> [false, false, false, false]),
                );
            }
            if($value->es_grupo1==='0' and $value->es_grupo2==='1'){
                $body_table[] = array(
                    array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                    array('text' => $value->cuenta, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => $value->variacion.'%', 'style' =>  ["tableBody2", "right"],'color'=>$value->variacion<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                );
            }
            // data subtitle header
            if($value->es_grupo1==='1' and $value->es_grupo2==='1'){
                $body_table[] = array(
                    array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                    array('text' => $value->cuenta, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => $value->variacion.'%', 'style' =>  ["tableBody2", "right"],'color'=>$value->variacion<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                );
            }
            // data body
            if($value->es_grupo1==='0' and $value->es_grupo2==='0'){
                $body_table[] = array(
                    array('text' => '', 'style' =>  ["tableBody", "center"],'border'=> [false, false, false, false]),
                    array('text' => $value->cuenta, 'style' =>  ["tableBody", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo1,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo1<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo2,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo2<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => $value->variacion.'%', 'style' =>  ["tableBody", "right"],'color'=>$value->variacion<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody", "center"],'border'=> [false, false, false, false]),
                );
            }
        }
        $results=$body_table;

        $array_content1=[];
        $array_content1[]=array('text' => mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["title", 'center']);
        $array_content1[]=array('text' => 'ESTADO DE SITUACIÓN FINANCIERA', 'style' => ["br","subtitle", 'center']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content1[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content1[]=array('text' => $data['datos']['titulomes'], 'style' => ["subtitles", 'center']);
        $array_content1[]=array('text' => "(Expresado en soles)", 'style' => ["subtitles", 'center']);
        $array_content1[]=array('text' => '', 'style' => ["br"]);
        $array_content1[]=array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 0,
                'widths'=> ['50%','50%'],
                'body' => $financial_situation
            ),
         'border'=>[false, false, false, false]
        );
        $array_content2=[];
        $array_content2[]=array('text' => mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["title", 'center']);
        $array_content2[]=array('text' => 'ESTADO DE RESULTADOS', 'style' => ["br","subtitle", 'center']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content2[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content2[]=array('text' => $data['datos']['titulomes'], 'style' => ["subtitles", 'center']);
        $array_content2[]=array('text' => "(Expresado en soles)", 'style' => ["subtitles", 'center']);
        $array_content2[]=array('text' => '', 'style' => ["br"]);
        $array_content2[]=array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 0,
                'widths'=> ['8%','36%','17%','17%','14%','8%'],
                'body' => $results
            ),
         'border'=>[false, false, false, false]
        );
        $content1=[];
        $content1[]=$array_content1;
        $content1[]=array('text' => '', 'pageBreak' => 'before');
        $content1[]=$array_content2;
        $content[]=$content1;

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }
        $info = array(
            'title' => 'ESTADOS FINANCIEROS',
            'author' => $empresaUser->nombre,
        );
        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 12, 'bold' => true, 'color' => '#023246'),
            'subtitle' => array('fontSize' => 10, 'bold' => true, 'color' => '#023246'),
            'subtitles' => array('fontSize' => 10, 'bold' => true, 'color' => '#023246'),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody0' => array('fontSize' => 8, 'bold' => 'true','color' => '#023246', 'border-color'=>'#023246'),
            'tableBody1' => array('fontSize' => 8, 'bold' => 'true','color' => '#023246'),
            'tableBody2' => array('fontSize' => 8, 'bold' => 'true','color' => '#023246'),
            'tableBody' => array('fontSize' => 7, 'color' => '#023246'),
            'tableBody3' => array('fontSize' => 7, 'bold' => 'true','color' => '#023246'),
            'tableBody4' => array('fontSize' => 6,'color' => '#023246'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'left','margin' => [0, -67, 0, 0])
        );

        $pageMargins = [30, 30, 30, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
 }


 public function getFinancialStatementsLegalPdf(Request $request){
    $jResponse = GlobalMethods::authorizationLamb($this->request);
    $code   = $jResponse["code"];
    $valida = $jResponse["valida"];
    $id_user = $jResponse["id_user"];
    $id_entidad = $this->request->id_entidad;
    $id_empresa = $this->request->id_empresa;
    $id_mesfirst = $this->request->id_mesfirst;
    $id_anhofirst = $this->request->id_anhofirst;
    $id_messecond = $this->request->id_messecond;
    $id_anhosecond = $this->request->id_anhosecond;
    if($valida=='SI'){
        $jResponse=[];
        try{
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
            /* if($id_entidad!==null and $id_entidad!=='*' and $id_entidad!=='all'){
                $array_deptos=SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, 1);
                if($array_deptos and $array_deptos[0] and $array_deptos[0]==='*'){
                    unset($array_deptos[0]);
                }
            }else{
                $array_deptos=ManagementData::listMyDepartment($id_user);
            }
            $filter_deptos = array_column($array_deptos,'id_depto');
            $deptos = implode(",",$filter_deptos);
            if(!$deptos){
                $deptos='null';
            } */
            $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $datos['titulomes']='a '.$meses[$id_mesfirst].' de '.$id_anhofirst;
            $datos['titulomes1']=$meses[$id_mesfirst].' '.$id_anhofirst;
            $datos['titulomes2']=$meses[$id_messecond].' '.$id_anhosecond;
            $datos['empresa_user']=SetupData::companyByUser($id_user);
            $datos['empresa']=ManagementData::getCompanyById($id_empresa);
            $datos['entidad']=(object) ['materno' => ''];
            if($id_entidad && $id_entidad!=='*'){
                $datos['entidad']=ManagementData::getEntityById($id_entidad);
            }
            $items = ManagementData::getFinancialStatementsLegal($this->request);
            $data['datos'] = $datos;
            $data['items'] = [];
            if ($items) {
                $data['items'] = $items;
            }
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generateFinancialStatementsLegalPdf($data);
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
public function generateFinancialStatementsLegalPdf($data) {
    $body_table = [];
    $body_table1 = [];
    $body_table2 = [];

    $body_table1[] = array(
        array('text' => '', 'style' =>  ["tableBody2", "center","bold"],'border'=> [false, false, false, true]),
        array('text' => $data['datos']['titulomes1'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
    );
    $body_table2[] = array(
        array('text' => '', 'style' =>  ["tableBody2", "center","bold"],'border'=> [false, false, false, true]),
        array('text' => $data['datos']['titulomes1'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
    );
    foreach ($data['items']['financial_situation'] as $key => $value){
        if($value->level1==='1'){
            if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1' and $value->es_grupo4==='1'){
                $body_table1[] = array(
                    array('text' => $value->name, 'style' =>  ["tableBody1", "left"],'border'=> [false, false, false, true]),
                    array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, false, false, true]),
                );
            }
            if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='0' and $value->es_grupo4==='1'){
                $body_table1[] = array(
                    array('text' => $value->name, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                );
            }
            if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='0' and $value->es_grupo4==='0'){
                $body_table1[] = array(
                    array('text' => $value->name, 'style' =>  ["tableBody", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                );
            }
/*             if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1' and $value->es_grupo4==='1'){
                $body_table1[] = array(
                    array('text' => 'TOTAL ACTIVO', 'style' =>  ["tableBody0", "left"],'border'=> [false, false, false, true]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo<0?'red':'black','border'=> [false, false, false, true]),
                );
            } */
        }
    }

    foreach ($data['items']['financial_situation'] as $key => $value){
        if($value->level1==='1'){
            if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1' and $value->es_grupo4==='1'){
                $body_table1[] = array(
                    array('text' => 'TOTAL ACTIVO', 'style' =>  ["tableBody0", "left"],'border'=> [false, true, false, true]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, true, false, true]),
                );
            }
        }
    }

    foreach ($data['items']['financial_situation'] as $key => $value){
        if($value->level1==='2'){
            if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1' and $value->es_grupo4==='1'){
                $body_table2[] = array(
                    array('text' => $value->name, 'style' =>  ["tableBody1", "left"],'border'=> [false, false, false, true]),
                    array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, false, false, true]),
                );
            }
            if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='0' and $value->es_grupo4==='1'){
                $body_table2[] = array(
                    array('text' => $value->name, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                );
            }
            if($value->es_grupo1==='0' and $value->es_grupo2==='0' and $value->es_grupo3==='0' and $value->es_grupo4==='0'){
                $body_table2[] = array(
                    array('text' => $value->name, 'style' =>  ["tableBody", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                );
            }
/*             if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1' and $value->es_grupo3==='1'){
                $body_table2[] = array(
                    array('text' => 'TOTAL PASIVO Y PATRIMONIO', 'style' =>  ["tableBody0", "left"],'border'=> [false, false, false, true]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo<0?'red':'black','border'=> [false, false, false, true]),
                );
            } */
        }
    }
    foreach ($data['items']['financial_situation'] as $key => $value){
        if($value->level1==='2'){
            if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1' and $value->es_grupo3==='1'){
                $body_table2[] = array(
                    array('text' => 'TOTAL PASIVO Y PATRIMONIO', 'style' =>  ["tableBody0", "left"],'border'=> [false, true, false, true]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody0", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, true, false, true]),
                );
            }
        }
    }
    $body_table[] = array(
        array(
            'style' => 'tableExample',
            'color' => '#444',
            'table' => array(
                'headerRows' => 0,
                'widths'=> ['60%','40%'],
                'body' => $body_table1
            ),
         'border'=>[false, false, false, false]
        ),
        array(
            'style' => 'tableExample',
            'color' => '#444',
            'table' => array(
                'headerRows' => 0,
                'widths'=> ['60%','40%'],
                'body' => $body_table2,
            ),
         'border'=>[false, false, false, false]
        )
    );
    $financial_situation=$body_table;

    $body_table = [];
    $body_table[] = array(
        array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
        array('text' => '', 'style' =>  ["tableBody2", "center","bold"],'border'=> [false, false, false, true]),
        array('text' => $data['datos']['titulomes1'], 'style' =>  ["tableBody2", "right","bold"],'border'=> [false, false, false, true]),
        array('text' => '', 'style' =>  ["tableBody0", "center"],'border'=> [false, false, false, false]),
    );
    foreach ($data['items']['results'] as $key => $value){
/*         if(!$value->level1 and !$value->level2 and !$value->level3){
            $body_table[] = array(
                array('text' => '', 'style' =>  ["tableBody1", "center"],'border'=> [false, false, false, false]),
                array('text' => $value->nombre, 'style' =>  ["tableBody1", "left"],'border'=> [false, false, false, true]),
                array('text' => '', 'style' =>  ["tableBody1", "right"],'border'=> [false, false, true, true]),
                array('text' => '', 'style' =>  ["tableBody1", "center"],'border'=> [false, false, false, false]),
            );
        } */
        if($value->es_grupo2==='0' and $value->es_grupo3==='1'){
            $body_table[] = array(
                array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                array('text' => $value->nombre, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
            );
        }

        if($value->es_grupo2==='0' and $value->es_grupo3==='0'){
            $body_table[] = array(
                array('text' => '', 'style' =>  ["tableBody", "center"],'border'=> [false, false, false, false]),
                array('text' => $value->nombre, 'style' =>  ["tableBody", "left"],'border'=> [false, false, false, false]),
                array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                array('text' => '', 'style' =>  ["tableBody", "center"],'border'=> [false, false, false, false]),
            );
        }
    }

    foreach ($data['items']['results'] as $key => $value){
        if ($value->level1 === '1') {
            if($value->es_grupo1==='0' and $value->es_grupo2==='1' and $value->es_grupo3==='1'){
                $body_table[] = array(
                    array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                    array('text' => $value->nombre, 'style' =>  ["tableBody2", "left"],'border'=> [false, false, false, false]),
                    array('text' => number_format($value->saldo,2), 'style' =>  ["tableBody2", "right"],'color'=>$value->saldo<0?'red':'#023246','border'=> [false, false, false, false]),
                    array('text' => '', 'style' =>  ["tableBody2", "center"],'border'=> [false, false, false, false]),
                );
            }
        }
    }
    $results=$body_table;

    $array_content1=[];
    $array_content1[]=array('text' => mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["title", 'center']);
    $array_content1[]=array('text' => 'ESTADO DE SITUACIÓN FINANCIERA', 'style' => ["subtitle", 'center']);
    $array_content1[]=array('text' => $data['datos']['titulomes'], 'style' => ["subtitles", 'center']);
    $array_content1[]=array('text' => '(Expresado en soles)', 'style' => ["subtitles", 'center']);
    if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
        $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
        if($logo){
            $array_content1[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
        }
    }

    $array_content1[]=array('text' => '', 'style' => ["br"]);
    $array_content1[]=array(
        'style' => 'tableExample', 'color' => '#444',
        'table' => array(
            'headerRows' => 0,
            'widths'=> ['50%','50%'],
            'body' => $financial_situation
        ),
     'border'=>[false, false, false, false]
    );
    $array_content2=[];
    $array_content2[]=array('text' => mb_strtoupper($data['datos']['empresa']->nombre_legal, 'UTF-8'), 'style' => ["title", 'center']);
    $array_content2[]=array('text' => 'ESTADO DE RESULTADOS', 'style' => ["subtitle", 'center']);
    $array_content2[]=array('text' => $data['datos']['titulomes'], 'style' => ["subtitles", 'center']);
    $array_content2[]=array('text' => '(Expresado en soles)', 'style' => ["subtitles", 'center']);
    if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
        $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
        if($logo){
            $array_content2[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
        }
    }

    $array_content2[]=array('text' => '', 'style' => ["br"]);
    $array_content2[]=array(
        'style' => 'tableExample', 'color' => '#444',
        'table' => array(
            'headerRows' => 0,
            'widths'=> ['13%','42%','32%','13%'],
            'body' => $results
        ),
     'border'=>[false, false, false, false]
    );
    $content1=[];
    $content1[]=$array_content1;
    $content1[]=array('text' => '', 'pageBreak' => 'before');
    $content1[]=$array_content2;
    $content[]=$content1;

    $empresaUser=null;
    foreach ($data['datos']['empresa_user'] as $key => $value){
        $empresaUser=$value;
    }
    $info = array(
        'title' => 'ESTADOS FINANCIEROS',
        'author' => $empresaUser->nombre,
    );
    $styles = array(
        'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
        'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
        'title' => array('fontSize' => 12, 'bold' => true,  'color' => '#023246'),
        'subtitle' => array('fontSize' => 10, 'bold' => true,  'color' => '#023246'),
        'subtitles' => array('fontSize' => 10, 'bold' => true,  'color' => '#023246'),
        'tableExample' => array('margin' => [0, 0, 0, 0]),
        'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
        'tableBody0' => array('fontSize' => 10, 'bold' => 'true','color' => '#023246'),
        'tableBody1' => array('fontSize' => 9, 'bold' => 'true','color' => '#023246'),
        'tableBody2' => array('fontSize' => 8, 'bold' => 'true','color' => '#023246'),
        'tableBody' => array('fontSize' => 7, 'color' => '#023246'),
        'tableBody3' => array('fontSize' => 7, 'bold' => 'true','color' => '#023246'),
        'tableBody4' => array('fontSize' => 6,'color' => '#023246'),
        'center' => array('alignment' => 'center'),
        'right' => array('alignment' => 'right'),
        'bold' => array('bold' => true),
        'subheader' => array('fillColor' => '#CED4DA'),
        'br' => array('margin' => [0, 5, 0, 10]),
        'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
        'logo' => array('alignment' => 'left','margin' => [0, -67, 0, 0])
    );

    $pageMargins = [30, 30, 30, 30];

    return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
}

    public function sumaTotalArray($data, $key, $information, $column){
        $total = 0;
        foreach ($data as $keys => $value){
            $val=(array)$value;
            if($val[$key] === $information){
                $total = $total + $val[$column];
            }
        }
        return $total;
    }

    public function getAccountStatusLote(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data_log = $jResponse;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getAccountStatusLote($this->request, $data_log);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getAccountStatus(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data_log = $jResponse;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getAccountStatus($this->request, $data_log);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getAccountStatusPDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data_log = $jResponse;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_entidad_cte = $this->request->query('id_entidad_cte');
                // $lote = $this->request->query('lote');
                // $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['id_entidad_cte'] = $id_entidad_cte;

                if ($id_entidad != null and $id_entidad != ' '){
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
                    }
                }

                if ($id_entidad_cte != null and $id_entidad_cte != ' ' and $id_entidad_cte !== '*'){
                    $da_entidad = SetupData::entityDetailArray($id_entidad_cte);
                    foreach ($da_entidad as $item) {
                        $datos['CtaCte'] = '';
                    }
                } 

                $datos['periodo'] = "Todos los meses del ".$id_anho;
                if ($id_mes != "*"){
                    // print($id_mes);
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getAccountStatus($this->request, $data_log);

                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generatePDFAccountStatus($data);
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

    public function generatePDFAccountStatus($data){
        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'Fecha', 'style' =>  ["tableHeader", "center"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Código', 'style' =>  ["tableHeader", "left"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Sustento', 'style' =>  ["tableHeader", "left"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Cód. Cta.', 'style' =>  ["tableHeader", "left"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Glosa ', 'style' =>  ["tableHeader", "left"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Debe', 'style' =>  ["tableHeader", "center"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Haber', 'style' =>  ["tableHeader", "center"], 'border'=> [false, true, true, true]),
            array('rowSpan' => 2, 'text' => 'Saldo', 'style' =>  ["tableHeader", "center"], 'border'=> [false, true, false, true]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        $parentNew=null;
        $parentOld=null;
        $r = 0;
        $color = 'white';
        foreach ($data['items'] as $key => $value){
            $parentNew=$value->id_mes;
            $r = $i%2;
            if ( $r == 0) {
                $color = 'white';
            } else {
                $color = '#EDF1F7';
            }
            // print($r);
            if($i===0){
                $body_table[] = array(
                    array('colSpan' => 8, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","","",
                );
            }
            if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 8, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","","",
                );
            }



            $body_table[] = array(
                array('text' => $value->fecha, 'style' => ["tableBody","center"], 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => $value->codigo, 'style' => ["tableBody","left"], 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => $value->file,'link'=>url('/').'/'.$value->file_url, 'style' => ["tableBody","left"], 'color' => '#1155CC', 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => $value->id_cuentaaasi, 'style' => ["tableBody","left"], 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => $value->comentario, 'style' => ["tableBody","left"], 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => $value->debe ==0?'-':number_format($value->debe, 2), 'style' => ["tableBody","right"], 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => $value->haber ==0?'-':number_format($value->haber, 2), 'style' => ["tableBody","right"], 'border'=> [false, false, true, false], 'fillColor'=>$color),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo), 'border'=> [false, false, false, false], 'fillColor'=>$color),
            );
            $i++;
            $parentOld=$value->id_mes;
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Estado de Cuenta entre Entidades',
            'author' => $empresaUser->nombre,
        );

        $array_content=[];
        // $array_content[]=array("text" => "ENTIDAD:  ".$data['datos']['id_entidad']." - ".$data['datos']['entidad']." --  CUENTA CORRIENTE:  ".$data['datos']['id_entidad_cte']." - ".$data['datos']['CtaCte']." ", 'style' => ['subtitle', 'center']);
        $array_content[] = array(
            "text" => "ENTIDAD:  " . (isset($data['datos']['id_entidad']) ? $data['datos']['id_entidad'] : 'N/A') . 
                      " - " . (isset($data['datos']['entidad']) ? $data['datos']['entidad'] : 'N/A') . 
                      " --  CUENTA CORRIENTE:  " . 
                      (($data['datos']['id_entidad_cte'] !== '*') ? $data['datos']['id_entidad_cte'] : 'TODOS') . 
                      '' . (($data['datos']['id_entidad_cte'] !== '*') ? $data['datos']['CtaCte'] : ''),
            'style' => ['subtitle', 'center']
        );
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>50,'height'=>50, 'style' => ["logo"]);
            }
        }
        $array_content[]=array('text' => "ESTADO DE CUENTA ENTRE ENTIDADES", 'style' => ["br","title", 'center']);
        $array_content[]=array('text' => "Detalle de los movimientos de las cuentas corrientes entre entidades expresado en soles", 'style' => ["subtitle", 'center']);
        $array_content[]=array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        $array_content[]=array('text' => '', 'style' => ["br"]);


        $array_content[]=array('style' => 'tableExample', 'color' => '#444',
                                'table' => array(
                                    'headerRows' => 2,
                                    'widths'=> ['10%','10%','15%','10%','24%','10%','10%','10%'],
                                    'body' => $body_table
                                    )
                                );
        $content[]=$array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 14, 'bold' => true, 'color' => '#023246'),
            'subtitle' => array('fontSize' => 11, 'bold' => true, 'color' => '#023246'),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => '#023246', 'fillColor' => '#EDF1F7'),
            'tableBody' => array('fontSize' => 7, 'color' => '#023246'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'left','margin' => [5, -30, 0, -10])
        );
        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getAccountStatusSeatsNoticePDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{

                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_entidad_cte = $this->request->query('id_entidad_cte');
                $datos['id_entidad'] = $id_entidad;
                $datos['id_entidad_cte'] = $id_entidad_cte;

                if ($id_entidad != null and $id_entidad != ' '){
                    $d_entidad = SetupData::entityDetailView($id_entidad);
                    foreach ($d_entidad as $item) {
                        $datos['entidad'] = $item->nom_entidad;
                        $id_empresa = $item->id_empresa;
                    }
                }
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = $this->request;



                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generatePDFAccountStatusSeatsNotice($data);
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

    public function getAccountStatusSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data_log = $jResponse;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getAccountStatusSummary($this->request, $data_log);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getMonthlyControlPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        // $id_entidad = $this->request->id_entidad;

        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_grupoarchivo = $request->id_grupoarchivo;

        $entidades = $request->query('entidades', []);
        $main_array = [];

        if($valida=='SI'){
            $jResponse=[];
            try{

                foreach ($entidades as $id_entidad => $deptos) {
                    // $item = ManagementData::getMonthlyControl($id_grupoarchivo, $id_anho, $id_mes, $id_entidad, $deptos);
                    $datos['empresa_user'] = SetupData::companyByUser($id_user);
                    $datos['empresa'] = (object) ['ruc' => '','nombre_legal'=>''];
                    $datos['entidad'] = (object) ['materno' => ''];
                    $datos['entidad'] = ManagementData::getEntityById($id_entidad);
                    // $datos['deptos'] = $deptos;
                    $datos['id_depto'] = $deptos;
                    $datos['depto'] = ManagementData::getDeparmentByEntity($id_entidad, $deptos)->name;

                    if($datos['entidad']->id_empresa){
                        $datos['empresa'] = ManagementData::getCompanyById($datos['entidad']->id_empresa);
                    }

                    $items = ManagementData::getMonthlyControl($id_grupoarchivo, $id_anho, $id_mes, $id_entidad, $deptos, $datos['entidad']->id_empresa);

                    $data['datos'] = $datos;
                    $data['items'] = [];

                    if ($items) {
                        $data['items'] = $items;
                    }

                    // $main_array = array_merge($main_array, $data);
                    $main_array[] = $data;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateMonthlyControlPdf($main_array);
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

    public function generateMonthlyControlPdf($array_data)
    {
        foreach ($array_data as $index => $data) {
            $mes = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
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
            $parentNew = null;
            $parentOld = null;
            foreach ($data['items'] as $key => $value) {
                $parentNew = $value->id_grupoarchivo;
                if ($i === 0) {
                    $body_table[] = array(
                        array('colSpan' => 7, 'text' => $value->grupoarchivo, 'style' => ["tableBody", "left", "bold", "subheader"]), "", "", "", "", "", "",
                    );
                }
                if ($parentOld !== $parentNew and $i !== 0) {
                    $body_table[] = array(
                        array('colSpan' => 7, 'text' => $value->grupoarchivo, 'style' => ["tableBody", "left", "bold", "subheader"]), "", "", "", "", "", "",
                    );
                }
                $date_limite = new DateTime($value->fecha_limite);
                $date_limite = $date_limite->format('d/m/Y');
                $date_creacion = new DateTime($value->fecha_creacion);
                $date_creacion = $date_creacion->format('d/m/Y');
                $date_modificacion = new DateTime($value->fecha_modificacion);
                $date_modificacion = $date_modificacion->format('d/m/Y');
                if ($value->fecha_limite === null) {
                    $date_limite = '';
                }
                if ($value->fecha_creacion === null) {
                    $date_creacion = '';
                }
                if ($value->fecha_modificacion === null) {
                    $date_modificacion = '';
                }
                $body_table_array = [];
                $body_table_array[] = array('text' => $mes[$value->id_mes], 'style' => ["tableBody", "left"]);
                $body_table_array[] = array('text' => $value->tipoarchivo, 'style' => ["tableBody", "left"]);
                if ($value->file_url) {
                    $body_table_array[] = array('text' => $value->file_nombre, 'link' => url('/') . '/' . $value->file_url, 'style' => ["tableBody", "left"]);
                } else {
                    $body_table_array[] = array('text' => null, 'style' => ["tableBody", "left"]);
                }
                $body_table_array[] = array('text' => $date_limite, 'style' => ["tableBody", "center"]);
                $body_table_array[] = array('text' => $date_creacion, 'style' => ["tableBody", "center"]);
                $body_table_array[] = array('text' => $date_modificacion, 'style' => ["tableBody", "center"]);
                $body_table_array[] = array('text' => $value->user_name, 'style' => ["tableBody", "left"]);
                $body_table[] = $body_table_array;
                $i++;
                $parentOld = $value->id_grupoarchivo;
            }

            // $empresaUser = null;
            // foreach ($data['datos']['empresa_user'] as $key => $value) {
            //     $empresaUser = $value;
            // }
            // $info = array(
            //     'title' => 'CONTROL MENSUAL',
            //     'author' => $empresaUser->nombre,
            // );
            $array_content = [];

            if ($index === 0) {
                $array_content[] = array('text' => 'CONTROL MENSUAL', 'style' => ["br", "title", 'center']);
            } else {
                $array_content[] = array('text' => '', 'style' => ["br"]);
            }

            // $array_content[] = array('text' => 'CONTROL MENSUAL', 'style' => ["br", "title", 'center']);
            $array_content[] = array('text' => 'RUC: ' . mb_strtoupper($data['datos']['entidad']->ruc, 'UTF-8'), 'style' => ["subtitle", 'left']);
            $array_content[] = array('text' => 'RAZON SOCIAL: ' . mb_strtoupper($data['datos']['entidad']->nombre_legal, 'UTF-8'), 'style' => ["subtitle", 'left']);
            $array_content[] = array('text' => 'ENTIDAD: ' . $data['datos']['entidad']->id_entidad . ' - ' . $data['datos']['entidad']->entidad, 'style' => ["subtitle", 'left']);
            $array_content[] = array('text' => 'DEPTO: ' . $data['datos']['depto'], 'style' => ["subtitle", 'left']);
            if ($data['datos']['entidad']->logo && $data['datos']['entidad']->logo !== null) {
                $logo = $this->base64_encode_image($data['datos']['entidad']->logo);
                if ($logo) {
                    $array_content[] = array('image' => $logo, 'width' => 60, 'height' => 60, 'style' => ["logo"], 'isLogo' => 1);
                }
            }
            $array_content[] = array('text' => '', 'style' => ["br"]);
            $array_content[] = array(
                'style' => 'tableExample', 'color' => '#444',
                'table' => array(
                    'headerRows' => 1,
                    'widths' => ['10%', '19%', '24%', '10%', '10%', '10%', '17%'],
                    'body' => $body_table
                )
            );

            $content_array[] = $array_content;
        }

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
            'logo' => array('alignment' => 'right', 'margin' => [0, -65, 0, 0])
        );
        $pageMargins = [30, 30, 30, 30];

        // return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
        return $this->formatPDFJSONARRAY($content_array, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function generatePDFAccountStatusSeatsNotice($data){

        $body_table = [];

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Aviso de Asientos',
            'author' => $empresaUser->nombre,
        );

        $logo=null;
        $width=80;
        $height=80;
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
        } else {
            $logo=$this->base64_encode_image('img/lamb.png');
            $width=100;
            $height=70;
        }
        $body_table[] = array(
            array('colSpan' => 5, 'text' => '', 'style' => ["title","center"], 'border'=>[true, true, true, false]),"","","","",
        );
        $body_table[] = array(
            array('rowSpan' => 2, 'colSpan' => 2,'image' => $logo,'width'=>$width,'height'=>$height,
            'style' => ["logo"], 'border'=>[true, false, false, true]),"",
            array('colSpan' => 3, 'text' => $data['datos']['entidad'], 'style' => ["title","center","bold",],
            'border'=>[false, false, true, false], 'margin' => [0, 7, 0, 0]),"","",
        );
        $body_table[] = array(
            array('colSpan' => 2,'text' => '', 'border'=>[false, false, false, false]),"",
            array('colSpan' => 3, 'text' => 'AVISO DE ASIENTO', 'style' => ["subtitle","center","bold",],
            'border'=>[false, false, true, true], 'margin' => [0, 7, 0, 0]),"","",
        );

        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'ASIENTO', 'style' => ["header","bold"], 'border'=>[true, false, false, false]),"",
            array('text' => 'CUENTA', 'style' => ["header","bold"], 'border'=>[false, false, false, false]),
            array('text' => 'D/C', 'style' => ["header","bold",], 'border'=>[false, false, false, false]),
            array('text' => 'VALOR', 'style' => ["header","bold",], 'border'=>[false, false, true, false]),
        );
        $body_table[] = array(
            array('colSpan' => 2, 'text' => $data['items']->codigo, 'style' => ["header"], 'border'=>[true, false, false, true]),"",
            array('text' => $data['items']->id_cuentaaasi, 'style' => ["header","bold"], 'border'=>[false, false, false, true]),
            array('text' => $data['items']->debe >0?'DÉBITO':'CRÉDITO', 'style' => ["header","bold"], 'border'=>[false, false, false, true]),
            array('text' => $data['items']->debe >0?number_format($data['items']->debe,2):number_format($data['items']->haber,2), 'style' => ["header","bold"],'border'=>[false, false, true, true]),
        );
        $body_table[] = array(
            array('colSpan' => 5, 'text' => 'DETALLE', 'style' => ["header","bold"], 'border'=>[true, false, true, false]),"","","","",
        );

        $body_table[] = array(
            array('colSpan' => 5, 'text' => $data['items']->comentario, 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),"","","","",
        );
        $body_table[] = array(
            array('colSpan' => 5, 'text' => 'Cuentas Patrimoniales :  ' .$data['items']->cod_cuenta, 'style' => ["tableBody","left"], 'border'=>[true, false, true, true]),"","","","",
        );
        $body_table[] = array(
            array('text' => 'Fecha', 'style' => ["tableBody","center"], 'border'=>[true, false, false, false]),
            array('text' => '', 'style' => ["tableBody","center"], 'border'=>[false, false, false, false]),
            array('text' => 'Peparado por', 'style' => ["tableBody","center"], 'border'=>[false, false, false, false]),
            array('text' => '', 'style' => ["tableBody","center"], 'border'=>[false, false, false, false]),
            array('text' => 'Contabilizado por', 'style' => ["tableBody","center"], 'border'=>[false, false, true, false]),
        );
        $body_table[] = array(
            array('text' =>  date('d/m/Y H:i:s', strtotime($data['items']->fec_asiento)), 'style' => ["tableBody","center"], 'border'=>[true, false, false, true]),
            array('text' => '', 'style' => ["tableBody","center"], 'border'=>[false, false, false, true]),
            array('text' => $data['items']->nom_digitador, 'style' => ["tableBody","center"], 'border'=>[false, false, false, true]),
            array('text' => '', 'style' => ["tableBody","center"], 'border'=>[false, false, false, true]),
            array('text' => $data['items']->nom_contador, 'style' => ["tableBody","center"], 'border'=>[false, false, true, true]),
        );



        $content = array(
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444',
                'table' => array(
                    'headerRows' => 1,
                    'heights'=> [22,22,22,7,7,20,5,5,10,10,],
                    'widths'=> ['20%','20%','20%','20%','20%',],
                    'body' => $body_table
                    )
                ),
            );
            $styles = array(
                'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
                'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
                'title' => array('fontSize' => 20, 'bold' => 'true',  'color' => '#4A42BD',),
                'subtitle' => array('fontSize' => 13, 'bold' => false, 'color' => 'black',),
                'header' => array('fontSize' => 10,'alignment' => 'center',),
                'tableExample' => array('margin' => [0, 0, 0, 0]),
                'tableHeader' => array('fontSize' => 15, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
                'tableBody' => array('fontSize' => 9, 'color' => 'black'),
                'center' => array('alignment' => 'center'),
                'right' => array('alignment' => 'right'),
                'bold' => array('bold' => true),
                'subheader' => array('fillColor' => '#CED4DA'),
                'br' => array('margin' => [0, 5, 0, 10]),
                'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
                'logo' => array('alignment' => 'left','margin' => [80, 1, 0, 0]),
                'font'=> 'Helvetica',
            );
            $pageMargins = [20, 30, 20, 30];
        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getTravelSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getTravelSummary($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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


    public function getDeptoEntityGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getDeptoEntityGroup($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getEntityDeptoGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getEntityDeptoGroup($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function addEntityDeptoGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::addEntityDeptoGroup($this->request);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'La operación se realizo de manera exitosa';
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Ya existe un registro con esta entidad y grupo';
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

    public function editEntityDeptoGroup($id_grupo, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::editEntityDeptoGroup($id_grupo, $this->request);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'La operación se realizo de manera exitosa';
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

    public function deleteEntityDeptoGroup($id_grupo, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::deleteEntityDeptoGroup($id_grupo, $request);
                if($data=="OK"){
                    $response['success'] = true;
                    $response['message'] = "Se elimino correctamente";
                    $response['data'] = [];
                    $code = "200";
                }else{
                    $response['success'] = false;
                    $response['message'] = $data;
                    $response['data'] = [];
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

    public function deleteEntityGroupAndDeptos(Request $request,$id_grupo){
        $jResponseSession = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponseSession["code"];
        $valida = $jResponseSession["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::deleteEntityDeptoGroup($id_grupo, $request);
                if($data=="OK"){
                    $data_ = ManagementData::deleteEntityGroup($id_grupo, $request);
                    if($data_=="OK"){
                        $response['success'] = true;
                        $response['message'] = "Se elimino correctamente";
                        $response['data'] = [];
                        $code = "200";
                    }else{
                        $response['success'] = false;
                        $response['message'] = $data_;
                        $response['data'] = [];
                        $code = "202";
                    }
                }else{
                    $response['success'] = false;
                    $response['message'] = $data;
                    $response['data'] = [];
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

    public function getEntityGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getEntityGroup($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function addEntityGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::addEntityGroup($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'El registro se realizo correctamente';
                    $jResponse['data'] = $data;
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

    public function editEntityGroup($id_grupo, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::editEntityGroup($id_grupo, $this->request);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'El grupo se actualizo correctamente';
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

    public function deleteEntityGroup($id_grupo, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::deleteEntityGroup($id_grupo, $this->request);
                if ($data=='OK') {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'El grupo se elimino correctamente';
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

    public function getTravelSummaryPDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_persona = $this->request->query('id_persona');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                if ($id_persona != null and $id_persona != ' '){
                    $d_persona = ManagementData::getDataPerson($request);
                    $datos['persona'] = $d_persona;
                }

                $datos['periodo'] = "Año ".$id_anho;
                $datos['anho'] = $id_anho;
                if ($id_mes != null AND $id_mes != "null" AND $id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;
                }
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getTravelSummary($this->request);
                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generatePDFTravelSummary($data);
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

    public function generatePDFTravelSummary($data){
        $body_table = [];
        $headerTable = array(
            // array( 'text' => 'Depto', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Funcionario', 'style' =>  ["tableHeader", "left"]),
            // array( 'text' => 'Cta.Cte', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Saldo Ant.', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ppto. Gastos ', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ejec. Gastos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo Acum.', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo Ppto. Anual', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Ejecutado', 'style' =>  ["tableHeader", "center"]),
        );


        $body_table[] = $headerTable;
        $i = 0;

        $parentNew=null;
        $parentOld=null;

        foreach ($data['items'] as $key => $value){
            $parentNew=$value->id_depto;
            if($i===0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->depto, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            }

            if($i > 0 and $value->id_depto !== $data['items'][$i -1]->id_depto) {
                $body_table[] = array(
                    array('text' => 'Totales', 'style' => ["tableBody","right", "bold"]),
                    array('text' => '', 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalPto($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPto($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalEject($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEject($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalSaldo($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo($data, $data['items'][$i]->id_depto))),
                    array('text' => '', 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAcum($data, $data['items'][$i]->id_depto))),
                    array('text' => '', 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoPpto($data, $data['items'][$i]->id_depto))),
                    // array('text' => '', 'style' => ["tableBody","right"]),
                );
            }

            if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->depto, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            }


            $body_table[] = array(
                // array('text' => $value->id_depto, 'style' => ["tableBody","center"]),
                array('text' => $value->funcionario, 'style' => ["tableBody","left"]),
                // array('text' => $value->id_ctacte, 'style' => ["tableBody","left"]),
                array('text' => $value->saldo_anterior ==0?'-':$value->saldo_anterior, 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_anterior)),
                array('text' => $value->pto_gasto==0?'-':$value->pto_gasto, 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_gasto)),
                array('text' => $value->eje_gasto==0?'-':$value->eje_gasto, 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_gasto)),
                array('text' => $value->saldo, 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                array('text' => $value->saldo_acumulado, 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_acumulado)),
                array('text' => $value->saldo_ppto_anual, 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_ppto_anual)),
                // array('text' => number_format($value->porcentaje, 0).'%', 'style' => ["tableBody","right"], 'color' =>  number_format($value->porcentaje, 0) > number_format(100,0) ? "red" : "green"),
            );

            if($i > 0 and $i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('text' => 'Totales', 'style' => ["tableBody","right", "bold"]),
                    array('text' => '', 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalPto($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPto($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalEject($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEject($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalSaldo($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo($data, $data['items'][$i]->id_depto))),
                    array('text' => '', 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAcum($data, $data['items'][$i]->id_depto))),
                    array('text' => '', 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoPpto($data, $data['items'][$i]->id_depto))),
                    // array('text' => '', 'style' => ["tableBody","right"]),
                );
            }
/*             if($i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('colSpan'=> 2, 'text' => 'Total', 'style' => ["tableBody","right", "bold"]),"",
                    array('text' => number_format($this->totalGenAnt($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenAnt($data))),
                    array('text' => number_format($this->totalGenPto($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPto($data))),
                    array('text' => number_format($this->totalGenEject($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEject($data))),
                    array('text' => number_format($this->totalGenSaldo($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenSaldo($data))),
                    array('text' => number_format($this->totalGenAcum($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenAcum($data))),
                    array('text' => number_format($this->totalGenPptoAnual($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPptoAnual($data))),
                    // array('text' => '', 'style' => ["tableBody","right"]),
                );
            } */
            $i++;
            $parentOld=$value->id_depto;
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Presupuesto de Viaje',
            'author' => $empresaUser->nombre,
        );


        $array_content=[];
        $array_content[] = array('text' => "Presupuesto de Viajes", 'style' => ["title", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["br","subtitle", 'center']);
        $array_content[] = array('text' => 'RUC: '.mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'DENOMINACIÓN O RAZON SOCIAL: '.mb_convert_case($data['datos']['empresa']->nombre_legal, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'ENTIDAD: '.mb_convert_case($data['datos']['entidad']->materno, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => "Resumen del Presupuesto  y ejecución de gastos de viaje expresado en soles", 'style' => ["subtitle", 'center']);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['20%','12%','14%','14%','12%','14%', '14%'],
                'body' => $body_table
                )
            );
        $content[]=$array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 15, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            'subtitle_1' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'right','margin' => [0, -85, 0, 0])
        );
        $pageMargins = [20, 20, 20, 20];
        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function totalSaldoAnt($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo_anterior;
            }
        }
        return $total;
    }
    public function totalPto($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->pto_gasto;
            }
        }
        return $total;
    }
    public function totalEject($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->eje_gasto;
            }
        }
        return $total;
    }
    public function totalSaldo($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo;
            }
        }
        return $total;
    }

    public function totalSaldoAcum($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo_acumulado;
            }
        }
        return $total;
    }


    public function totalSaldoPpto($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo_ppto_anual;
            }
        }
        return $total;
    }

    public function totalGenAnt($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo_anterior;
        });
        return $total;
    }
    public function totalGenPto($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->pto_gasto;
        });
        return $total;
    }
    public function totalGenEject($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->eje_gasto;
        });
        return $total;
    }
    public function totalGenSaldo($data){
        $total = 0;

        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo;
        });
        return $total;
    }

    public function totalGenAcum($data){
        $total = 0;

        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo_acumulado;
        });
        return $total;
    }

    public function totalGenPptoAnual($data){
        $total = 0;

        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo_ppto_anual;
        });
        return $total;
    }

    public function getBudgetBalanceSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getBudgetBalanceSummary($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getBudgetBalance(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getBudgetBalance($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getBudgetBalanceReport(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getBudgetBalanceReport($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
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

    public function getBudgetBalanceResponsibles(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = ManagementData::getBudgetBalanceResponsibles($this->request);
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

    public function getBudgetBalanceReportDetail(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getBudgetBalanceReportDetail($this->request);
                $total_saldo = ManagementData::getBudgetBalanceReportDetailTotal($this->request);
                if ($data && $total_saldo) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'items' => $data,
                        'total' => $total_saldo,
                    ];
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

    public function getBudgetBalanceReportGeneral(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getBudgetBalanceReportGeneral($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'items' => $data,
                    ];
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

    public function getBudgetBalanceReportExpenses(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $expenses_admin = ManagementData::getBudgetBalanceReportExpensesAdmin($this->request);
                $expenses_depto = ManagementData::getBudgetBalanceReportExpensesDepto($this->request);
                $expenses_support = ManagementData::getBudgetBalanceReportExpensesSupport($this->request);
                if ($expenses_admin) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = [
                        'expenses_admin' => $expenses_admin,
                        'expenses_depto' => $expenses_depto,
                        'expenses_support' => $expenses_support
                    ];
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

    public function getBudgetBalancePDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_depto = $this->request->query('id_depto');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                $datos['periodo'] = "Año ".$id_anho;
                if ($id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getBudgetBalance($this->request);

                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generatePDFBudgetBalance($data);
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

    public function generatePDFBudgetBalance($data){
        $body_table = [];
        $headerTable = array(
            // array( 'text' => 'Depto', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Nombre', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Responsable', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Ejecutado', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo Ant.', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Pto. Ingresos ', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Pto. Gastos ', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ejec. Ingresos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ejec. Gastos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "center"]),
        );


        $body_table[] = $headerTable;
        $i = 0;

        $parentNew=null;
        $parentOld=null;
        foreach ($data['items'] as $key => $value){

            $parentNew=$value->id_depto_pa;
/*             if($i===0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->depto_pa, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            } */

            if($i > 0 and $value->id_depto_pa !== $data['items'][$i -1]->id_depto_pa) {
                $body_table[] = array(
                    array('colSpan'=> 3, 'text' => 'Totales', 'style' => ["tableBody","right", "bold"]),"","",
                    //array('text' => number_format($this->totalSaldoAnt_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt_($data, $data['items'][$i -1]->id_depto_pa))),
                    //array('text' => number_format($this->totalPtoIngreso_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoIngreso_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldo_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo_($data, $data['items'][$i -1]->id_depto_pa))),
                );
            }

/*             if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->depto_pa, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            } */

            $body_table[] = array(
                //array('text' => $value->id_depto, 'style' => ["tableBody","center"]),
                array('text' => $value->depto, 'style' => ["tableBody","center"]),
                //array('text' => $value->responsable, 'style' => ["tableBody","left"]),
                array('text' => number_format($value->porcentaje, 2).'%', 'style' => ["tableBody","center"], 'color' =>  number_format($value->porcentaje, 0) > number_format(100,0) ? "red" : "black"),
                array('text' => $value->saldo_anterior == 0?'-':number_format($value->saldo_anterior, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_anterior)),
                // array('text' => $value->pto_ingresos == 0?'-':number_format($value->pto_ingresos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_ingresos)),
                array('text' => $value->pto_gasto == 0?'-':number_format($value->pto_gasto, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_gasto)),
                array('text' => $value->eje_ingresos == 0?'-':number_format($value->eje_ingresos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_ingresos)),
                array('text' => $value->eje_gastos == 0?'-':number_format($value->eje_gastos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_gastos)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
            );

            if($i > 0 and $i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('colSpan'=> 3, 'text' => 'Totales', 'style' => ["tableBody","right", "bold"]), "","",
                    // array('text' => number_format($this->totalSaldoAnt_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt_($data, $data['items'][$i]->id_depto_pa))),
                    // array('text' => number_format($this->totalPtoIngreso_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoIngreso_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalPtoGasto_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoGasto_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectIngreso_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectIngreso_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectGasto_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectGasto_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldo_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo_($data, $data['items'][$i]->id_depto_pa))),
                );
            }

/*             if($i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('colSpan'=> 2, 'text' => 'Totales', 'style' => ["tableBody","right", "bold"]), "",
                    array('text' => number_format($this->totalGenAnt($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenAnt($data))),
                    array('text' => number_format($this->totalGenPtoIng($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPtoIng($data))),
                    array('text' => number_format($this->totalGenPtoGastos($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPtoGastos($data))),
                    array('text' => number_format($this->totalGenEjecIng($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEjecIng($data))),
                    array('text' => number_format($this->totalGenEjecGastos($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEjecGastos($data))),
                    array('text' => number_format($this->totalGenSaldo($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenSaldo($data))),
                );
            } */
            $i++;
            $parentOld=$value->id_depto_pa;
        }


        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Saldo de Presupuesto por Departamento',
            'author' => $empresaUser->nombre,
        );

        $array_content = [];
        $array_content[] = array('text' => "Saldo de Presupuesto por Departamento", 'style' => ["title", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["br","subtitle", 'center']);
        $array_content[] = array('text' => 'RUC: '.mb_convert_case($data['datos']['empresa']->ruc, MB_CASE_UPPER, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'DENOMINACIÓN O RAZON SOCIAL: '.mb_convert_case($data['datos']['empresa']->nombre_legal, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'ENTIDAD: '.mb_convert_case($data['datos']['entidad']->materno, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['20%','12%','14%','14%','14%', '14%','12%'],
                'body' => $body_table
                )
            );
        $content[] = $array_content;
            $styles = array(
                'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
                'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
                'title' => array('fontSize' => 13, 'bold' => true),
                'subtitle' => array('fontSize' => 11, 'bold' => true),
                'subtitle_1' => array('fontSize' => 9, 'bold' => true),
                'tableExample' => array('margin' => [0, 0, 0, 0]),
                'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
                'tableBody' => array('fontSize' => 7, 'color' => 'black'),
                'center' => array('alignment' => 'center'),
                'right' => array('alignment' => 'right'),
                'bold' => array('bold' => true),
                'subheader' => array('fillColor' => '#CED4DA'),
                'br' => array('margin' => [0, 5, 0, 10]),
                'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
                'logo' => array('alignment' => 'right','margin' => [0, -80, 0, 0])
            );
            $pageMargins = [20, 20, 20, 30];
            return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }


    public function totalSaldoAnt_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto_pa){
                $total = $total + $value->saldo_anterior;
            }
        }
        return $total;
    }
    public function totalPtoIngreso_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto_pa){
                $total = $total + $value->pto_ingresos;
            }
        }
        return $total;
    }

    public function totalPtoGasto_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            if($key === $value->id_depto_pa){
                $total = $total + $value->pto_gasto;
            }
        }
        return $total;
    }
    public function totalEjectIngreso_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            if($key === $value->id_depto_pa){
                $total = $total + $value->eje_ingresos;
            }
        }
        return $total;
    }

    public function totalEjectGasto_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            if($key === $value->id_depto_pa){
                $total = $total + $value->eje_gastos;
            }
        }
        return $total;
    }

    public function totalSaldo_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto_pa){
                $total = $total + $value->saldo;
            }
        }
        return $total;
    }


    public function totalGenPtoIng($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->pto_ingresos;
        });
        return $total;
    }
    public function totalGenPtoGastos($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->pto_gasto;
        });
        return $total;
    }
    public function totalGenEjecIng($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->eje_ingresos;
        });
        return $total;
    }
    public function totalGenEjecGastos($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->eje_gastos;
        });
        return $total;
    }

    public function validColor($data){
        $color = '#023246';
        if($data < 0){
            $color = 'red';
        }
        return $color;
    }

    public function editResponsibleBudgetBalance(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::editResponsibleBudgetBalance($this->request);
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

    public function getResponsible(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $items = ManagementData::getResponsible($this->request);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $items;
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

    public function getTravelSummaryByFunctionary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getTravelSummaryByFunctionary($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function getTravelSummaryByFunctionaryPDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_persona = $this->request->query('id_persona');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;

                if ($id_entidad != null and $id_entidad != ' '){
                    $d_entidad = SetupData::entityDetail($id_entidad);

                    foreach ($d_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
                    }
                }

                if ($id_persona != null and $id_persona != ' '){
                    $d_persona = ManagementData::getDataPerson($request);
                    $datos['persona'] = $d_persona[0];
                }

                $datos['periodo'] = "Año ".$id_anho;
                if ($id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getTravelSummaryByFunctionary($this->request);

                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generatePDFTravelSummaryByFunctionary($data);
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

    public function generatePDFTravelSummaryByFunctionary($data){

        $body_tables = [
        ];

        $body_tables[] = array(
            array('colSpan' => 4, 'text' => $data['datos']['persona']->nom_persona, 'style' => ["tableBody","center","bold","tableHeader"]),"","","",
        );
        $body_tables[] = array(
            array('colSpan' => 2, 'text' => 'Datos Generales', 'style' => ["tableBody","center","bold","subheader"]),"",
            array('colSpan' => 2, 'text' => 'Información Adicional', 'style' => ["tableBody","center","bold","subheader"]),"",
        );

        $body_tables[] = array(
            array('colSpan' => 2, 'text' => 'Nacimiento', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),"",
            array('text' => 'Estado Civil', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),
            array('text' => 'Teléfono', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),
        );
        $body_tables[] = array(
            array('colSpan' => 2, 'text' => date('d/m/Y',strtotime($data['datos']['persona']->fecha_nac)), 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),"",
            array('text' => $data['datos']['persona']->estado_civil, 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
            array('text' => $data['datos']['persona']->telefono?$data['datos']['persona']->telefono: '-----', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
        );
/*         $body_tables[] = array(
            array('colSpan' => 2, 'text' => '', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),"",
            array('text' => '', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
            array('text' => '', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
        ); */
        $body_tables[] = array(
            array('colSpan' => 2, 'text' => 'Edad', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),"",
            array('text' => 'País', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),
            array('text' => 'Dirección', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),
        );
        $body_tables[] = array(
            array('colSpan' => 2, 'text' => $data['datos']['persona']->edad, 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),"",
            array('text' => $data['datos']['persona']->pais, 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
            array('text' => $data['datos']['persona']->direccion, 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
        );
/*         $body_tables[] = array(
            array('colSpan' => 2, 'text' => '', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),"",
            array('text' => '', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
            array('text' => '', 'style' => ["tableBody","left"], 'border'=>[true, false, true, false]),
        ); */
        $body_tables[] = array(
            array('colSpan' => 2, 'text' => 'Documento', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),"",
            array('text' => 'Sexo', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),
            array('text' => 'Correo', 'style' => ["tableBody","left","bold"], 'border'=>[true, false, true, false]),
        );
        $body_tables[] = array(
            array('colSpan' => 2, 'text' => $data['datos']['persona']->doc_number, 'style' => ["tableBody","left"], 'border'=>[true, false, true, true]),"",
            array('text' => $data['datos']['persona']->sexo, 'style' => ["tableBody","left"], 'border'=>[true, false, true, true]),
            array('text' => $data['datos']['persona']->email?$data['datos']['persona']->email:'--------', 'style' => ["tableBody","left"], 'border'=>[true, false, true, true]),
        );

        $body_table_person=$body_tables;


        $body_table = [];
        $headerTable = array(
            array( 'text' => 'Mes', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Depto.', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Cta.Cte', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Saldo Ant.', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Pto. Gastos ', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ejec. Gastos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo Acumulado', 'style' =>  ["tableHeader", "center"]),
        );


        $body_table[] = $headerTable;
        $i = 0;

        $parentNew=null;
        $parentOld=null;

        foreach ($data['items'] as $key => $value){


            $parentNew=$value->id_depto;
            if($i===0){
                $body_table[] = array(
                    array('colSpan' => 6, 'text' => $value->depto, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                    // array('colSpan' => 7, 'text' => $value->depto, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            }
            // suma de los totales por grupo. Anterior mente subtotal
            if($i > 0 and $value->id_depto !== $data['items'][$i -1]->id_depto) {
                $body_table[] = array(
                    array('text' => 'Totales', 'style' => ["tableBody","right"]),
                    //array('colSpan'=> 3, 'text' => 'SubTotal', 'style' => ["tableBody","right"]), "","",
                    array('text' => '', 'style' => ["tableBody","right"]),
                    //array('text' => number_format($this->totalSaldoAnt($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"]),
                    array('text' => number_format($this->totalPto($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"]),
                    array('text' => number_format($this->totalEject($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"]),
                    array('text' => number_format($this->totalSaldo($data, $data['items'][$i -1]->id_depto), 2), 'style' => ["tableBody","right"]),
                    array('text' => '', 'style' => ["tableBody","right"]),
                );
            }

            if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 6, 'text' => $value->depto, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }


            $body_table[] = array(
                array('text' => $value->mes, 'style' => ["tableBody","left"]),
                //array('text' => $value->id_depto, 'style' => ["tableBody","center"]),
                //array('text' => $value->id_ctacte, 'style' => ["tableBody","left"]),
                array('text' => $value->saldo_anterior == 0 ? '-':number_format($value->saldo_anterior, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_anterior)),
                array('text' => $value->pto_gasto == 0 ? '-':number_format($value->pto_gasto,2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_gasto)),
                array('text' => $value->eje_gasto == 0 ? '-':number_format($value->eje_gasto,2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_gasto)),
                array('text' => number_format($value->saldo,2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                array('text' => number_format($value->saldo_acumulado, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_acumulado)),
            );
            // suma de los totales por grupo. Anterior mente subtotal
            if($i > 0 and $i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('text' => 'Totales', 'style' => ["tableBody","right", "bold"]),
                    //array('colSpan'=> 3, 'text' => 'SubTotal', 'style' => ["tableBody","right", "bold"]), "","",
                    array('text' => '', 'style' => ["tableBody","right"]),
                    //array('text' => number_format($this->totalSaldoAnt($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalPto($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPto($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalEject($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEject($data, $data['items'][$i]->id_depto))),
                    array('text' => number_format($this->totalSaldo($data, $data['items'][$i]->id_depto), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo($data, $data['items'][$i]->id_depto))),
                    array('text' => '', 'style' => ["tableBody","right"]),
                );
            }
/*             if($i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('text' => 'Total', 'style' => ["tableBody","right", "bold"]),
                    // array('colSpan'=> 3, 'text' => 'Total', 'style' => ["tableBody","right", "bold"]), "","",
                    array('text' => '', 'style' => ["tableBody","right"]),
                    // array('text' => number_format($this->totalGenAnt($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenAnt($data))),
                    array('text' => number_format($this->totalGenPto($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPto($data))),
                    array('text' => number_format($this->totalGenEject($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEject($data))),
                    array('text' => number_format($this->totalGenSaldo($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenSaldo($data))),
                    array('text' => '', 'style' => ["tableBody","right"]),
                );
            } */
            $i++;
            $parentOld=$value->id_depto;
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Presupuesto de Viaje',
            'author' => $empresaUser->nombre,
        );

        $array_content = [];
        $array_content[] = array('text' => "PRESUPUESTO DE VIAJES", 'style' => ["title", 'center']);
        $array_content[] = array('text' => "ENTIDAD: ".$data['datos']['id_entidad']."-".$data['datos']['entidad'], 'style' => ['subtitle', 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        $array_content[] = array('text' => "Resumen del Presupuesto  y ejecución de gastos de viaje", 'style' => ["subtitle", 'center']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>40,'height'=>40, 'style' => ["logo"]);
            }
        }
        $array_content [] = array('text' => '', 'style' => ["br"]);
        $array_content [] = array('style' => 'tablePersons', 'color' => '#444',
        'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['15%','15%','20%','50%'],
                    'body' => $body_table_person
                )
            );
        $array_content [] = array('text' => '', 'style' => ["br"]);
        $array_content [] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['18%','16%','16%','16%','16%','18%'],
                'body' => $body_table
                )
            );

        $content[] = $array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 17, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            // margin (izquierda,arriba,
            'tablePersons' => array('margin' => [60, 18, 60, 0]),
            'tableExample' => array('margin' => [60, 0, 60, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 8, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'left','margin' => [60, -60, 0, 0])
        );
        $pageMargins = [20, 15, 20, 30];
        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }


    public function getTravelSummaryByFunctionaryExcel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_persona = $this->request->query('id_persona');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;

                if ($id_entidad != null and $id_entidad != ' '){
                    $d_entidad = SetupData::entityDetail($id_entidad);

                    foreach ($d_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
                    }
                }

                if ($id_persona != null and $id_persona != ' '){
                    $d_persona = ManagementData::getDataPerson($request);
                    $datos['persona'] = $d_persona[0];
                }

                $datos['periodo'] = "Año ".$id_anho;
                if ($id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getTravelSummaryByFunctionary($this->request);


                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                // print_r($data);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                //$jResponse['data'] = $this->generatePDFTravelSummaryByFunctionary($data);
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
	    }

        return Excel::create('lista', function($excel) use($data) {


            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.management.travelOfficial", array('data'=>$data));

                $sheet->setOrientation('landscape');


            $sheet->setOrientation('landscape');
            });
        })->download('xls');


        // return response()->json($jResponse,$code);
    }




    /* public function exportXlsDirectory(Request $request){
        $respuesta = ['nerror'=> 0 ,'mensaje' => "ok"];
        $data = [];

        try{

            $data=DirectoryData::listDirectory($this->request);
            print_r($data);
        }catch(Exception $e){
           return 'Error';
        }


       Excel::create('lista', function($excel) use($data) {


            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.humanTalent.directory", array('data'=>$data));

                $sheet->setOrientation('landscape');


            $sheet->setOrientation('landscape');
            });
        })->download('xls');
    } */




    public function uploadAccountStatus(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            $session_token = $jResponse["token"];
            if($valida=='SI'){
                $jResponse=[];
                try{
                    $path = $this->request->url;
                    if($path){
                        $deleteFile = $this->deleteFile($path);
                    }
                    $file = $this->request->file('archivo');
                    $fecha = new DateTime();

                    $filename = $file->getClientOriginalName();
                    $format = strtoupper($file->getClientOriginalExtension());
                    $size = $file->getSize();
                    $fileData = array(
                        'url' => $path,
                        'filename' => $filename,
                        'size'=>$size,
                        'format'=>$format,
                    );



/*                     $fileUpload = $this->uploadFile($file,null,'conta-diario-files/account-status', $session_token);
                    if($fileUpload['success']==true){ */
                        $data = ManagementData::uploadAccountStatus($this->request,$fileData,$id_user);
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
                    /* }
                    else{
                        $jResponse=$fileUpload;
                        $code = "202";
                    } */
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            return response()->json($jResponse);
        }
        public function deleteFileAccountStatus(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                $jResponse=[];
                try{
/*                     $path = $this->request->url;
                    $deleteFile = $this->deleteFile($path);
                    if($deleteFile['success']==true){ */
                        $data = ManagementData::deleteFileAccountStatus($this->request);
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
                    /* }
                    else{
                        $jResponse=$deleteFile;
                        $code = "202";
                    } */
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
            return response()->json($jResponse);
        }

        public function updateUserDownloadFile(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            if($valida=='SI'){
                $jResponse=[];
                try{
                    $data = ManagementData::updateUserDownloadFile($this->request,$id_user);
                    if($data=="OK"){
                        $response['success'] = true;
                        $response['message'] = "Update date download ok";
                        $response['data'] = [];
                        $code = "200";
                    }else{
                        $response['success'] = false;
                        $response['message'] = $data;
                        $response['data'] = [];
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

        public function getPersonsYearMonthSearch(Request $request)
        {
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code = $jResponse["code"];
            $valida = $jResponse["valida"];

            if ($valida == 'SI') {
                $jResponse = [];

                $entity = $request->query('entity');
                $year = $request->query('year');
                $search = $request->query('search');
                $month = $request->query('month');
                $depto = $request->query('depto');
                try {
                    $data = ManagementData::listPersonalYearMonthSearch($entity,$year, $month, $search, $depto);
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }

            }
            return response()->json($jResponse, $code);
        }


        public function getExpenseDetail(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                $jResponse=[];
                $data = ManagementData::getExpenseDetail($this->request);
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

        public function getIncomeDetail(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            if($valida=='SI'){
                $jResponse=[];
                $data = ManagementData::getIncomeDetail($this->request);
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

        public function getAnualBudgetExecutionPDF(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code   = $jResponse["code"];
            $valida = $jResponse["valida"];
            $id_user = $jResponse["id_user"];
            if($valida=='SI'){
                $jResponse=[];
                try{
                    $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                    $id_mes = $this->request->query('id_mes');
                    $id_anho = $this->request->query('id_anho');
                    $id_entidad = $this->request->query('id_entidad');
                    $id_empresa = null;
                    $tipo = $this->request->query('tipo');
                    $datos['id_entidad'] = $id_entidad;
                    $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                    if($id_entidad && $id_entidad !=='*'){
                        $datos['entidad']=ManagementData::getEntityById($id_entidad);
                        $d_entidad = SetupData::entityDetail($id_entidad);
                        foreach ($d_entidad as $item) {
                            $id_empresa = $item->id_empresa;
                        }
                    }

                    $datos['periodo'] = "Año ".$id_anho;
                    $datos['anho'] = $id_anho;
                    $datos['periodo_'] = "Movimientos del ".$id_anho;
                    if ($id_mes != "null" AND $id_mes != "*"){
                        $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                        $fecha = new DateTime($c_date);
                        $fecha->modify('last day of this month');
                        $mes = $meses[($fecha->format('n')) - 1];
                        $datos['periodo'] = $mes." del ".$id_anho;

                    }
                    $datos['empresa_user']=SetupData::companyByUser($id_user);
                    $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                    $items = ManagementData::getBudgetBalanceSummary($this->request);
                    $itemsGastos = ManagementData::getExpenseDetail($this->request);
                    $itemsIngresos = ManagementData::getIncomeDetail($this->request);

                    $data['datos'] = $datos;
                    $data['items'] = [];
                    $data['itemsGastos'] = [];
                    $data['itemsIngresos'] = [];
                    // print_r($items);
                    if ($items) {
                        $data['items'] = $items->data;
                    }

                    if ($itemsGastos) {
                        $data['itemsGastos'] = $itemsGastos->data;
                    }

                    if ($itemsIngresos) {
                        $data['itemsIngresos'] = $itemsIngresos->data;
                    }

                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $this->generateAnualExecutionbudgetPdf($data);
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

    public function generateAnualExecutionbudgetPdf($data){

        $body_table = [];
        $headerTable = array(
            // array( 'text' => 'Depto', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Nombre.', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Responsable', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Pto. Gastos ', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Ejec. Ingresos', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Ejec. Gastos', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Porcentaje', 'style' =>  ["tableHeader", "right"]),
        );
        $body_table[] = $headerTable;
        $i = 0;
        $parentNew=null;
        $parentOld=null;
        foreach ($data['items'] as $key => $value){
            $parentNew=$value->id_depto_pa;
/*             if($i===0){
                $body_table[] = array(
                    array('colSpan' => 6, 'text' => $value->depto_pa, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            } */

            if($i > 0 and $value->id_depto_pa !== $data['items'][$i -1]->id_depto_pa) {
                $body_table[] = array(
                    array('text' => 'SubTotal', 'style' => ["tableBody","right"]),
                    array('text' => number_format($this->totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldo_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => ' ', 'style' => ["tableBody","center"]),
                );
            }

/*             if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 6, 'text' => $value->depto_pa, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            } */


            $body_table[] = array(
                // array('text' => $value->id_depto, 'style' => ["tableBody","center"]),
                array('text' => $value->depto, 'style' => ["tableBody","center"]),
                // array('text' => $value->responsable, 'style' => ["tableBody","left"]),
                array('text' => $value->pto_gasto == 0?'-':number_format($value->pto_gasto, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_gasto)),
                array('text' => $value->eje_ingresos == 0?'-':number_format($value->eje_ingresos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_ingresos)),
                array('text' => $value->eje_gastos == 0?'-':number_format($value->eje_gastos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_gastos)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                array('text' => number_format($value->porcentaje, 0).'%', 'style' => ["tableBody","right"], 'color' =>  number_format($value->porcentaje, 0) > number_format(100,0) ? "red" : "green"),
            );

            if($i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('text' => 'Totales', 'style' => ["tableBody","right", "bold"]),
                    array('text' => number_format($this->totalGenPtoGastos($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPtoGastos($data))),
                    array('text' => number_format($this->totalGenEjecIng($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEjecIng($data))),
                    array('text' => number_format($this->totalGenEjecGastos($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEjecGastos($data))),
                    array('text' => number_format($this->totalGenSaldo($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenSaldo($data))),
                    array('text' => ' ', 'style' => ["tableBody","center"]),
                );
            }
            $i++;
            $parentOld=$value->id_depto_pa;
        }

        if(count($data['items']) === 0) {
            $body_table[] = array(
                array('colSpan'=> 6, 'text' => 'No hay registro', 'style' => ["tableBody","center", "bold"]), "","","","","",
            );
        }

        $body_table_gastos = [];
        $headerTable_gastos = array(
            array( 'text' => 'Fecha', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Lote', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Cuenta', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Glosa', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Debe', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Haber', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "right"]),
        );
        $body_table_gastos[] = $headerTable_gastos;
        $i = 0;
        $parentNew=null;
        $parentOld=null;

        foreach ($data['itemsGastos'] as $key => $value){
            $parentNew=$value->id_mes;
            if($i===0){
                $body_table_gastos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }

            if($parentOld!==$parentNew and $i!==0){
                $body_table_gastos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }


            $body_table_gastos[] = array(
                array('text' => date_format(date_create($value->fec_asiento),"d/m/Y"), 'style' => ["tableBody","left"]),
                array('text' => $value->codigo, 'style' => ["tableBody","center"]),
                // array('text' => $value->cuenta, 'style' => ["tableBody","left"]),
                array('text' => $value->glosa, 'style' => ["tableBody","left"]),
                array('text' => $value->debe == 0 ? '-':number_format($value->debe, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->debe)),
                array('text' => $value->haber == 0 ? '-':number_format($value->haber, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->haber)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
            );

            if($i === (count($data['itemsGastos'])-1)) {
                $body_table_gastos[] = array(
                    array('colSpan'=> 5, 'text' => 'Total', 'style' => ["tableBody","right", "bold"]), "","","","",
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                );
            }
            $i++;
            $parentOld=$value->id_mes;
        }
        // table date for summary incoming
        $body_table_ingresos = [];
        $headerTable_ingresos = array(
            array( 'text' => 'Fecha', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Lote', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Cuenta', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Glosa', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Debe', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Haber', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "right"]),
        );

        $body_table_ingresos[] = $headerTable_ingresos;
        $i = 0;

        $parentNew=null;
        $parentOld=null;

        foreach ($data['itemsIngresos'] as $key => $value){
            $parentNew=$value->id_mes;
            if($i===0){
                $body_table_ingresos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }

            if($parentOld!==$parentNew and $i!==0){
                $body_table_ingresos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }


            $body_table_ingresos[] = array(
                array('text' => $value->fec_asiento, 'style' => ["tableBody","left"]),
                array('text' => $value->codigo, 'style' => ["tableBody","center"]),
                // array('text' => $value->cuenta, 'style' => ["tableBody","left"]),
                array('text' => $value->glosa, 'style' => ["tableBody","left"]),
                array('text' => $value->debe == 0 ? '-':number_format($value->debe, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->debe)),
                array('text' => $value->haber == 0 ? '-':number_format($value->haber, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->haber)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
            );

            if($i === (count($data['itemsIngresos'])-1)) {
                $body_table_ingresos[] = array(
                    array('colSpan'=> 5, 'text' => 'Total', 'style' => ["tableBody","right", "bold"]), "","","","",
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                );
            }
            $i++;
            $parentOld=$value->id_mes;
        }
        if(count($data['itemsIngresos']) === 0) {
            $body_table_ingresos[] = array(
                array('colSpan'=> 6, 'text' => 'No hay registro', 'style' => ["tableBody","Center", "bold"]), "","","","","",
            );
        }
        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Ejecución Presupuestal Anual - Sin saldos iniciales',
            'author' => $empresaUser->nombre,
        );

        $array_content = [];
        $array_content[] = array('text' => "Ejecución Presupuestal Anual - Sin Saldos Iniciales", 'style' => ["br","title", 'center']);
        $array_content[] = array('text' => 'RUC: '.mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'DENOMINACIÓN O RAZON SOCIAL: '.mb_convert_case($data['datos']['empresa']->nombre_legal, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'ENTIDAD: '.mb_convert_case($data['datos']['entidad']->materno, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => 'Resumen de Ejecución', 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'left']);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['25%','15%','15%','15%','15%','15%'],
                'body' => $body_table
                )
            );
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => 'Detalles de Gastos', 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => $data['datos']['periodo_'], 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => 'Listado de Gastos realizados por departamento durante el año '.$data['datos']['anho'], 'style' => ["subtitle", 'left']);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['12%','12%','42%','12%','12%','10%'],
                'body' => $body_table_gastos
                )
            );
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => 'Detalles de Ingresos', 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => $data['datos']['periodo_'], 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => 'Listado de Ingresos obtenidos por departamento durante el año '.$data['datos']['anho'], 'style' => ["subtitle", 'left']);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['12%','12%','42%','12%','12%','10%'],
                'body' => $body_table_ingresos
                )
            );

        $content[] = $array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 12, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            'subtitle_1' => array('fontSize' => 10, 'bold' => true),
            'subtitle_' => array('fontSize' => 10, 'bold' => false),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'right','margin' => [0, -70, 0, 0])
        );

        $pageMargins = [20, 20, 20, 30];
        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getBudgetExecutionPDF(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }
                $datos['periodo'] = "Año ".$id_anho;
                $datos['anho'] = $id_anho;
                $datos['periodo_'] = "Movimientos del ".$id_anho;
                if ($id_mes != "null" AND $id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getBudgetBalanceSummary($this->request);
                $itemsGastos = ManagementData::getExpenseDetail($this->request);
                $itemsIngresos = ManagementData::getIncomeDetail($this->request);

                $data['datos'] = $datos;
                $data['items'] = [];
                $data['itemsGastos'] = [];
                $data['itemsIngresos'] = [];

                if ($items) {
                    $data['items'] = $items->data;
                }

                if ($itemsGastos) {
                    $data['itemsGastos'] = $itemsGastos->data;
                }

                if ($itemsIngresos) {
                    $data['itemsIngresos'] = $itemsIngresos->data;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $this->generateExecutionbudgetPdf($data);
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

    public function generateExecutionbudgetPdf($data){

        $body_table = [];
        $headerTable = array(
            // array( 'text' => 'Depto', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Nombre.', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Porcentaje', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Pto. Ingresos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo Anterior', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Pto. Gastos ', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ejec. Ingresos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Ejec. Gastos', 'style' =>  ["tableHeader", "center"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "center"]),
        );
        $body_table[] = $headerTable;
        $i = 0;
        $parentNew=null;
        $parentOld=null;
        foreach ($data['items'] as $key => $value){
            $parentNew=$value->id_depto_pa;
/*             if($i===0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->depto_pa, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            } */

            if($i > 0 and $value->id_depto_pa !== $data['items'][$i -1]->id_depto_pa) {
                $body_table[] = array(
                    array('colSpan'=> 2, 'text' => 'Totales', 'style' => ["tableBody","right"]), "",
                    // array('text' => number_format($this->totalPtoIngreso_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoIngreso_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldoAnt_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldo_($data, $data['items'][$i -1]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo_($data, $data['items'][$i -1]->id_depto_pa))),
                );
            }

/*             if($parentOld!==$parentNew and $i!==0){
                $body_table[] = array(
                    array('colSpan' => 7, 'text' => $value->depto_pa, 'style' => ["tableBody","left","bold","subheader"]),"","","","","","",
                );
            } */


            $body_table[] = array(
                // array('text' => $value->id_depto, 'style' => ["tableBody","center"]),
                array('text' => $value->depto, 'style' => ["tableBody","center"]),
                array('text' => number_format($value->porcentaje, 2).'%', 'style' => ["tableBody","right"], 'color' =>  number_format($value->porcentaje, 0) > number_format(100,0) ? "red" : "black"),
                // array('text' => $value->pto_ingresos == 0?'-':number_format($value->pto_ingresos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_ingresos)),
                array('text' => $value->saldo_anterior == 0?'-':number_format($value->saldo_anterior, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo_anterior)),
                array('text' => $value->pto_gasto == 0?'-':number_format($value->pto_gasto, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->pto_gasto)),
                array('text' => $value->eje_ingresos == 0?'-':number_format($value->eje_ingresos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_ingresos)),
                array('text' => $value->eje_gastos == 0?'-':number_format($value->eje_gastos, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->eje_gastos)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
            );

            if($i > 0 and $i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('colSpan'=> 2, 'text' => 'Totales', 'style' => ["tableBody","right", "bold"]), "",
                    // array('text' => number_format($this->totalPtoIngreso_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoIngreso_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldoAnt_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldoAnt_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalPtoGasto_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalPtoGasto_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectIngreso_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectIngreso_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalEjectGasto_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalEjectGasto_($data, $data['items'][$i]->id_depto_pa))),
                    array('text' => number_format($this->totalSaldo_($data, $data['items'][$i]->id_depto_pa), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalSaldo_($data, $data['items'][$i]->id_depto_pa))),
                );
            }

/*             if($i === (count($data['items'])-1)) {
                $body_table[] = array(
                    array('colSpan'=> 2, 'text' => 'Totales', 'style' => ["tableBody","right", "bold"]), "",
                    // array('text' => number_format($this->totalGenPtoIng($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPtoIng($data))),
                    array('text' => number_format($this->totalGenAnt($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenAnt($data))),
                    array('text' => number_format($this->totalGenPtoGastos($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenPtoGastos($data))),
                    array('text' => number_format($this->totalGenEjecIng($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEjecIng($data))),
                    array('text' => number_format($this->totalGenEjecGastos($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenEjecGastos($data))),
                    array('text' => number_format($this->totalGenSaldo($data), 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($this->totalGenSaldo($data))),
                );
            } */
            $i++;
            $parentOld=$value->id_depto_pa;
        }

        $body_table_gastos = [];
        $headerTable_gastos = array(
            array( 'text' => 'Fecha', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Lote', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Cuenta', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Glosa', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Debe', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Haber', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "right"]),
        );
        $body_table_gastos[] = $headerTable_gastos;
        $i = 0;
        $parentNew=null;
        $parentOld=null;

        foreach ($data['itemsGastos'] as $key => $value){
            $parentNew=$value->id_mes;
            if($i===0){
                $body_table_gastos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }

            if($parentOld!==$parentNew and $i!==0) {
                $body_table_gastos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",);
            }


            $body_table_gastos[] = array(
                array('text' => date_format(date_create($value->fec_asiento),"d/m/Y") , 'style' => ["tableBody","left"]),
                array('text' => $value->codigo, 'style' => ["tableBody","center"]),
                // array('text' => $value->cuenta, 'style' => ["tableBody","left"]),
                array('text' => $value->glosa, 'style' => ["tableBody","left"]),
                array('text' => $value->debe == 0 ? '-':number_format($value->debe, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->debe)),
                array('text' => $value->haber == 0 ? '-':number_format($value->haber, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->haber)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
            );

            if($i === (count($data['itemsGastos'])-1)) {
                $body_table_gastos[] = array(
                    array('colSpan'=> 5, 'text' => 'Total', 'style' => ["tableBody","right", "bold"]), "","","","",
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                );
            }
            $i++;
            $parentOld=$value->id_mes;
        }
        $body_table_ingresos = [];
        $headerTable_ingresos = array(
            array( 'text' => 'Fecha', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Lote', 'style' =>  ["tableHeader", "center"]),
            // array( 'text' => 'Cuenta', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Glosa', 'style' =>  ["tableHeader", "left"]),
            array( 'text' => 'Debe', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Haber', 'style' =>  ["tableHeader", "right"]),
            array( 'text' => 'Saldo', 'style' =>  ["tableHeader", "right"]),
        );
        $body_table_ingresos[] = $headerTable_ingresos;
        $i = 0;

        $parentNew=null;
        $parentOld=null;

        foreach ($data['itemsIngresos'] as $key => $value){
            $parentNew=$value->id_mes;
            if($i===0){
                $body_table_ingresos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }

            if($parentOld!==$parentNew and $i!==0){
                $body_table_ingresos[] = array(
                    array('colSpan' => 6, 'text' => $value->mes, 'style' => ["tableBody","left","bold","subheader"]),"","","","","",
                );
            }


            $body_table_ingresos[] = array(
                array('text' => date_format(date_create($value->fec_asiento),"d/m/Y"), 'style' => ["tableBody","left"]),
                array('text' => $value->codigo, 'style' => ["tableBody","center"]),
                // array('text' => $value->cuenta, 'style' => ["tableBody","left"]),
                array('text' => $value->glosa, 'style' => ["tableBody","left"]),
                array('text' => $value->debe == 0 ? '-':number_format($value->debe, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->debe)),
                array('text' => $value->haber == 0 ? '-':number_format($value->haber, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->haber)),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
            );

            if($i === (count($data['itemsIngresos'])-1)) {
                $body_table_ingresos[] = array(
                    array('colSpan'=> 5, 'text' => 'Total', 'style' => ["tableBody","right", "bold"]), "","","","",
                    array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right"], 'color' => $this->validColor($value->saldo)),
                );
            }
            $i++;
            $parentOld=$value->id_mes;
        }

        $empresaUser=null;
        foreach ($data['datos']['empresa_user'] as $key => $value){
            $empresaUser=$value;
        }

        $info = array(
            'title' => 'Ejecución Presupuestal',
            'author' => $empresaUser->nombre,
        );

        $array_content = [];
        $array_content[] = array('text' => "Ejecución Presupuestal", 'style' => ["br","title", 'center']);
        $array_content[] = array('text' => 'RUC: '.mb_convert_case($data['datos']['empresa']->ruc, MB_CASE_UPPER, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'DENOMINACIÓN O RAZON SOCIAL: '.mb_convert_case($data['datos']['empresa']->nombre_legal, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        $array_content[] = array('text' => 'ENTIDAD: '.mb_convert_case($data['datos']['entidad']->materno, MB_CASE_TITLE, 'UTF-8'), 'style' => ["subtitle_1", 'left']);
        if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null){
            $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => '', 'style'=> ["br"]);
        $array_content[] = array('text' => 'Resumen de Ejecución', 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle_", 'left']);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['22%','13%','13%','13%','13%','13%', '13%'],
                'body' => $body_table
                )
            );
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => 'Detalles de Gastos', 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => $data['datos']['periodo_'], 'style' => ["subtitle_", 'left']);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['12%','12%','42%','12%','12%','10%'],
                'body' => $body_table_gastos
                )
            );
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('text' => 'Detalles de Ingresos', 'style' => ["subtitle", 'left']);
        $array_content[] = array('text' => $data['datos']['periodo_'], 'style' => ["subtitle_", 'left']);
        $array_content[] =             array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['12%','12%','42%','12%','12%','10%'],
                'body' => $body_table_ingresos
                )
            );
        $content[] = $array_content;
        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 15, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            'subtitle_1' => array('fontSize' => 10, 'bold' => true),
            'subtitle_' => array('fontSize' => 10, 'bold' => false),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'totalParent' => array('fontSize' => 8, 'bold' => 'true'),
            'logo' => array('alignment' => 'right','margin' => [0, -80, 0, 0])
        );
        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }
    public function addUpdateAccountNotice(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::addUpdateAccountNotice($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Se registro correctamente la cuenta';
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

    public function getCorporateIncomeExpenses(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getCorporateIncomeExpenses($this->request);
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


    public function getYearMonthControll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getYearMonthControll();
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

    public function cvMonthControll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::cvMonthControll($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Se registro correctamente la cuenta';
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

    public function listDepartments(Request $request, $id_empresa){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $entidades = $request->entidades;
        if($valida=='SI'){
            $jResponse=[];
            try{
                // echo($id_user);
                $withAllOoption   = $request->query('withAllOoption') ? $request->query('withAllOoption') : '1';
                $data = ManagementData::listDepartments($entidades, $id_user, $withAllOoption);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] =  $data;
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


    public function getMonthlyControlSummaryRanking(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getMonthlyControlSummaryRanking($this->request);
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
    public function getMonthlyControlSummaryNoScore(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::getMonthlyControlSummaryNoScore($this->request);
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

    public function ctaWithoutEquivalences(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::ctaWithoutEquivalences($this->request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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

    public function importCalendarMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ManagementData::importCalendarMonthlyControl($request);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Se registro correctamente el documento';
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

    public function getTravelSummaryExcel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_persona = $this->request->query('id_persona');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                if ($id_persona != null and $id_persona != ' '){
                    $d_persona = ManagementData::getDataPerson($request);
                    $datos['persona'] = $d_persona[0];
                }

                $datos['periodo'] = "Año ".$id_anho;
                $datos['anho'] = $id_anho;
                if ($id_mes != null AND $id_mes != "null" AND $id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;
                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getTravelSummary($this->request);


                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }
                // print_r($data);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
	    }

        return Excel::create('lista', function($excel) use($data) {


            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.management.travelSummary", array('data'=>$data));

                $sheet->setOrientation('landscape');


            $sheet->setOrientation('landscape');
            });
        })->download('xls');


        // return response()->json($jResponse,$code);
    }


    public function getBudgetBalanceExcel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_depto = $this->request->query('id_depto');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                $datos['periodo'] = "Año ".$id_anho;
                if ($id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getBudgetBalance($this->request);

                $data['datos'] = $datos;
                $data['items'] = [];

                if ($items) {
                    $data['items'] = $items;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
	    }


        return Excel::create('lista', function($excel) use($data) {


            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.management.budgetBalance", array('data'=>$data));

                $sheet->setOrientation('landscape');


            $sheet->setOrientation('landscape');
            });
        })->download('xls');
    }


    public function getBudgetExecutionExcel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }
                $datos['periodo'] = "Año ".$id_anho;
                $datos['anho'] = $id_anho;
                $datos['periodo_'] = "Movimientos del ".$id_anho;
                if ($id_mes != "null" AND $id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getBudgetBalanceSummary($this->request);
                $itemsGastos = ManagementData::getExpenseDetail($this->request);
                $itemsIngresos = ManagementData::getIncomeDetail($this->request);

                $data['datos'] = $datos;
                $data['items'] = [];
                $data['itemsGastos'] = [];
                $data['itemsIngresos'] = [];

                if ($items) {
                    $data['items'] = $items->data;
                }

                if ($itemsGastos) {
                    $data['itemsGastos'] = $itemsGastos->data;
                }

                if ($itemsIngresos) {
                    $data['itemsIngresos'] = $itemsIngresos->data;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                // $jResponse['data'] = $this->generateExecutionbudgetPdf($data);
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return Excel::create('lista', function($excel) use($data) {


            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.management.budgetExcecution", array('data'=>$data));

                $sheet->setOrientation('landscape');


            $sheet->setOrientation('landscape');
            });
        })->download('xls');
    }

    public function getAnualBudgetExecutionExcel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_mes = $this->request->query('id_mes');
                $id_anho = $this->request->query('id_anho');
                $id_entidad = $this->request->query('id_entidad');
                $id_empresa = null;
                $tipo = $this->request->query('tipo');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                $datos['periodo'] = "Año ".$id_anho;
                $datos['anho'] = $id_anho;
                $datos['periodo_'] = "Movimientos del ".$id_anho;
                if ($id_mes != "null" AND $id_mes != "*"){
                    $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = $mes." del ".$id_anho;

                }
                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = ManagementData::getBudgetBalanceSummary($this->request);
                $itemsGastos = ManagementData::getExpenseDetail($this->request);
                $itemsIngresos = ManagementData::getIncomeDetail($this->request);

                $data['datos'] = $datos;
                $data['items'] = [];
                $data['itemsGastos'] = [];
                $data['itemsIngresos'] = [];
                // print_r($items);
                if ($items) {
                    $data['items'] = $items->data;
                }

                if ($itemsGastos) {
                    $data['itemsGastos'] = $itemsGastos->data;
                }

                if ($itemsIngresos) {
                    $data['itemsIngresos'] = $itemsIngresos->data;
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return Excel::create('lista', function($excel) use($data) {


            $excel->sheet('lista', function($sheet) use($data) {


                $sheet->loadView("excel.management.anualBudgetExcecution", array('data'=>$data));

                $sheet->setOrientation('landscape');


            $sheet->setOrientation('landscape');
            });
        })->download('xls');
    }




}

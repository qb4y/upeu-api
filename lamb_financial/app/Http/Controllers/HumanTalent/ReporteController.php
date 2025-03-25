<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\ReporteData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\SetupData;
use App\Http\Data\APSData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;
use DateTime;
use App\Http\Data\Report\ManagementData;

class ReporteController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function buscarPersona(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::buscarPersona($request);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function fichapersona(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_persona = $request->query('id_persona');
                $data = ReporteData::fichapersona($id_persona);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function getDatosGenrales(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_persona = $request->query('id_persona');
                $data = ReporteData::getDatosGenrales($id_persona);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function getEmpleadoByEntity(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::getEmpleadoByEntity($request);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
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
    public function uploadDocument(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            $fileUpload=$this->uploadFile($request);
            if($fileUpload['success']==true){
                try{
                    $nombre=null;
                    if($request->input('nombre')){
                        $nombre=$request->input('nombre');
                    }
                    $data = array(
                        "id_persona"=>$request->input('id_persona'),
                        "id_tipodocumento"=>$request->input('id_tipodocumento'),
                        "nombre"=>$nombre,
                        "url"=>$fileUpload['data']['path'],
                        );
                    $result=ReporteData::guardarPersonaDocuemntoUrl($data);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "El documento fue guardado exitosamente";
                    $jResponse['data'] = $result;
                    $code = "200";
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "ORA-".$e->getMessage();
                    $jResponse['data'] =[];
                    $code = "400";
                }
            }else{
                $jResponse=$fileUpload;
            }
        }        
        return response()->json($jResponse,$code);
                
    }
    public function uploadFile($request){
        $data=[];
        $jResponse=[];
            try{
                $file      = $request->file('file');
                $filename  = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $size = $file->getClientSize();
                $picture   = $filename;
                $file->move(public_path()."/humant_talent_files/documents/", $picture);
                $path=request()->getHost()."/api/lamb_financial/public/humant_talent_files/documents/".$picture;
                $data['path']=$path;
                $data['filename']=$filename;
                $data['size']=$size;
                $jResponse['success'] = true;
                $jResponse['message'] = "El documento fue subido exitosamente";
                $jResponse['data'] = $data;
                $jResponse['code'] ="200";
            }catch(Exception $e){                   
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] =[];
                $jResponse['code'] ="400";
            }     
        return $jResponse;
    }
    public function fichapersonadni(Request $request){
        $jResponse['success'] = false;
        $jResponse['message'] = 'No hay acceso';
        $jResponse['data'] = [];
        $code = "202";
  
        try{
            $dni= $request->query('dni');
            $data = ReporteData::fichapersonadni($dni);
            if ($data['nerror']==0) {          
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $data];
                $code = "200";
            }else{
                $jResponse['success'] = false;
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
               
        return response()->json($jResponse,$code);
                
    }
    
    public function getPdfFichaPersona(Request $request){

       
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $data=[];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_persona = $request->query('id_persona');



                $id_empresa = null;
                if ($id_entidad != null and $id_entidad != ' '){
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                $data = ReporteData::fichapersona($id_persona);
                $data['data_empresa']=ManagementData::getCompanyById($id_empresa);
                if($data['data_empresa']->logo && $data['data_empresa']->logo!==null){
                    $logo=$this->base64_encode_image($data['datos']['empresa']->logo);
                    if($logo){
                        $data['data_empresa']->logo = $logo;
                    }
                }
                // $data = ReporteData::fichapersona($id_persona);

                // $data['data_empresa'] = 'hshshs';
                // $data['dempresa'] = 'hola';
                // print_r($data['data_empresa']);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $code = "202";
                }
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $code = "400";
            }
        }   
            return $this->generatePdf($data, 'ficha_personal', 'human-talent.personFile','proliant');
    }
    public function personInfoDetail($id_persona){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $data=[];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::personInfoDetail($id_persona,$id_entidad);
//                dd($data);
                if (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function afpnet(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ReporteData::afpnet($request);
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
        return response()->json($jResponse,$code);
                
    }

    public function deleteDocument(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ReporteData::eliminarPersonaDocuemntoUrl($request);
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
        return response()->json($jResponse,$code);
                
    }
    public function removeImage()
    {  
        if(file_exists(public_path('upload/bio.png'))){
        unlink(public_path('upload/bio.png'));
        }else{

        dd('File does not exists.');

        }
    }
    public function taxdistribution(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = ReporteData::taxdistribution($id_empresa, $id_entidad, $id_anho, $id_mes);
                $total_plame = ReporteData::listTaxPlame($id_empresa, $id_entidad, $id_anho, $id_mes);
                $t_plame = array('renta_cuarta' => 0, 'renta_quinta' => 0, 'dev_quinta_cat' => 0,
                'onp' => 0, 'essalud' => 0, 'eps' => 0, 'essalud_aporte' => 0, 
                'essalud_cant' => 0, 'calculado' => 0, 'total' => 0);

                foreach ($total_plame as $key => $value){
                    $t_plame['renta_cuarta'] = $t_plame['renta_cuarta'] + $value->renta_cuarta;
                    $t_plame['renta_quinta'] = $t_plame['renta_quinta'] + $value->renta_quinta;
                    $t_plame['dev_quinta_cat'] = $t_plame['dev_quinta_cat'] + $value->dev_quinta_cat;
                    $t_plame['onp'] = $t_plame['onp'] + $value->onp;
                    $t_plame['essalud'] = $t_plame['essalud'] + $value->essalud;
                    $t_plame['eps'] = $t_plame['eps'] + $value->eps;
                    $t_plame['essalud_aporte'] = $t_plame['essalud_aporte'] + $value->essalud_aporte;
                    $t_plame['essalud_cant'] = $t_plame['essalud_cant'] + $value->essalud_cant;
                    $t_plame['calculado'] = $t_plame['calculado'] + $value->calculado;
                    $t_plame['total'] = $t_plame['total'] + $value->calculado;
                }
                $datos['data'] = $data;
                $datos['t_plame'] = $t_plame;
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }

        }       
        return response()->json($jResponse,$code);
                
    }
    
    public function taxdistributionEducative(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = ReporteData::taxdistributionEducative($id_empresa, $id_entidad, $id_anho, $id_mes);
                $total_plame = ReporteData::listTaxPlame($id_empresa, $id_entidad, $id_anho, $id_mes);
                $t_plame = array('renta_cuarta' => 0, 'renta_quinta' => 0, 'dev_quinta_cat' => 0,
                'onp' => 0, 'essalud' => 0, 'calculado' => 0, 'total' => 0);

                foreach ($total_plame as $key => $value){
                    $t_plame['renta_cuarta'] = $t_plame['renta_cuarta'] + $value->renta_cuarta;
                    $t_plame['renta_quinta'] = $t_plame['renta_quinta'] + $value->renta_quinta;
                    $t_plame['dev_quinta_cat'] = $t_plame['dev_quinta_cat'] + $value->dev_quinta_cat;
                    $t_plame['onp'] = $t_plame['onp'] + $value->onp;
                    $t_plame['essalud'] = $t_plame['essalud'] + $value->essalud;
                    $t_plame['calculado'] = $t_plame['calculado'] + $value->calculado;
                    $t_plame['total'] = $t_plame['total'] + $value->calculado;
                }
                $datos['data'] = $data;
                $datos['t_plame'] = $t_plame;
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $datos;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }

        }       
        return response()->json($jResponse,$code);
                
    }

    public function listTaxPlame(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_empresa = $request->query('id_empresa');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');

            try {
                $data = ReporteData::listTaxPlame($id_empresa, $id_entidad, $id_anho, $id_mes);
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
        return response()->json($jResponse,$code);
                
    }

    public function addTaxPlame(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::addTaxPlame($this->request);
                if($data=="ok"){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $data;                        
                    $jResponse['data'] = [];
                    $code = "202";
                }	

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }

	    }
        return response()->json($jResponse,$code);
    }

    public function getPdfAFPNET(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];

            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = " - ";

            $id_empresa = $this->request->query('id_empresa');
            $id_entidad = $this->request->query('id_entidad');
            $id_mes = $this->request->query('id_mes');
            $id_anho = $this->request->query('id_anho');

            $mes = AccountingData::getMonthById($id_mes);
            $datos['periodo'] = $mes->nombre." - ".$id_anho;
            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
            
            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*"){
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }
            
            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: ".$item->ruc;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = ReporteData::afpnet($this->request);
                
            $data['datos'] = $datos;
            $data['items'] = [];
    
            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFAfpNet($data);
            $code = "200";
        }       
        return response()->json($jResponse, $code);           
    }

    public function generatePDFAfpNet($data){
        
        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'N°', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'CUSPP', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Tipo Doc.', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Número', 'style' =>  ["tableHeader", "center"]),
            array('colSpan' => 3, 'text' => 'Datos del trabajador', 'style' =>  ["tableHeader", "center"]),
            '','',
            array('rowSpan' => 2, 'text' => 'Relación Laboral', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Inicio de RL', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Cese de RL', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Excepción de Aportar', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Sueldo Base', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Aporte', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'AE', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Inicio de RL', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Riesgo', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'AFP', 'style' =>  ["tableHeader", "center"]),
        );

        $headerSubTable = array(
            '','','','',
            array('text' => 'Paterno', 'style' =>  ["tableHeader"]),
            array('text' => 'Materno', 'style' =>  ["tableHeader"]),
            array('text' => 'Nombres', 'style' =>  ["tableHeader"]),
            '','','','','','','','','','',
        );

        $body_table[] = $headerTable;
        $body_table[] = $headerSubTable;
        
        $i = 0;
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody","center"]),
                array('text' => $value->num_cuspp, 'style' => ["tableBody","center"]),
                array('text' => $value->tipo_documento, 'style' => ["tableBody","center"]),
                array('text' => $value->numero_documento, 'style' => ["tableBody","center"]),
                array('text' => $value->paterno, 'style' => ["tableBody"]),
                array('text' => $value->materno, 'style' => ["tableBody"]),
                array('text' => $value->nombre, 'style' => ["tableBody"]),
                array('text' => $value->r_l, 'style' => ["tableBody","center"]),
                array('text' => $value->inicio_rl, 'style' => ["tableBody","center"]),
                array('text' => $value->cese_rl, 'style' => ["tableBody","center"]),
                array('text' => $value->excepcion_aporte, 'style' => ["tableBody","center"]),
                array('text' => number_format($value->base, 2), 'style' => ["tableBody","right"]),
                array('text' => $value->aporte, 'style' => ["tableBody","center"]),
                array('text' => $value->a_sfp, 'style' => ["tableBody","center"]),
                array('text' => $value->ae, 'style' => ["tableBody","center"]),
                array('text' => $value->riesgo, 'style' => ["tableBody","center"]),
                array('text' => $value->afp, 'style' => ["tableBody","center"]),
            );
        }

        $info = array(
            'title' => 'UPN - AFPNET',
            'author' => 'UPN',
        );
        
        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']),
            array('text' => 'AFPNET - '.$data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 2,
                    'widths'=> ['3%','8%','3%','5%','8%','10%','9%','6%','5%','5%','6%','5%','4%','5%','5%','5%','8%'],
                    'body' => $body_table
                )
            ),
        );

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }

    public function getPdfTaxDistribution(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];

            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = " - ";

            $id_empresa = $this->request->query('id_empresa');
            $id_entidad = $this->request->query('id_entidad');
            $id_mes = $this->request->query('id_mes');
            $id_anho = $this->request->query('id_anho');

            $mes = AccountingData::getMonthById($id_mes);
            $datos['periodo'] = $mes->nombre." - ".$id_anho;
            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
            
            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*"){
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }
            
            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: ".$item->ruc;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = ReporteData::taxdistribution($id_empresa, $id_entidad, $id_anho, $id_mes);
            $total = ReporteData::listTaxPlame($id_empresa, $id_entidad, $id_anho, $id_mes);
            // print($total);
            $data['datos'] = $datos;
            $data['items'] = [];
            $data['total-plame'] = $total;
    
            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFTaxDistribution($data);
            $code = "200";
        }       
        return response()->json($jResponse, $code);           
    }

    public function generatePDFTaxDistribution($data){
        
        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'N°', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'ENTIDAD', 'style' =>  ["tableHeader"]),
            array('rowSpan' => 2, 'text' => 'RENTA 4TA', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'IMP. RENTA 5TA', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'DEV 5TA CAT ', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'ONP', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'ESSALUD', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'EPS', 'style' =>  ["tableHeader", "center"]),
            array('colSpan' => 2, 'text' => 'ESSALUD', 'style' =>  ["tableHeader", "center"]),
            "",
            array('rowSpan' => 2, 'text' => 'CALCULADO', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'TOTAL', 'style' =>  ["tableHeader", "center"]),
        );

        $headerSubTable = array(
            '','','','','','','','',
            array('text' => 'APORTE', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'CANT', 'style' =>  ["tableHeader", "center"]),
            '',''
        );

        $body_table[] = $headerTable;
        $body_table[] = $headerSubTable;
        
        $i = 0;
        $totales = array('t_renta_cuarta' => 0, 't_imp_renta_quinta' => 0, 't_dev_quinta_cat' => 0,
                        't_onp' => 0, 't_essalud' => 0, 't_eps' => 0, 't_essalud_vida' => 0, 
                        't_essalud_vida_cant' => 0, 't_calculado' => 0);
        $t_plame = array('t_renta_cuarta' => 0, 't_renta_quinta' => 0, 't_dev_quinta_cat' => 0,
                        't_onp' => 0, 't_essalud' => 0, 't_eps' => 0, 't_essalud_vida' => 0, 
                        't_essalud_vida_cant' => 0, 't_calculado' => 0);
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody","center"]),
                array('text' => $value->entidad, 'style' => ["tableBody"]),
                array('text' => number_format($value->renta_cuarta, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->imp_renta_quinta, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->dev_quinta_cat, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->onp, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->essalud, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->eps, 2), 'style' => ["tableBody","right"]),
                array('text' => $value->essalud_vida, 'style' => ["tableBody","center"]),
                array('text' => $value->essalud_vida_cant, 'style' => ["tableBody","center"]),
                array('text' => number_format($value->calculado, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->calculado, 2), 'style' => ["tableBody","right"]),
            );
            $totales['t_renta_cuarta'] = $totales['t_renta_cuarta'] + $value->renta_cuarta;
            $totales['t_imp_renta_quinta'] = $totales['t_imp_renta_quinta'] + $value->imp_renta_quinta;
            $totales['t_dev_quinta_cat'] = $totales['t_dev_quinta_cat'] + $value->dev_quinta_cat;
            $totales['t_onp'] = $totales['t_onp'] + $value->onp;
            $totales['t_essalud'] = $totales['t_essalud'] + $value->essalud;
            $totales['t_eps'] = $totales['t_eps'] + $value->eps;
            $totales['t_essalud_vida'] = $totales['t_essalud_vida'] + $value->essalud_vida;
            $totales['t_essalud_vida_cant'] = $totales['t_essalud_vida_cant'] + $value->essalud_vida_cant;
            $totales['t_calculado'] = $totales['t_calculado'] + $value->calculado;
        }

        foreach ($data['total-plame'] as $key => $value){
            $t_plame['t_renta_cuarta'] = $t_plame['t_renta_cuarta'] + $value->renta_cuarta;
            $t_plame['t_renta_quinta'] = $t_plame['t_renta_quinta'] + $value->renta_quinta;
            $t_plame['t_dev_quinta_cat'] = $t_plame['t_dev_quinta_cat'] + $value->dev_quinta_cat;
            $t_plame['t_onp'] = $t_plame['t_onp'] + $value->onp;
            $t_plame['t_essalud'] = $t_plame['t_essalud'] + $value->essalud;
            $t_plame['t_eps'] = $t_plame['t_eps'] + $value->eps;
            $t_plame['t_essalud_vida'] = $t_plame['t_essalud_vida'] + $value->essalud_aporte;
            $t_plame['t_essalud_vida_cant'] = $t_plame['t_essalud_vida_cant'] + $value->essalud_cant;
            $t_plame['t_calculado'] = $t_plame['t_calculado'] + $value->calculado;
        }

        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'TOTAL ASSINET', 'style' => ["tableBody","center","bold","subheader"]),"",
            array('text' => number_format($totales['t_renta_cuarta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_imp_renta_quinta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_dev_quinta_cat'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_onp'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_essalud'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_eps'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_essalud_vida'],2), 'style' => ["tableBody","center","bold","subheader"]),
            array('text' => number_format($totales['t_essalud_vida_cant'],2), 'style' => ["tableBody","center","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
        );

        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'PLAME IMPUESTO', 'style' => ["tableBody","center","bold","subheader"]),"",
            array('text' => number_format($t_plame['t_renta_cuarta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_renta_quinta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_dev_quinta_cat'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_onp'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_essalud'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_eps'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_essalud_vida'],2), 'style' => ["tableBody","center","bold","subheader"]),
            array('text' => number_format($t_plame['t_essalud_vida_cant'],2), 'style' => ["tableBody","center","bold","subheader"]),
            array('text' => number_format($t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
        );

        $body_table[] = array(
            array('colSpan' => 2, 'text' => 'DIFERENCIA - AJUSTE', 'style' => ["tableBody","center","bold","subheader"]),"",
            array('text' => number_format($totales['t_renta_cuarta'] - $t_plame['t_renta_cuarta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_imp_renta_quinta'] - $t_plame['t_renta_quinta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_dev_quinta_cat'] - $t_plame['t_dev_quinta_cat'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_onp'] - $t_plame['t_onp'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_essalud'] - $t_plame['t_essalud'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_eps'] - $t_plame['t_eps'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_essalud_vida'] - $t_plame['t_essalud_vida'],2), 'style' => ["tableBody","center","bold","subheader"]),
            array('text' => number_format($totales['t_essalud_vida_cant'] - $t_plame['t_essalud_vida_cant'],2), 'style' => ["tableBody","center","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'] - $t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'] - $t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
        );

        $info = array(
            'title' => 'UPN - DISTRIBUCIÓN DE IMPUESTOS DE PDT PLAME',
            'author' => 'UPN',
        );
        
        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']),
            array('text' => $data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array('text' => "DISTRIBUCIÓN DE IMPUESTOS DE PDT PLAME", 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 2,
                    'widths'=> ['4%','10%','8%','9%','8%','9%','9%','9%','7%','7%','9%','9%'],
                    'body' => $body_table
                )
            ),
        );

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 8, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
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

    
    public function getPdfTaxDistributionEducative(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];

            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = " - ";

            $id_empresa = $this->request->query('id_empresa');
            $id_entidad = $this->request->query('id_entidad');
            $id_mes = $this->request->query('id_mes');
            $id_anho = $this->request->query('id_anho');

            $mes = AccountingData::getMonthById($id_mes);
            $datos['periodo'] = $mes->nombre." - ".$id_anho;
            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
            
            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*"){
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }
            
            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: ".$item->ruc;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = ReporteData::taxdistributionEducative($id_empresa, $id_entidad, $id_anho, $id_mes);
            $total = ReporteData::listTaxPlame($id_empresa, $id_entidad, $id_anho, $id_mes);
            // print($total);
            $data['datos'] = $datos;
            $data['items'] = [];
            $data['total-plame'] = $total;
    
            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFTaxDistributionEducative($data);
            $code = "200";
        }       
        return response()->json($jResponse, $code);           
    }

    public function generatePDFTaxDistributionEducative($data){
        
        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'N°', 'style' =>  ["tableHeader", "center"]),
            array('colSpan' => 2, 'text' => 'CUENTAS', 'style' =>  ["tableHeader", "center"]),
            "",
            array('rowSpan' => 2, 'text' => 'RENTA 4TA', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'IMP. RENTA 5TA', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'DEV 5TA CAT ', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'ONP', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'ESSALUD', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'CALCULADO', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'TOTAL', 'style' =>  ["tableHeader", "center"]),
        );

        $headerSubTable = array(
            '',
            array('text' => 'DPTO.', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'COLEGIO', 'style' =>  ["tableHeader", "center"]),
            '','','','','','',''
        );

        $body_table[] = $headerTable;
        $body_table[] = $headerSubTable;
        
        $i = 0;
        $totales = array('t_renta_cuarta' => 0, 't_imp_renta_quinta' => 0, 't_dev_quinta_cat' => 0,
                        't_onp' => 0, 't_essalud' => 0, 't_calculado' => 0);
        $t_plame = array('t_renta_cuarta' => 0, 't_renta_quinta' => 0, 't_dev_quinta_cat' => 0,
                        't_onp' => 0, 't_essalud' => 0, 't_calculado' => 0);
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody","center"]),
                array('text' => $value->id_depto, 'style' => ["tableBody"]),
                array('text' => $value->depto, 'style' => ["tableBody"]),
                array('text' => number_format($value->renta_cuarta, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->imp_renta_quinta, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->dev_quinta_cat, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->onp, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->essalud, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->calculado, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->calculado, 2), 'style' => ["tableBody","right"]),
            );
            $totales['t_renta_cuarta'] = $totales['t_renta_cuarta'] + $value->renta_cuarta;
            $totales['t_imp_renta_quinta'] = $totales['t_imp_renta_quinta'] + $value->imp_renta_quinta;
            $totales['t_dev_quinta_cat'] = $totales['t_dev_quinta_cat'] + $value->dev_quinta_cat;
            $totales['t_onp'] = $totales['t_onp'] + $value->onp;
            $totales['t_essalud'] = $totales['t_essalud'] + $value->essalud;
            $totales['t_calculado'] = $totales['t_calculado'] + $value->calculado;
        }

        foreach ($data['total-plame'] as $key => $value){
            $t_plame['t_renta_cuarta'] = $t_plame['t_renta_cuarta'] + $value->renta_cuarta;
            $t_plame['t_renta_quinta'] = $t_plame['t_renta_quinta'] + $value->renta_quinta;
            $t_plame['t_dev_quinta_cat'] = $t_plame['t_dev_quinta_cat'] + $value->dev_quinta_cat;
            $t_plame['t_onp'] = $t_plame['t_onp'] + $value->onp;
            $t_plame['t_essalud'] = $t_plame['t_essalud'] + $value->essalud;
            $t_plame['t_calculado'] = $t_plame['t_calculado'] + $value->calculado;
        }

        $body_table[] = array(
            array('colSpan' => 3, 'text' => 'TOTAL ASSINET', 'style' => ["tableBody","center","bold","subheader"]),"","",
            array('text' => number_format($totales['t_renta_cuarta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_imp_renta_quinta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_dev_quinta_cat'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_onp'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_essalud'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
        );

        $body_table[] = array(
            array('colSpan' => 3, 'text' => 'PLAME IMPUESTO', 'style' => ["tableBody","center","bold","subheader"]),"","",
            array('text' => number_format($t_plame['t_renta_cuarta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_renta_quinta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_dev_quinta_cat'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_onp'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_essalud'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
        );

        $body_table[] = array(
            array('colSpan' => 3, 'text' => 'DIFERENCIA - AJUSTE', 'style' => ["tableBody","center","bold","subheader"]),"","",
            array('text' => number_format($totales['t_renta_cuarta'] - $t_plame['t_renta_cuarta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_imp_renta_quinta'] - $t_plame['t_renta_quinta'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_dev_quinta_cat'] - $t_plame['t_dev_quinta_cat'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_onp'] - $t_plame['t_onp'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_essalud'] - $t_plame['t_essalud'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'] - $t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['t_calculado'] - $t_plame['t_calculado'], 2), 'style' => ["tableBody","right","bold","subheader"]),
        );

        $info = array(
            'title' => 'UPN - DISTRIBUCIÓN DE IMPUESTOS DE PDT PLAME',
            'author' => 'UPN',
        );
        
        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']),
            array('text' => $data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array('text' => "DISTRIBUCIÓN DE IMPUESTOS DE PDT PLAME", 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 2,
                    'widths'=> ['4%','8%', '14%','11%','11%','10%','10%','10%','11%','11%'],
                    'body' => $body_table,
                )
            ),
        );

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 8, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSONEducative($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }

    public function formatPDFJSONEducative($info = null, $content, $styles, $pageSize, $pageOrientation = 'portrait', $pageMargins){
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


    public function generatePdf($p_data, $namePdf, $nameView, $orientation = "landscape")
    {
        $data = $p_data;
        $pdf = DOMPDF::loadView('pdf.' . $nameView, compact('data'))->setPaper('A4', $orientation);
        return $pdf->stream($namePdf . '.pdf');
    }

    public function getResponsibleReporting(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::getResponsibleReporting($this->request);
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

    public function rudResponsibleReporting(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $opcion = $request->opcion;
                $mensaje = '';
                if($opcion == 'r'){
                    $data = ReporteData::addResponsibleReporting($this->request);
                    $mensaje = "The item was created successfully";
                } else if($opcion == 'u'){
                    $data = ReporteData::disabledResponsibleReporting($this->request);
                    $mensaje = "The item was disabled successfully";   
                } else if($opcion == 'd'){
                    $data = ReporteData::deleteResponsibleReporting($this->request);
                    $mensaje = "The item was deleted successfully";   
                }
                
                if($data=="OK"){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $mensaje;                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $data;                        
                    $jResponse['data'] = [];
                    $code = "202";
                }	

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
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
            $data = ReporteData::getTipoArchivo($this->request);
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
                $data = ReporteData::addTipoArchivo($this->request);
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


    public function addConfigMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::addConfigMonthlyControl($this->request);
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
    public function getConfigMonthlyControl(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::getConfigMonthlyControl($this->request);
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
    public function editConfigMonthlyControl($id_archivo_gth,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::editConfigMonthlyControl($id_archivo_gth,$this->request);
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
    public function deleteConfigMonthlyControl($id_archivo_gth,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ReporteData::deleteConfigMonthlyControl($id_archivo_gth);
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
        $id_entidad = $this->request->id_entidad;
        if($valida=='SI'){
            $jResponse=[];
            try{
                $empresa=ReporteData::getEntityById($id_entidad);
                $data = ReporteData::getMonthlyControl($this->request,$empresa->id_empresa);
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
                $fileUpload = $this->uploadFile_($file,null,'monthly-control-gth',$id_entidad.'-'.$id_grupoarchivo.'-'.$id_tipoarchivo.'-'.$fecha->getTimestamp());
                if($fileUpload['success']==true){
                    $data = ReporteData::uploadMonthlyControl($this->request,$fileUpload['data'],$id_user);
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
                    $data = ReporteData::deleteFileMonthlyControl($this->request,$id_user);
                    if($data=="OK"){
                        $response['success'] = true;
                        $response['message'] = "El archivo se elimino correctamente";
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
                    $datos['entidad']=ReporteData::getEntityById($id_entidad);
                }
                if($datos['entidad']->id_empresa){
                $datos['empresa']=ManagementData::getCompanyById($datos['entidad']->id_empresa);
                }

                $items = ReporteData::getMonthlyControl($this->request,$datos['entidad']->id_empresa);
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
            if($value->fecha_limite===null){
                $date_limite='';
            }
            if($value->fecha_creacion===null){
                $date_creacion='';
            }
            if($value->fecha_modificacion===null){
                $date_modificacion='';
            }
            $body_table[] = array(
                array('text' => $mes[$value->id_mes], 'style' => ["tableBody","left"]),
                array('text' => $value->tipoarchivo, 'style' => ["tableBody","left"]),
                array('text' => $value->file_nombre,'link'=>url('/').'/'.$value->file_url, 'style' => ["tableBody","left"]),
                array('text' => $date_limite, 'style' => ["tableBody","center"]),
                array('text' => $date_creacion, 'style' => ["tableBody","center"]),
                array('text' => $date_modificacion, 'style' => ["tableBody","center"]),
                array('text' => $value->user_name, 'style' => ["tableBody","left"]),
            );
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
        
        $content = array(
            array('text' => mb_strtoupper($data['datos']['entidad']->paterno, 'UTF-8'), 'style' => ["br","title", 'center']),
            #array('text' => $data['datos']['empresa']->nombre, 'style' => ["br","title", 'center']),
            #array('text' => $data['datos']['empresa']->ruc, 'style' => ["subtitle", 'center']),
            array('text' => mb_strtoupper($data['datos']['entidad']->materno, 'UTF-8'), 'style' => ["subtitle", 'center']),
            array('image' => $this->base64_encode_image('https://www.upeu.edu.pe/wp-content/uploads/2017/06/Logo-UPeU.png'),'width'=>50,'height'=>50, 'style' => ["logo"]),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['10%','19%','24%','10%','10%','10%','17%'],
                    'body' => $body_table
                )
            ),
        );

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
            'logo' => array('alignment' => 'left','margin' => [0, -50, 0, 0])
        );
        $pageMargins = [30, 30, 30, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
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

    private function uploadFile_($archivo, $nombre, $path,$folder=null)
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

}


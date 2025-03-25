<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\FifthCategoryData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\SetupData;
use App\Http\Data\APSData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FifthCategoryController extends Controller {
    
    private $request;
    
    public function __construct(Request $request){
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

    public function fifthCategoryTotal(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');

            try {
                $data = FifthCategoryData::fifthCategoryTotal($id_entidad, $id_anho);
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

    public function fifthCategoryProjection(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $uit = $request->query('uit');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $page = $request->query('page');
            $pageSize = $request->query('pageSize');
            $countData = $request->query('countData');
            $type = $request->query('type');
            
            if ($type == 'excel'){
                $limit = 0;
                $offset = $countData;
            }else{
                $limit = ($page - 1) * $pageSize + 1;
                $offset = $page * $pageSize;
            }

            try {
                $data = FifthCategoryData::fifthCategoryProjection($uit, $id_entidad, $id_anho, $limit, $offset);
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

    public function fifthCategoryAdjustment(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $uit = $request->query('uit');
            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $page = $request->query('page');
            $pageSize = $request->query('pageSize');
            $countData = $request->query('countData');
            $type = $request->query('type');
            $id_persona = "";
            
            if ($type == 'excel'){
                $limit = 0;
                $offset = $countData;
            }else{
                $limit = ($page - 1) * $pageSize + 1;
                $offset = $page * $pageSize;
            }

            try {
                $data = FifthCategoryData::fifthCategoryAdjustment($uit, $id_entidad, $id_anho, $limit, $offset, $id_persona);
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

    public function getPdfFifthCategoryProjection1(){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $uit = $this->request->query('uit');
                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_anho = $this->request->query('id_anho');
                
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = $id_anho;

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
                
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
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

                $items = FifthCategoryData::fifthCategoryProjection($uit, $id_entidad, $id_anho, 0, 0);
                
                $data['datos'] = $datos;
                $data['items'] = [];
        
                if ($items) {
                    $data['items'] = $items;
                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'fifth_category_calculation_page', 'human-talent.fifth-category.fifthCategoryCalculation', 'landscape');
    }

    public function getPdfFifthCategoryProjection(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $uit = $this->request->query('uit');
            $id_empresa = $this->request->query('id_empresa');
            $id_entidad = $this->request->query('id_entidad');
            $id_anho = $this->request->query('id_anho');
            $countData = $this->request->query('countData');
            
            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['logo'] = "";
            $datos['periodo'] = $id_anho;

            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
            
            $list_entidad = SetupData::entityDetail($id_entidad);

            foreach ($list_entidad as $item) {
                $datos['entidad'] = $item->nombre;
            }
            
            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: ".$item->ruc;
                
                if($item->logo && $item->logo!==null){
                    $logo=$this->base64_encode_image($item->logo);
                    if($logo){
                        $datos['logo'] = $logo;
                    }
                }
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = FifthCategoryData::fifthCategoryProjection($uit, $id_entidad, $id_anho, 0, $countData);
                
            $data['datos'] = $datos;
            $data['items'] = [];
    
            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFFifthCategoryProjection($data);
            $code = "200";
        }       
        return response()->json($jResponse,$code);           
    }

    public function generatePDFFifthCategoryProjection($data){
        
        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'N°', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Código', 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Apellidos y Nombres', 'style' =>  ["tableHeader"]),
            array('colSpan' => 2, 'text' => 'Periodo Cálculo', 'style' =>  ["tableHeader", "center"]),
            '',
            array('rowSpan' => 2, 'text' => 'Meses', 'style' =>  ["tableHeader", "center"]),
            array('text' => "Básico\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Asig Fam\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "RE\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "RV\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Grati\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Bon Ext.\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Asig Ed\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Bon Ed\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Comis.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Bono \n Dest", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Prest. \n Alim.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Asig. \n UPeU", 'style' =>  ["tableHeader", "center"]),
            array('text' => "VLD", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Renta \n Anual", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Deduc\n 7 UIT", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Renta \n Neta", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Renta\n 8%", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Renta \n 14%", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Renta\n 17%", 'style' =>  ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "TOTAL\n IR \n 5TA CAT.", 'style' =>  ["tableHeader", "center"]),
        );

        $headerSubTable = array(
            '','','',
            array('text' => 'Ingreso', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Salida', 'style' =>  ["tableHeader", "center"]),
            '',
            array('text' => '1000', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1122/1121', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1222', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1086', 'style' =>  ["tableHeader", "center"]),
            '',
            array('text' => '3100', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1118', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1119', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1151', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1215', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1147', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1138', 'style' =>  ["tableHeader", "center"]),
            array('text' => '1145', 'style' =>  ["tableHeader", "center"]),
            '','','','','','',''
        );

        $body_table[] = $headerTable;
        $body_table[] = $headerSubTable;
        
        $i = 0;
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody","center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody","center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => date('d/m/Y', strtotime($value->ingreso)), 'style' => ["tableBody","center"]),
                array('text' => date('d/m/Y', strtotime($value->salida)), 'style' => ["tableBody","center"]),
                array('text' => $value->total_meses, 'style' => ["tableBody","center"]),
                array('text' => number_format($value->basico_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->primainf_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->remunesp_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->remunvar_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->grat_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->bextraord_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->asiged_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->basiged_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->comisiones_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->bdestaque_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->bonprestalim_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->asigupeu_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->vld_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->renta_bruta_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->deduccion_7uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->renta_neta, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->hasta_5uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->hasta_20uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->hasta_35uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->total_ir, 2), 'style' => ["tableBody","right"]),
            );
        }

        $info = array(
            'title' => 'UPN - PROYECCION DE 5TA CATEGORÍA',
            'author' => 'UPN',
        );
        $array_content = [];
        $array_content[] = array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']);
        $array_content[] = array('text' => 'PROYECCION DE 5TA CATEGORÍA - '.$data['datos']['entidad'], 'style' => ['subtitle', 'center']);
        $array_content[] = array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        if($data['datos']['logo'] && $data['datos']['logo']!==null){
                $array_content[]=array('image' => $data['datos']['logo'],'width'=>60,'height'=>60, 'style' => ["logo"]);
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444', 
            'table' => array(
                'headerRows' => 2,
                'widths'=> ['2%','3%','17%','4%','4%','2%','3%','4%','3%','3%','3%','3%','3%','3%','3%','3%','3%','3%','3%','4%','4%','4%','4%','4%','4%','4%'],
                'body' => $body_table
            )
        );

        $content[] = $array_content;
        $styles = array(
            'pageInfoLeft' => array('fontSize' => 9, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 9, 'alignment' => 'right'),
            'title' => array('fontSize' => 12, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699', 'margin' => [-3, 0, -3, 0]),
            'tableBody' => array('fontSize' => 7, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'logo' => array('alignment' => 'left','margin' => [0, -65, 0, 0])
        );

        $pageMargins = [15, 30, 15, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A3', 'landscape', $pageMargins);
    }

    public function getPdfFifthCategoryAdjustment1(){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];  
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $uit = $this->request->query('uit');
                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_anho = $this->request->query('id_anho');
                $id_persona = "";
                
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = $id_anho;

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
                
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
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

                $items = FifthCategoryData::fifthCategoryAdjustment($uit, $id_entidad, $id_anho, 0, 0, $id_persona);
                
                $data['datos'] = $datos;
                $data['items'] = [];
        
                if ($items) {
                    $data['items'] = $items;
                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'fifth_category_calculation_page', 'human-talent.fifth-category.fifthCategoryCalculation', 'landscape');
    }

    public function getPdfFifthCategoryAdjustment(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if ($valida == 'SI') {
            $jResponse = [];
            $uit = $this->request->query('uit');
            $id_empresa = $this->request->query('id_empresa');
            $id_entidad = $this->request->query('id_entidad');
            $id_anho = $this->request->query('id_anho');
            $countData = $this->request->query('countData');
            $id_persona = "";
            
            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = $id_anho;

            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);
            
            $list_entidad = SetupData::entityDetail($id_entidad);

            foreach ($list_entidad as $item) {
                $datos['entidad'] = $item->nombre;
            }
            
            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: ".$item->ruc;
                $datos['logo'] = $item->logo;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = FifthCategoryData::fifthCategoryAdjustment($uit, $id_entidad, $id_anho, 0, $countData, $id_persona);
                
            $data['datos'] = $datos;
            $data['items'] = [];
    
            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFFifthCategoryAdjustment($data);
            $code = "200";
        }       
        return response()->json($jResponse,$code);           
    }

    public function generatePDFFifthCategoryAdjustment($data){
        
        $body_table = [];
        $headerTable = array(
            array('text' => 'N°', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Código', 'style' =>  ["tableHeader", "center"]),
            array('text' => 'Apellidos y Nombres', 'style' =>  ["tableHeader"]),
            array('text' => "Básico\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Grat\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Grat Ext.\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Bonif\n Asig.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Otros\n Conc. Rem", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Asig.\n Fam.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Horas\n Extras", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Rem Emp\n Anterior", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Vacac.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Prest. \n Alim.", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Total Rem. \n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Dcto\n Limite", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Renta\n 8%", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Renta \n 14%", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Renta\n 17%", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Renta T.\n Anual", 'style' =>  ["tableHeader", "center"]),
            array('text' => "I.R \n APS", 'style' =>  ["tableHeader", "center"]),
            array('text' => "Dif Ajust\n Diciembre", 'style' =>  ["tableHeader", "center"]),
            array('text' => "5TA CAT. \n 1502", 'style' =>  ["tableHeader", "center"]),
            array('text' => "5TA CAT. \n 1350", 'style' =>  ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;
        
        $i = 0;
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody","center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody","center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => number_format($value->a_basico_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->b_greatif_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->c_greatifextra_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->d_bonifasign_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->e_otrconcepremun_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->f_asignfam_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->g_horasextras_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->h_remempresasant_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->i_vacaciones_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->j_presalim_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->descto_limite, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->hasta_5uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->hasta_20uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->hasta_35uit, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->renta_anual_total, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->rt_retenciones_anual, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->dif_ajust_dic, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->quinta_1502, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->quinta_1350, 2), 'style' => ["tableBody","right"]),
            );
        }

        $info = array(
            'title' => 'UPN - AJUSTE DE 5TA CATEGORÍA',
            'author' => 'UPN',
        );
        $array_content = [];
        $array_content[] = array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']);
        $array_content[] = array('text' => 'AJUSTE DE 5TA CATEGORÍA - '.$data['datos']['entidad'], 'style' => ['subtitle', 'center']);
        $array_content[] = array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        if($data['datos']['logo'] && $data['datos']['logo']!==null){
            $logo=$this->base64_encode_image($data['datos']['logo']);
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
                'widths'=> ['2%','3%','17%','4%','4%','4%','4%','4%','4%','3%','4%','4%','4%','4%','4%','4%','4%','3%','4%','4%','4%','4%','4%'],
                'body' => $body_table
            )
        );
        $content[] = $array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 12, 'bold' => true),
            'subtitle' => array('fontSize' => 11, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699', 'margin' => [-3, 0, -3, 0]),
            'tableBody' => array('fontSize' => 7, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'logo' => array('alignment' => 'left','margin' => [0, -75, 0, 0])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A3', 'landscape', $pageMargins);
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

    public function generatePdf($p_data, $namePdf, $nameView, $orientation = "landscape")
    {
        $data = $p_data;
        $pdf = DOMPDF::loadView('pdf.' . $nameView, array('data'=>$data))->setPaper('A4', $orientation);
        return $pdf->stream($namePdf . '.pdf');
    }

    public function getPdfFifthCategoryCertificate(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        $datos['empresa'] = "";
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $uit = $this->request->query('uit');
                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_anho = $this->request->query('id_anho');
                $id_persona =  $this->request->query('id_persona');

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['direccion'] = " - ";
                $datos['telefono'] = " - ";
                $datos['id_anho'] = $id_anho;

                $datos['entidad'] = "Todas las entidades";
                if ($id_entidad != "*") {
                    $list_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($list_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                    }
                }

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

                foreach ($list_razon_social as $item) {
                    $datos['id_empresa'] = $id_empresa;
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: " . $item->ruc;
                }
                

                
                 $list_doc_representative = SetupData::DocRepresentativeFilters($this->request);
                foreach ($list_doc_representative as $item) {
                    $datos['representante'] = $item->representante;
                    $datos['direccion_legal'] = $item->direccion_legal;
                    $datos['nom_entidad'] = $item->nam_entidad;
                    $datos['nom_ciudad'] = $item->nom_ciudad;
                    if($item->id_tipodocumento == 1){
                        $datos['documento'] =  "DNI Nº ".$item->documento;
                    } elseif ($item->id_tipodocumento == 4) {
                        $datos['documento'] =  "CarEx Nº ".$item->documento;
                    } elseif ($item->id_tipodocumento == 7) {
                        $datos['documento'] =  "Pass Nº ".$item->documento;
                    }
                }

                
                $items = FifthCategoryData::fifthCategoryAdjustment($uit, $id_entidad, $id_anho, 0, 0, $id_persona);
                $data['datos'] = $datos;
                $data['items'] = [];
        
                if ($items) {
                    $data['items'] = $items;
                }

            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'FifthCategory_certificate', 'human-talent.fifth-category.certificadoRentasyRe5cat', 'proliand');
    }

    public function getUit(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FifthCategoryData::getUit($this->request);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
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

    public function addUit(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FifthCategoryData::addUit($this->request);
                if($data=="OK"){
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

    public function editUit($id_uit, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                    $ok = FifthCategoryData::editUit($id_uit, $this->request);
                    if($ok=="OK"){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $ok;
                        $jResponse['data'] = [];
                        $code = "202";
                    }

            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    
    public  function deleteUit($id_plan_fichafinanciera){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                FifthCategoryData::deleteUit($id_plan_fichafinanciera);
                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";                    
                $jResponse['data'] = [];
                $code = "200";                  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
}
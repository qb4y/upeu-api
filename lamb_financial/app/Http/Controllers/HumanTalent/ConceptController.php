<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\ConceptData;
use App\Http\Data\HumanTalent\ReporteData;
use App\Http\Data\SetupData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;

class ConceptController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }

    public function getTypeConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getTypeConcepts();
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
    public function getConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getConcepts();
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

    public function getPaidConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getPaidConcepts($request);
                $datos = ReporteData::getDatosGenrales($request);
                if ($data || $datos) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data,'datos' => $datos];
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

    public function getBallotConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getBallotConcepts($request);
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
    public function getTypeGroupAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getTypeGroupAccount();
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
    public function getConceptsPayrollAps(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getConceptsPayrollAps($request);
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
    public function getBalanceConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getBalanceConcepts($request);
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
    public function getBalanceEquityAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getBalanceEquityAccounts($request);
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
    public function getAccountBalancesSpending(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getAccountBalancesSpending($request);
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
    public function getDetailEquityAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getDetailEquityAccounts($request);
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
    public function getPDFConceptsPayrollAps(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_tipoconceptoaps = $request->query('id_tipoconceptoaps');
                $data = ConceptData::getConceptsPayrollAps($request);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $this->generatePDFConceptsPayrollAps($data, $id_tipoconceptoaps);
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

    public function generatePDFConceptsPayrollAps($data, $id_tipoconceptoaps){
        
        $body_table = [];
        $headerTable = array(
            array('text' => "CÓDIGO", 'style' =>  ["tableHeader", "center"]),
            array('text' => "CONCEPTO", 'style' =>  ["tableHeader"]),
            array('text' => "TIPO CONTRATO", 'style' =>  ["tableHeader"]),
            array('text' => "N° CTA", 'style' =>  ["tableHeader", "center"]),
            array('text' => "CUENTA", 'style' =>  ["tableHeader"]),
            array('text' => "", 'style' =>  ["tableHeader"]),
            array('text' => "EMPRESARIAL", 'style' =>  ["tableHeader"]),
            array('text' => "SUNAT", 'style' =>  ["tableHeader", "center"]),
            
        );

        $body_table[] = $headerTable;
        
        $i = 0;
        $temp = null;
        $cellColspan = false;
        $filterConceptname = 'Todos los tipos de conceptos';
        foreach ($data as $key => $value){
            
            if($i == 0){
                $temp = $value->id_tipoconceptoaps;
                $cellColspan = true;
                if($id_tipoconceptoaps && $id_tipoconceptoaps!=null && $id_tipoconceptoaps!='null'){
                    $filterConceptname = strtoupper($value->tipoconceptoplanilla);
                }
            }elseif ($temp != $value->id_tipoconceptoaps) {
                $temp = $value->id_tipoconceptoaps;
                $cellColspan = true;
            }else{
                $cellColspan = false;
            }

            if($cellColspan){
                $body_table[] = array(
                    array(
                    'colSpan' => 8,
                    'text' => strtoupper($value->tipoconceptoplanilla),
                    'style' => ['tableBody', 'bold', 'subconcept']
                    )
                );
            }
            
            $i++;
            $body_table[] = array(
                array('text' => $value->id_conceptoaps, 'style' => ["tableBody","center"]),
                array('text' => $value->concepto, 'style' => ["tableBody"]),
                array('text' => $value->contrato, 'style' => ["tableBody"]),
                array('text' => $value->id_cuentaaasi, 'style' => ["tableBody","center"]),
                array('text' => $value->cuenta, 'style' => ["tableBody"]),
                array('text' => $value->id_cuentaempresarial, 'style' => ["tableBody","center"]),
                array('text' => $value->cuentaempresarial, 'style' => ["tableBody"]),
                array('text' => $value->cod_sunat, 'style' => ["tableBody","center"]),
            );
        }

        $info = array(
            'title' => 'UPN - CONCEPTOS DE LA PLANILLA APS',
            'author' => 'UPN',
        );
        
        $content = array(
            array('text' => 'CONCEPTOS DE LA PLANILLA APS', 'style' => ["br","title", 'center']),
            array('text' => 'Tipo Concepto: '.$filterConceptname, 'style' => ['subtitle', 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['6%','23%','15%','7%','22%','5%','17%','5%'],
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
            'tableHeader' => array('fontSize' => 7, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subconcept' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getPDFBalanceConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');
                $id_anho = $request->query('year');
                $acumulate = $request->query('acumulate');
                $month_init = $request->query('month_init');
                $month = $request->query('month');
                $id_conceptoaps = $request->query('id_conceptoaps');
                $getEntity=ConceptData::getEntity($id_entidad);
                $data['items'] = ConceptData::getBalanceConcepts($request);
                $data['entidad']=$id_entidad.' - '.$getEntity->nombre;
                if($acumulate=='1'){
                    $data['fechas']=$meses[$month_init-1].' - '.$meses[$month-1].' del '.$id_anho;
                }else{
                    $data['fechas']=$meses[$month-1].' del '.$id_anho;
                }
                if (count($data['items'])>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $this->generatePDFBalanceConcepts($data, $id_conceptoaps);
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

    public function generatePDFBalanceConcepts($data, $id_conceptoaps){
        
        $body_table = [];
        $headerTable = array(
            array('text' => "DOCUMENTO", 'style' =>  ["tableHeader", "center"]),
            array('text' => "APELLIDOS Y NOMBRES", 'style' =>  ["tableHeader"]),
            array('text' => "CONCEPTO", 'style' =>  ["tableHeader"]),
            array('text' => "VALOR", 'style' =>  ["tableHeader", "center"]),
            array('text' => "REFERENCIA", 'style' =>  ["tableHeader"]),
        );

        $body_table[] = $headerTable;
        
        $i = 0;
        $temp = null;
        $cellColspan = false;
        $filterConceptname = 'Todos los conceptos';
        $total_ref=0;
        $total_valor=0;
        foreach ($data['items'] as $key => $value){
            
            if($i == 0){
                $temp = $value->id_tipoconceptoaps;
                $cellColspan = true;
                if($id_conceptoaps && $id_conceptoaps!=null && $id_conceptoaps!='null'){
                    $filterConceptname = strtoupper($value->concepto);
                }
            }elseif ($temp != $value->id_tipoconceptoaps) {
                $temp = $value->id_tipoconceptoaps;
                $cellColspan = true;
            }else{
                $cellColspan = false;
            }

            if($cellColspan){
                $body_table[] = array(
                    array(
                    'colSpan' => 5,
                    'text' => strtoupper($value->tipoconcepto),
                    'style' => ['tableBody', 'bold', 'subconcept']
                    )
                );
            }
            $colorValor = "";
            $colorRef = "";
            if ($value->valor < 0){
                $colorValor = "red";
            }
            if ($value->referencia < 0){
                $colorRef = "red";
            }
            $i++;
            $body_table[] = array(
                array('text' => $value->num_documento, 'style' => ["tableBody","center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => $value->concepto, 'style' => ["tableBody"]),
                array('text' => number_format($value->valor, 2), 'style' => ["tableBody","right", $colorValor]),
                array('text' => number_format($value->referencia, 2), 'style' => ["tableBody","right", $colorRef]),
            );
            $total_ref=$total_ref+$value->valor;
            $total_valor=$total_valor+$value->referencia;
        }
        $colorValor = "";
        $colorRef = "";
        if ($total_valor < 0){
            $colorValor = "red";
        }
        if ($total_ref < 0){
            $colorRef = "red";
        }
        $body_table[] = array(
            array('colSpan' => 3,'text' => 'Total', 'style' => ["tableBody","right","bold"]),"","",
            array('text' => number_format($total_ref, 2), 'style' => ["tableBody","right","bold", $colorRef]),
            array('text' => number_format($total_valor, 2), 'style' => ["tableBody","right","bold", $colorValor]),
        );

        $info = array(
            'title' => $data['entidad'].' - SALDOS POR CONCEPTOS',
            'author' => '',
        );
        
        $content = array(
            array('text' => $data['entidad'].' - SALDOS POR CONCEPTOS', 'style' => ["br","title", 'center']),
            array('text' => 'Tipo Concepto: '.$filterConceptname, 'style' => ['subtitle', 'center']),
            array('text' => $data['fechas'], 'style' => ['subtitle', 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['11%','30%','29%','15%','15%'],
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
            'red' => array('color' => 'red'),
            'subconcept' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getPDFBalanceEquityAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        
        if($valida=='SI'){
            $jResponse=[];
            try{

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = " - ";
                $datos['tipo_reporte'] = "";

                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('year');
                $acumulate = $request->query('acumulate');
                $month_init = $request->query('month_init');
                $month = $request->query('month');

                $items = ConceptData::getBalanceEquityAccounts($request);

                $list_razon_social = SetupData::enterpriseByIdEntity($id_entidad);
            
                $list_entidad = SetupData::entityDetail($id_entidad);
                $month_object = SetupData::getMonthById($month);
                $month_object_name = '';

                foreach ($month_object as $item) {
                    $month_object_name = $item->nombre;
                }

                if($acumulate=='1'){
                    $month_ini_object = SetupData::getMonthById($month_init);
                    foreach ($month_ini_object as $item) {
                        $datos['periodo'] = $item->nombre." - ".$month_object_name." ".$id_anho;
                    }
                    $datos['tipo_reporte'] = "Acumulado";
                }else{
                    $datos['periodo'] = $month_object_name." ".$id_anho;
                }

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
                
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }

                $data['datos'] = $datos;
                
                $data['items'] = [];

                if (count($items)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $data['items'] = $items;
                    $title = "SALDO DE LAS CUENTAS PATRIMONIALES";
                    $jResponse['data'] = $this->generatePDFBalanceEquityAndSpendingAccounts($data, $title);
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

    public function getPDFAccountBalancesSpending(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        
        if($valida=='SI'){
            $jResponse=[];
            try{

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = " - ";
                $datos['tipo_reporte'] = "";

                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('year');
                $acumulate = $request->query('acumulate');
                $month_init = $request->query('month_init');
                $month = $request->query('month');

                $items = ConceptData::getAccountBalancesSpending($request);

                $list_razon_social = SetupData::enterpriseByIdEntity($id_entidad);
            
                $list_entidad = SetupData::entityDetail($id_entidad);
                $month_object = SetupData::getMonthById($month);
                $month_object_name = '';

                foreach ($month_object as $item) {
                    $month_object_name = $item->nombre;
                }

                if($acumulate=='1'){
                    $month_ini_object = SetupData::getMonthById($month_init);
                    foreach ($month_ini_object as $item) {
                        $datos['periodo'] = $item->nombre." - ".$month_object_name." ".$id_anho;
                    }
                    $datos['tipo_reporte'] = "Acumulado";
                }else{
                    $datos['periodo'] = $month_object_name." ".$id_anho;
                }

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
                
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }

                $data['datos'] = $datos;
                
                $data['items'] = [];

                if (count($items)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $data['items'] = $items;
                    $title = 'SALDOS DE LAS CUENTAS DE GASTOS';
                    $jResponse['data'] = $this->generatePDFBalanceEquityAndSpendingAccounts($data, $title);
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

    public function generatePDFBalanceEquityAndSpendingAccounts($data, $title){
        
        $body_table = [];
        $headerTable = array(
            array('text' => "CUENTA", 'style' =>  ["tableHeader", "center"]),
            array('text' => "NOMBRE", 'style' =>  ["tableHeader"]),
            array('text' => "DEBE", 'style' =>  ["tableHeader", "center"]),
            array('text' => "HABER", 'style' =>  ["tableHeader", "center"]),
            array('text' => "SALDO", 'style' =>  ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;
        
        $cellColspan = false;
        $i = 0;
        $totalDebe=0;
        $totalHaber=0;
        $totalSaldo=0;
        foreach ($data['items'] as $key => $value){
            
            if($i == 0){
                $temp = $value->parent;
                $cellColspan = true;
                
            }elseif ($temp != $value->parent) {
                $temp = $value->parent;
                $cellColspan = true;
            }else{
                $cellColspan = false;
            }

            if($cellColspan){
                $body_table[] = array(
                    array(
                    'colSpan' => 5,
                    'text' => strtoupper($value->parentnombre),
                    'style' => ['tableBody', 'bold', 'subconcept']
                    )
                );
            }
            $colorSaldo = "";
            if ($value->saldo < 0){
                $colorSaldo = "red";
            }
            
            $i++;
            $body_table[] = array(
                array('text' => $value->cuenta, 'style' => ["tableBody","center"]),
                array('text' => $value->nombre, 'style' => ["tableBody"]),
                array('text' => number_format($value->debe, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->haber, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->saldo, 2), 'style' => ["tableBody","right", $colorSaldo]),
            );
            $totalDebe=$totalDebe+$value->debe;
            $totalHaber=$totalHaber+$value->haber;
            $totalSaldo=$totalSaldo+$value->saldo;
        }
        $colorSaldo = "";
        if ($totalSaldo < 0){
            $colorSaldo = "red";
        }
        $body_table[] = array(
            array('colSpan' => 2,'text' =>'Total', 'style' => ["tableBody","right","bold"]),"",
            array('text' => number_format($totalDebe, 2), 'style' => ["tableBody","right","bold"]),
            array('text' => number_format($totalHaber, 2), 'style' => ["tableBody","right","bold"]),
            array('text' => number_format($totalSaldo, 2), 'style' => ["tableBody","right","bold", $colorSaldo]),
        );
        $info = array(
            'title' => $data['datos']['entidad'].' - '.$title,
            'author' => $data['datos']['entidad'],
        );
        
        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']),
            array('text' => $title.' - '.$data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['tipo_reporte'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['10%','60%','10%','10%','10%'],
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
            'red' => array('color' => 'red'),
            'subconcept' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getPDFBallotConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_tipogrupocuenta = $request->query('id_tipogrupocuenta');
                $data = ConceptData::getBallotConcepts($request);
                if (count($data)>0) {          
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $this->generatePDFBallotConcepts($data, $id_tipogrupocuenta);
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

    public function generatePDFBallotConcepts($data, $id_tipogrupocuenta){
        
        $body_table = [];
        $headerTable = array(
            array('text' => "NOMBRE", 'style' =>  ["tableHeader"]),
            array('text' => "ORDEN", 'style' =>  ["tableHeader", "center"]),
            array('text' => "CONCEPTOS", 'style' =>  ["tableHeader"]),
        );

        $body_table[] = $headerTable;
        
        $i = 0;
        $temp = null;
        $cellColspan = false;
        $filterGroupname = 'Todos los grupos de cuentas';
        foreach ($data as $key => $value){
            
            if($i == 0){
                $temp = $value->id_tipogrupocuenta;
                $cellColspan = true;
                if($id_tipogrupocuenta && $id_tipogrupocuenta!=null && $id_tipogrupocuenta!='null'){
                    $filterGroupname = strtoupper($value->grupocuenta);
                }
            }elseif ($temp != $value->id_tipogrupocuenta) {
                $temp = $value->id_tipogrupocuenta;
                $cellColspan = true;
            }else{
                $cellColspan = false;
            }

            if($cellColspan){
                $body_table[] = array(
                    array(
                    'colSpan' => 3,
                    'text' => strtoupper($value->grupocuenta),
                    'style' => ['tableBody', 'bold', 'subconcept']
                    )
                );
            }
            
            $i++;
            $body_table[] = array(
                array('text' => $value->nombre, 'style' => ["tableBody"]),
                array('text' => $value->num_orden, 'style' => ["tableBody", "center"]),
                array('text' => $value->cuentas, 'style' => ["tableBody"]),
            );
        }

        $info = array(
            'title' => 'UPN - DETALLE DE LAS CUENTAS PATRIMONIALES',
            'author' => 'UPN',
        );
        
        $content = array(
            array('text' => 'DETALLE DE LAS CUENTAS PATRIMONIALES', 'style' => ["br","title", 'center']),
            array('text' => 'Grupo de cuenta: '.$filterGroupname, 'style' => ['subtitle', 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['30%','10%','60%'],
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
            'red' => array('color' => 'red'),
            'subconcept' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getPDFPaidConcepts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        
        if($valida=='SI'){
            $jResponse=[];
            try{

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['periodo'] = " - ";
                $datos['tipo_reporte'] = "";

                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('year');
                $id_tipoconceptoaps = $request->query('id_tipoconceptoaps');
                $id_persona = $request->query('id_persona');
                $month = $request->query('month');
                $person_data = null;
                
                $items = ConceptData::getPaidConcepts($request);
                
                if($id_persona && $id_persona!='null' && $id_persona!=null){
                    $person_data = ReporteData::getDatosGenrales($request);
                }

                $list_razon_social = SetupData::enterpriseByIdEntity($id_entidad);
            
                $list_entidad = SetupData::entityDetail($id_entidad);
                $month_object = SetupData::getMonthById($month);
                $month_object_name = '';

                foreach ($month_object as $item) {
                    $month_object_name = $item->nombre;
                }

                $datos['periodo'] = $month_object_name." ".$id_anho;

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
                
                foreach ($list_razon_social as $item) {
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: ".$item->ruc;
                }

                $data['datos'] = $datos;
                $data['person'] = $person_data;
                
                $data['items'] = [];

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $data['items'] = $items;
                $jResponse['data'] = $this->generatePDFPaidConcepts($data);
                $code = "200";

            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }      
        return response()->json($jResponse,$code);           
    }

    public function generatePDFPaidConcepts($data){
        
        $body_table = [];
        $headerTable = array(
            array(
                'colSpan' => 6, 'fillColor' => '#336699', 'color' => 'white',
                'text' => 'DATOS PERSONALES', 'style' => ["tableBody","center"]
            ),
            "","", "","", "",
        );
        
        $description = '';
        $i = 0;
        if (!is_null($data['person'])){
            $body_table[] = $headerTable;
            $description = "Personal: ".$data['person']->nom_persona;
            $body_table[] = array(
                array('text' => 'Apellidos y Nombres', 'style' => ["tableBody", "bold","subheader"]),
                array('text' => $data['person']->nom_persona, 'style' => ["tableBody"]),
                array('text' => 'F. NAc', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->fec_nacimiento, 'style' => ["tableBody"]),
                array('text' => 'Sexo', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->sexo, 'style' => ["tableBody"]),
            );
            $body_table[] = array(
                array('text' => 'Numero Documento', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->num_documento, 'style' => ["tableBody"]),
                array('text' => 'Estado civil', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->estadocivil, 'style' => ["tableBody"]),
                array('text' => 'Telefono', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->num_telefono, 'style' => ["tableBody"]),
            );
            $body_table[] = array(
                array('text' => 'Email', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->correo, 'style' => ["tableBody"]),
                array('text' => 'Nacionalidad', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->nacionalidad, 'style' => ["tableBody"]),
                array('text' => 'Direccion', 'style' => ["tableBody", "bold", "subheader"]),
                array('text' => $data['person']->direccion, 'style' => ["tableBody"]),
            );
        }

        $separation = array(
            array(
                'border' => [false, false, false, false],
                'colSpan' => 6, 'text' => '', 'style' => ["br"]
            ),
            "","", "","", "",
        );

        $headerConcepts = array(
            array(
                'colSpan' => 6, 'fillColor' => '#336699', 'color' => 'white',
                'text' => 'CONCEPTOS', 'style' => ["tableHeader","center"]
            ),
            "","", "","", "",
        );

        $headerTableConcepts = array(
            array('text' => 'Código', 'style' => ["tableBody","center", "subheader"]),
            array('colSpan' => 4, 'text' => 'Concepto', 'style' => ["tableBody", "subheader"]),"","","",
            array('text' => 'Valor', 'style' => ["tableBody","center", "subheader"]),
        );

        $body_table[] = $separation;
        $body_table[] = $headerConcepts;
        $body_table[] = $headerTableConcepts;

        foreach ($data['items'] as $key => $value){
            
            $body_table[] = array(
                array('text' => $value->codigo, 'style' => ["tableBody","center"]),
                array('colSpan' => 4, 'text' => $value->concepto, 'style' => ["tableBody"]),"","","",
                array('text' => number_format($value->valor,2), 'style' => ["tableBody", "right"]),
            );
        }

        $info = array(
            'title' => 'UPN - CONCEPTOS PAGADOS',
            'author' => 'UPN',
        );

        $br = '';
        if($description != ''){
            $br = 'br';
        }
        
        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br","title", 'center']),
            array('text' => 'CONCEPTOS PAGADOS - '.$data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => [$br]),
            array('text' => $description, 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => [$br]),
            array(
                'style' => 'tableExample', 'color' => '#444', 
                'table' => array(
                    'headerRows' => 1,
                    'widths'=> ['15%','25%','10%','15%','10%', '20%'],
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
            'red' => array('color' => 'red'),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [20, 30, 20, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
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
    public function getTypePayroll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getTypePayroll();
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
    public function getTypeContract(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getTypeContract();
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
    public function getTypePlan(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getTypePlan();
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
    public function getConceptPayrollAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getConceptPayrollAccount($request);
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
    public function getRestriccion(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getRestriccion($request);
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

    public function listPayrollConceptAccountTab(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::listPayrollConceptAccountTab($this->request);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
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
                $code = "500";
            }
        }

        return response()->json($jResponse,$code);
    }

    public function getDataToEditPayrollConceptAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getDataToEditPayrollConceptAccount($this->request);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";                    
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function addPayrollConceptAccountTab(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::addPayrollConceptAccountTab($this->request);
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
    
    public  function deletePayrollConceptAccountTab($id_cuentaaasi, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $data = ConceptData::deletePayrollConceptAccountTab($id_cuentaaasi, $this->request); 
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
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function getFinancialStatementPlan(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::getFinancialStatementPlan($this->request);
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


    public function copyFinancialStatement(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::copyFinancialStatement($this->request);
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
    public function addFinancialStatementPlan(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = ConceptData::addFinancialStatementPlan($this->request);
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

    public function editFinancialStatementPlan($id_plan_fichafinanciera, Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                    $ok = ConceptData::editFinancialStatementPlan($id_plan_fichafinanciera, $this->request);
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

    
    public  function deleteFinancialStatementPlan($id_plan_fichafinanciera){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $result=ConceptData::deleteFinancialStatementPlan($id_plan_fichafinanciera);
                $jResponse['success'] = true;
                $jResponse['message'] = $result;                    
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


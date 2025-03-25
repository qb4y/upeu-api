<?php
namespace App\Http\Controllers\HumanTalent;

use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalent\SocialBenefitsData;
use App\Http\Data\SetupData;
use Carbon\Carbon;
use DOMPDF;
use Exception;
use Illuminate\Http\Request;
use Convertidor;
use DateTime;
// for image view
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SocialBenefitsController extends Controller
{
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
    
    public function getDataFormatCtaBancaria(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $listTypeBankAccount = SocialBenefitsData::listTypeBankAccount();
                $listBanks = SocialBenefitsData::listBanks();
                $data=['typeBankAccount'=>$listTypeBankAccount,
                'banks'=>$listBanks];
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getYearPayrollRegistry(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::yearPayrollRegistry($this->request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function ctsTotal(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');

            try {
                $data = SocialBenefitsData::ctsTotal($id_entidad, $id_anho, $id_tramo);
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

    public function ctsSummary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');
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
                $data = SocialBenefitsData::ctsSummary($id_entidad, $id_anho, $id_tramo, $pageSize);
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

    public function ctsCalculation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');
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
                $data = SocialBenefitsData::ctsCalculation($id_entidad, $id_anho, $id_tramo, $pageSize);
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

    public function ctsProvision(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');
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
                $data = SocialBenefitsData::ctsProvision($id_entidad, $id_anho, $id_tramo, $pageSize);
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

    public function getPdfCtsProvision(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];

            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = " - ";

            $id_entidad = $this->request->query('id_entidad');
            $id_anho = $this->request->query('id_anho');
            $id_tramo = $this->request->query('id_tramo');
            $countData = $this->request->query('countData');

            if ($id_tramo == "1") {
                $id_anho_init = $id_anho - 1;
                $datos['periodo'] = "Noviembre ".$id_anho_init." - Abril " . $id_anho;
                $datos['mes_1'] = "Noviembre";
                $datos['mes_2'] = "Diciembre";
                $datos['mes_3'] = "Enero";
                $datos['mes_4'] = "Febrero";
                $datos['mes_5'] = "Marzo";
                $datos['mes_6'] = "Abril";
            } else {
                $datos['periodo'] = "Mayo - Octubre " . $id_anho;
                $datos['mes_1'] = "Mayo";
                $datos['mes_2'] = "Junio";
                $datos['mes_3'] = "Julio";
                $datos['mes_4'] = "Agosto";
                $datos['mes_5'] = "Septiembre";
                $datos['mes_6'] = "Octubre";
            }
            $list_razon_social = SetupData::enterpriseByIdEntity($id_entidad);

            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*") {
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }

            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: " . $item->ruc;
                $datos['logo'] = $item->logo;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = SocialBenefitsData::ctsProvision($id_entidad, $id_anho, $id_tramo,null);

            $data['datos'] = $datos;
            $data['items'] = [];

            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFCtsProvision($data);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function generatePDFCtsProvision($data)
    {

        $body_table = [];
        $headerTable = array(
            array('text' => 'N°', 'style' => ["tableHeader", "center"]),
            array('text' => 'Código', 'style' => ["tableHeader", "center"]),
            array('text' => 'Apellidos y Nombres', 'style' => ["tableHeader"]),
            array('text' => "N° Meses \n Laborados", 'style' => ["tableHeader", "center"]),
            array('text' => "Total\n Gratificación", 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_1'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_2'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_3'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_4'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_5'], 'style' => ["tableHeader", "center"]),
            array('text' => "Total APS", 'style' => ["tableHeader", "center"]),
            array('text' => "Total ASSINET", 'style' => ["tableHeader", "center"]),
            array('text' => "Provision " . $data['datos']['mes_6'], 'style' => ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        foreach ($data['items'] as $key => $value) {
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody", "center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody", "center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => $value->meses, 'style' => ["tableBody", "center"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m1, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m2, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m3, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m4, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m5, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->total_aps, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->assinet, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->prov_restante, 2), 'style' => ["tableBody", "right"]),
            );
        }

        $info = array(
            'title' => 'UPN - PROVISIÓN DE CTS',
            'author' => 'UPN',
        );
        $array_content = [];
        $array_content[] = array('text' => $data['datos']['empresa'], 'style' => ["br", "title", 'center']);
        $array_content[] = array('text' => 'PROVISIÓN DE CTS - ' . $data['datos']['entidad'], 'style' => ['subtitle', 'center']);
        $array_content[] = array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        if($data['datos']['logo'] && $data['datos']['logo']!==null){
            $logo=$this->base64_encode_image($data['datos']['logo']);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths' => ['3%', '6%', '25%', '8%', '8%', '6%', '6%', '6%', '6%', '6%', '7%', '7%', '6%'],
                'body' => $body_table,
            ),
        );

        $content[] = $array_content;
        $styles = array(
            'pageInfoLeft' => array('fontSize' => 9, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 9, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699', 'margin' => [-3, 0, -3, 0]),
            'tableBody' => array('fontSize' => 7, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'logo' => array('alignment' => 'left','margin' => [0, -68, 0, 0])
        );

        $pageMargins = [25, 30, 25, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }


    public function getPdfCtsSummary(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if ($valida == 'SI') {
            $jResponse = [];

            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = " - ";

            $id_entidad = $this->request->query('id_entidad');
            $id_anho = $this->request->query('id_anho');
            $id_tramo = $this->request->query('id_tramo');
            $countData = $this->request->query('countData');

            if ($id_tramo == "1") {
                $datos['periodo'] = "Noviembre " . ($id_anho - 1) . " - Abril " . $id_anho;
            } else {
                $datos['periodo'] = "Mayo - Diciembre " . $id_anho;
            }
            $list_razon_social = SetupData::enterpriseByIdEntity($id_entidad);

            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*") {
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }

            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: " . $item->ruc;
                $datos['logo'] = $item->logo;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = SocialBenefitsData::ctsSummary($id_entidad, $id_anho, $id_tramo,null);

            $data['datos'] = $datos;
            $data['items'] = [];

            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFCTSSummary($data);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function generatePDFCTSSummary($data)
    {

        $body_table = [];
        $headerTable = array(
            array('text' => 'N°', 'style' => ["tableHeader", "center"]),
            array('text' => 'Código', 'style' => ["tableHeader", "center"]),
            array('text' => 'Nombres y Apellidos', 'style' => ["tableHeader"]),
            array('text' => 'Entidad Bancaria', 'style' => ["tableHeader", "center"]),
            array('text' => 'Cuenta Bancaria', 'style' => ["tableHeader", "center"]),
            array('text' => 'CTS depositar', 'style' => ["tableHeader", "center"]),
            array('text' => 'Sueldos', 'style' => ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        $totales = array('total' => 0, 'sueldos' => 0);
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody", "center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody", "center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => $value->entidad_bancaria, 'style' => ["tableBody","center"]),
                array('text' => $value->cta_bancaria, 'style' => ["tableBody","center"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody","right"]),
                array('text' => number_format($value->sueldos, 2), 'style' => ["tableBody","right"])
            );
            $totales['total'] = $totales['total'] + $value->total;
            $totales['sueldos'] = $totales['sueldos'] + $value->sueldos;
        }

        $body_table[] = array(
            array('colSpan' => 5, 'text' => 'TOTAL', 'style' => ["tableBody","center","bold","subheader"]),
            "","","","",
            array('text' => number_format($totales['total'], 2), 'style' => ["tableBody","right","bold","subheader"]),
            array('text' => number_format($totales['sueldos'], 2), 'style' => ["tableBody","right","bold","subheader"])
        );

        $info = array(
            'title' => $data['datos']['entidad'].' - CTS Pagos',
            'author' => $data['datos']['entidad'],
        );
        $array_content = [];
        $array_content[] = array('text' => $data['datos']['empresa'], 'style' => ["br", "title", 'center']);
        $array_content[] = array('text' => 'RESUMEN DE CTS - PERSONAL ' . $data['datos']['entidad'], 'style' => ['subtitle', 'center']);
        $array_content[] = array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        if($data['datos']['logo'] && $data['datos']['logo']!==null){
            $logo=$this->base64_encode_image($data['datos']['logo']);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array(
            'style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 1,
                'widths'=> ['5%','10%','30%','19%','15%','12%','9%'],
                'body' => $body_table
            )
        );

        $content[] = $array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 9, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 9, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 5, 0, 15]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'logo' => array('alignment' => 'left','margin' => [0, -60, 0, 0])
        );

        $pageMargins = [30, 40, 30, 40];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getPdfCtsCalculation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];

            $datos['empresa'] = "---";
            $datos['ruc'] = "RUC : ";
            $datos['periodo'] = " - ";

            $id_entidad = $this->request->query('id_entidad');
            $id_anho = $this->request->query('id_anho');
            $id_tramo = $this->request->query('id_tramo');
            $countData = $this->request->query('countData');

            if ($id_tramo == "1") {
                $datos['periodo'] = "Noviembre " . ($id_anho - 1) . " - Abril " . $id_anho;
            } else {
                $datos['periodo'] = "Mayo - Diciembre " . $id_anho;
            }
            $list_razon_social = SetupData::enterpriseByIdEntity($id_entidad);

            $list_entidad = SetupData::entityDetail($id_entidad);

            foreach ($list_entidad as $item) {
                $datos['entidad'] = $item->nombre;
            }

            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: " . $item->ruc;
                $datos['logo'] = $item->logo;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = SocialBenefitsData::ctsCalculation($id_entidad, $id_anho, $id_tramo,null);

            $data['datos'] = $datos;
            $data['items'] = [];

            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFCTSCalculation($data);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function generatePDFCTSCalculation($data)
    {

        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'N°', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Código', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Apellidos y Nombres', 'style' => ["tableHeader"]),
            array('colSpan' => 2, 'text' => 'Periodo Cálculo', 'style' => ["tableHeader", "center"]),
            '',
            array('rowSpan' => 2, 'text' => 'Meses', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Días', 'style' => ["tableHeader", "center"]),
            array('text' => "Remun.\n Básica", 'style' => ["tableHeader", "center"]),
            array('text' => "Asig Fam\n Prima Inf", 'style' => ["tableHeader", "center"]),
            array('text' => "Remun\n Esp", 'style' => ["tableHeader", "center"]),
            array('text' => "Remun\n Var.", 'style' => ["tableHeader", "center"]),
            array('text' => "VLD\n Prom", 'style' => ["tableHeader", "center"]),
            array('text' => "Bon Esp.\n Volunt.", 'style' => ["tableHeader", "center"]),
            array('text' => "Bon\n Cargo", 'style' => ["tableHeader", "center"]),
            array('text' => 'Comis.', 'style' => ["tableHeader", "center"]),
            array('text' => "Grat.\n Ant.", 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "1/6 \n Grat.", 'style' => ["tableHeader", "center"]),
            array('text' => 'Faltas', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Rem Comp \n CTS", 'style' => ["tableHeader", "center"]),
            array('colSpan' => 3, 'text' => 'Total a depositar CTS', 'style' => ["tableHeader", "center"]),
            '', '',
        );

        $headerSubTable = array(
            '', '', '',
            array('text' => 'Ingreso', 'style' => ["tableHeader", "center"]),
            array('text' => 'Salida', 'style' => ["tableHeader", "center"]),
            '', '',
            array('text' => '1000', 'style' => ["tableHeader", "center"]),
            array('text' => '1122/1121', 'style' => ["tableHeader", "center"]),
            array('text' => '1222', 'style' => ["tableHeader", "center"]),
            array('text' => '1086', 'style' => ["tableHeader", "center"]),
            array('text' => '1145', 'style' => ["tableHeader", "center"]),
            array('text' => '1079', 'style' => ["tableHeader", "center"]),
            array('text' => '1090', 'style' => ["tableHeader", "center"]),
            array('text' => '1151', 'style' => ["tableHeader", "center"]),
            array('text' => '1519', 'style' => ["tableHeader", "center"]),
            '',
            array('text' => '3000', 'style' => ["tableHeader", "center"]),
            '',
            array('text' => 'X Meses', 'style' => ["tableHeader", "center"]),
            array('text' => 'X Dias', 'style' => ["tableHeader", "center"]),
            array('text' => 'Total', 'style' => ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;
        $body_table[] = $headerSubTable;

        $i = 0;
        foreach ($data['items'] as $key => $value) {
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody", "center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody", "center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => date('d/m/Y', strtotime($value->ingreso)), 'style' => ["tableBody", "center"]),
                array('text' => date('d/m/Y', strtotime($value->salida)), 'style' => ["tableBody", "center"]),
                array('text' => $value->meses, 'style' => ["tableBody", "center"]),
                array('text' => $value->dias, 'style' => ["tableBody", "center"]),
                array('text' => number_format($value->basico, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->prima_infantil, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->remun_especie, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->remun_variable, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->viaticos_ld, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->bon_esp_voluntaria, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->bon_cargo, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->comisiones, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->grati, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->d_grati, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->faltas, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->rc_cts, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->x_meses, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->x_dias, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody", "right"]),
            );
        }

        $info = array(
            'title' => 'UPN - DETALLE DE CÁLCULO DE CTS',
            'author' => 'UPN',
        );
        $array_content = [];
        $array_content[] = array('text' => $data['datos']['empresa'], 'style' => ["br", "title", 'center']);
        $array_content[] = array('text' => 'DETALLE DE CÁLCULO DE CTS - PERSONAL ' . $data['datos']['entidad'], 'style' => ['subtitle', 'center']);
        $array_content[] = array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']);
        $array_content[] = array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']);
        if($data['datos']['logo'] && $data['datos']['logo']!==null){
            $logo=$this->base64_encode_image($data['datos']['logo']);
            if($logo){
                $array_content[]=array('image' => $logo,'width'=>60,'height'=>60, 'style' => ["logo"]);
            }
        }
        $array_content[] = array('text' => '', 'style' => ["br"]);
        $array_content[] = array('style' => 'tableExample', 'color' => '#444',
            'table' => array(
                'headerRows' => 2,
                'widths' => ['2%', '4%', '19%', '5%', '5%', '3%', '2%', '4%', '5%', '4%', '4%', '4%', '4%', '4%', '4%', '4%', '4%', '4%', '4%', '4%', '3%', '4%'],
                'body' => $body_table,
            ),
        );

        $content[] = $array_content;

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 6, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 6, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10]),
            'logo' => array('alignment' => 'left','margin' => [0, -65, 0, 0])
        );

        $pageMargins = [15, 30, 15, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }

    public function getConstanciaDepCtsPerson(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        $items = [];
        $totales = [];
        $datos['empresa'] = "";
        $datos['periodo'] = "";
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['direccion'] = " - ";
                $datos['telefono'] = " - ";

                $id_empresa = $this->request->query('id_empresa');
                $id_entidad = $this->request->query('id_entidad');
                $id_persona = $request->query('id_persona');
                $date_start = $request->query('date_start');
                $date_finish = $request->query('date_finish');
                //obtener el segundo viernes para fecha de deposito
                $dates = Carbon::parse(date("Y-m-d", strtotime($date_finish . "+ 1 month")));
                $year = $request->query('id_anho');
                $deposit_date = date("d/m/Y", strtotime('second friday ' . $dates->format('F') . ' ' . $year));
                $deposit_date_t = date("Y-m-d", strtotime('second friday ' . $dates->format('F') . ' ' . $year));
                $datos['date_text_doc'] =  $this->convert_date_text($deposit_date_t);

                $datos['entidad'] = "Todas las entidades";
                if ($id_entidad != "*") {
                    $list_entidad = SetupData::entityDetail($id_entidad);

                    foreach ($list_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
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
                    $datos['documento'] = $item->documento;
                    $datos['telephone'] = $item->telephone;
                }

                $personBankAccount = $data = SocialBenefitsData::getPersonBankAccount($id_persona, $date_start, $date_finish, 4);
                foreach ($personBankAccount as $item) {
                    $datos['nombre_banco'] = $item->name_bank;
                    $datos['cta_bancaria'] = $item->cuenta;
                    $datos['moneda'] = "Soles";
                    $datos['fecha_deposito'] = $deposit_date;
                }

                $items = SocialBenefitsData::getConstanciaDepCtsPerson($id_entidad, $id_persona, $date_start, $date_finish);

                $data['datos'] = $datos;
                $data['person'] = [];

                if ($items) {
                    $data['person'] = $items[0];

                }
            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'constancia_deposito_cts_page', 'human-talent.socialBenefits.constanciaCts', 'proliant');
    }

    public function getidCalCts(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::getIdCalcuCts($this->request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getPersonBankAccount(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];

            $id_persona = $request->query('id_persona');
            $date_start = $request->query('date_start');
            $date_finish = $request->query('date_finish');
            $id_tipoctabanco = $request->query('id_tipoctabanco');

            try {
                $data = SocialBenefitsData::getPersonBankAccount($id_persona, $date_start, $date_finish, $id_tipoctabanco);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function gratificationTotal(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');

            try {
                $data = SocialBenefitsData::gratificationTotal($id_entidad, $id_anho, $id_tramo);
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

    public function gratificacionSummary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');
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
                $data = SocialBenefitsData::gratificacionSummary($id_entidad, $id_anho, $id_tramo, $limit, $offset);
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

    public function gratificationProvision(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');
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
                $data = SocialBenefitsData::gratificationProvision($id_entidad, $id_anho, $id_tramo, $limit, $offset);
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

    public function gratificationCalculation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $id_entidad = $request->query('id_entidad');
            $id_anho = $request->query('id_anho');
            $id_tramo = $request->query('id_tramo');
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
                $data = SocialBenefitsData::gratificationCalculation($id_entidad, $id_anho, $id_tramo, $limit, $offset);
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

    public function getPdfGratificationSummary(Request $request)
    {
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
            $id_anho = $this->request->query('id_anho');
            $id_tramo = $this->request->query('id_tramo');
            $countData = $this->request->query('countData');

            if ($id_tramo == "1") {
                $datos['periodo'] = "Enero - Junio " . $id_anho;
            } else {
                $datos['periodo'] = "Julio - Diciembre " . $id_anho;
            }
            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*") {
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }

            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: " . $item->ruc;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = SocialBenefitsData::gratificacionSummary($id_entidad, $id_anho, $id_tramo, 0, $countData);

            $data['datos'] = $datos;
            $data['items'] = [];

            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFGratificationSummary($data);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function generatePDFGratificationSummary($data)
    {

        $body_table = [];
        $headerTable = array(
            array('text' => 'N°', 'style' => ["tableHeader", "center"]),
            array('text' => 'Código', 'style' => ["tableHeader", "center"]),
            array('text' => 'Nombres y Apellidos', 'style' => ["tableHeader"]),
            array('text' => 'Entidad Bancaria', 'style' => ["tableHeader", "center"]),
            array('text' => 'Cuenta Bancaria', 'style' => ["tableHeader", "center"]),
            array('text' => 'Total Gratificación', 'style' => ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        $totales = array('total' => 0);
        foreach ($data['items'] as $key => $value){
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody", "center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody", "center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => $value->entidad_bancaria, 'style' => ["tableBody", "center"]),
                array('text' => $value->cta_bancaria, 'style' => ["tableBody", "center"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody", "right"]),
            );
            $totales['total'] = $totales['total'] + $value->total;
        }

        $body_table[] = array(
            array('colSpan' => 5, 'text' => 'TOTAL', 'style' => ["tableBody","center","bold","subheader"]),
            "","","","",
            array('text' => number_format($totales['total'], 2), 'style' => ["tableBody","right","bold","subheader"])
        );

        $info = array(
            'title' => 'UPN - Gratificación Pagos',
            'author' => 'UPN',
        );

        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br", "title", 'center']),
            array('text' => 'RESUMEN DE GRATIFICACIÓN - PERSONAL ' . $data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444',
                'table' => array(
                    'headerRows' => 1,
                    'widths' => ['5%', '11%', '34%', '21%', '16%', '13%'],
                    'body' => $body_table,
                ),
            ),
        );

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 9, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 9, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 5, 0, 15]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699'),
            'tableBody' => array('fontSize' => 7, 'color' => 'black'),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'bold' => array('bold' => true),
            'subheader' => array('fillColor' => '#CED4DA'),
            'br' => array('margin' => [0, 5, 0, 10])
        );

        $pageMargins = [30, 40, 30, 40];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'portrait', $pageMargins);
    }

    public function getPdfGratificationProvision(Request $request)
    {
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
            $id_anho = $this->request->query('id_anho');
            $id_tramo = $this->request->query('id_tramo');
            $countData = $this->request->query('countData');

            if ($id_tramo == "1") {
                $datos['periodo'] = "Enero - Junio " . $id_anho;
                $datos['mes_1'] = "Enero";
                $datos['mes_2'] = "Febrero";
                $datos['mes_3'] = "Marzo";
                $datos['mes_4'] = "Abril";
                $datos['mes_5'] = "Mayo";
                $datos['mes_6'] = "Junio";
            } else {
                $datos['periodo'] = "Julio - Diciembre " . $id_anho;
                $datos['mes_1'] = "Julio";
                $datos['mes_2'] = "Agosto";
                $datos['mes_3'] = "Septiembre";
                $datos['mes_4'] = "Octubre";
                $datos['mes_5'] = "Noviembre";
                $datos['mes_6'] = "Diciembre";
            }
            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*") {
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }

            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: " . $item->ruc;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = SocialBenefitsData::gratificationProvision($id_entidad, $id_anho, $id_tramo, 0, $countData);

            $data['datos'] = $datos;
            $data['items'] = [];

            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFGratificationProvision($data);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function generatePDFGratificationProvision($data)
    {

        $body_table = [];
        $headerTable = array(
            array('text' => 'N°', 'style' => ["tableHeader", "center"]),
            array('text' => 'Código', 'style' => ["tableHeader", "center"]),
            array('text' => 'Apellidos y Nombres', 'style' => ["tableHeader"]),
            array('text' => "N° Meses \n Laborados", 'style' => ["tableHeader", "center"]),
            array('text' => "Total\n Gratificación", 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_1'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_2'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_3'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_4'], 'style' => ["tableHeader", "center"]),
            array('text' => $data['datos']['mes_5'], 'style' => ["tableHeader", "center"]),
            array('text' => "Total APS", 'style' => ["tableHeader", "center"]),
            array('text' => "Total ASSINET", 'style' => ["tableHeader", "center"]),
            array('text' => "Provision " . $data['datos']['mes_6'], 'style' => ["tableHeader", "center"]),
        );

        $body_table[] = $headerTable;

        $i = 0;
        foreach ($data['items'] as $key => $value) {
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody", "center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody", "center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => $value->meses, 'style' => ["tableBody", "center"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m1, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m2, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m3, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m4, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->m5, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->total_aps, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->assinet, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->prov_restante, 2), 'style' => ["tableBody", "right"]),
            );
        }

        $info = array(
            'title' => 'UPN - PROVISIÓN DE GRATIFICACIÓN',
            'author' => 'UPN',
        );

        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br", "title", 'center']),
            array('text' => 'PROVISIÓN DE GRATIFICACIÓN - ' . $data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444',
                'table' => array(
                    'headerRows' => 1,
                    'widths' => ['3%', '6%', '25%', '8%', '8%', '6%', '6%', '6%', '6%', '6%', '7%', '7%', '6%'],
                    'body' => $body_table,
                ),
            ),
        );

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 9, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 9, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 8, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699', 'margin' => [-3, 0, -3, 0]),
            'tableBody' => array('fontSize' => 7, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10]),
        );

        $pageMargins = [25, 30, 25, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }

    public function getPdfGratificationCalculation(Request $request)
    {
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
            $id_anho = $this->request->query('id_anho');
            $id_tramo = $this->request->query('id_tramo');
            $countData = $this->request->query('countData');

            if ($id_tramo == "1") {
                $datos['periodo'] = "Enero - Junio " . $id_anho;
            } else {
                $datos['periodo'] = "Julio - Diciembre " . $id_anho;
            }
            $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

            $datos['entidad'] = "Todas las entidades";
            if ($id_entidad != "*") {
                $list_entidad = SetupData::entityDetail($id_entidad);

                foreach ($list_entidad as $item) {
                    $datos['entidad'] = $item->nombre;
                }
            }

            foreach ($list_razon_social as $item) {
                $datos['empresa'] = $item->nombre_legal;
                $datos['ruc'] = "RUC: " . $item->ruc;
            }

            $printer_details = SetupData::printerFooterDetail($id_user);
            foreach ($printer_details as $item) {
                $datos['fechahora'] = $item->fechahora;
                $datos['username'] = $item->username;
            }

            $items = SocialBenefitsData::gratificationCalculation($id_entidad, $id_anho, $id_tramo, 0, $countData);

            $data['datos'] = $datos;
            $data['items'] = [];

            if ($items) {
                $data['items'] = $items;
            }

            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $this->generatePDFGratificationCalculation($data);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function generatePDFGratificationCalculation($data)
    {

        $body_table = [];
        $headerTable = array(
            array('rowSpan' => 2, 'text' => 'N°', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Código', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Apellidos y Nombres', 'style' => ["tableHeader"]),
            array('colSpan' => 2, 'text' => 'Periodo Cálculo', 'style' => ["tableHeader", "center"]),
            '',
            array('rowSpan' => 2, 'text' => 'Meses', 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => 'Días', 'style' => ["tableHeader", "center"]),
            array('text' => "Remun.\n Básica", 'style' => ["tableHeader", "center"]),
            array('text' => "Asig Fam\n Prima Inf", 'style' => ["tableHeader", "center"]),
            array('text' => "Remun\n Esp", 'style' => ["tableHeader", "center"]),
            array('text' => "Remun\n Var.", 'style' => ["tableHeader", "center"]),
            array('text' => "VLD\n Prom", 'style' => ["tableHeader", "center"]),
            array('text' => "Bon Esp.\n Volunt.", 'style' => ["tableHeader", "center"]),
            array('text' => "Bon\n Cargo", 'style' => ["tableHeader", "center"]),
            array('text' => 'Comis.', 'style' => ["tableHeader", "center"]),
            array('colSpan' => 2, 'text' => 'Total a depositar CTS', 'style' => ["tableHeader", "center"]),
            '',
            array('text' => "Bon\n Ext.", 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Total\n Grat.", 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Diezmo\n 10%.", 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "5TA\n Cat.", 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Adel.", 'style' => ["tableHeader", "center"]),
            array('rowSpan' => 2, 'text' => "Grat\n Neta", 'style' => ["tableHeader", "center"]),
        );

        $headerSubTable = array(
            '', '', '',
            array('text' => 'Ingreso', 'style' => ["tableHeader", "center"]),
            array('text' => 'Salida', 'style' => ["tableHeader", "center"]),
            '', '',
            array('text' => '1000', 'style' => ["tableHeader", "center"]),
            array('text' => '1122/1121', 'style' => ["tableHeader", "center"]),
            array('text' => '1222', 'style' => ["tableHeader", "center"]),
            array('text' => '1086', 'style' => ["tableHeader", "center"]),
            array('text' => '1145', 'style' => ["tableHeader", "center"]),
            array('text' => '1079', 'style' => ["tableHeader", "center"]),
            array('text' => '1090', 'style' => ["tableHeader", "center"]),
            array('text' => '1151', 'style' => ["tableHeader", "center"]),
            array('text' => 'X Meses', 'style' => ["tableHeader", "center"]),
            array('text' => 'X Dias', 'style' => ["tableHeader", "center"]),
            array('text' => '9% ESS', 'style' => ["tableHeader", "center"]),
            '', '', '', '', '',
        );

        $body_table[] = $headerTable;
        $body_table[] = $headerSubTable;

        $i = 0;
        foreach ($data['items'] as $key => $value) {
            $i++;
            $body_table[] = array(
                array('text' => $i, 'style' => ["tableBody", "center"]),
                array('text' => $value->num_documento, 'style' => ["tableBody", "center"]),
                array('text' => $value->nom_persona, 'style' => ["tableBody"]),
                array('text' => date('d/m/Y', strtotime($value->ingreso)), 'style' => ["tableBody", "center"]),
                array('text' => date('d/m/Y', strtotime($value->salida)), 'style' => ["tableBody", "center"]),
                array('text' => $value->meses, 'style' => ["tableBody", "center"]),
                array('text' => $value->dias, 'style' => ["tableBody", "center"]),
                array('text' => number_format($value->basico, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->prima_infantil, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->remun_especie, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->remun_variable, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->viaticos_ld, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->bon_esp_voluntaria, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->bon_cargo, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->comisiones, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->x_meses, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->x_dias, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->b_essalud, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->total, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->diezmo, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->quinta, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->adelantos, 2), 'style' => ["tableBody", "right"]),
                array('text' => number_format($value->grat_neta, 2), 'style' => ["tableBody", "right"]),
            );
        }

        $info = array(
            'title' => 'UPN - PLANILLA DE GRATIFICACIÓN',
            'author' => 'UPN',
        );

        $content = array(
            array('text' => $data['datos']['empresa'], 'style' => ["br", "title", 'center']),
            array('text' => 'PLANILLA DE GRATIFICACIÓN ' . $data['datos']['entidad'], 'style' => ['subtitle', 'center']),
            array('text' => $data['datos']['ruc'], 'style' => ["subtitle", 'center']),
            array('text' => $data['datos']['periodo'], 'style' => ["subtitle", 'center']),
            array('text' => '', 'style' => ["br"]),
            array(
                'style' => 'tableExample', 'color' => '#444',
                'table' => array(
                    'headerRows' => 2,
                    'widths' => ['2%', '4%', '17%', '5%', '5%', '2%', '2%', '4%', '5%', '4%', '4%', '4%', '4%', '4%', '4%', '4%', '3%', '4%', '4%', '4%', '3%', '4%', '4%'],
                    'body' => $body_table,
                ),
            ),
        );

        $styles = array(
            'pageInfoLeft' => array('fontSize' => 7, 'alignment' => 'center'),
            'pageInfoRight' => array('fontSize' => 7, 'alignment' => 'right'),
            'title' => array('fontSize' => 11, 'bold' => true),
            'subtitle' => array('fontSize' => 10, 'bold' => true),
            'tableExample' => array('margin' => [0, 0, 0, 0]),
            'tableHeader' => array('fontSize' => 6, 'bold' => 'true', 'color' => 'white', 'fillColor' => '#336699', 'margin' => [-3, 0, -3, 0]),
            'tableBody' => array('fontSize' => 6, 'color' => 'black', 'margin' => [-2, 0, -2, 0]),
            'center' => array('alignment' => 'center'),
            'right' => array('alignment' => 'right'),
            'br' => array('margin' => [0, 5, 0, 10]),
        );

        $pageMargins = [15, 30, 15, 30];

        return $this->formatPDFJSON($info, $content, $styles, 'A4', 'landscape', $pageMargins);
    }

    public function formatPDFJSON($info = null, $content, $styles, $pageSize, $pageOrientation = 'portrait', $pageMargins)
    {
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
        //print_r($p_data);

        $pdf = DOMPDF::loadView('pdf.' . $nameView, array('data' => $data))->setPaper('A4', $orientation);
        return $pdf->stream($namePdf . '.pdf');
    }

    public function getPdfCartaCtsLiquidation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        $datos['empresa'] = "";
        $response=[];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $entity = $request->query('id_entidad');
                $person = $request->query('id_persona');
                $fec_termino = $request->query('fec_cese');
                $fec_pago = $request->query('fec_pago');
                $id_tipo_cese = $request->query('id_tipo_cese');
                $id_empresa = $this->request->query('id_empresa');

                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $fecha = Carbon::parse($fec_termino);
                $mes = $meses[($fecha->format('n')) - 1];
                $datos['date_description'] = $fecha->format('d') . ' de ' . $mes . ' del ' . $fecha->format('Y');

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['direccion'] = " - ";
                $datos['telefono'] = " - ";

                $datos['entidad'] = "Todas las entidades";
                if ($entity != "*") {
                    $list_entidad = SetupData::entityDetail($entity);

                    foreach ($list_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
                    }

                }

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

                foreach ($list_razon_social as $item) {
                    $datos['id_empresa'] = $id_empresa;
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: " . $item->ruc;

                }

                $personBankAccount = $data = SocialBenefitsData::getPersonBankAccount($person, $fec_pago, $fec_termino, 4);
                foreach ($personBankAccount as $item) {
                    $datos['p_nombre'] = $item->nombre." ".$item->paterno." ".$item->materno;
                    if($item->id_tipodocumento == 1){
                        $datos['num_documento'] =  "DNI Nº ".$item->num_documento;
                    } elseif ($item->id_tipodocumento == 4) {
                        $datos['num_documento'] =  "CarEx Nº ".$item->num_documento;
                    } elseif ($item->id_tipodocumento == 7) {
                        $datos['num_documento'] =  "Pass Nº ".$item->num_documento;
                    }
                    $datos['sexo'] = $item->sexo;
                    $datos['nombre_banco'] = $item->name_bank;
                    $datos['cta_bancaria'] = $item->cuenta;
                    $datos['moneda'] = "Soles";
                    $datos['fecha_deposito'] = $fec_pago;
                }
                
                $list_doc_representative = SetupData::DocRepresentativeFilters($this->request);
                foreach ($list_doc_representative as $item) {
                    $datos['representante'] = $item->representante;
                    $datos['direccion_legal'] = $item->direccion_legal;
                    $datos['nom_entidad'] = $item->nam_entidad;
                    $datos['nom_ciudad'] = $item->nom_ciudad;
                    $datos['documento'] = $item->documento;
                }

          
                $data['message'] ='';
                if (count($list_doc_representative) <= 0) {
                    $data['message'] = "No se ha encontrado un representante legal.";
                } else {
                    if (count($personBankAccount) <= 0 or is_null($datos['nombre_banco'])) {
                        $data['message'] = "No cuenta con una cuenta bancaria.";
                    }
                }
                $data['datos'] = $datos;

            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'carta_cts', 'human-talent.socialBenefits.cartaCts', 'proliand');
    }

    public function getPdfJobCertificateLiquidation(Request $request)
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

                $entity = $request->query('id_entidad');
                $person = $request->query('id_persona');
                $fec_termino = $request->query('fec_cese');
                $fec_pago = $request->query('fec_pago');
                $id_tipo_cese = $request->query('id_tipo_cese');
                $id_empresa = $this->request->query('id_empresa');
                $cargo = $request->query('position');
                $fe_inicio = $request->query('fe_inicio');

                $datos['date_description'] =  $this->convert_date_text($fec_termino);
                $datos['date_text_ini'] =  $this->convert_date_text($fe_inicio);
                $datos['total_elapsed_time'] = $this->tiempoTranscurridoFechas($fe_inicio, $fec_termino);
                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['direccion'] = " - ";
                $datos['telefono'] = " - ";

                $datos['entidad'] = "Todas las entidades";
                if ($entity != "*") {
                    $list_entidad = SetupData::entityDetail($entity);

                    foreach ($list_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
                    }

                }

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

                foreach ($list_razon_social as $item) {
                    $datos['id_empresa'] = $id_empresa;
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: " . $item->ruc;

                }
                
                $personData = SocialBenefitsData::getNaturalPersonforId($person);
                foreach ($personData as $item) {
                    $datos['p_nombre'] = $item->nombre." ".$item->paterno." ".$item->materno;
                    if($item->id_tipodocumento == 1){
                        $datos['num_documento'] =  "DNI Nº ".$item->num_documento;
                    } elseif ($item->id_tipodocumento == 4) {
                        $datos['num_documento'] =  "CarEx Nº ".$item->num_documento;
                    } elseif ($item->id_tipodocumento == 7) {
                        $datos['num_documento'] =  "Pass Nº ".$item->num_documento;
                    }
                    $datos['sexo'] = $item->sexo;
                    $datos['cargo'] = $cargo;
                }
                
                $list_doc_representative = SetupData::DocRepresentativeFilters($this->request);
                foreach ($list_doc_representative as $item) {
                    $datos['representante'] = $item->representante;
                    $datos['direccion_legal'] = $item->direccion_legal;
                    $datos['nom_entidad'] = $item->nam_entidad;
                    $datos['nom_ciudad'] = $item->nom_ciudad;
                    $datos['documento'] = $item->documento;
                    $datos['telefono'] = $item->telephone;
                }

                $data['datos'] = $datos;

            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'job_certificate', 'human-talent.socialBenefits.certificadoTrabajo', 'proliand');
    }

    public function importPersonBankAccount(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::importPersonBankAccount($this->request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }

        }
        return response()->json($jResponse, $code);
    }

    public function listBank(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::listBank($this->request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function adUpPersonBankAccount(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::addUpPersonBankAccount($this->request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }

        }
        return response()->json($jResponse, $code);
    }

    public function getPdfAffidavitLiquidation()
    {
        $data = [];
        $list_entidad = SetupData::entityDetail('17112');

        foreach ($list_entidad as $item) {
            $data['entidad'] = $item->nombre;
        }
        return $this->generatePdf($data, 'affidavit_liquidation', 'human-talent.socialBenefits.liquidacion', 'proliand');
    }

    public function getPdfHolidayRecord(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $data = [];
        $datos=[];
        $datos['empresa'] = "";
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $entity = $request->query('id_entidad');
                $person = $request->query('id_persona');
                $fec_termino = $request->query('fec_cese');
                $fec_pago = $request->query('fec_pago');
                $id_tipo_cese = $request->query('id_tipo_cese');
                $fec_vac = $request->query('fec_vac');
                $id_empresa = $this->request->query('id_empresa');
                $cargo = $request->query('position');

                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $fecha = Carbon::parse($fec_termino);
                $mes = $meses[($fecha->format('n')) - 1];
                $datos['date_description'] = $fecha->format('d') . ' de ' . $mes . ' del ' . $fecha->format('Y');
                $datos['anho']=$fecha->format('Y');

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['direccion'] = " - ";
                $datos['telefono'] = " - ";
                $datos['id_entidad'] = $entity;
                $datos['entidad'] = "Todas las entidades";
                if ($entity != "*") {
                    $list_entidad = SetupData::entityDetailView($entity);

                    foreach ($list_entidad as $item) {
                        $datos['entidad'] = $item->nom_entidad;
                        $id_empresa = $item->id_empresa;
                    }

                }

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

                foreach ($list_razon_social as $item) {
                    $datos['id_empresa'] = $id_empresa;
                    $datos['empresa'] = $item->nombre_legal;
                    $datos['ruc'] = "RUC: " . $item->ruc;

                }
                $personData = SocialBenefitsData::getNaturalPersonforId($person);
                foreach ($personData as $item) {
                    $datos['p_nombre'] = $item->nombre." ".$item->paterno." ".$item->materno;
                    if($item->id_tipodocumento == 1){
                        $datos['num_documento'] =  "DNI Nº ".$item->num_documento;
                    } elseif ($item->id_tipodocumento == 4) {
                        $datos['num_documento'] =  "CarEx Nº ".$item->num_documento;
                    } elseif ($item->id_tipodocumento == 7) {
                        $datos['num_documento'] =  "Pass Nº ".$item->num_documento;
                    }
                    $datos['sexo'] = $item->sexo;
                }
                $list_doc_representative = SetupData::DocRepresentativeFilters($this->request);
                foreach ($list_doc_representative as $item) {
                    $datos['representante'] = $item->representante;
                    $datos['direccion_legal'] = $item->direccion_legal;
                    $datos['nom_entidad'] = $item->nam_entidad;
                    $datos['nom_ciudad'] = $item->nom_ciudad;
                    $datos['documento'] = $item->documento;
                }
                $datos['cargo']='';
                $result = SocialBenefitsData::getPdfHolidayRecord($entity, $person, $fec_termino, $fec_pago, $id_tipo_cese,$fec_vac);
                #print_r($datos);
                $data_result=[];
                $count=0;
                foreach ($result as $item) {
                    $count++;
                    if($count===1){
                        $datos['cargo'] = $item->cargo;
                        $data_result=$item;
                    }
                }
                $data['datos'] = $datos;
                $data['data'] = $data_result;

            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'holiday_record', 'human-talent.socialBenefits.constanciaVacaciones', 'proliand');
    }

    public function getliquidationCalculation(Request $request)
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

                $entity = $request->query('id_entidad');
                $person = $request->query('id_persona');
                $fec_termino = $request->query('fec_cese');
                $fec_pago = $request->query('fec_pago');
                $id_sistemapension = $request->query('id_sistemapension');
                $id_tipo_cese = $request->query('id_tipo_cese');
                $id_empresa = $this->request->query('id_empresa');
                $fec_vac = $this->request->query('fec_vac');
                $cant_vac_pend = $this->request->query('cant_vac_pend');
                $cant_cal_grat_trunc = $this->request->query('cant_cal_grat_trunc');

                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $fecha = Carbon::parse($fec_termino);
                $mes = $meses[($fecha->format('n')) - 1];
                $datos['date_description'] = $fecha->format('d') . ' de ' . $mes . ' del ' . $fecha->format('Y');
                

                $datos['empresa'] = "---";
                $datos['ruc'] = "RUC : ";
                $datos['direccion'] = " - ";
                $datos['telefono'] = " - ";

                $datos['entidad'] = "Todas las entidades";
                if ($entity != "*") {
                    $list_entidad = SetupData::entityDetail($entity);

                    foreach ($list_entidad as $item) {
                        $datos['entidad'] = $item->nombre;
                        $id_empresa = $item->id_empresa;
                    }

                }

                $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

                // foreach ($list_razon_social as $item) {
                    $datos['id_empresa'] = $id_empresa;
                    $datos['empresa'] = $list_razon_social->nombre_legal;
                    $datos['logo'] = $list_razon_social->logo;
                    $datos['ruc'] = "RUC: " . $list_razon_social->ruc;

                // }

                $list_doc_representative = SetupData::DocRepresentativeFilters($this->request);
                foreach ($list_doc_representative as $item) {
                    $datos['representante'] = $item->representante;
                    $datos['direccion_legal'] = $item->direccion_legal;
                    $datos['nom_entidad'] = $item->nam_entidad;
                    $datos['nom_ciudad'] = $item->nom_ciudad;
                    $datos['documento'] = $item->documento;
                }

                $items = SocialBenefitsData::liquidationCalculation($entity, $person, $fec_termino, $fec_pago, $id_tipo_cese,$id_sistemapension,$fec_vac,$cant_vac_pend,$cant_cal_grat_trunc);
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    $data['items'] = $items[0];
                }

            } catch (Exception $e) {
            }
        }
        end:
        return $this->generatePdf($data, 'affidavit_liquidation', 'human-talent.socialBenefits.liquidacion', 'proliand');
    }

    public function dataPersonLiquidation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $entity = $request->query('id_entidad');
                $person = $request->query('id_persona');
                $fec_termino = $request->query('fec_cese');
                if ($fec_termino == '' || $fec_termino == null) {
                    $fec_termino = date("Y-m-d");
                }
                $data = SocialBenefitsData::dataPersonLiquidation($entity, $person, $fec_termino);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listCessationType(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::listCessationType();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listPensionSystem(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::listPensionSystem();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function importDirectory(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = SocialBenefitsData::importDirectory($this->request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }

        }
        return response()->json($jResponse, $code);
    }

    protected function convert_date_text($date){
        $text = '';
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $fecha = Carbon::parse($date);
        $mes = $meses[($fecha->format('n')) - 1];
        $text = $fecha->format('d') . ' de ' . $mes . ' del ' . $fecha->format('Y');
        return $text;
    }



function tiempoTranscurridoFechas($fechaInicio,$fechaFin)
{
    $fecha1 = new DateTime($fechaInicio);
    $fecha2 = new DateTime($fechaFin);
    $fecha = $fecha1->diff($fecha2);
    $tiempo = "";
         
    //años
    if($fecha->y > 0)
    {
        $tiempo .= $fecha->y;
             
        if($fecha->y == 1)
            $tiempo .= " año";
        else
            $tiempo .= " años";
        
        if ($fecha->m > 0 AND $fecha->d > 0) {
            $tiempo .= ", ";
        } else {
            $tiempo .= "y ";
        }
    }
         
    //meses
    if($fecha->m > 0)
    {
        
        $tiempo .= $fecha->m;
             
        if($fecha->m == 1)
            $tiempo .= " mes ";
        else
            $tiempo .= " meses ";

        if ($fecha->d > 0) {
            $tiempo .= "y ";
        } else {
            $tiempo .= ", ";
        }
    }
         
    //dias
    if($fecha->d > 0)
    {
        $tiempo .= $fecha->d;
             
        if($fecha->d == 1)
            $tiempo .= " día, ";
        else
            $tiempo .= " días, ";
    }
         
  
    return $tiempo;
}

}

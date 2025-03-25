<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalent\PayrollData;
use App\Http\Data\SetupData;
use App\Http\Data\Report\ManagementData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Convertidor;
use DateTime;
use DOMPDF;
use PDO;
use PDF;

class PayrollController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function legalPayroll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $data=[];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = PayrollData::legalPayroll($request);
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
    public function legalPayrollExcel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        $data=[];
        if($valida=='SI'){
            $jResponse=[];
            try{

                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $id_anho = $this->request->query('year');
                $mes_inicio = $this->request->query('month_init');
                $mes_final = $this->request->query('month_finally');
                $datos['id_entidad'] = $id_entidad;
                $datos['entidad']=(object) ['materno' => 'Todas las entidades'];
                if($id_entidad && $id_entidad !=='*'){
                    $datos['entidad']=ManagementData::getEntityById($id_entidad);
                    $d_entidad = SetupData::entityDetail($id_entidad);
                    foreach ($d_entidad as $item) {
                        $id_empresa = $item->id_empresa;
                    }
                }

                $datos['periodo'] = "Planilla del ".$id_anho;

                if ($mes_inicio != "null" AND $mes_inicio != "*" AND $mes_final != "null" AND $mes_final != "*" and $mes_inicio == $mes_final){
                    $c_date = $id_anho.'-'.$mes_inicio.'-01 23:59:00';
                    $fecha = new DateTime($c_date);
                    // $fecha->modify('last day of this month');
                    $mes = $meses[($fecha->format('n')) - 1];
                    $datos['periodo'] = "Planilla Legal ".$mes." ".$id_anho;

                } else if($mes_inicio != "null" AND $mes_inicio != "*" AND $mes_final != "null" AND $mes_final != "*" and $mes_inicio != $mes_final) {
                    $c_date = $id_anho.'-'.$mes_inicio.'-01 23:59:00';
                    $c_date_init = $id_anho.'-'.$mes_inicio.'-01 23:59:00';
                    $c_date_finally = $id_anho.'-'.$mes_final.'-01 23:59:00';
                    $fecha_init = new DateTime($c_date);
                    $fecha_finally = new DateTime($c_date);
                    // $fecha_init->modify('last day of this month');
                    // $fecha->modify('last day of this month');
                    $mes_init = $meses[($fecha_init->format('n')) - 1];
                    $mes_finally = $meses[($fecha_finally->format('n')) - 1];
                    $datos['periodo'] = "Planilla Legal ".$mes_init." - ".$mes_finally."".$id_anho;

                }

                $datos['empresa_user']=SetupData::companyByUser($id_user);
                $datos['empresa']=ManagementData::getCompanyById($id_empresa);
                $items = PayrollData::legalPayroll($request);
                
                $data['datos'] = $datos;
                $data['items'] = [];
                if ($items) {
                    // print_r($items['items']);
                    $data['items'] = $items;
                }


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
}
<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\FinancialStatementData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;

class FinancialStatementController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }

    public function getSummaryAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FinancialStatementData::summaryAccounts($request);
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
    public function getSummaryAccountsPdf(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $data=[];
        $meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_entidad = $request->query('id_entidad');
                $year = $request->query('year');
                $acumulate = $request->query('acumulate');
                $month_init = $request->query('month_init');
                $month = $request->query('month');
                $fondo = $request->query('fondo');
                $getEntity=FinancialStatementData::getEntity($id_entidad);
                $data['items'] = FinancialStatementData::summaryAccounts($request);
                $data['datos']['entidad']=$id_entidad.' - '.$getEntity->nombre;
                if($acumulate && $acumulate=='1'){
                    $data['datos']['month']=$meses[$month_init-1].' a '.$meses[$month-1].' de '.$year;
                }else{
                    $data['datos']['month']=$meses[$month-1].' de '.$year;
                }
                if($fondo && $fondo!='0'){
                    $data['datos']['fondo']=$fondo.' - '.$request->query('fondo_nombre');
                }else{
                    $data['datos']['fondo']='Todos los Fondos';
                }
            }catch(Exception $e){  
            }
        }    
        end:
        $pdf = DOMPDF::loadView('pdf.human-talent.summaryAccounts', compact('data'))->setPaper('A4', 'landscape');
        return $pdf->stream('summary_accounts' . '.pdf');
        }

    public function getFondos(Request $request){
            $jResponse = GlobalMethods::authorizationLamb($this->request);
            $code       = $jResponse["code"];
            $valida     = $jResponse["valida"];
            $id_entidad = $jResponse["id_entidad"];
    
            if($valida=='SI'){
                $jResponse=[];
                try{
                    $data = FinancialStatementData::getFondos();
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
    public function getPersonalFinacialAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $data=[];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = FinancialStatementData::personalFinacialAccounts($request);
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
}


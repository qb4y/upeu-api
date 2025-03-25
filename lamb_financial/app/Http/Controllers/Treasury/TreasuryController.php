<?php
namespace App\Http\Controllers\Treasury;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Treasury\TreasuryData;
use App\Http\Data\Treasury\ExpensesData;
use App\Http\Data\Accounting\Setup\AccountingData;
//use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use DOMPDF;

class TreasuryController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }

    
    public function listMyBankAccountsX(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];  
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $data = TreasuryData::listBankAccounts($id_entidad,$id_depto);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function listBankAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $id_entidad   = $request->query('id_entidad');
                $id_depto   = $request->query('id_depto');
                $data = TreasuryData::listBankAccounts($id_entidad,$id_depto);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function showBankAccounts($id_ctabancaria){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $data = TreasuryData::showBankAccounts($id_ctabancaria);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data[0];
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
    public function addBankAccounts(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_entidad = $params->id_entidad;
            $id_depto = $params->id_depto;
            $id_banco = $params->id_banco;
            $id_moneda = $params->id_moneda;
            $id_cuentaempresarial = $params->id_cuentaempresarial;
            $id_tipoplan = $params->id_tipoplan;
            $id_cuentaaasi = $params->id_cuentaaasi;
            $id_restriccion = $params->id_restriccion;
            $id_ctacte = $params->id_ctacte;
            $nombre = $params->nombre;
            $cuenta_corriente = $params->cuenta_corriente;
            $id_depto_oper = $params->id_depto_oper;
            $estado = $params->estado;
            try{
                $data = TreasuryData::addBankAccounts($id_entidad,$id_depto,
                $id_banco,$id_moneda,$id_cuentaempresarial,
                $id_tipoplan,$id_cuentaaasi,$id_restriccion,
                $id_ctacte,$nombre,$cuenta_corriente,
                $id_depto_oper, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
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
    public function updateBankAccounts($id_ctabancaria){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_banco = $params->id_banco;
            $id_moneda = $params->id_moneda;
            $id_cuentaempresarial = $params->id_cuentaempresarial;
            $id_cuentaaasi = $params->id_cuentaaasi;
            $id_tipoctacte = $params->id_tipoctacte;
            $nombre = $params->nombre;
            $cuenta_corriente = $params->cuenta_corriente;
            $id_depto_oper = $params->id_depto_oper;
            $estado = $params->estado;
            try{
                $data = TreasuryData::updateBankAccounts($id_ctabancaria,$id_banco,
                $id_moneda,$id_cuentaempresarial,$id_cuentaaasi,$nombre,
                $cuenta_corriente, $id_tipoctacte,$id_depto_oper,$estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteBankAccounts($id_ctabancaria){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = TreasuryData::deleteBankAccounts($id_ctabancaria);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
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
    public function listCheckbooks(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{
                $id_ctabancaria   = $request->query('id_ctabancaria');
                $id_anho   = $request->query('id_anho');
                $id_mes   = $request->query('id_mes');
                $data = TreasuryData::listCheckbooks($id_ctabancaria,$id_anho,$id_mes);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function showCheckbooks($id_chequera){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $data = TreasuryData::showCheckbooks($id_chequera);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function addCheckbooks(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_ctabancaria = $params->id_ctabancaria;
            $id_anho = $params->id_anho;
            $id_mes = $params->id_mes;
            $id_voucher = $params->id_voucher;
            $numero = $params->numero;
            $importe = $params->importe;
            $fecha = $params->fecha;
            $detalle = $params->detalle;
            $beneficiario = $params->beneficiario;
            try{

                $existeUnChequeIgual = TreasuryData::getChequeExiste($id_ctabancaria, $numero);
                if($existeUnChequeIgual) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Ya existe un cheque con el número $numero, en la cuenta bancaria.";
                    $jResponse['data'] = null;
                    $code = "202";
                   goto end;
                }

                $data = TreasuryData::addCheckbooks($id_ctabancaria,$id_anho,$id_mes,$id_voucher,$numero,$importe,$fecha,$detalle,$beneficiario);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updateCheckbooks($id_chequera){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_ctabancaria = $params->id_ctabancaria;
            $id_voucher = $params->id_voucher;
            $numero = $params->numero;
            $importe = $params->importe;
            $fecha = $params->fecha;
            $detalle = $params->detalle;
            $beneficiario = $params->beneficiario;
            try{
                $data = TreasuryData::updateCheckbooks($id_chequera,$id_ctabancaria,$id_voucher,$numero,$importe,$fecha,$detalle,$beneficiario);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function deleteCheckbooks($id_chequera){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];             
        if($valida=='SI'){
            $jResponse=[];
            try{
                //Verificar si un user se ha asignado
                $data = TreasuryData::showCheckbooksUserAsigned($id_chequera);
                if(count($data)>0){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Un Usuario se ha Asignado la Chequera  ";
                    $jResponse['data'] = $data;
                    $code = "202";
                }else{
                    // TreasuryData::deleteCheckbooksPersona($id_chequera);
                    TreasuryData::deleteCheckbooksVouchers($id_chequera);
                    $data = TreasuryData::deleteCheckbooks($id_chequera);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
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
    public function listMyBankAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $id_voucher   = $request->query('id_voucher');
                $id_banco   = $request->query('id_banco');
                if ($id_voucher) {
                    $data = TreasuryData::listMyBankAccounts($id_voucher);
                }
                if(empty($id_voucher) and empty($id_banco)){
                    $data = TreasuryData::listBankAccounts($id_entidad,$id_depto);
                }
                if ($id_banco) {
                    $data = TreasuryData::listBankAccountsBanco($id_banco, $id_entidad, $id_depto);
                }
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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

    public function showAccountsByBank(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_banco   = $request->query('id_banco');
                $data = TreasuryData::showAccountsByBank($id_entidad, $id_depto, $id_banco);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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

    public function listTypeTarjetas(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $data = TreasuryData::listTypeTarjetas();
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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
    public function listMyCheckbooks(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];            
            try{     
                $id_voucher   = $request->query('id_voucher');
                $data = TreasuryData::listMyCheckbooks($id_voucher);                
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = $data;
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


    public function detractionsPermissions(Request $request){

        $response = GlobalMethods::authorizationLamb($this->request);
        $code   = $response["code"];
        $valida = $response["valida"];
        $id_user = $response['id_user'];
        $id_entidad = $response['id_entidad'];
        if($valida=='SI'){
            $code = 200;
            $response=[];
            $response['data'] = TreasuryData::actionsByModuleUser(
                $id_user,
                $id_entidad,
                'expenses/deductions');

        }

        return response()->json($response,$code);
    }


    public function myPaymentsPdf(Request $request){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $username   =  $jResponse["email"];


        if($valida == 'SI'){
            $mensaje = '';
            $jResponse = [];

            try {

                $id_voucher = $request->query('id_voucher');

                $id_mes = $request->query('id_mes');
                $id_tipovoucher = $request->query('id_tipovoucher');

                $fecha = $request->query('fecha');
                $numero = $request->query('numero');
                $lote = $request->query('lote');
                // dd($id_mes, $id_tipovoucher);
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                }
                $voucherData = AccountingData::listVoucherModulesAllShow($id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher, $id_voucher);
                $payments = ExpensesData::myPayments($id_voucher);
                $subtotal = ExpensesData::myPaymentsCash($id_voucher);
                $total = ExpensesData::myPaymentsTotal($id_voucher);
                $detractions = ExpensesData::Detractions($id_entidad, $id_voucher);
                $retentions = TreasuryData::Retentions($id_entidad, $id_voucher);
                // dd($voucherData);
                $pdf = DOMPDF::loadView('pdf.treasury.reports.myPayments',[
                    // 'voucherDatas'=>$voucherData[0],
                    'payments'=>$payments,
                    'subtotal'=>$subtotal,
                    'total'=>$total,
                    'detractions'=>$detractions,
                    'retentions'=>$retentions,

                    'fecha'=>$fecha,
                    'numero'=>$numero,
                    'lote'=>$lote,

                    'username'=>$username
                ])->setPaper('a4', 'portrait');

                $data =  base64_encode($pdf->stream('print.pdf'));

                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items'=>$data]
                ];

                return response()->json($jResponse);

            } catch(Exception $e) {

                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();

            }
        } else {
            $mensaje=$jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.treasury.reports.myPayments',['mensaje'=>$mensaje])->setPaper('a4', 'portrait');
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items'=>$doc]
        ];

        return response()->json($jResponse);

    }

    public function certificado(Request $request ){
        $num_op = $request["num_operacion"];
        
        $query = "select   fc_nombre_persona(id_cliente) as cliente from caja_deposito
        where nro_operacion =   '".$num_op."' ";
        $oQuery = DB::select($query); 
  
            $nombre_cliente=$oQuery['0'];
            $nombre_cliente = $nombre_cliente->cliente;
     
        $pdf = DOMPDF::loadView('pdf.treasury.donaciones.certificado',['nombre'=>$nombre_cliente] )->setPaper('a4', 'landscape');
         
        

        return $pdf->download('CERTIFICADO.pdf');

    }



    public function Retentions(Request $request){

        $response = GlobalMethods::authorizationLamb($this->request);
        $code   = $response["code"];
        $valida = $response["valida"];        
        $id_entidad = $response['id_entidad'];
        $id_voucher = $request->query('id_voucher');
        if($valida=='SI'){
            $code = 200;
            $response=[];
            $response['success']=true;
            $query = TreasuryData::Retentions($id_entidad, $id_voucher);
            $response['data'] = ['items'=>$query];

        }

        return response()->json($response,$code);
    }

    public function retentionsPermissions(Request $request){

        $response = GlobalMethods::authorizationLamb($this->request);
        $code   = $response["code"];
        $valida = $response["valida"];
        $id_user = $response['id_user'];
        $id_entidad = $response['id_entidad'];

        if($valida=='SI'){
            $code = 200;
            $response=[];
            $response['data'] = TreasuryData::actionsByModuleUser(
                $id_user,
                $id_entidad,
                'expenses/retentions');
        }

        return response()->json($response,$code);

    }


    public function paymentsPermissions(Request $request){

        $response = GlobalMethods::authorizationLamb($this->request);
        $code   = $response["code"];
        $valida = $response["valida"];
        $id_user = $response['id_user'];
        $id_entidad = $response['id_entidad'];
        if($valida=='SI'){
            $code = 200;
            $response=[];
            $response['data'] = TreasuryData::actionsByModuleUser(
                $id_user,
                $id_entidad,
                'expenses/payments');

        }

        return response()->json($response,$code);
    }


    public function listColectionControl(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = TreasuryData::listColectionControl($request, $id_entidad, $id_depto);
          
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $query  = collect($data)->groupBy('facultad');
                    $datar = array();
                    foreach ($query as $key => $value) {
                        array_push($datar, ['facultad' => $key, 'data' => $value]);
                    }
                    $jResponse['data'] = $datar;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se encontraron resultados";
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listColectionControlDetalle(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = TreasuryData::listColectionControlDetalle($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se encontraron resultados";
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


    public  function generarDatosGrafico(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        // dd('hola', $request);
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
               
                $return  =  TreasuryData::generarDatosGrafico($request, $id_user, $id_entidad, $id_depto); 
                // dd($return, 'ssss');
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
    public function graficoColectionControl(Request $request) {
        // dd('dddd');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = TreasuryData::graficoColectionControl($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data ;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function graficoColectionControlDetalle(Request $request) {
        // dd('dddd');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = TreasuryData::graficoColectionControlDetalle($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $query  = collect($data)->groupBy('dia');
                    $datar = array();
                    foreach ($query as $key => $value) {
                        array_push($datar, ['dia' => $key, 'data' => $value]);
                    }
                    $jResponse['data'] = ['data' => $datar];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
}
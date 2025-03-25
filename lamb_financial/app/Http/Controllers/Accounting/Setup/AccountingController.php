<?php
namespace App\Http\Controllers\Accounting\Setup;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Data\Treasury\ExpensesData;
use App\Http\Data\TreasuryData;
use App\Http\Requests\AccountingVoucherRequest;
use App\Http\Requests\ExchangeRateRequest;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Inventories\WarehousesData;
use App\Http\Controllers\Report\Accounting\ReportLegalController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Report\AccountingLegalData;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Http\Data\Sales\SalesData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PDO;
use phpDocumentor\Reflection\Types\Array_;
use PhpParser\Node\Stmt\TryCatch;
use SoapClient;
class AccountingController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listConfigVoucher(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $entity = $request->query('entity');
                // $depto  = $request->query('depto');
                $depto  = $request->get('depto', $request->get('departament', 0));
                $year   = $request->query('year');
                $opc = "";
                if($depto != 0){
                    $opc = "AND A.ID_DEPTO = ".$depto;
                }
                try{
                    $data = AccountingData::listConfigVoucher($entity,$year,$opc);
                    if(count($data)>0){
                        $jResponse['message'] = "Success";
                        $jResponse['data'] = ['items' => $data];
                        $error = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $error = "204";
                    }
                    $jResponse['success'] = true;
                }catch(Exception $e){
                    dd($e->getMessage());
                }
            }
        }
        return response()->json($jResponse,$error);
    }
    public function showConfigVoucher(Request $request,$id_tipoasiento){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $year   = $request->query('year');
                $entity = $request->query('entity');
                $depto  = $request->query('depto');

                try{
                    $data = AccountingData::showConfigVoucher($entity,$year,$depto,$id_tipoasiento);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data[0];
                        $error = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $error = "204";
                    }
                }catch(Exception $e){
                    dd($e->getMessage());
                }
            }
        }
        return response()->json($jResponse,$error);
    }
    public function addConfigVoucher(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $entity = $params->data->entity;
                $id_depto = $params->data->depto;
                $id_tipoasiento = $params->data->id_tipoasiento;
                $year = $params->data->year;
                $id_modulo = $params->data->id_modulo;
                $automatico = $params->data->automatico;
                $nombre = $params->data->nombre;
                $id_tipovoucher = $params->data->id_tipovoucher;
                $id_sistemaexterno = $params->data->id_sistemaexterno;
                $id_tipoasiento_parent = $params->data->id_tipoasiento_parent;
                try{
                    $data = AccountingData::addConfigVoucher($id_tipoasiento,$entity,$id_depto,$id_modulo,$id_tipovoucher,$year,$automatico,$nombre,$id_sistemaexterno, $id_tipoasiento_parent);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = ['items' => "Item registrado"];
                    $error = "201";
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = substr($e->getMessage(),34,30);
                    $jResponse['data'] = [];
                    $error = "400";
                }
            }
        }
        return response()->json($jResponse,$error);
    }

    public function cloneConfigVoucherByEntidad(Request $request, $id_entidad){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $data = Input::all();
            $validador = Validator::make($data,  ['id_anho_to' => 'required', 'id_anho_from' => 'required']);
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();;
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $id_anho_to = Input::get('id_anho_to');
            $id_anho_from = Input::get('id_anho_from');

            $pdo = DB::getPdo();
            DB::beginTransaction();
            try{
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $stmt = $pdo->prepare("begin eliseo.scp_conf_vouch_clone_by_year(:v_ID_ENTIDAD, :v_ID_ANHO_TO,
                :v_ID_ANHO_FROM, :v_ERROR, :v_MSGERROR); end;");
                $stmt->bindParam(':v_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':v_ID_ANHO_TO', $id_anho_to, PDO::PARAM_INT);
                $stmt->bindParam(':v_ID_ANHO_FROM', $id_anho_from, PDO::PARAM_INT);
                $stmt->bindParam(':v_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':v_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);


/*
        vitmar
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $entity = $params->data->entity;
                $id_depto = $params->data->depto;
                $id_tipoasiento = $params->data->id_tipoasiento;
                $year = $params->data->year;
                $id_modulo = $params->data->id_modulo;
                $automatico = $params->data->automatico;
                $nombre = $params->data->nombre;
                $id_tipovoucher = $params->data->id_tipovoucher;
                $id_sistemaexterno = $params->data->id_sistemaexterno;
                $id_tipoasiento_parent = $params->data->id_tipoasiento_parent;
                try{
                    $data = AccountingData::addConfigVoucher($id_tipoasiento,$entity,$id_depto,$id_modulo,$id_tipovoucher,$year,$automatico,$nombre,$id_sistemaexterno, $id_tipoasiento_parent);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = ['items' => "Item registrado"];
                    $error = "201";
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = substr($e->getMessage(),34,30);
                    $jResponse['data'] = [];
                    $error = "400";
                }
            }
        }
        return response()->json($jResponse,$error);
        */
    }


    public function updateConfigVoucher(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));

                $entity = $params->data->entity;
                $id_depto = $params->data->depto;
                $id_tipoasiento = $params->data->id_tipoasiento;
                $year = $params->data->year;
                $id_modulo = $params->data->id_modulo;
                $automatico = $params->data->automatico;
                $nombre = $params->data->nombre;
                $id_tipovoucher = $params->data->id_tipovoucher;
                $id_sistemaexterno = $params->data->id_sistemaexterno;
                $id_tipoasiento_parent = $params->data->id_tipoasiento_parent;

                try{
                    $data = AccountingData::updateConfigVoucher($id_tipoasiento,$entity,$id_depto,$id_modulo,$id_tipovoucher,$year,$automatico,$nombre,$id_sistemaexterno, $id_tipoasiento_parent);
                    // if(count($data)>0){
                        // $jResponse['success'] = true;
                        // $jResponse['message'] = "Success";
                        // $jResponse['data'] = $data;
                        // $code = "200";
                    // }else{
                    //     $jResponse['success'] = false;
                    //     $jResponse['message'] = $data;
                    //     $jResponse['data'] = [];
                    //     $code = "204";
                    // }

                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = ['items' => $data];
                    $error = "200";
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "204";
                }
            }
        }
        return response()->json($jResponse);
    }
    public function deleteConfigVoucher($year,$entity,$depto,$id_tipoasiento){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $error = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                /*$entity = $request->query('entity');
                $depto  = $request->query('depto');
                $year   = $request->query('year');
                $id_tipoasiento   = $request->query('id_tipoasiento');*/
                try{
                    $data = AccountingData::deleteConfigVoucher($entity,$depto,$year,$id_tipoasiento);
                    $error = "204";
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                    $error = "404";
                }

            }
        }
        return response()->json($jResponse,$error);
    }
    public function listTipoAsiento(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $entity = $request->query('id_entidad');
                    $year   = $request->query('id_anho');
                    $depto   = $request->query('id_depto');
                    if($entity != "" && $year != ""){
                        $data = AccountingData::listTipoAsientoEntidad($entity,$year,$depto);
                    }else{
                        $data = AccountingData::listTipoAsiento();
                    }

                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTipoAsientoEntidad(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $entity = $request->query('entity');
                    $year   = $request->query('year');
                    $data = AccountingData::listTipoAsientoEntidad($entity,$year, null);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTipoComprobante(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $tipo = $request->query('tipo');
                    //dd($tipo);
                    if($tipo){
                        $data = AccountingData::listTipoComprobante($tipo);
                    }else{
                        $data = AccountingData::listTipoComprobanteAll();
                    }
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTipoPlan(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::listTipoPlan();
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listDeptoParent($entity){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            try{
                $data = AccountingData::listDeptoParent($entity);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "204";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }


    public function listVoucher(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $id_entidad = $request->query('id_entidad');
            $id_depto  = $request->query('id_depto');
            $id_anho   = $request->query('id_anho');
            $id_mes   = $request->query('id_mes');
            $id_tipoasiento   = $request->query('id_tipoasiento');
            $id_tipovoucher   = $request->query('id_tipovoucher');

            try{
                $data = AccountingData::listVoucher($id_entidad,$id_depto,$id_anho,$id_mes,$id_tipoasiento,$id_tipovoucher,$id_user);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }


    public function deleteVoucher($idVoucher){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $resp = AccountingData::deleteVoucher($idVoucher);
                $jResponse['success'] = true;
                if($resp){ //-> resp == 1 if defined
                    $jResponse['message'] = "Voucher has been deleted";
                    $jResponse['data'] = $resp == 1; // true | false
                    $code = "200";
                }else{ // !resp || resp !=1
                    $jResponse['message'] = "The item does not exist, can't deleted";
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
                $jResponse['code'] = 405;
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listMyVoucher(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $id_entidad = $request->query('id_entidad');
            $id_depto  = $request->query('id_depto');
            $id_anho   = $request->query('id_anho');
            $id_mes   = $request->query('id_mes');
            $id_tipoasiento   = $request->query('id_tipoasiento');
            $id_tipovoucher   = $request->query('id_tipovoucher');
            $all_vouchers   = filter_var($request->query('all_vouchers'), FILTER_VALIDATE_BOOLEAN);

            try{
                $data = AccountingData::listMyVoucher($id_entidad,$id_depto,$id_anho,
                    $id_mes,$id_tipoasiento,$id_tipovoucher,$id_user, $all_vouchers);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showVoucher(Request $request,$id_voucher){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $year   = $request->query('year');
                $entity = $request->query('entity');
                $depto  = $request->query('depto');
                try{
                    $data = AccountingData::showVoucher($id_voucher);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function validarVoucherAutomatico(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $id_entidad = $request->query('id_entidad');
                $id_depto  = $request->query('id_depto');
                $id_anho   = $request->query('id_anho');
                $id_tipoasiento   = $request->query('id_tipoasiento');
                $id_tipovoucher   = $request->query('id_tipovoucher');
                try{
                    $data = AccountingData::voucherAutomatico($id_entidad,$id_depto,$id_tipoasiento,$id_tipovoucher,$id_anho);

                    if(count($data)>0){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data[0]];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addVoucher(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $id_voucher = 0;
            try{
                $params = json_decode(file_get_contents("php://input"));
                $fecha  = $params->data->fecha;
                $data = explode("-", $fecha);
                $mes = intval($data[1]); // porción1
                $anho = intval($data[0]); // porción2
                if($anho == $params->data->id_anho){
                    if($mes == $params->data->id_mes){
                        $pdo = DB::getPdo();

                        $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_CREAR_VOUCHER(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_FECHA, :P_ID_TIPOASIENTO, :P_ID_TIPOVOUCHER, :P_ID_SEAT_PARENT, :P_ACTIVO, :ID_PERSONA, :P_ID_VOUCHER); end;");
                        $stmt->bindParam(':P_ID_ENTIDAD', $params->data->id_entidad, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_DEPTO', $params->data->id_depto, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_ANHO', $params->data->id_anho, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_MES', $params->data->id_mes, PDO::PARAM_INT);
                        $stmt->bindParam(':P_FECHA', $params->data->fecha, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_TIPOASIENTO', $params->data->id_tipoasiento, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_TIPOVOUCHER', $params->data->id_tipovoucher, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_SEAT_PARENT', $params->data->id_seat_parent, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ACTIVO', $params->data->activo, PDO::PARAM_STR);
                        $stmt->bindParam(':ID_PERSONA', $id_user, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                        $stmt->execute();

                        if(property_exists($params->data, 'asignar_default') and $params->data->asignar_default === 'S') {
                            $list = AccountingData::listAssignVouchers($id_voucher,$id_user);
                            if(count($list) == 0){
                                $data = AccountingData::assignVouchers($id_voucher,$id_user,null);
                                $jResponse['success'] = true;
                                $jResponse['message'] = "The voucher has been successfully assigned";
                                $jResponse['data'] = $id_voucher;
                                $code = "200";
                            }
                            $voucherChild = DB::table('CONTA_VOUCHER')->where('ID_VOUCHER_PARENT', $id_voucher )->first();
                            if($voucherChild){
                                $lista = AccountingData::listAssignVouchers($voucherChild->id_voucher,$id_user);
                                if(count($lista) == 0){
                                    $data = AccountingData::assignVouchers($voucherChild->id_voucher,$id_user,null);
                                    $jResponse['success'] = true;
                                    $jResponse['message'] = "The voucher has been successfully assigned";
                                    $jResponse['data'] = $voucherChild->id_voucher;
                                    $code = "200";
                                }
                            }
                        }

                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $id_voucher;
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = "El Mes del campo Fecha es incorrecto";
                        $jResponse['data'] = $id_voucher;
                        $code = "200";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "El Año del campo Fecha es incorrecto";
                    $jResponse['data'] = $id_voucher;
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "501";
            }
        }
        return response()->json($jResponse,$code);

        /*
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $entity = $params->data->entity;
                $depto = $params->data->depto;
                $year = $params->data->year;
                $mes = $params->data->mes;
                $id_tipoasiento = $params->data->id_tipoasiento;
                $id_tipovoucher = $params->data->id_tipovoucher;
                $voucher = 0;
                try{
                    //$params = json_decode(file_get_contents("php://input"));
                    $bindings = [
                        'entity' => $params->data->entity,
                        'id_depto' => $params->data->depto,
                        'year' => $params->data->year,
                        'mes' => $mes,
                        'id_tipoasiento' => $id_tipoasiento,
                        'id_tipovoucher' => $id_tipovoucher,
                        'activo' => $params->data->activo,
                        'voucher' => $voucher
                    ];
                    $result = DB::executeProcedure('PKG_ACCOUNTING.SP_CREAR_VOUCHER', $bindings);
                    /*$pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_CREAR_VOUCHER(:p1, :p2, :p3, :p4, :p5, :p6); end;");
                    $stmt->bindParam(':p1', $entity, PDO::PARAM_INT);
                    $stmt->bindParam(':p2', $depto, PDO::PARAM_INT);
                    $stmt->bindParam(':p3', $year, PDO::PARAM_INT);
                    $stmt->bindParam(':p4', $mes, PDO::PARAM_INT);
                    $stmt->bindParam(':p5', $id_tipoasiento, PDO::PARAM_INT);
                    $stmt->bindParam(':p6', $voucher, PDO::PARAM_INT);
                    $stmt->execute();*/
                          /*
                    $stmt = $db->getPdo()->prepare("EXEC PKG_ACCOUNTING.SP_CREAR_VOUCHER ?, ?, ?, ?, ?, ?");
                    $stmt->bindParam(1, $entity);
                    $stmt->bindParam(2, $depto);
                    $stmt->bindParam(3, $year);
                    $stmt->bindParam(4, $mes);
                    $stmt->bindParam(5, $id_tipoasiento);
                    $stmt->bindParam(6, $voucher, PDO::PARAM_INT|PDO::PARAM_INPUT_OUTPUT);


                    echo $voucher . "\n";
                    $stmt->execute();
                    echo $voucher;*

                    //dd($result);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = ['items' => $result[0]];
                    $code = "201";
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "500";
                }
            }
        }
        return response()->json($jResponse,$code);*/
    }
    public function addVoucherPurchases(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_voucher = 0;
            try{
                $params = json_decode(file_get_contents("php://input"));
                $fecha  = $params->fecha;
                $data = explode("-", $fecha);
                $mes = intval($data[1]); // porción1
                $anho = intval($data[0]); // porción2
                if($anho == $params->id_anho){
                    if($mes == $params->id_mes){
                        $asiento = AccountingData::showVoucherConfig($id_entidad,$id_depto,$params->id_tipovoucher,$params->id_anho);
                        foreach ($asiento as $key => $value){
                            $id_tipoasiento = $value->id_tipoasiento;
                        }

                        $pdo = DB::getPdo();
                        $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_CREAR_VOUCHER(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_FECHA, :P_ID_TIPOASIENTO, :P_ID_TIPOVOUCHER, :P_ACTIVO, :P_ID_VOUCHER); end;");
                        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_ANHO', $params->id_anho, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ID_MES', $params->id_mes, PDO::PARAM_INT);
                        $stmt->bindParam(':P_FECHA', $params->fecha, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_TIPOASIENTO', $id_tipoasiento, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_TIPOVOUCHER', $params->id_tipovoucher, PDO::PARAM_INT);
                        $stmt->bindParam(':P_ACTIVO', $params->activo, PDO::PARAM_STR);
                        $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                        $stmt->execute();
                        $jResponse['success'] = true;
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $id_voucher;
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = "El Mes del campo Fecha es incorrecto";
                        $jResponse['data'] = $id_voucher;
                        $code = "200";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "El Año del campo Fecha es incorrecto";
                    $jResponse['data'] = $id_voucher;
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "501";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateVoucher($id_voucher){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                /*$entity = $params->data->entity;
                $depto = $params->data->depto;
                $year = $params->data->year;
                $mes = $params->data->mes;
                $id_tipoasiento = $params->data->id_tipoasiento;*/
                $activo = $params->data->activo;
                try{
                    $valida = AccountingData::validaVoucherDia($id_voucher);
                    foreach($valida as $item){
                        $dias = $item->dias;
                    }
                    if($activo === 'S'){
                        if($dias <= 30) {
                            $data = AccountingData::updateVoucher($id_voucher,$activo);
                            //$data = AccountingData::updateVoucher($id_voucher,$entity,$depto,$year,$mes,$id_tipoasiento,$activo);
                            $jResponse['success'] = true;
                            $jResponse['message'] = "The item was updated successfully";
                            $jResponse['data'] = $data[0];
                            $code = "200";
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "Solo puede modificar el voucher por 30 días desde su creación.";
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    }else{
                        $data = AccountingData::updateVoucher($id_voucher,$activo);
                        //$data = AccountingData::updateVoucher($id_voucher,$entity,$depto,$year,$mes,$id_tipoasiento,$activo);
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";
                        $jResponse['data'] = $data[0];
                        $code = "200";
                    }
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
        }
        return response()->json($jResponse,$code);
    }

    public function editVoucher(AccountingVoucherRequest $request, $idVoucher){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::editVoucher($idVoucher, $this->request->all());
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listDocumentoImpresion(Request $request){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if($valida =='SI')
        {
            $entity = $request->query('entity');
            $depto = $request->query('depto');
            $jResponse =[];
            try
            {
                $data = AccountingData::listDocumentoImpresion($entity,$depto);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "204";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function myListDocumentoImpresion(Request $request){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        $id_user    = $jResponse["id_user"];
        if($valida =='SI')
        {
            $jResponse =[];
            try
            {
                $data = AccountingData::listDocumentoImpresion($id_entidad,$id_depto);
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
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showDocumentoImpresion($id_documento){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida =='SI')
        {
            $jResponse =[];
            try
            {
                $data = AccountingData::showDocumentoImpresion($id_documento);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "204";
                }
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addDocumentoImpresion(){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida =='SI')
        {
            $jResponse =[];
            try
            {
                $params = json_decode(file_get_contents("php://input"));
                $entity = $params->entity;
                $depto = $params->depto;
                $id_comprobante = $params->id_comprobante;
                $nombre = $params->nombre;
                $puerto = $params->puerto;
                $linea = $params->linea;
                $columna = $params->columna;
                $serie = $params->serie;
                $contador = $params->contador;
                $id_comprobante_afecto = $params->id_comprobante_afecto;

                $data = AccountingData::addDocumentoImpresion($id_comprobante,$entity,$depto,$nombre,$puerto,$linea,$columna,$serie,$contador, $id_comprobante_afecto);
                /*foreach ($data as $key => $item){
                    $id = $item->id_documento;
                }*/
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data[0];
                $code = "201";

            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateDocumentoImpresion($id_documento){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        if($valida =='SI')
        {
            $jResponse =[];
            try
            {
                $params = json_decode(file_get_contents("php://input"));
                $id_comprobante = $params->id_comprobante;
                $entity = $params->entity;
                $nombre = $params->nombre;
                $puerto = $params->puerto;
                $linea = $params->linea;
                $columna = $params->columna;
                $serie = $params->serie;
                $contador = $params->contador;
                $id_comprobante_afecto = $params->id_comprobante_afecto;

                /*$data = AccountingData::showDocumentoImpresionSerie($entity,$serie);
                if(count($data)==1){*/
                    $data = AccountingData::updateDocumentoImpresion($id_documento,$id_comprobante,$nombre,$puerto,$linea,$columna,$serie,$contador, $id_comprobante_afecto);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                /*}else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Ya Existe una Serie Igual para el Documento";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }    */
            }
            catch(Exception $e)
            {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateDocumentoImpresionAtrr($id_documento){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                $data = json_decode(json_encode($params),true);
                foreach($data as $clave=> $valor){
                    //echo($clave."-".$valor."<br>");
                    $data = AccountingData::updateDocumentoImpresionAtrr($id_documento,$clave,$valor);
                }
                //$id_comprobante = $params->data->activo;
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = [];
                $code = "200";

                /*try{
                    $data = AccountingData::updateDocumentoImpresion($id_documento,$id_comprobante,$nombre,$puerto,$linea,$columna,$serie,$contador);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $data[0];
                    $code = "201";
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "200";
                }*/
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listDocumentoImpresionDetails(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $id_documento = $request->query('id_documento');
                try{
                    $data = AccountingData::listDocumentoImpresionDetails($id_documento);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showDocumentoImpresionDetails($id_docdetalle){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::showDocumentoImpresionDetails($id_docdetalle);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data[0];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addDocumentoImpresionDetails(Request $request){
        // $jResponse = [
        //     'success' => false,
        //     'message' => 'Recurso no Autorizado',
        //     'data' => []
        // ];
        // $code = "401";
        // $token = $this->request->header('Authorization');
        // $params = json_decode(file_get_contents("php://input"));
        // if ($token) {
        //     session_id($token);
        //     session_start();
        //     $bindings = [
        //         'p_token' => $token
        //     ];
        //     $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        //     $valida = $result[0];
        //     if( $valida->active == 'SI'  ){
        $this->validate($request,[
            'id_documento' => 'required',
            'contenido' => 'required|max:300',
            'modo' => 'required|max:1|min:1',
            'tipo' => 'required|max:1|min:1',
            'pos_x' => 'required|numeric',
            'pos_y' => 'required|numeric',
        ]);
        $params = json_decode(file_get_contents("php://input"));
        $id_documento = $params->id_documento;
        $contenido = $params->contenido;
        $modo = $params->modo;
        $tipo = $params->tipo;
        $pos_x = $params->pos_x;
        $pos_y = $params->pos_y;
        try{
            $data = AccountingData::addDocumentoImpresionDetails($id_documento,$contenido,$modo,$tipo,$pos_x,$pos_y);
            //dd($data);
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was created successfully";
            $jResponse['data'] = $data[0];
            $code = "201";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "500";
        }
        //     }
        // }
        return response()->json($jResponse,$code);
    }
    public function updateDocumentoImpresionDetails(Request $request, $id_docdetalle){
        // $jResponse = [
        //     'success' => false,
        //     'message' => 'Recurso no Autorizado',
        //     'data' => []
        // ];
        // $code = "401";
        // $token = $this->request->header('Authorization');
        // $params = json_decode(file_get_contents("php://input"));
        // if ($token) {
        //     session_id($token);
        //     session_start();
        //     $bindings = [
        //         'p_token' => $token
        //     ];
        //     $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        //     $valida = $result[0];
        //     if( $valida->active == 'SI'  ){
        $this->validate($request,[
            'id_documento' => 'required',
            'contenido' => 'required|max:300',
            'modo' => 'required|max:1|min:1',
            'tipo' => 'required|max:1|min:1',
            'pos_x' => 'required|numeric',
            'pos_y' => 'required|numeric',
        ]);
        $params = json_decode(file_get_contents("php://input"));
        $id_documento = $params->id_documento;
        $contenido = $params->contenido;
        $modo = $params->modo;
        $tipo = $params->tipo;
        $pos_x = $params->pos_x;
        $pos_y = $params->pos_y;
        try{
            $data = AccountingData::updateDocumentoImpresionDetails($id_docdetalle,$id_documento,$contenido,$modo,$tipo,$pos_x,$pos_y);
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully";
            $jResponse['data'] = $data[0];
            $code = "200";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "500";
        }
        //     }
        // }
        return response()->json($jResponse,$code);
    }
    public function deleteDocumentoImpresionDetails($id_docdetalle){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::deleteDocumentoImpresionDetails($id_docdetalle);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "400";
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteDocumentoImpresion($id_documento){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::deleteDocumentoImpresionDetailsByIdDocumento($id_documento);
                    $data = AccountingData::deleteDocumentoImpresion($id_documento);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "400";
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPeriodos(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $params = json_decode(file_get_contents("php://input"));
                // $entity = $params->data->entity;
                // $year = $params->data->year;
                // $plan = $params->data->plan;
                // $nombre = $params->data->nombre;
                // $fecha_ini = $params->data->fecha_ini;
                // $fecha_fin = $params->data->fecha_fin;
                $entity = $params->entity;
                $year = $params->year;
                $plan = $params->plan;
                $nombre = $params->nombre;
                $fecha_ini = $params->fecha_ini;
                $fecha_fin = $params->fecha_fin;
                $data = AccountingData::listPeriodos($entity,$year);
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The items already exist";
                    $jResponse['data'] = [];
                    $code = "201";
                }else{
                    $data = AccountingData::listPeriodosActivos($entity);
                    if(count($data) < 2){
                        try{
                            $bindings = [
                                'entity' => $entity,
                                'year' => $year,
                                'plan' => $plan,
                                'nombre' => $nombre,
                                'fecha_ini' => $fecha_ini,
                                'fecha_fin' => $fecha_fin
                            ];
                            DB::executeProcedure('PKG_ACCOUNTING.SP_CREAR_PERIODOS', $bindings);
                            $jResponse['success'] = true;
                            $jResponse['message'] = "The item was created successfully";
                            $jResponse['data'] = [];
                            $code = "201";
                        }catch(Exception $e){
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "500";
                        }
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Ya Existen 2 periodos Contables Activos";
                        $jResponse['data'] = [];
                        $code = "203";
                    }
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPeriodosMeses(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $entity = $request->query('entity');
                $year = $request->query('year');
                try{
                    $data = AccountingData::listPeriodosMeses($entity,$year);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showPeriodos(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida == 'SI'){
            $jResponse=[];
            try{
                $entity = $request->query('entity');
                $year = $request->query('year');
                $data = AccountingData::showPeriodoStatus($entity,$year);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $data[0];
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "204";

            }
        }
        return response()->json($jResponse,$code);
    }
    public function updatePeriodoMes(){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida == 'SI'){
            $jResponse=[];

            $data = Input::get('data');
            $entity = $data['entity'];
            $year = $data['year'];
            $mes = $data['mes'];

            $data = AccountingData::showPeriodoStatus($entity,$year);
            foreach ($data as $key => $item){
                $activo = $item->activo;
            }
            if($activo !== "1"){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto! Ya no se puede cambiar el esto de los meses, el año [$year] ya esta cerrado.";
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            if($mes != 1){
                $mes_a = $mes-1;
            }else{
                $mes_a = $mes;
            }

            $data = AccountingData::showMesStatus($entity,$year,$mes);
            foreach ($data as $key => $item){
                $estado = $item->estado;
            }

            if($estado === "0"){ // OPEN
                $data = AccountingData::showMesStatusOpen($entity,$year,'1');
                if(count($data) > 2) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Alto! No se permite Aperturar mas de 3 meses.";
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }

                if($mes !== $mes_a){
                    $data = AccountingData::showMesStatus($entity,$year,$mes_a);
                    foreach ($data as $key => $item){
                        $estado_a = $item->estado;
                    }
                    if($estado_a === "0"){
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Alto! Primero aperture el mes anterior.";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                }

                AccountingData::updatePeriodoMes($entity,$year,$mes,"1", $id_user);
                $jResponse['success'] = true;
                $jResponse['message'] = "Éxito! Mes aperturado con éxito.";
                $jResponse['data'] = [];
                $code = "200";
                goto end;

            }elseif($estado == "1"){ // CLOSE
                if($mes !== $mes_a){
                    $data = AccountingData::showMesStatus($entity,$year,$mes_a);
                    foreach ($data as $key => $item){
                        $estado_a = $item->estado;
                    }
                    if($estado_a === "1"){
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Alto! No se permite cerrar el mes, cierre primero el anterior";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                }
                AccountingData::updatePeriodoMes($entity,$year,$mes,"2", $id_user);
                $jResponse['success'] = true;
                $jResponse['message'] = "Éxito! Mes cerrado exitosamente.";
                $jResponse['data'] = [];
                $code = "200";
                goto end;
            }else{ // STATUS = 3 THEN RE-ABRIR
                AccountingData::updatePeriodoMes($entity,$year,$mes,"1", $id_user);
                $jResponse['success'] = true;
                $jResponse['message'] = "Éxito! Mes re-abierto exitosamente.";
                $jResponse['data'] = [];
                $code = "200";
                goto end;
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function updatePeriodoChangeStatus(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida == 'SI'){
            $jResponse=[];
            $data_input = Input::get('data');

            $params = json_decode(file_get_contents("php://input"));
            $entity = $data_input['entity'];
            $year = $data_input['year'];
            $activo = $data_input['activo'];

            $data = AccountingData::showMesStatusOpen($entity,$year,'0');
            if(count($data) === 12){
                // Solo cuando los periodos del año aún no fueron tocados se podra cambiar es estado.
                $data = AccountingData::updatePeriodo($entity,$year, $activo);
                $jResponse['success'] = true;
                $jResponse['message'] = "Éxito! El año contable $year fué cambiado de estado con éxito.";
                $jResponse['data'] = [];
                $code = "200";
            } else {
                $data = AccountingData::showPeriodosActivos($entity,$year-1);
                if(count($data)>0){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Alto! No se puede cerrar el año $year, primero cierre en año anterior";
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }

                $data = AccountingData::showMesStatusOpen($entity,$year,'2');
                if(count($data) < 12){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Alto! No se puede cerrar el año $year, existen meses sin cerrar.";
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }
                try{
                    $data = AccountingData::updatePeriodo($entity,$year, $activo);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Éxito! El año contable $year fué cambiado de estado con éxito.";
                    $jResponse['data'] = [];
                    $code = "200";
                } catch (Exception $e) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }
            }
        }
        end:
        return response()->json($jResponse,$code);
    }
    public function planAccountingEnterprise(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingController::recursivePlanAccounting("A");
                    $jResponse['message'] = "SUCCES";
                    $jResponse['success'] = true;
                    $jResponse['data'] = ['items' => $data];
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }
        }
        return response()->json($jResponse);
    }

    public function planAccountingEnterprisePcgeV2(Request $request){
        $jResponse = [];
        $code = 200;
        try{
            $text_search = $request->get('text_search', '');
            $anho = $request->get('anho', '');
            $id_empresa = $request->get('id_empresa', '');
            $jResponse['message'] = "Success";
            $jResponse['success'] = true;
            $jResponse['data'] = AccountingData::planCtaEnterprisePcgeV2($text_search, $anho, $id_empresa);
        }catch(Exception $e){
            $jResponse['message'] = $e->getMessage();
            $jResponse['success'] = false;
            $jResponse['data'] = [];
            $code = 203;
        }
        return response()->json($jResponse, $code);
    }

    public function getPCGEExportPdf(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $request->input('id_empresa');
        $entidad = $request->input('id_entidad');
        $anho = $request->input('id_anho');
        $mes = $request->input('id_mes');
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            ReportLegalController::ExportPCGEPDF($empresa, $entidad, $anho, $mes);
        }
        return response()->json($jResponse, $code);
    }



    // export-import-pantigoso

    public function getexportToPdfPCGEaPCD(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $request->input('id_empresa');
        $entidad = $request->input('id_entidad');
        $anho = $request->input('id_anho');
        $mes = $request->input('id_mes');

        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingLegalData::getExportPCGEaPCDDataTab($empresa, $entidad, $anho, $mes);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }
    public function getexportToPdfPCDaPCGE(Request $request)
    {
        $jResponse = [];
        $code = 200;
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $request->input('id_empresa');
        $entidad = $request->input('id_entidad');
        $anho = $request->input('id_anho');
        $mes = $request->input('id_mes');

        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingLegalData::getExportPCDaPCGEDataTab($empresa, $entidad, $anho, $mes);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }


    public function storeImportPCGE(Request $request)
    {
        $jResponse = [];
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $valida = $jResponse["valida"];
        $isValid = $valida == 'SI'; // true  : false;

        if (!$isValid) return;

        $jResponse = [];
        $jResponse['success'] = true;
        $jResponse['message'] = "Success";
        $datin =  \Excel::load($request->file('archivo_file'));
        // // dd($datin);
        $data = $datin->toArray();
        $listaValid = array();

        // Aqui validarmos
        $id_empresa = 0;
        foreach ($data as $index => $d) {
            if ($index > 0) {
                if ($index == 0 || empty($d['id_empresa']) || empty($d['codigo_aaasi']) || empty($d['codigo_empresarial'])) {
                    continue;
                }
                $id_empresa = $d['id_empresa'];
                $cuentaaasi = null;
                if ($d['codigo_aaasi']) {
                    $cuentaaasi = DB::table('eliseo.CONTA_CTA_DENOMINACIONAL')
                        ->where('id_cuentaaasi', $d['codigo_aaasi'])
                        ->where('id_tipoplan', 1)
                        ->first();
                    if (!$cuentaaasi) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "¡Alto! La cuenta aasi no existe " . $d['codigo_aaasi'];
                        $jResponse['data'] = [];
                        return response()->json($jResponse, 400);
                    }
                }
                $ctaempresarial = null;
                if ($d['codigo_empresarial']) {
                    $ctaempresarial = DB::table('eliseo.CONTA_CTA_EMPRESARIAL')
                        ->where('id_cuentaempresarial', $d['codigo_empresarial'])
                        ->first();
                    if (!$ctaempresarial) {
                        DB::rollback();
                        $jResponse['success'] = false;
                        $jResponse['message'] = "¡Alto! La cuenta empresarial no existe " . $d['codigo_empresarial'];
                        $jResponse['data'] = [];
                        return response()->json($jResponse, 400);
                    }
                }
            }
        }

        // aqui eliminamos todo y guardamos
        DB::beginTransaction();
        // $ids_anhos = [2019, 2020, 2021, 2022, 2023, 2024];
        $ids_anhos = [2025];
        $id_empresas = [
            202,
            203,
            204,
            205,
            206,
            207,
            208,
            209,
            210
        ];
        DB::table('eliseo.conta_empresa_cta')->whereIn('id_anho', $ids_anhos)
            ->whereIn('id_empresa', $id_empresas)
            ->delete();
        foreach ($ids_anhos as $id_anho) {
            foreach ($id_empresas as $id_empresa) {
                foreach ($data as $index => $d) {
                    if ($index == 0 || empty($d['id_empresa']) || empty($d['codigo_aaasi']) || empty($d['codigo_empresarial'])) {
                        continue;
                    }
                    if ($index > 0) {
                        // return response()->json($d['id_empresa'], 200);
                        # code...
                        DB::table('eliseo.conta_empresa_cta')->insert([
                            'id_anho' =>  $id_anho,
                            'id_empresa' => $id_empresa,
                            'id_cuentaempresarial' => $d['codigo_empresarial'],
                            'id_cuentaaasi' => $d['codigo_aaasi'],
                            'id_restriccion' => '0A', //consultar si el valor por defecto si esta bien
                            'id_moneda' => 7,
                            'id_tipoplan' => 1 //consultar si el valor por defecto si esta bien
                        ]);
                    }
                }
            }
        }
        DB::commit();
        $jResponse = [
            'success' => true,
            'message' => 'Datos obtenidos correctamente.',
            'data' => $listaValid
        ];
        return response()->json($jResponse, 200);
    }

    public function storeImportPCD(Request $request)
    {

        $jResponse = [];
        $code = 200;
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $request->input('id_empresa');
        $entidad = $request->input('id_entidad');
        $anho = $request->input('id_anho');
        $mes = $request->input('id_mes');

        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $code = "200";
            $datin =  \Excel::load($request->file('archivo_file'));
            // // dd($datin);
            $data = $datin->toArray();
            $listaValid = array();
            $i = 0;
            foreach ($data as $d) {
                if ($i >= 0) {
                    // echo $d['id_empresa'];
                    $obj = [
                        // 'id_empresa'          =>    $request->id_empresa,
                        // 'id_empresa'          =>    '17112',
                        // 'id_anho'               =>    '2024',
                        'id_empresa'            => $d['id_empresa'],
                        'id_anho'               => $d['id_anho'],
                        'codigo_aaasi'          => $d['codigo_aaasi'],
                        'codigo_parent'         => $d['codigo_parent'],
                        'nombre_denominacional' => $d['nombre_denominacional'],
                        'codigo_empresarial'    => $d['codigo_empresarial'],
                        'nombre_empresarial'    => $d['nombre_empresarial'],

                    ];
                    $listaValid[] = $obj;
                }
                $i++;
            }
            // Imprimir el array $listaValid como un objeto JSON
            $response = [
                'success' => true,
                'message' => 'Datos obtenidos correctamente.',
                'data' => $listaValid
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
            // echo json_encode($listaValid);  


            //bd guardars
            // $return = AccountingLegalData::insertDataPCGEExcel($listaValid,$empresa,$anho);
            // if ($return['nerror']==0) {
            //     $jResponse['success'] = true;
            //     $jResponse['message'] = $return['msgerror'];                 
            //     $jResponse['data'] = [];
            //     $code = "200";  
            // } else {
            //   $jResponse['success'] = false;
            //   $jResponse['message'] = $return['msgerror'];
            //   $jResponse['data'] = [];
            //   $code = "202";
            // }

        }
        return response()->json($jResponse, $code);
    }
    // export-import-pantigoso



    public function planAccountingEnterprisePcdV2(Request $request)
    {
        $jResponse = [];
        $code = 200;
        try{
            $text_search = $request->get('text_search', '');
            $anho = $request->get('anho', '');
            $id_empresa = $request->get('id_empresa', '');
            $jResponse['message'] = "Success";
            $jResponse['success'] = true;
            $jResponse['data'] = AccountingData::planCtaEnterprisePcdV2($text_search, $anho, $id_empresa);
        }catch(Exception $e){
            $jResponse['message'] = $e->getMessage();
            $jResponse['success'] = false;
            $jResponse['data'] = [];
            $code = 203;
        }
        return response()->json($jResponse, $code);
    }

    public function recursivePlanAccounting($id_parent){
        $parent = [];
        $data = AccountingData::planCtaEnterprise($id_parent);
        foreach ($data as $key => $value){
            $row = $this->recursivePlanAccounting($value->id_cuentaempresarial);
            $parent[] = ['value' => $value->id_cuentaempresarial, 'text' => $value->id_cuentaempresarial.'-'.$value->nombre,'children'=>$row];
        }
        return $parent;
    }
    public function showPlanAccountingEnterprise($id_cuentaempresarial){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::showPlanAccountingEnterprise($id_cuentaempresarial);
                    $jResponse['message'] = "SUCCES";
                    $jResponse['success'] = true;
                    $jResponse['data'] =  $data[0];
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }
        }
        return response()->json($jResponse);
    }
    public function addPlanAccountingEnterprise(){
        $params = json_decode(file_get_contents("php://input"));
        $id_parent = $params->id_parent;
        $nombre = $params->nombre;
        try{
            $data = AccountingData::addPlanAccountingEnterprise($id_parent,$nombre);
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was created successfully";
            $jResponse['data'] = $data[0];
            $code = "201";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "500";
        }
        return response()->json($jResponse,$code);
    }

    public function updatePlanAccountingEnterpriseV2(Request $request, $id_cuentaempresarial){
        $this->validate($request,[
            'nombre' => 'required|max:1000'
        ]);
        $jResponse = [];
        try{
            $nombre = $request->get('nombre');
            DB::table('eliseo.CONTA_CTA_EMPRESARIAL')
                ->where('ID_CUENTAEMPRESARIAL',$id_cuentaempresarial)
                ->update(array('NOMBRE'=> $nombre));
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function errorResponse($message, $code = 422) {
        return response()->json(['error' => ['message' => $message, 'code' => $code]], $code);
    }

    public function successResponse($data, $code = 200) {
        return response()->json(['data' => $data], $code);
    }

    public function deletePlanAccountingEnterpriseV2($id_cuentaempresarial){
        $exists = DB::table('eliseo.CONTA_CTA_EMPRESARIAL')
                    ->where('ID_PARENT',$id_cuentaempresarial)
                    ->exists();
        if($exists) {
            return $this->errorResponse('Un momento, la cuenta empresarial tiene hijos, no puede es posible eliminarlo.');
        }

        $exists = DB::table('eliseo.CONTA_EMPRESA_CTA')
                ->where('id_cuentaempresarial',$id_cuentaempresarial)
                ->exists();
        if($exists) {
            return $this->errorResponse("Un momento, la cuenta empresarial tiene equivalencias, no puede es posible eliminarlo.");
        }

        $jResponse = [];
        try{
            DB::table('eliseo.CONTA_CTA_EMPRESARIAL')
                    ->where('ID_CUENTAEMPRESARIAL',$id_cuentaempresarial)
                    ->delete();
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function listPlanAccountingEquivalentv2(Request $request, $id_cuentaempresarial){
        $this->validate($request,[
            'id_empresa' => 'required'
        ]);
        $id_empresa = $request->query('id_empresa');
        $jResponse = [];
        try{
            $query = "SELECT
                        A.ID_ANHO,A.ID_EMPRESA,A.ID_CUENTAEMPRESARIAL,A.ID_CUENTAAASI,A.ID_RESTRICCION,A.ID_MONEDA,A.ID_TIPOPLAN,
                        B.NOMBRE NOMBRE_CUENTAAASI,
                        c.razon_social empresa_nombre_2
                    FROM CONTA_EMPRESA_CTA A
                        INNER JOIN CONTA_CTA_DENOMINACIONAL B ON A.ID_CUENTAAASI=B.ID_CUENTAAASI
                        left join conta_empresa c on a.id_empresa=c.id_empresa
                    WHERE 1=1
                        AND A.ID_EMPRESA = $id_empresa
                        AND A.ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial'
                    ORDER BY
                        A.ID_ANHO DESC, A.ID_CUENTAAASI DESC";
            $jResponse = DB::select($query);
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function addPlanAccountingEquivalentV2(Request $request, $id_cuentaempresarial){
        $this->validate($request,[
            'id_empresa' => 'required',
            'id_anho' => 'required',
            'id_tipoplan' => 'required',
            'id_cuentaaasi' => 'required',
            'id_restriccion' => 'required',
        ]);
        $id_empresa = $request->get('id_empresa');
        $id_anho = $request->get('id_anho');
        $id_tipoplan = $request->get('id_tipoplan');
        $id_cuentaaasi = $request->get('id_cuentaaasi');
        $id_restriccion = $request->get('id_restriccion');
        // $id_moneda = $request->get('id_moneda', 7);
        $id_moneda = 7;

        // $item = DB::table('eliseo.CONTA_ENTIDAD')->where('ID_ENTIDAD', $id_entidad)->first();

        $exists = DB::table('eliseo.CONTA_EMPRESA_CTA')
                ->where('id_anho',$id_anho)
                ->where('id_empresa',$id_empresa)
                ->where('id_cuentaempresarial',$id_cuentaempresarial)
                ->where('id_tipoplan',$id_tipoplan)
                ->exists();
        if($exists) {
            return $this->errorResponse("Un momento, ya existe una cuenta equivalente para la cuenta empresarial: $id_cuentaempresarial, empresa: $id_empresa, año: $id_anho, Tipo plan: $id_tipoplan");
        }
        $jResponse = [];
        try{
            DB::table('CONTA_EMPRESA_CTA')->insert(
                array(
                    'ID_ANHO' => $id_anho,
                    'ID_EMPRESA'=> $id_empresa,
                    'ID_CUENTAEMPRESARIAL'=> $id_cuentaempresarial,
                    'ID_CUENTAAASI'=> $id_cuentaaasi,
                    'ID_RESTRICCION'=> $id_restriccion,
                    'ID_MONEDA'=> $id_moneda,
                    'ID_TIPOPLAN'=> $id_tipoplan)
                );
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function getContaEmpresasV2(Request $request){
        $jResponse = [];
        try{
            $jResponse = DB::table('CONTA_EMPRESA')->get();
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function updatePlanAccountingEquivalentV2(Request $request, $id_cuentaempresarial){
        $this->validate($request,[
            'id_anho' => 'required',
            'id_empresa' => 'required',
            'id_tipoplan' => 'required',
            'id_cuentaaasi' => 'required', // Editable
            'id_restriccion' => 'required', // Editable
        ]);
        $id_empresa = $request->get('id_empresa');
        $id_anho = $request->get('id_anho');
        $id_tipoplan = $request->get('id_tipoplan');
        $id_cuentaaasi = $request->get('id_cuentaaasi');
        $id_restriccion = $request->get('id_restriccion');
        // $id_moneda = $request->get('id_moneda', 7);
        // $id_moneda = 7;

        $jResponse = [];
        try{
            DB::table('CONTA_EMPRESA_CTA')
            ->where('id_anho',$id_anho)
            ->where('id_empresa',$id_empresa)
            ->where('id_cuentaempresarial',$id_cuentaempresarial)
            ->where('id_tipoplan',$id_tipoplan)
            ->update(
                array(
                    'ID_CUENTAAASI'=> $id_cuentaaasi,
                    'ID_RESTRICCION'=> $id_restriccion
                    )
                );
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function deletePlanAccountingEquivalentV2(Request $request,$id_cuentaempresarial,$id_anho,$id_empresa,$id_tipoplan){
        // $id_moneda = $request->get('id_moneda', 7);
        // $id_moneda = 7;

        $jResponse = [];
        try{
            DB::table('CONTA_EMPRESA_CTA')
            ->where('id_anho',$id_anho)
            ->where('id_empresa',$id_empresa)
            ->where('id_cuentaempresarial',$id_cuentaempresarial)
            ->where('id_tipoplan',$id_tipoplan)
            ->delete();

        }catch(Exception $e){
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse($jResponse);
    }

    public function updatePlanAccountingEnterprise($id_cuentaempresarial){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $nombre = $params->data->nombre;
                try{
                    $data = AccountingData::updatePlanAccountingEnterprise($id_cuentaempresarial,$nombre);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $data[0];
                    $code = "201";
                }catch(Exception $e){
                    $jResponse['success'] = false;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deletePlanAccountingEnterprise($id_cuentaempresarial){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::showPlanAccountingEnterpriseParent($id_cuentaempresarial);
                    if(count($data)>0){
                        $jResponse['success'] = False;
                        $jResponse['message'] = "The Countable  Accounting can not be deleted";
                        $jResponse['data'] = [];
                        $code = "400";
                    }else{
                        $data = AccountingData::deletePlanAccountingEnterprise($id_cuentaempresarial);
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was deleted successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "400";
                }
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listTypeMoney(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                try{
                    $data = AccountingData::listTypeMoney();
                    $jResponse['success'] = true;

                    if(count($data)>0){
                        $jResponse['message'] = "Succes";

                        $jResponse['data'] = ['items' => $data];

                        if ($this->request->typeChange == 'true') {
                            $jResponse['data']['typeChange'] = AccountingData::typeChangeToday();
                        }

                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }



                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTypeIGV(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                try{
                    $data = AccountingData::listTypeIGV();
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTypeRestriction(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                try{
                    $data = AccountingData::listTypeRestriction();
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPlanAccountingDenominationalSearchV2(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                $q = $request->query('q');
                $id_tipoplan = $request->query('id_tipoplan');
                $per_page = $request->query('perPage', 15);
                $page = $request->query('page', 1);
                try{
                    $data = AccountingData::listPlanAccountingDenominationalSearchV2($q,$id_tipoplan, $per_page, $page);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }

        }
        return response()->json($jResponse,$code);
    }
    public function listPlanAccountingDenominationalSearch(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                $id_cuentaaasi = $request->query('id_cuentaaasi');
                $id_tipoplan = $request->query('id_tipoplan');
                try{
                    $data = AccountingData::listPlanAccountingDenominationalSearch($id_cuentaaasi,$id_tipoplan);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listDenominationalAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = array(
                    'text_search' => $request->query('text_search'),
                    'id_tipoplan' => $request->query('id_tipoplan'),
                    'page_size' => $request->query('page_size') ? $request->query('page_size') : 10
                );
                $data = AccountingData::listDenominationalAccount($params);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPlanAccountingDenominational($id_tipoplan){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                try{
                    $data = AccountingData::listPlanAccountingDenominational($id_tipoplan);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showPlanAccountingEquivalent(Request $request,$id_cuentaempresarial){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('year');
                try{
                    $data = AccountingData::showPlanAccountingEquivalent($id_entidad,$id_anho,$id_cuentaempresarial);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data[0];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addPlanAccountingEquivalent(){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $id_entidad = $params->data->id_entidad;
                $id_anho = $params->data->year;
                $id_cuentaempresarial = $params->data->id_cuentaempresarial;
                $id_tipoplan = $params->data->id_tipoplan;
                $id_cuentaaasi = $params->data->id_cuentaaasi;
                $id_restriccion = $params->data->id_restriccion;
                try{
                    $data = AccountingData::addPlanAccountingEquivalent($id_entidad,$id_anho,$id_cuentaempresarial,$id_tipoplan,$id_cuentaaasi,$id_restriccion);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = $data[0];
                    $code = "201";
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updatePlanAccountingEquivalent($id_cuentaempresarial){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $id_entidad = $params->id_entidad;
                $id_anho = $params->year;
                $id_cuentaempresarial = $params->id_cuentaempresarial;
                $id_tipoplan = $params->id_tipoplan;
                $id_cuentaaasi = $params->id_cuentaaasi;
                $id_restriccion = $params->id_restriccion;
                try{
                    $data = AccountingData::updatePlanAccountingEquivalent($id_entidad,$id_anho,$id_cuentaempresarial,$id_tipoplan,$id_cuentaaasi,$id_restriccion);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }catch(Exception $e){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listAccountingEntry(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida ==='SI'){
            $id_entidad = $request->query('id_entidad');
            $id_depto  = $request->query('id_depto');
            $id_anho  = $request->query('id_anho');
            $id_modulo  = $request->query('id_modulo');
            try{
                //$data = AccountingData::listAccountingEntry($id_entidad,$id_depto,$id_anho,$id_modulo);
                $data = AccountingData::listRecursivaAccountingEntry($id_entidad,$id_depto,$id_anho,$id_modulo,0);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listMyAccountingEntry(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];
        if($valida === 'SI'){
            $id_anho  = $request->query('id_anho');
            $id_modulo  = $request->query('id_modulo');
            try{
                $data = AccountingData::listAccountingEntry($id_entidad,$id_depto,$id_anho,$id_modulo);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = $data;
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listAccountingEntryModule(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_modulo  = $request->query('id_modulo');
            $id_tipoventa = $request->query('id_tipoventa');
            $codigo = $request->query('codigo');
            $id_almacen = $request->query('id_almacen');
            try{
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                }
                $data = AccountingData::listAccountingEntryModule($id_entidad,$id_depto,$id_anho,$id_modulo,$id_tipoventa,$codigo,$id_almacen);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = ['items' => []];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['message'] = substr($e->getMessage(),34,33);
            }
        }
        return response()->json($jResponse,$code);
    }


    public function listAccountingEntryModuleResidence(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_modulo  = $request->query('id_modulo');
            $codigo = $request->query('codigo');
            try{
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                }
                // dd($id_entidad,$id_depto,$id_anho,$id_modulo,$codigo);
                $data = AccountingData::listAccountingEntryModuleResidence($id_entidad,$id_depto,$id_anho,$id_modulo,$codigo);

                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['message'] = substr($e->getMessage(),34,33);
            }
        }
        return response()->json($jResponse,$code);
    }

    // como jugando
    public function listAccountingEntryModuleAlmacen(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $id_modulo  = $request->query('id_modulo'); //
            $id_tipoventa = $request->query('id_tipoventa');
            $codigo = $request->query('codigo');

            $tipo_transaccion = $request->query('tipo_transaccion'); //
            $id_almacen = $request->query('id_almacen');
            if (!$id_tipoventa && $tipo_transaccion) {
                $transaccion = DB::select("SELECT
                    B.ID_TIPOTRANSACCION
                    FROM TIPO_GRUPO_CONTA A JOIN TIPO_TRANSACCION B
                    ON A.ID_TIPOGRUPOCONTA = B.ID_TIPOGRUPOCONTA
                    WHERE A.CODIGO = '$tipo_transaccion'");

                if ($transaccion) {
                    $id_tipoventa = $transaccion[0]->id_tipotransaccion;
                }
            }

            try{
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                }
                if (empty($id_almacen)) {
                    $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                    foreach ($warehouse as $key => $item){
                        $id_almacen = $item->id_almacen;
                    }
                }
                // si es Almacen Hijo, se obtiene la dinamica del parent
                $warehouseParent = WarehousesData::showWarehousesUserParent($id_almacen);
                if(count($warehouseParent)>0){
                    foreach ($warehouseParent as $key => $item){
                        $id_almacen_p = $item->id_parent;
                    }
                    if($id_almacen_p != ""){
                        $id_almacen = $id_almacen_p;
                    }
                }

                $data = AccountingData::listAccountingEntryModuleAlmacen($id_entidad,$id_depto,$id_anho,$id_modulo,$id_tipoventa,$codigo, $id_almacen);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['message'] = substr($e->getMessage(),34,33);
            }
        }
        return response()->json($jResponse,$code);
    }

    public function showAccountingEntry($id_dinamica){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::showAccountingEntry($id_dinamica);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
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
        }
        return response()->json($jResponse,$code);
    }
    public function addAccountingEntry(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_entidad = $params->data->id_entidad;
            $id_depto = $params->data->id_depto;
            $id_anho = $params->data->id_anho;
            $id_modulo = $params->data->id_modulo;
            $id_tipoigv = $params->data->id_tipoigv;
            $nombre = $params->data->nombre;
            $importe = $params->data->importe;
            $activo = $params->data->activo;
            $id_tipoventa = $params->data->id_tipoventa;
            $comentario = $params->data->comentario;
            $id_almacen = $params->data->id_almacen;
            try{
                $ip = GlobalMethods::ipClient($this->request);
                $data = AccountingData::addAccountingEntry($id_entidad,$id_depto,$id_anho,$id_modulo,$id_tipoigv,$nombre,
                $importe,$activo,$id_user,$ip,$id_tipoventa,$comentario,$id_almacen);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data[0];
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

    public function cloneAccountingEntryByEntity(Request $request, $id_entidad){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $data = Input::all();
            $validador = Validator::make($data,  ['id_anho_to' => 'required', 'id_anho_from' => 'required']);
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();;
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $id_anho_to = Input::get('id_anho_to');
            $id_anho_from = Input::get('id_anho_from');

            $pdo = DB::getPdo();
            DB::beginTransaction();
            try{
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $stmt = $pdo->prepare("begin eliseo.scp_dinamic_clone_by_year(:v_ID_ENTIDAD, :v_ID_ANHO_TO,
                :v_ID_ANHO_FROM, :v_ERROR, :v_MSGERROR); end;");
                $stmt->bindParam(':v_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':v_ID_ANHO_TO', $id_anho_to, PDO::PARAM_INT);
                $stmt->bindParam(':v_ID_ANHO_FROM', $id_anho_from, PDO::PARAM_INT);
                $stmt->bindParam(':v_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':v_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function cloneAccountingEntryByid(Request $request, $id_dinamica){
        $jResponse = GlobalMethods::authorizationLamb($request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $data = Input::all();
            $validador = Validator::make($data,  ['id_anho_to' => 'required']);
            if($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();;
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $id_anho_to = Input::get('id_anho_to');

            $pdo = DB::getPdo();
            DB::beginTransaction();
            try{
                $error = 0;
                $msgerror = "";
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $stmt = $pdo->prepare("begin eliseo.scp_dinamic_clone_by_id(:v_ID_DINAMICA, :v_ID_ANHO_TO,
                :v_ERROR, :v_MSGERROR); end;");
                $stmt->bindParam(':v_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':v_ID_ANHO_TO', $id_anho_to, PDO::PARAM_INT);
                $stmt->bindParam(':v_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':v_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if($error === 1) {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msgerror;
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }
                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = 'Success';
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function updateAccountingEntry($id_dinamica){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->data->nombre;
            $importe = $params->data->importe;
            $id_tipoventa= $params->data->id_tipoventa;
            $comentario= $params->data->comentario;
            try{
                $ip = GlobalMethods::ipClient($this->request);
                $data = AccountingData::updateAccountingEntry($id_dinamica,$nombre,$importe,$id_user,
                $ip,$id_tipoventa,$comentario);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
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
    public function AccountingEntry($id_dinamica){
        // $jResponse = [
        //     'success' => false,
        //     'message' => 'Recurso no Autorizado',
        //     'data' => []
        // ];
        // $code = "401";
        // $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        // if ($token) {
        //     session_id($token);
        //     session_start();
        //     $bindings = [
        //         'p_token' => $token
        //     ];
        //     $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
        //     $valida = $result[0];
        //     if( $valida->active == 'SI'  ){
        $activo = $params->data->activo;
        try{
            // if($activo == "S"){
            //     $valida_dc = AccountingData::AccountingEntryValida($id_dinamica);
            //     if($valida_dc == 0){
            //         $data = AccountingData::AccountingEntry($id_dinamica,$activo);
            //         $jResponse['success'] = true;
            //         $jResponse['message'] = "The item was updated successfully";
            //         $jResponse['data'] = $data[0];
            //     }else{
            //         $jResponse['success'] = False;
            //         $jResponse['message'] = "No esta Configurado Correctamente el Asiento";
            //         $jResponse['data'] = [];
            //     }
            // }else{
                $data = AccountingData::AccountingEntry($id_dinamica,$activo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
            // }
            $code = "200";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        //     }
        // }
        return response()->json($jResponse,$code);
    }
    // public function listDynamicArticles(Request $request){
    //     $jResponse = GlobalMethods::authorizationLamb($this->request);
    //     $code   = $jResponse["code"];
    //     $valida = $jResponse["valida"];
    //     $id_user = $jResponse["id_user"];
    //     if($valida=='SI'){
    //         $jResponse=[];
    //         try{
    //             $text_search = $request->text_search;
    //             $data = AccountingData::listDynamicArticles($text_search);
    //             if(count($data)>0){
    //                 $jResponse['message'] = "success";
    //                 $jResponse['data'] = $data;
    //                 $code = "200";
    //             }else{
    //                 $jResponse['message'] = "The item does not exist";
    //                 $jResponse['data'] = [];
    //                 $code = "203";
    //             }
    //         }catch(Exception $e){
    //             $jResponse['success'] = true;
    //             $jResponse['message'] = $e->getMessage();
    //             $jResponse['data'] = null;
    //             $code = "400";
    //         }
    //     }
    //     return response()->json($jResponse,$code);
    // }

    public function deleteAccountingEntry($id_dinamica){

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];

            try{
                $data = AccountingData::listAccountingEntryDetails($id_dinamica);
                if(count($data)>0){
                    $jResponse['success'] = False;
                    $jResponse['message'] = "The Countable  Accounting can not be deleted, Exist Items";
                    $jResponse['data'] = [];
                    $code = "400";
                }else{
                    //auditoria log
                    $delete=[
			'tabla'=>'CONTA_DINAMICA',//nombre tabla
                        'pk'=>array('ID_DINAMICA'),//pk de la tabla, no incluir en columna
			'columna'=>array('NOMBRE','IMPORTE','ACTIVO'),//columnas a auditar minimo una columna,se recomienda colocar los campos en trigger
			'where'=>array('ID_DINAMICA'=>$id_dinamica),//condicion para eliminar debe ser igual a los parametros de metodo eliminar
                        'id_user'=>$id_user,//usuario
                        'dataeliminar'=>[]
                    ];

                    //antes de eliminar recuparar data
                    $delete["dataeliminar"]=GlobalMethods::recuperarDatosDelete($delete);

                    $data = AccountingData::deleteAccountingEntry($id_dinamica);

                    //ejecuta el log
                    GlobalMethods::logDelete($delete);

                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";


                }
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }

        }
        return response()->json($jResponse,$code);
    }
    public function listAccountingEntryDetails(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                $id_dinamica = $request->query('id_dinamica');
                try{
                    //$data = AccountingData::listAccountingEntryDetails($id_dinamica);
                    $data = AccountingData::listAccountingRecursivoEntryDetails($id_dinamica,0);

                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "203";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showAccountingEntryDetails($id_asiento){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI'  ){
                try{
                    $data = AccountingData::showAccountingEntryDetails($id_asiento);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = ['items' => $data];
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "203";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addAccountingEntryDetails(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_dinamica = $params->data->id_dinamica;
            $id_tipoplan = $params->data->id_tipoplan;
            $id_cuentaaasi = $params->data->id_cuentaaasi;
            $id_restriccion = $params->data->id_restriccion;
            $id_cuentaempresarial = $params->data->id_cuentaempresarial;
            $nombre = $params->data->nombre;
            $dc = $params->data->dc;
            $activo = $params->data->activo;
            $id_entidad = $params->data->id_entidad;
            $depto = $params->data->depto;
            $porcentaje = $params->data->porcentaje;
            $unico = $params->data->unico;
            $nro_asiento= $params->data->nro_asiento;
            $destino= $params->data->destino;
            $indicador= $params->data->indicador;
            $unico_ctacte= $params->data->unico_ctacte;
            $agrupa= $params->data->agrupa;
            $ctacte= $params->data->ctacte;
            $id_parent=$params->data->id_parent;
            $id_fondo=$params->data->id_fondo;
            $primario=$params->data->primario;
            try{
                $data = AccountingData::addAccountingEntryDetails($id_dinamica,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$id_cuentaempresarial,$nombre,$dc,$activo,$id_entidad,$depto,$porcentaje,$unico,$nro_asiento,$destino,$indicador,$unico_ctacte,$agrupa,$ctacte,$id_parent,$id_fondo,$primario);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was created successfully";
                $jResponse['data'] = $data[0];
                $code = "201";
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "200";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateAccountingEntryDetails($id_asiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_cuentaaasi = $params->data->id_cuentaaasi;
            $id_restriccion = $params->data->id_restriccion;
            $id_cuentaempresarial = $params->data->id_cuentaempresarial;
            $nombre = $params->data->nombre;
            $dc = $params->data->dc;
            $id_entidad = $params->data->id_entidad;
            $depto = $params->data->depto;
            $porcentaje = $params->data->porcentaje;
            $unico = $params->data->unico;
            $nro_asiento= $params->data->nro_asiento;
            $destino= $params->data->destino;
            $indicador= $params->data->indicador;
            $unico_ctacte= $params->data->unico_ctacte;
            $agrupa= $params->data->agrupa;
            $ctacte= $params->data->ctacte;
            $id_fondo= $params->data->id_fondo;
            $primario= $params->data->primario;
            try{
                $ip = GlobalMethods::ipClient($this->request);
                $data = AccountingData::updateAccountingEntryDetails($id_asiento,$id_cuentaaasi,$id_restriccion,$id_cuentaempresarial,$nombre,$dc,$id_entidad,$depto,$porcentaje,$unico,$nro_asiento,$destino,$indicador,$unico_ctacte,$agrupa,$ctacte, $id_fondo, $primario);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "201";
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "200";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function AccountingEntryDetails($id_asiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $activo = $params->data->activo;;
            try{
                $ip = GlobalMethods::ipClient($this->request);
                $data = AccountingData::AccountingEntryDetails($id_asiento,$activo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "201";
            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "200";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteAccountingEntryDetails($id_asiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::deleteAccountingEntryDetails($id_asiento);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listRestriccionAccounting(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_tipoplan = $request->query('id_tipoplan');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            try{
                $data = AccountingData::listRestriccionAccounting($id_tipoplan,$id_cuentaaasi);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPlanAccountingEnterpriseSearch(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            //$params = json_decode(file_get_contents("php://input"));
            $id_anho = $request->query('id_anho');
            $id_entidad = $request->query('id_entidad');
            $id_tipoplan = $request->query('id_tipoplan');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            try{
                $data = AccountingData::listPlanAccountingEnterpriseSearch($id_anho,$id_entidad,$id_tipoplan,$id_cuentaaasi);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "204";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }

        return response()->json($jResponse,$code);
    }
    public function listPlanAccountingEnterpriseSearchV2(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];
        $code = "401";
        $token = $this->request->header('Authorization');
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];
            if( $valida->active == 'SI' ){
                $q = $request->query('q');
                $per_page = $request->query('perPage', 15);
                $page = $request->query('page', 1);
                try{
                    $data = AccountingData::listPlanAccountingEnterpriseSearchV2($q, $per_page, $page);
                    $jResponse['success'] = true;
                    if(count($data)>0){
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data;
                        $code = "200";
                    }else{
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "204";
                    }
                }catch(Exception $e){
                    $jResponse['message'] = $e->getMessage();
                }
            }

        }
        return response()->json($jResponse,$code);
    }
    public function listMoneda(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida === 'SI') {
            $jResponse = [];
            try {
                $data = AccountingData::listMoneda();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listTipoCambio(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_anho = $request->query('id_anho');
            $id_mes = $request->query('id_mes');
            $id_moneda_main = $request->query('id_moneda_main');
            $id_moneda = $request->query('id_moneda');
            try{
                $data = AccountingData::listTipoCambio($id_anho,$id_mes,$id_moneda_main,$id_moneda);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
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

    public function addTipoCambio(Request $request) {
        // $jResponse = GlobalMethods::authorizationLamb($this->request);
        // $code   = $jResponse["code"];
        // $valida = $jResponse["valida"];
        // $id_user = $jResponse["id_user"];
        // if($valida=='SI'){
        $jResponse=[];
        $this->validate($request, [
            'fecha' => 'required|date_format:Y-m-d|before:tomorrow',
            'compra' => 'required|numeric|min:0|not_in:0',
            'venta' => 'required|numeric|min:0|not_in:0',
            'denominacional' => 'required|numeric',
            'moneda_main' => 'required',
            'moneda_exchange' => 'required'
        ]);
        $params = json_decode(file_get_contents("php://input"));

        $fecha = $params->fecha;
        $compra = $params->compra;
        $venta = $params->venta;
        $denominacional = $params->denominacional;
        $moneda_main = $params->moneda_main;
        $moneda = $params->moneda_exchange;

        // $jResponse['success'] = false;
        // $jResponse['message'] = "Wait: ".$fecha;
        // $jResponse['data'] = [];
        // return response()->json($jResponse,422);

        $first = DB::table("TIPO_CAMBIO")
                    ->where('fecha', $fecha)
                    ->where('id_moneda_main', $moneda_main)
                    ->where('id_moneda', $moneda)
                    ->first();
        if($first) {
            return $this->errorResponse("Ya existe tipo cambio para: ".$fecha);
        }

        // validacion// OJO
        try{
            $data = AccountingData::addTipoCambio($fecha,$compra,$venta,$denominacional,$moneda_main,$moneda);
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully";
            $jResponse['data'] = $data;
            $code = "200";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "422";
        }
        return response()->json($jResponse,$code);
    }
    /*public static $rules_save = [
        'class_subjects' => ['required', 'regex:/[0-9]([0-9]|-(?!-))+/'],
    ];*/
    public static function rulesRetencion() {
        return [
            // 'retencion_forma_pago' => 'required',
            // 'retencion_cuenta_bancaria' => 'required',
            /*'retencion_nro' => 'required|max:8',
            'retencion_serie' => 'required|max:4',
            'retencion_fecha' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'retencion_importe' => 'required|numeric',
            'retencion_id_voucher_mb' => 'required',*/
            'buy' => 'required|regex:/^\d+(\.\d{1,3})?$/'
        ];
    }

    public function getTipoCambioBySunat($anho, $mes) {
        // Dataos extraidos de la SUNAT
        $params = array('year' => $anho, 'month' => $mes);
        $url = 'https://api.apis.net.pe/v1/tipo-cambio-sunat?'.http_build_query($params);
        return json_decode(file_get_contents($url), true);
    }

    public function getTipoCambioChile($anho, $mes) {
        // Datos extraidos del Banco Central de Chile
        $url = 'https://mindicador.cl/api/dolar/'.$anho;
        $data = json_decode(file_get_contents($url), true)['serie'];
        $filteredByMonth = array_filter($data, function($item) use ($mes) {
            return Carbon::parse($item['fecha'])->month == $mes;
        });
        return $filteredByMonth;
    }

    public function actualizarTipcoCambio($item, $moneda_main, $moneda) {
        $anho = Carbon::parse($item['fecha'])->format('Y');
        $mes = Carbon::parse($item['fecha'])->format('m');
        $adia = Carbon::parse($item['fecha'])->format('d');
        $acompra = $item['compra'] ?? $item['valor'];
        $aventa = $item['venta'] ?? $item['valor'];
        $diasact = [];
        $multi = false;
        SalesData::actualizarTipcoCambio($anho, $mes, $adia, $acompra, $aventa, $diasact, $moneda_main, $moneda, $multi);
    }

    public function TipoCambio($anho, $mes, $moneda_main, $moneda){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida === 'SI') {
            $anho = $anho ?? date("Y");
            $mes = $mes ?? date("m");

            try {
                $data = [];
                $jResponse = [];
                switch ("$moneda_main-$moneda") {
                    case '7-9':
                        $data = $this->getTipoCambioBySunat($anho, $mes);
                        $jResponse = ['success' => true, 'message' => "The item was updated successfully", 'data' => []];
                        $code = '200';
                        break;
                    case '4-9':
                        $data = $this->getTipoCambioChile($anho, $mes);
                        $jResponse = ['success' => true, 'message' => "The item was updated successfully", 'data' => []];
                        $code = '200';
                        break;
                    default:
                        $data = [];
                        $jResponse = ['success' => false, 'message' => "Tipo de cambio no disponible", 'data' => []];
                        $code = '202';
                        break;
                }

                foreach ($data as $item) {
                    $this->actualizarTipcoCambio($item, $moneda_main, $moneda);
                }

            } catch(Exception $e) {
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }

    public function updateTipoCambio(Request $request,$fecha){
        // $jResponse = GlobalMethods::authorizationLamb($this->request);
        // $code   = $jResponse["code"];
        // $valida = $jResponse["valida"];
        // $id_user = $jResponse["id_user"];
        // if($valida=='SI'){
        //     $jResponse=[];
        $request->merge(['fecha' => $fecha]);
        $this->validate($request, [
            'fecha' => 'required|date_format:Y-m-d|before:tomorrow',
            'compra' => 'required|numeric|min:0|not_in:0',
            'venta' => 'required|numeric|min:0|not_in:0',
            'denominacional' => 'required|numeric',
            'id_moneda_main' => 'required',
            'id_moneda' => 'required'
        ]);

        $params = json_decode(file_get_contents("php://input"));
        $compra = $params->compra;
        $venta = $params->venta;
        $denominacional = $params->denominacional;
        $id_moneda_main = $params->id_moneda_main;
        $id_moneda = $params->id_moneda;
        try{
            // $ip = GlobalMethods::ipClient($this->request);
            $data = AccountingData::updateTipoCambio($fecha,$compra,$venta,$denominacional,$id_moneda_main,$id_moneda);
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully";
            // $jResponse['data'] = $data[0];
            $jResponse['data'] = $data;
            $code = "200";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "201";
        }
        // }
        return response()->json($jResponse,$code);
    }

    public function deleteTipoCambio(Request $request, $fecha, $id_moneda_main, $id_moneda){
        try{
            DB::table('eliseo.TIPO_CAMBIO')
                    ->where('ID_MONEDA', $id_moneda)
                    ->where('ID_MONEDA_MAIN', $id_moneda_main)
                    ->where('FECHA', $fecha)
                    ->delete();
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was deleted successfully";
            $jResponse['data'] = [];
            $code = "200";
        }catch(Exception $e){
            $jResponse['success'] = true;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "422";
        }
        return response()->json($jResponse,$code);
    }

    public function listIndicador(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $data = AccountingData::listIndicador();
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully";
            $jResponse['data'] = $data;
            $code = "200";
        }
        return response()->json($jResponse,$code);
    }

    public function seatDeptoUnico(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $data = AccountingData::listDeptoUnico();
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully";
            $jResponse['data'] = $data;
            $code = "200";
        }
        return response()->json($jResponse,$code);
    }

    public function seatCtaCteUnico(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $data = AccountingData::listCtaCteUnico();
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully";
            $jResponse['data'] = $data;
            $code = "200";
        }
        return response()->json($jResponse,$code);
    }

    public function listDeptoAsientoAccounting(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $jResponse=[];
            $id_asiento = $request->query('id_asiento');
            try{
                $data = AccountingData::listDeptoAsientoAccounting($id_asiento);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listCtaCteAsientoAccounting(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $jResponse=[];
            $id_asiento = $request->query('id_asiento');
            try{
                $data = AccountingData::listCtaCteAsientoAccounting($id_asiento);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listCtaCteAccountingV2(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $id_tipoplan = $request->query('id_tipoplan');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            $id_restriccion = $request->query('id_restriccion');
            $id_dinamica    = $request->query("id_dinamica");

            $search_term = $request->query('search_term');
            $per_page = $request->query('per_page', 15);
            $page = $request->query('page', 1);
            // $nombre    = $request->query("nombre");
            //$page    = $request->query("page");
            $all = "";
            try{
                $data = AccountingData::listCtaCteAccountingV2($id_entidad,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$search_term,$per_page, $page, $all);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listCtaCteAccounting(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $id_tipoplan = $request->query('id_tipoplan');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            $id_restriccion = $request->query('id_restriccion');
            $id_dinamica    = $request->query("id_dinamica");
            $nombre    = $request->query("nombre");
            //$page    = $request->query("page");
            $all = "";
            try{
                $data = AccountingData::listCtaCteAccounting($id_entidad,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$nombre,$all);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listCheckingAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = array(
                'id_entidad' => $id_entidad,
                'id_tipoctacte' => $request->query('id_tipoctacte'),
                'text_search' => $request->query('text_search'),
                'page_size' => $request->query('page_size') ? $request->query('page_size') : 10
            );
            try{
                $data = AccountingData::listCheckingAccount($params);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = $data;
                $code = "200";

            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listCtaCteAccountingSearch(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $id_tipoplan = $request->query('id_tipoplan');
            $id_cuentaaasi = $request->query('id_cuentaaasi');
            $id_restriccion = $request->query('id_restriccion');
            $nombre    = $request->query("nombre");
            $all = "";
            try{
                $data = AccountingData::listCtaCteAccountingSearch($id_entidad,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$nombre);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = $e->getMessage();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listDepositoAccountingEntry(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto  = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_entidad = $request->query('id_entidad');
            $id_depto  = $request->query('id_depto');
            $id_anho  = $request->query('id_anho');
            $id_dinamica  = $request->query('id_dinamica');
            try{
                $id_modulo=14;//tesoria
                $data = AccountingData::listDepositoAccountingEntry($id_entidad,$id_depto,$id_anho,$id_modulo,$id_dinamica);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
                }
            }catch(Exception $e){
                $jResponse['message'] = substr($e->getMessage(),34,33);
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateRecursivoAccountingEntry($id_dinamica){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_parent= $params->data->id_parent;
            try{
                $data = AccountingData::updateRecursivoAccountingEntry($id_dinamica,$id_parent);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "200";
            }
        }

        return response()->json($jResponse,$code);
    }
    public function listVoucherModules(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{

                $id_anho = $request->query('id_anho');

                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                    }
                }

                $id_mes   = $request->query('id_mes');
                $id_tipovoucher   = $request->query('id_tipovoucher');
                $data = array(
                    array("id_voucher"=>"0","fecha"=>"","numero"=>"todos","lote"=>"","activo"=>"S")
                );
                $data = AccountingData::listVoucherModules($id_user,$id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listVoucherModulesAasinet(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho   = $request->query('id_anho');
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                    }
                }

                $id_mes   = $request->query('id_mes');

                $id_tipovoucher   = $request->query('id_tipovoucher');
                $data = array(
                    array("id_voucher"=>"0","fecha"=>"","numero"=>"todos","lote"=>"","activo"=>"S")
                );
                $data = AccountingData::listVoucherModulesAasinet($id_user,$id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher);
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listMyVoucherModules(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho   = $request->query('id_anho');
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                    }
                }
                $id_mes   = $request->query('id_mes');
                $id_tipovoucher   = $request->query('id_tipovoucher');
                $all_vouchers   = filter_var($request->query('all_vouchers'), FILTER_VALIDATE_BOOLEAN);

                $data = AccountingData::listMyVoucherModules($id_user,$id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher, $id_user, $all_vouchers);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function deleteMyVoucher(Request $request, $id_voucher){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{

                AccountingData::deleteContaVoucherUser($id_voucher);
                AccountingData::deleteContaVoucher($id_voucher);
                /*if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }*/
                $jResponse['message'] = "Success";
                $jResponse['data'] = [];
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

    public function listTypeVoucher(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::listTypeVoucher();
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function assignVouchers(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_voucher = $params->id_voucher;
            $id_cvoucher = $params->id_cvoucher;
            try{
                $yaEstaAsignado = AccountingData::listAssignVouchers($id_voucher,$id_user);
                if(count($yaEstaAsignado) === 0){
                    $data = AccountingData::assignVouchers($id_voucher,$id_user,$id_cvoucher);
                } else {
                    $data = AccountingData::desassignVoucher($id_voucher,$id_user);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = $id_voucher;
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = true;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function showVoucherModules(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try {
                // $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                // foreach ($data_anho as $item){
                //     $id_anho = $item->id_anho;
                // }
                $id_tipovoucher   = $request->query('id_tipovoucher');
                $data = array(
                    array("id_voucher"=>"0","fecha"=>"","numero"=>"todos","lote"=>"","activo"=>"S")
                );
                // dd($id_user,$id_entidad,$id_depto,$id_anho,$id_tipovoucher);
                // $data = AccountingData::showVoucherModules($id_user,$id_entidad,$id_depto,$id_anho,$id_tipovoucher);
                $data = AccountingData::showVoucherModules($id_user,$id_entidad,$id_depto,$id_tipovoucher);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = null;
                    $code = "202";
                }
            } catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listCurrentAccounts(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho   = $request->query('id_anho');
                $id_mes   = $request->query('id_mes');
                $id_cuentaaasi   = $request->query('id_cuentaaasi');
                $id_ctacte   = $request->query('id_ctacte');
                $items = AccountingData::listCurrentAccounts($id_entidad,$id_anho,$id_mes,$id_cuentaaasi,$id_ctacte);
                $total = AccountingData::listCurrentAccountsTotal($id_entidad,$id_anho,$id_mes,$id_cuentaaasi,$id_ctacte);
                $jResponse['success'] = true;
                if(count($items)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $items, 'total' => $total];
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
    public function listGroupLevel(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho   = $request->query('id_anho');
                $id_tiporeporte   = $request->query('id_tiporeporte');
                $data = AccountingController::recursiveGroupLevel($id_entidad,$id_anho,$id_tiporeporte,"A");
                if ($data) {
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
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function recursiveGroupLevel($id_entidad,$id_anho,$id_tiporeporte,$id_parent){
        $parent = [];
        $data = AccountingData::listGroupLevel($id_entidad,$id_anho,$id_tiporeporte,$id_parent);
        foreach ($data as $key => $value){
            $row = $this->recursiveGroupLevel($id_entidad,$id_anho,$id_tiporeporte,$value->id_nivel);
            $parent[] = [
                            'id_nivel' => $value->id_nivel,
                            'id_parent' => $value->id_parent,
                            'id_entidad' => $value->id_entidad,
                            'id_tiporeporte' => $value->id_tiporeporte,
                            'nombre' => $value->nombre,
                            'nivel' => $value->nivel,
                            'children' => $row
                        ];
        }
        return $parent;
    }
    public function addGroupLevel(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));

            $id_parent = $params->id_parent;
            $id_tiporeporte = $params->id_tiporeporte;
            $nombre = $params->nombre;
            $nivel = $params->nivel;
            try{
                $data = AccountingData::addGroupLevel($id_parent,$id_entidad,$id_tiporeporte,$nombre,$nivel);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function updateGroupLevel($id_nivel){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));

            $id_parent = $params->id_parent;
            $id_tiporeporte = $params->id_tiporeporte;
            $nombre = $params->nombre;
            $nivel = $params->nivel;
            try{
                $data = AccountingData::updateGroupLevel($id_nivel,$id_parent,$id_entidad,$id_tiporeporte,$nombre,$nivel);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function deleteGroupLevel($id_nivel){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::deleteGroupLevel($id_nivel);
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
    public function listGroupLevelDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_nivel   = $request->query('id_nivel');
                $id_anho   = $request->query('id_anho');
                $data = AccountingData::listGroupLevelDetails($id_nivel,$id_anho,$id_entidad);
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addGroupLevelDetails(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));

            $id_nivel = $params->id_nivel;
            $id_anho = $params->id_anho;
            $depto = $params->depto;
            try{
                AccountingData::deleteGroupLevelDetails($id_nivel,$id_anho);
                foreach ($depto as $id_depto){
                    $data = AccountingData::addGroupLevelDetails($id_nivel,$id_anho,$id_entidad,$id_depto);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function listEntityDepto(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_nivel  = $request->query('id_nivel');
                $id_anho  = $request->query('id_anho');
                if (is_null($id_nivel)){
                    $id_nivel = '0';
                }

                $data = AccountingController::recursiveLevelDepto($id_entidad,"A",$id_nivel,$id_anho);
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = ['items' => $data[0]];
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse);
    }
    public function recursiveLevelDepto($id_entidad,$id_parent,$id_nivel,$id_anho){
        $parent = [];
        $data = AccountingData::LevelDepto($id_entidad,$id_parent,$id_nivel,$id_anho);
        $checked = false;
        foreach ($data as $key => $value){
            if($value->asignado == 0){
                $checked = false;
            }else{
                $checked = true;
            }
            $row = $this->recursiveLevelDepto($id_entidad,$value->id_depto,$id_nivel,$id_anho);
            $parent[] = ['value' => $value->id_depto, 'text' => $value->nombre,'checked'=>$checked,'children'=>$row];
        }
        return $parent;
    }
    public function listLevelParent(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::listDeptoParent($id_entidad);
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listGroupAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho   = $request->query('id_anho');
                $id_tiporeporte   = $request->query('id_tiporeporte');
                $data = AccountingController::recursiveGroupAccount($id_entidad,$id_anho,$id_tiporeporte,"A");
                if ($data) {
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
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteGroupAccountV2($id_cuenta){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = $id_cuenta;
                $data = AccountingData::deleteGroupAccountV2($id_cuenta);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function recursiveGroupAccount($id_entidad,$id_anho,$id_tiporeporte,$id_parent){
        $parent = [];
        $data = AccountingData::listGroupAccount($id_entidad,$id_anho,$id_tiporeporte,$id_parent);
        foreach ($data as $key => $value){
            $row = $this->recursiveGroupAccount($id_entidad,$id_anho,$id_tiporeporte,$value->id_cuenta);
            $parent[] = [
                            'id_cuenta' => $value->id_cuenta,
                            'id_parent' => $value->id_parent,
                            'id_entidad' => $value->id_entidad,
                            'id_tiporeporte' => $value->id_tiporeporte,
                            'nombre' => $value->nombre,
                            'orden' => $value->orden,
                            'children' => $row
                        ];
        }
        return $parent;
    }
    public function addGroupAccount(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));

            $id_parent = $params->id_parent;
            $id_tiporeporte = $params->id_tiporeporte;
            $nombre = $params->nombre;
            $orden = $params->orden;
            $dc = $params->dc;
            try{
                $data = AccountingData::addGroupAccount($id_parent,$id_entidad,$id_tiporeporte,$nombre,$orden,$dc);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function addGroupAccountDetailsV2(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));

            $id_cuenta = $params->id_cuenta;
            $id_anho = $params->id_anho;
            $cuentas = $params->cuentas;
            //$id_cuentaaasi = $params->id_cuentaaasi;
            //$cta_cte = $params->cta_cte;
            try{
                //AccountingData::deleteGroupLevelDetails($id_nivel,$id_anho);
                /*foreach ($cuenta as $id_cuentaaasi){
                    $data = AccountingData::addGroupAccountDetails($id_cuenta,$id_anho,$id_cuentaaasi,$cta_cte);
                }*/
                foreach ($cuentas as $key => $value){
                    $data = AccountingData::addGroupAccountDetailsV2($id_cuenta,$id_anho,$value -> id_cuentaaasi);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function addGroupAccountDetailsCteV2(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_cdetalle = $params->id_cdetalle;
            $cta_ctes = $params->cta_ctes;
            try{
                foreach ($cta_ctes as $key => $value){
                    $data  = AccountingData::addGroupAccountDetailsCte($id_cdetalle,$value->id_ctacte);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function addGroupAccountDetails(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));

            $id_cuenta = $params->id_cuenta;
            $id_anho = $params->id_anho;
            $cuenta = $params->cuenta;
            //$id_cuentaaasi = $params->id_cuentaaasi;
            //$cta_cte = $params->cta_cte;
            try{
                //AccountingData::deleteGroupLevelDetails($id_nivel,$id_anho);
                /*foreach ($cuenta as $id_cuentaaasi){
                    $data = AccountingData::addGroupAccountDetails($id_cuenta,$id_anho,$id_cuentaaasi,$cta_cte);
                }*/
                foreach ($cuenta as $key => $value){
                    $data = AccountingData::addGroupAccountDetails($id_cuenta,$id_anho,$value->id_cuentaaasi,$value->cta_cte);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function listGroupAccountDetailsV2(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_cuenta   = $request->query('id_cuenta');
                $id_anho   = $request->query('id_anho');
                $data = AccountingData::listGroupAccountDetailsV2($id_cuenta,$id_anho);
                if ($data) {
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
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listGroupAccountDetails(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_cuenta   = $request->query('id_cuenta');
                $id_anho   = $request->query('id_anho');
                $data = AccountingData::listGroupAccountDetails($id_cuenta,$id_anho);
                if ($data) {
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
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listGroupAccountCteV2(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_cuentaaasi   = $request->query('id_cuentaaasi');
                $id_anho   = $request->query('id_anho');
                $data = AccountingData::listGroupAccountCteV2($id_cuentaaasi,$id_anho, $id_entidad);
                if ($data) {
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
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listGroupAccountCTE(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_cuenta   = $request->query('id_cuenta');
                $id_anho   = $request->query('id_anho');
                $data = AccountingData::listGroupAccountCTE($id_cuenta,$id_anho);
                if ($data) {
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
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteCteByIdV2($id_cta_cte){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::deleteCteByIdV2($id_cta_cte);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function deleteGroupAccountDetails($id_cdetalle){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::deleteGroupAccountDetailsCte($id_cdetalle);
                $data = AccountingData::deleteGroupAccountDetails($id_cdetalle);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
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
    public function showAccountingEntryValidate(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_dinamica   = $request->query('id_dinamica');
                $data = AccountingData::showAccountingEntryValidate($id_dinamica);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function listAccountingEntryChooses(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_dinamica   = $request->query('id_dinamica');
                $data = AccountingData::showAccountingEntryValidate($id_dinamica);
                foreach ($data as $key => $value){
                    $obj = (object) $value;
                    $edit = $obj->edit;
                }
                if($edit == "S"){
                    $parent = [];
                    $id_indicador = "";
                    $dc = "";
                    $dato = AccountingData::listEntryAccount($id_dinamica);
                    foreach ($dato as $key => $value){
                        $dc = $value->dc;
                        $id_indicador = $value->id_indicador;
                    }
                    $dato = AccountingData::listEntryAccountCta($id_dinamica,$id_indicador,$dc);

                    foreach ($dato as $key => $value){
                        $depto = AccountingData::listEntryDepto($value->id_asiento);
                        $ctacte = AccountingData::listEntryCtaCte($value->id_asiento);
                        $parent[] = [
                                        'id_asiento' => $value->id_asiento,
                                        'id_cuentaaasi' => $value->id_cuentaaasi,
                                        'nombre' => $value->nombre,
                                        'porcentaje' => $value->porcentaje,
                                        'deptos' => $depto,
                                        'ctacte' => $ctacte
                                    ];
                    }
                    if(count($parent)>0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = $parent;
                        $code = "200";
                    }else{
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'The item does not exist';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'El Asiento Contable, NO es Editable';
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
    public function listTypeArrangements(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::listTypeArrangements();
                if ($data) {
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
    public function addArrangements(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_modulo = $params->id_modulo;
            $id_tipoarreglo = $params->id_tipoarreglo;
            $id_origen = $params->id_origen;
            $id_tipoorigen = property_exists($params, 'id_tipoorigen') ? $params->id_tipoorigen : null;
            if($id_modulo == 11 && $id_tipoorigen == null){
                $id_tipoorigen = 3;
            }
            $motivo = $params->motivo;
            $campos = array();
            if($id_tipoarreglo == 2){
                /*$campos['glosa'] = $params->glosa;
                $campos['importe'] = $params->importe;
                $campos['cuenta'] = $params->cuenta;
                $campos['nivel'] = $params->nivel;*/
                //$campos = $params->detalle;
            }
            try{
                $id_arreglo = AccountingData::addArrangements($id_entidad,$id_depto,$id_user,$id_modulo,$id_tipoarreglo,$id_origen,$motivo, $id_tipoorigen);
                $dice = 'a';
                foreach ($campos as $key => $value) {
                    $campo = $value->glosa;
                    $dice = $value->dice;
                    $d_dice = $value->d_decir;
                    $id_arreglo_entrada = property_exists($value, 'id_arreglo_entrada') ? $value->id_arreglo_entrada : '';
                    $id_referencia = property_exists($value, 'id_referencia') ? $value->id_referencia : '';
                    //if(strlen($value)>0){
                        //AccountingData::addArrangementsDetails($id_arreglo,$key,$dice,$value);
                    $content = array(
                        'ID_ARREGLO' => $id_arreglo,
                        'CAMPO'=> $campo,
                        'DICE' => $dice,
                        'DEBE_DECIR'=> $d_dice,
                        'ID_ARREGLO_ENTRADA'=> $id_arreglo_entrada,
                        'ID_REFERENCIA'=> $id_referencia);
                        AccountingData::addArrangementsDetails($content);
                    //}
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
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
    public function showTipoCambio($fecha){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::showTipoCambio($fecha);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
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
    public function listExternalSystemSeat(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_tipoasiento = $request->query('id_tipoasiento');
                $data = AccountingData::listExternalSystemSeat($id_entidad,$id_depto,$id_tipoasiento);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
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
    public function listExternalSystem(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $data = AccountingData::listExternalSystem($id_entidad,$id_depto);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
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
    public function showExternalSystem($id_sistemaexterno){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::showExternalSystem($id_sistemaexterno);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "203";
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
    public function addExternalSystem(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_entidad = $params->id_entidad;
            $id_depto = $params->id_depto;
            $id_tipoasiento = $params->id_tipoasiento;
            $codigo = $params->codigo;
            $nombre = $params->nombre;
            $estado = $params->estado;
            try{
                $data = AccountingData::addExternalSystem($id_entidad,$id_depto,$id_tipoasiento,$codigo,$nombre,$estado);
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
    public function updateExternalSystem($id_sistemaexterno){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $codigo = $params->codigo;
            $nombre = $params->nombre;
            $estado = $params->estado;
            try{
                $data = AccountingData::updateExternalSystem($id_sistemaexterno,$codigo,$nombre,$estado);
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
    public function deleteExternalSystem($id_sistemaexterno){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = AccountingData::deleteExternalSystem($id_sistemaexterno);
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
    public function listSeatAaasinet(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $msn = "";
            try{
                $id_voucher = $request->query('id_voucher');
                $id_modulo = $request->query('id_modulo');
                $id_operorigen = $request->query('id_operorigen');
                //$id_anho= $request->query('id_anho');
                $voucher = AccountingData::showVoucher($id_voucher);
                foreach ($voucher as $item){
                    $id_entidad = $item->id_entidad;
                    $id_depto = $item->id_depto;
                    $id_anho = $item->id_anho;
                    $id_tipoasiento = $item->id_tipoasiento;
                    $numero = $item->numero;
                    $fecha = $item->fecha;
                    $fecha_aasi = $item->fecha_aasi;
                    $periodo = $item->periodo;
                }
                // $codigo = 0;
                // $ex = AccountingData::ExternalSystem($id_entidad,$id_depto,$id_anho,$id_tipoasiento,$id_modulo);
                // foreach ($ex as $item){
                //     $codigo = $item->codigo;
                // }
                $empresa = AccountingData::ShowEntidadEmpresa($id_entidad);
                foreach ($empresa as $item){
                    $id_empresa = $item->id_empresa;
                }
                // if($codigo != 0){
                if($id_entidad == "7124" || $id_entidad == "9415"){
                    $data = AccountingData::listSeatAaasinetlamb($id_anho,$id_voucher,$id_empresa);
                }else{
                    $data = AccountingData::listSeatAaasinet($id_anho,$id_voucher,$id_empresa);
                }
                $jResponse['message'] = $msn;
                $jResponse['data'] = $data;
                $code = "200";
                // }else{
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = "No Existe External System";
                //     $jResponse['data'] = [];
                //     $code = "202";
                // }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function uploadSeatAaasinetSOA(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI'){
            $jResponse=[];
            $jResponse['result'] = "";
            $msn = "";
            try{
                $inicialesUsers = AccountingData::getInicialesNombreUsuario($id_user);
                foreach ($inicialesUsers as $item) {
                    $inicialesUser = $item->iniciales;
                }

                /*$id_voucher = $request->query('id_voucher');
                $id_modulo = $request->query('id_modulo');
                $id_operorigen = $request->query('id_operorigen');*/

                $params = json_decode(file_get_contents("php://input"));
                $id_voucher = $params->id_voucher;
                $id_modulo = $params->id_modulo;
                $id_operorigen = $params->id_operorigen;

                $voucher = AccountingData::showVoucher($id_voucher);
                foreach ($voucher as $item){
                    $id_entidad = $item->id_entidad;
                    $id_depto = $item->id_depto;
                    $id_anho = $item->id_anho;
                    $id_tipoasiento = $item->id_tipoasiento;
                    $numero = $item->numero;
                    $fecha = $item->fecha;
                    $fecha_aasi = $item->fecha_aasi;
                    $periodo = $item->periodo;
                    $id_tipovoucher = $item->id_tipovoucher;                }
                $codigo = 0;
                $ex = AccountingData::ExternalSystem($id_entidad,$id_depto,$id_anho,$id_tipoasiento,$id_modulo);
                foreach ($ex as $item){
                    $codigo = $item->codigo;
                    $url_aasinet = $item->url_aasinet."?wsdl";
                }
                if($id_operorigen == 3){//compras
                    $descripcion = $inicialesUser."::COMPRAS";
                }elseif($id_operorigen == 8){//cheques
                    if($id_tipovoucher == 6){
                        $descripcion = $inicialesUser."::TLC - RENDICION";
                    }else{
                        $descripcion = $inicialesUser."::CHEQUES";
                    }
                }elseif($id_operorigen == 9){//tlc
                    $descripcion = $inicialesUser."::TELECREDITO";
                }elseif($id_operorigen == 89){// Pagos Generales by @vitmar
                    $descripcion = $inicialesUser."::PAGOS";
                }elseif($id_operorigen == 1){// Pagos Generales by @vitmar
                    $descripcion = $inicialesUser."::VENTAS";
                }elseif($id_operorigen == 13){// Pagos Generales by @vitmar
                    $descripcion = $inicialesUser."::PEDIDOS";
                }else{
                    $descripcion = $inicialesUser."::INGRESOS";
                }
                //$jResponse['ex'] = $ex;
                //dd($ex);

                if($codigo != 0){
		    //dd("hola");
                    $data_xml = AccountingData::uploadSeatAaasinet($id_entidad,$id_depto,$id_anho,$id_operorigen,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion);
		    //dd($data_xml);
                    //
                    $contador          = 0;
                    $link              = "";
                    $context           = "";
                    $componente        = "";
                    $parameters        = "";
                    $description       = "";
                    $items             = "";
                    foreach($data_xml as $row){
                        $contador++;
                        if($contador == 1){
                            $link           = $row->url;
                            $context        = $row->context;
                            $componente     = $row->component;
                            $parameters     = $row->parameters;
                            $description    = $row->description;
                        }
                        $items              = $items.utf8_encode($row->items);
                    }
                    $component = "<AASIS>".$context.$componente.$parameters."<Content><JournalList><Journal>".$description."<Items>".$items."</Items></Journal></JournalList></Content></AASIS>";
                     //dd($component);
                    //$jResponse['componente'] = $component;
                    $jResponse['success'] = false;
                    $client = new SoapClient($link, array('exceptions' => 0));
                    //$msn = "Error de conexi&oacute;n Load <br/>";
                    $load = $client->Load(array("xmlPayload"=>$component));
                    //dd($load);
                    if (is_soap_fault($load)) {
                        //trigger_error("1 SOAP Fault: (faultcode: {$load->faultcode}, faultstring: {$load->faultstring})", E_USER_ERROR);
                        $msn = "Error de conexi&oacute;n Load <br/>";
                        $jResponse['mmsn'] = $msn;
                    }else{

                        $result = $client->Execute(array("payLoadKey"=>$load->LoadResult));
                        //$jResponse['result'] = $result;
                        $rs = (array)$result;
                        //$msn = $rs ;
                        if (is_soap_fault($result)) {
                            //trigger_error("ERROR: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
                            $msn = "Error de conexi&oacute;n Execute<br/>";
                            $jResponse['mmsn'] = $msn;
                        }else{
                            try{
                                $bodytag = $result->ExecuteResult;
                                $xml = simplexml_load_string($bodytag);//string convierte a xml

                                $lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalCode;

                                $tipo_lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalTypeCode;
                                $t_lote_aasi = $tipo_lote_aasinet." ".$lote_aasinet;
                                if($lote_aasinet != "X" && $lote_aasinet != "" ){
                                    //$bo_optimus->insert_update_lotes(s_mai_id(), $area, s_nivel(),$lote,$voucher,$lote_aasinet);
                                    //$bo_optimus->update_lote_cw(s_mai_id(), $area, s_nivel(),$lote,$voucher,$t_lote_aasi,'CO');
                                    //actualizar lote en conta_voucher
                                    AccountingData::updateVoucherLote($id_voucher,$t_lote_aasi,'N');
                                    $jResponse['success'] = true;
                                    $msn = "El voucher de ".$descripcion." número: ".$numero." fué registrado en Aasinet con lote nro: ".$tipo_lote_aasinet." ".$lote_aasinet;
                                }

                            }catch(Exception $e){
                                $msn = (string) $xml->Messages->Exceptions->string ;
                                //$msn = "el error es de Jesus";
                                $jResponse['success'] = false;
                                $jResponse['message'] = $msn." - ".$e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                            }
                        }
                    }
                }else{
                    $msn = "No Existe External System";
                }
                if($jResponse['success'] == true){
                    $jResponse['message'] = $msn;
                    $jResponse['data'] = $msn;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn."-ERROR";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $msn." - ".$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }


    public function uploadSeatAaasinet(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $TOKEN_SHELL_EXEC = "";
        $TOKEN_USER = "";
        $REST_URL_LOAD = "";
        $REST_URL_EXECUTE = "";
        $REST_CERTIFICADO = "";

        if($valida=='SI'){
            $jResponse=[];
            $jResponse['result'] = "";
            $msn = "";
            try{
                $inicialesUsers = AccountingData::getInicialesNombreUsuario($id_user);
                foreach ($inicialesUsers as $item) {
                    $inicialesUser = $item->iniciales;
                }
                /*$id_voucher = $request->query('id_voucher');
                $id_modulo = $request->query('id_modulo');
                $id_operorigen = $request->query('id_operorigen');*/

                $params = json_decode(file_get_contents("php://input"));
                $id_voucher = $params->id_voucher;
                $id_modulo = $params->id_modulo;
                $id_operorigen = $params->id_operorigen;

                $isTesting = property_exists($params, 'testing');

                $voucher = AccountingData::showVoucher($id_voucher);
                foreach ($voucher as $item){
                    $id_entidad = $item->id_entidad;
                    $id_depto = $item->id_depto;
                    $id_anho = $item->id_anho;
                    $id_tipoasiento = $item->id_tipoasiento;
                    $numero = $item->numero;
                    $fecha = $item->fecha;
                    $fecha_aasi = $item->fecha_aasi;
                    $periodo = $item->periodo;
                    $id_tipovoucher = $item->id_tipovoucher;                }
                $codigo = 0;
                $ex = AccountingData::ExternalSystem($id_entidad,$id_depto,$id_anho,$id_tipoasiento,$id_modulo);

                // add.
                $entidad_config_data = AccountingData::EntidadConfiguracion($id_entidad);


                foreach ($ex as $item){
                    $codigo = $item->codigo;
                    $url_aasinet = $item->url_aasinet."?wsdl";
                }

                // Cambio
                foreach($entidad_config_data as $row){
                    $TOKEN_SHELL_EXEC = $row->token_shell_exec;
                    $TOKEN_USER = $row->token_user;
                    $REST_URL_LOAD = $row->rest_url_load;
                    $REST_URL_EXECUTE = $row->rest_url_execute;
                    $REST_CERTIFICADO = $row->rest_certificado;
                }


                if($id_operorigen == 3){//compras
                    $descripcion = $inicialesUser."::COMPRAS";
                }elseif($id_operorigen == 8){//cheques
                    if($id_tipovoucher == 6){
                        $descripcion = $inicialesUser."::TLC - RENDICION";
                    }else{
                        $descripcion = $inicialesUser."::CHEQUES";
                    }
                }elseif($id_operorigen == 9){//tlc
                    $descripcion = $inicialesUser."::TELECREDITO";
                }elseif($id_operorigen == 89){// Pagos Generales by @vitmar
                    $descripcion = $inicialesUser."::PAGOS";
                }elseif($id_operorigen == 1){// Pagos Generales by @vitmar
                    $descripcion = $inicialesUser."::VENTAS";
                }elseif($id_operorigen == 13){// Pagos Generales by @vitmar
                    $descripcion = $inicialesUser."::PEDIDOS";
                }else{
                    $descripcion = $inicialesUser."::INGRESOS";
                }
                //$jResponse['ex'] = $ex;
                //dd($ex);

                if($codigo != 0){
                    //SI ES LA ENTIDAD 7124, VALIDA QUE LOS ASIENTOS ESTEN  REGISTRADOS SOLO EN SU NIVEL
                    if($id_entidad == 7124){
                        $cant = AccountingData::showValidatSeats($id_voucher,$id_depto);
                    }else{
                        $cant = 0;
                    }
                    if($cant == 0){
                        $data_xml = AccountingData::uploadSeatAaasinet($id_entidad,$id_depto,$id_anho,$id_operorigen,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                        // dd($data_xml);
                        $contador          = 0;
                        $link              = "";
                        $context           = "";
                        $componente        = "";
                        $parameters        = "";
                        $description       = "";
                        $items             = "";
                        foreach($data_xml as $row){
                            $contador++;
                            if($contador == 1){
                                $link           = $row->url;
                                $context        = $row->context;
                                $componente     = $row->component;
                                $parameters     = $row->parameters;
                                $description    = $row->description;
                            }
                            $items              = $items.utf8_encode($row->items);
                        }
                        $component = "<AASIS>".$context.$componente.$parameters."<Content><JournalList><Journal>".$description."<Items>".$items."</Items></Journal></JournalList></Content></AASIS>";
                        // dd($component);
                        //$jResponse['componente'] = $component;
                        $jResponse['success'] = false;
                        $me_status = 0;
                        $salida = shell_exec($TOKEN_SHELL_EXEC);
                        $token = str_replace("TOKEN:", "", $salida);
                        $token = preg_replace("[\n|\r|\n\r]", "", $token);
                        $dataraw = "{'UserName': '".$TOKEN_USER."','XmlContent': '".$component."'}";
                        if($isTesting) {
                            $resP = array(
                                'yyyy' => '____________',
                                CURLOPT_URL => $REST_URL_LOAD,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS =>" ".$dataraw." ",
                                CURLOPT_HTTPHEADER => array(
                                    "Authorization: Bearer ".$token." ",
                                    "Content-Type: text/plain"
                                ),
                            );
                            dd($resP);
                        }
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $REST_URL_LOAD,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS =>" ".$dataraw." ",
                            CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer ".$token." ",
                            "Content-Type: text/plain"
                            ),
                        ));

                        $response = curl_exec($curl);
                        $me_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        curl_close($curl);

                        if ($me_status == 200){
                            $payloadkey = $response;
                            $dataraw_execute = "{'UserName': '".$TOKEN_USER."','XmlContent': '".$payloadkey."'}";
                            $curl = curl_init();

                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $REST_URL_EXECUTE,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS =>" ".$dataraw_execute." ",
                                CURLOPT_HTTPHEADER => array(
                                "Authorization: Bearer ".$token." ",
                                "Content-Type: text/plain"
                                ),
                            ));

                            $response = curl_exec($curl);
                            $me_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                            curl_close($curl);
                            if ($me_status == 200){
                                try{
                                    $xml = simplexml_load_string($response);//string convierte a xml
                                    $lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalCode;
                                    $tipo_lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalTypeCode;
                                    $t_lote_aasi = $tipo_lote_aasinet." ".$lote_aasinet;

                                    if($lote_aasinet != "X" && $lote_aasinet != "" ){
                                        AccountingData::updateVoucherLote($id_voucher,$t_lote_aasi,'N');
                                        $jResponse['success'] = true;
                                        $msn = "El voucher de ".$descripcion." número: ".$numero." fué registrado en Aasinet con lote nro: ".$tipo_lote_aasinet." ".$lote_aasinet;
                                    }
                                }catch(Exception $e){
                                    $msn = (string) $xml->Messages->Exceptions->string ;
                                    $jResponse['success'] = false;
                                    $jResponse['message'] = $msn." - ".$e->getMessage();
                                    $jResponse['data'] = [];
                                    $code = "202";
                                }
                            }else {
                                echo "Execute Faild ...\n";
                                echo "me_status : $me_status\n";
                                echo "reponse\n";
                                echo $response ;
                            }

                        }else{
                            echo "Load Faild ...\n";
                            echo "me_status : $me_status\n";
                            echo "reponse\n";
                            echo $response ;
                        }
                    }else{
                        $msn = "Alto: Existen Asientos que estan registrados en departamentos diferentes al ".$id_depto." *** ";
                    }

                }else{
                    $msn = "No Existe External System";
                }
                if($jResponse['success'] == true){
                    $jResponse['message'] = $msn;
                    $jResponse['data'] = $msn;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn."-ERROR";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $msn." - ".$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    // Creaded by Vitmar Aliaga.
    /**
     * Este método funciona para dos peticiones
     * @getAll();
     * @getByQuery();
     */
   public function listFunding(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                // $text_search = $request->query('text_search');
                // $data = AccountingData::listFunding($text_search);
                $data = AccountingData::listAllFunding();
                if ($data) {
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function getNotContabilizadosUser(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI') {
            $jResponse = [];
            $lista = array();
            try {
                $id_entidad = 0;
                $id_depto = 0;
                $id_anho = 0;
                $id_mes = 0;
                $id_tipovoucher = 0;

                $id_voucher = $request->query('id_voucher');
                $voucher = AccountingData::getVoucherById($id_voucher);
                if(count($voucher) > 0) {
                    $id_entidad = $voucher[0]->id_entidad;
                    $id_depto = $voucher[0]->id_depto;
                    $id_anho = $voucher[0]->id_anho;
                    $id_mes = $voucher[0]->id_mes;
                    $id_tipovoucher = $voucher[0]->id_tipovoucher;
                }

                $lista = AccountingData::listVoucherNotContabilizados(
                    // $id_user,
                $id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher
                // , $id_user
                );
                $jResponse['success'] = true;
                if (count($lista) > 0) {
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $lista;
                    $code = "200";
                }else{
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function updateVoucherContabilizados(Request $request, $id_compra){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI') {
            $jResponse = [];
            $lista = array();
            try {
                // Validator::
                $data = Input::all();
                $validador = Validator::make($data,  ['id_voucher' => 'required']);
                if($validador->fails()) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe un voucher';
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }

                $voucherChildFirst = AccountingData::getVoucherChildFirstByIdParent($data['id_voucher']);
                if(count($voucherChildFirst) === 0) {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'No existe un voucher de pago para el voucher seleccionado.';
                    $jResponse['data'] = NULL;
                    $code = "202";
                    goto end;
                }

                // Eliminar su pago automáticamente, junto con su ajuste.
                $pagoDetalle = ExpensesData::getPagoCompraByIdCompra($id_compra);

                if(count($pagoDetalle) !== 0) {
                    $idPago = $pagoDetalle[0]->id_pago;
                    $idpagoDetalle = 'C'.$pagoDetalle[0]->id_pcompra;
                    $tipo = 0; // Solo uno o muchos items
                    $place = 2; // de donde esta eliminar { 1 : del proceso, 2: de la lista}
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_ELIMINAR_PAGO_DETALLE(:P_ID_PAGO, :P_ID_DETALLE,:P_TIPO, :P_PLACE); end;");
                    $stmt->bindParam(':P_ID_PAGO', $idPago, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DETALLE', $idpagoDetalle, PDO::PARAM_STR);
                    $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_INT);
                    $stmt->bindParam(':P_PLACE', $place, PDO::PARAM_INT);
                    // $stmt->execute();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [];
                    $code = "200";
                }
                // Cambiar de voucher su retención
                $retencionCompra = ExpensesData::getRetencionCompraByIdCompra($id_compra);
                if(count($retencionCompra) !== 0) {
                    $retencionCompras = ExpensesData::getRetencionComprasByIdRetencion($retencionCompra[0]->id_retencion);
                    foreach ($retencionCompras as $rc) {
                        PurchasesData::updateCompra(['id_voucher' => $data['id_voucher']], $rc->id_compra);
                        PurchasesData::updateCompraSeatContaAsiento(['voucher' => $data['id_voucher']], 3, $rc->id_compra);
                    }
                    ExpensesData::updateRetencion(['id_voucher' => $voucherChildFirst[0]->id_voucher], $retencionCompra[0]->id_retencion);
                    ExpensesData::updateRetencionSeatContaAsiento(['voucher' => $voucherChildFirst[0]->id_voucher],
                12,$retencionCompra[0]->id_retencion);
                }

                // Cambiar de voucher su detraccion
                $detraccionCompra = ExpensesData::getDetraccionCompraByIdCompra($id_compra);
                if(count($detraccionCompra) > 0) {

                    $detraccionCompras = ExpensesData::getRetencionComprasByIdRetencion($detraccionCompra[0]->id_detraccion);
                    foreach ($detraccionCompras as $dc) {
                        PurchasesData::updateCompra(['id_voucher' => $data['id_voucher']], $dc->id_compra);
                        PurchasesData::updateCompraSeatContaAsiento(['voucher' => $data['id_voucher']], 3, $dc->id_compra);
                    }
                    ExpensesData::updateDetraccion(['id_voucher' => $voucherChildFirst[0]->id_voucher], $detraccionCompra[0]->id_detraccion);
                    ExpensesData::updateDetraccionSeatContaAsiento(['voucher' => $voucherChildFirst[0]->id_voucher],
                        11, $detraccionCompra[0]->id_detraccion);
                }

                // Cambiar de voucher la compra en si.
                PurchasesData::updateCompra(['id_voucher' => $data['id_voucher']], $id_compra);
                PurchasesData::updateCompraSeatContaAsiento(['voucher' => $data['id_voucher']], 3, $id_compra);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $lista;
                $code = "200";

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = NULL;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function listVoucherContabilizados(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI') {
            $jResponse = [];
            $lista = array();
            try {
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_tipovoucher   = $request->query('id_tipovoucher');

                // $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                // foreach ($data_anho as $item){
                //     $id_anho = $item->id_anho;
                // }
                $data = AccountingData::listVoucherContabilizados(
                    //$id_user,
                    $id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher
                    //, $id_user
                );

                foreach ($data as $datum) {
                    $item_response = new \stdClass();
                    $item_response = $datum;
                    $item_response->documentos = PurchasesData::getComprasAndReceiptForFeesByIdVoucher($datum->id_voucher);
                    array_push($lista, $item_response);
                }

                $jResponse['success'] = true;
                if (count($lista) > 0) {
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $lista;
                    $code = "200";
                }else{
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }


    public function createCurrentAccountAaasinet(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $jResponse=[];
        if($valida=='SI'){
            $id_ruc = Input::get('id_ruc');
            $nombre = Input::get('nombre');
            if($id_ruc) {
                $jResponse = $this->createSubAccountOnAasinet($id_entidad, $id_ruc, $nombre);
            } else {
                $jResponse['success'] == false;
                $jResponse['message'] = "El RUC no es válido.";
                $jResponse['data'] = NULL;
                $jResponse['code'] = "200";
            }
        }
        return response()->json($jResponse,$jResponse['code']);
    }

    public static function createSubAccountOnAasinetSOA($id_entidad, $id_ruc, $nombre)
    {
        $jResponse = [];
        $jResponse['success'] = false;
        $jResponse['message'] = "";
        $jResponse['data'] = [];
        $jResponse['code'] = "200";
        try{
            $msn = "raios";
            $ex = AccountingData::ExternalSystemURL($id_entidad);

            foreach ($ex as $item){
                $url_aasinet = $item->url_aasinet."?wsdl";
            }

            $jResponse['data'] = [
                'id_entidad' => $id_entidad,
                'url_aasinet' => $url_aasinet,
                'id_ruc' => $id_ruc,
                'nombre' => $nombre,
            ];

            if($id_ruc === 0){
                $jResponse['success'] = false;
                $jResponse['message'] =  "Número de sub-cuenta inválida.";
                $jResponse['code'] = "202";
                goto end;
            }

            $data_xml = AccountingData::createCurrentAccountAaasinet($id_entidad,$url_aasinet,$id_ruc,$nombre);
            $contador          = 0;
            $link              = "";
            $context           = "";
            $componente        = "";
            $parameters        = "";
            $items             = "";
            foreach($data_xml as $row){
                $contador++;
                if($contador == 1){
                    $link           = $row->url;
                    $context        = $row->context;
                    $componente     = $row->component;
                    $parameters     = $row->parameters;
                    $items    = $row->items;
                }
            }

            //         $client = new SoapClient($link, array('exceptions' => 0));
            //     //$msn = "Error de conexi&oacute;n Load <br/>";
            //     $load = $client->Load(array("xmlPayload"=>$component));
            //     //dd($load);
            //     if (is_soap_fault($load)) {
            //         //trigger_error("1 SOAP Fault: (faultcode: {$load->faultcode}, faultstring: {$load->faultstring})", E_USER_ERROR);
            //         $msn = "Error de conexi&oacute;n Load <br/>";
            //     }else{
            //         try{
            //             $result = $client->Execute(array("payLoadKey"=>$load->LoadResult));
            //             //dd($result);
            //             $rs = (array)$result;
            //             //$msn = $rs ;
            //             if (is_soap_fault($result)) {
            //                 //trigger_error("ERROR: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
            //                 $msn = "Error de conexi&oacute;n Execute<br/>";
            //             }else{
            //                 $bodytag = $result->ExecuteResult;
            //                 $xml = simplexml_load_string($bodytag);//string convierte a xml
            //                 // dd($xml);
            //                 $cta_cte = $xml->Result->SubAccountsTransferResult->ArrayOfSubAccount->SubAccount->Code;

            //                 if($id_ruc === (string)$cta_cte ){

            //                     $jResponse['success'] = true;
            //                     $msn = "Cuenta Corriente Creada.";
            //                 }else{
            //                     $msn = $rs ;
            //                 }
            //             }
            //         }catch(Exception $e){
            //             $msn = (string) $xml->Messages->Exceptions->string ;
            //             $jResponse['success'] = false;
            //             $jResponse['message'] = $msn;
            //             $jResponse['data'] = [];
            //             $code = "202";
            //         }

            $component = "<AASIS>".$context.$componente.$parameters."<Content><Items>".$items."</Items></Content></AASIS>";
            $jResponse['success'] = false;
            $client = new SoapClient($link, array('exceptions' => 0));
            $load = $client->Load(array("xmlPayload"=>$component));
            if (is_soap_fault($load)) {
                $jResponse['success'] = false;
                $jResponse['message'] =  "Error de conexión, Método Load <br/>";
                $jResponse['code'] = "202";
                goto end;
            }
            $result = $client->Execute(array("payLoadKey"=>$load->LoadResult));
            $rs = (array)$result;
            if (is_soap_fault($result)) {
                $jResponse['success'] = false;
                $jResponse['message'] =  "Error de conexión, Método Execute<br/>";
                $jResponse['code'] = "202";
                goto end;
            }

            $bodytag = $result->ExecuteResult;
            $xml = simplexml_load_string($bodytag);// String convierte a xml
            $cta_cte = $xml->Result->SubAccountsTransferResult->ArrayOfSubAccount->SubAccount->Code;
            // if($id_ruc !== ((Array)$cta_cte)[0] ){
            if($id_ruc !== (string)$cta_cte ){
                $jResponse['success'] = false;
                $jResponse['message'] = $rs;
                $jResponse['code'] = "202";
                goto end;
            }
            $jResponse['success'] = true;
            $jResponse['message'] = "Cuenta Corriente Creada.";
            $jResponse['code'] = "200";
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['code'] = "202";
        }
        end:
        return $jResponse;
    }


    public static function createSubAccountOnAasinet($id_entidad, $id_ruc, $nombre)
    {
        $jResponse = [];
        $jResponse['success'] = false;
        $jResponse['message'] = "";
        $jResponse['data'] = [];
        $jResponse['code'] = "200";

        $TOKEN_SHELL_EXEC = "";
        $TOKEN_USER = "";
        $REST_URL_LOAD = "";
        $REST_URL_EXECUTE = "";
        $REST_CERTIFICADO = "";
        try{
            $msn = "raios";
            $ex = AccountingData::ExternalSystemURL($id_entidad);

            $entidad_config_data = AccountingData::EntidadConfiguracion($id_entidad);

            foreach ($ex as $item){
                $url_aasinet = $item->url_aasinet."?wsdl";
            }

            foreach($entidad_config_data as $row){
                $TOKEN_SHELL_EXEC = $row->token_shell_exec;
                $TOKEN_USER = $row->token_user;
                $REST_URL_LOAD = $row->rest_url_load;
                $REST_URL_EXECUTE = $row->rest_url_execute;
                $REST_CERTIFICADO = $row->rest_certificado;
            }

            $jResponse['data'] = [
                'id_entidad' => $id_entidad,
                'url_aasinet' => $url_aasinet,
                'id_ruc' => $id_ruc,
                'nombre' => $nombre,
            ];

            if($id_ruc === 0){
                $jResponse['success'] = false;
                $jResponse['message'] =  "Número de sub-cuenta inválida.";
                $jResponse['code'] = "202";
                goto end;
            }

            $data_xml = AccountingData::createCurrentAccountAaasinet($id_entidad,$url_aasinet,$id_ruc,$nombre, $REST_CERTIFICADO);
            $contador          = 0;
            $link              = "";
            $context           = "";
            $componente        = "";
            $parameters        = "";
            $items             = "";
            foreach($data_xml as $row){
                $contador++;
                if($contador == 1){
                    $link           = $row->url;
                    $context        = $row->context;
                    $componente     = $row->component;
                    $parameters     = $row->parameters;
                    $items    = $row->items;
                }
            }

            //         $client = new SoapClient($link, array('exceptions' => 0));
            //     //$msn = "Error de conexi&oacute;n Load <br/>";
            //     $load = $client->Load(array("xmlPayload"=>$component));
            //     //dd($load);
            //     if (is_soap_fault($load)) {
            //         //trigger_error("1 SOAP Fault: (faultcode: {$load->faultcode}, faultstring: {$load->faultstring})", E_USER_ERROR);
            //         $msn = "Error de conexi&oacute;n Load <br/>";
            //     }else{
            //         try{
            //             $result = $client->Execute(array("payLoadKey"=>$load->LoadResult));
            //             //dd($result);
            //             $rs = (array)$result;
            //             //$msn = $rs ;
            //             if (is_soap_fault($result)) {
            //                 //trigger_error("ERROR: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
            //                 $msn = "Error de conexi&oacute;n Execute<br/>";
            //             }else{
            //                 $bodytag = $result->ExecuteResult;
            //                 $xml = simplexml_load_string($bodytag);//string convierte a xml
            //                 // dd($xml);
            //                 $cta_cte = $xml->Result->SubAccountsTransferResult->ArrayOfSubAccount->SubAccount->Code;

            //                 if($id_ruc === (string)$cta_cte ){

            //                     $jResponse['success'] = true;
            //                     $msn = "Cuenta Corriente Creada.";
            //                 }else{
            //                     $msn = $rs ;
            //                 }
            //             }
            //         }catch(Exception $e){
            //             $msn = (string) $xml->Messages->Exceptions->string ;
            //             $jResponse['success'] = false;
            //             $jResponse['message'] = $msn;
            //             $jResponse['data'] = [];
            //             $code = "202";
            //         }

            $component = "<AASIS>".$context.$componente.$parameters."<Content><Items>".$items."</Items></Content></AASIS>";
            $jResponse['success'] = false;

            $me_status = 0;
            $salida = shell_exec($TOKEN_SHELL_EXEC);
            $token = str_replace("TOKEN:", "", $salida);
            $token = preg_replace("[\n|\r|\n\r]", "", $token);
            $dataraw = "{'UserName': '".$TOKEN_USER."','XmlContent': '".$component."'}";
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $REST_URL_LOAD,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>" ".$dataraw." ",
                CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$token." ",
                "Content-Type: text/plain"
                ),
            ));

            $response = curl_exec($curl);
            $me_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($me_status == 200){
                $payloadkey = $response;
                $dataraw_execute = "{'UserName': '".$TOKEN_USER."','XmlContent': '".$payloadkey."'}";
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $REST_URL_EXECUTE,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS =>" ".$dataraw_execute." ",
                    CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer ".$token." ",
                    "Content-Type: text/plain"
                    ),
                ));

                $response = curl_exec($curl);
                $me_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                if ($me_status == 200){
                    try{

                        $xml = simplexml_load_string($response);// String convierte a xml
                        $cta_cte = $xml->Result->SubAccountsTransferResult->ArrayOfSubAccount->SubAccount->Code;
                        // if($id_ruc !== ((Array)$cta_cte)[0] ){
                        if($id_ruc !== (string)$cta_cte ){
                            throw new Exception("Subcuenta creada: ".$cta_cte." es diferente al ruc: ".$id_ruc.". ", 1);
                            // $jResponse['success'] = false;
                            // $jResponse['message'] = "Subcuenta creada: ".$cta_cte." es diferente al ruc: ".$id_ruc.". ";
                            // $jResponse['code'] = "202";
                            // goto end;
                        }
                        // $xml = simplexml_load_string($response);//string convierte a xml
                        // $lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalCode;
                        // $tipo_lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalTypeCode;
                        // $t_lote_aasi = $tipo_lote_aasinet." ".$lote_aasinet;

                        // if($lote_aasinet != "X" && $lote_aasinet != "" ){
                        //     AccountingData::updateVoucherLote($id_voucher,$t_lote_aasi,'N');
                        //     $jResponse['success'] = true;
                        //     $msn = "El voucher de ".$descripcion." número: ".$numero." fué registrado en Aasinet con lote nro: ".$tipo_lote_aasinet." ".$lote_aasinet;
                        // }
                    }catch(Exception $e){
                        // $msn = (string) $xml->Messages->Exceptions->string ;
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msn." - ".$e->getMessage();
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                }else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "me_status: ".$me_status." - reponse: ".$response;
                    $jResponse['data'] = [];
                    $code = "202";
                    goto end;
                    // echo "Execute Faild ...\n";
                    // echo "me_status : $me_status\n";
                    // echo "reponse\n";
                    // echo $response ;
                }

            } else{
                $jResponse['success'] = false;
                $jResponse['message'] = "me_status: ".$me_status." - reponse: ".$response;
                $jResponse['data'] = [];
                $code = "202";
                goto end;
                // echo "Load Faild ...\n";
                // echo "me_status : $me_status\n";
                // echo "reponse\n";
                // echo $response ;
            }
            // $client = new SoapClient($link, array('exceptions' => 0));
            // $load = $client->Load(array("xmlPayload"=>$component));
            // if (is_soap_fault($load)) {
            //     $jResponse['success'] = false;
            //     $jResponse['message'] =  "Error de conexión, Método Load <br/>";
            //     $jResponse['code'] = "202";
            //     goto end;
            // }
            // $result = $client->Execute(array("payLoadKey"=>$load->LoadResult));
            // $rs = (array)$result;
            // if (is_soap_fault($result)) {
            //     $jResponse['success'] = false;
            //     $jResponse['message'] =  "Error de conexión, Método Execute<br/>";
            //     $jResponse['code'] = "202";
            //     goto end;
            // }

            // // $bodytag = $result->ExecuteResult;
            // $xml = simplexml_load_string($bodytag);// String convierte a xml
            // $cta_cte = $xml->Result->SubAccountsTransferResult->ArrayOfSubAccount->SubAccount->Code;
            // // if($id_ruc !== ((Array)$cta_cte)[0] ){
            // if($id_ruc !== (string)$cta_cte ){
            //     $jResponse['success'] = false;
            //     $jResponse['message'] = $rs;
            //     $jResponse['code'] = "202";
            //     goto end;
            // }
            $jResponse['success'] = true;
            $jResponse['message'] = "Cuenta Corriente Creada.";
            $jResponse['code'] = "200";
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $jResponse['code'] = "202";
        }
        end:
        return $jResponse;
    }


    public function validateVoucher(Request $request){
        $jResponse  = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_user    = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto   = $jResponse["id_depto"];
        if($valida == 'SI'){
            $jResponse = [];
            try{
                $id_tipovoucher = $request->query('id_tipovoucher');
                $id_voucher = 0;
                $numero = 0;
                $msg_error = "";
                $error = 0;
                $fecha = "";
                for($x=1 ; $x <= 200 ; $x++){
                    $msg_error .= "0";
                    $fecha .="0";
                }
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,'7',$tiene_params,$params);
                if($rpta["nerror"]==0){
                    $id_anho = $rpta["id_anho"];
                }
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("BEGIN PKG_ACCOUNTING.SP_VALIDAR_VOUCHER(
                    :P_ID_ENTIDAD,
                    :P_ID_DEPTO,
                    :P_ID_ANHO,
                    :P_ID_TIPOVOUCHER,
                    :P_ID_PERSONA,
                    :P_ID_VOUCHER,
                    :P_NUMERO,
                    :P_FECHA,
                    :P_ERROR,
                    :P_MSN
                    );
                    end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_TIPOVOUCHER', $id_tipovoucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_INT);
                    $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSN', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [
                            'id_voucher' => $id_voucher,
                            'numero' => $numero,
                            'fecha' => $fecha
                        ];
                    $code                 = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;
                    $jResponse['data'] = [];
                    $code                 = "202";
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
    public function addAccountingSeat(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            //$id_voucher = null;
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_asiento = null;
                $id_tipoorigen = $params->id_tipoorigen;
                $id_origen = $params->id_origen;
                $id_fondo = $params->id_fondo;
                $id_depto = $params->id_depto;
                $id_cuentaaasi = $params->id_cuentaaasi;
                $id_ctacte = $params->id_ctacte;
                $id_restriccion = $params->id_restriccion;
                $importe = $params->importe;
                $descripcion = $params->descripcion;
                $id_voucher = $params->id_voucher;
                $importe_me = $params->importe_me;
                $tipo = "1"; //Insertar
                AccountingData::addAccountingSeat($id_asiento,$id_tipoorigen,$id_origen,$id_fondo,$id_depto,$id_cuentaaasi,$id_ctacte,$id_restriccion,$importe,$descripcion,$id_voucher,$importe_me,$tipo);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "501";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateAccountingSeat($id_asiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            //$id_voucher = null;
            try{
                $params = json_decode(file_get_contents("php://input"));
                $id_tipoorigen = $params->id_tipoorigen;
                $id_origen = $params->id_origen;
                $id_fondo = $params->id_fondo;
                $id_depto = $params->id_depto;
                $id_cuentaaasi = $params->id_cuentaaasi;
                $id_ctacte = $params->id_ctacte;
                $id_restriccion = $params->id_restriccion;
                $importe = $params->importe;
                $descripcion = $params->descripcion;
                $id_voucher = $params->id_voucher;
                $importe_me = $params->importe_me;
                $tipo = "2"; //Editar
                AccountingData::addAccountingSeat($id_asiento,$id_tipoorigen,$id_origen,$id_fondo,$id_depto,$id_cuentaaasi,$id_ctacte,$id_restriccion,$importe,$descripcion,$id_voucher,$importe_me,$tipo);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "501";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteAccountingSeat($id_asiento){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            //$id_voucher = null;
            try{
                AccountingData::deleteAccountingSeat($id_asiento);
                $jResponse['success'] = true;
                $jResponse['message'] = "Succes";
                $jResponse['data'] = [];
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "501";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listAccountingSeat(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $id_tipoorigen = $request->query('id_tipoorigen');
                $id_origen = $request->query('id_origen');
                $data = AccountingData::listAccountingSeat($id_tipoorigen,$id_origen);
                $total = AccountingData::showAccountingSeatTotal($id_tipoorigen,$id_origen);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data']    = [ "data"=>$data, "total"=>$total ];
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
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
        return response()->json($jResponse,$code);
    }
    public function showAccountingSeat($id_asiento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = AccountingData::showAccountingSeat($id_asiento);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
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
    public function listDeptoParentSesion()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $entity = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = AccountingData::listDeptoParentSesion($entity);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
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
    public function listVoucherModulesAll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_anho   = $request->query('id_anho');
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item){
                        $id_anho = $item->id_anho;
                    }

                }

                $id_mes   = $request->query('id_mes');
                $id_tipovoucher   = $request->query('id_tipovoucher');
                $data = AccountingData::listVoucherModulesAll($id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function listVoucherMoveInventories(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item){
                    $id_anho = $item->id_anho;
                }
                $id_mes   = $request->query('id_mes');
                $id_anho   = $request->query('id_anho');
                $id_tipovoucher   = $request->query('id_tipovoucher');
                $almacen   = $request->query('almacen');
                $tipo   = $request->query('tipo');
                $data = AccountingData::listVoucherMoveInventories($id_entidad,$id_depto,$id_anho,$id_mes,$id_tipovoucher,$almacen,$tipo);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateArrangement($id_arreglo){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $params = json_decode(file_get_contents("php://input"));
                $serie = $params->serie;
                $numero = $params->numero;
                $fecha = $params->fecha;
                $data = AccountingData::updateArrangementDocumet($id_arreglo,$serie,$numero,$fecha);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";
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
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function getDailyBook()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingData::dailyBookResumen($empresa, $entidad, $anho, $mes);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function getDailyBookLotes()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $params = [
            'id_empresa' => $queryParams['id_empresa'],
            'id_entidad' => $queryParams['id_entidad'],
            'id_anho' => $queryParams['id_anho'],
            'id_mes' => $queryParams['id_mes'],
            'per_page' => isset($queryParams['per_page']) ? $queryParams['per_page'] : 20,// operador ternario -> if else
        ];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingData::dailyBookLotes($params);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function getDailyBookExport()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingData::dailyBookExport($empresa, $entidad, $anho, $mes);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function getDailyBookExportPdfUPN()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            // ReportLegalController::testUPN($empresa, $entidad, $anho, $mes);
        }
        return response()->json($jResponse, $code);
    }

    public function getDailyBookExportPdf()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            ReportLegalController::test($empresa, $entidad, $anho, $mes);
        }
        return response()->json($jResponse, $code);
    }

    // Libro Mayor
    public function getLedgerBook()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingData::dailyBookResumen($empresa, $entidad, $anho, $mes);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function getLedgerBookLotes()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $params = [
            'id_empresa' => $queryParams['id_empresa'],
            'id_entidad' => $queryParams['id_entidad'],
            'id_anho' => $queryParams['id_anho'],
            'id_mes' => $queryParams['id_mes'],
            'per_page' => isset($queryParams['per_page']) ? $queryParams['per_page'] : 20,// operador ternario -> if else
        ];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingData::dailyBookLotes($params);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }

    public function getLedgerBookExport()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            $jResponse = [];
            $jResponse['success'] = true;
            $jResponse['message'] = "Success";
            $jResponse['data'] = AccountingData::dailyBookExport($empresa, $entidad, $anho, $mes);
            $code = "200";
        }
        return response()->json($jResponse, $code);
    }
    public function getLedgerBookExportPdf()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        $isValid = $valida == 'SI'; // true  : false;
        if ($isValid) {
            ReportLegalController::libro_mayor($empresa, $entidad, $anho, $mes);
        }
        return response()->json($jResponse, $code);
    }
    public function getLedgerBookExportUpnPdf()
    {
        // Autorización del usuario
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse['code'];
        $valida = $jResponse["valida"];
        
        // Obtener parámetros de la solicitud
        $queryParams = $this->request->all();
        $empresa = $queryParams["id_empresa"]; // SI : NO
        $entidad = $queryParams["id_entidad"];
        $anho = $queryParams["id_anho"];
        $mes = $queryParams["id_mes"];
        // $id_cuentaempresarial = isset($queryParams["id_cuentaempresarial"]) ? $queryParams["id_cuentaempresarial"] : null;

        $isValid = $valida == 'SI'; // true  : false;
        
        if ($isValid) {
               // Llamada a la función que genera el PDF
            ReportLegalController::libro_mayor_upn($empresa,$entidad,$anho,$mes);
        }
        // Retornar la respuesta JSON
        return response()->json($jResponse, $code);
    }
}

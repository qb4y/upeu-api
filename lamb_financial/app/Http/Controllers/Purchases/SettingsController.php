<?php
namespace App\Http\Controllers\Purchases;
use App\Http\Data\Purchases\SettingsData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\ORM\CompraSuspencion;
use App\ORM\ContaEntidadAnhoConfig;
use App\ORM\ContaEntidadDepto;
use App\ORM\Persona;
use PDO;

class SettingsController extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listPurchasesOfSettings(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[]; 
            $id_voucher = $request->query('id_voucher');
            $data = SettingsData::listPurchasesOfSettings($id_entidad,$id_depto,$id_voucher);
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
        }        
        return response()->json($jResponse,$code);
    }
    public function addPurchasesOfSettings(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            $params = json_decode(file_get_contents("php://input"));
            $id_compra = $params->id_compra;
            $id_proveedor = $params->id_proveedor;
            $id_dinamica = $params->id_dinamica;
            //$id_moneda = $params->id_moneda;
            $id_voucher = $params->id_voucher;
            $fecha = $params->fecha;
            $dc = $params->dc;
            $importe = $params->importe;
            $importe_me = $params->importe_me;
            
            
            $id_tipoorigen = $params->id_tipoorigen;
            /*$id_fondo = $params->id_fondo;
            $id_depto_a = $params->id_depto;
            $id_cuentaaasi = $params->id_cuentaaasi;
            $id_ctacte = $params->id_ctacte;
            $id_restriccion = $params->id_restriccion;*/
            
            $descripcion = $params->descripcion;
            
            $id_tipovoucher = $params->id_tipovoucher;//11 = Compra; 12 = RxH
            $modo = $params->modo;
            $tiene_params = "S";
            $rpta = AccountingData::AccountingYearMonthTC($id_entidad,7,$tiene_params,$params);
            if($rpta["nerror"]==0){
                $id_anho = $rpta["id_anho"];
                $id_mes = $rpta["id_mes"];
                $tc = $rpta["tc"];
                $id_ajuste = 0;
                $error = 0;
                $msg_error = "";
                $estado = "0";
                for($x=1;$x<=200;$x++){
                    $msg_error .= "0";
                }
                try{
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_CREAR_AJUSTE(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_ANHO,:P_ID_MES,:P_ID_PERSONA,:P_ID_COMPRA,:P_ID_DINAMICA,:P_ID_VOUCHER,:P_FECHA,:P_ID_IMPORTE,:P_IMPORTE_ME,:P_DC,:P_ID_AJUSTE,:P_ERROR,:P_ESTADO, :P_ID_PROVEEDOR); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);  
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                    $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_IMPORTE', $importe, PDO::PARAM_STR);
                    $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                    $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_AJUSTE', $id_ajuste, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ESTADO', $estado, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_PROVEEDOR', $id_proveedor, PDO::PARAM_INT);
                    $stmt->execute();
                    if($dc == "C"){
                        $importe = $importe*-1;
                    }
                    if($error == 0){
                        if($id_ajuste != 0){
                            $id_voucher = null; //SE ENVIA NULL, PARA QUE CUANDO CONFIRME SE EDITE EL VOUCHER
                            if($modo == "0"){//Genera Asiento x Asiento
                                AccountingData::addAccountingSeat($id_tipoorigen,$id_ajuste,$id_fondo,$id_depto_a,$id_cuentaaasi,$id_ctacte,$id_restriccion,$importe,$descripcion,$id_voucher,$importe_me);
                            }else{//Genera Asiento Automatico
                                AccountingData::generateAccountingSeat($id_dinamica,$id_tipoorigen,$id_ajuste,$importe,$descripcion,$id_voucher,$importe_me);
                            }
                            $jResponse['success'] = true;
                            $jResponse['message'] = "Succes";                    
                            $jResponse['data'] = ['id_ajuste' => $id_ajuste];
                            $code = "200";
                        }else{
                            $jResponse['success'] = false;
                            $jResponse['message'] = $msg_error;                    
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msg_error;                    
                        $jResponse['data'] = [];
                        $code = "202";
                    }                       
                }catch(Exception $e){
                    $jResponse['success'] = false;                    
                    $jResponse['message'] = $e->getMessage();
                    $jResponse['data'] = [];
                    $error = "202";
                }
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $rpta["msgerror"];
                $jResponse['data'] = [];
                $code = "202";
            }
               
        }        
        return response()->json($jResponse,$code);
    }
    public function updatePurchasesOfSettings($id_ajuste){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            /*$params = json_decode(file_get_contents("php://input"));
            
            $id_tipovoucher = $params->id_tipovoucher;//11 = Compra; 12 = RxH
            $modo = $params->modo;
            $tiene_params = "S";
            */
            $error = 0;
            $msg_error = "";
            $estado = "0";
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            try{
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_UPDATE_AJUSTE(:P_ID_AJUSTE,:P_ERROR,:P_MSG); end;");
                $stmt->bindParam(':P_ID_AJUSTE', $id_ajuste, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['id_ajuste' => $id_ajuste];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;                    
                    $jResponse['data'] = [];
                    $code = "202";
                }                       
            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
               
        }        
        return response()->json($jResponse,$code);
    }
    public function deletePurchasesOfSettings($id_ajuste){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $error = 0;
            $msg_error = "";
            $estado = "0";
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            try{
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_DELETE_AJUSTE(:P_ID_AJUSTE,:P_ERROR,:P_MSG); end;");
                $stmt->bindParam(':P_ID_AJUSTE', $id_ajuste, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
                $stmt->execute();
                if($error == 0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msg_error;                    
                    $jResponse['data'] = [];
                    $code = "202";
                }                       
            }catch(Exception $e){
                $jResponse['success'] = false;                    
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $error = "202";
            }
               
        }        
        return response()->json($jResponse,$code);
    }
}
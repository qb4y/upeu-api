<?php
namespace App\Http\Controllers\Accounting\Setup;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Accounting\Setup\SeatCWFJData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use PDO;
use phpDocumentor\Reflection\Types\Array_;
use SoapClient;
class SeatCWFJController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listVoucherCWAasinet(Request $request){
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
                if($id_tipovoucher == 1){//VENTAS
                    $data = SeatCWFJData::listVoucherCWAasinetSales($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 2){//COMPRAS
                    $data = SeatCWFJData::listVoucherCWAasinetPurchases($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 3){//CHEQUE
                    $data = SeatCWFJData::listVoucherCWAasinetCHQ($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 4){//TLC
                    $data = SeatCWFJData::listVoucherCWAasinetTLC($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 5){//INGRESOS
                    $data = SeatCWFJData::listVoucherCWAasinetIncome($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 14){//VENTAS CU
                    $data = SeatCWFJData::listVoucherCWAasinetSalesCU($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 15){//VENTAS IMP
                    $data = SeatCWFJData::listVoucherCWAasinetSalesIMP($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 16){//VENTAS OPTIMUS
                    $data = SeatCWFJData::listVoucherCWAasinetSalesOPT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 17){//VENTAS TPP
                    $data = SeatCWFJData::listVoucherCWAasinetSalesTPP($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }
                
                elseif($id_tipovoucher == 18){//VENTAS CAT
                    $data = SeatCWFJData::listVoucherCWAasinetSalesCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 19){//Compras CAT
                    $data = SeatCWFJData::listVoucherCWAasinetPurchasesCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 20){//Telecreditos CAT
                    $data = SeatCWFJData::listVoucherCWAasinetTLCCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 21){//Cheques CAT
                    $data = SeatCWFJData::listVoucherCWAasinetCHQCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
                }elseif($id_tipovoucher == 22){//Ingresos CAT
                    $data = SeatCWFJData::listVoucherCWAasinetIncomeCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes);
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
                $code = "500";
            } 
        }
        return response()->json($jResponse,$code);
    } 
    public function listSeatAaasinet(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"]; 
        $id_user = $jResponse["id_user"]; 
        if($valida=='SI'){
            $jResponse=[];
            $msn = "";
            try{     
                $id_voucher = $request->query('id_voucher');
                $id_anho   = $request->query('id_anho');
                $id_tipovoucher   = $request->query('id_tipovoucher');
                
                if($id_tipovoucher == 1){//VENTAS
                    $data = SeatCWFJData::listCWSeatAaasinetSales($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 2){//COMPRAS
                    $data = SeatCWFJData::listCWSeatAaasinetPurchases($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 3){//TLC
                    $data = SeatCWFJData::listCWSeatAaasinetCHQ($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 4){//TLC
                    $data = SeatCWFJData::listCWSeatAaasinetTLC($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 5){//INGRESOS
                    $data = SeatCWFJData::listCWSeatAaasinetIncome($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 14){//VENTAS CU
                    $data = SeatCWFJData::listCWSeatAaasinetCU($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 15){//VENTAS IMP
                    $data = SeatCWFJData::listCWSeatAaasinetIMP($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 16){//VENTAS OPT
                    $data = SeatCWFJData::listCWSeatAaasinetOPT($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 17){//VENTAS TPP
                    $data = SeatCWFJData::listCWSeatAaasinetTPP($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 18){//VENTAS TPP
                    $data = SeatCWFJData::listCWSeatAaasinetSalesCAT($id_depto,$id_anho,$id_voucher);
                }

                elseif($id_tipovoucher == 19){//Compras CAT
                    $data = SeatCWFJData::listCWSeatAaasinetPurchasesCAT($id_depto,$id_anho,$id_voucher);
                }elseif($id_tipovoucher == 20){//Telecreditos CAT
                    $data = SeatCWFJData::listCWSeatAaasinetTLCCAT($id_depto,$id_anho,$id_voucher); 
                }elseif($id_tipovoucher == 21){//Cheques CAT
                    $data = SeatCWFJData::listCWSeatAaasinetCHQCAT($id_depto,$id_anho,$id_voucher); 
                }elseif($id_tipovoucher == 22){//Ingresos CAT
                    $data = SeatCWFJData::listCWSeatAaasinetIncomeCAT($id_depto,$id_anho,$id_voucher);
                }




                $jResponse['message'] = $msn;                    
                $jResponse['data'] = $data;
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
    public function uploadSeatAaasinet(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"]; 
        $TOKEN_SHELL_EXEC = ""; 
        $TOKEN_USER = "";
        $REST_URL_LOAD = "";
        $REST_URL_EXECUTE = "";
        $REST_CERTIFICADO = "";
        $area = "";

        if($valida=='SI'){
            $jResponse=[];
            $jResponse['result'] = "";
            $msn = "";
            try{
                $inicialesUsers = AccountingData::getInicialesNombreUsuario($id_user);
                foreach ($inicialesUsers as $item) {
                    $inicialesUser = $item->iniciales;
                }
                
                $params = json_decode(file_get_contents("php://input"));
                $id_voucher = $params->id_voucher;
                $id_anho = $params->id_anho;
                $id_tipovoucher = $params->id_tipovoucher;
                
                $voucher = SeatCWFJData::showVoucher($id_voucher,$id_depto,$id_anho,$id_tipovoucher);
                foreach ($voucher as $item){
                    $id_tipoasiento = $item->id_tipoasiento;
                    $numero = $id_voucher;
                    $fecha = $item->fecha;
                    $fecha_aasi = $item->fecha_aasi;
                    $periodo = $item->periodo; 
                    $codigo = $item->codigo;              
                }
                    //$codigo = 0;
                /*$ex = AccountingData::ExternalSystem($id_entidad,$id_depto,$id_anho,$id_tipoasiento,$id_modulo);
                foreach ($ex as $item){
                    //$codigo = $item->codigo;
                    $url_aasinet = $item->url_aasinet."?wsdl";
                }*/
                $entidad_config_data = AccountingData::EntidadConfiguracion($id_entidad);
                foreach($entidad_config_data as $row){
                    $TOKEN_SHELL_EXEC = $row->token_shell_exec; 
                    $TOKEN_USER = $row->token_user; 
                    $REST_URL_LOAD = $row->rest_url_load; 
                    $REST_URL_EXECUTE = $row->rest_url_execute; 
                    $REST_CERTIFICADO = $row->rest_certificado;  
                    $url_aasinet = $row->url_aasinet."?wsdl";
                }

                if($codigo != 0){ 

                    if($id_tipovoucher == 1){//VENTAS
                        $descripcion = $inicialesUser."::CW-VENTAS";
                        $area = "10";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetSales($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 2){//COMPRAS
                        $descripcion = $inicialesUser."::CW-COMPRAS";
                        $area = "03";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetPurchases($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 3){//CHEQUE
                        $descripcion = $inicialesUser."::CW-CHEQUES";
                        $area = "18";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetCHQ($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 4){//TLC
                        $descripcion = $inicialesUser."::CW-TLC";
                        $area = "13";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetTLC($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 5){//INGRESOS
                        $descripcion = $inicialesUser."::CW-INGRESOS";
                        $area = "05";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetIncome($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 14){//VENTAS CU
                        $descripcion = $inicialesUser."::CW-VENTAS";
                        $area = "12";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetCU($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 15){//VENTAS IMP
                        $descripcion = $inicialesUser."::CW-VENTAS";
                        $area = "11";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetIMP($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 16){//VENTAS OPT
                        $descripcion = $inicialesUser."::CW-VENTAS";
                        $area = "22";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetOPT($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 17){//VENTAS TPP
                        $descripcion = $inicialesUser."::CW-VENTAS";
                        $area = "27";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetTPP($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    } 

                    elseif($id_tipovoucher == 18){//VENTAS CAT
                        $descripcion = $inicialesUser."::CW-VENTAS";
                        $area = "10";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetSalesCAT($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 19){//COMPRAS CAT
                        $descripcion = $inicialesUser."::CW-COMPRAS";
                        $area = "03";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetPurchasesCAT($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 20){//TLC CAT
                        $descripcion = $inicialesUser."::CW-TLC";
                        $area = "13";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetTLCCAT($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 21){//CHEQUE CAT
                        $descripcion = $inicialesUser."::CW-CHEQUES";
                        $area = "18";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetCHQCAT($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }elseif($id_tipovoucher == 22){//INGRESOS CAT
                        $descripcion = $inicialesUser."::CW-INGRESOS";
                        $area = "05";
                        $data_xml = SeatCWFJData::uploadSeatAaasinetIncomeCAT($id_entidad,$id_depto,$id_anho,$id_voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $REST_CERTIFICADO);
                    }

		    
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
                                $xml = simplexml_load_string($response);//string convierte a xml
                                $lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalCode;
                                $tipo_lote_aasinet = $xml->Result->ExternalMultipleAccountingResult->ArrayOfJournalInfo->JournalInfo->JournalTypeCode;
                                //$t_lote_aasi = $tipo_lote_aasinet." ".$lote_aasinet;

                                if($lote_aasinet != "X" && $lote_aasinet != "" ){
                                    //AccountingData::updateVoucherLote($id_voucher,$t_lote_aasi,'N');

                                    SeatCWFJData::insert_update_lote($id_voucher,$id_depto,$id_anho,$id_tipovoucher,$lote_aasinet,$area,substr($id_tipoasiento, 0, 4));
                                    SeatCWFJData::update_lote_cw($id_voucher,$id_depto,$id_anho,$id_tipovoucher,$lote_aasinet,$area,substr($id_tipoasiento, 0, 4));

                                    //$bo_optimus->insert_update_lotes(s_mai_id(), $area, s_nivel(),$lote,$voucher,$lote_aasinet);
                                    //$bo_optimus->update_lote_cw(s_mai_id(), $area, s_nivel(),$lote,$voucher,$t_lote_aasi,'CO');

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
                    $msn = "No Existe External System";
                }
                if($jResponse['success'] == true){
                    $jResponse['message'] = $msn;                    
                    $jResponse['data'] = $msn;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn."-ERROR ".$e->getMessage();
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
}
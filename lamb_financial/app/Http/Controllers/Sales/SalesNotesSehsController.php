<?php
namespace App\Http\Controllers\Sales;
use App\Http\Data\FinancialEnrollment\ProceduresDiscounts;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Sales\SalesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Accounting\Setup\TypeTransactionData;
use App\Http\Data\Inventories\WarehousesData;
use App\Http\Data\Accounting\Setup\PrintData;
use App\Http\Data\Setup\PersonData;
use App\Http\Data\Sales\PoliticsData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use DOMPDF;
use App\Http\Data\FinancesStudent\StudentData;
use App\Http\Data\Sales\SalesSehsData;
use Carbon\Carbon;

class SalesNotesSehsController extends Controller{

    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public function addNotesSales(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        $id_entidad = $request->id_entidad;

        if($valida=='SI'){
            $jResponse=[];              
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item){
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;                
            }
            if($id_anho !== $id_anho_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un año activo.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
            foreach ($data_mes as $item){
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;                
            }
            if($id_mes !== $id_mes_actual){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, No existe un mes activo.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_persona);
            foreach ($warehouse as $key => $item){
                $id_almacen = $item->id_almacen;
            }

            if(count($warehouse) === 0){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, El usuario no tiene asignado un almacén.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
             }
            if(count($warehouse) > 1){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, Hay mas de un almacén asignado al usuario.";                    
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            
            $tipcam["tc"] = false;
            $id_moneda = 9;
            if($id_moneda === '9') {
                $tipcam = GlobalMethods::verificaTipoCambio();
            } else {
                $tipcam["tc"] = true;
            }
            if($tipcam["tc"] === false){
                $jResponse['success'] = false;
                $jResponse['message'] = "Alto, actualice el tipo de cambio.";                        
                $jResponse['data'] = [];
                $code = "202";
                GOTO end;
            }
            $id_venta = $request->id_venta;
            $id_comprobante = $request->id_comprobante;
            $id_tiponota = $request->id_tiponota;
            $glosa = $request->glosa;
            $detail = $request->detail;

            $aid_vdetalle = array();
            $acantidad = array();

            foreach ($request->detail as $datos) {
                $items = (object)$datos;
                $aid_vdetalle[] = $items->id_vdetalle;
                $acantidad[] = $items->cantidad2;
            }

            $id_vdetalle     = implode("|", $aid_vdetalle);
            $cantidad  = implode("|", $acantidad);
            // dd($id_vdetalle, $cantidad, $id_anho, $id_mes);
            $sid_venta = 0;
            $msgerror = "";   
            DB::beginTransaction();
            try{
                for($x=1;$x<=200;$x++){
                    $msgerror .= "0";
                }
                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_SALES_SEHS.SP_IUPD_NOTA_INV(
                    :P_ID_VENTA_ORI,
                    :P_ID_COMPROBANTE,
                    :P_ID_TIPONOTA,
                    :P_GLOSA,
                    :P_ID_ANHO,
                    :P_ID_MES,
                    :P_ID_PERSONA,
                    :P_ID_VDETALLE,
                    :P_CANTIDAD,
                    :P_ID_VENTA,
                    :P_ERROR,
                    :P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VENTA_ORI', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_TIPONOTA', $id_tiponota, PDO::PARAM_STR);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_VENTA', $sid_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();                            
                if($error !== 0){
                    throw new Exception($msgerror, 1);
                }
                    
                $response = $this->finishNotesSales($sid_venta,$id_comprobante,$id_persona,$id_entidad);
                if(!$response['success']){
                    throw new Exception($response['message'], 1);
                }

                DB::commit();
                $jResponse['success'] = true;
                $jResponse['message'] = "Se guardo con exito";                    
                $jResponse['data'] = $sid_venta;
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

    private function finishNotesSales($id_venta,$id_comprobante,$id_user,$id_entidad) {
    
          
        $pdo = DB::getPdo();
                
        $datos = SalesData::showSales($id_venta);
        $id_depto='';
        $id_parent='';
        foreach ($datos as $item){
            $id_depto = $item->id_depto;
            $id_parent = $item->id_parent;
        }

        // venta origen.
        $datosParent = SalesData::showSalesStatus($id_parent);
        $id_comprobante_afecto='';
        foreach ($datosParent as $item2){
            $id_comprobante_afecto = $item2->id_comprobante;
        }

        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto);
        if(count($data) === 0) {
            $jResponse['success'] = false;
            $jResponse['message'] = "Alto: Debe asignarse una punto de impresion para el documento [$id_comprobante] que afecta a [$id_comprobante_afecto]. En la entidad: $id_entidad y depto: $id_depto";                    
            $jResponse['data'] = NULL;

            GOTO end;
        }
        if(count($data) > 1) {
            $jResponse['success'] = false;
            $jResponse['message'] = "Alto: Tiene asignado más de un punto de impresión para el documento. [$id_comprobante] que afecte a [$id_comprobante_afecto]. En la entidad: $id_entidad y depto: $id_depto";                    
            $jResponse['data'] = NULL;

            GOTO end;
        }

        foreach ($data as $item){
            $id_documento = $item->id_documento;
            $ip = $item->ip;
            $service_port = $item->puerto;
        }
        
        $id_credito = 2; //$params->id_credito;
        $efectivo = 0;
        $credito = 0;
        $tarjeta = 0;
        $id_tipotarjeta = '';
        $operacion = '';
        $error = 0;
        $msgerror = "";   
        for($x=1;$x<=200;$x++){
            $msgerror .= "0";
        }

        $stmt = $pdo->prepare("begin PKG_SALES.SP_FINALIZAR_VENTA(:P_ID_VENTA, :P_ID_CREDITO, :P_EFECTIVO, :P_CREDITO, :P_TARJETA, :P_ID_TIPOTARJETA, :P_OPERACION, :P_ERROR, :P_MSGERROR); end;");
        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);                        
        $stmt->bindParam(':P_ID_CREDITO', $id_credito, PDO::PARAM_INT);
        $stmt->bindParam(':P_EFECTIVO', $efectivo, PDO::PARAM_STR);
        $stmt->bindParam(':P_CREDITO', $credito, PDO::PARAM_STR);
        $stmt->bindParam(':P_TARJETA', $tarjeta, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
        $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();                             
        if($error == 0){
         
            $msn = "";
           ////////////////
            usleep(2500000); //Dormir 2 segundos

            if($id_entidad === "7124") {
                SalesData::UpdateSalesHash($id_venta);
            } else {
                SalesData::UpdateSalesHashUPN($id_venta);
            }

            PrintData::deletePrint($id_user);//TODOS
            PrintData::deleteTemporal($id_user);//TODOS
            PrintData::addDocumentsPrints($id_user,1,"x");//TODOS
            PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'H',0);//TODOS
            SalesData::addSalesParametersHead($id_venta,$id_user,$id_documento);
            $cont = SalesData::addSalesParametersBody($id_venta,$id_user,$id_documento);
            SalesData::addSalesParametersFoot($id_venta,$id_user,$id_documento,$cont);
            $cont = PrintData::addDocumentsPrintsFixedParameters($id_user,$id_documento,'F',$cont);//TODOS
            //SalesController::addPrintSales($id_user,$id_venta,$id_documento);
            //SalesController::PrintSales($id_user,$id_comprobante);
            $credit = SalesData::ShowSalesToCredit($id_venta);
            if($credit === "0"){
                $cant = 1;
            }else{
                $cant = 2;
            }
            for ($i = 1; $i <= $cant; $i++) {
                $msn = SalesController::print($id_user,$ip,$service_port); // TODOS
            }
            ////////////////////////////////
            $jResponse['success'] = true;
            $jResponse['message'] = "The item was updated successfully "."(Impresion: ".$msn.")";
            $jResponse['data'] = [];
        }else{
    
            $jResponse['success'] = false;
            $jResponse['message'] = $msgerror;
            $jResponse['data'] = null;
 
        }                            
        end:
        return $jResponse; 
    }
}


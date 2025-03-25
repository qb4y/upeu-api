<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Sales\SalesController;
use App\Http\Data\Accounting\Setup\PrintData;
use App\Http\Data\FinancialEnrollment\ProceduresDiscounts;
use App\Http\Data\Sales\SalesData;
use App\Http\Data\Treasury\ExpensesData;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Treasury\IncomeData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Setup\PersonData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use DOMPDF;
use Excel;
use Carbon\Carbon;
class IncomeController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function listMedioPago()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_modules = $this->request->query('codigo_modulo');
//            dd($id_modules);
            try {
                $data = IncomeData::listMedioPago($id_modules);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        //return "zzz";
        return response()->json($jResponse, $code);
    }

    public function listFinancialEntities(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $estado = $request->query('estado');
                $data = IncomeData::listFinancialEntities($estado);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showFinancialEntities($id_banco)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::showFinancialEntities($id_banco);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addFinancialEntities()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->nombre;
            $sigla = $params->sigla;
            $estado = $params->estado;
            try {
                $data = IncomeData::addFinancialEntities($nombre, $sigla, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function updateFinancialEntities($id_banco)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->nombre;
            $sigla = $params->sigla;
            $estado = $params->estado;
            try {
                $data = IncomeData::updateFinancialEntities($id_banco, $nombre, $sigla, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function deleteFinancialEntities($id_banco)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::deleteFinancialEntities($id_banco);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    /*public function listBankAccount(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];        
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];            
            try{    
                $id_banco   = $request->query('id_banco');
                $data = IncomeData::listBankAccount($id_banco,$id_entidad,$id_depto);                
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
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }*/
    public function listCardType()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::listCardType();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listDepositType()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::listDepositType();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addDeposit()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $session = $jResponse;
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_deposito = null;
        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        try {
                            DB::beginTransaction();
                            try {
                                $params = json_decode(file_get_contents("php://input"));
                                $importe = $params->importe;
                                if ($importe > 0) {
                                    $error = 0;
                                    $msgerror = "";
                                    for ($x = 1; $x <= 200; $x++) {
                                        $msgerror .= "0";
                                    }
                                    $tc = $tipcam["denominacional"];
                                    $importe_tarjeta = 0;
                                    $importe_trans = 0;
                                    if ($params->id_mediopago != '008') {
                                        $importe_tarjeta = $params->importe;
                                    }

                                    $ventas = $params->venta;
                                    $venta_ids = "";
                                    $imp_ventas = "";

                                    $i = 1;
                                    foreach ($ventas as $key => $item) {
                                        $id_venta = $item->id_venta;
                                        $importe_vnt = $item->total;
                                        if ($importe_vnt > 0) {
                                            if ($i == 1) {
                                                $venta_ids = $id_venta;
                                                $imp_ventas = $importe_vnt;
                                            } else {
                                                $venta_ids = $venta_ids . "|" . $id_venta;
                                                $imp_ventas = $imp_ventas . "|" . $importe_vnt;
                                            }
                                            $i++;
                                        }
                                    }
                                    if (!empty($params->id_vale)) {
                                        $id_vale = $params->id_vale;
                                    } else {
                                        $id_vale = null;
                                    }

                                    $tipo_asiento = "MB";
                                    $pdo = DB::getPdo();
                                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_MEDIOPAGO, :P_ID_PERSONA, 
                                            :P_ID_CLIENTE,:P_VENTAS,:P_IMP_VENTAS,:P_ID_TIPOTRANSACCION,:P_ID_MONEDA,:P_ID_DINAMICA,:P_ID_TIPOTARJETA,:P_ID_CTABANCARIA,:P_OPERACION,:P_FECHA_OP,
                                            :P_IMPORTE,:P_IMPORTE_TARJETA,:P_IMPORTE_TRANS,:P_IMPORTE_ME,:P_TIPOCAMBIO,:P_GLOSA,:P_NOMBRE_DEP,:P_DOCUMENTO_DEP,:P_ID_TIPOASIENTO,
                                            :P_ERROR,:P_MSGERROR,:P_ID_VALE,:P_ID_DEPOSITO); end;");
                                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_MEDIOPAGO', $params->id_mediopago, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_CLIENTE', $params->id_cliente, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_VENTAS', $venta_ids, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMP_VENTAS', $imp_ventas, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $params->id_tipodeposito, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_MONEDA', $params->id_moneda, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_TIPOTARJETA', $params->id_tipotarjeta, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_CTABANCARIA', $params->id_ctabancaria, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_OPERACION', $params->operacion, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_FECHA_OP', $params->fecha_op, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_tarjeta, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE_TRANS', $importe_trans, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_GLOSA', $params->glosa, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_NOMBRE_DEP', $params->nombre_dep, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_DOCUMENTO_DEP', $params->documento_dep, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_TIPOASIENTO', $tipo_asiento, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_VALE', $id_vale, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                                    $stmt->execute();

                                    // print_r('$error:'.$error);
                                    if ($error == 0) {
                                        $id_comprobante = "00";
                                        // VALIDA SI IMPRIME O NOP
                                        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user, $id_comprobante,'');
                                        $imprimir = $data[0]->imprimir;
                                        if($imprimir == "S"){
                                            $respo = ProceduresDiscounts::generateTicketDeposito($id_deposito, $session);
                                            if ($respo['success']) {
                                                DB::commit();
                                            } else {
                                                //DB::rollback();
                                            }
                                        }else{
                                            DB::commit();
                                        }
                                       
                                        if (!empty($params->id_vfile)) {
                                            $paramsValeFile = ["id_deposito" => $id_deposito];
                                            ExpensesData::updateValeFile($params->id_vfile, $paramsValeFile);
                                        }
                                        DB::commit();
                                        $jResponse['success'] = true;
                                        $jResponse['message'] = "The item was updated successfully";
                                        $jResponse['data'] = [];
                                        $code = "200";
                                    } else {
                                        DB::rollback();
                                        $jResponse['success'] = false;
                                        $jResponse['message'] = $msgerror;
                                        $jResponse['data'] = [];
                                        $code = "202";
                                    }
                                } else {
                                    DB::rollback();
                                    $jResponse['success'] = false;
                                    $jResponse['message'] = "El Importe debe ser Mayor a 0";
                                    $jResponse['data'] = [];
                                    $code = "202";
                                }
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                                DB::rollback();
                            }
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


//    DEpos estudent

    public function addDepositStudent()
    {
//        dd('gola');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $session = $jResponse;
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_deposito = null;
        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        try {
                            DB::beginTransaction();
                            try {
                                $params = json_decode(file_get_contents("php://input"));
                                $importe = $params->importe;
                                if ($importe > 0) {
                                    $error = 0;
                                    $msgerror = "";
                                    for ($x = 1; $x <= 200; $x++) {
                                        $msgerror .= "0";
                                    }
                                    $tc = $tipcam["denominacional"];
                                    $importe_tarjeta = 0;
                                    $importe_trans = 0;
                                    if ($params->id_mediopago != '008') {
                                        $importe_trans = $params->importe;
                                    }

                                    $ventas = $params->venta;
                                    $venta_ids = "";
                                    $imp_ventas = "";

                                    $i = 1;
                                    foreach ($ventas as $key => $item) {
                                        $id_venta = $item->id_venta;
                                        $importe_vnt = $item->total;
                                        if ($importe_vnt > 0) {
                                            if ($i == 1) {
                                                $venta_ids = $id_venta;
                                                $imp_ventas = $importe_vnt;
                                            } else {
                                                $venta_ids = $venta_ids . "|" . $id_venta;
                                                $imp_ventas = $imp_ventas . "|" . $importe_vnt;
                                            }
                                            $i++;
                                        }
                                    }
                                    if (!empty($params->id_vale)) {
                                        $id_vale = $params->id_vale;
                                    } else {
                                        $id_vale = null;
                                    }

                                    if ($id_depto == "1"){
                                        $tipo_asiento = "MB";
                                    }elseif($id_depto == "5"){
                                        $tipo_asiento = "MBJ";
                                    }elseif($id_depto == "6"){
                                        $tipo_asiento = "MBT";
                                    }elseif($id_depto == "8"){
                                        $tipo_asiento = "MBS";
                                    }else{
                                        $tipo_asiento = "MB";
                                    }

                                    $isAutomatic = "N";
                                    $pdo = DB::getPdo();
                                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_ALUMNO(
                                    :P_ID_ENTIDAD,
                                    :P_ID_DEPTO,
                                    :P_ID_ANHO,
                                    :P_ID_MES,
                                    :P_ID_MEDIOPAGO,
                                    :P_ID_PERSONA,                                          
                                    :P_ID_CLIENTE,
                                    :P_AUTOMATICO,
                                    :P_VENTAS,
                                    :P_IMP_VENTAS,
                                    :P_ID_TIPOTRANSACCION,
                                    :P_ID_MONEDA,
                                    :P_ID_DINAMICA,
                                    :P_ID_TIPOTARJETA,
                                    :P_ID_CTABANCARIA,
                                    :P_OPERACION,
                                    :P_FECHA_OP,
                                    :P_IMPORTE,
                                    :P_IMPORTE_TARJETA,
                                    :P_IMPORTE_ME,
                                    :P_TIPOCAMBIO,
                                    :P_GLOSA,
                                    :P_NOMBRE_DEP,
                                    :P_DOCUMENTO_DEP,
                                    :P_ID_TIPOASIENTO,
                                    :P_ERROR,
                                    :P_MSGERROR, :P_ID_DEPOSITO); end;");
                                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_MEDIOPAGO', $params->id_mediopago, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_CLIENTE', $params->id_cliente, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_AUTOMATICO', $isAutomatic, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_VENTAS', $venta_ids, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMP_VENTAS', $imp_ventas, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_TIPOTRANSACCION', $params->id_tipodeposito, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_MONEDA', $params->id_moneda, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_TIPOTARJETA', $params->id_tipotarjeta, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_ID_CTABANCARIA', $params->id_ctabancaria, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_OPERACION', $params->operacion, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_FECHA_OP', $params->fecha_op, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_trans, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_GLOSA', $params->glosa, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_NOMBRE_DEP', $params->nombre_dep, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_DOCUMENTO_DEP', $params->documento_dep, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_TIPOASIENTO', $tipo_asiento, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                    $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                                    $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                                    $stmt->execute();

                                    // print_r('$error:'.$error);
                                    if ($error == 0) {//
                                        $id_comprobante = "00";
                                        // VALIDA SI IMPRIME O NOP
                                        $data = PrintData::showIPDocumentUserPrint($id_entidad, $id_depto, $id_user, $id_comprobante,'');
                                        $imprimir = $data[0]->imprimir;
                                        if($imprimir == "S"){
                                            $respo = ProceduresDiscounts::generateTicketDeposito($id_deposito, $session);
                                            if ($respo['success']) {
                                                DB::commit();
                                            } else {
                                                DB::rollback();
                                            }
                                        }else{
                                            DB::commit();
                                        }
                                        
                                        $jResponse['success'] = true;
                                        $jResponse['message'] = "The item was updated successfully";
                                        $jResponse['data'] = [];
                                        $code = "200";
                                    } else {
                                        DB::rollback();
                                        $jResponse['success'] = false;
                                        $jResponse['message'] = $msgerror;
                                        $jResponse['data'] = [];
                                        $code = "202";
                                    }
                                } else {
                                    DB::rollback();
                                    $jResponse['success'] = false;
                                    $jResponse['message'] = "El Importe debe ser Mayor a 0";
                                    $jResponse['data'] = [];
                                    $code = "202";
                                }
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                                DB::rollback();
                            }
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addDepositStudentMassive()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $session = $jResponse;
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_deposito = null;
        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        try {
                            DB::beginTransaction();
                            try {
                                $body = json_decode(file_get_contents("php://input"));
                                $operations = array();
                                $deposits = $body->deposits;
                                foreach ($deposits as $params) {
//                                    var_export($params);

//                                dd($params);
                                    $importe = $params->importe;
                                    if ($importe > 0) {
                                        $error = 0;
                                        $msgerror = "";
                                        for ($x = 1; $x <= 200; $x++) {
                                            $msgerror .= "0";
                                        }
                                        
                                        if ($id_depto == "1"){
                                            $tipo_asiento = "MB";
                                        }elseif($id_depto == "5"){
                                            $tipo_asiento = "MBJ";
                                        }elseif($id_depto == "6"){
                                            $tipo_asiento = "MBT";
                                        }elseif($id_depto == "8"){
                                            $tipo_asiento = "MBS";
                                        }else{
                                            $tipo_asiento = "MB";
                                        }
                                        $isAutomatic = "N";
                                        $id_mediopago = "001";
                                        $id_tipotarjeta = "";
                                        $tc = $tipcam["denominacional"];
                                        $importe_trans = 0;
//                                    if (id_mediopago != '008') {
                                        $importe_trans = $params->importe;
                                        $importe = 0;
//                                    }

//                                        $ventas = $params->venta;
                                        $venta_ids = "";
                                        $imp_ventas = "";

                                        $i = 1;
                                        /*foreach ($ventas as $key => $item) {
                                            $id_venta = $item->id_venta;
                                            $importe_vnt = $item->total;
                                            if ($importe_vnt > 0) {
                                                if ($i == 1) {
                                                    $venta_ids = $id_venta;
                                                    $imp_ventas = $importe_vnt;
                                                } else {
                                                    $venta_ids = $venta_ids . "|" . $id_venta;
                                                    $imp_ventas = $imp_ventas . "|" . $importe_vnt;
                                                }
                                                $i++;
                                            }
                                        }*/
                                        $array_date = explode('/',$params->fecha);
                                        $date_op = "{$array_date[2]}-{$array_date[1]}-{$array_date[0]}";

//                                        dd('Fecha', $date_op,'>', date("Y-m-d", strtotime($params->fecha)));
                                        if (!empty($params->id_vale)) {
                                            $id_vale = $params->id_vale;
                                        } else {
                                            $id_vale = null;
                                        }


//                                        dd('WAYPEAYY', $id_mediopago);
                                        $pdo = DB::getPdo();
                                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_ALUMNO(
                                    :P_ID_ENTIDAD,
                                    :P_ID_DEPTO,
                                    :P_ID_ANHO,
                                    :P_ID_MES,
                                    :P_ID_MEDIOPAGO,
                                    :P_ID_PERSONA,                                          
                                    :P_ID_CLIENTE,
                                    :P_AUTOMATICO,
                                    :P_VENTAS,
                                    :P_IMP_VENTAS,
                                    :P_ID_TIPOTRANSACCION,
                                    :P_ID_MONEDA,
                                    :P_ID_DINAMICA,
                                    :P_ID_TIPOTARJETA,
                                    :P_ID_CTABANCARIA,
                                    :P_OPERACION,
                                    :P_FECHA_OP,
                                    :P_IMPORTE,
                                    :P_IMPORTE_TARJETA,
                                    :P_IMPORTE_ME,
                                    :P_TIPOCAMBIO,
                                    :P_GLOSA,
                                    :P_NOMBRE_DEP,
                                    :P_DOCUMENTO_DEP,
                                    :P_ID_TIPOASIENTO,
                                    :P_ERROR,
                                    :P_MSGERROR, :P_ID_DEPOSITO); end;");
                                        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CLIENTE', $params->id_persona, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_AUTOMATICO', $isAutomatic, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_VENTAS', $venta_ids, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMP_VENTAS', $imp_ventas, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_TIPOTRANSACCION', $params->id_tipodeposito, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MONEDA', $params->id_moneda, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CTABANCARIA', $params->id_ctabancaria, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_OPERACION', $params->operacion, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_FECHA_OP', $date_op, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_trans, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_GLOSA', $params->glosa, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_NOMBRE_DEP', $params->nom_persona, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_DOCUMENTO_DEP', $params->num_documento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_TIPOASIENTO', $tipo_asiento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                                        $stmt->execute();

                                        // print_r('$error:'.$error);
                                        if ($error == 0) {//
//                                            dd('res');
                                            DB::commit();
                                            $jResponse['success'] = true;
                                            $jResponse['message'] = "The item was updated successfully";
                                            $jResponse['data'] = $id_deposito;
                                            $code = "200";
                                        } else {
                                            DB::rollback();
                                            $jResponse['success'] = false;
                                            $jResponse['message'] = $msgerror;
                                            $jResponse['data'] = [];
                                            $code = "202";
                                        }
                                    } else {
                                        DB::rollback();
                                        $jResponse['success'] = false;
                                        $jResponse['message'] = "El Importe debe ser Mayor a 0";
                                        $jResponse['data'] = [];
                                        $code = "202";
                                    }
                                }
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                                DB::rollback();
                            }
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function cashRegister(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_mediopago = $request->query('id_mediopago');
                $p = $request->query('p');
                $id_persona = $p ? $id_user : null;

                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoVoucher($id_voucher);
                    foreach ($data_anho as $item) {
                        $id_anho = $item->id_anho;
                    }
                }
                $dataA = IncomeData::cashRegister($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona);
                $dataT = IncomeData::cashRegisterTotal($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona);
                $dataC = IncomeData::cashRegisterCajero($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona);
                $arqueo = IncomeData::listArching($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher);
                $jResponse['success'] = true;
                if (count($dataA) > 0) {
                    $jResponse['message'] = "Succes";
                    //$jResponse['data'] = ['items' => $dataA];
                    $jResponse['data'] = ['items' => $dataA, 'total' => $dataT, 'resumen' => $dataC, 'arqueo' => $arqueo];
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function cashRegisterCajaPdf(Request $request) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $username = $jResponse["email"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $cajero = $request->query('cajero');
                $idPersona = $request->query('id_persona');
                $voucher = collect(AccountingData::showVoucher($id_voucher))->first();
                $data = IncomeData::cashRegisterCajaPdf($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $idPersona);
                // dd($data);
                $pdf = DOMPDF::loadView('pdf.treasury.reports.cashRegisterCaja', [
                    'data' => $data,
                    'voucher' => $voucher,
                    'cajero' => $cajero,
                    'username' => $username // OBLIGATORIO
                ])->setPaper('a4', 'portrait');
                $doc = base64_encode($pdf->stream('print.pdf'));
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc]
                ];
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = 'ERROR: ' . $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', ['mensaje' => $mensaje])->setPaper('a4', 'portrait');
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];

        return response()->json($jResponse);
    }

    // para excel 
    public function exportXlsCaja(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $username = $jResponse["email"];
        $id_user = $jResponse["id_user"];
        $respuesta = ['nerror'=> 0 ,'mensaje' => "ok"];
        $data = [];
        try{
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
            }
            $id_mes = $request->query('id_mes');
            $id_voucher = $request->query('id_voucher');
            $cajero = $request->query('cajero');
            $idPersona = $request->query('id_persona');
            $voucher = collect(AccountingData::showVoucher($id_voucher))->first();
            $data = IncomeData::cashRegisterCajaPdf($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $idPersona);
                // dd($data);
            // print_r($data);
        }catch(Exception $e){
           return 'Error';
        }
       Excel::create('lista', function($excel) use($data, $voucher,  $cajero)  { 
            $excel->sheet('lista', function($sheet) use($data, $voucher,  $cajero) {
                $sheet->loadView("excel.treasury.cashRegisterCajaExcel", array('data'=>$data,'voucher'=>$voucher,'cajero'=>$cajero));
                $sheet->setOrientation('landscape'); 
            $sheet->setOrientation('landscape');
            });
        })->download('xlsx');
    }

    public function cashRegisterExportPdf(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $username = $jResponse["email"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $mensaje = '';
            $jResponse = [];

            try {
                $id_anho = $request->query('id_anio');
                if(!$id_anho){
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item) {
                        $id_anho = $item->id_anho;
                    }
                }
                 
                
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_mediopago = $request->query('id_mediopago');
                $p = $request->query('p');
                $id_persona = $p ? $id_user : null;
                $voucher = collect(AccountingData::showVoucher($id_voucher))->first();
                $data = IncomeData::cashRegister($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona);
                $summary = IncomeData::cashRegisterCajero($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_mediopago, $id_persona);
                if (count($summary) == 2) {
                    $summary = [$summary[1], $summary[0]];
                }
                $pdf = DOMPDF::loadView('pdf.treasury.reports.cashRegister', [
                    'data' => $data,
                    'summary' => $summary,
                    'voucher' => $voucher,
                    'username' => $username // OBLIGATORIO
                    // ])->setPaper('a4', 'landscape');
                ])->setPaper('a4', 'portrait');
                $doc = base64_encode($pdf->stream('print.pdf'));
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc]
                ];
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = 'ERROR: ' . $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', ['mensaje' => $mensaje])->setPaper('a4', 'portrait');
        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];

        return response()->json($jResponse);

    }

    public function showVoucherDeposits($id_voucher)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_mediopago = $this->request->query('id_mediopago');
                $mainReport = IncomeData::showVoucherDeposits($id_voucher, $id_mediopago);
                $total = IncomeData::showVoucherDepositsCouting($id_voucher, $id_mediopago);
                $jResponse['success'] = true;
                if (count($mainReport) > 0) {
                    $jResponse['message'] = "Succes";
                    //$jResponse['data'] = ['items' => $dataA];
                    $jResponse['data'] = ['items' => $mainReport, 'total' => $total];
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function exportVoucherDeposits($id_voucher)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $valida = $jResponse["valida"];
        $username = $jResponse["email"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            try {
                $id_mediopago = $this->request->query('id_mediopago');

                $voucher = collect(AccountingData::showVoucher($id_voucher))->first();

                $data = IncomeData::showVoucherDeposits($id_voucher, $id_mediopago);
                $total = IncomeData::showVoucherDepositsCouting($id_voucher, $id_mediopago);
//                dd($data);
                $pdf = DOMPDF::loadView('pdf.treasury.reports.cashRegisterSummary', [
                    'deposits' => $data,
                    'voucher' => $voucher,
                    'total' => $total,
                    'depto' => $id_depto,
                    'username' => $username // OBLIGATORIO
                    // ])->setPaper('a4', 'landscape');
                ])->setPaper('a4', 'portrait');
                $doc = base64_encode($pdf->stream('print.pdf'));
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc]
                ];
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'landscape');
        $pdf->save($ruta);

        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];
        return response()->json($jResponse);
    }

    public function depositsCorrect(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_mediopago = $request->query('id_mediopago');
                $params = (object)array(
                    'id_anho' => $id_anho,
                    'id_mediopago' => $id_mediopago,
                );
                $data = IncomeData::depositsCorrect($params);

                $jResponse['success'] = true;
                if ($data->count() > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function depositsCorrectConfirm($idDeposit)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $anula = "N";

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $params = (object)array(
                    'id_user' => $id_user,
                );
                //Validando los depositos con anticipos
                $deposito = IncomeData::showDepositId($idDeposit);
                foreach ($deposito as $item) {
                    $id_entidad = $item->id_entidad;
                    $id_depto = $item->id_depto;
                    $id_anho = $item->id_anho;
                    $id_deposito = $item->id_deposito;
                    $id_cliente = $item->id_cliente;
                    $importe = $item->importe;
                }
                $anticipo = IncomeData::showAdvanceId($id_deposito);
                if (count($anticipo) > 0) {
                    $total = IncomeData::showAdvanceTotal($id_entidad,$id_depto,$id_anho,$id_cliente);
                    foreach ($total as $item) {
                        $total = $item->total;
                    }

                    $pago = IncomeData::showSalesPayment($id_deposito);
                    foreach ($pago as $item) {
                        $pago_venta = $item->pago;
                    }

                    if(($total+$pago_venta) >= $importe){
                        //Anula
                        $anula = "S";
                    }else{
                        //NO Anula
                        $anula = "N";
                    }
                }else{
                    //Anular
                    $anula = "S";
                }

                if($anula === "S"){
                    $data = IncomeData::depositsCorrectConfirm($idDeposit, $params);

                    if ($data) {
                        $jResponse['message'] = "Succes";
                        $jResponse['success'] = true;
                        $jResponse['data'] = $data;

                        $code = "200";
                    } else {
                        $jResponse['message'] = "No realizado";
                        $jResponse['success'] = false;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['message'] = "NO se puede ANULAR, El SALDO de ANTICIPO ya esta Procesado";
                    $jResponse['success'] = false;
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
        return response()->json($jResponse, $code);
    }

    public function addDepositImports(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //$params = json_decode(file_get_contents("php://input"));
                $params = "";
                $tiene_params = "N";
                $id_ctabancaria = $request->id_ctabancaria;
                $id_moneda = $request->id_moneda;
                $glosa = $request->glosa;
                //dd($id_ctabancaria);
                //VALIDA AÑO, MES, TC, Y PARAMETROS
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, $id_moneda, $tiene_params, $params);
                if ($rpta["nerror"] == 0) {
                    $dato["id_entidad"] = $id_entidad;
                    $dato["id_depto"] = $id_depto;
                    $dato["id_anho"] = $rpta["id_anho"];
                    $dato["id_mes"] = $rpta["id_mes"];
                    $dato["id_user"] = $id_user;
                    $dato["id_moneda"] = $id_moneda;
                    $dato["id_ctabancaria"] = $id_ctabancaria;
                    $dato["tc"] = $rpta["tc"];
                    $dato["glosa"] = $glosa;
                    //SE ELIMINA REGISTROS INCOMPLETOS
                    IncomeData::deleteDepositImports($id_entidad, $id_depto, $rpta["id_anho"], $id_user);
                    \Excel::load($request->excel, function ($reader) use ($dato) {
                        $excel = $reader->get();
                        $reader->each(function ($row) use ($dato) {

                            $documento = $row->codigo;
                            $id_cliente = PersonData::showStudentsPersons($documento);
                            if ($id_cliente != 0) {
                                $operacion = $row->operacion;
                                $fecha_op = date_format($row->fecha, 'Y-m-d');
                                $importe = $row->importe;
                                $importe_me = $row->importe_me;
                                $error = 0;
                                $msn_error = "00000000000000000000000000000000000000000000000000";
                                $pdo = DB::getPdo();
                                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_IMP(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_PERSONA, 
                                        :P_ID_CLIENTE,:P_ID_MONEDA,:P_ID_CTABANCARIA,:P_OPERACION,:P_FECHA_OP,:P_IMPORTE,:P_IMPORTE_ME,:P_TIPOCAMBIO,:P_GLOSA,:P_ERROR,:P_MSGERROR); end;");
                                $stmt->bindParam(':P_ID_ENTIDAD', $dato["id_entidad"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_DEPTO', $dato["id_depto"], PDO::PARAM_STR);
                                $stmt->bindParam(':P_ID_ANHO', $dato["id_anho"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_MES', $dato["id_mes"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_PERSONA', $dato["id_user"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_MONEDA', $dato["id_moneda"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_ID_CTABANCARIA', $dato["id_ctabancaria"], PDO::PARAM_INT);
                                $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                                $stmt->bindParam(':P_FECHA_OP', $fecha_op, PDO::PARAM_STR);
                                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                                $stmt->bindParam(':P_TIPOCAMBIO', $dato["tc"], PDO::PARAM_STR);
                                $stmt->bindParam(':P_GLOSA', $dato["glosa"], PDO::PARAM_STR);
                                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                $stmt->bindParam(':P_MSGERROR', $msn_error, PDO::PARAM_STR);
                                $stmt->execute();
                            }

                        });
                    });
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listDepositImports()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, 7, "N", '');
                if ($rpta["nerror"] == 0) {
                    $data = IncomeData::listDepositImports($id_entidad, $id_depto, $id_user, $rpta["id_anho"]);
                    $jResponse['success'] = true;
                    if (count($data) > 0) {
                        $jResponse['message'] = "Succes";
                        $jResponse['data'] = $data;
                        $code = "200";
                    } else {
                        $jResponse['message'] = "The item does not exist";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
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
        return response()->json($jResponse, $code);
    }

    public function addDepositImportsFinish()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = "";
                $tiene_params = "N";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, '7', $tiene_params, $params);
                if ($rpta["nerror"] == 0) {
                    $id_anho = $rpta["id_anho"];
                    $id_mes = $rpta["id_mes"];
                    $error = 0;
                    $msn_error = "00000000000000000000000000000000000000000000000000";
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_IMP_FIN(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_PERSONA, :P_ERROR,:P_MSGERROR); end;");
                    $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                    $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_MSGERROR', $msn_error, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($error == 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $msn_error;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    // hola
    public function studentsImport(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
//        dd($id_user);
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //$params = json_decode(file_get_contents("php://input"));
                $params = "";
                $tiene_params = "N";
                $id_ctabancaria = $request->id_ctabancaria;
                $id_moneda = $request->id_moneda;
                $glosa = $request->glosa;
                //dd($id_ctabancaria);
                //VALIDA AÑO, MES, TC, Y PARAMETROS
//                $rpta = AccountingData::AccountingYearMonthTC($id_entidad,$id_moneda,$tiene_params,$params);
//                if(true){ // validare i
                $dato["id_entidad"] = $id_entidad;
                $dato["id_depto"] = $id_depto;
//                    $dato["id_anho"]    = $rpta["id_anho"];
//                    $dato["id_mes"]     = $rpta["id_mes"];
                $dato["id_user"] = $id_user;
                $dato["id_moneda"] = $id_moneda;
                $dato["id_ctabancaria"] = $id_ctabancaria;
//                    $dato["tc"]         = $rpta["tc"];
                $dato["glosa"] = $glosa;
                //SE ELIMINA REGISTROS INCOMPLETOS
//                    IncomeData::deleteDepositImports($id_entidad,$id_depto,$rpta["id_anho"],$id_user);
                $data = array();
                \Excel::load($request->excel, function ($reader) use ($dato, &$data) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($dato, &$data) {
                        $client = (object)array();
//                        dd($row->fecha);

                        /*if (\DateTime::createFromFormat('Y-m-d H:i:s', $row->fecha) !== FALSE) {
                            // it's a date
                            dd('isDate', $row->fecha);
                        }else {
                            dd('IS NOT DATE', $row->fecha);
                        }*/
                        $eItem = (object)array(
                            'documento' => $row->codigo,
                            'operacion' => $row->operacion,
                            'fecha' => $row->fecha->format('d/m/Y'),
                            'importe' => $row->importe,
                            'importe_me' => $row->importe_me);
                        $client = (object)array_merge((array)$client, (array)$eItem);
                        $student = PersonData::showStudentByUniversityCode($eItem->documento);
                        $client = (object)array_merge((array)$client, (array)$student);
                        $item = (object)array();
                        if ($student) {
                            $numDocValid = ($student and $student->num_documento);
                            $item->is_valid = $numDocValid;
                            $item->message = $numDocValid ? 'Ok' : 'no existe num. documento relacionado';
                        } else {
//                                dd('doies not');
                            $item->is_valid = false;
                            $item->codigo = $eItem->documento;
                            $item->message = 'no hay registro relacionados';

                        }
                        $client = (object)array_merge((array)$client, (array)$item);
//                            dd($client);
//                            dd($client, $data);
                        array_push($data, $client);

                        /*if($id_cliente != 0){
                            $operacion = $row->operacion;
                            $fecha_op = date_format($row->fecha, 'Y-m-d');
                            $importe   = $row->importe;
                            $importe_me   = $row->importe_me;
                            $error = 0;
                            $msn_error = "00000000000000000000000000000000000000000000000000";
                            $pdo = DB::getPdo();
                            $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_IMP(:P_ID_ENTIDAD, :P_ID_DEPTO, :P_ID_ANHO, :P_ID_MES, :P_ID_PERSONA,
                                    :P_ID_CLIENTE,:P_ID_MONEDA,:P_ID_CTABANCARIA,:P_OPERACION,:P_FECHA_OP,:P_IMPORTE,:P_IMPORTE_ME,:P_TIPOCAMBIO,:P_GLOSA,:P_ERROR,:P_MSGERROR); end;");
                            $stmt->bindParam(':P_ID_ENTIDAD', $dato["id_entidad"], PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_DEPTO', $dato["id_depto"], PDO::PARAM_STR);
                            $stmt->bindParam(':P_ID_ANHO', $dato["id_anho"], PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_MES', $dato["id_mes"], PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_PERSONA', $dato["id_user"],PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_CLIENTE', $id_cliente , PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_MONEDA', $dato["id_moneda"], PDO::PARAM_INT);
                            $stmt->bindParam(':P_ID_CTABANCARIA', $dato["id_ctabancaria"], PDO::PARAM_INT);
                            $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                            $stmt->bindParam(':P_FECHA_OP', $fecha_op, PDO::PARAM_STR);
                            $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                            $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                            $stmt->bindParam(':P_TIPOCAMBIO', $dato["tc"], PDO::PARAM_STR);
                            $stmt->bindParam(':P_GLOSA', $dato["glosa"], PDO::PARAM_STR);
                            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                            $stmt->bindParam(':P_MSGERROR', $msn_error, PDO::PARAM_STR);
                            $stmt->execute();
                        }*/
                    });
//                        dd($data);

                });
                usort($data, function ($a, $b) {
                    return ($a->is_valid < $b->is_valid) ? 1 : -1;
                });
//                    $data = collect($data)->sortByDesc('is_valid');
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listBanKAccountType()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::listBanKAccountType();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listVoucherCashClosing(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $cerrado = $request->query('cerrado');

                // comment:"Desiciones"
                if ($id_entidad == "7124") {
                    if ($id_depto == "1") {
                        $id_tipoasiento = 'MB';
                    } elseif ($id_depto == "4") {
                        $id_tipoasiento = 'MBC';  
                    } elseif ($id_depto == "5") {
                        $id_tipoasiento = 'MBJ';
                    } elseif ($id_depto == "7") {
                        $id_tipoasiento = 'MBL';
                    }elseif ($id_depto == "8") {
                        $id_tipoasiento = 'MBS';
                    }else {
                        $id_tipoasiento = 'MBT';
                    }
                } else {
                    $id_tipoasiento = 'MB';
                }

                //$cerrado = false;
                $data = IncomeData::listVoucherCashClosing($id_entidad, $id_depto, $id_anho, $id_mes, $cerrado, $id_tipoasiento);
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showVoucherCashClosingDeposits($id_cierre)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $data = IncomeData::showVoucherCashClosingDeposits($id_cierre);
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showVoucherCashClosingDepositsExport($id_cierre)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $valida = $jResponse["valida"];
        $username = $jResponse["email"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            try {
                $data = IncomeData::showVoucherCashClosingDeposits($id_cierre);
                $cierre_box = $data->first();
                $id_voucher = property_exists($cierre_box, 'id_voucher') ? $cierre_box->id_voucher : null;

                if ($id_depto == "1") {
                    $id_tipoasiento = 'MB';
                } elseif ($id_depto == "5") {
                    $id_tipoasiento = 'MBJ';
                } elseif ($id_depto == "4") {
                    $id_tipoasiento = 'MBC';
                } elseif ($id_depto == "8") {
                    $id_tipoasiento = 'MBS';
                } else {
                    $id_tipoasiento = 'MBT';
                }
                $cierre = collect(IncomeData::listVoucherCashClosingByIdCierre($id_cierre, $id_tipoasiento));

//                $voucher = collect(AccountingData::showVoucher($id_voucher))->first();

                $tot = 0;
                $tot_me = 0;
                foreach ($data->toArray() as $key => $value) {
                    if (isset($value->importe)) {
                        $tot += $value->importe;
                    }
                    if (isset($value->importe_me)) {
                        $tot_me += $value->importe_me;
                    }
                }
                $total = (object)array(
                    'importe' => $tot,
                    'importe_me' => $tot_me,
                );
                $pdf = DOMPDF::loadView('pdf.treasury.reports.cashClosingDeposits', [
                    'closebox' => $data,
//                    'voucher'=>$voucher,
                    'cierre' => $cierre->first(),
                    'total' => $total,
                    'username' => $username // OBLIGATORIO
                ])->setPaper('a4', 'portrait');
                $doc = base64_encode($pdf->stream('print.pdf'));
                $jResponse = [
                    'success' => true,
                    'message' => "OK",
                    'data' => ['items' => $doc]
                ];
                return response()->json($jResponse);
            } catch (Exception $e) {
                $mensaje = $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            }
        } else {
            $mensaje = $jResponse["message"];
        }

        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'landscape');
        $pdf->save($ruta);

        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => ['items' => $doc]
        ];
        return response()->json($jResponse);
    }

    public function addCashClosing(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_voucher = $request->id_voucher;
                $id_dinamica = $request->id_dinamica;
                $glosa = $request->glosa;
                $error = 0;
                $msn_error = "00000000000000000000000000000000000000000000000000";
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_CAJA.SP_CERRAR_CAJA(:P_ID_VOUCHER, :P_ID_DINAMICA, :P_ID_PERSONA, :P_GLOSA, :P_ERROR,:P_MSGERROR); end;");
                $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msn_error, PDO::PARAM_STR);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $msn_error;
                    $jResponse['data'] = [];
                    $code = "202";
                }
                $code = "202";

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function myCashRegister(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $id_mes = $request->query('id_mes');
                $id_voucher = $request->query('id_voucher');
                $id_mediopago = $request->query('id_mediopago');
                $lista = IncomeData::myCashRegister($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_user);
                $total = IncomeData::myCashRegisterTotal($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_user);
                $resumen = IncomeData::myCashRegisterPago($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_user);
                $jResponse['success'] = true;
                if (count($lista) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $lista, 'total' => $total, 'resumen' => $resumen];
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
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function admissionImport(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
//        dd($id_user);
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params = "";
                $tiene_params = "N";
                $id_ctabancaria = $request->id_ctabancaria;
                $id_moneda = $request->id_moneda;
                $glosa = $request->glosa;
                $dato["id_entidad"] = $id_entidad;
                $dato["id_depto"] = $id_depto;
                $dato["id_user"] = $id_user;
                $dato["id_moneda"] = $id_moneda;
                $dato["id_ctabancaria"] = $id_ctabancaria;
                $dato["glosa"] = $glosa;
                //SE ELIMINA REGISTROS INCOMPLETOS
//                    IncomeData::deleteDepositImports($id_entidad,$id_depto,$rpta["id_anho"],$id_user);
                $data = array();
                \Excel::load($request->excel, function ($reader) use ($dato, &$data) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($dato, &$data) {
                        $client = (object)array();
                        $eItem = (object)array(
                            'codigo' => $row->codigo_pago,
                            'operacion' => $row->operacion,
                            'fecha' => $row->fecha->format('d/m/Y'),
                            'importe' => $row->importe,
                            'importe_me' => $row->importe_me);
                        $client = (object)array_merge((array)$client, (array)$eItem);
                        $student = IncomeData::showAdmissionByCode($eItem->codigo);
                        $client = (object)array_merge((array)$client, (array)$student);
                        $item = (object)array();
                        //dd($student);
                        if ($student) {
                            $numDocValid = ($student and $student->codigo_pago);
                            $item->is_valid = $numDocValid;
                            $item->message = $numDocValid ? 'Ok' : 'no existe num. Codigo de Pago relacionado';
                        } else {
                            $item->is_valid = false;
                            $item->codigo = $eItem->codigo;
                            $item->message = 'no hay registro relacionados';
                        }
                        $client = (object)array_merge((array)$client, (array)$item);
                        array_push($data, $client);
                    });
                });
                usort($data, function ($a, $b) {
                    return ($a->is_valid < $b->is_valid) ? 1 : -1;
                });
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showAdmissionByCode(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $codigo = $request->codigo;
                $item = IncomeData::showAdmissionByCode($codigo);
        
                if (!empty($item)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $item;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addDepositAdmissionMassive()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $session = $jResponse;
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_deposito = null;
        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        try {
                            DB::beginTransaction();
                            try {
                                $body = json_decode(file_get_contents("php://input"));
                                $operations = array();
                                $deposits = $body;//->deposits;
                                foreach ($deposits as $params) {
//                                    
                                    $importe = $params->importe;
                                    if ($importe > 0) {
                                        $error = 0;
                                        $msgerror = "";
                                        for ($x = 1; $x <= 200; $x++) {
                                            $msgerror .= "0";
                                        }
                                        
                                        if ($id_depto == "1"){
                                            $tipo_asiento = "MB";
                                        }elseif($id_depto == "5"){
                                            $tipo_asiento = "MBJ";
                                        }elseif($id_depto == "6"){
                                            $tipo_asiento = "MBT";
                                        }elseif($id_depto == "8"){
                                            $tipo_asiento = "MBS";
                                        }else{
                                            $tipo_asiento = "MB";
                                        }
                                        $isAutomatic = "N";
                                        $id_mediopago = "001";
                                        $id_tipotarjeta = "";
                                        $tc = $tipcam["denominacional"];
                                        $importe_trans = 0;
                                        //if ($params->id_mediopago != '008') {
                                            $importe_trans = $params->importe;
                                            $importe = 0;
                                            $venta_ids = "";
                                            $imp_ventas = "";
                                        //}

                                        $i = 1;
                                        $array_date = explode('/',$params->fecha);
                                        $date_op = "{$array_date[2]}-{$array_date[1]}-{$array_date[0]}";
                                        if (!empty($params->id_vale)) {
                                            $id_vale = $params->id_vale;
                                        } else {
                                            $id_vale = null;
                                        }
                                        $pdo = DB::getPdo();
                                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_ALUMNO_ADM(
                                    :P_ID_ENTIDAD,
                                    :P_ID_DEPTO,
                                    :P_ID_ANHO,
                                    :P_ID_MES,
                                    :P_ID_MEDIOPAGO,
                                    :P_ID_PERSONA,                                          
                                    :P_ID_CLIENTE,
                                    :P_ID_PERSONA_INSCRIPCION,
                                    :P_ID_MONEDA,
                                    :P_ID_DINAMICA,
                                    :P_ID_TIPOTARJETA,
                                    :P_ID_CTABANCARIA,
                                    :P_OPERACION,
                                    :P_FECHA_OP,
                                    :P_IMPORTE,
                                    :P_IMPORTE_TARJETA,
                                    :P_IMPORTE_ME,
                                    :P_TIPOCAMBIO,
                                    :P_GLOSA,
                                    :P_NOMBRE_DEP,
                                    :P_DOCUMENTO_DEP,
                                    :P_ID_TIPOASIENTO,
                                    :P_ERROR,
                                    :P_MSGERROR, 
                                    :P_ID_DEPOSITO); end;");
                                        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CLIENTE', $params->id_persona, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_PERSONA_INSCRIPCION', $params->id_persona_inscripcion, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_MONEDA', $params->id_moneda, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CTABANCARIA', $params->id_ctabancaria, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_OPERACION', $params->operacion, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_FECHA_OP', $date_op, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_trans, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_GLOSA', $params->glosa, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_NOMBRE_DEP', $params->nom_persona, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_DOCUMENTO_DEP', $params->num_documento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_TIPOASIENTO', $tipo_asiento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                                        $stmt->execute();

                                        // print_r('$error:'.$error);
                                        if ($error == 0) {//
//                                            dd('res');
                                            DB::commit();
                                            $jResponse['success'] = true;
                                            $jResponse['message'] = "The item was updated successfully";
                                            $jResponse['data'] = $id_deposito;
                                            $code = "200";
                                        } else {
                                            DB::rollback();
                                            $jResponse['success'] = false;
                                            $jResponse['message'] = $msgerror;
                                            $jResponse['data'] = [];
                                            $code = "202";
                                        }
                                    } else {
                                        DB::rollback();
                                        $jResponse['success'] = false;
                                        $jResponse['message'] = "El Importe debe ser Mayor a 0";
                                        $jResponse['data'] = [];
                                        $code = "202";
                                    }
                                }
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                                DB::rollback();
                            }
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addDepositAdmissionIndividual(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $session = $jResponse;
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_deposito = null;

        $id_mediopago = $request->id_mediopago;
        $id_persona = $request->id_persona;
        $id_persona_inscripcion = $request->id_persona_inscripcion;
        $id_moneda = $request->id_moneda;
        $id_dinamica = $request->id_dinamica;
        $importe = $request->importe;
        $glosa = $request->glosa;
        $nombre_dep = $request->nombre_dep;
        $documento_dep = $request->documento_dep;
        $date = Carbon::now();
        $date_op = $date->format('Y-m-d H:i:s');

        $isAutomatic = "N";
        $id_tipotarjeta = "";
        $id_ctabancaria = "";
        $importe_trans = 0;
        $venta_ids = "";
        $imp_ventas = "";
        $operacion = null;
        $importe_me = 0;
        $id_vale = null;

        if ($id_moneda == '9') {
            $importe_me = $importe;
            $importe = 0;
        }

        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        try {
                            DB::beginTransaction();
                            try {
                                    if ($importe > 0) {
                                        $error = 0;
                                        $msgerror = "";
                                        for ($x = 1; $x <= 200; $x++) {
                                            $msgerror .= "0";
                                        }
                                        
                                        if ($id_depto == "1"){
                                            $tipo_asiento = "MB";
                                        }elseif($id_depto == "5"){
                                            $tipo_asiento = "MBJ";
                                        }elseif($id_depto == "6"){
                                            $tipo_asiento = "MBT";
                                        }elseif($id_depto == "8"){
                                            $tipo_asiento = "MBS";
                                        }else{
                                            $tipo_asiento = "MB";
                                        }
                                    
                                        $tc = $tipcam["denominacional"];
                          
                                        $pdo = DB::getPdo();
                                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_ALUMNO_ADM(
                                                                :P_ID_ENTIDAD,
                                                                :P_ID_DEPTO,
                                                                :P_ID_ANHO,
                                                                :P_ID_MES,
                                                                :P_ID_MEDIOPAGO,
                                                                :P_ID_PERSONA,                                          
                                                                :P_ID_CLIENTE,
                                                                :P_ID_PERSONA_INSCRIPCION,
                                                                :P_ID_MONEDA,
                                                                :P_ID_DINAMICA,
                                                                :P_ID_TIPOTARJETA,
                                                                :P_ID_CTABANCARIA,
                                                                :P_OPERACION,
                                                                :P_FECHA_OP,
                                                                :P_IMPORTE,
                                                                :P_IMPORTE_TARJETA,
                                                                :P_IMPORTE_ME,
                                                                :P_TIPOCAMBIO,
                                                                :P_GLOSA,
                                                                :P_NOMBRE_DEP,
                                                                :P_DOCUMENTO_DEP,
                                                                :P_ID_TIPOASIENTO,
                                                                :P_ERROR,
                                                                :P_MSGERROR, 
                                                                :P_ID_DEPOSITO); end;");
                                        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MEDIOPAGO', $id_mediopago, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CLIENTE', $id_persona, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_PERSONA_INSCRIPCION', $id_persona_inscripcion, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CTABANCARIA', $id_ctabancaria, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_FECHA_OP', $date_op, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_trans, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_NOMBRE_DEP', $nombre_dep, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_DOCUMENTO_DEP', $documento_dep, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_TIPOASIENTO', $tipo_asiento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                                        $stmt->execute();

                                        // print_r('$error:'.$error);
                                        if ($error == 0) {//
                                            DB::commit();
                                            $jResponse['success'] = true;
                                            $jResponse['message'] = "The item was updated successfully";
                                            $jResponse['data'] = $id_deposito;
                                            $code = "200";
                                        } else {
                                            DB::rollback();
                                            $jResponse['success'] = false;
                                            $jResponse['message'] = $msgerror;
                                            $jResponse['data'] = [];
                                            $code = "202";
                                        }
                                    } else {
                                        DB::rollback();
                                        $jResponse['success'] = false;
                                        $jResponse['message'] = "El Importe debe ser Mayor a 0";
                                        $jResponse['data'] = [];
                                        $code = "202";
                                    }
                   
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                                DB::rollback();
                            }
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addDepositStudentBank()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $session = $jResponse;
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_deposito = null;

        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');

        if ($valida == 'SI') {
            $jResponse = [];
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
                $id_anho_actual = $item->id_anho_actual;
            }
            if ($id_anho == $id_anho_actual) {
                $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
                foreach ($data_mes as $item) {
                    $id_mes = $item->id_mes;
                    $id_mes_actual = $item->id_mes_actual;
                }
                if ($id_mes == $id_mes_actual) {
                    $tipcam = GlobalMethods::verificaTipoCambio();

                    if ($tipcam["tc"] == true) {
                        try {
                            DB::beginTransaction();
                            try {
                                $body = json_decode(file_get_contents("php://input"));
                                $operations = array();
                                $deposits = $body->deposits;
                                foreach ($deposits as $params) {
                                    $importe = $params->importe;
                                    if ($importe > 0) {
                                        $error = 0;
                                        $msgerror = "";
                                        for ($x = 1; $x <= 200; $x++) {
                                            $msgerror .= "0";
                                        }
                                        
                                        if ($id_depto == "1"){
                                            $tipo_asiento = "MB";
                                        }elseif($id_depto == "5"){
                                            $tipo_asiento = "MBJ";
                                        }elseif($id_depto == "6"){
                                            $tipo_asiento = "MBT";
                                        }elseif($id_depto == "8"){
                                            $tipo_asiento = "MBS";
                                        }else{
                                            $tipo_asiento = "MB";
                                        }
                                        $isAutomatic = "N";
                                        $id_mediopago = "001";
                                        $id_tipotarjeta = "";
                                        $tc = $tipcam["denominacional"];
                                        $importe_trans = 0;
                                        $importe_trans = $params->importe;
                                        $importe = 0;
                                        $venta_ids = "";
                                        $imp_ventas = "";

                                        $id_tipodeposito = "";

                                        $i = 1;
                                        
                                        // $array_date = explode('/',$params->fecha_operacion);
                                        // $date_op = "{$array_date[2]}-{$array_date[1]}-{$array_date[0]}";
                                        $date_op = $params->fecha_operacion;

                                        $pdo = DB::getPdo();
                                        $stmt = $pdo->prepare("begin PKG_CAJA.SP_CREAR_DEPOSITO_ALUMNO(
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO,
                                        :P_ID_ANHO,
                                        :P_ID_MES,
                                        :P_ID_MEDIOPAGO,
                                        :P_ID_PERSONA,                                          
                                        :P_ID_CLIENTE,
                                        :P_AUTOMATICO,
                                        :P_VENTAS,
                                        :P_IMP_VENTAS,
                                        :P_ID_TIPOTRANSACCION,
                                        :P_ID_MONEDA,
                                        :P_ID_DINAMICA,
                                        :P_ID_TIPOTARJETA,
                                        :P_ID_CTABANCARIA,
                                        :P_OPERACION,
                                        :P_FECHA_OP,
                                        :P_IMPORTE,
                                        :P_IMPORTE_TARJETA,
                                        :P_IMPORTE_ME,
                                        :P_TIPOCAMBIO,
                                        :P_GLOSA,
                                        :P_NOMBRE_DEP,
                                        :P_DOCUMENTO_DEP,
                                        :P_ID_TIPOASIENTO,
                                        :P_ERROR,
                                        :P_MSGERROR, :P_ID_DEPOSITO); end;");
                                        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MEDIOPAGO', $params->id_mediopago, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CLIENTE', $params->id_cliente, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_AUTOMATICO', $isAutomatic, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_VENTAS', $venta_ids, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMP_VENTAS', $imp_ventas, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipodeposito, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_MONEDA', $params->id_moneda, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_DINAMICA', $params->id_dinamica, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_TIPOTARJETA', $id_tipotarjeta, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_ID_CTABANCARIA', $params->id_ctabancaria, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_OPERACION', $params->nro_operacion, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_FECHA_OP', $date_op, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_TARJETA', $importe_trans, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_IMPORTE_ME', $params->importe_me, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_TIPOCAMBIO', $tc, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_GLOSA', $params->glosa, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_NOMBRE_DEP', $params->nom_persona, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_DOCUMENTO_DEP', $params->num_documento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_TIPOASIENTO', $tipo_asiento, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                                        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                                        $stmt->bindParam(':P_ID_DEPOSITO', $id_deposito, PDO::PARAM_INT);
                                        $stmt->execute();
                                        if ($error == 0) {

                                            $result = IncomeData::updateFinishDepositsVouchesEstudents($params->id_dfile, '2', $id_deposito, 'Finalizado con éxito', $id_user, $fecha_reg);
                                            if ($result['success']) {
                                                DB::commit();
                                                $jResponse['success'] = true;
                                                $jResponse['message'] = "The item was updated successfully";
                                                $jResponse['data'] = $id_deposito;
                                                $code = "200";
                                            } else {
                                                DB::rollback();
                                                $jResponse['success'] = false;
                                                $jResponse['message'] = $result['message'];
                                                $jResponse['data'] = [];
                                                $code = "202";
                                            }
                                           
                                        } else {
                                            DB::rollback();
                                            $jResponse['success'] = false;
                                            $jResponse['message'] = $msgerror;
                                            $jResponse['data'] = [];
                                            $code = "202";
                                        }
                                    } else {
                                        DB::rollback();
                                        $jResponse['success'] = false;
                                        $jResponse['message'] = "El Importe debe ser Mayor a 0";
                                        $jResponse['data'] = [];
                                        $code = "202";
                                    }
                                }
                            } catch (Exception $e) {
                                $jResponse['success'] = false;
                                $jResponse['message'] = $e->getMessage();
                                $jResponse['data'] = [];
                                $code = "202";
                                DB::rollback();
                            }
                        } catch (Exception $e) {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $e->getMessage();
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Actualice TIPO de CAMBIO!!!";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No Existe Mes Activo!!!";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Existe Año Activo!!!";
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function voucherDepositsStudentsPortal(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::voucherDepositsStudentsPortal($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
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
        return response()->json($jResponse, $code);
    }
    public function viewFileDepositos(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $result = IncomeData::viewFileDepositos($request);
                if($result['success']){
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                }else{
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateDepositsVouchesEstudents($id_dfile, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $result = IncomeData::updateDepositsVouchesEstudents($id_dfile, $request, $id_user, $fecha_reg);
                if($result['success']){
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
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
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::listBank($request, $id_entidad, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
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
        return response()->json($jResponse, $code);
    }
    public function getPrrocess($id_dfile)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::getPrrocess($id_dfile);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = $data;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getCajeroVouchers()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = IncomeData::getCajeroVouchers($id_user);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = $data;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function nextOrRefusedPaso($id_dfile, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $result = IncomeData::nextOrRefusedPaso($id_dfile, $request, $id_user, $fecha_reg);
                if($result['success']){
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = $result['success'];
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
                DB::rollback();
            }
        }
        return response()->json($jResponse, $code);
    }
}

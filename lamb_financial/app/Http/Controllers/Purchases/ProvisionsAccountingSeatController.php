<?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 6/03/2019
 * Time: 17:13
 */

namespace App\Http\Controllers\Purchases;


use App\Http\Data\Purchases\PurchasesData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use PDO;

class ProvisionsAccountingSeatController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index() {
    }

    public function update($id_compra, $id_casiento) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI') {
            $jResponse = [];

            try {
                $rules = $this->rulesAccountindSeat();
                $validator = Validator::make(Input::all(), $rules);
                if ($validator->fails())
                {
                    $errorString = implode(",",$validator->messages()->all());
                    $jResponse['success'] = false;
                    $jResponse['message'] = $errorString;
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }
                // Preprarar data
                $id_fondo = Input::get('id_fondo');
                $id_depto = Input::get('id_depto');
                $id_cuentaaasi = Input::get('id_cuentaaasi');
                $id_restriccion = Input::get('id_restriccion');
                $id_ctacte = Input::get('id_ctacte');
                $importe = Input::get('importe');
                $is_dc = Input::get('is_dc');
                $descripcion = Input::get('descripcion');

                $valida_si_requiere_cta_cte = PurchasesData::chooserAasinet($id_entidad, $id_cuentaaasi);
                if(
                        $valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'S' 
                    ){
                        if (is_null($id_ctacte)){
                            $jResponse['success'] = false;
                            $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                            $jResponse['data'] = null;
                            $code = "202";
                            goto end;
                        }
                        if (trim($id_ctacte) == ''){
                            $jResponse['success'] = false;
                            $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                            $jResponse['data'] = null;
                            $code = "202";
                            goto end;
                        }
                }
                if(
                    $valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'N' &&
                    trim($id_ctacte) != ''
                ){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "La cuenta $id_cuentaaasi no requiere de Cuenta Corriente";
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }

                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_ACCOUNTING_SEAT_ACTUALIZAR(:P_ID_FONDO, :P_ID_DEPTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION,
                :P_ID_CTACTE, :P_IMPORTE, :P_IS_DC, :P_DESCRIPCION, :P_ID_COMPRA, :P_ERROR, :P_ID_CASIENTO); end;");
                $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT); //
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR); //
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR); //
                $stmt->bindParam(':P_IS_DC', $is_dc, PDO::PARAM_STR); //
                $stmt->bindParam(':P_DESCRIPCION', $descripcion, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT); //

                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CASIENTO', $id_casiento, PDO::PARAM_INT);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_casiento;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        end:
        return response()->json($jResponse,$code);
    }

    public function show($id_compra, $id_accounting_seat) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = PurchasesData::getCompraAsientoById($id_accounting_seat, $id_entidad);
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
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function getListbByIdCompra($id_compra) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $compra = PurchasesData::ShowCompraEmpresa($id_compra);
                foreach ($compra as $item){
                    $id_anho = $item->id_anho;
                    $id_empresa = $item->id_empresa;
                }
                // $data = PurchasesData::listPurchasesSeatsAcounting($id_compra,$id_anho,$id_empresa);
                
                $data = PurchasesData::getListCompraAsientoByIdCompra($id_compra,$id_anho,$id_empresa);
                $total = PurchasesData::listPurchasesSeatsAcountingTotal($id_compra);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    // $jResponse['data']    = [ "data"=>$data, "total"=>$total ];
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

    public function validarSiRequiereCtaCte($id_compra, $id_cuentaaasi)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                // $id_cuentaaasi = Input::get('id_cuentaaasi');

                $valida_si_requiere_cta_cte = PurchasesData::chooserAasinet($id_entidad, $id_cuentaaasi);
                if($valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'S'){
                    $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                    $jResponse['data'] = 'S';
                }
                if($valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'N'){
                    $jResponse['message'] = "La cuenta $id_cuentaaasi no requiere de Cuenta Corriente";
                    $jResponse['data'] = 'N';
                }
                $jResponse['success'] = true;
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

    private function rulesAccountindSeat()
    {
        return [
            'id_compra' => 'required',
            'id_ctacte' => '',
            'descripcion' => 'max:50',
            'id_cuentaaasi' => 'required',
            'id_depto' => 'required',
            'id_fondo' => 'required',
            'id_restriccion' => 'required',
            'importe' => 'required|numeric',
            //'is_dc' => 'required',
        ];
    }

    public function store($id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        if($valida=='SI') {
            $jResponse = [];
            $id_casiento = 0;
            try {

                $rules = $this->rulesAccountindSeat();
                $validator = Validator::make(Input::all(), $rules);
                if ($validator->fails())
                {
                    $errorString = implode(",",$validator->messages()->all());
                    $jResponse['success'] = false;
                    $jResponse['message'] = $errorString;
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }

                // Preprarar data
                $id_fondo = Input::get('id_fondo');
                $id_depto = Input::get('id_depto');
                $id_cuentaaasi = Input::get('id_cuentaaasi');
                $id_restriccion = Input::get('id_restriccion');
                $id_ctacte = Input::get('id_ctacte');
                $importe = Input::get('importe');
                $is_dc = Input::get('is_dc');
                $descripcion = Input::get('descripcion');
                $id_compra = Input::get('id_compra');
                $valida_si_requiere_cta_cte = PurchasesData::chooserAasinet($id_entidad, $id_cuentaaasi);
                if(
                    $valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'S' 
                ){
                    if (is_null($id_ctacte)){
                        $jResponse['success'] = false;
                        $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                    if (trim($id_ctacte) == ''){
                        $jResponse['success'] = false;
                        $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                }
                if(
                    $valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'N' &&
                    trim($id_ctacte) != ''
                ){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "La cuenta $id_cuentaaasi no requiere de Cuenta Corriente";
                    $jResponse['data'] = null;
                    $code = "202";
                    goto end;
                }


                $error = 0;
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_ACCOUNTING_SEAT_GUARDAR(:P_ID_FONDO, :P_ID_DEPTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION,
                :P_ID_CTACTE, :P_IMPORTE, :P_IS_DC, :P_DESCRIPCION, :P_ID_COMPRA, :P_ERROR, :P_ID_CASIENTO); end;");
                $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT); //
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR); //
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR); //
                $stmt->bindParam(':P_IS_DC', $is_dc, PDO::PARAM_STR); //
                $stmt->bindParam(':P_DESCRIPCION', $descripcion, PDO::PARAM_STR); //
                $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT); //

                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CASIENTO', $id_casiento, PDO::PARAM_INT);
                $stmt->execute();
                if ($error == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $id_casiento;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Error";
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
        end:
        return response()->json($jResponse,$code);
    }


    private function rulesAccountindSeatImport()
    {
        return [
            'cta_cte' => '',
            'cuenta' => 'required',
            'depto' => 'required',
            'fondo' => 'required',
            'restriccion' => 'required',
            'importe' => 'required|numeric',
            'dc' => 'required',
            'glosa' => 'max:50',
        ];
    }
    public function storeImport(Request $request, $id_compra)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        // $id_depto = $jResponse["id_depto"];
        if($valida=='SI') {
            $jResponse = [];
            // $id_casiento = 0;
            DB::beginTransaction();
            try {

                $import_data = \Excel::load($request->excel, function($reader) use($request) {
                    $reader->select(array('fondo', 'cuenta', 'restriccion', 'cta_cte', 'depto', 'importe','dc','glosa'))->get();
                })->get();

                // $import_data_filter = array_filter($import_data->toArray(), function($row) {
                //     return (!is_null($row['codigo']) && !empty($row['codigo']));
                // });

                // if(empty($import_data_filter && sizeOf($import_data_filter))) {
                //     throw new Exception('Alto! La lista del excel esta vacía', 1);
                // }
                
                // VALIDAR
                foreach ($import_data->toArray() as $value) {
                    $validator = Validator::make($value, $this->rulesAccountindSeatImport());
                    if ($validator->fails())
                    {
                        $errorString = implode(",",$validator->messages()->all());
                        $jResponse['success'] = false;
                        $jResponse['message'] = $errorString;
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                    $id_cuentaaasi = $value['cuenta'];
                    $id_ctacte = $value['cta_cte'];

                    $valida_si_requiere_cta_cte = PurchasesData::chooserAasinet($id_entidad, $id_cuentaaasi);
                    if($valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'S'){
                        if (is_null($id_ctacte)){
                            $jResponse['success'] = false;
                            $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                            $jResponse['data'] = null;
                            $code = "202";
                            goto end;
                        }
                        if (trim($id_ctacte) == ''){
                            $jResponse['success'] = false;
                            $jResponse['message'] = "La cuenta $id_cuentaaasi requiere de Cuenta Corriente";
                            $jResponse['data'] = null;
                            $code = "202";
                            goto end;
                        }
                    }
                    if($valida_si_requiere_cta_cte[0]->requiere_cta_cte == 'N' && trim($id_ctacte) != '') {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "La cuenta $id_cuentaaasi no requiere de Cuenta Corriente";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }                                               
                }

                // $id_compra = Input::get('id_compra');
                foreach ($import_data->toArray() as $value) {
                    $id_fondo = $value['fondo'];
                    $id_depto = $value['depto'];
                    $id_cuentaaasi = $value['cuenta'];
                    $id_restriccion = $value['restriccion'];
                    $id_ctacte = $value['cta_cte'];
                    $importe = $value['importe'];
                    $is_dc = $value['dc'];
                    $descripcion = $value['glosa'];
                    $error = 0;
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_ACCOUNTING_SEAT_GUARDAR(:P_ID_FONDO, :P_ID_DEPTO, :P_ID_CUENTAAASI, :P_ID_RESTRICCION,
                    :P_ID_CTACTE, :P_IMPORTE, :P_IS_DC, :P_DESCRIPCION, :P_ID_COMPRA, :P_ERROR, :P_ID_CASIENTO); end;");
                    $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT); //
                    $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_IS_DC', $is_dc, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_DESCRIPCION', $descripcion, PDO::PARAM_STR); //
                    $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT); //

                    $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                    $stmt->bindParam(':P_ID_CASIENTO', $id_casiento, PDO::PARAM_INT);
                    $stmt->execute();
                    if ($error === 1) {
                        throw new Exception('Error en el procedimiento.', 1);
                    }
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = [];
                $code = "200";
                DB::commit();
            }
            catch(Exception $e)
            {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = null;
                $jResponse['code'] = "202";
            }
        }
        end:
        return response()->json($jResponse,$code);
    }

    public function destroy($compra_id, $id_accounting_seat) {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI') {
            $msn = "";
            try {
                $data = PurchasesData::deleteCompraAsiento($id_accounting_seat);
                $data2 = PurchasesData::deleteCompraAsiento_children($id_accounting_seat);

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
}
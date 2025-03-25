<?php

namespace App\Http\Controllers\Treasury;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;
use PDO;
use DOMPDF;
use App\Http\Data\Treasury\TaxDocumentsData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Carbon\Carbon;
class TaxDocumentsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function addMyDocuments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if($valida=='SI'){
            $jResponse=[];
            try{

                //Obtengo el TC
                $tiene_params = "S";
                $rpta = AccountingData::AccountingYearMonthTC($id_entidad, 7, $tiene_params,'S');
                if ($rpta["nerror"] == 0) {
                    $tc = $rpta["tc"];
                }

                DB::beginTransaction();
               $result = TaxDocumentsData::addMyDocuments($request, $id_user, $id_entidad, $id_depto, $fecha_reg,$tc);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
                DB::rollback();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addAuthorizeRefusedDocuments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if($valida=='SI'){
            $jResponse=[];
            try{
                DB::beginTransaction();
               $result = TaxDocumentsData::addAuthorizeRefusedDocuments($request, $id_user, $fecha_reg);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
                DB::rollback();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listMyDocument(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $data = TaxDocumentsData::listMyDocument($request, $id_entidad, $id_depto, $id_user);
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function getProcesosDocuments()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $data = TaxDocumentsData::getProcesosDocuments();
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteMyDocuments($id_documento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                DB::beginTransaction();
               $result = TaxDocumentsData::deleteMyDocuments($id_documento);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
                DB::rollback();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addSeats(Request $request) 
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $result = TaxDocumentsData::addSeats($request);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addSeatsTransaction(Request $request) 
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $result = TaxDocumentsData::addSeatsTransaction($request);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateSeats(Request $request, $id_casiento) 
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $result = TaxDocumentsData::updateSeats($request, $id_casiento);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listSeats($id_documento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $data = TaxDocumentsData::listSeats($id_documento);
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteAsientoDocumeto($id_casiento) 
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $result = TaxDocumentsData::deleteAsientoDocumeto($id_casiento);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function processDocuments($id_documento)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $data = TaxDocumentsData::processDocuments($id_documento);
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function getYearDocuments()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $data = TaxDocumentsData::getYearDocuments();
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function listPgastoMyDocument(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $data = TaxDocumentsData::listPgastoMyDocument($request, $id_entidad, $id_depto, $id_user);
                if  (count($data)>0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addGastoMyDocument(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $date = Carbon::now();
        $fecha_reg = $date->format('Y-m-d H:m:s');
        if($valida=='SI'){
            $jResponse=[];
            try{
                DB::beginTransaction();
               $result = TaxDocumentsData::addGastoMyDocument($request, $fecha_reg, $id_entidad);
                if  ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                    DB::commit();
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                    DB::rollback();
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
                DB::rollback();
            }
        }
        return response()->json($jResponse,$code);
    }
    public function getPaymentDocument($id_pago)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $object = TaxDocumentsData::getPaymentDocument($id_pago);
                if  (!empty($object)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $object;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function getSeatsPagoDocument($id_pgasto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
               $object = TaxDocumentsData::getSeatsPagoDocument($id_pgasto);
                if  (!empty($object)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'Exito';
                    $jResponse['data'] = $object;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Sin resultados';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse,$code);
    }
}
?>
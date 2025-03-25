<?php

namespace App\Http\Controllers\HumanTalent;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalent\PaymentsData;
// use App\Http\Data\Orders\OrdersData;
use App\Http\Data\APSData;
use App\Http\Data\SetupData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use PDF;
use DOMPDF;
use App\qrcode;
use Mail;
use Response;
use Excel;
use Swift_Mailer;
use App\Mail\SendBoleta;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Psr7\MimeType;
use App\Http\Data\Utils\SendEmail; 

class PaymentsController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function anhoPayments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_persona = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PaymentsData::anhoPayments($id_entidad, $id_persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    /*public function generatePaymentsTickets(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];
            $planilla = [];
            $gen=0;
            $nogen=0;
            $msgerror="";
            try{

                $params      = json_decode(file_get_contents("php://input"));
                $id_entidad  = $params->id_entidad;
                $id_anho     = $params->id_anho;
                $id_mes      = $params->id_mes;
                $option      = $params->option;
                $id_persona  = $params->id_persona;
                $id_depto    = $params->id_depto;


                $retdir      =  PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes);
                if($retdir["nerror"]==0){
                    $entidad  = APSData::entidadPersona($id_entidad);

                    $id_persona_enti  = 0;
                    foreach ($entidad as $data) {
                        $id_persona_enti = $data->id_persona;
                    }

                    $empresa = APSData::entidadEmpresa($id_persona_enti);

                    if ($option == true) {
                        if(strlen($id_persona)==0){

                            $id_persona = 0;
                        }
                        $data_persona = APSData::personPlanilla($id_entidad,$id_anho,$id_mes,$id_persona);
                        foreach($data_persona as $row){
                            $id_depto    = $row->id_depto_padre;
                        }
                    }else{
                        $id_persona = 0;
                    }
                    if(strlen($id_depto)>0){
                        $respuesta  = PaymentsData::validaGenerate($id_entidad,$id_anho,$id_mes,$id_depto,$id_persona);

                        if($respuesta["nerror"]==0){

                            $data_planilla  = APSData::entidadPlanilla($id_entidad,$id_depto,$id_anho,$id_mes,$id_persona);


                            if (count($data_planilla)>0){


                                foreach ($data_planilla as $key => $data) {
                                    $id_employe   = $data->id_persona;
                                    $id_contrato  = $data->id_contrato;
                                    $employee     = APSData::employee($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $remuneration = APSData::remuneration($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $retention    = APSData::retention($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $contribution = APSData::contribution($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $diezmo       = APSData::descuentos($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $tdiezmo      = APSData::tdescuentos($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $tremu        = APSData::tRemuneration($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $treten       = APSData::tRetention($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $tcontri      = APSData::tContribution($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $tneto        = APSData::Neto($id_entidad, $id_employe, $id_anho, $id_mes,$id_contrato);
                                    $employee1    = [];
                                    foreach ($employee as $key => $data_employee) {

                                        $employee1[] = ['nom_persona' => $data_employee->nom_persona,
                                            'nom_cargo' => $data_employee->nom_cargo,
                                            'mes' => $data_employee->mes,
                                            'essalud' => $data_employee->essalud,
                                            'cuss' => $data_employee->cuss,
                                            'fec_nacimiento' => $data_employee->fec_nacimiento,
                                            'num_documento' => $data_employee->num_documento,
                                            'fec_inicio' => $data_employee->fec_inicio,
                                            'fec_termino' => $data_employee->fec_termino,
                                            'dh' => $data_employee->dh,
                                            'vacaciones' => $data_employee->vacaciones,
                                            'afp' => $data_employee->afp,
                                            'mes_name' => $data_employee->mes_name];
                                    }
                                    $remuneration_item = [];
                                    foreach ($remuneration as $key => $data_remun) {
                                        $remuneration_item[] = ['nombre' => $data_remun->nombre, 'importe' => $data_remun->cos_valor];
                                    }
                                    $retention_item = [];
                                    foreach ($retention as $key => $data_reten) {
                                        $retention_item[] = ['nombre' => $data_reten->nombre, 'importe' => $data_reten->cos_valor];
                                    }
                                    $contribution_item = [];
                                    foreach ($contribution as $key => $data_contr) {
                                        $contribution_item[] = ['nombre' => $data_contr->nombre, 'importe' => $data_contr->cos_valor];
                                    }
                                    $diezmo_item = [];
                                    foreach ($diezmo as $key => $data_diezmo) {
                                        $diezmo_item[] = ['nombre' => $data_diezmo->nombre, 'importe' => $data_diezmo->cos_valor];
                                    }
                                    $tremu_item = [];
                                    foreach ($tremu as $key => $tremu) {
                                        $tremu_item[] = ['imp' => $tremu->imp];
                                    }
                                    $treten_item = [];
                                    foreach ($treten as $key => $treten) {
                                        $treten_item[] = ['imp' => $treten->imp];
                                    }
                                    $tcontri_item = [];
                                    foreach ($tcontri as $key => $tcontri) {
                                        $tcontri_item[] = ['imp' => $tcontri->imp];
                                    }
                                    $tdiezmo_item = [];
                                    foreach ($tdiezmo as $key => $tdiezmo) {
                                        $tdiezmo_item[] = ['imp' => $tdiezmo->imp];
                                    }
                                    $tneto_item = [];
                                    foreach ($tneto as $key => $tneto) {
                                        $tneto_item[] = ['imp' => $tneto->imp];
                                    }
                                    $company = [];
                                    foreach ($empresa as $key => $data) {

                                            $company[] = [
                                            'id_ruc' => $data->id_ruc,
                                            'nombre' => $data->nombre,
                                            'employee' => $employee1[0],
                                            'remuneration' => $remuneration_item,
                                            'retention' => $retention_item,
                                            'contribution' => $contribution_item,
                                            'diezmo' => $diezmo_item,
                                            't_remu' => $tremu_item,
                                            't_reten' => $treten_item,
                                            't_contri' => $tcontri_item,
                                            't_neto' => $tneto_item,
                                            't_diezmo' => $tdiezmo_item,
                                            'entity' => $id_entidad];

                                    }
                                    $planilla=[];
                                    array_push($planilla, array("datos" => $company));


                                    $id_certificado = $respuesta["certificado"];

                                    $ret = $this->generarPlantillaBoleta($planilla,$id_anho,$id_mes,$id_certificado,$id_depto);

                                    if( strlen($ret["html"])>0 and strlen($ret["p"])>0 and  strlen($ret["nombre_entidad"])>0 and strlen($ret["nomarchivo"])>0){

                                        $ret=$this->firmarBoletapdf($ret["html"],$ret["nombre_entidad"],$ret["nomarchivo"],$id_certificado,$employee,$id_depto,$ret["p"],$retdir["directorio"]);
                                        if($ret["nerror"]==0){
                                            $gen++;
                                        }else{
                                            $nogen++;
                                            $msgerror = $ret["msgerror"];
                                        }

                                    }else{
                                        $nogen++;
                                    }



                                    //$this->firmarBoletapdf($planilla,$id_anho,$id_mes,$id_entidad,$id_depto,$id_certificado);
                                }
                                if($gen==0){
                                    $jResponse['success'] = false;
                                    $jResponse['message'] = 'No se ha generado firma digital '.$msgerror;
                                    $jResponse['data'] = [];
                                    $code = "202";
                                }else{
                                    $mensaje="";
                                    if($gen>0 and $nogen==0){
                                        $mensaje="Se ha generado correctamente";
                                    }else{
                                        $mensaje="Se ha generado  ".$gen. " correctamente y ".$nogen." no se ha generado";
                                    }
                                    $jResponse['success'] = true;
                                    $jResponse['message'] = $mensaje;
                                    $jResponse['data'] = [];
                                    $code = "200";
                                }
                            }else{
                                $jResponse['success'] = false;
                                $jResponse['message'] = 'No hay data para generar';
                                $jResponse['data'] = [];
                                $code = "202";
                            }


                        }else{
                            $jResponse['success'] = false;
                            $jResponse['message'] = $respuesta["msgerror"];
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'No existe información de boleta para el periodo '.$id_anho.'-'.$id_mes.' para el personal('.$id_persona.')';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $retdir["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(\Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' '.$e->getLine().' '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";

            }

        }

        return response()->json($jResponse,$code);
    }*/
    public function generatePaymentsTickets()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $planilla = [];
            $gen = 0;
            $nogen = 0;
            $msgerror = "";
            try {

                $params         = json_decode(file_get_contents("php://input"));
                $id_entidad     = $params->id_entidad;
                $id_anho        = $params->id_anho;
                $id_mes         = $params->id_mes;
                $option         = $params->option;
                $id_persona     = $params->id_persona;
                $id_depto       = $params->id_depto;
                $id_certificado = $params->id_certificado;
                $clave          = $params->clave;

                $retdir      =  PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
                if ($retdir["nerror"] == 0) {
                    $entidad  = APSData::entidadPersona($id_entidad);

                    $id_persona_enti  = 0;
                    foreach ($entidad as $data) {
                        $id_persona_enti = $data->id_persona;
                    }

                    $empresa = APSData::entidadEmpresa($id_persona_enti);

                    if ($option == true) {
                        if (strlen($id_persona) == 0) {

                            $id_persona = 0;
                        }
                        $data_persona = APSData::personPlanilla($id_entidad, $id_anho, $id_mes, $id_persona);
                        foreach ($data_persona as $row) {
                            $id_depto    = $row->id_depto_padre;
                        }
                    } else {
                        $id_persona = 0;
                    }
                    if (strlen($id_depto) > 0) {
                        $respuesta  = PaymentsData::validaGenerate($id_entidad, $id_anho, $id_mes, $id_depto, $id_persona, $id_certificado);

                        if ($respuesta["nerror"] == 0) {

                            $data_planilla  = APSData::entidadPlanilla($id_entidad, $id_depto, $id_anho, $id_mes, $id_persona);


                            if (count($data_planilla) > 0) {


                                foreach ($data_planilla as $key => $data) {
                                    $id_employe   = $data->id_persona;
                                    $id_contrato  = $data->id_contrato;
                                    $employee     = APSData::employee($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $remuneration = APSData::remuneration($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $retention    = APSData::retention($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $contribution = APSData::contribution($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $diezmo       = APSData::descuentos($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $tdiezmo      = APSData::tdescuentos($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $tremu        = APSData::tRemuneration($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $treten       = APSData::tRetention($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $tcontri      = APSData::tContribution($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $tneto        = APSData::Neto($id_entidad, $id_employe, $id_anho, $id_mes, $id_contrato);
                                    $employee1    = [];
                                    foreach ($employee as $key => $data_employee) {

                                        $employee1[] = [
                                            'nom_persona' => $data_employee->nom_persona,
                                            'nom_cargo' => $data_employee->nom_cargo,
                                            'mes' => $data_employee->mes,
                                            'essalud' => $data_employee->essalud,
                                            'cuss' => $data_employee->cuss,
                                            'fec_nacimiento' => $data_employee->fec_nacimiento,
                                            'num_documento' => $data_employee->num_documento,
                                            'fec_inicio' => $data_employee->fec_inicio,
                                            'fec_termino' => $data_employee->fec_termino,
                                            'dh' => $data_employee->dh,
                                            'vacaciones' => $data_employee->vacaciones,
                                            'afp' => $data_employee->afp,
                                            'mes_name' => $data_employee->mes_name
                                        ];
                                    }
                                    $remuneration_item = [];
                                    foreach ($remuneration as $key => $data_remun) {
                                        $remuneration_item[] = ['nombre' => $data_remun->nombre, 'importe' => $data_remun->cos_valor];
                                    }
                                    $retention_item = [];
                                    foreach ($retention as $key => $data_reten) {
                                        $retention_item[] = ['nombre' => $data_reten->nombre, 'importe' => $data_reten->cos_valor];
                                    }
                                    $contribution_item = [];
                                    foreach ($contribution as $key => $data_contr) {
                                        $contribution_item[] = ['nombre' => $data_contr->nombre, 'importe' => $data_contr->cos_valor];
                                    }
                                    $diezmo_item = [];
                                    foreach ($diezmo as $key => $data_diezmo) {
                                        $diezmo_item[] = ['nombre' => $data_diezmo->nombre, 'importe' => $data_diezmo->cos_valor];
                                    }
                                    $tremu_item = [];
                                    foreach ($tremu as $key => $tremu) {
                                        $tremu_item[] = ['imp' => $tremu->imp];
                                    }
                                    $treten_item = [];
                                    foreach ($treten as $key => $treten) {
                                        $treten_item[] = ['imp' => $treten->imp];
                                    }
                                    $tcontri_item = [];
                                    foreach ($tcontri as $key => $tcontri) {
                                        $tcontri_item[] = ['imp' => $tcontri->imp];
                                    }
                                    $tdiezmo_item = [];
                                    foreach ($tdiezmo as $key => $tdiezmo) {
                                        $tdiezmo_item[] = ['imp' => $tdiezmo->imp];
                                    }
                                    $tneto_item = [];
                                    foreach ($tneto as $key => $tneto) {
                                        $tneto_item[] = ['imp' => $tneto->imp];
                                    }
                                    $company = [];
                                    foreach ($empresa as $key => $data) {

                                        $company[] = [
                                            'id_ruc' => $data->id_ruc,
                                            'nombre' => $data->nombre,
                                            'employee' => $employee1[0],
                                            'remuneration' => $remuneration_item,
                                            'retention' => $retention_item,
                                            'contribution' => $contribution_item,
                                            'diezmo' => $diezmo_item,
                                            't_remu' => $tremu_item,
                                            't_reten' => $treten_item,
                                            't_contri' => $tcontri_item,
                                            't_neto' => $tneto_item,
                                            't_diezmo' => $tdiezmo_item,
                                            'entity' => $id_entidad
                                        ];
                                    }
                                    $planilla = [];
                                    array_push($planilla, array("datos" => $company));


                                    //$id_certificado = $respuesta["certificado"];

                                    $ret = $this->generarPlantillaBoleta($planilla, $id_anho, $id_mes, $id_certificado, $id_depto);

                                    if (strlen($ret["html"]) > 0 and strlen($ret["p"]) > 0 and  strlen($ret["nombre_entidad"]) > 0 and strlen($ret["nomarchivo"]) > 0) {

                                        $ret = $this->firmarBoletapdf($ret["html"], $ret["nombre_entidad"], $ret["nomarchivo"], $id_certificado, $employee, $id_depto, $ret["p"], $retdir["directorio"], $clave);
                                        if ($ret["nerror"] == 0) {
                                            $gen++;
                                        } else {
                                            $nogen++;
                                            $msgerror = $ret["msgerror"];
                                        }
                                    } else {
                                        $nogen++;
                                    }



                                    //$this->firmarBoletapdf($planilla,$id_anho,$id_mes,$id_entidad,$id_depto,$id_certificado);
                                }
                                if ($gen == 0) {
                                    $jResponse['success'] = false;
                                    $jResponse['message'] = 'No se ha generado firma digital ' . $msgerror;
                                    $jResponse['data'] = [];
                                    $code = "202";
                                } else {
                                    $mensaje = "";
                                    if ($gen > 0 and $nogen == 0) {
                                        $mensaje = "Se ha generado correctamente";
                                    } else {
                                        $mensaje = "Se ha generado  " . $gen . " correctamente y " . $nogen . " no se ha generado";
                                    }
                                    $jResponse['success'] = true;
                                    $jResponse['message'] = $mensaje;
                                    $jResponse['data'] = [];
                                    $code = "200";
                                }
                            } else {
                                $jResponse['success'] = false;
                                $jResponse['message'] = 'No hay data para generar';
                                $jResponse['data'] = [];
                                $code = "202";
                            }
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = $respuesta["msgerror"] . '*' . $id_certificado;
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'No existe información de boleta para el periodo ' . $id_anho . '-' . $id_mes . ' para el personal(' . $id_persona . ')';
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $retdir["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (\Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }

        return response()->json($jResponse, $code);
    }
    public function listPaymentTicket(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_anho = $request->query('id_anho');
                $data = PaymentsData::listPaymentTicket($id_entidad, $id_anho, $id_user);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public  function deletePaymentTicket($clave)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $ret = PaymentsData::deletePaymentTicket($clave);

                if ($ret['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ret['msgerror'];
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
    public function listProcessTicket(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $data = PaymentsData::listProcessTicket($id_entidad, $id_anho, $id_mes);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listProcessTicketArea(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_depto = $request->query('id_depto');
                $data = PaymentsData::listProcessTicketArea($id_entidad, $id_anho, $id_mes, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] =  'File: ' . $e->getFile() . ' line: ' . $e->getLine() . ' ORA-' . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listProcessTicketPerson(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_depto = $request->query('id_depto');
                $data = PaymentsData::listProcessTicketPerson($id_entidad, $id_anho, $id_mes, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] =  'File: ' . $e->getFile() . ' line: ' . $e->getLine() . ' ORA-' . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function generarPlantillaBoleta($data, $id_anho, $id_mes, $id_certificado, $id_depto)
    {
        $qrcodigo = "";

        $html = '';

        $dataFirma = PaymentsData::showCertificate($id_certificado);

        $firma = "";

        $p = "";

        $representante = "";
        $representantedoc = "";

        $nombre_general = "";
        $ubicacion = "";
        $nomarchivo = "";
        foreach ($dataFirma as $row) {

            $firma = $row->firma;

            $representante = $row->representante;
            $representantedoc = $row->num_documento;
        }

        foreach ($data as $item) {

            foreach ($item['datos'] as $item) {
                $html .= '<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
                $html .= '<tr>';
                $html .= '<td coslpan="2" style="background-color: #992E45;text-align: center;font-size: 8px;color: #FFFFFF;">BOLETA DE PAGO DE REMUNERACIONES</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><br/></td>';
                $html .= '<td><br/></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $ruta = asset('img/upeu.png');
                $html .= '<td style="width:36%;" rowspan="2"><br/><img src="' . $ruta . '" width="53" height="51"></td>';
                $nombre_general = $item['nombre'];
                $html .= '<td style="width:64%; font-size: 11px; font-family: "Times New Roman", Georgia, Serif;">' . $item['nombre'] . '</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
                $html .= '<tr>';
                $html .= '<td><br/></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td  style="text-align: center;font-size: 8px;">Expresado en Soles<br/>Decreto Supremo N. 15-72 del 28/09/72<br/>RUC: ' . $item['id_ruc'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><br/></td>';
                $html .= '</tr>';
                $html .= '</table>';

                $html .= '<table style="width:100%;font-family: "Times New Roman", Georgia, Serif;">';
                $html .= '<tr>';
                $html .= '<td style="width:50%;">';
                $html .= '<table class="table" style="font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';

                $html .= '<tr>';
                $html .= '<td><strong>Nombre:</strong></td>';
                $html .= '<td>' . $item['employee']['nom_persona'] . '</td>';
                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<td><strong>Cargo:</strong></td>';
                $html .= '<td>' . $item['employee']['nom_cargo'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Codigo ESSALUD:</strong></td>';
                $html .= '<td>' . $item['employee']['essalud'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Codigo CUSS:</strong></td>';
                $html .= '<td>' . $item['employee']['cuss'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Fecha de Nacimiento:</strong></td>';
                $html .= '<td>' . $item['employee']['fec_nacimiento'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Número de DNI:</strong></td>';
                $html .= '<td>' . $item['employee']['num_documento'] . '</td>';
                $html .= '</tr>';

                $html .= '</table>';
                $html .= '</td>';
                $html .= '<td style="width:50%;">';
                $html .= '<table style="font-size: 7px; font-family: "Times New Roman", Georgia, Serif;">';

                $html .= '<tr>';
                $html .= '<td><strong>Mes de Pago:</strong></td>';
                $html .= '<td>' . $item['employee']['mes'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Fecha de Ingreso:</strong></td>';
                $html .= '<td>' . $item['employee']['fec_inicio'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Fecha de Cese:</strong></td>';
                $html .= '<td>' . $item['employee']['fec_termino'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>Dias / Horas Trabajados:</strong></td>';
                $html .= '<td>' . $item['employee']['dh'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong> Vacaciones:</strong></td>';
                $html .= '<td>' . $item['employee']['vacaciones'] . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td><strong>AFP:</strong></td>';
                $html .= '<td>' . $item['employee']['afp'] . '</td>';
                $html .= '</tr>';

                $html .= '</table>';
                $html .= '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td colspan="2"><br/></td>';
                $html .= '</tr>';
                $html .= '</table>';



                $html .= '<table   style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;border-collapse: collapse;">';
                $html .= '<tr style="background-color: #992E45;text-align: center;font-size: 8px;color: #FFFFFF;">';
                $html .= '<th style="width:34%;border: 1px solid #992E45;">INGRESOS</th>';
                $html .= '<th style="width:33%;border: 1px solid #992E45;">APORTES DEL TRABAJADOR</th>';
                $html .= '<th style="width:33%;border: 1px solid #992E45;">DESCUENTOS</th>';
                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #992E45;" rowspan="2">';
                $html .= '<table style="width:100%; font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                foreach ($item['remuneration'] as $detalle) {

                    $html .= '<tr>';
                    $html .= '<td style="width:70%;">' . $detalle['nombre'] . '</td>';
                    $html .= '<td style="width:30%;text-align: right;">' . $detalle['importe'] . '</td>';
                    $html .= '</tr>';
                }
                foreach ($item['t_remu'] as $detalle) {

                    $html .= '<tr>';
                    $html .= '<td style="width:70%;"><strong>TOTAL</strong></td>';
                    $html .= '<td style="width:30%;text-align: right;"><strong>' . $detalle['imp'] . '</strong></td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                $html .= '</td>';
                $html .= '<td style="border: 1px solid #992E45;">';
                $html .= '<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';

                foreach ($item['retention'] as $detalle) {

                    $html .= '<tr>';
                    $html .= '<td style="width:70%;">' . $detalle['nombre'] . '</td>';
                    $html .= '<td style="width:30%;text-align: right;">' . $detalle['importe'] . '</td>';
                    $html .= '</tr>';
                }

                foreach ($item['t_reten'] as $detalle) {

                    $html .= '<tr>';
                    $html .= '<td style="width:70%;"><strong>TOTAL</strong></td>';
                    $html .= '<td style="width:30%;text-align: right;"><strong>' . $detalle['imp'] . '</strong></td>';
                    $html .= '</tr>';
                }

                $html .= '</table>';
                $html .= '</td>';
                $html .= '<td style="border: 1px solid #992E45;" rowspan="2">';
                $html .= '<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                foreach ($item['diezmo'] as $detalle) {
                    $html .= '<tr>';
                    $html .= '<td style="width:70%;">' . $detalle['nombre'] . '</td>';
                    $html .= '<td style="width:30%;text-align: right;">' . $detalle['importe'] . '</td>';
                    $html .= '</tr>';
                }
                foreach ($item['t_diezmo'] as $detalle) {
                    $html .= '<tr>';
                    $html .= '<td style="width:70%;"><strong>TOTAL</strong></td>';
                    $html .= '<td style="width:30%;text-align: right;">' . $detalle['imp'] . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                $html .= '</td>';
                $html .= '</tr>';

                //20138122256+2017+8+42188532+MARLO RIMARACHIN,Wilder+3,428.34
                $html .= '<tr>';

                $html .= '<td style="border: 1px solid #992E45;">';
                $html .= '<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';

                $html .= '<tr>';
                $html .= '<td colspan="2"><strong>APORTES DEL EMPLEADOR</strong></td>';
                $html .= '</tr>';
                foreach ($item['contribution'] as $detalle) {
                    $html .= '<tr>';
                    $html .= '<td style="width:70%;">' . $detalle['nombre'] . '</td>';
                    $html .= '<td style="width:30%;text-align: right;">' . $detalle['importe'] . '</td>';
                    $html .= '</tr>';
                }

                foreach ($item['t_contri'] as $detalle) {
                    $html .= '<tr>';
                    $html .= '<td style="width:70%;"><strong>TOTAL</strong></td>';
                    $html .= '<td style="width:30%;text-align: right;"><strong>' . $detalle['imp'] . '</strong></td>';
                    $html .= '</tr>';
                }

                $html .= '</table>';
                $html .= '</td>';

                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<table style="width:100%;font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                $html .= '<tr>';
                $html .= '<td><br/><br/></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td  style="width:50%; text-align: left;">';
                $html .= '<table style="font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                $neto = 0;
                foreach ($item['t_neto'] as $detalle) {
                    $html .= '<tr>';
                    $html .= '<td><strong>NETO A PAGAR</strong></td>';
                    $html .= '<td><strong>' . $detalle['imp'] . '</strong></td>';
                    $html .= '</tr>';

                    $neto = str_replace(",", '.', $detalle['imp']);
                }
                $html .= '</table>';
                $html .= '</td>';
                $html .= '<td  style="width:50%; text-align: right;">' . $item['employee']['mes_name'] . '</td>';
                $html .= '</tr>';
                $html .= '</table>';

                $qrcodigo = $item['id_ruc'] . $id_anho . $id_mes . $item['employee']['num_documento'] . $item['employee']['nom_persona'] . $neto;
                $key = $id_anho . $id_mes . $item['employee']['num_documento'];

                $nomarchivo = $item['employee']['num_documento'] . '-' . $id_anho . '-' . $id_mes;

                $html .= '<table  style="width:100%;font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                $html .= '<tr><td><br/></td></tr>';
                $html .= '<tr><td><br/></td></tr>';
                $html .= '<tr>';
                $html .= '<td  style="width:33%;font-size: 8px; text-align: center;">';
                if (strlen($firma) > 0) {
                    $ruta = asset('img/' . $firma);
                    $html .= '<img src="' . $ruta . '" width="50" height="40" style="margin-top: -10px !important;padding-top: -10px !important;margin-bottom: -10px !important;padding-bottom: -10px !important;">';
                }
                $html .= '</td>';
                $html .= '<td style="width:34%;text-align: center;" rowspan="2">';
                $qr = new qrcode();
                $p = password_hash($qrcodigo, PASSWORD_DEFAULT);
                $url_pdf = url('humantalent/payments-tickets-worker-download');
                $qr->link($url_pdf . "?p=" . $p);
                $html .= '<img src="' . $qr->get_link() . '" border="0" width="70" height="70"/>';
                $html .= '</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td  style="width:33%;font-size: 7px;text-align: center;">';
                $html .= '--------------------------------------------<br/>';
                $html .= 'EMPLEADOR<br/>';
                $html .= $representante . '<br/>';
                $html .= 'DNI: ' . $representantedoc . '<br/><br/>';
                $html .= '*Doc. Interno: ' . $id_certificado;


                $html .= '</td>';

                $html .= '<td  style="width:33%;font-size: 7px;text-align: center;">';
                $html .= '--------------------------------------------<br/>';
                $html .= 'TRABAJADOR<br/>';
                $html .= $item['employee']['nom_persona'] . '<br/>';
                $html .= 'DNI: ' . $item['employee']['num_documento'];

                $html .= '</td>';
                $html .= '</tr>';

                $html .= '</table>';

                $html .= '<table style="width:100%;font-size: 7px;">';
                $html .= '<tr><td><br/></td></tr>';
                $html .= '<tr>';
                $html .= '<td  style="font-size: 7px;">Documento firmado digitalmente por ' . $nombre_general . ' con fecha ' . date('d/m/Y') . '</td>';
                $html .= '</tr>';
                $html .= '</table>';
            }
        }

        $return = [
            'html' => $html,
            'p' => $p,
            'nombre_entidad' => $nombre_general,
            'nomarchivo' => $nomarchivo
        ];
        return $return;
    }

    public function firmarBoletapdf($html, $nombre_entidad, $nomarchivo, $id_certificado, $employee, $id_depto, $clave_doc, $directorio, $clave)
    {

        $dataFirma = PaymentsData::showCertificate($id_certificado);
        //$clave="";
        $file = "";
        $ubicacion = "";
        foreach ($dataFirma as $row) {
            $file      = $row->archivo;
            //$clave     = $this->desencriptar($row->clave,$row->numserie);
            $ubicacion = $row->ubicacion;
        }
        $ret["nerror"] = 1;
        $ret["msgerror"] = 'No se ha generado Firma';



        if ($clave != '') {


            try {

                $certificado = array();

                if (openssl_pkcs12_read($file, $certificado, $clave)) {


                    PDF::SetCreator('DIGETI');
                    PDF::SetAuthor('DIGETI-UPeU');
                    PDF::SetTitle('eBoletas UPeU');
                    PDF::AddPage();
                    $info = array(
                        'Name' => 'UPeU',
                        'Location' => $ubicacion,
                        'Reason' => $nombre_entidad,
                        'ContactInfo' => 'http://www.upeu.edu.pe',
                    );

                    //$ret["nerror"]=1;
                    //$ret["msgerror"]=$file;
                    //return $ret;

                    PDF::setSignature($certificado['cert'], $certificado['pkey'], $clave, '', 2, $info);
                    PDF::writeHTML($html, true, 0, true, 0);



                    $carpeta = $directorio;

                    PDF::Output($carpeta . '/' . $nomarchivo . '.pdf', 'F');
                    PDF::reset();
                    $id_entidad = 0;
                    $id_anho = 0;
                    $id_mes = 0;
                    $id_persona = 0;
                    $id_contrato = 0;

                    $id_proceso = 1;
                    foreach ($employee as $row) {
                        $id_entidad = $row->id_entidad;
                        $id_anho = $row->id_anho;
                        $id_mes = $row->id_mes;
                        $id_persona = $row->id_persona;
                        $id_contrato = $row->id_contrato;
                    }

                    PaymentsData::addPaymentTicket($id_entidad, $id_anho, $id_mes, $id_persona, $id_contrato, $id_proceso, $id_depto, $clave_doc, $nomarchivo . '.pdf');

                    $ret["nerror"] = 0;
                    $ret["msgerror"] = 'Se ha generado correctamente';
                } else {
                    $ret["nerror"] = 1;
                    $ret["msgerror"] = 'No se puede leer certificados';
                }
            } catch (Exception $e) {
                //echo $e->getMessage()."<br/>";
                $ret["nerror"] = 1;
                $ret["msgerror"] = $e->getFile() . ' ' . $e->getLine() . ' Msg' . $e->getMessage();
            }
        } else {
            $ret["nerror"] = 1;
            $ret["msgerror"] = 'No existe certificado digital';
        }
        return $ret;
    }
    /*public function firmarBoletapdf($html,$nombre_entidad,$nomarchivo,$id_certificado,$employee,$id_depto,$clave_doc,$directorio){

       $dataFirma=PaymentsData::showCertificate($id_certificado);
       $clave="";
       $file="";
       $ubicacion="";
       foreach($dataFirma as $row){
           $file      = $row->archivo;
           $clave     = $this->desencriptar($row->numserie,$row->id_persona);
           $ubicacion = $row->ubicacion;
       }
       $ret["nerror"]=0;
       $ret["msgerror"]='';

       //$clave = 'mmmmmm';
       if($clave!=''){

            try{
                 PDF::SetCreator('DIGETI');
                 PDF::SetAuthor('DIGETI-UPeU');
                 PDF::SetTitle('eBoletas UPeU');
                 PDF::AddPage();
                 $info = array(
                         'Name' => 'UPeU',
                         'Location' =>$ubicacion,
                         'Reason' =>$nombre_entidad,
                         'ContactInfo' => 'http://www.upeu.edu.pe',
                         );
                 PDF::setSignature($signature, $signature, $clave, '', 2, $info);
                 PDF::writeHTML($html,true,0,true,0);

                 $carpeta = $directorio;

                 PDF::Output($carpeta.'/'.$nomarchivo.'.pdf','F');
                 PDF::reset();
                 $id_entidad=0;
                 $id_anho=0;
                 $id_mes=0;
                 $id_persona=0;
                 $id_contrato=0;

                 $id_proceso=1;
                 foreach($employee as $row){
                     $id_entidad=$row->id_entidad;
                     $id_anho=$row->id_anho;
                     $id_mes=$row->id_mes;
                     $id_persona=$row->id_persona;
                     $id_contrato=$row->id_contrato;
                 }

                 PaymentsData::addPaymentTicket($id_entidad,$id_anho,$id_mes,$id_persona,$id_contrato,$id_proceso,$id_depto,$clave_doc,$nomarchivo.'.pdf');

             }catch(Exception $e){
                 //echo $e->getMessage()."<br/>";
                 $ret["nerror"]=1;
                 $ret["msgerror"]=$e->getMessage();
             }
       }else{
           $ret["nerror"]=1;
           $ret["msgerror"]='No existe certificado digital';
       }
       return $ret;
    }*/

    public function listCertificate(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = PaymentsData::listCertificate();
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

    public function addCertificate(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        //dd($request);
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $file = $request->file('file');

                $extencion       = $file->getClientOriginalExtension();
                $nombre_archivo  = $file->getClientOriginalName();
                $archivo         = $file->getPathname();
                $archivo         = file_get_contents($archivo);

                $data = (object) openssl_x509_parse($archivo);

                $finicio    = $data->validFrom;
                $ffinal    = $data->validTo;
                //$serial    = $data->serialNumber;
                $datos     = (object)$data->subject;
                //$empresa   = $datos->O;
                //$datemp    = $datos->OU;
                //$ruc       = $datemp[1];
                $distrito  = '';
                if (isset($datos->L)) {
                    $distrito  = $datos->L;
                }

                $ciudad    = $datos->ST;
                $dni       = $datos->serialNumber;

                $adatos = explode(":", $dni);

                if (count($adatos) > 1) {
                    $dni = $adatos[1];
                }

                //$apellidos = $datos->SN;
                //$nombre    = $datos->GN;
                $firma  = $request->file('firma');
                $ext    = $firma->getClientOriginalExtension();
                //$fileName = $file->getClientOriginalName();
                $archivofirma = $dni . "." . $ext;
                $path = 'img';
                $firma->move($path, $archivofirma);


                //$id_persona      = $request->id_persona;
                $descripcion     = $request->descripcion;
                $clave           = $request->clave;
                $desde           = '20' . substr($finicio, 0, 2) . '/' . substr($finicio, 2, 2) . '/' . substr($finicio, 4, 2);  //$request->desde;
                $hasta           = '20' . substr($ffinal, 0, 2) . '/' . substr($ffinal, 2, 2) . '/' . substr($ffinal, 4, 2); //$request->hasta;
                //$firma           = $request->firma;
                $ubicacion       = $ciudad . ' - ' . $distrito;
                //$clave           = $this->encriptar($clave,$id_persona);
                $ok = PaymentsData::addCertificate($descripcion, $nombre_archivo, $archivo, $dni, $desde, $hasta, $clave, $archivofirma, $ubicacion);

                if ($ok == "OK") {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $ok;
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public  function deleteCertificate($id_certificado)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {


                PaymentsData::deleteCertificate($id_certificado);


                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public  function addCertificateDepto($id_certificado)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params         = json_decode(file_get_contents("php://input"));
                //$id_certificado = $params->id_certificado;
                $id_entidad     = $params->id_entidad;
                $id_depto       = $params->id_depto;

                $ret = PaymentsData::addCertificateDepto($id_certificado, $id_entidad, $id_depto);

                if ($ret == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Departamento ya esta asignado';
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
    public  function deleteCertificateDepto($id_certificado, $id_entidad, $id_depto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                PaymentsData::deleteCertificateDepto($id_certificado, $id_entidad, $id_depto);

                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addCertificate_ant(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        //dd($request);
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $file = $request->file('file');

                $extencion       = $file->getClientOriginalExtension();
                $nombre_archivo  = $file->getClientOriginalName();
                $archivo         = $file->getPathname();
                $archivo         =  file_get_contents($archivo);

                $data = (object) openssl_x509_parse($archivo);

                $id_persona      = $request->id_persona;
                $descripcion     = $request->descripcion;
                $clave           = $request->clave;
                $desde           = $request->desde;
                $hasta           = $request->hasta;
                $firma           = $request->firma;
                $clave           = $this->encriptar($clave, $id_persona);
                $ok = PaymentsData::addCertificate($descripcion, $nombre_archivo, $archivo, $clave, $desde, $hasta, $id_persona, $firma);
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function linkBoletaPDF(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        //dd($request);
        if ($valida == 'SI') {
            $jResponse = [];

            try {

                $clave   = $request->p;
                $type   = $request->type;

                if ($type == "S") {
                    PaymentsData::updateBoletaPDF($clave, 4);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "OK";
                $jResponse['data'] = [];
                $code = "200";
                
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    /*
    public function linkBoletaPDF(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        //dd($request);
        if ($valida == 'SI') {
            $jResponse = [];

            try {
                $params  = json_decode(file_get_contents("php://input"));

                $clave   = $params->p;
                $type   = $params->type;

                $data = PaymentsData::showBoletaPDF($clave);


                $archivo = "";
                $id_entidad = 0;
                $id_anho = 0;
                $id_mes = 0;
                foreach ($data as $row) {
                    $archivo = $row->archivo;
                    $id_entidad = $row->id_entidad;
                    $id_anho = $row->id_anho;
                    $id_mes  = $row->id_mes;
                }

                $retdir = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
                if ($retdir["nerror"] == 0) {

                    if ($archivo != "") {
                        //$file = realpath("boletas"). '/' . $archivo;
                        $file = $retdir["directorio"] . '/' . $archivo;

                        if ($type == "S") {
                            PaymentsData::updateBoletaPDF($clave, 4);
                        }

                        $dirbol = "boletas";
                        if (!file_exists($dirbol)) {

                            mkdir($dirbol, 0777);
                        }

                        copy($file, $dirbol . '/' . $archivo);

                        $file = 'boletas/' . $archivo;
                        $jResponse['success'] = true;
                        $jResponse['message'] = "OK";
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "No hay data";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Origen de archivo no existe " . $retdir["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage() . '*' . $file;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    */
    public function unlinkBoletaPDF(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        //dd($request);
        if ($valida == 'SI') {
            $jResponse = [];

            try {
                $params  = json_decode(file_get_contents("php://input"));

                $clave   = $params->p;

                //$clave=$request->p;
                $data = PaymentsData::showBoletaPDF($clave);


                $archivo = "";
                $id_entidad = 0;
                $id_anho = 0;
                $id_mes = 0;
                foreach ($data as $row) {
                    $archivo = $row->archivo;
                    $id_entidad = $row->id_entidad;
                    $id_anho = $row->id_anho;
                    $id_mes  = $row->id_mes;
                }

                $retdir = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
                if ($retdir["nerror"] == 0) {

                    if ($archivo != "") {

                        $file = 'boletas/' . $archivo;

                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
            } catch (Exception $e) {
            }
        }

        $jResponse['success'] = true;
        $jResponse['message'] = "OK";
        $jResponse['data'] = [];
        $code = "200";
        return response()->json($jResponse, $code);
    }
    public function downloadBoletaPDF(Request $request)
    {
        //public function downloadBoletaPDF($clave){
        try {
            $clave = $request->query('p');
            $type = 'N';
            if ($request->has('type')) {
                $type = $request->query('type');
            }

            //$clave=$request->p;
            $data = PaymentsData::showBoletaPDF($clave);


            $archivo = "";
            $id_entidad = 0;
            $id_anho = 0;
            $id_mes = 0;
            foreach ($data as $row) {
                $archivo = $row->archivo;
                $id_entidad = $row->id_entidad;
                $id_anho = $row->id_anho;
                $id_mes  = $row->id_mes;
            }

            $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

            $mes = $dmeses[$id_mes];
            $carpeta      =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes;
            $file  = $carpeta. '/' . $archivo; 


            $ret = PaymentsData::getUrlByName($file);

            if($ret['nerror']==0) {
                $url  = $ret['data'];

                $file = $url; 

                if ($type == "S") {
                    PaymentsData::updateBoletaPDF($clave, 4);
                }

                $content_types = 'application/pdf';
                    return response(file_get_contents($file), 200)
                        ->header('Content-Type', $content_types);

            } else {
                return  $ret['message'];
            }
        } catch (Exception $e) {
            return "ORA-" . $e->getMessage();
        }
    }
    /*
    public function displayBoletaPDF(Request $request)
    {
        //public function downloadBoletaPDF($clave){
        try {
            $clave = $request->query('p');
            //$clave=$request->p;
            $data = PaymentsData::showBoletaPDF($clave);

            $type = 'N';
            if ($request->has('type')) {
                $type = $request->query('type');
            }


            $archivo = "";
            $id_entidad = 0;
            $id_anho = 0;
            $id_mes = 0;
            foreach ($data as $row) {
                $archivo = $row->archivo;
                $id_entidad = $row->id_entidad;
                $id_anho = $row->id_anho;
                $id_mes = $row->id_mes;
            }
            $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

            $mes = $dmeses[$id_mes];
            $carpeta      =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes;
            $file  = $carpeta. '/' . $archivo; 

            $ret = PaymentsData::getUrlByName($file);
            if($ret['nerror']==0) {
                $url  = $ret['data'];

                $getFile = file_get_contents($url);

                $doc  = base64_encode($getFile);

                if ($type == "S") {
                    PaymentsData::updateBoletaPDF($clave, 3);
                }
     
                $jResponse['success'] = true;
                $jResponse['message'] = 'ok';
                $jResponse['data'] = $doc;
                $code = "200";
            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $ret['message'];
                $jResponse['data'] = '';
                $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-" . $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse, $code);
    }
    
    public function displayBoletaPDF(Request $request)
    {
        //public function downloadBoletaPDF($clave){
        try {
            $clave = $request->query('p');
            //$clave=$request->p;
            $data = PaymentsData::showBoletaPDF($clave);

            $type = 'N';
            if ($request->has('type')) {
                $type = $request->query('type');
            }


            $archivo = "";
            $id_entidad = 0;
            $id_anho = 0;
            $id_mes = 0;
            foreach ($data as $row) {
                $archivo = $row->archivo;
                $id_entidad = $row->id_entidad;
                $id_anho = $row->id_anho;
                $id_mes = $row->id_mes;
            }

            $retdir = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
            if ($retdir["nerror"] == 0) {

                if ($archivo != "") {
                    //$file = realpath("boletas"). '/' . $archivo;
                    $file = $retdir["directorio"] . '/' . $archivo;

                    if ($type == "S") {
                        PaymentsData::updateBoletaPDF($clave, 3);
                    }

                    return response()->file($file);
                } else {
                    return "No hay data";
                }
            } else {
                return "Origen de archio no existe " . $retdir["msgerror"];
            }
        } catch (Exception $e) {
            return "Error Interno: " . $e->getMessage();
        }
    }
    public function displayBoletaPDF(Request $request)
    {
        //public function downloadBoletaPDF($clave){
        try {
            $clave = $request->query('p');
            //$clave=$request->p;
            $data = PaymentsData::showBoletaPDF($clave);

            $type = 'N';
            if ($request->has('type')) {
                $type = $request->query('type');
            }


            $archivo = "";
            $id_entidad = 0;
            $id_anho = 0;
            $id_mes = 0;
            foreach ($data as $row) {
                $archivo = $row->archivo;
                $id_entidad = $row->id_entidad; 
                $id_anho = $row->id_anho;
                $id_mes = $row->id_mes;
            }
            $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

            $mes = $dmeses[$id_mes];
            $carpeta      =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes;
            $file  = $carpeta. '/' . $archivo; 

            $ret = PaymentsData::checkFileExists($file);
            if($ret['nerror']==0) {

                if ($type == "S") {
                    PaymentsData::updateBoletaPDF($clave, 3);
                }
                //$ret = PaymentsData::getUrlByName($file);

                $content = PaymentsData::responseFile($file);
                return response($content, 200)->header('Content-Type', MimeType::fromFilename($archivo));
                //return response()->file($content);
                //return (new Response($content, 200))->header('Content-Type', 'image/jpeg');
                
                //return $ret['data'];// Storage::disk('minio-talent')->response($file, $archivo);
                //return response()->file(Storage::disk('minio-talent')->url(urldecode($file)));
            }else{
                return $ret["message"].' file: '.$file;
            }
        } catch (Exception $e) {

            return "Error Interno: " . $e->getMessage();
        }
 
    }
    */
    public function displayBoletaPDF(Request $request)
    {
        //public function downloadBoletaPDF($clave){
        try {
            $clave = $request->query('p');
            //$clave=$request->p;
            $data = PaymentsData::showBoletaPDF($clave);

            $type = 'N';
            if ($request->has('type')) {
                $type = $request->query('type');
            }


            $archivo = "";
            $id_entidad = 0;
            $id_anho = 0;
            $id_mes = 0;
            foreach ($data as $row) {
                $archivo = $row->archivo;
                $id_entidad = $row->id_entidad; 
                $id_anho = $row->id_anho;
                $id_mes = $row->id_mes;
            }
            $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

            $mes = $dmeses[$id_mes];
            $carpeta      =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes;
            $file  = $carpeta. '/' . $archivo; 


            $ret = PaymentsData::getUrlByName($file);

            if($ret['nerror']==0) {
                $url  = $ret['data'];

                $getFile = file_get_contents($url); 

                $doc  = base64_encode($getFile);
                
                if ($type == "S") {
                    PaymentsData::updateBoletaPDF($clave, 3);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item was created successfully';
                $jResponse['data'] = ['file'=>$url,'base'=>$doc];
                $code = "200";

            }else{
                $jResponse['success'] = false;
                $jResponse['message'] = $ret['message'];
                $jResponse['data'] = [];
                $code = "202";

            }
     
        } catch (Exception $e) {

            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-" . $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse, $code);
 
    }
    public function updateBoletaPDF($clave, Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $clave = $clave;
                $id_proceso = $request->id_proceso;

                PaymentsData::updateBoletaPDF($clave, $id_proceso);
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item was created successfully';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showBoletaPDF(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $clave = $request->p;
                $conditions = $request->conditions;

                $data = PaymentsData::showBoletaPDF($clave);

                $archivo = "";
                $id_entidad = 0;
                $id_anho = 0;
                $id_mes = 0;
                foreach ($data as $row) {
                    $archivo = $row->archivo;
                    $id_entidad = $row->id_entidad;
                    $id_anho = $row->id_anho;
                    $id_mes  = $row->id_mes;
                }

                $retdir  = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
                if ($retdir["nerror"] == 0) {
                    if ($archivo != "") {
                        //$file = realpath("boletas"). '/' . $archivo;
                        $file = $retdir["directorio"] . '/' . $archivo;
                        if ($conditions == "S") {
                            PaymentsData::updateBoletaPDF($clave, 3);
                        }
                        return response()->file($file);
                    } else {
                        $archivo = "noexiste";
                        $data = 'No hay datos';
                        $pdf = DOMPDF::loadView('pdf.vacio', compact('data'))->setPaper('a4');
                        return $pdf->stream($archivo . '.pdf');
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = '';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = $retdir["msgerror"];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public  function test()
    {

        $this->sendSMS('upeuerp_sms', 'UP3UERP2018', '997541436', 'Hola');
    }

    public  function sendEmail()
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $params      = json_decode(file_get_contents("php://input"));
                $id_entidad  = $params->id_entidad;
                $id_anho     = $params->id_anho;
                $id_mes      = $params->id_mes;
                $email       = $params->email;
                $id_persona  = $params->id_persona;
                $option      = $params->option;
                $id_depto    = $params->id_depto;
                $id_proceso   = $params->id_proceso;
                $contar = 1;
                // $id_entidad  = 17112;
                // $id_anho     = 2019;
                // $id_mes      = 8;
                // $email       = 'amelio.apaza@adventistas.org';
                // $id_persona  = 7932;
                // $option      = true;
                // $id_depto    = '3';
                // $id_proceso   = 0;
                // $contar      = 1;


                $mail_host = "";
                $mail_port = "";
                $mail_encryption = "";
                $mail_username = "";
                $mail_password = "";
                $mail_from_name = "";
                $mail_body = "";
                $mail_footer = "";

                $sms_username = "";
                $sms_password = "";
                $nom_empresa = "";
                $nom_entidad = "";

                $respuesta = PaymentsData::obtenerDatosFirma($id_entidad, $id_depto);



                if ($respuesta["nerror"] == 1) {

                    $jResponse = [
                        'nerror' => 1,
                        'mensaje' => $respuesta["msgerror"],
                        'clave' => "",
                        'nomarchivo' => "",
                        'data' => ''
                    ];

                    return response()->json($jResponse, $code);
                }

                $dataFirma = PaymentsData::showCertificate($respuesta['certificado']);

                foreach ($dataFirma as $row) {

                    $mail_host = $row->mail_host;
                    $mail_port = $row->mail_port;
                    $mail_encryption = $row->mail_encryption;
                    $mail_username = $row->mail_username;
                    $mail_password = $row->mail_password;
                    $mail_from_name = $row->mail_from_name;
                    $mail_body = $row->mail_body;
                    $mail_footer = $row->mail_footer;
                    $sms_username = $row->sms_username;
                    $sms_password = $row->sms_password;
                }

                // $empresa = SetupData::enterpriseByIdEntity($id_entidad);
                $empresa = SetupData::entityDetailView($id_entidad);


                foreach ($empresa as $row) {

                    $nom_empresa = $row->nom_empresa;
                    $nom_entidad = $row->nom_entidad;
                }

                if ($option == true) {
                    $data_persona = APSData::personPlanilla($id_entidad, $id_anho, $id_mes, $id_persona);
                    foreach ($data_persona as $row) {
                        $id_depto    = $row->id_depto_padre;
                    }

                    $contar = count($data_persona);
                    if (strlen($id_proceso) == 0) {
                        $id_proceso = '2';
                    }
                }

                
                if ($contar > 0) {
                    $data = PaymentsData::sendEmail($id_entidad, $id_depto, $id_anho, $id_mes, $id_persona, $email, $id_proceso);

                    $n = 0;
                    $archivo = "";
                    foreach ($data as $row) {
                        $mail_body_persona = $mail_body;
                        $correo    = $row->email;
                        if ($option == true) {
                            $correo    = $email;
                        }
                        $persona   = $row->nombre . ' ' . $row->paterno . ' ' . $row->materno;
                        $clave     = $row->clave;
                        $mes       = $row->mes;
                        $celular   = $row->celular;
                        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                            $archivo = $row->archivo;
                            $cod     = $mes . '-' . $id_anho;
                            if ($archivo != "") {

                                // Reemplazando los datos en el cuerpo del correo
                                $replace_old = array("{{nom_empresa}}", "{{persona}}", "{{mes}}", "{{id_anho}}");
                                $replace_new   = array($nom_empresa, $persona, $mes, $id_anho);

                                $mail_body_persona = str_replace($replace_old, $replace_new, $mail_body_persona);

                                // $data = array('id_anho'=>$id_anho,'mes'=>$mes,'persona'=>$persona);
                                $data = array('mail_body' => $mail_body_persona, 'mail_footer' => $mail_footer);

                                if($id_entidad==7124){
                                    $data = [
                                        'from_email' => $mail_username,
                                        'from_name' => $mail_from_name,
                                        'correo'=>$correo,
                                        'html'=>$mail_body_persona.$mail_footer,
                                        'asunto'=>'Entrega de boleta de pago del periodo ' . $cod . ' - ' . $nom_empresa,
                                        'attachments'=>'',
                                    ];
                                    $ret = SendEmail::send($data);


                                }else{
                                    $backup = Mail::getSwiftMailer();
                                    // Setup your gmail mailer
                                    $transport = \Swift_SmtpTransport::newInstance($mail_host, $mail_port, $mail_encryption);
                                    $transport->setUsername($mail_username);
                                    $transport->setPassword($mail_password);
                                    // Any other mailer configuration stuff needed...
                                    $nmail = new Swift_Mailer($transport);

                                    // Set the mailer as gmail
                                    Mail::setSwiftMailer($nmail);

                                    Mail::send('emails.avisoboleta', $data, function ($message) use ($correo, $cod, $nom_empresa, $mail_username, $mail_from_name) {
                                        $message->setFrom([$mail_username => $mail_from_name]);
                                        $message->subject('Entrega de boleta de pago del periodo ' . $cod . ' - ' . $nom_empresa);
                                        $message->to($correo);
                                        //$message->to("sotil07@gmail.com");

                                    }); 
                                }

                                if (strlen($celular) > 0) {
                                    $msg = "Estimado(a) " . $persona . " su boleta de pago del periodo " . $cod . ", ya  puede ser visualizada. " . $nom_entidad;
                                    //$celular = "981906194";
                                    $this->sendSMS($sms_username, $sms_password, $celular, $msg);
                                }
                                PaymentsData::updateBoletaPDF($clave, 2, $correo, $celular);


                                $n++;
                            }
                        }
                    }
                    if ($n > 0) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'Succes';
                        $jResponse['data'] = [];
                        $code = "200";
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "No se puede procesar ";
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No existe informción para enviar";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function emailBoletaPDF(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $email = $request->email;
                $clave = $request->clave;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {


                    $data = PaymentsData::showBoletaPDF($clave);

                    $archivo = "";
                    $cod = "";
                    $persona  = "";
                    $correo = "";
                    $mes = "";
                    $id_entidad = 0;
                    $id_depto = '';
                    $id_anho = 0;
                    $id_mes  = 0;

                    $mail_host = "";
                    $mail_port = "";
                    $mail_encryption = "";
                    $mail_username = "";
                    $mail_password = "";
                    $mail_from_name = "";

                    $nom_empresa = "";
                    $nom_entidad = "";

                    foreach ($data as $row) {
                        $archivo = $row->archivo;
                        $cod = $row->nombre . ' - ' . $row->id_anho;
                        $correo   = $row->correo;
                        $persona  = $row->persona;
                        $mes = $row->nombre;
                        $id_entidad = $row->id_entidad;
                        $id_depto = $row->id_depto;
                        $id_anho = $row->id_anho;
                        $id_mes = $row->id_mes;
                    }

                    $respuesta = PaymentsData::obtenerDatosFirma($id_entidad, $id_depto);

                    $dataFirma = PaymentsData::showCertificate($respuesta['certificado']);

                    foreach ($dataFirma as $row) {

                        $mail_host = $row->mail_host;
                        $mail_port = $row->mail_port;
                        $mail_encryption = $row->mail_encryption;
                        $mail_username = $row->mail_username;
                        $mail_password = $row->mail_password;
                        $mail_from_name = $row->mail_from_name;
                    }

                    // $empresa = SetupData::enterpriseByIdEntity($id_entidad);
                    $empresa = SetupData::entityDetailView($id_entidad);
                    foreach ($empresa as $row) {

                        $nom_empresa = $row->nom_empresa;
                        $nom_entidad = $row->nom_entidad;
                    }

                    $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

                    $mes = $dmeses[$id_mes];

                    //$retdir  = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
                    $file  =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes.'/'.$archivo;
                    $retdir = PaymentsData::checkFileExists($file);
      
                    if ($retdir["nerror"] == 0) {
                        if ($archivo != "") {

                            $data = array('id_anho' => $id_anho, 'mes' => $mes, 'persona' => $persona, 'nom_entidad' => $nom_entidad);
                            $cod = $mes . '-' . $id_anho;

                            $backup = Mail::getSwiftMailer();
                            // Setup your gmail mailer
                            $transport = \Swift_SmtpTransport::newInstance($mail_host, $mail_port, $mail_encryption);
                            $transport->setUsername($mail_username);
                            $transport->setPassword($mail_password);
                            // Any other mailer configuration stuff needed...
                            $nmail = new Swift_Mailer($transport);

                            // Set the mailer as gmail
                            Mail::setSwiftMailer($nmail);
                            //$asunto='Boleta de pago del periodo ' . $cod . '  - ' . $nom_entidad;
                            //Mail::to($email)->send(new SendBoleta($data,$file,$archivo,$asunto));

                            $ret = PaymentsData::getUrlByName($file);

                            $url  = $ret['data'];

                            $getFile = file_get_contents($url);

                            $doc  = base64_encode($getFile);

                            Mail::send('emails.boleta', $data, function ($message) use ($doc, $archivo, $email, $cod, $nom_entidad, $mail_username, $mail_from_name) {
                                $message->setFrom([$mail_username => $mail_from_name]);
                                $message->subject('Boleta de pago del periodo ' . $cod . '  - ' . $nom_entidad);
                                $message->to($email);
                                $message->attachData($doc, $archivo, [
                                    'mime' => 'application/pdf',
                                ]);
                                
                            });

                            $jResponse['success'] = true;
                            $jResponse['message'] = 'Succes';
                            $jResponse['data'] = [];
                            $code = "200";
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "No existe archivo para enviar";
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $retdir["message"];
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Dirección de correo (" . $email . ") no es válida.";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    /*
    public function emailBoletaPDF(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $email = $request->email;
                $clave = $request->clave;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {


                    $data = PaymentsData::showBoletaPDF($clave);

                    $archivo = "";
                    $cod = "";
                    $persona  = "";
                    $correo = "";
                    $mes = "";
                    $id_entidad = 0;
                    $id_depto = '';
                    $id_anho = 0;
                    $id_mes  = 0;

                    $mail_host = "";
                    $mail_port = "";
                    $mail_encryption = "";
                    $mail_username = "";
                    $mail_password = "";
                    $mail_from_name = "";

                    $nom_empresa = "";
                    $nom_entidad = "";

                    foreach ($data as $row) {
                        $archivo = $row->archivo;
                        $cod = $row->nombre . ' - ' . $row->id_anho;
                        $correo   = $row->correo;
                        $persona  = $row->persona;
                        $mes = $row->nombre;
                        $id_entidad = $row->id_entidad;
                        $id_depto = $row->id_depto;
                        $id_anho = $row->id_anho;
                        $id_mes = $row->id_mes;
                    }

                    $respuesta = PaymentsData::obtenerDatosFirma($id_entidad, $id_depto);

                    $dataFirma = PaymentsData::showCertificate($respuesta['certificado']);

                    foreach ($dataFirma as $row) {

                        $mail_host = $row->mail_host;
                        $mail_port = $row->mail_port;
                        $mail_encryption = $row->mail_encryption;
                        $mail_username = $row->mail_username;
                        $mail_password = $row->mail_password;
                        $mail_from_name = $row->mail_from_name;
                    }

                    // $empresa = SetupData::enterpriseByIdEntity($id_entidad);
                    $empresa = SetupData::entityDetailView($id_entidad);
                    foreach ($empresa as $row) {

                        $nom_empresa = $row->nom_empresa;
                        $nom_entidad = $row->nom_entidad;
                    }
                    $retdir  = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);

                    if ($retdir["nerror"] == 0) {
                        if ($archivo != "") {
                            $file = realpath("boletas") . '/' . $archivo;
                            $file = $retdir["directorio"] . '/' . $archivo;
                            $data = array('id_anho' => $id_anho, 'mes' => $mes, 'persona' => $persona, 'nom_entidad' => $nom_entidad);
                            $cod = $mes . '-' . $id_anho;

                            $backup = Mail::getSwiftMailer();
                            // Setup your gmail mailer
                            $transport = \Swift_SmtpTransport::newInstance($mail_host, $mail_port, $mail_encryption);
                            $transport->setUsername($mail_username);
                            $transport->setPassword($mail_password);
                            // Any other mailer configuration stuff needed...
                            $nmail = new Swift_Mailer($transport);

                            // Set the mailer as gmail
                            Mail::setSwiftMailer($nmail);


                            Mail::send('emails.boleta', $data, function ($message) use ($file, $archivo, $email, $cod, $nom_entidad, $mail_username, $mail_from_name) {
                                $message->setFrom([$mail_username => $mail_from_name]);
                                $message->subject('Boleta de pago del periodo ' . $cod . '  - ' . $nom_entidad);
                                $message->to($email);
                                $message->attach($file, [
                                    'as' => $archivo,
                                    'mime' => 'application/pdf',
                                ]);
                            });
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'Succes';
                            $jResponse['data'] = [];
                            $code = "200";
                        } else {
                            $jResponse['success'] = false;
                            $jResponse['message'] = "No existe archivo para enviar";
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = $retdir["msgerror"];
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Dirección de correo (" . $email . ") no es válida.";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    */
    public function listPaymentTracing(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_depto = $request->query('id_depto');
                $tipo = $request->query('tipo');
                $id_proceso = $request->query('id_proceso');
                $persona = $request->query('persona');

                $data = PaymentsData::listPaymentTracing($id_entidad, $id_anho, $id_mes, $id_depto, $tipo, $id_proceso, $persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listPaymentTracingSUNAFIL(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $id_depto = $request->query('id_depto');
                $tipo = $request->query('tipo');
                $id_proceso = $request->query('id_proceso');
                $persona = $request->query('persona');

                $access = $request->query('access');
                $data = PaymentsData::listPaymentTracingSUNAFIL($id_entidad, $id_anho, $id_mes, $id_depto, $tipo, $access, $id_proceso, $persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listProcesos(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $data = PaymentsData::listProcesos();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    private function encriptar($string, $key)
    {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }


    private function desencriptar($string, $key)
    {
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    public function sendSMS($username, $password, $celular, $msg)
    {

        //$celular=$request->celular;
        //$msg= $request->msg;


        $values['app'] = 'webservices';
        // $values['u'] = 'upeuerp_sms';
        // $values['p'] = 'UP3UERP2018';
        $values['u'] = $username;
        $values['p'] = $password;
        $values['to'] = $celular;
        $values['msg'] = $msg;


        $data = http_build_query($values);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        $rpta["mensaje_id"] = 0;
        $rpta["mensaje_estado_rpta"] = 'NO';
        $rpta["destino"] = $celular;

        $url = 'https://www.mensajesonline.pe/sendsms/';
        try {
            $context  = stream_context_create($options);
            $respuesta = file_get_contents($url, false, $context);
            return $respuesta;
        } catch (Exception $e) {
            $rpta["mensaje_id"] = 0;
            $rpta["mensaje_estado_rpta"] = $e->getMessage();
            $rpta["destino"] = $celular;
        }
        return $rpta;
    }

    public function listEmailCelular(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                if ($request->has('id_persona')) {
                    $id_user = $request->query('id_persona');
                }
                $data = PaymentsData::listEmailCelular($id_user);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
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

    public function procEmailCelular()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code      = $jResponse["code"];
        $valida    = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];

            try {

                $params      = json_decode(file_get_contents("php://input"));


                $id_persona  = $id_user;
                if (isset($params->id_persona)) {
                    $id_persona = $params->id_persona;
                }
                $opcion      = $params->opcion;
                $tipo        = $params->tipo;
                $id_virtual  = $params->id_virtual;
                $id_telefono = $params->id_telefono;
                $email       = $params->email;
                $celular     = $params->celular;


                $return  =  PaymentsData::procEmailCelular($id_persona, $opcion, $tipo, $id_virtual, $id_telefono, $email, $celular);

                if ($return['nerror'] == 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item was created successfully';
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "ORA-" . $return['msgerror'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (\Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }

        return response()->json($jResponse, $code);
    }


    public  function pruebas(Request $request)
    {

        /*$file = $request->file('file');

        $extencion       = $file->getClientOriginalExtension();
        $nombre_archivo  = $file->getClientOriginalName();
        $archivo         = $file->getPathname();
        //$archivo       =  file_get_contents($archivo);



        if (!$almacén_cert = file_get_contents($archivo)) {
            echo "Error: No se puede leer el fichero del certificado\n";
            exit;
        }
        $data = (object) openssl_x509_parse($almacén_cert);

        //10323468

         dd($data);

        $ffinal    = $data->validFrom;
        $ffinal    = $data->validTo;
        $serial    = $data->serialNumber;
        $datos     = (object)$data->subject;

        $empresa   = $datos->O;
        $datemp    = $datos->OU;
        $ruc       = $datemp[1];

        $distrito  = $datos->L;
        $ciudad    = $datos->ST;

        $dni       = $datos->serialNumber;

        $apellidos = $datos->SN;
        $nombre    = $datos->GN;




        if (openssl_pkcs12_read($almacén_cert, $info_cert, "upeu2018")) {
            echo "Información del certificado\n";
            dd($info_cert);
        } else {
            echo "Error: No se puede leer el almacén de certificados.\n";
            exit;
        }
        */
        /*$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $url_dw= url('humantalent/payments-tickets-worker-display');
        $url_s = str_replace("http://",$protocol."://", $url_dw);
        echo $protocol. "<br/>".$url_dw."<br/>".$url_s."</br>".$this->url_completa();*/
        //PaymentsData::pruebas();


        $respuesta = ['nerror' => 0, 'mensaje' => "ok"];

        try {
            $j = 0;
        } catch (\Exception $e) {
            $respuesta = ['nerror' => 1, 'mensaje' => 'Hola'];
        }
        return response()->json($respuesta, 202);
    }

    private function url_completa($forwarded_host = false)
    {
        $ssl   = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
        $proto = strtolower($_SERVER['SERVER_PROTOCOL']);
        $proto = substr($proto, 0, strpos($proto, '/')) . ($ssl ? 's' : '');
        if ($forwarded_host && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            if (isset($_SERVER['HTTP_HOST'])) {
                $host = $_SERVER['HTTP_HOST'];
            } else {
                $port = $_SERVER['SERVER_PORT'];
                $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
                $host = $_SERVER['SERVER_NAME'] . $port;
            }
        }
        $request = $_SERVER['REQUEST_URI'];
        return $proto . '://' . $host . $request;
    }
    public function exportexcel()
    {
        $respuesta = ['nerror' => 0, 'mensaje' => "ok"];
        $data = [];
        try {

            $data = DB::table('psto_proyecto')->get();
        } catch (Exception $e) {
            return 'Error';
        }


        Excel::create('lista', function ($excel) use ($data) {

            $excel->sheet('lista', function ($sheet) use ($data) {


                $sheet->loadView("excel.asist")->withKey($data);

                $sheet->setOrientation('landscape');
            });
        })->export('xls');
    }
    public function getBoletaPDF(Request $request)
    {
        $resp = ['success' => false, 'file' => '', 'message' => ''];
        try {
            $clave = $request->p;
            $data = PaymentsData::showBoletaPDF($clave);

            $archivo = "";
            $id_entidad = 0;
            $id_anho = 0;
            $id_mes = 0;
            foreach ($data as $row) {
                $archivo = $row->archivo;
                $id_entidad = $row->id_entidad;
                $id_anho = $row->id_anho;
                $id_mes = $row->id_mes;
            }
            if ($id_entidad > 0) {
                $retdir = PaymentsData::directorioBoleta($id_entidad, $id_anho, $id_mes);
                if ($retdir["nerror"] == 0) {

                    if ($archivo != "") {
                        $file = $retdir["directorio"] . '/' . $archivo;
                        $getFile = file_get_contents($file);

                        $doc  = base64_encode($getFile);
                        // PaymentsData::updateBoletaPDF($clave,3);
                        $resp = ['success' => true, 'file' => $doc, 'message' => ''];
                    } else {
                        $resp = ['success' => false, 'file' => '', 'message' => 'No existe el archivo'];
                    }
                } else {
                    $resp = ['success' => false, 'file' => '', 'message' => 'origen de archivo no existe'];
                }
            } else {
                $resp = ['success' => false, 'file' => '', 'message' => 'No existe data'];
            }
        } catch (Exception $e) {
            $resp = ['success' => false, 'file' => '', 'message' => 'Erro: ' . $e->getMessage()];
        }
        return response()->json($resp);
    }
}

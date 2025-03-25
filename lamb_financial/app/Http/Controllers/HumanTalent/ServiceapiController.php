<?php
namespace App\Http\Controllers\HumanTalent;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\APSData;
use Illuminate\Http\Request;
use App\Http\Data\SetupData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\HumanTalent\PaymentsData;
use App\Http\Data\HumanTalent\ServiceApiData;
use App\Http\Data\HumanTalent\SignatureData;
use App\Http\Data\GlobalMethods;
use App\qrcode;
use PDF;

class ServiceapiController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public function getEntity(Request $request){
        $jResponse = [
            'nerror' => 1,
            'mensaje' => 'ERROR',
            'data' =>[]
        ];

        $id_persona = $request->id_persona;
        $data = SetupData::entityByType_new($id_persona);
        $datames = SetupData::month();
        $dproceso = PaymentsData::listProcesos();
        
        $entity = 0;
        $k=0;
        foreach($data as $row){
            if($k==0){
                $entity=$row->id;
            }
            $k++;
        }
        $datanho = SetupData::yearActivoAll($entity);
        $ddepto =  AccountingData::listDeptoParent($entity);

        if (count($data)>0) {
            
            $datp=array();
            
            $d['id_proceso']='-';
            $d['nombre']='';
            $datp[]=$d;
            foreach($dproceso as $row){
                $d=array();
                $d['id_proceso']=$row->id_proceso;
                $d['nombre']=$row->nombre;
                $datp[]=$d;
            }
                        
            $jResponse = [
                'nerror' => 0,
                'mensaje' => 'ok',
                'data' =>  ['items' => $data,'dmes'=>$datames,'danho'=>$datanho,'ddepto'=>$ddepto,'dproceso'=>$datp]
            ];
            
        }else{
            $jResponse = [
                'nerror' => 1,
                'mensaje' => 'ERROR',
               'data' => []
            ];
        }

        return response()->json($jResponse);
    }
    public function getAnho(Request $request){
        $jResponse = [
            'nerror' => 1,
            'mensaje' => 'ERROR',
            'data' =>[]
        ];

        $entity = $request->id_entidad;
       
        $datanho = SetupData::yearActivoAll($entity); 
        $ddepto =  AccountingData::listDeptoParent($entity);

            
                        
        $jResponse = [
            'nerror' => 0,
            'mensaje' => 'ok',
           'data' =>  ['danho'=>$datanho,'ddepto'=>$ddepto]
        ];
            
        

        return response()->json($jResponse);
    }
    
    public function listProcessTicket(Request $request){
       $data=[];
        try{
            
            $id_entidad = $request->id_entidad;
            $id_anho =$request->id_anho;
            $id_mes = $request->id_mes;
            
           
            $data = PaymentsData::listProcessTicket($id_entidad,$id_anho,$id_mes);
            if (count($data)>0) {          
                $jResponse = [
                    'nerror' => 0,
                    'mensaje' => 'ok',
                   'data' =>  ['items'=>$data]
                ];
            }else{
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' => 'ERROR',
                    'data' =>[]
                ];
            }
        }catch(Exception $e){                    
            $jResponse = [
                'nerror' => 1,
                'mensaje' => "ORA-".$e->getMessage(),
                'data' =>[]
            ];
        }
               
        return response()->json($jResponse);
    }
    public function listPaymentTracing(Request $request){
        
         $jResponse = [
                'nerror' => 1,
                'mensaje' => "Error",
                'data' =>[]
        ];
        try{
            $id_entidad = $request->id_entidad;
            $id_anho = $request->id_anho;
            $id_mes = $request->id_mes;
            $id_depto = $request->id_depto;
            $tipo = $request->tipo;
            $id_proceso = $request->id_proceso;
            $persona = $request->libre;

            $data = PaymentsData::listPaymentTracing($id_entidad,$id_anho,$id_mes,$id_depto,$tipo,$id_proceso,$persona,'E');
            if (count($data)>0) {          
                $jResponse = [
                    'nerror' => 0,
                    'mensaje' => 'ok',
                   'data' =>  ['items'=>$data]
                ];
            }else{
                $jResponse = [
                'nerror' => 1,
                'mensaje' => "No hay Data:",
                'data' =>[]
             ];
            }
        }catch(Exception $e){ 
            $jResponse = [
                'nerror' => 1,
                'mensaje' => "ORA-".$e->getMessage(),
                'data' =>[]
            ];
        }
        
        return response()->json($jResponse);
    }
     public function previapdfweb(Request $request){
       
            $jResponse=[];
            $planilla = [];
            $gen=0;
            $nogen=0;
            $msgerror="";
            $id_certificado=0;
            try{
                
      
                $id_entidad     = $request->id_entidad;
                $id_anho        = $request->id_anho;
                $id_mes         = $request->id_mes;
                $id_persona     = $request->id_persona;
                $id_depto       = $request->id_depto;
                
                
                    $entidad  = APSData::entidadPersona($id_entidad);

                    $id_persona_enti  = 0;
                    foreach ($entidad as $data) {
                        $id_persona_enti = $data->id_persona;
                    }

                    $empresa = APSData::entidadEmpresa($id_persona_enti);
                    
                    $respuesta  = PaymentsData::obtenerDatosFirma($id_entidad,$id_depto);

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

                                    $ret = $this->generarPrevioBoleta($planilla,$id_anho,$id_mes,$id_depto,$id_certificado);

                                    if( strlen($ret["html"])>0 and strlen($ret["p"])>0 and  strlen($ret["nombre_entidad"])>0 and strlen($ret["nomarchivo"])>0){
                                        
                                        PDF::SetCreator('DIGETI');
                                        PDF::SetAuthor('DIGETI-UPeU');
                                        PDF::SetTitle('eBoletas UPeU');
                                        PDF::AddPage();
                                        $info = array(
                                                'Name' => 'UPeU',
                                                'Location' =>'Lurigancho(Chosica) - Lima',
                                                'Reason' =>'UPeU',
                                                'ContactInfo' => 'http://www.upeu.edu.pe',
                                                );

                                        PDF::writeHTML($ret["html"],true,0,true,0);



                                        $carpeta = 'boletas';
                                        
                                        $ruta = public_path() .'/'.$carpeta.'/boletaprevio.pdf';
                                      

                                        PDF::Output($ruta,'F');
                                        
                                        
                                        
                                        $archivo =file_get_contents($ruta);
                                        //$archivo =$ruta;
                                        
                                        $doc  = base64_encode($archivo);
                    
                                        $jResponse = [
                                            'success' => true,
                                            'message' => "OK",
                                            'data' => ['items'=>$doc]
                                            ];

                                    }else{
                                         $jResponse = [
                                            'success' => false,
                                            'message' => "No se ha generado",
                                            'data'=>[]
                                            ];
                                    }


                                }
                                
                            }else{
                                $jResponse = [
                                    'success' => false,
                                    'message' => "No hay data para generar",
                                    'data'=>[]
                                    ];
                            }
                        }else{
                            $jResponse = [
                                'success' => false,
                                'message' =>$respuesta["msgerror"],
                                'data'=>[]
                            ];
                            
                        }

            }catch(\Exception $e){ 
                $jResponse = [
                    'success' => false,
                    'message' => "ORA-".$e->getFile().' '.$e->getLine().' '.$e->getMessage(),
                    'data'=>[]
                ];
                

            }
 
            
        return response()->json($jResponse);
    }
    public function previapdf(Request $request){
       
            $jResponse=[];
            $planilla = [];
            $gen=0;
            $nogen=0;
            $msgerror="";
            $id_certificado=0;
            try{
                
      
                $id_entidad     = $request->id_entidad;
                $id_anho        = $request->id_anho;
                $id_mes         = $request->id_mes;
                $id_persona     = $request->id_persona;
                $id_depto       = $request->id_depto;
                
                
                    $entidad  = APSData::entidadPersona($id_entidad);

                    $id_persona_enti  = 0;
                    foreach ($entidad as $data) {
                        $id_persona_enti = $data->id_persona;
                    }

                    $empresa = APSData::entidadEmpresa($id_persona_enti);
                    
                    $respuesta  = PaymentsData::obtenerDatosFirma($id_entidad,$id_depto);

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

                                    $ret = $this->generarPrevioBoleta($planilla,$id_anho,$id_mes,$id_depto,$id_certificado);

                                    if( strlen($ret["html"])>0 and strlen($ret["p"])>0 and  strlen($ret["nombre_entidad"])>0 and strlen($ret["nomarchivo"])>0){
                                        
                                        PDF::SetCreator('DIGETI');
                                        PDF::SetAuthor('DIGETI-UPeU');
                                        PDF::SetTitle('eBoletas UPeU');
                                        PDF::AddPage();
                                        $info = array(
                                                'Name' => 'UPeU',
                                                'Location' =>'Lurigancho(Chosica) - Lima',
                                                'Reason' =>'UPeU',
                                                'ContactInfo' => 'http://www.upeu.edu.pe',
                                                );

                                        PDF::writeHTML($ret["html"],true,0,true,0);



                                        $carpeta = 'boletas';
                                        
                                        $ruta = public_path() .'/'.$carpeta.'/boletaprevio.pdf';
                                        //dd($ruta);

                                        PDF::Output($ruta,'F');
                                        
                                        
                                        
                                        $archivo =file_get_contents($ruta);
                                        //$archivo =$ruta;
                                        
                                        $doc  = base64_encode($archivo);
                    
                                        $jResponse = [
                                            'nerror' => 0,
                                            'mensaje' => "OK",
                                            'data' => $doc
                                            ];

                                    }else{
                                         $jResponse = [
                                            'nerror' => 1,
                                            'mensaje' => "No se ha generado",
                                            'data'=>''
                                            ];
                                    }


                                }
                                
                            }else{
                                $jResponse = [
                                    'nerror' => 1,
                                    'mensaje' => "No hay data para generar",
                                    'data'=>''
                                    ];
                            }
                        }else{
                            $jResponse = [
                                'nerror' => 1,
                                'mensaje' =>$respuesta["msgerror"]

                            ];
                            
                        }

            }catch(\Exception $e){ 
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' => "ORA-".$e->getFile().' '.$e->getLine().' '.$e->getMessage(),
                    'data'=>''
                ];
                

            }
 
            
        return response()->json($jResponse);
    }
    public function generarPrevioBoleta($data,$id_anho,$id_mes,$id_depto,$id_certificado){
       $qrcodigo="";
     
       $html='';
       
       $dataFirma=PaymentsData::showCertificate($id_certificado);

       $firma="";
       
       $p="";


       $representante="";
       $representantedoc="";

       $nombre_general="";
       $ubicacion="";
       $nomarchivo="";
       $logo_boleta="";
       $boleta_title_background="#992E45";
       $boleta_ds_remuneraciones="Decreto Supremo N. 15-72 del 28/09/72";
       
       foreach($dataFirma as $row){

           $firma = $row->firma;

           $representante=$row->representante;
           $representantedoc=$row->num_documento;
           $logo_boleta = $row->logo_boleta;
           $boleta_title_background = $row->boleta_title_background;
           $boleta_ds_remuneraciones = $row->boleta_ds_remuneraciones;
       }
   
        foreach($data as $item){

            foreach($item['datos'] as $item){
                $html.='<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td coslpan="2" style="background-color: '.$boleta_title_background.';text-align: center;font-size: 8px;color: #FFFFFF;">BOLETA DE PAGO DE REMUNERACIONES</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><br/></td>';
                        $html.='<td><br/></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        // $ruta=asset('img/upeu.png');
                        $ruta=asset($logo_boleta);
                        $html.='<td style="width:15%; " rowspan="2"><br/><img src="'.$ruta.'"  height="51"></td>';
                        $nombre_general=$item['nombre'];
                        $html.='<td style="width:70%; text-align: center; font-size: 11px; font-family: "Times New Roman", Georgia, Serif;">'.$item['nombre'].'</td>';
                        $html.='<td style="width:15%; " rowspan="2">&nbsp;</td>';


                    $html.='</tr>';
                $html.='</table>';
                $html.='<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td><br/></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td  style="text-align: center;font-size: 8px;">Expresado en Soles<br/>'.$boleta_ds_remuneraciones.'<br/>RUC: '.$item['id_ruc'] .'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><br/></td>';
                    $html.='</tr>';
                $html.='</table>';
                
                $html.='<table style="width:100%;font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td style="width:50%;">';
                            $html.='<table class="table" style="font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';

                                $html.='<tr>';
                                    $html.='<td><strong>Nombre:</strong></td>';
                                    $html.='<td>'.$item['employee']['nom_persona'].'</td>';
                                $html.='</tr>';
                                
                                $html.='<tr>';
                                    $html.='<td><strong>Cargo:</strong></td>';
                                    $html.='<td>'.$item['employee']['nom_cargo'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Codigo ESSALUD:</strong></td>';
                                    $html.='<td>'.$item['employee']['essalud'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Codigo CUSS:</strong></td>';
                                    $html.='<td>'.$item['employee']['cuss'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Fecha de Nacimiento:</strong></td>';
                                    $html.='<td>'.$item['employee']['fec_nacimiento'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Número de DNI:</strong></td>';
                                    $html.='<td>'.$item['employee']['num_documento'].'</td>';
                                $html.='</tr>';

                            $html.='</table>';
                        $html.='</td>';
                        $html.='<td style="width:50%;">';
                            $html.='<table style="font-size: 7px; font-family: "Times New Roman", Georgia, Serif;">';

                                $html.='<tr>';
                                    $html.='<td><strong>Mes de Pago:</strong></td>';
                                    $html.='<td>'.$item['employee']['mes'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Fecha de Ingreso:</strong></td>';
                                    $html.='<td>'.$item['employee']['fec_inicio'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Fecha de Cese:</strong></td>';
                                    $html.='<td>'.$item['employee']['fec_termino'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Dias / Horas Trabajados:</strong></td>';
                                    $html.='<td>'.$item['employee']['dh'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong> Vacaciones:</strong></td>';
                                    $html.='<td>'.$item['employee']['vacaciones'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>AFP:</strong></td>';
                                    $html.='<td>'.$item['employee']['afp'].'</td>';
                                $html.='</tr>';

                            $html.='</table>';
                        $html.='</td>';
                     $html.='</tr>';
                     $html.='<tr>';
                        $html.='<td colspan="2"><br/></td>';
                    $html.='</tr>';
                $html.='</table>';
                
                

                $html.='<table   style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;border-collapse: collapse;">';
                    $html.='<tr style="background-color: '.$boleta_title_background.';text-align: center;font-size: 8px;color: #FFFFFF;">';
                        $html.='<th style="width:34%;border: 1px solid '.$boleta_title_background.';">INGRESOS</th>';
                        $html.='<th style="width:33%;border: 1px solid '.$boleta_title_background.';">APORTES DEL TRABAJADOR</th>';
                        $html.='<th style="width:33%;border: 1px solid '.$boleta_title_background.';">DESCUENTOS</th>';
                    $html.='</tr>';
                   
                    $html.='<tr>';
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';" rowspan="2">';
                            $html.='<table style="width:100%; font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                            foreach($item['remuneration'] as $detalle){

                                $html.='<tr>';
                                    $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                    $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                $html.='</tr>';

                            }
                            foreach($item['t_remu'] as $detalle){

                                $html.='<tr>';
                                    $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                    $html.='<td style="width:30%;text-align: right;"><strong>'.$detalle['imp'].'</strong></td>';
                                $html.='</tr>';

                            }
                            $html.='</table>';
                        $html.='</td>';
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';">';
                            $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                                
                                foreach($item['retention'] as $detalle){

                                    $html.='<tr>';
                                        $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                    $html.='</tr>';

                                }

                                foreach($item['t_reten'] as $detalle){

                                    $html.='<tr>';
                                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                        $html.='<td style="width:30%;text-align: right;"><strong>'.$detalle['imp'].'</strong></td>';
                                    $html.='</tr>';

                                }
                                        
                            $html.='</table>';
                        $html.='</td>';
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';" rowspan="2">';
                            $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                                foreach($item['diezmo'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                    $html.='</tr>';
                                }
                                foreach($item['t_diezmo'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['imp'].'</td>';
                                    $html.='</tr>';
                                }
                            $html.='</table>';
                        $html.='</td>';
                    $html.='</tr>';
                    
                   //20138122256+2017+8+42188532+MARLO RIMARACHIN,Wilder+3,428.34
                    $html.='<tr>';
                        
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';">';
                            $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                                
                                $html.='<tr>';
                                    $html.='<td colspan="2"><strong>APORTES DEL EMPLEADOR</strong></td>';
                                $html.='</tr>';
                                foreach($item['contribution'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                    $html.='</tr>';
                                }

                                foreach($item['t_contri'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                        $html.='<td style="width:30%;text-align: right;"><strong>'.$detalle['imp'].'</strong></td>';
                                    $html.='</tr>';

                                }
                                
                            $html.='</table>';
                        $html.='</td>';
    
                    $html.='</tr>';
                $html.='</table>';   
                $html.='<table style="width:100%;font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td><br/><br/></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td  style="width:50%; text-align: left;">';
                        $html.='<table style="font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                        $neto=0;
                            foreach($item['t_neto'] as $detalle){
                               $html.='<tr>';
                                    $html.='<td><strong>NETO A PAGAR</strong></td>';
                                    $html.='<td><strong>'.$detalle['imp'].'</strong></td>';
                                $html.='</tr>';
                                
                                $neto= str_replace(",",'.',$detalle['imp']);
                            }
                        $html.='</table>';
                        $html.='</td>';
                        $html.='<td  style="width:50%; text-align: right;">'.$item['employee']['mes_name'].'</td>';
                    $html.='</tr>';
                 $html.='</table>';
                 
                $qrcodigo=$item['id_ruc'].$id_anho.$id_mes.$item['employee']['num_documento'].$item['employee']['nom_persona'].$neto;
                $key=$id_anho.$id_mes.$item['employee']['num_documento'];
                 
                $nomarchivo = $item['employee']['num_documento'].'-'.$id_anho.'-'.$id_mes;
                 
                $html.='<table  style="width:100%;font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr><td><br/></td></tr>';
                    $html.='<tr><td><br/></td></tr>';
                    $html.='<tr>';
                        $html.='<td  style="width:33%;font-size: 8px; text-align: center;">';
                            if(strlen($firma)>0){
                                $ruta=asset('img/'.$firma);
                                $html.='<img src="'.$ruta.'" width="50" height="40" style="margin-top: -10px !important;padding-top: -10px !important;margin-bottom: -10px !important;padding-bottom: -10px !important;">';
                            }
                        $html.='</td>';
                        $html.='<td style="width:34%;text-align: center;" rowspan="2">';
                         $qr = new qrcode();
                         $p = password_hash($qrcodigo, PASSWORD_DEFAULT); 
                         $url_pdf= url('humantalent/payments-tickets-worker-download');
                         //$qr->link($url_pdf."?p=".$p);
                         //$html.='<img src="'.$qr->get_link().'" border="0" width="70" height="70"/>';
                        $html.='</td>';
                        $html.='<td></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td  style="width:33%;font-size: 7px;text-align: center;">';
                            $html.='--------------------------------------------<br/>';
                            $html.='EMPLEADOR<br/>';
                            $html.=$representante.'<br/>';
                            $html.='DNI: '.$representantedoc.'<br/><br/>';
                            //$html.='*Doc. Interno: '.$id_certificado;
                      

                        $html.='</td>';
                        
                         $html.='<td  style="width:33%;font-size: 7px;text-align: center;">';
                            $html.='--------------------------------------------<br/>';
                            $html.='TRABAJADOR<br/>';
                            $html.=$item['employee']['nom_persona'].'<br/>';
                            $html.='DNI: '.$item['employee']['num_documento'];
                            
                        $html.='</td>';
                    $html.='</tr>';

                $html.='</table>';
                
                $html.='<table style="width:100%;font-size: 7px;">';
                $html.='<tr><td><br/></td></tr>';
                /*$html.='<tr>';
                        $html.='<td  style="font-size: 7px;">Documento firmado digitalmente por '.$nombre_general.' con fecha '.date('d/m/Y').'</td>';
                    $html.='</tr>';*/
                $html.='</table>';

            }
        }
        
        $return=[
            'html'=>$html,
            'p'=>$p,
            'nombre_entidad'=>$nombre_general,
            'nomarchivo'=>$nomarchivo
        ];
       return $return;

    }
    public function obtenerpdffirmar(Request $request){
        
       
            $planilla = [];
            $gen=0;
            $nogen=0;
            $msgerror="";
            $mensajeerror="No se puede obtener";
            $nroerror=1;
            $id_entidad     = $request->id_entidad;
            $id_anho        = $request->id_anho;
            $id_mes         = $request->id_mes;
            $option         = true;
            $id_persona     = $request->id_persona;
            $id_depto       = $request->id_depto;
            $items          = $request->items;
            $id_user          = $request->id_user;
            $nomfirma       = $request->firma;

            $logo="";
            $razon="";
            $ubicacion="";
            try{

                
                $id_certificado = 0;
                $certificadodig = '';//$request->certificado;
                $clave          = '';//$request->clave;
                $pkey           = '';//$request->pkey;
                
                /*$jResponse = [
                                            'nerror' => 1,
                                            'mensaje' => "No se ha generado ".$items."*".$id_user,
                                            'clave' => "",
                                            'nomarchivo' => "",
                                            'data'=>''
                                            ];
                
                 return response()->json($jResponse);
                */
                //$retdir      =  PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes);
                //if($retdir["nerror"]==0){
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
                        $respuesta  = PaymentsData::obtenerDatosFirma($id_entidad,$id_depto);

                        if($respuesta["nerror"]==0){
                            
                            $logo=$respuesta["logo"];
                            $razon=$respuesta["razon"];
                            $ubicacion=$respuesta["ubicacion"];
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
                                            'mes_name' => $data_employee->mes_name,
                                            'id_contrato' => $id_contrato,
                                            'id_depto_padre' => $data_employee->id_depto_padre];
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

                                        /*$ret=$this->firmarBoletapdf($ret["html"],$ret["nombre_entidad"],$ret["nomarchivo"],$id_certificado,$employee,$id_depto,$ret["p"],$retdir["directorio"],$clave,$certificadodig,$pkey);
                                        if($ret["nerror"]==0){
                                            $gen++;
                                        }else{
                                            $nogen++;
                                            $msgerror = $ret["msgerror"];
                                        }*/
                                        
                                        PDF::SetCreator('DIGETI');
                                        PDF::SetAuthor('DIGETI-UPeU');
                                        PDF::SetTitle('eBoletas UPeU');
                                        PDF::AddPage();
                                        $info = array(
                                                'Name' => 'UPeU',
                                                'Location' =>'Lurigancho(Chosica) - Lima',
                                                'Reason' =>'UPeU',
                                                'ContactInfo' => 'http://www.upeu.edu.pe',
                                                );

                                        PDF::writeHTML($ret["html"],true,0,true,0);



                                        $carpeta = 'boletas';
                                        
                                        $ruta = public_path() .'/'.$carpeta.'/boletafimar.pdf';
                                        //dd($ruta);

                                        PDF::Output($ruta,'F');
                                        
                                        
                                        
                                        $archivo =file_get_contents($ruta);
                                        //$archivo =$ruta;
                                        
                                        $doc  = base64_encode($archivo);
                    
                                        $jResponse = [
                                            'nerror' => 0,
                                            'mensaje' => "OK",
                                            'clave' => $ret["p"],
                                            'nomarchivo' => $ret["nomarchivo"],
                                            'data' => $doc
                                            ];
                                            $mensajeerror="Se ha generado archivo origen";
                                            $nroerror=0;

                                    }else{
                                        $jResponse = [
                                            'nerror' => 1,
                                            'mensaje' => "No se ha generado",
                                            'clave' => "",
                                            'nomarchivo' => "",
                                            'data'=>''
                                            ];
                                        
                                            $mensajeerror="No se ha generado";
                                            $nroerror=1;
                                        //$nogen++;
                                    }


            
                                    //$this->firmarBoletapdf($planilla,$id_anho,$id_mes,$id_entidad,$id_depto,$id_certificado);
                                }
                                /*if($gen==0){
                                                                        
                                    $jResponse = [
                                        'nerror' => 1,
                                        'mensaje' =>'No se ha generado firma digital, '.$msgerror

                                    ];
                                }else{
                                    $mensaje="";
                                    if($gen>0 and $nogen==0){
                                        $mensaje="Se ha generado correctamente";
                                    }else{
                                        $mensaje="Se ha generado  ".$gen. " correctamente y ".$nogen." no se ha generado";
                                    }
                                    
                                    
                                    $jResponse = [
                                        'nerror' => 0,
                                        'mensaje' =>$mensaje

                                    ];
                                }*/
                            }else{
                               
                                
                               $jResponse = [
                                    'nerror' => 1,
                                    'mensaje' => "No hay data para generar",
                                    'clave' => "",
                                    'nomarchivo' => "",
                                    'data'=>''
                                    ];
                                $mensajeerror="No hay data para generar";
                                $nroerror=1;
                            }


                        }else{
                            $jResponse = [
                                'nerror' => 1,
                                'mensaje' =>$respuesta["msgerror"].'*'.$id_certificado,
                                'clave' => "",
                                'nomarchivo' => "",
                                'data'=>''    
                            ];
                            $mensajeerror=substr($respuesta["msgerror"],0,1999);
                            $nroerror=1;
                            
                        }
                    }else{
                        $jResponse = [
                            'nerror' => 1,
                            'mensaje' =>'No existe información de boleta para el periodo '.$id_anho.'-'.$id_mes.' para el personal('.$id_persona.')',
                            'clave' => "",
                            'nomarchivo' => "",
                            'data'=>''
                        ];
                        $mensajeerror='No existe información de boleta para el periodo '.$id_anho.'-'.$id_mes.' para el personal('.$id_persona.')';
                        $nroerror=1;
                       
                    }
                /*}else{
                    $jResponse = [
                        'nerror' => 1,
                        'mensaje' => $retdir["msgerror"],
                        'clave' => "",
                        'nomarchivo' => "",
                        'data'=>''    
                    ];
                    $mensajeerror=substr($retdir["msgerror"],0,1999);
                    $nroerror=1;

                }*/
            }catch(\Exception $e){                   
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' => "ORA-".$e->getFile().' '.$e->getLine().' '.$e->getMessage(),
                    'clave' => "",
                    'nomarchivo' => "",
                    'data'=>''
                ];
                
                $mensajeerror=substr($e->getMessage(),0,1999);
                $nroerror=1;

            }
            
            $error = '';
            $origen ='';
            if($nroerror==1){
                $error = $mensajeerror;
            }else{
                $origen = $mensajeerror;
            }
            
            $id_log = SignatureData::logfirmaboleta(0,'',$items,0,$id_persona,$id_anho,$id_mes,$origen,$id_user,$error,1,'','','',$nomfirma);
            $logo_firma='';
            if($logo){
                $ruta = public_path() .'/'.$logo;
                $archivo =file_get_contents($ruta);
                                        
                $logo_firma  = base64_encode($archivo);
            }
  
            $respuesta=[
                'nerror' => $jResponse['nerror'],
                'mensaje' => $jResponse['mensaje'],
                'clave' => $jResponse['clave'],
                'nomarchivo' => $jResponse['nomarchivo'],
                'data'=>$jResponse['data'],
                'items'=>$id_log,
                'logo'=>$logo_firma,
                'razon'=>$razon,
                'ubicacion'=>$ubicacion,
            ];
            
        return response()->json($respuesta);
    }
    public function firmarwin(Request $request){
        
       
            $planilla = [];
            $gen=0;
            $nogen=0;
            $msgerror="";
            try{

                $id_entidad     = $request->id_entidad;
                $id_anho        = $request->id_anho;
                $id_mes         = $request->id_mes;
                $option         = true;
                $id_persona     = $request->id_persona;
                $id_depto       = $request->id_depto;
                $id_certificado = 0;
                $certificadodig = $request->certificado;
                $clave          = $request->clave;
                $pkey           = $request->pkey;                
                
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' =>trim(str_replace(' ','+',$certificadodig) )

                ];
                
                PaymentsData::insertlog($certificadodig."|".$clave."|".$pkey);

                 //return response()->json($jResponse);
                
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
                        $respuesta  = PaymentsData::obtenerDatosFirma($id_entidad,$id_depto);

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
                                            'mes_name' => $data_employee->mes_name,
                                            'id_contrato' => $id_contrato
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
                                            'entity' => $id_entidad];

                                    }
                                    $planilla=[];
                                    array_push($planilla, array("datos" => $company));


                                    $id_certificado = $respuesta["certificado"];

                                    $ret = $this->generarPlantillaBoleta($planilla,$id_anho,$id_mes,$id_certificado,$id_depto);

                                    if( strlen($ret["html"])>0 and strlen($ret["p"])>0 and  strlen($ret["nombre_entidad"])>0 and strlen($ret["nomarchivo"])>0){

                                        $ret=$this->firmarBoletapdf($ret["html"],$ret["nombre_entidad"],$ret["nomarchivo"],$id_certificado,$employee,$id_depto,$ret["p"],$retdir["directorio"],$clave,$certificadodig,$pkey);
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
                                                                        
                                    $jResponse = [
                                        'nerror' => 1,
                                        'mensaje' =>'No se ha generado firma digital, '.$msgerror,
                                        'clave' => "",
                                    'nomarchivo' => "",
                                    'data'=>''

                                    ];
                                }else{
                                    $mensaje="";
                                    if($gen>0 and $nogen==0){
                                        $mensaje="Se ha generado correctamente";
                                    }else{
                                        $mensaje="Se ha generado  ".$gen. " correctamente y ".$nogen." no se ha generado";
                                    }
                                    
                                    
                                    $jResponse = [
                                        'nerror' => 0,
                                        'mensaje' =>$mensaje,
                                        'clave' => "",
                                    'nomarchivo' => "",
                                    'data'=>''

                                    ];
                                }
                            }else{
                               
                                
                               $jResponse = [
                                    'nerror' => 1,
                                    'mensaje' => "No hay data para generar",
                                    'clave' => "",
                                    'nomarchivo' => "",
                                    'data'=>''
                                    ];
                            }


                        }else{
                            $jResponse = [
                                'nerror' => 1,
                                'mensaje' =>$respuesta["msgerror"].'*'.$id_certificado,
                                'clave' => "",
                                'nomarchivo' => "",
                                'data'=>''    
                            ];
                            
                        }
                    }else{
                        $jResponse = [
                            'nerror' => 1,
                            'mensaje' =>'No existe información de boleta para el periodo '.$id_anho.'-'.$id_mes.' para el personal('.$id_persona.')',
                            'clave' => "",
                            'nomarchivo' => "",
                            'data'=>''
                        ];
                       
                    }
                }else{
                    $jResponse = [
                        'nerror' => 1,
                        'mensaje' => $retdir["msgerror"],
                        'clave' => "",
                        'nomarchivo' => "",
                        'data'=>''    
                    ];

                }
            }catch(\Exception $e){                   
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' => "ORA-".$e->getFile().' '.$e->getLine().' '.$e->getMessage(),
                    'clave' => "",
                    'nomarchivo' => "",
                    'data'=>''
                ];

            }
              
        
            
        return response()->json($jResponse);
    }
    public function generarPlantillaBoleta($data,$id_anho,$id_mes,$id_certificado,$id_depto){
       $qrcodigo="";
     
       $html='';
       
       $dataFirma=PaymentsData::showCertificate($id_certificado);

       $firma="";
       
       $p="";

       $representante="";
       $representantedoc="";

       $nombre_general="";
       $ubicacion="";
       $nomarchivo="";
       $logo_boleta="";
       $boleta_title_background="#992E45";
       $boleta_ds_remuneraciones="Decreto Supremo N. 15-72 del 28/09/72";
       
       foreach($dataFirma as $row){

           $firma = $row->firma;

           $representante=$row->representante;
           $representantedoc=$row->num_documento;
           $logo_boleta = $row->logo_boleta;
           $boleta_title_background = $row->boleta_title_background;
           $boleta_ds_remuneraciones = $row->boleta_ds_remuneraciones;
       }
   
        foreach($data as $item){

            foreach($item['datos'] as $item){
                $html.='<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td coslpan="2" style="background-color: '.$boleta_title_background.'; text-align: center;font-size: 8px;color: #FFFFFF;">BOLETA DE PAGO DE REMUNERACIONES</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><br/></td>';
                        $html.='<td><br/></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        // $ruta=asset('img/upeu.png');
                        $ruta=asset($logo_boleta);
                    $html.='<td style="width:15%; " rowspan="2"><br/><img src="'.$ruta.'" height="51"></td>';
                        $nombre_general=$item['nombre'];
                        $html.='<td style="width:70%; text-align: center; font-size: 11px; font-family: "Times New Roman", Georgia, Serif;">'.$item['nombre'].'</td>';
                        $html.='<td style="width:15%; " rowspan="2">&nbsp;</td>';
                    $html.='</tr>';
                $html.='</table>';
                $html.='<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td><br/></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td  style="text-align: center;font-size: 8px;">Expresado en Soles<br/>'.$boleta_ds_remuneraciones.'<br/>RUC: '.$item['id_ruc'] .'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><br/></td>';
                    $html.='</tr>';
                $html.='</table>';
                
                $html.='<table style="width:100%;font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td style="width:50%;">';
                            $html.='<table class="table" style="font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';

                                $html.='<tr>';
                                    $html.='<td><strong>Nombre:</strong></td>';
                                    $html.='<td>'.$item['employee']['nom_persona'].'</td>';
                                $html.='</tr>';
                                
                                $html.='<tr>';
                                    $html.='<td><strong>Cargo:</strong></td>';
                                    $html.='<td>'.$item['employee']['nom_cargo'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Codigo ESSALUD:</strong></td>';
                                    $html.='<td>'.$item['employee']['essalud'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Codigo CUSS:</strong></td>';
                                    $html.='<td>'.$item['employee']['cuss'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Fecha de Nacimiento:</strong></td>';
                                    $html.='<td>'.$item['employee']['fec_nacimiento'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Número de DNI:</strong></td>';
                                    $html.='<td>'.$item['employee']['num_documento'].'</td>';
                                $html.='</tr>';

                            $html.='</table>';
                        $html.='</td>';
                        $html.='<td style="width:50%;">';
                            $html.='<table style="font-size: 7px; font-family: "Times New Roman", Georgia, Serif;">';

                                $html.='<tr>';
                                    $html.='<td><strong>Mes de Pago:</strong></td>';
                                    $html.='<td>'.$item['employee']['mes'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Fecha de Ingreso:</strong></td>';
                                    $html.='<td>'.$item['employee']['fec_inicio'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Fecha de Cese:</strong></td>';
                                    $html.='<td>'.$item['employee']['fec_termino'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>Dias / Horas Trabajados:</strong></td>';
                                    $html.='<td>'.$item['employee']['dh'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong> Vacaciones:</strong></td>';
                                    $html.='<td>'.$item['employee']['vacaciones'].'</td>';
                                $html.='</tr>';
                                $html.='<tr>';
                                    $html.='<td><strong>AFP:</strong></td>';
                                    $html.='<td>'.$item['employee']['afp'].'</td>';
                                $html.='</tr>';

                            $html.='</table>';
                        $html.='</td>';
                     $html.='</tr>';
                     $html.='<tr>';
                        $html.='<td colspan="2"><br/></td>';
                    $html.='</tr>';
                $html.='</table>';
                
                

                $html.='<table   style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;border-collapse: collapse;">';
                    $html.='<tr style="background-color: '.$boleta_title_background.';text-align: center;font-size: 8px;color: #FFFFFF;">';
                        $html.='<th style="width:34%;border: 1px solid '.$boleta_title_background.';">INGRESOS</th>';
                        $html.='<th style="width:33%;border: 1px solid '.$boleta_title_background.';">APORTES DEL TRABAJADOR</th>';
                        $html.='<th style="width:33%;border: 1px solid '.$boleta_title_background.';">DESCUENTOS</th>';
                    $html.='</tr>';
                   
                    $html.='<tr>';
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';" rowspan="2">';
                            $html.='<table style="width:100%; font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                            foreach($item['remuneration'] as $detalle){

                                $html.='<tr>';
                                    $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                    $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                $html.='</tr>';

                            }
                            foreach($item['t_remu'] as $detalle){

                                $html.='<tr>';
                                    $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                    $html.='<td style="width:30%;text-align: right;"><strong>'.$detalle['imp'].'</strong></td>';
                                $html.='</tr>';

                            }
                            $html.='</table>';
                        $html.='</td>';
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';">';
                            $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                                
                                foreach($item['retention'] as $detalle){

                                    $html.='<tr>';
                                        $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                    $html.='</tr>';

                                }

                                foreach($item['t_reten'] as $detalle){

                                    $html.='<tr>';
                                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                        $html.='<td style="width:30%;text-align: right;"><strong>'.$detalle['imp'].'</strong></td>';
                                    $html.='</tr>';

                                }
                                        
                            $html.='</table>';
                        $html.='</td>';
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';" rowspan="2">';
                            $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                                foreach($item['diezmo'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                    $html.='</tr>';
                                }
                                foreach($item['t_diezmo'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['imp'].'</td>';
                                    $html.='</tr>';
                                }
                            $html.='</table>';
                        $html.='</td>';
                    $html.='</tr>';
                    
                   //20138122256+2017+8+42188532+MARLO RIMARACHIN,Wilder+3,428.34
                    $html.='<tr>';
                        
                        $html.='<td style="border: 1px solid '.$boleta_title_background.';">';
                            $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                                
                                $html.='<tr>';
                                    $html.='<td colspan="2"><strong>APORTES DEL EMPLEADOR</strong></td>';
                                $html.='</tr>';
                                foreach($item['contribution'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;">'.$detalle['nombre'].'</td>';
                                        $html.='<td style="width:30%;text-align: right;">'.$detalle['importe'].'</td>';
                                    $html.='</tr>';
                                }

                                foreach($item['t_contri'] as $detalle){
                                    $html.='<tr>';
                                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                                        $html.='<td style="width:30%;text-align: right;"><strong>'.$detalle['imp'].'</strong></td>';
                                    $html.='</tr>';

                                }
                                
                            $html.='</table>';
                        $html.='</td>';
    
                    $html.='</tr>';
                $html.='</table>';   
                $html.='<table style="width:100%;font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr>';
                        $html.='<td><br/><br/></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td  style="width:50%; text-align: left;">';
                        $html.='<table style="font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                        $neto=0;
                            foreach($item['t_neto'] as $detalle){
                               $html.='<tr>';
                                    $html.='<td><strong>NETO A PAGAR</strong></td>';
                                    $html.='<td><strong>'.$detalle['imp'].'</strong></td>';
                                $html.='</tr>';
                                
                                $neto= str_replace(",",'.',$detalle['imp']);
                            }
                        $html.='</table>';
                        $html.='</td>';
                        $html.='<td  style="width:50%; text-align: right;">'.$item['employee']['mes_name'].'</td>';
                    $html.='</tr>';
                 $html.='</table>';
                 
                $qrcodigo=$item['id_ruc'].$id_anho.$id_mes.$item['employee']['num_documento'].$item['employee']['nom_persona'].$neto;
                $key=$id_anho.$id_mes.$item['employee']['num_documento'];
                 
                // $nomarchivo = $item['employee']['num_documento'].'-'.$id_anho.'-'.$id_mes;
                //$nomarchivo = $item['employee']['id_contrato'].'-'.$item['employee']['num_documento'].'-'.$id_anho.'-'.$id_mes;
                $nomarchivo = $item['employee']['id_depto_padre'].'-'.$item['employee']['id_contrato'].'-'.$item['employee']['num_documento'].'-'.$id_anho.'-'.$id_mes;
                
                $html.='<table  style="width:100%;font-size: 8px;font-family: "Times New Roman", Georgia, Serif;">';
                    $html.='<tr><td><br/></td></tr>';
                    $html.='<tr><td><br/></td></tr>';
                    $html.='<tr>';
                        $html.='<td  style="width:33%;font-size: 8px; text-align: center;">';
                            if(strlen($firma)>0){
                                $ruta=asset('img/'.$firma);
                                $html.='<img src="'.$ruta.'" width="50" height="40" style="margin-top: -10px !important;padding-top: -10px !important;margin-bottom: -10px !important;padding-bottom: -10px !important;">';
                            }
                        $html.='</td>';
                        $html.='<td style="width:34%;text-align: center;" rowspan="2">';
                         $qr = new qrcode();
                         $p = password_hash($qrcodigo, PASSWORD_DEFAULT); 
                         $url_pdf= url('humantalent/payments-tickets-worker-download');
                         //$qr->link($url_pdf."?p=".$p);
                         //$html.='<img src="'.$qr->get_link().'" border="0" width="70" height="70"/>';
                        $html.='</td>';
                        $html.='<td></td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td  style="width:33%;font-size: 7px;text-align: center;">';
                            $html.='--------------------------------------------<br/>';
                            $html.='EMPLEADOR<br/>';
                            $html.=$representante.'<br/>';
                            $html.='DNI: '.$representantedoc.'<br/><br/>';
                            //$html.='*Doc. Interno: '.$id_certificado;
                      

                        $html.='</td>';
                        
                         $html.='<td  style="width:33%;font-size: 7px;text-align: center;">';
                            $html.='--------------------------------------------<br/>';
                            $html.='TRABAJADOR<br/>';
                            $html.=$item['employee']['nom_persona'].'<br/>';
                            $html.='DNI: '.$item['employee']['num_documento'];
                            
                        $html.='</td>';
                    $html.='</tr>';

                $html.='</table>';
                
                $html.='<table style="width:100%;font-size: 7px;">';
                $html.='<tr><td><br/></td></tr>';
                /*$html.='<tr>';
                        $html.='<td  style="font-size: 7px;">Documento firmado digitalmente por '.$nombre_general.' con fecha '.date('d/m/Y').'</td>';
                    $html.='</tr>';*/
                $html.='</table>';

            }
        }
        
        $return=[
            'html'=>$html,
            'p'=>$p,
            'nombre_entidad'=>$nombre_general,
            'nomarchivo'=>$nomarchivo
        ];
       return $return;

    }
    
    public function firmarBoletapdf($html,$nombre_entidad,$nomarchivo,$id_certificado,$employee,$id_depto,$clave_doc,$directorio,$clave,$certificadodig,$pkey){
        
       $dataFirma=PaymentsData::showCertificate($id_certificado);
       //$clave="";
       
       $file = trim(str_replace(' ','+',$certificadodig));
       $file = trim(str_replace('*',' ',$file));
       $ubicacion="";
       foreach($dataFirma as $row){
           //$file      = $row->archivo;
           //$clave     = $this->desencriptar($row->clave,$row->numserie);
           $ubicacion = $row->ubicacion;
       }
       $ret["nerror"]=1;
       $ret["msgerror"]='No se ha generado Firma';
       
           
       
       if($clave!=''){
           
                              
            try{
                
                $certificado =array();
                    
               // if (openssl_pkcs12_read($file, $certificado, $clave)) {

             
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
                    
                    //$ret["nerror"]=1;
                    //$ret["msgerror"]=$file;
                    //return $ret;
                   
$file='-----BEGIN CERTIFICATE-----
MIIKgzCCCGugAwIBAgIJbX34BdtpQRejMA0GCSqGSIb3DQEBCwUAMIIBIjELMAkG 
A1UEBhMCUEUxDTALBgNVBAgMBExJTUExDTALBgNVBAcMBExJTUExPTA7BgNVBAsM 
NHNlZSBjdXJyZW50IGFkZHJlc3MgYXQgd3d3LmNhbWVyZmlybWEuY29tLnBlL2Fk 
ZHJlc3MxMDAuBgNVBAsMJ0FDIENBTUVSRklSTUEgUEVSw5ogQ0VSVElGSUNBRE9T 
IC0gMjAxNjEUMBIGA1UEBRMLMjA1NjYzMDI0NDcxGjAYBgNVBGEMEU5UUlBFLTIw 
NTY2MzAyNDQ3MSAwHgYDVQQKDBdDQU1FUkZJUk1BIFBFUsOaIFMuQS5DLjEwMC4G 
A1UEAwwnQUMgQ0FNRVJGSVJNQSBQRVLDmiBDRVJUSUZJQ0FET1MgLSAyMDE2MB4X 
DTE4MTAzMTIyMTUxMVoXDTIxMTAzMDIyMTUxMVowggF/MS4wLAYJKoZIhvcNAQkB 
Fh9nZXJlbmNpYS5maW5hbmNpZXJhQHVwZXUuZWR1LnBlMSYwJAYDVQQDDB1NSVJU 
SEEgSkVBTkVUVEUgVE9SUkVTIE5Vw5FFWjEYMBYGA1UEKgwPTUlSVEhBIEpFQU5F 
VFRFMRYwFAYDVQQEDA1UT1JSRVMgTlXDkUVaMRUwEwYDVQQFEwxETkk6MTA2MDA4 
NTUxEzARBgNVBAcMCkxVUklHQU5DSE8xFDASBgNVBAgMC0xJTUEgLSBMSU1BMRsw 
GQYDVQQMDBJHRVJFTlRFIEZJTkFOQ0lFUk8xGDAWBgNVBAsMD0FSRUEgRklOQU5D 
SUVSQTEzMDEGA1UECwwqSXNzdWVkIGJ5IEFDIENBTUVSRklSTUEgUEVSw5ogU0FD 
IEVSIFtQRTFdMRQwEgYDVQRhDAsyMDEzODEyMjI1NjEiMCAGA1UECgwZVU5JVkVS 
U0lEQUQgUEVSVUFOQSBVTklPTjELMAkGA1UEBhMCUEUwggEiMA0GCSqGSIb3DQEB 
AQUAA4IBDwAwggEKAoIBAQDbQUhUPvwcLqZQdJdW2j97zClnokWS6COZUpJJgyTn 
21/vMSMYIadbD+xgaABimeO8KOyJFRXmKEWO/v/XM9X7SMAkObQYGNx2BACIEWq8 
QWWcB4DE4uaEaRXFL9iALFrKX6F7zDBwPaZCA+7LAeFRKRVSLPJSTpCMPSvmz9wr 
3z/4s0dhdJam0yvuXkSsoVqJssI5J0Q/lezvcZakLS3Bv/KJnGh9fAfWTWIvREBp 
2pyBCmlj8zjHkLc0ra9ctzubcGR/GA9Ru5bmiyhwZjSmllbgkLD7i6FSLcSPO4Q2 
OpMZac9kAdp+ssHYrJEUXWXIe34uN5W2xf2nw6Znjh4TAgMBAAGjggRZMIIEVTAM 
BgNVHRMBAf8EAjAAMA4GA1UdDwEB/wQEAwIGwDAdBgNVHSUEFjAUBggrBgEFBQcD 
AgYIKwYBBQUHAwQwHQYDVR0OBBYEFFrdtAr3wBrvVirxD8TJRquA8Z1lMIGNBggr 
BgEFBQcBAQSBgDB+MFQGCCsGAQUFBzAChkhodHRwOi8vd3d3LmNhbWVyZmlybWEu 
Y29tL2NlcnRzL2FjX2NhbWVyZmlybWFfcGVydV9jZXJ0aWZpY2Fkb3MtMjAxNi5j 
cnQwJgYIKwYBBQUHMAGGGmh0dHA6Ly9vY3NwLmNhbWVyZmlybWEuY29tMIIBQgYD 
VR0jBIIBOTCCATWAFDpuZRjnVtLk8y3dpXxybf8w4YYnoYIBEKSCAQwwggEIMQsw 
CQYDVQQGEwJFUzEPMA0GA1UECAwGTUFEUklEMQ8wDQYDVQQHDAZNQURSSUQxQjBA 
BgNVBAsMOXNlZSBjdXJyZW50IGFkZHJlc3MgYXQgaHR0cHM6Ly93d3cuY2FtZXJm 
aXJtYS5jb20vYWRkcmVzczEjMCEGA1UECwwaQUMgQ0FNRVJGSVJNQSBQRVLDmiAt 
IDIwMTYxEjAQBgNVBAUTCUE4Mjc0MzI4NzEYMBYGA1UEYQwPVkFURVMtQTgyNzQz 
Mjg3MRswGQYDVQQKDBJBQyBDQU1FUkZJUk1BIFMuQS4xIzAhBgNVBAMMGkFDIENB 
TUVSRklSTUEgUEVSw5ogLSAyMDE2ggkAjGpF9TOqN0YwgaAGA1UdHwSBmDCBlTBI 
oEagRIZCaHR0cDovL2NybC5jYW1lcmZpcm1hLmNvbS9hY19jYW1lcmZpcm1hX3Bl 
cnVfY2VydGlmaWNhZG9zLTIwMTYuY3JsMEmgR6BFhkNodHRwOi8vY3JsMS5jYW1l 
cmZpcm1hLmNvbS9hY19jYW1lcmZpcm1hX3BlcnVfY2VydGlmaWNhZG9zLTIwMTYu 
Y3JsMIHMBgNVHREEgcQwgcGBH2dlcmVuY2lhLmZpbmFuY2llcmFAdXBldS5lZHUu 
cGWkgZ0wgZoxHzAdBgorBgEEAYGHLh4HDA9NSVJUSEEgSkVBTkVUVEUxFjAUBgor 
BgEEAYGHLh4IDAZUT1JSRVMxFjAUBgorBgEEAYGHLh4JDAZOVcORRVoxRzBFBgor 
BgEEAYGHLh4KDDdDRVJUSUZJQ0FETyBERSBQRVJTT05BIEZJU0lDQSBDT04gVklO 
Q1VMQUNJT04gQSBFTVBSRVNBMBwGA1UdEgQVMBOBEWNhQGNhbWVyZmlybWEuY29t 
MIGQBgNVHSAEgYgwgYUwgYIGDCsGAQQBgYcuHhAAATByMCkGCCsGAQUFBwIBFh1o 
dHRwczovL3BvbGljeS5jYW1lcmZpcm1hLmNvbTBFBggrBgEFBQcCAjA5DDdDRVJU 
SUZJQ0FETyBERSBQRVJTT05BIEZJU0lDQSBDT04gVklOQ1VMQUNJT04gQSBFTVBS 
RVNBMA0GCSqGSIb3DQEBCwUAA4ICAQBKsG8qW5CKESs1AxuDwsZqLrS9BJuotLgV 
5KmYyAx3yoANpE/touzmEaPyAVxm2ZWK7qBFy50SgaLe3FGn2h2REbpSLnglp5jH 
tzGvp18NfjIP3/SMtqSDoHQNRHhwAxOCeHWDpWJCc9N6wdWblhHUV3WXQ0mCZtsV 
8fIYDrFD6/p+f027gzgmL5CC/GxzXc/wW4HXUjNmqle8WZwTqvqAy7JQR6C2pxOa 
lSnRH58yVCEv7p82ybemk0kpt2Ujiz10dy1z5MTKL9rUKqB+aPUIz4aAIX6rvpM1 
kce/RoWMYdzHBd6EqwedhmO4KfK71COcRTl9EAK5aXtP/pJedEWTWhRPCfSfc7cq 
F/qWZ0A9El92sLaiL40UpFvZyJfXRUd1p3YGXAvcJX+AcBQdPMSLGTQ7WCd6yKlw 
eioT9+X6jEC38NJ14J5kjJbl8jY2n/x3Tw7Z0d7MERkG/g4/VGvt8HJ4FPOW82Lw 
omtTNt7jt10U2yjXdt61n3cuyCHJovzw2Y5TRqSyWkPzF/BtgFUd2DG50apF6Le4 
tUhp0uVp0oA1Rsy6AeJdGwOLTAM5GEABhfvIZP2COLmlTvxp6yRq04eCrt0t/HPS 
+xSpVSSemWvZX762uJD18qa8BXPII/2veMvMKeOQsHabd7+xEDHAGSnTrc1/STyD 
s+iNGIPa+A==
-----END CERTIFICATE-----';
$pkey='-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDbQUhUPvwcLqZQ 
dJdW2j97zClnokWS6COZUpJJgyTn21/vMSMYIadbD+xgaABimeO8KOyJFRXmKEWO 
/v/XM9X7SMAkObQYGNx2BACIEWq8QWWcB4DE4uaEaRXFL9iALFrKX6F7zDBwPaZC 
A+7LAeFRKRVSLPJSTpCMPSvmz9wr3z/4s0dhdJam0yvuXkSsoVqJssI5J0Q/lezv 
cZakLS3Bv/KJnGh9fAfWTWIvREBp2pyBCmlj8zjHkLc0ra9ctzubcGR/GA9Ru5bm 
iyhwZjSmllbgkLD7i6FSLcSPO4Q2OpMZac9kAdp+ssHYrJEUXWXIe34uN5W2xf2n 
w6Znjh4TAgMBAAECggEAbt+raNbU/JdmiSb8ZPJGyh6rDXoUvr5fSihkS4JjBlB8 
SePKQGi8TaNWwEW3dCUn8b2sJ4IfKd1Rl4dB7xyKZ+EZMPhxJwJjcdaCQky0cfCM 
JoPwzR+EvI67cJTKYb6MRuxnJ8hQ8mFqktWUStpvi3BAFOcfvxl1pUVFzxlr2E7E 
N4DYYkQFCNTCpfxx5im8ZNyTCJ6hejDb11wNMqncTttJK7fKW2LgWi6YThPBJyoe 
Ir3Dw3ByHCefsnU7S2X/IqoArC513hTNm2f2oSOsTGTqMMV99vsMs09i8W+XPdg+ 
67NCNg1GKypI3ojlsLs18YwsUzwiwWOqcKiH2EzI4QKBgQD02+gCwMvRmZIq3Crs 
MlkWZRAb0tIQQ+J45yz3iy21t6gmEBIPB0OhcwNKVygH6UFuz3eiHo4J6PHaqq7d 
ZVoiMUOiPxUdwzWI20/Kd5c7+7f9Ty71f7d0eAXxO94ZHcnmq5iNGkpexZbgkQpd 
FBtXXV1SWnh/T58++Sh4cAbUsQKBgQDlOyS0OzeSUWTZp5HjNM124ZYZL3PMoTxm 
5CAoPevBiniCmNFImvzKnRu2B80ghqEQHgrkPo6w0L/Qzga3c18mXxjD1wKoSu+q 
57W9TM+UBgRPxotyc7PTwRK+QR+CJmMOk+nis6PYgdn/Ms9Z/E/BJsMhYV91GCoT 
UVMRGfugAwKBgQDx0VfRmDvyFPtgrq0JNTP8RPTitZLVk2VPR+eF1YLgCq/aX1am 
VuM4yBBA09Bp97eyStx4VDqsmMx5ysUFSzn3uLV1v10IVqhBL24eo3rNN2xek1vq 
AibYtEwSELDoFx41W9Q9zfASpoq53yPgBqJ15nPqiASmnqjDlWedge2NEQKBgQCE 
x++I3sxf2vn6AdDFtW5WsHFLgWsoWzUrvHTMGqIXZ+hKbc68qdpIWx0UIgy2DIX7 
WguhDoOE9EZH4y+M0C2LgoZL2p3VciLF8pYZYwbSjXGt7CoWT+MYg7whrINd1nKG 
nTNbeAcugHkQFBR74WUpUxSwn0C2CRtGOVNOkqholQKBgHa95iJUvFsRPDWhkCTJ 
uS4c6hqLmoTNl6iTd75P2Rcda3cMxkbMbPdwURBZsS7fgap9YI3mvwD1DZsn2hDk 
9irvrnx9L71ntGC6q1XfHqk2hPzyh7IGbZbJk7KUgN6VzVNTTnvKeyMJt8aPbEq+ 
NVO4YCR8a+EHKEdQzDJllhg4
-----END PRIVATE KEY-----';
                    $pk = trim(str_replace(' ','+',$pkey));
                    $pk = trim(str_replace('*',' ',$pk));
                    
                    
$file='-----BEGIN CERTIFICATE-----
MIIKgzCCCGugAwIBAgIJbX34BdtpQRejMA0GCSqGSIb3DQEBCwUAMIIBIjELMAkGA1UEBhMCUEUx
DTALBgNVBAgMBExJTUExDTALBgNVBAcMBExJTUExPTA7BgNVBAsMNHNlZSBjdXJyZW50IGFkZHJl
c3MgYXQgd3d3LmNhbWVyZmlybWEuY29tLnBlL2FkZHJlc3MxMDAuBgNVBAsMJ0FDIENBTUVSRklS
TUEgUEVSw5ogQ0VSVElGSUNBRE9TIC0gMjAxNjEUMBIGA1UEBRMLMjA1NjYzMDI0NDcxGjAYBgNV
BGEMEU5UUlBFLTIwNTY2MzAyNDQ3MSAwHgYDVQQKDBdDQU1FUkZJUk1BIFBFUsOaIFMuQS5DLjEw
MC4GA1UEAwwnQUMgQ0FNRVJGSVJNQSBQRVLDmiBDRVJUSUZJQ0FET1MgLSAyMDE2MB4XDTE4MTAz
MTIyMTUxMVoXDTIxMTAzMDIyMTUxMVowggF/MS4wLAYJKoZIhvcNAQkBFh9nZXJlbmNpYS5maW5h
bmNpZXJhQHVwZXUuZWR1LnBlMSYwJAYDVQQDDB1NSVJUSEEgSkVBTkVUVEUgVE9SUkVTIE5Vw5FF
WjEYMBYGA1UEKgwPTUlSVEhBIEpFQU5FVFRFMRYwFAYDVQQEDA1UT1JSRVMgTlXDkUVaMRUwEwYD
VQQFEwxETkk6MTA2MDA4NTUxEzARBgNVBAcMCkxVUklHQU5DSE8xFDASBgNVBAgMC0xJTUEgLSBM
SU1BMRswGQYDVQQMDBJHRVJFTlRFIEZJTkFOQ0lFUk8xGDAWBgNVBAsMD0FSRUEgRklOQU5DSUVS
QTEzMDEGA1UECwwqSXNzdWVkIGJ5IEFDIENBTUVSRklSTUEgUEVSw5ogU0FDIEVSIFtQRTFdMRQw
EgYDVQRhDAsyMDEzODEyMjI1NjEiMCAGA1UECgwZVU5JVkVSU0lEQUQgUEVSVUFOQSBVTklPTjEL
MAkGA1UEBhMCUEUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDbQUhUPvwcLqZQdJdW
2j97zClnokWS6COZUpJJgyTn21/vMSMYIadbD+xgaABimeO8KOyJFRXmKEWO/v/XM9X7SMAkObQY
GNx2BACIEWq8QWWcB4DE4uaEaRXFL9iALFrKX6F7zDBwPaZCA+7LAeFRKRVSLPJSTpCMPSvmz9wr
3z/4s0dhdJam0yvuXkSsoVqJssI5J0Q/lezvcZakLS3Bv/KJnGh9fAfWTWIvREBp2pyBCmlj8zjH
kLc0ra9ctzubcGR/GA9Ru5bmiyhwZjSmllbgkLD7i6FSLcSPO4Q2OpMZac9kAdp+ssHYrJEUXWXI
e34uN5W2xf2nw6Znjh4TAgMBAAGjggRZMIIEVTAMBgNVHRMBAf8EAjAAMA4GA1UdDwEB/wQEAwIG
wDAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwHQYDVR0OBBYEFFrdtAr3wBrvVirxD8TJ
RquA8Z1lMIGNBggrBgEFBQcBAQSBgDB+MFQGCCsGAQUFBzAChkhodHRwOi8vd3d3LmNhbWVyZmly
bWEuY29tL2NlcnRzL2FjX2NhbWVyZmlybWFfcGVydV9jZXJ0aWZpY2Fkb3MtMjAxNi5jcnQwJgYI
KwYBBQUHMAGGGmh0dHA6Ly9vY3NwLmNhbWVyZmlybWEuY29tMIIBQgYDVR0jBIIBOTCCATWAFDpu
ZRjnVtLk8y3dpXxybf8w4YYnoYIBEKSCAQwwggEIMQswCQYDVQQGEwJFUzEPMA0GA1UECAwGTUFE
UklEMQ8wDQYDVQQHDAZNQURSSUQxQjBABgNVBAsMOXNlZSBjdXJyZW50IGFkZHJlc3MgYXQgaHR0
cHM6Ly93d3cuY2FtZXJmaXJtYS5jb20vYWRkcmVzczEjMCEGA1UECwwaQUMgQ0FNRVJGSVJNQSBQ
RVLDmiAtIDIwMTYxEjAQBgNVBAUTCUE4Mjc0MzI4NzEYMBYGA1UEYQwPVkFURVMtQTgyNzQzMjg3
MRswGQYDVQQKDBJBQyBDQU1FUkZJUk1BIFMuQS4xIzAhBgNVBAMMGkFDIENBTUVSRklSTUEgUEVS
w5ogLSAyMDE2ggkAjGpF9TOqN0YwgaAGA1UdHwSBmDCBlTBIoEagRIZCaHR0cDovL2NybC5jYW1l
cmZpcm1hLmNvbS9hY19jYW1lcmZpcm1hX3BlcnVfY2VydGlmaWNhZG9zLTIwMTYuY3JsMEmgR6BF
hkNodHRwOi8vY3JsMS5jYW1lcmZpcm1hLmNvbS9hY19jYW1lcmZpcm1hX3BlcnVfY2VydGlmaWNh
ZG9zLTIwMTYuY3JsMIHMBgNVHREEgcQwgcGBH2dlcmVuY2lhLmZpbmFuY2llcmFAdXBldS5lZHUu
cGWkgZ0wgZoxHzAdBgorBgEEAYGHLh4HDA9NSVJUSEEgSkVBTkVUVEUxFjAUBgorBgEEAYGHLh4I
DAZUT1JSRVMxFjAUBgorBgEEAYGHLh4JDAZOVcORRVoxRzBFBgorBgEEAYGHLh4KDDdDRVJUSUZJ
Q0FETyBERSBQRVJTT05BIEZJU0lDQSBDT04gVklOQ1VMQUNJT04gQSBFTVBSRVNBMBwGA1UdEgQV
MBOBEWNhQGNhbWVyZmlybWEuY29tMIGQBgNVHSAEgYgwgYUwgYIGDCsGAQQBgYcuHhAAATByMCkG
CCsGAQUFBwIBFh1odHRwczovL3BvbGljeS5jYW1lcmZpcm1hLmNvbTBFBggrBgEFBQcCAjA5DDdD
RVJUSUZJQ0FETyBERSBQRVJTT05BIEZJU0lDQSBDT04gVklOQ1VMQUNJT04gQSBFTVBSRVNBMA0G
CSqGSIb3DQEBCwUAA4ICAQBKsG8qW5CKESs1AxuDwsZqLrS9BJuotLgV5KmYyAx3yoANpE/touzm
EaPyAVxm2ZWK7qBFy50SgaLe3FGn2h2REbpSLnglp5jHtzGvp18NfjIP3/SMtqSDoHQNRHhwAxOC
eHWDpWJCc9N6wdWblhHUV3WXQ0mCZtsV8fIYDrFD6/p+f027gzgmL5CC/GxzXc/wW4HXUjNmqle8
WZwTqvqAy7JQR6C2pxOalSnRH58yVCEv7p82ybemk0kpt2Ujiz10dy1z5MTKL9rUKqB+aPUIz4aA
IX6rvpM1kce/RoWMYdzHBd6EqwedhmO4KfK71COcRTl9EAK5aXtP/pJedEWTWhRPCfSfc7cqF/qW
Z0A9El92sLaiL40UpFvZyJfXRUd1p3YGXAvcJX+AcBQdPMSLGTQ7WCd6yKlweioT9+X6jEC38NJ1
4J5kjJbl8jY2n/x3Tw7Z0d7MERkG/g4/VGvt8HJ4FPOW82LwomtTNt7jt10U2yjXdt61n3cuyCHJ
ovzw2Y5TRqSyWkPzF/BtgFUd2DG50apF6Le4tUhp0uVp0oA1Rsy6AeJdGwOLTAM5GEABhfvIZP2C
OLmlTvxp6yRq04eCrt0t/HPS+xSpVSSemWvZX762uJD18qa8BXPII/2veMvMKeOQsHabd7+xEDHA
GSnTrc1/STyDs+iNGIPa+A==
-----END CERTIFICATE-----';

$pk='-----BEGIN PRIVATE KEY-----
MIIKgzCCCGugAwIBAgIJbX34BdtpQRejMA0GCSqGSIb3DQEBCwUAMIIBIjELMAkGA1UEBhMCUEUx
DTALBgNVBAgMBExJTUExDTALBgNVBAcMBExJTUExPTA7BgNVBAsMNHNlZSBjdXJyZW50IGFkZHJl
c3MgYXQgd3d3LmNhbWVyZmlybWEuY29tLnBlL2FkZHJlc3MxMDAuBgNVBAsMJ0FDIENBTUVSRklS
TUEgUEVSw5ogQ0VSVElGSUNBRE9TIC0gMjAxNjEUMBIGA1UEBRMLMjA1NjYzMDI0NDcxGjAYBgNV
BGEMEU5UUlBFLTIwNTY2MzAyNDQ3MSAwHgYDVQQKDBdDQU1FUkZJUk1BIFBFUsOaIFMuQS5DLjEw
MC4GA1UEAwwnQUMgQ0FNRVJGSVJNQSBQRVLDmiBDRVJUSUZJQ0FET1MgLSAyMDE2MB4XDTE4MTAz
MTIyMTUxMVoXDTIxMTAzMDIyMTUxMVowggF/MS4wLAYJKoZIhvcNAQkBFh9nZXJlbmNpYS5maW5h
bmNpZXJhQHVwZXUuZWR1LnBlMSYwJAYDVQQDDB1NSVJUSEEgSkVBTkVUVEUgVE9SUkVTIE5Vw5FF
WjEYMBYGA1UEKgwPTUlSVEhBIEpFQU5FVFRFMRYwFAYDVQQEDA1UT1JSRVMgTlXDkUVaMRUwEwYD
VQQFEwxETkk6MTA2MDA4NTUxEzARBgNVBAcMCkxVUklHQU5DSE8xFDASBgNVBAgMC0xJTUEgLSBM
SU1BMRswGQYDVQQMDBJHRVJFTlRFIEZJTkFOQ0lFUk8xGDAWBgNVBAsMD0FSRUEgRklOQU5DSUVS
QTEzMDEGA1UECwwqSXNzdWVkIGJ5IEFDIENBTUVSRklSTUEgUEVSw5ogU0FDIEVSIFtQRTFdMRQw
EgYDVQRhDAsyMDEzODEyMjI1NjEiMCAGA1UECgwZVU5JVkVSU0lEQUQgUEVSVUFOQSBVTklPTjEL
MAkGA1UEBhMCUEUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDbQUhUPvwcLqZQdJdW
2j97zClnokWS6COZUpJJgyTn21/vMSMYIadbD+xgaABimeO8KOyJFRXmKEWO/v/XM9X7SMAkObQY
GNx2BACIEWq8QWWcB4DE4uaEaRXFL9iALFrKX6F7zDBwPaZCA+7LAeFRKRVSLPJSTpCMPSvmz9wr
3z/4s0dhdJam0yvuXkSsoVqJssI5J0Q/lezvcZakLS3Bv/KJnGh9fAfWTWIvREBp2pyBCmlj8zjH
kLc0ra9ctzubcGR/GA9Ru5bmiyhwZjSmllbgkLD7i6FSLcSPO4Q2OpMZac9kAdp+ssHYrJEUXWXI
e34uN5W2xf2nw6Znjh4TAgMBAAGjggRZMIIEVTAMBgNVHRMBAf8EAjAAMA4GA1UdDwEB/wQEAwIG
wDAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwHQYDVR0OBBYEFFrdtAr3wBrvVirxD8TJ
RquA8Z1lMIGNBggrBgEFBQcBAQSBgDB+MFQGCCsGAQUFBzAChkhodHRwOi8vd3d3LmNhbWVyZmly
bWEuY29tL2NlcnRzL2FjX2NhbWVyZmlybWFfcGVydV9jZXJ0aWZpY2Fkb3MtMjAxNi5jcnQwJgYI
KwYBBQUHMAGGGmh0dHA6Ly9vY3NwLmNhbWVyZmlybWEuY29tMIIBQgYDVR0jBIIBOTCCATWAFDpu
ZRjnVtLk8y3dpXxybf8w4YYnoYIBEKSCAQwwggEIMQswCQYDVQQGEwJFUzEPMA0GA1UECAwGTUFE
UklEMQ8wDQYDVQQHDAZNQURSSUQxQjBABgNVBAsMOXNlZSBjdXJyZW50IGFkZHJlc3MgYXQgaHR0
cHM6Ly93d3cuY2FtZXJmaXJtYS5jb20vYWRkcmVzczEjMCEGA1UECwwaQUMgQ0FNRVJGSVJNQSBQ
RVLDmiAtIDIwMTYxEjAQBgNVBAUTCUE4Mjc0MzI4NzEYMBYGA1UEYQwPVkFURVMtQTgyNzQzMjg3
MRswGQYDVQQKDBJBQyBDQU1FUkZJUk1BIFMuQS4xIzAhBgNVBAMMGkFDIENBTUVSRklSTUEgUEVS
w5ogLSAyMDE2ggkAjGpF9TOqN0YwgaAGA1UdHwSBmDCBlTBIoEagRIZCaHR0cDovL2NybC5jYW1l
cmZpcm1hLmNvbS9hY19jYW1lcmZpcm1hX3BlcnVfY2VydGlmaWNhZG9zLTIwMTYuY3JsMEmgR6BF
hkNodHRwOi8vY3JsMS5jYW1lcmZpcm1hLmNvbS9hY19jYW1lcmZpcm1hX3BlcnVfY2VydGlmaWNh
ZG9zLTIwMTYuY3JsMIHMBgNVHREEgcQwgcGBH2dlcmVuY2lhLmZpbmFuY2llcmFAdXBldS5lZHUu
cGWkgZ0wgZoxHzAdBgorBgEEAYGHLh4HDA9NSVJUSEEgSkVBTkVUVEUxFjAUBgorBgEEAYGHLh4I
DAZUT1JSRVMxFjAUBgorBgEEAYGHLh4JDAZOVcORRVoxRzBFBgorBgEEAYGHLh4KDDdDRVJUSUZJ
Q0FETyBERSBQRVJTT05BIEZJU0lDQSBDT04gVklOQ1VMQUNJT04gQSBFTVBSRVNBMBwGA1UdEgQV
MBOBEWNhQGNhbWVyZmlybWEuY29tMIGQBgNVHSAEgYgwgYUwgYIGDCsGAQQBgYcuHhAAATByMCkG
CCsGAQUFBwIBFh1odHRwczovL3BvbGljeS5jYW1lcmZpcm1hLmNvbTBFBggrBgEFBQcCAjA5DDdD
RVJUSUZJQ0FETyBERSBQRVJTT05BIEZJU0lDQSBDT04gVklOQ1VMQUNJT04gQSBFTVBSRVNBMA0G
CSqGSIb3DQEBCwUAA4ICAQBKsG8qW5CKESs1AxuDwsZqLrS9BJuotLgV5KmYyAx3yoANpE/touzm
EaPyAVxm2ZWK7qBFy50SgaLe3FGn2h2REbpSLnglp5jHtzGvp18NfjIP3/SMtqSDoHQNRHhwAxOC
eHWDpWJCc9N6wdWblhHUV3WXQ0mCZtsV8fIYDrFD6/p+f027gzgmL5CC/GxzXc/wW4HXUjNmqle8
WZwTqvqAy7JQR6C2pxOalSnRH58yVCEv7p82ybemk0kpt2Ujiz10dy1z5MTKL9rUKqB+aPUIz4aA
IX6rvpM1kce/RoWMYdzHBd6EqwedhmO4KfK71COcRTl9EAK5aXtP/pJedEWTWhRPCfSfc7cqF/qW
Z0A9El92sLaiL40UpFvZyJfXRUd1p3YGXAvcJX+AcBQdPMSLGTQ7WCd6yKlweioT9+X6jEC38NJ1
4J5kjJbl8jY2n/x3Tw7Z0d7MERkG/g4/VGvt8HJ4FPOW82LwomtTNt7jt10U2yjXdt61n3cuyCHJ
ovzw2Y5TRqSyWkPzF/BtgFUd2DG50apF6Le4tUhp0uVp0oA1Rsy6AeJdGwOLTAM5GEABhfvIZP2C
OLmlTvxp6yRq04eCrt0t/HPS+xSpVSSemWvZX762uJD18qa8BXPII/2veMvMKeOQsHabd7+xEDHA
GSnTrc1/STyDs+iNGIPa+A==
-----END PRIVATE KEY-----';
                    PDF::setSignature($file, $pk, $clave, '', 2, $info);
                    //PDF::setSignature($certificado['cert'], $certificado['pkey'], $clave, '', 2, $info);
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
                    
                    $ret["nerror"]=0;
                    $ret["msgerror"]='Se ha generado correctamente';
       
                /*}else{
                    $ret["nerror"]=1;
                    $ret["msgerror"]='No se puede leer certificados';
                }*/
             }catch(Exception $e){ 
                 //echo $e->getMessage()."<br/>";
                 $ret["nerror"]=1;
                 $ret["msgerror"]=$e->getFile().' '. $e->getLine().' Msg'.$e->getMessage();
             }
       }else{
           $ret["nerror"]=1;
           $ret["msgerror"]='No existe certificado digital';
       }
       return $ret;
    }
    
    public function listaPrevia(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code       = $jResponse["code"];
        $valida     = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];
            try{
                $id_depto = $request->query('id_depto');
                $id_entidad = $request->query('id_entidad');
                $id_anho = $request->query('id_anho');
                $id_mes = $request->query('id_mes');
                $data = PaymentsData::listaprevia($id_entidad,$id_anho,$id_mes,$id_depto);
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
    
    public function listaPreviaWin(Request $request){
        
        try{
            $id_depto = $request->id_depto;
            $id_entidad = $request->id_entidad;
            $id_anho = $request->id_anho;
            $id_mes = $request->id_mes;
            $id_persona = $request->id_persona;
            $data = PaymentsData::listaprevia($id_entidad,$id_anho,$id_mes,$id_depto,$id_persona);
            if (count($data)>0) {  
                $jResponse = [
                 'nerror' => 0,
                 'mensaje' => 'ok',
                 'data' =>  ['items' => $data]
                ];

            }else{
                $jResponse = [
                 'nerror' => 1,
                 'mensaje' => 'ok',
                 'data' =>  []
                ];
            }
        }catch(Exception $e){                    
            $jResponse = [
                 'nerror' => 1,
                 'mensaje' => "ORA-".$e->getMessage(),
                 'data' =>  []
                ];
        }
                
        return response()->json($jResponse);
                
    }
    public function listaprocesar(Request $request){
        
        try{
            $id_depto = $request->id_depto;
            $id_entidad = $request->id_entidad;
            $id_anho = $request->id_anho;
            $id_mes = $request->id_mes;
            $id_persona = $request->id_persona;
            $data = PaymentsData::listaprocesar($id_entidad,$id_anho,$id_mes,$id_depto,$id_persona);
            if (count($data)>0) {  
                $jResponse = [
                 'nerror' => 0,
                 'mensaje' => 'ok',
                 'data' =>  ['items' => $data]
                ];

            }else{
                $jResponse = [
                 'nerror' => 1,
                 'mensaje' => 'ok',
                 'data' =>  []
                ];
            }
        }catch(Exception $e){                    
            $jResponse = [
                 'nerror' => 1,
                 'mensaje' => "ORA-".$e->getMessage(),
                 'data' =>  []
                ];
        }
                
        return response()->json($jResponse);
                
    }
    
    public function generarProceso(Request $request){

        $id_gestion = 0;
        $carpeta ='';
        $archivo='';
        try{
            $id_entidad=$request->id_entidad;   
            $id_anho=$request->id_anho;
            $id_mes=$request->id_mes;
            $id_persona=$request->id_persona;
            $id_contrato=$request->id_contrato;
            $id_depto=$request->id_depto;
            $nomarchivo=$request->nomarchivo;
            $clave = $request->clave;
            $id_log = $request->items;
            //$doc = $request->doc;
            
            $file = $request->file('file');

                       
            //$ext    = $file->getClientOriginalExtension();
                    
            $id_proceso  = 1;
            
            //$archivo = base64_decode($doc);

            $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

            $mes = $dmeses[$id_mes];

            
            $carpeta      =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes;
            
            $archivo = $nomarchivo.'.pdf';

            PaymentsData::saveFile($file, $carpeta, $archivo);
            

            $id_gestion= PaymentsData::addPaymentTicket($id_entidad,$id_anho,$id_mes,$id_persona,$id_contrato,$id_proceso,$id_depto,$clave,$archivo);
            $jResponse = [
                    'nerror' => 0,
                    'mensaje' => 'Se ha generado correctamente',
                    'data' =>  []
            ];
        }catch(Exception $e){                    
            $jResponse = [
                 'nerror' => 1,
                 'mensaje' => "ORA-".$e->getMessage(),
                 'data' =>  []
                ];
        }
        $nerror = $jResponse['nerror'];
        $error =  substr($jResponse['mensaje'],0,1999);
        
        $tamano='';
        if($jResponse['nerror']==0){
            $tamano= $file->getClientSize() . ' bytes'; //filesize($carpeta.'/'.$archivo) . ' bytes';
        }
        
               
        $id = SignatureData::logfirmaboleta(1,$id_log,0,$id_gestion,'','','','',0,$error,$nerror,$archivo,$tamano,$carpeta,'');
        
        return response()->json($jResponse);
    }
    /*
    public function generarProceso(Request $request){

        $id_gestion = 0;
        $carpeta ='';
        $archivo='';
        try{
            $id_entidad=$request->id_entidad;   
            $id_anho=$request->id_anho;
            $id_mes=$request->id_mes;
            $id_persona=$request->id_persona;
            $id_contrato=$request->id_contrato;
            $id_depto=$request->id_depto;
            $nomarchivo=$request->nomarchivo;
            $clave = $request->clave;
            $id_log = $request->items;
            //$doc = $request->doc;
            
            $file = $request->file('file');
            
            //$ext    = $file->getClientOriginalExtension();
                    
            $id_proceso  = 1;
            
            //$archivo = base64_decode($doc);

            
            $retdir      =  PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes);
            
            if($retdir["nerror"]==0){
                      
                $carpeta     = $retdir["directorio"];
                $archivo = $nomarchivo.'.pdf';


                $file->move($carpeta,$archivo);
                

                $id_gestion= PaymentsData::addPaymentTicket($id_entidad,$id_anho,$id_mes,$id_persona,$id_contrato,$id_proceso,$id_depto,$clave,$archivo);
                $jResponse = [
                     'nerror' => 0,
                     'mensaje' => 'Se ha generado correctamente',
                     'data' =>  []
                ];
            }else{
                $jResponse = [
                 'nerror' => 1,
                 'mensaje' => $retdir["mensaje"],
                 'data' =>  []
                ];
            }
        }catch(Exception $e){                    
            $jResponse = [
                 'nerror' => 1,
                 'mensaje' => "ORA-".$e->getMessage(),
                 'data' =>  []
                ];
        }
        $nerror = $jResponse['nerror'];
        $error =  substr($jResponse['mensaje'],0,1999);
        
        $tamano='';
        if($jResponse['nerror']==0){
            $tamano=filesize($carpeta.'/'.$archivo) . ' bytes';
        }
        
               
        $id = SignatureData::logfirmaboleta(1,$id_log,0,$id_gestion,'','','','',0,$error,$nerror,$archivo,$tamano,$carpeta,'');
        
        return response()->json($jResponse);
    }*/
    public function boletaFirmadoPDF(Request $request){
        //public function downloadBoletaPDF($clave){   
           try{
               $clave = $request->p;
               //$clave=$request->p;
               $data = PaymentsData::showBoletaPDF($clave);
   
              
   
               $archivo="";
               $id_entidad=0;
               $id_anho=0;
               $id_mes=0;
               foreach($data as $row){
                   $archivo=$row->archivo;
                   $id_entidad=$row->id_entidad;
                   $id_anho=$row->id_anho;
                   $id_mes=$row->id_mes;
               }



                   if($archivo!=""){
                        $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

                        $mes = $dmeses[$id_mes];
                       $carpeta      =  'boletapago/'.$id_entidad.'/'.$id_anho.'/'.$mes;
                       $file  = $carpeta. '/' . $archivo; 

                       $ret = PaymentsData::getUrlByName($file);
                       if($ret['nerror']==0) {
                           $url  = $ret['data'];

                            $getFile = file_get_contents($url);

                            $doc  = base64_encode($getFile);

                            $jResponse = [
                                'nerror' => 0,
                                'mensaje' => "OK",
                                'data' => $doc
                                ];


                       }else{

                           $jResponse = [
                            'nerror' => 1,
                            'mensaje' => $ret['message'],
                            'data' => ''
                            ];
                       }

                   }else{
                       $jResponse = [
                           'nerror' => 1,
                           'mensaje' => "No hay data",
                           'data' => ''
                           ];
                   }
   
           }catch(Exception $e){ 
               $jResponse = [
                           'nerror' => 1,
                           'mensaje' => $e->getMessage(),
                           'data' => ''
                           ];
           }
           
           return response()->json($jResponse);
           
       }
     /*  
    public function boletaFirmadoPDF(Request $request){
     //public function downloadBoletaPDF($clave){   
        try{
            $clave = $request->p;
            //$clave=$request->p;
            $data = PaymentsData::showBoletaPDF($clave);

           

            $archivo="";
            $id_entidad=0;
            $id_anho=0;
            $id_mes=0;
            foreach($data as $row){
                $archivo=$row->archivo;
                $id_entidad=$row->id_entidad;
                $id_anho=$row->id_anho;
                $id_mes=$row->id_mes;
            }

            $retdir= PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes);
            if($retdir["nerror"]==0){

                if($archivo!=""){
                    
                    $file  = $retdir["directorio"]. '/' . $archivo; 
                    
                    $file =file_get_contents($file);

                    $doc  = base64_encode($file);
                    
                    $jResponse = [
                        'nerror' => 0,
                        'mensaje' => "OK",
                        'data' => $doc
                        ];

                }else{
                    $jResponse = [
                        'nerror' => 1,
                        'mensaje' => "No hay data",
                        'data' => ''
                        ];
                }
            }else{
                $jResponse = [
                        'nerror' => 1,
                        'mensaje' => "No hay data",
                        'data' => ''
                        ];
            }
        }catch(Exception $e){ 
            $jResponse = [
                        'nerror' => 1,
                        'mensaje' => $e->getMessage(),
                        'data' => ''
                        ];
        }
        
        return response()->json($jResponse);
        
    }*/
    public function logfirma(Request $request){
        $jResponse = [
            'nerror' => 1,
            'mensaje' => 'ERROR',
            'data' =>[]
        ];

        try{
            $id_persona = $request->id_persona;
            $id_anho = $request->id_anho;
            $id_mes = $request->id_mes;
            
            $datalog = SignatureData::logfirma($id_persona,$id_anho,$id_mes);

            $jResponse = [
                'nerror' => 0,
                'mensaje' => 'ok',
                'data' =>  ['items'=>$datalog]
            ];
            
        }catch(Exception $e){ 
            $jResponse = [
                        'nerror' => 1,
                        'mensaje' => $e->getMessage(),
                        'data' => ''
                        ];
        }

        return response()->json($jResponse);
    }
    public function actualizarArchivo(Request $request){
        $jResponse = [
            'nerror' => 1,
            'mensaje' => 'ERROR',
            'data' =>[]
        ];

        try{
            $id_entidad = $request->id_entidad;
            $id_depto = $request->id_depto;
            $id_persona = $request->id_persona;
            $id_anho = $request->id_anho;
            $id_mes = $request->id_mes;
            PaymentsData::actualizarArchivo($id_entidad, $id_depto, $id_anho, $id_mes, $id_persona);

            $jResponse = [
                'nerror' => 0,
                'mensaje' => 'ok',
                'data' =>  []
            ];

        }catch(Exception $e){ 
            $jResponse = [
                        'nerror' => 1,
                        'mensaje' => 'File: '.$e->getFile().' Linea: '.$e->getLine().' mensaje: '.$e->getMessage(),
                        'data' => ''
                        ];
        }

        return response()->json($jResponse);
    }
    public function showDirectory(Request $request){
        $jResponse = [
            'nerror' => 1,
            'mensaje' => 'ERROR',
            'data' =>[]
        ];

        try{
            $id_entidad = $request->id_entidad;
            $id_anho = $request->id_anho;
            $id_mes = $request->id_mes;
            $html = PaymentsData::showDirectory($id_entidad, $id_anho,$id_mes);

            $jResponse = [
                'nerror' => 0,
                'mensaje' => 'ok',
                'data' =>  $html
            ];

        }catch(Exception $e){ 
            $jResponse = [
                        'nerror' => 1,
                        'mensaje' => 'File: '.$e->getFile().' Linea: '.$e->getLine().' mensaje: '.$e->getMessage(),
                        'data' => ''
                        ];
        }

        return response()->json($jResponse);
    }
    public function getViewPdf(Request $request){
        try{
            $items  = $request->items;
            $id_persona  = $request->id_persona;
            $id_anho  = $request->id_anho;
            $id_mes  = $request->id_mes;
            $id_user  = $request->id_user;
            $nomfirma = $request->firma;
            $ret = ServiceApiData::getViewPdf($request);
            if($ret['nerror'] == 0){
                $error  = '';
                $origen =$ret['mensaje'];
                $id_log = SignatureData::logfirmaboleta(0,'',$items,0,$id_persona,$id_anho,$id_mes,$origen,$id_user,$error,1,'','','',$nomfirma);
                $jResponse=[
                    'nerror' => 0,
                    'mensaje' => $ret['mensaje'],
                    'clave' => $ret['clave'],
                    'nomarchivo' => $ret['nomarchivo'],
                    'data'=>$ret['data'],
                    'items'=>$id_log,
                    'logo'=>$ret['logo'],
                    'razon'=>$ret['razon'],
                    'ubicacion'=>$ret['ubicacion'],
                    'url'=>$ret['url'],
                ];
            }else{
                $error  = $ret['mensaje'];
                $origen ='';
                $id_log = SignatureData::logfirmaboleta(0,'',$items,0,$id_persona,$id_anho,$id_mes,$origen,$id_user,$error,1,'','','',$nomfirma);
                $jResponse=[
                    'nerror' => 1,
                    'mensaje' => $ret['mensaje'],
                    'clave' => '',
                    'nomarchivo' => '',
                    'data'=> '',
                    'items'=> $id_log,
                    'logo'=> '',
                    'razon'=> '',
                    'ubicacion'=> '',
                    'url'=>''
                ];
            }
        }catch(Exception $e){ 
            $error  = substr($e->getMessage(),0,1999);
            $origen ='';
            $id_log = SignatureData::logfirmaboleta(0,'',$items,0,$id_persona,$id_anho,$id_mes,$origen,$id_user,$error,1,'','','',$nomfirma);
            $jResponse = [
                'nerror' => 1,
                'mensaje' => 'File: '.$e->getFile().' Linea: '.$e->getLine().' mensaje: '.$e->getMessage(),
                'clave' => '',
                'nomarchivo' => '',
                'data'=> '',
                'items'=> $id_log,
                'logo'=> '',
                'razon'=> '',
                'ubicacion'=> '',
                'url'=>''
            ];
            
        }
        return response()->json($jResponse);
    }
    public function getpreviapdf(Request $request){
        try{
            $ret = ServiceApiData::getViewPdf($request);
            if($ret['nerror'] == 0){
                
                $jResponse=[
                    'nerror' => 0,
                    'mensaje' => $ret['mensaje'],
                    'data'=>$ret['data'],
                ];
            }else{
                
                $jResponse=[
                    'nerror' => 1,
                    'mensaje' => $ret['mensaje'],
                    'data'=> '',
 
                ];
            }
        }catch(Exception $e){ 
            
            $jResponse = [
                'nerror' => 1,
                'mensaje' => 'File: '.$e->getFile().' Linea: '.$e->getLine().' mensaje: '.$e->getMessage(),
                'data'=> '',

            ];
            
        }
        return response()->json($jResponse);
    }
    public function getFirmaLocal(Request $request){
        try{
            $id_entidad = $request->id_entidad;
            $id_depto = $request->id_depto;
            $ret = ServiceApiData::getFirmaLocal($id_entidad,$id_depto);
            
        }catch(Exception $e){ 
            
            $ret  = [
                'nerror' => 1,
                'mensaje' => 'File: '.$e->getFile().' Linea: '.$e->getLine().' mensaje: '.$e->getMessage(),
    

            ];
            
        }
        return response()->json($ret);
    }
    public function getUrls(Request $request){
        $jResponse = [
            'nerror' => 1,
            'mensaje' => 'ERROR',
            'data' =>[]
        ];

        try{

            $id_user = $request->id_user;
            $data =  ServiceApiData::getUrls($id_user);

            if(count($data)>0) {
                $jResponse = [
                    'nerror' => 0,
                    'mensaje' => 'ok',
                'data' =>  ['items'=>$data]
                ];
            }
        }catch(Exception $e){ 
            
            $ret  = [
                'nerror' => 1,
                'mensaje' => 'File: '.$e->getFile().' Linea: '.$e->getLine().' mensaje: '.$e->getMessage(),
    

            ];
            
        }
        return response()->json($jResponse);
    }
    
}


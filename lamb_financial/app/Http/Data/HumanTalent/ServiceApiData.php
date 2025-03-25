<?php

namespace App\Http\Data\HumanTalent;
use App\Http\Data\HumanTalent\PaymentsData;
use App\Http\Data\APSData;
use Illuminate\Support\Facades\DB;
use App\qrcode;
use PDF;
use DOMPDF;
class ServiceApiData 
{
    public static function getViewPdf($request) {

        $id_entidad     = $request->id_entidad;
        $id_anho        = $request->id_anho;
        $id_mes         = $request->id_mes;
        $id_persona     = $request->id_persona;
        $id_depto       = $request->id_depto;
        $items          = $request->items;
        $id_user         = $request->id_user;
        $nomfirma       = $request->firma;

        $logo="";
        $razon="";
        $ubicacion="";
        $urlPdf = "";



            $objEnt = DB::table('CONTA_ENTIDAD')
                ->where('id_entidad',$id_entidad)
                ->first();
            $id_persona_enti = $objEnt->id_persona;

            $objEmp = DB::table('MOISES.VW_PERSONA_JURIDICA')
                ->whereRaw("ID_RUC IN (
                    SELECT cem.ID_RUC 
                    FROM CONTA_ENTIDAD ce, CONTA_EMPRESA cem
                    WHERE ce.ID_EMPRESA = cem.ID_EMPRESA 
                    AND ce.ID_PERSONA = $id_persona_enti
                )")
                ->select('ID_PERSONA','ID_RUC','NOM_COMERCIAL', 'NOMBRE')
                ->first();

            $objDep = DB::table('APS_PLANILLA as A')
            ->join('MOISES.PERSONA as B','B.ID_PERSONA','=','A.ID_PERSONA')
                ->where('A.ID_PERSONA',$id_persona)
                ->where('A.ID_ENTIDAD',$id_entidad)
                ->where('A.ID_ANHO',$id_anho)
                ->where('A.ID_MES',$id_mes)
                ->select(DB::raw("substr(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE"))
                ->first();

            if($objDep){
                $id_depto    = $objDep->id_depto_padre;
            }
            if(empty($id_depto)){
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' =>'No existe información de boleta para el periodo '.$id_anho.'-'.$id_mes.' para el personal('.$id_persona.')',
                ];
                return $jResponse;
            }
            $objCert = DB::table('aps_certificado')
                ->where('id_entidad',$id_entidad)
                ->where('id_depto_padre',$id_depto)
                ->select('id_certificado','firma_ubicacion','firma_razon','logo_boleta',
                'boleta_title_background','boleta_ds_remuneraciones','logo_firma'
                )
                ->first();
            if(empty($objCert)){
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' =>'No hay datos de firma',

                ];
                return $jResponse;
            }
            $logo= $objCert->logo_boleta;
            $razon=$objCert->firma_razon;
            $ubicacion=$objCert->firma_ubicacion;

            $query = DB::table("aps_planilla as a");
            $query->join("moises.persona as b", 'b.ID_PERSONA', '=', 'A.ID_PERSONA');
            $query->select(
                'A.ID_PERSONA', 'a.id_entidad','B.PATERNO', 'B.MATERNO', 'B.NOMBRE', 'A.ID_DEPTO', 'A.ID_CONTRATO',
                DB::raw("substr(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE")
            );
            $query->groupBy('A.ID_PERSONA', 'a.id_entidad','B.PATERNO', 'B.MATERNO', 'B.NOMBRE', 'A.ID_DEPTO', 'A.ID_CONTRATO');
            $query->where("A.ID_ENTIDAD", $id_entidad);
            $query->where("A.ID_ANHO", $id_anho);
            $query->where("A.ID_MES", $id_mes);
            $query->where("A.ID_PERSONA", $id_persona);
            $entPerson = $query->first();

            if(empty($entPerson)){
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' =>'No hay data para generar',

                ];
                return $jResponse;
            }

            $employee     =  self::empleados($id_entidad, $entPerson->id_persona, $id_anho, $id_mes, $entPerson->id_contrato);


            //$ret = self::generarPlantillaBoleta($objEmp,$employee,$id_anho,$id_mes,$objCert->id_certificado);
            $cert= self::datosPrint($objCert->id_certificado);

            

            if( !empty($cert)){
                $neto = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'N') ;

                $qrcodigo=$objEmp->id_ruc.$id_anho.$id_mes.$employee->num_documento.$employee->nom_persona.$neto;

                $nomarchivo = $employee->id_depto_padre.'-'.$employee->id_contrato.'-'.$employee->num_documento.'-'.$id_anho.'-'.$id_mes;

                $p = password_hash($qrcodigo, PASSWORD_DEFAULT); 
                $urlBase= url('humantalent/payments-tickets-worker-download');
                $urlPdf = $urlBase."?p=".$p;

                $dataIng = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'I');
                $totalIng = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'I') ;

                $dataRet = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'R');
                $totalRet = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'R') ;

                $dataDes = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'D');
                $totalDes = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'D') ;

                $dataApo = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'A');
                $totalApo = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'A') ;

                $pdf = DOMPDF::loadView('pdf.human-talent.boleta', [
                    'objEmp' => $objEmp,
                    'employee' => $employee,
                    'objCert' => $cert,
                    'dataIng' => $dataIng,
                    'totalIng' => $totalIng,
                    'dataRet' => $dataRet,
                    'totalRet' => $totalRet,
                    'dataDes' => $dataDes,
                    'totalDes' => $totalDes,
                    'dataApo' => $dataApo,
                    'totalApo' => $totalApo,
                    'neto' => $neto,
                ])->setPaper('a4', 'portrait');

                $doc =  base64_encode($pdf->stream('boleta.pdf'));

                //$pdf = App::make('dompdf.wrapper');
                /*$carpeta = 'boletas';
                $ruta = public_path() .'/'.$carpeta.'/boletafimar.pdf';
                PDF::loadView('pdf.human-talent.boleta',['html'=>$ret["html"]])->save($ruta);
                $archivo =file_get_contents($ruta);
                $doc  = base64_encode($archivo);*/
                /*
                PDF::SetCreator('DTI');
                PDF::SetAuthor('DTI-UPeU');
                PDF::SetTitle('eBoletas UPeU');
                PDF::AddPage();

                PDF::writeHTML($ret["html"],true,0,true,0);
                $carpeta = 'boletas';
                $ruta = public_path() .'/'.$carpeta.'/boletafimar.pdf';
                PDF::Output($ruta,'F');
                $archivo =file_get_contents($ruta);
                $doc  = base64_encode($archivo);*/
                
                $jResponse = [
                    'nerror' => 0,
                    'mensaje' => "OK",
                    'clave' => $p,
                    'nomarchivo' => $nomarchivo,
                    'data' => $doc
                    ];

            }else{
                $jResponse = [
                    'nerror' => 1,
                    'mensaje' => "No se ha generado",
                    ];
                return $jResponse;
            }

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
            'logo'=>$logo_firma,
            'razon'=>$razon,
            'ubicacion'=>$ubicacion,
            'url'=>$urlPdf
        ];
        return $respuesta;

    }
    private static function generarPlantillaBoleta($objEmp,$employee,$id_anho,$id_mes,$id_certificado){
    //public static function generarPlantillaBoleta($data,$id_anho,$id_mes,$id_certificado,$id_depto){

        $qrcodigo="";
        $html='';
        $dataFirma=PaymentsData::showCertificate($id_certificado);
        $firma="";
        $p="";
        $representante="";
        $representantedoc="";
        $nombre_general="";
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
        
        $html.='<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
            $html.='<tr>';
                $html.='<td coslpan="2" style="background-color: '.$boleta_title_background.'; text-align: center;font-size: 8px;color: #FFFFFF;">BOLETA DE PAGO DE REMUNERACIONES</td>';
            $html.='</tr>';
            $html.='<tr>';
                $html.='<td><br/></td>';
                $html.='<td><br/></td>';
            $html.='</tr>';
            $html.='<tr>';
            $ruta=asset($logo_boleta);
            $html.='<td style="width:15%; " rowspan="2"><br/><img src="'.$ruta.'" height="51"></td>';
                $nombre_general=$objEmp->nombre;
                $html.='<td style="width:70%; text-align: center; font-size: 11px; font-family: "Times New Roman", Georgia, Serif;">'.$objEmp->nombre.'</td>';
                $html.='<td style="width:15%; " rowspan="2">&nbsp;</td>';
            $html.='</tr>';
        $html.='</table>';
        $html.='<table  style="width:100%; font-family: "Times New Roman", Georgia, Serif;">';
            $html.='<tr>';
                $html.='<td><br/></td>';
            $html.='</tr>';
            $html.='<tr>';
                $html.='<td  style="text-align: center;font-size: 8px;">Expresado en Soles<br/>'.$boleta_ds_remuneraciones.'<br/>RUC: '.$objEmp->id_ruc .'</td>';
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
                        $html.='<td>'.$employee->nom_persona.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Cargo:</strong></td>';
                        $html.='<td>'.$employee->nom_cargo.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Codigo ESSALUD:</strong></td>';
                        $html.='<td>'.$employee->essalud.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Codigo CUSS:</strong></td>';
                        $html.='<td>'.$employee->cuss.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Fecha de Nacimiento:</strong></td>';
                        $html.='<td>'.$employee->fec_nacimiento.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Número de DNI:</strong></td>';
                        $html.='<td>'.$employee->num_documento.'</td>';
                    $html.='</tr>';

                $html.='</table>';
            $html.='</td>';
            $html.='<td style="width:50%;">';
                $html.='<table style="font-size: 7px; font-family: "Times New Roman", Georgia, Serif;">';

                    $html.='<tr>';
                        $html.='<td><strong>Mes de Pago:</strong></td>';
                        $html.='<td>'.$employee->mes.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Fecha de Ingreso:</strong></td>';
                        $html.='<td>'.$employee->fec_inicio.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Fecha de Cese:</strong></td>';
                        $html.='<td>'.$employee->fec_termino.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>Dias / Horas Trabajados:</strong></td>';
                        $html.='<td>'.$employee->dh.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong> Vacaciones:</strong></td>';
                        $html.='<td>'.$employee->vacaciones.'</td>';
                    $html.='</tr>';
                    $html.='<tr>';
                        $html.='<td><strong>AFP:</strong></td>';
                        $html.='<td>'.$employee->afp.'</td>';
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
                $data = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'I');
                foreach($data as $row){

                    $html.='<tr>';
                        $html.='<td style="width:70%;">'.$row->nombre.'</td>';
                        $html.='<td style="width:30%;text-align: right;">'.number_format($row->importe,2,",",".").'</td>';
                    $html.='</tr>';

                }
                $total = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'I') ;
                if($total> 0){

                    $html.='<tr>';
                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                        $html.='<td style="width:30%;text-align: right;"><strong>'.number_format($total,2,",",".").'</strong></td>';
                    $html.='</tr>';

                }
                $html.='</table>';
            $html.='</td>';
            $html.='<td style="border: 1px solid '.$boleta_title_background.';">';
                $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                    
                $data = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'R');
                foreach($data as $row){

                    $html.='<tr>';
                        $html.='<td style="width:70%;">'.$row->nombre.'</td>';
                        $html.='<td style="width:30%;text-align: right;">'.number_format($row->importe,2,",",".").'</td>';
                    $html.='</tr>';

                }
                $total = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'R') ;
                if($total> 0){

                    $html.='<tr>';
                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                        $html.='<td style="width:30%;text-align: right;"><strong>'.number_format($total,2,",",".").'</strong></td>';
                    $html.='</tr>';

                }
                            
                $html.='</table>';
            $html.='</td>';
            $html.='<td style="border: 1px solid '.$boleta_title_background.';" rowspan="2">';
                $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                $data = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'D');
                foreach($data as $row){

                    $html.='<tr>';
                        $html.='<td style="width:70%;">'.$row->nombre.'</td>';
                        $html.='<td style="width:30%;text-align: right;">'.number_format($row->importe,2,",",".").'</td>';
                    $html.='</tr>';

                }
                $total = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'D') ;
                if($total> 0){

                    $html.='<tr>';
                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                        $html.='<td style="width:30%;text-align: right;"><strong>'.number_format($total,2,",",".").'</strong></td>';
                    $html.='</tr>';

                }
                $html.='</table>';
            $html.='</td>';
        $html.='</tr>';

        $html.='<tr>';
            
            $html.='<td style="border: 1px solid '.$boleta_title_background.';">';
                $html.='<table style="width:100%;font-size: 7px;font-family: "Times New Roman", Georgia, Serif;">';
                    
                    $html.='<tr>';
                        $html.='<td colspan="2"><strong>APORTES DEL EMPLEADOR</strong></td>';
                    $html.='</tr>';
                    $data = self::detalle($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'A');
                foreach($data as $row){

                    $html.='<tr>';
                        $html.='<td style="width:70%;">'.$row->nombre.'</td>';
                        $html.='<td style="width:30%;text-align: right;">'.number_format($row->importe,2,",",".").'</td>';
                    $html.='</tr>';

                }
                $total = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'A') ;
                if($total> 0){

                    $html.='<tr>';
                        $html.='<td style="width:70%;"><strong>TOTAL</strong></td>';
                        $html.='<td style="width:30%;text-align: right;"><strong>'.number_format($total,2,",",".").'</strong></td>';
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
                    $total = self::detalleTotal($employee->id_entidad,$employee->id_persona,$id_anho,$id_mes, $employee->id_contrato,'N') ;
                    if($total> 0){
                    $html.='<tr>';
                            $html.='<td><strong>NETO A PAGAR</strong></td>';
                            $html.='<td><strong>'.number_format($total,2,",",".").'</strong></td>';
                        $html.='</tr>';
                        
                        $neto= $total;
                    }
                $html.='</table>';
                $html.='</td>';
                $html.='<td  style="width:50%; text-align: right;">'.$employee->mes_name.'</td>';
            $html.='</tr>';
        $html.='</table>';

        $qrcodigo=$objEmp->id_ruc.$id_anho.$id_mes.$employee->num_documento.$employee->nom_persona.$neto;

        $nomarchivo = $employee->id_depto_padre.'-'.$employee->id_contrato.'-'.$employee->num_documento.'-'.$id_anho.'-'.$id_mes;
        
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
            

                $html.='</td>';
                
                $html.='<td  style="width:33%;font-size: 7px;text-align: center;">';
                    $html.='--------------------------------------------<br/>';
                    $html.='TRABAJADOR<br/>';
                    $html.=$employee->nom_persona.'<br/>';
                    $html.='DNI: '.$employee->num_documento;
                    
                $html.='</td>';
            $html.='</tr>';

        $html.='</table>';
        
        $html.='<table style="width:100%;font-size: 7px;">';
        $html.='<tr><td><br/></td></tr>';
        $html.='</table>';

        $return=[
             'html'=>$html,
             'p'=>$p,
             'nombre_entidad'=>$nombre_general,
             'nomarchivo'=>$nomarchivo
         ];
        return $return;
 
     }
     private static function empleados($entity, $id_persona, $anho, $mes, $id_contrato){

        $resultado = DB::table('APS_PLANILLA as A')
        ->select('C.NOM_PERSONA', 'A.NOM_CARGO',
            DB::raw("(SELECT COALESCE(max(NUM_DOCUMENTO ),'') FROM moises.PERSONA_DOCUMENTO pd 
                                WHERE ID_PERSONA = $id_persona
                                AND ID_TIPODOCUMENTO = 97) as ESSALUD"),
            DB::raw("(SELECT COALESCE(max(NUM_DOCUMENTO ),'') FROM moises.PERSONA_DOCUMENTO pd 
                                WHERE ID_PERSONA = $id_persona
                                AND ID_TIPODOCUMENTO = 98) as CUSS"),
            DB::raw("TO_CHAR(C.FEC_NACIMIENTO,'DD/MM/YYYY') AS FEC_NACIMIENTO"),
            'C.NUM_DOCUMENTO',
            DB::raw("FC_MES_NAME(LPAD($mes,2,0))||' del '||$anho AS MES"),
            DB::raw("TO_CHAR(B.FEC_INICIO,'DD/MM/YYYY') AS FEC_INICIO"),
            DB::raw("TO_CHAR(B.FEC_TERMINO,'DD/MM/YYYY') AS FEC_TERMINO"),
            DB::raw("A.NUM_DIAS||' / '||A.NUM_HORAS DH"),
            DB::raw("TO_CHAR(A.INI_VACACIONES,'DD/MM/YYYY')||' al '||TO_CHAR(A.FIN_VACACIONES,'DD/MM/YYYY') VACACIONES"),
            DB::raw("(SELECT X.NOMBRE FROM APS_SISTEMA_PENSION X WHERE X.ID_SISTEMAPENSION = A.ID_SISTEMAPENSION) AFP"),
            DB::raw("TO_CHAR((LAST_DAY(TO_DATE('$mes/$anho','MM/YYYY'))),'DD')||' '||FC_MES_NAME(LPAD($mes,2,0))||' del '||$anho AS MES_NAME"),
            'A.ID_ENTIDAD', 'A.ID_ANHO', 'A.ID_MES', 'A.ID_PERSONA', 'A.ID_CONTRATO', DB::raw("substr(a.id_depto,0,1) as ID_DEPTO_PADRE"))
        ->join('APS_EMPLEADO as B', 'A.ID_ENTIDAD', '=',DB::raw("B.ID_ENTIDAD and A.ID_PERSONA = B.ID_PERSONA and A.ID_CONTRATO = B.ID_CONTRATO"))
        ->join('MOISES.VW_PERSONA_NATURAL_LIGHT as C', 'A.ID_PERSONA', '=', 'C.ID_PERSONA')
        ->where('A.ID_ENTIDAD', $entity)
        ->where('A.ID_PERSONA', $id_persona)
        ->where('A.ID_CONTRATO', $id_contrato)
        ->where('A.ID_ANHO', $anho)
        ->where('A.ID_MES', $mes)
        ->first();
        return $resultado;
     }
     private static function detalle($id_entidad, $id_persona,$id_anho,$id_mes,$id_contrato,$tipo){
        $q = DB::table('APS_PLANILLA_DETALLE as A');
        $q->select(
            'A.ID_CONCEPTOAPS', 
            'B.NOMBRE', 
            'A.COS_REFERENCIA1', 
            'A.COS_REFERENCIA2', 
            'A.COS_REFERENCIA3', 
            'A.COS_VALOR as importe'
            );
        $q->join('APS_CONCEPTO_PLANILLA as B', 'A.ID_CONCEPTOAPS', '=', 'B.ID_CONCEPTOAPS');
        $q->where('A.ID_ENTIDAD', $id_entidad);
        $q->where('A.ID_PERSONA', $id_persona);
        $q->where('A.ID_ANHO', $id_anho);
        $q->where('A.ID_MES', $id_mes);
        $q->where('A.ID_CONTRATO', $id_contrato);
        if($tipo == 'I'){
            $q->where('A.ID_TIPOPLANILLA', 98626);
        }
        if($tipo == 'D'){
            $q->whereRaw("(B.TIPO='D' or B.TIPO1='D')");
        }else{
            $q->where('B.TIPO', $tipo);
        }
        $q->orderBy('A.ID_CONCEPTOAPS');
        $data = $q->get();
        return $data;
     }
     private static function detalleTotal($id_entidad, $id_persona,$id_anho,$id_mes,$id_contrato,$tipo){
        $q = DB::table('APS_PLANILLA_DETALLE as A');
        $q->select(
            DB::raw("coalesce(SUM(A.COS_VALOR),0) as total"));
        $q->join('APS_CONCEPTO_PLANILLA as B', 'A.ID_CONCEPTOAPS', '=', 'B.ID_CONCEPTOAPS');
        $q->where('A.ID_ENTIDAD', $id_entidad);
        $q->where('A.ID_PERSONA', $id_persona);
        $q->where('A.ID_ANHO', $id_anho);
        $q->where('A.ID_MES', $id_mes);
        $q->where('A.ID_CONTRATO', $id_contrato);
        if($tipo == 'I'){
            $q->where('A.ID_TIPOPLANILLA', 98626);
        }
        if($tipo == 'D'){
            $q->whereRaw("(B.TIPO='D' or B.TIPO1='D')");
        }else{
            $q->where('B.TIPO', $tipo);
        }

        $obj = $q->first();
        $total=0;
        if(!empty($obj)){
            $total=$obj->total;
        }
        return $total;
     }
     public static function datosPrint($id_certificado)
    {
        $resultado = DB::table('APS_CERTIFICADO as a')
        ->join('moises.persona as p', 'a.id_persona', '=', 'p.id_persona')
        ->join('moises.persona_documento as pd', 'p.id_persona', '=', 'pd.id_persona')
        ->select(
            'a.ID_CERTIFICADO', 
            'a.DESCRIPCION', 
            'a.NOMBRE_ARCHIVO', 
            'a.ARCHIVO', 
            'a.CLAVE', 
            'a.DESDE', 
            'a.HASTA', 
            'a.ID_PERSONA', 
            'a.FIRMA', 
            'a.ESTADO', 
            DB::raw("p.paterno||' '||p.materno||' '||p.nombre as representante"), 'pd.NUM_DOCUMENTO', 'a.UBICACION', 'a.NUMSERIE', 
            DB::raw("case when a.HASTA<current_date then '1' else '0' end as fincert"),
            DB::raw("to_char(a.HASTA,'DD/MM/YYYY') as FHASTA"), 
            'logo_boleta', 
            'boleta_title_background', 
            'boleta_ds_remuneraciones', 
            'logo_firma', 
            'MAIL_DRIVER', 
            'MAIL_HOST', 
            'MAIL_PORT', 
            'MAIL_USERNAME', 
            'MAIL_PASSWORD', 
            'MAIL_ENCRYPTION', 
            'MAIL_FROM_NAME', 
            'MAIL_BODY', 
            'MAIL_FOOTER', 
            'SMS_USERNAME', 
            'SMS_PASSWORD')
        ->where('pd.ID_TIPODOCUMENTO', 1)
        ->where('a.ID_CERTIFICADO', $id_certificado)
        ->first();
        return $resultado;
    }
    public static function getFirmaLocal($id_entidad,$id_depto){
         $objCert = DB::table('aps_certificado')
            ->where('id_entidad',$id_entidad)
            ->where('id_depto_padre',$id_depto)
            ->select('id_certificado','firma_ubicacion','firma_razon','logo_boleta',
            'boleta_title_background','boleta_ds_remuneraciones','logo_firma'
            )
            ->first();
        if(empty($objCert)){
            $jResponse = [
                'nerror' => 1,
                'mensaje' =>'No hay datos de firma',

            ];
            return $jResponse;
        }
        $logo= $objCert->logo_boleta;
        $logo_firma='';
        if($logo){
            $ruta = public_path() .'/'.$logo;
            $archivo =file_get_contents($ruta);
                                    
            $logo_firma  = base64_encode($archivo);
        }
        $jResponse = [
            'nerror' => 0,
            'mensaje' =>'ok',
            'logo' =>$logo_firma,
            'razon' =>$objCert->firma_razon,
            'ubicacion' =>$objCert->firma_ubicacion,

        ];
        return $jResponse;
    }
    public static function getUrls($id_user)
    {

        $resultado = DB::table('api_firma as a')
        ->join('api_firma_user as b', 'a.id_api_firma', '=', 'b.id_api_firma')
        ->select(
            'a.id_api_firma', 
            'a.url_api', 
            'a.descripcion'
            )
        ->where('a.vigencia', 1)
        ->where('b.user_id', $id_user)
        ->orderBy('a.url_api')
        ->get();
        return $resultado;
    }
}
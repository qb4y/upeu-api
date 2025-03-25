<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PayrollData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public static function legalPayroll($request){
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $month_init = $request->query('month_init');
        $month_finally = $request->query('month_finally');
        $state = $request->query('state');
        $search = $request->query('search');
        $orderColumn = $request->query('orderColumn');
        $order = $request->query('order');
        if(!$orderColumn){
            $orderColumn='nombres';
        }
        if(!$order){
            $order='ASC';
        }
        $months='';
        if($month_init != $month_finally){
            for ($x = $month_init; $x <= intval($month_finally); $x++) {
                if($x==$month_finally){
                $months=$months.$x;
                }else{
                    $months=$months.$x.',';
                }
            } 
        }else{
            $months=$month_init;
        }
       
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $whereState='';
        if ($state != '*') {
            $whereState = " WHERE ESTADO = '$state'";
        }
        $results= DB::table(DB::raw("(SELECT * FROM MOISES.VW_PERSONA_NATURAL_LIGHT WHERE (UPPER(NUM_DOCUMENTO) LIKE UPPER('%$search%')
            OR UPPER(PATERNO) LIKE UPPER('%$search%') OR UPPER(MATERNO) like UPPER('%$search%') OR UPPER(NOMBRE) LIKE UPPER('%$search%'))) PN"))
            ->join(DB::raw("(SELECT ESTADO,ID_PERSONA,FEC_ENTIDAD,ID_CONTRATO,ID_ENTIDAD,ID_TIPOCONTRATO,ID_CATEGORIAOCUPACIONAL FROM APS_EMPLEADO $whereState) E"),'E.ID_PERSONA', '=', 'PN.ID_PERSONA')
            ->join(DB::raw("(SELECT ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MES,ID_SISTEMAPENSION,ID_PERSONA,ID_CONTRATO,NOM_CARGO FROM  APS_PLANILLA WHERE ID_ENTIDAD =$id_entidad and ID_ANHO = $id_anho AND ID_MES IN ($months)) PL"), 'PL.ID_PERSONA', DB::raw('E.ID_PERSONA AND PL.ID_ENTIDAD=E.ID_ENTIDAD AND PL.ID_CONTRATO=E.ID_CONTRATO'))
            ->join('TIPO_CONTRATO as TC', 'TC.ID_TIPOCONTRATO', '=', 'E.ID_TIPOCONTRATO')
            ->leftJoin('APS_CATEGORIA_OCUPACIONAL as CO', 'CO.ID_CATEGORIAOCUPACIONAL', '=', 'E.ID_CATEGORIAOCUPACIONAL')
            ->leftJoin('MOISES.PERSONA_DOCUMENTO as CUSPP', 'CUSPP.ID_PERSONA', '=', DB::raw('E.ID_PERSONA AND CUSPP.ID_TIPODOCUMENTO = 98'))
            ->leftJoin('APS_SISTEMA_PENSION as SP', 'SP.ID_SISTEMAPENSION', '=', 'PL.ID_SISTEMAPENSION')
            ->select(
                'PL.ID_ENTIDAD', DB::raw('FC_NAMEENTITY(PL.ID_ENTIDAD) AS ENTIDAD'),  'PL.ID_DEPTO', DB::raw('FC_NAMESDEPTO(PL.ID_ENTIDAD, PL.ID_DEPTO) AS DEPTO'),
                'PN.ID_PERSONA', 'PN.NUM_DOCUMENTO', 'PN.PATERNO', 'PN.MATERNO', 'PN.NOMBRE',
                DB::raw("(CASE  WHEN  E.ESTADO = 'A' THEN 'Activo' ELSE 'Inactivo' END) as ESTADO"),
                'PN.TIPO_SEXO',
                'TC.NOMBRE AS TIPO_CONTRATO', 'CO.NOMBRE AS OBRERO', 'PL.NOM_CARGO', 'E.FEC_ENTIDAD',
                DB::raw("'-' as COD_ESSALUD"),
                'CUSPP.NUM_DOCUMENTO AS NUM_CUSPP','PN.FEC_NACIMIENTO', 'SP.NOMBRE AS AFP', 'E.ID_CONTRATO'
            )->groupBy('PL.ID_ENTIDAD', 'PL.ID_DEPTO', 'PN.ID_PERSONA', 'PN.NUM_DOCUMENTO', 'PN.PATERNO','PN.MATERNO','PN.NOMBRE',
                'E.ESTADO', 'PN.TIPO_SEXO', 'TC.NOMBRE', 'CO.NOMBRE', 'PL.NOM_CARGO', 'E.FEC_ENTIDAD', 'CUSPP.NUM_DOCUMENTO',
                'PN.FEC_NACIMIENTO', 'SP.NOMBRE','E.ID_CONTRATO')
            ->orderBy('PN.PATERNO', $order); 
            if(!is_null($pageSize)){
               $results= $results->paginate($pageSize);
            }
            #print_r($results->toSql());
            foreach($results as $item){
                #Remuneraciones
                $cuentas_bloque1=[];
                $totalBloque1=0;
                #Descuentos
                $cuentas_bloque2=[];
                $totalBloque2=0;
                #Neto de haberes o neto a pagar
                $cuentas_bloque3=[];
                $totalBloque3=0;
                #Aportaciones
                $cuentas_bloque4=[];
                $totalBloque4=0;
                $cuentas=DB::table(
                    DB::raw("(SELECT ID_CONCEPTOAPS,CASE WHEN TIPO IN ('I') THEN 1 WHEN TIPO IN ('R','D','AY') THEN 2 WHEN TIPO IN ('N') THEN 3 WHEN TIPO IN ('A') THEN 4 ELSE 0 END AS TIPO  FROM APS_CONCEPTO_PLANILLA WHERE TIPO IN ('I','R','D','AY','N','A') ORDER BY TIPO) CP"))
                    ->join('APS_CONCEPTO_PLANILLA_CUENTA as CPC', 'CP.ID_CONCEPTOAPS','=','CPC.ID_CONCEPTOAPS')
                    ->join('CONTA_CTA_DENOMINACIONAL as CD', 'CPC.ID_CUENTAAASI',
                    '=',DB::raw("CD.ID_CUENTAAASI AND CPC.ID_TIPOPLAN=CD.ID_TIPOPLAN AND CPC.ID_RESTRICCION=CD.ID_RESTRICCION"))
                    ->leftJoin(
                        DB::raw("(SELECT ID_ENTIDAD,ID_PERSONA,ID_ANHO,ID_MES,ID_CONCEPTOAPS,COS_VALOR FROM APS_PLANILLA_DETALLE WHERE ID_ENTIDAD=$id_entidad AND ID_PERSONA=$item->id_persona AND ID_ANHO=$id_anho AND ID_MES IN (".$months.")) PD"),
                        'PD.ID_CONCEPTOAPS','=','CP.ID_CONCEPTOAPS')
                    ->select(
                        DB::raw("SUBSTR(CD.ID_CUENTAAASI,0,1) AS PARENT"),
                        "CD.ID_CUENTAAASI",
                        "CD.NOMBRE",
                        "CP.TIPO",
                        DB::raw("NVL(SUM(PD.COS_VALOR),0) as MONTO")
                    )->where('ES_DEBITO','=','1')->groupBy('CD.ID_CUENTAAASI', "CD.NOMBRE","CP.TIPO")
                    ->orderByRaw('PARENT DESC,CD.ID_CUENTAAASI ASC')->get();
                foreach($cuentas as $account){
                    $concepts=DB::table(DB::raw("(SELECT NOMBRE,ID_CONCEPTOAPS,CASE WHEN TIPO IN ('I') THEN 1 WHEN TIPO IN ('R','D','AY') THEN 2 WHEN TIPO IN ('N') THEN 3 WHEN TIPO IN ('A') THEN 4 ELSE 0 END AS TIPO FROM APS_CONCEPTO_PLANILLA WHERE TIPO IN ('I','R','D','AY','N','A') ORDER BY TIPO) CP"))
                    ->join(
                        DB::raw("(SELECT ID_CUENTAAASI,ID_CONCEPTOAPS FROM APS_CONCEPTO_PLANILLA_CUENTA WHERE ID_CUENTAAASI=$account->id_cuentaaasi) CPC"),
                        'CPC.ID_CONCEPTOAPS','=', 'CP.ID_CONCEPTOAPS')
                    ->leftJoin(
                        DB::raw("(SELECT ID_ENTIDAD,ID_PERSONA,ID_ANHO,ID_MES,ID_CONCEPTOAPS,COS_VALOR FROM APS_PLANILLA_DETALLE WHERE ID_ENTIDAD=$id_entidad AND ID_PERSONA=$item->id_persona AND ID_ANHO=$id_anho AND ID_MES IN (".$months.")) PD"),
                        'PD.ID_CONCEPTOAPS','=','CPC.ID_CONCEPTOAPS')
                    ->select(
                        "CPC.ID_CONCEPTOAPS",
                        "CP.NOMBRE",
                        DB::raw("NVL(SUM(PD.COS_VALOR),0) as MONTO")
                    )
                    ->groupBy('CPC.ID_CONCEPTOAPS')
                    ->groupBy('CP.NOMBRE')
                    ->orderByRaw('CPC.ID_CONCEPTOAPS ASC')
                    ->get();
                    if($account->tipo==='1'){
                        $account->concepts=$concepts;
                        $cuentas_bloque1[]=$account;
                        $totalBloque1=$totalBloque1+$account->monto;
                    }
                    if($account->tipo==='2'){
                        $account->concepts=$concepts;
                        $cuentas_bloque2[]=$account;
                        $totalBloque2=$totalBloque2+$account->monto;
                    }
                    if($account->tipo==='3'){
                        $account->nombre='Neto a pagar';
                        $account->concepts=$concepts;
                        $cuentas_bloque3[]=$account;
                        $totalBloque3=$totalBloque3+$account->monto;
                    }
                    if($account->tipo==='4'){
                        $account->concepts=$concepts;
                        $cuentas_bloque4[]=$account;
                        $totalBloque4=$totalBloque4+$account->monto;
                    }
                }
                $item->cuentas_bloque1=$cuentas_bloque1;
                $item->total_bloque1=$totalBloque1;
                $item->cuentas_bloque2=$cuentas_bloque2;
                $item->total_bloque2=$totalBloque2;
                $item->cuentas_bloque3=$cuentas_bloque3;
                $item->total_bloque3=$totalBloque3;
                $item->cuentas_bloque4=$cuentas_bloque4;
                $item->total_bloque4=$totalBloque4;
            }
        
        return $results;
    }
}
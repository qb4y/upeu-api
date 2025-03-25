<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialStatementData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function summaryAccounts($request){
        $id_entidad = $request->query('id_entidad');
        $year = $request->query('year');
        $acumulate = $request->query('acumulate');
        $month_init = $request->query('month_init');
        $month = $request->query('month');
        $fondo = $request->query('fondo');
        $datos=[];
        $months='';
        if($acumulate=='1'){
            for ($x = $month_init; $x <= intval($month); $x++) {
                if($x!=$month){
                $months=$months.$x.',';
                }else{
                    $months=$months.$x;
                }
            } 
        }else{
            $months=$month;
        }
        $fondoWhere='is not null';
        if($fondo && $fondo!='0'){
            $fondoWhere='='.$fondo;
        }
        try
        {
            $qry="SELECT ID_PARENT PARENT_CUENTA,ID_CUENTAAASI as CUENTA,PARENT_NOMBRE, NOMBRE,
                SUM(NVL(ASSINET,0)) ASSINET,SUM(NVL(APS,0)) APS,(SUM(NVL(ASSINET,0))-SUM(NVL(APS,0))) DIFERENCIA
                FROM(
                    SELECT cdp.ID_CUENTAAASI ID_PARENT,cd.ID_CUENTAAASI,cdp.NOMBRE PARENT_NOMBRE, cd.NOMBRE,
                    SUM(d.COS_VALOR) ASSINET,0 AS APS
                    FROM  CONTA_CTA_DENOMINACIONAL cd
                    INNER JOIN CONTA_CTA_DENOMINACIONAL cdp ON cdp.ID_CUENTAAASI=cd.ID_PARENT
                    
                    INNER JOIN (SELECT DISTINCT ID_ENTIDAD,ID_CUENTAAASI,ID_TIPOPLAN,ID_RESTRICCION,ID_PLAN_FICHAFINANCIERA FROM APS_CONCEPTO_PLANI_CUENTAFICHA  WHERE ID_ENTIDAD = $id_entidad) CPC ON CPC.ID_CUENTAAASI=cd.ID_CUENTAAASI
                    AND CPC.ID_TIPOPLAN=cd.ID_TIPOPLAN AND CPC.ID_RESTRICCION=cd.ID_RESTRICCION 
                    INNER JOIN (SELECT DISTINCT * FROM APS_PLAN_FICHAFINANCIERA WHERE ID_ENTIDAD = $id_entidad) PF ON PF.ID_PLAN_FICHAFINANCIERA=CPC.ID_PLAN_FICHAFINANCIERA
                    
                    LEFT JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_FONDO,ID_TIPOPLAN,ID_RESTRICCION,ID_CUENTAAASI,COS_VALOR FROM VW_CONTA_DIARIO WHERE ID_ENTIDAD = $id_entidad
                    AND ID_ANHO =  $year 
			        AND ID_FONDO $fondoWhere
                    AND ID_MES IN($months)) d ON d.ID_CUENTAAASI=CPC.ID_CUENTAAASI
                    AND d.ID_TIPOPLAN=CPC.ID_TIPOPLAN
                    AND d.ID_RESTRICCION=CPC.ID_RESTRICCION 
                    
                    AND TO_NUMBER(d.ID_ANHO||LPAD(d.ID_MES,2,0))>=TO_NUMBER(PF.ANHO_INICIO||LPAD(PF.MES_INICIO,2,0))
                    AND TO_NUMBER(d.ID_ANHO||LPAD(d.ID_MES,2,0))<=TO_NUMBER(NVL(PF.ANHO_FIN,EXTRACT(YEAR FROM SYSDATE))||LPAD(NVL(PF.MES_FIN,EXTRACT(MONTH FROM SYSDATE)),2,0))
                    AND d.ID_ENTIDAD=CPC.ID_ENTIDAD
                    
                    GROUP BY cdp.ID_CUENTAAASI,cd.ID_CUENTAAASI,cdp.NOMBRE,cd.NOMBRE
                    UNION ALL      
                    SELECT cdp.ID_CUENTAAASI ID_PARENT,cd.ID_CUENTAAASI,cdp.NOMBRE PARENT_NOMBRE, cd.NOMBRE,0 AS ASSINET, SUM(p.COS_VALOR) APS
                    FROM CONTA_CTA_DENOMINACIONAL cd
                    INNER JOIN CONTA_CTA_DENOMINACIONAL cdp ON cdp.ID_CUENTAAASI=cd.ID_PARENT
                    
                    INNER JOIN (SELECT DISTINCT ID_ENTIDAD,ID_CUENTAAASI,ID_TIPOPLAN,ID_RESTRICCION,ID_PLAN_FICHAFINANCIERA,ID_CONCEPTOAPS FROM APS_CONCEPTO_PLANI_CUENTAFICHA WHERE ID_CONCEPTOAPS IS NOT NULL AND  ID_ENTIDAD = $id_entidad AND ID_FONDO $fondoWhere) c ON c.ID_CUENTAAASI=cd.ID_CUENTAAASI
                    AND c.ID_TIPOPLAN=cd.ID_TIPOPLAN AND c.ID_RESTRICCION=cd.ID_RESTRICCION 
                    INNER JOIN (SELECT DISTINCT * FROM APS_PLAN_FICHAFINANCIERA WHERE ID_ENTIDAD = $id_entidad) PF ON PF.ID_PLAN_FICHAFINANCIERA=c.ID_PLAN_FICHAFINANCIERA
                    
                    LEFT JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_CONCEPTOAPS,COS_VALOR FROM VW_APS_PLANILLA WHERE ID_ENTIDAD =$id_entidad 
                    AND ID_ANHO = $year
                    AND ID_MES IN($months)) p ON p.ID_CONCEPTOAPS=c.ID_CONCEPTOAPS
                    
                    AND TO_NUMBER(p.ID_ANHO||LPAD(p.ID_MES,2,0))>=TO_NUMBER(PF.ANHO_INICIO||LPAD(PF.MES_INICIO,2,0))
                    AND TO_NUMBER(p.ID_ANHO||LPAD(p.ID_MES,2,0))<=TO_NUMBER(NVL(PF.ANHO_FIN,EXTRACT(YEAR FROM SYSDATE))||LPAD(NVL(PF.MES_FIN,EXTRACT(MONTH FROM SYSDATE)),2,0))
                    AND p.ID_ENTIDAD=c.ID_ENTIDAD
                    
                    GROUP BY cdp.ID_CUENTAAASI,cd.ID_CUENTAAASI,cdp.NOMBRE,cd.NOMBRE
                )
                GROUP BY ID_PARENT,ID_CUENTAAASI,PARENT_NOMBRE, NOMBRE
                ORDER BY ID_PARENT,ID_CUENTAAASI";
               //print_r($qry);
            /* $qry = "SELECT ID_PARENT PARENT_CUENTA,ID_CUENTAAASI as CUENTA,PARENT_NOMBRE, NOMBRE,
            SUM(NVL(ASSINET,0)) ASSINET,SUM(NVL(APS,0)) APS,(SUM(NVL(ASSINET,0))-SUM(NVL(APS,0))) DIFERENCIA
            FROM
            (SELECT cdp.ID_CUENTAAASI ID_PARENT,cd.ID_CUENTAAASI,cdp.NOMBRE PARENT_NOMBRE, cd.NOMBRE,
            SUM(d.COS_VALOR) ASSINET,0 AS APS
            FROM CONTA_CTA_DENOMINACIONAL cd
            INNER JOIN CONTA_CTA_DENOMINACIONAL cdp ON cdp.ID_CUENTAAASI=cd.ID_PARENT
            
            INNER JOIN (SELECT * FROM APS_CONCEPTO_PLANI_CUENTAFICHA WHERE ID_FONDO ".$fondoWhere.") CPC ON CPC.ID_CUENTAAASI=cd.ID_CUENTAAASI
            AND CPC.ID_TIPOPLAN=cd.ID_TIPOPLAN AND CPC.ID_RESTRICCION=cd.ID_RESTRICCION 
            INNER JOIN APS_PLAN_FICHAFINANCIERA PF ON PF.ID_PLAN_FICHAFINANCIERA=CPC.ID_PLAN_FICHAFINANCIERA
            
            LEFT JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_FONDO,ID_TIPOPLAN,ID_CUENTAAASI,COS_VALOR FROM VW_CONTA_DIARIO WHERE ID_ENTIDAD = ".$id_entidad." 
            AND ID_ANHO =   ".$year." 
            AND ID_MES IN(".$months.")) d ON d.ID_CUENTAAASI=CPC.ID_CUENTAAASI
            AND d.ID_FONDO=CPC.ID_FONDO
            AND d.ID_TIPOPLAN=CPC.ID_TIPOPLAN
            
            AND CONCAT(d.ID_ANHO,d.ID_MES)>=CONCAT(PF.ANHO_INICIO,LPAD(PF.MES_INICIO,2,0))
            AND CONCAT(d.ID_ANHO,d.ID_MES)<=CONCAT(PF.ANHO_FIN,LPAD(PF.MES_FIN,2,0))
            AND d.ID_ENTIDAD=CPC.ID_ENTIDAD
            
            GROUP BY cdp.ID_CUENTAAASI,cd.ID_CUENTAAASI,cdp.NOMBRE,cd.NOMBRE
            UNION ALL         
            SELECT cdp.ID_CUENTAAASI ID_PARENT,cd.ID_CUENTAAASI,cdp.NOMBRE PARENT_NOMBRE, cd.NOMBRE,0 AS ASSINET, SUM(p.COS_VALOR) APS
            FROM CONTA_CTA_DENOMINACIONAL cd
            INNER JOIN CONTA_CTA_DENOMINACIONAL cdp ON cdp.ID_CUENTAAASI=cd.ID_PARENT
            
            INNER JOIN (SELECT * FROM APS_CONCEPTO_PLANI_CUENTAFICHA WHERE ID_FONDO ".$fondoWhere.") c ON c.ID_CUENTAAASI=cd.ID_CUENTAAASI
            AND c.ID_TIPOPLAN=cd.ID_TIPOPLAN AND c.ID_RESTRICCION=cd.ID_RESTRICCION 
            INNER JOIN APS_PLAN_FICHAFINANCIERA PF ON PF.ID_PLAN_FICHAFINANCIERA=c.ID_PLAN_FICHAFINANCIERA
            
            LEFT JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_CONCEPTOAPS,COS_VALOR FROM VW_APS_PLANILLA WHERE ID_ENTIDAD = ".$id_entidad." 
            AND ID_ANHO =  ".$year." 
            AND ID_MES IN(".$months.")) p ON p.ID_CONCEPTOAPS=c.ID_CONCEPTOAPS
            
            AND CONCAT(p.ID_ANHO,p.ID_MES)>=CONCAT(PF.ANHO_INICIO,LPAD(PF.MES_INICIO,2,0))
            AND CONCAT(p.ID_ANHO,p.ID_MES)<=CONCAT(PF.ANHO_FIN,LPAD(PF.MES_FIN,2,0))
            AND p.ID_ENTIDAD=c.ID_ENTIDAD
            
            GROUP BY cdp.ID_CUENTAAASI,cd.ID_CUENTAAASI,cdp.NOMBRE,cd.NOMBRE)
            GROUP BY ID_PARENT,ID_CUENTAAASI,PARENT_NOMBRE, NOMBRE
            ORDER BY ID_PARENT,ID_CUENTAAASI"; */
            #print_r($qry);
            $datos = DB::connection('oracle')->select($qry);
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
                
        return $datos;
    }
    
    public static function getFondos()
    {
        $query = DB::table('conta_fondo')
            ->select(
                'id_fondo'
                ,'nombre'
            )
            ->where('es_grupo','=','0')
            ->orderBy('id_parent')
            ->orderBy('id_fondo')
            ->get();
        return $query;
    }
    public static function getEntity($id_entidad)
    {
        $query = DB::table('conta_entidad')
            ->select('nombre')
            ->where('id_entidad','=',$id_entidad)
            ->first();
        return $query;
    }
    public static function personalFinacialAccounts($request){
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $acumulate = $request->query('acumulate');
        $month_init = $request->query('month_init');
        $month = $request->query('month');
        $fondo = $request->query('fondo');
        $search = $request->query('search');
        $orderColumn = $request->query('orderColumn');
        $order = $request->query('order');
        if(!$orderColumn){
            $orderColumn='nombres';
        }
        if(!$order){
            $order='asc';
        }
        $months='';
        if($acumulate=='1'){
            for ($x = $month_init; $x <= intval($month); $x++) {
                if($x!=$month){
                $months=$months.$x.',';
                }else{
                    $months=$months.$x;
                }
            } 
        }else{
            $months=$month;
        }
        $fondoWhere='is not null';
        if($fondo && $fondo!='0'){
            $fondoWhere='='.$fondo;
        }
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $results= DB::table("MOISES.PERSONA as P")
            ->join("MOISES.PERSONA_NATURAL as PN", "PN.ID_PERSONA", "=", "P.ID_PERSONA")
            ->join('APS_EMPLEADO as E','E.ID_PERSONA', '=', 'P.ID_PERSONA')
            ->join(DB::raw('(SELECT * FROM APS_PLANILLA WHERE ID_ENTIDAD='.$id_entidad.' AND ID_ANHO='.$id_anho.' AND ID_MES IN ('.$months.'))PL'), 'PL.ID_PERSONA', DB::raw('E.ID_PERSONA AND PL.ID_ENTIDAD=E.ID_ENTIDAD AND PL.ID_CONTRATO=E.ID_CONTRATO'))
            ->join('TIPO_CONTRATO as TC', 'TC.ID_TIPOCONTRATO', '=', 'E.ID_TIPOCONTRATO')
            ->leftJoin('TIPO_PAGO as TP', 'TP.ID_TIPOPAGO', '=', 'E.ID_TIPOPAGO')
            ->select(
                'P.ID_PERSONA',
                'PN.NUM_DOCUMENTO',
                DB::raw("P.PATERNO||' '||P.MATERNO||', '||P.NOMBRE as NOMBRES"),
                'PL.NOM_CARGO',
                'PL.ID_DEPTO',
                DB::raw('TP.NOMBRE as TIPOPAGO'),
                DB::raw('TC.NOMBRE as TIPOCONTRATO'),
                DB::raw("SUM(PL.FMR) as PUNTAJE_FMR")
            )
            ->whereRaw("(UPPER(PN.NUM_DOCUMENTO) LIKE UPPER('%{$search}%') OR UPPER(P.PATERNO) LIKE UPPER('%{$search}%') 
            OR UPPER(P.MATERNO) like UPPER('%{$search}%') OR UPPER(P.NOMBRE) LIKE UPPER('%{$search}%'))")
            ->groupBy('P.ID_PERSONA','PN.NUM_DOCUMENTO','P.PATERNO','P.MATERNO','P.NOMBRE','TC.NOMBRE','PL.NOM_CARGO','PL.ID_DEPTO','TP.NOMBRE')
            ->orderBy($orderColumn,$order)
            ->paginate($pageSize);
            foreach($results as $item){
                $cuentas=DB::table(DB::raw("(
                    SELECT cdp.ID_CUENTAAASI ID_PARENT,cd.ID_CUENTAAASI,cdp.NOMBRE PARENT_NOMBRE, cd.NOMBRE,
                    SUM(d.COS_VALOR) AS MONTO
                    FROM  CONTA_CTA_DENOMINACIONAL cd
                    INNER JOIN CONTA_CTA_DENOMINACIONAL cdp ON cdp.ID_CUENTAAASI=cd.ID_PARENT
                    
                    INNER JOIN (SELECT DISTINCT ID_ENTIDAD,ID_CUENTAAASI,ID_TIPOPLAN,ID_RESTRICCION,ID_PLAN_FICHAFINANCIERA FROM APS_CONCEPTO_PLANI_CUENTAFICHA  WHERE ID_ENTIDAD = $id_entidad AND SISTEMA LIKE '%ASSINET%') CPC ON CPC.ID_CUENTAAASI=cd.ID_CUENTAAASI
                    AND CPC.ID_TIPOPLAN=cd.ID_TIPOPLAN AND CPC.ID_RESTRICCION=cd.ID_RESTRICCION 
                    INNER JOIN (SELECT DISTINCT * FROM APS_PLAN_FICHAFINANCIERA WHERE ID_ENTIDAD = $id_entidad) PF ON PF.ID_PLAN_FICHAFINANCIERA=CPC.ID_PLAN_FICHAFINANCIERA
                    
                    LEFT JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_FONDO,ID_TIPOPLAN,ID_RESTRICCION,ID_CUENTAAASI,COS_VALOR FROM VW_CONTA_DIARIO WHERE ID_ENTIDAD = $id_entidad
                    AND ID_ANHO =  $id_anho 
                    AND ID_FONDO $fondoWhere
                    AND ID_CTACTE ='$item->num_documento'  
                    AND ID_MES IN($months)) d ON d.ID_CUENTAAASI=CPC.ID_CUENTAAASI
                    AND d.ID_TIPOPLAN=CPC.ID_TIPOPLAN
                    AND d.ID_RESTRICCION=CPC.ID_RESTRICCION 
                    
                    
                    AND TO_NUMBER(d.ID_ANHO||LPAD(d.ID_MES,2,0))>=TO_NUMBER(PF.ANHO_INICIO||LPAD(PF.MES_INICIO,2,0))
                    AND TO_NUMBER(d.ID_ANHO||LPAD(d.ID_MES,2,0))<=TO_NUMBER(NVL(PF.ANHO_FIN,EXTRACT(YEAR FROM SYSDATE))||LPAD(NVL(PF.MES_FIN,EXTRACT(MONTH FROM SYSDATE)),2,0))
                    AND d.ID_ENTIDAD=CPC.ID_ENTIDAD
                    
                    GROUP BY cdp.ID_CUENTAAASI,cd.ID_CUENTAAASI,cdp.NOMBRE,cd.NOMBRE
                    
                    UNION ALL      
                    SELECT cdp.ID_CUENTAAASI ID_PARENT,cd.ID_CUENTAAASI,cdp.NOMBRE PARENT_NOMBRE, cd.NOMBRE,SUM(p.COS_VALOR) AS MONTO
                    FROM CONTA_CTA_DENOMINACIONAL cd
                    INNER JOIN CONTA_CTA_DENOMINACIONAL cdp ON cdp.ID_CUENTAAASI=cd.ID_PARENT
                    
                    INNER JOIN (SELECT DISTINCT ID_ENTIDAD,ID_CUENTAAASI,ID_TIPOPLAN,ID_RESTRICCION,ID_PLAN_FICHAFINANCIERA,ID_CONCEPTOAPS FROM APS_CONCEPTO_PLANI_CUENTAFICHA WHERE ID_CONCEPTOAPS IS NOT NULL AND  ID_ENTIDAD = $id_entidad AND ID_FONDO $fondoWhere  AND SISTEMA LIKE '%APS%') c ON c.ID_CUENTAAASI=cd.ID_CUENTAAASI
                    AND c.ID_TIPOPLAN=cd.ID_TIPOPLAN AND c.ID_RESTRICCION=cd.ID_RESTRICCION 
                    INNER JOIN (SELECT DISTINCT * FROM APS_PLAN_FICHAFINANCIERA WHERE ID_ENTIDAD = $id_entidad) PF ON PF.ID_PLAN_FICHAFINANCIERA=c.ID_PLAN_FICHAFINANCIERA
                    
                    LEFT JOIN (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_CONCEPTOAPS,COS_VALOR FROM VW_APS_PLANILLA WHERE ID_ENTIDAD =$id_entidad 
                    AND ID_ANHO = $id_anho
                    AND ID_PERSONA =$item->id_persona 
                    AND ID_MES IN($months)) p ON p.ID_CONCEPTOAPS=c.ID_CONCEPTOAPS
                    
                    AND TO_NUMBER(p.ID_ANHO||LPAD(p.ID_MES,2,0))>=TO_NUMBER(PF.ANHO_INICIO||LPAD(PF.MES_INICIO,2,0))
                    AND TO_NUMBER(p.ID_ANHO||LPAD(p.ID_MES,2,0))<=TO_NUMBER(NVL(PF.ANHO_FIN,EXTRACT(YEAR FROM SYSDATE))||LPAD(NVL(PF.MES_FIN,EXTRACT(MONTH FROM SYSDATE)),2,0))
                    AND p.ID_ENTIDAD=c.ID_ENTIDAD
                    
                    GROUP BY cdp.ID_CUENTAAASI,cd.ID_CUENTAAASI,cdp.NOMBRE,cd.NOMBRE
                )"))
                ->select(
                    "ID_CUENTAAASI",
                    "NOMBRE",            
                    DB::raw("SUM(NVL(MONTO,0)) as MONTO")
                )
                ->groupBy('ID_PARENT')
                ->groupBy('ID_CUENTAAASI')
                ->groupBy('PARENT_NOMBRE')
                ->groupBy('NOMBRE')
                ->orderByRaw('ID_PARENT,ID_CUENTAAASI ASC')
                ->get();
                foreach($cuentas as $account){
                    $concepts=DB::table('APS_CONCEPTO_PLANILLA as CP')
                    ->join(DB::raw('(SELECT DISTINCT ID_ENTIDAD,ID_CONCEPTOAPS,ID_PLAN_FICHAFINANCIERA,ID_CUENTAAASI FROM 
                    APS_CONCEPTO_PLANI_CUENTAFICHA WHERE ID_CUENTAAASI='.$account->id_cuentaaasi.' AND ID_ENTIDAD='.$id_entidad.' AND ID_FONDO '.$fondoWhere.') CPC')
                    , 'CPC.ID_CONCEPTOAPS','=','CP.ID_CONCEPTOAPS')
                    ->join(DB::raw("(SELECT DISTINCT * FROM APS_PLAN_FICHAFINANCIERA WHERE ID_ENTIDAD = $id_entidad) PF"), 'PF.ID_PLAN_FICHAFINANCIERA', '=', 'CPC.ID_PLAN_FICHAFINANCIERA')
                    ->leftJoin(DB::raw('(SELECT ID_ENTIDAD,ID_PERSONA,ID_ANHO,ID_MES,ID_CONCEPTOAPS,COS_VALOR FROM APS_PLANILLA_DETALLE
                    WHERE ID_ENTIDAD='.$id_entidad.
                    ' AND ID_PERSONA='.$item->id_persona.' AND ID_ANHO='.$id_anho.
                    ' AND ID_MES IN ('.$months.')) PD'), 'PD.ID_CONCEPTOAPS', 
                    '=',DB::raw('CPC.ID_CONCEPTOAPS 
                    AND TO_NUMBER(PD.ID_ANHO||LPAD(PD.ID_MES,2,0))>=TO_NUMBER(PF.ANHO_INICIO||LPAD(PF.MES_INICIO,2,0))
                    AND TO_NUMBER(PD.ID_ANHO||LPAD(PD.ID_MES,2,0))<=TO_NUMBER(NVL(PF.ANHO_FIN,EXTRACT(YEAR FROM SYSDATE))||LPAD(NVL(PF.MES_FIN,EXTRACT(MONTH FROM SYSDATE)),2,0))'))
                    ->select(
                        "CPC.ID_CONCEPTOAPS",
                        "CP.NOMBRE",
                        DB::raw("NVL(SUM(PD.COS_VALOR),0) as MONTO")
                    );
                    $concepts=$concepts->groupBy('CPC.ID_CONCEPTOAPS')
                    ->groupBy('CP.NOMBRE')
                    ->orderByRaw('CPC.ID_CONCEPTOAPS ASC')
                    ->get();
                    $account->concepts=$concepts?$concepts:[];
                }
                $item->cuentas=$cuentas;
                }
        
        return $results;
    }
}


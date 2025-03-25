<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;

class ConceptData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
  
    public static function getTypeConcepts()
    {
        $query = DB::table('TIPO_CONCEPTO_PLANILLA')
            ->select('NOMBRE','ID_TIPOCONCEPTOAPS')
            ->orderBy('SIGNO')
            ->orderBy('ID_TIPOCONCEPTOAPS')
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
    public static function getConcepts()
    {
        $query = DB::table('APS_CONCEPTO_PLANILLA as CP')
            ->join('TIPO_CONCEPTO_PLANILLA as TCP', 'TCP.ID_TIPOCONCEPTOAPS', '=', 'CP.ID_TIPOCONCEPTOAPS')
            ->select('CP.ID_CONCEPTOAPS',
            'CP.NOMBRE as CONCEPTO',
            DB::raw("CP.ID_CONCEPTOAPS||'-'||CP.NOMBRE as CONCEPTOAPS"),
            'CP.ID_TIPOCONCEPTOAPS',
            'TCP.NOMBRE as TIPOCONCEPTO')
            ->orderBy('TCP.NOMBRE')
            ->orderBy('CP.ID_CONCEPTOAPS')
            ->get();
        return $query;
    }
    
    public static function getPaidConcepts($request)
    {
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $id_mes = $request->query('month');
        $id_tipoconceptoaps = $request->query('id_tipoconceptoaps');
        $id_persona = $request->query('id_persona');
        $query = DB::table('APS_CONCEPTO_PLANILLA as CP')
        ->join('TIPO_CONCEPTO_PLANILLA as TCP', 'TCP.ID_TIPOCONCEPTOAPS', '=', 'CP.ID_TIPOCONCEPTOAPS')
        ->join('APS_PLANILLA_DETALLE as PD', 'PD.ID_CONCEPTOAPS', '=', 'CP.ID_CONCEPTOAPS')
        ->select('TCP.ID_TIPOCONCEPTOAPS',
            'CP.ID_CONCEPTOAPS AS CODIGO',
            'CP.NOMBRE AS CONCEPTO',
            DB::raw("NVL(SUM(PD.COS_VALOR),0) as VALOR")
        )
        ->where('PD.ID_ENTIDAD','=',$id_entidad)
        ->where('PD.ID_ANHO','=',$id_anho);
        if($id_mes && $id_mes!='null'){
            $query= $query->where('PD.ID_MES','=',$id_mes);
        }
        if($id_tipoconceptoaps && $id_tipoconceptoaps!='null'){
            $query= $query->where('TCP.ID_TIPOCONCEPTOAPS','=',$id_tipoconceptoaps);
        }
        if($id_persona && $id_persona!='null'){
            if(!is_numeric($id_persona)) {
                $id_persona=NULL;
            }
            $query= $query->where('PD.ID_PERSONA','=',$id_persona);
        }
        
        
        $query= $query->groupBy('TCP.ID_TIPOCONCEPTOAPS')
        ->groupBy('CP.ID_CONCEPTOAPS')
        ->groupBy('CP.NOMBRE')
        ->orderBy('CP.ID_CONCEPTOAPS')
        ->get();
        return $query;
    }
    public static function getBallotConcepts($request)
    {
        $id_tipogrupocuenta = $request->query('id_tipogrupocuenta');
        $query = DB::table('CONTA_CUENTA_GRUPO as CCG')
        ->select(
            'CCG.ID_TIPOGRUPOCUENTA',
            'CCG.ID_CUENTAGRUPO',
            'CCG.NOMBRE',
            'TGC.NOMBRE as GRUPOCUENTA',
            'CCG.CUENTAS',
            'CCG.NUM_ORDEN'
        )->join('TIPO_GRUPO_CUENTA as TGC', 'TGC.ID_TIPOGRUPOCUENTA', '=', 'CCG.ID_TIPOGRUPOCUENTA');
        if($id_tipogrupocuenta && $id_tipogrupocuenta!=null && $id_tipogrupocuenta!='null'){
            $query=$query->where('TGC.ID_TIPOGRUPOCUENTA','=',$id_tipogrupocuenta);
        }
        $query=$query->orderBY('TGC.NOMBRE','ASC')
        ->orderBY('CCG.NOMBRE','ASC')
        ->get();
        return $query;
    }
    public static function getTypeGroupAccount()
    {
        $query = DB::table('TIPO_GRUPO_CUENTA')
        ->select(
            'ID_TIPOGRUPOCUENTA',
            'NOMBRE',
            'NOM_CLASE'
        )
        ->orderBY('NOM_CLASE','ASC')
        ->orderBY('NOMBRE','ASC')
        ->get();
        return $query;
    }
    public static function getConceptsPayrollAps($request)
    {
        $id_tipoconceptoaps = $request->query('id_tipoconceptoaps');
        $query = DB::table('APS_CONCEPTO_PLANILLA as CP')
        ->select(
            'CP.ID_CONCEPTOAPS',
            'CP.NOMBRE as CONCEPTO',
            'TC.NOMBRE as CONTRATO',
            'TCP.NOMBRE as TIPOCONCEPTOPLANILLA',
            'TCP.ID_TIPOCONCEPTOAPS',
            'CPC.ID_CUENTAAASI',
            'CCD.NOMBRE as CUENTA',
            'CCE.ID_CUENTAEMPRESARIAL',
            'CCE.NOMBRE as CUENTAEMPRESARIAL',
            'CP.COD_SUNAT'
        )
        ->join('APS_CONCEPTO_PLANILLA_CUENTA as CPC', 'CPC.ID_CONCEPTOAPS', '=', 'CP.ID_CONCEPTOAPS')
        ->join('TIPO_PLANILLA as TP', 'CPC.ID_TIPOPLANILLA', '=', 'TP.ID_TIPOPLANILLA')
        ->join('TIPO_CONTRATO as TC', 'TC.ID_TIPOCONTRATO', '=', 'CPC.ID_TIPOCONTRATO')
        ->join('CONTA_CTA_DENOMINACIONAL as CCD', 'CPC.ID_CUENTAAASI', '=', DB::raw('CCD.ID_CUENTAAASI AND CPC.ID_RESTRICCION=CCD.ID_RESTRICCION AND CPC.ID_TIPOPLAN=CCD.ID_TIPOPLAN'))
        ->join('CONTA_EMPRESA_CTA as CEC', 'CEC.ID_CUENTAAASI', '=', DB::raw('CPC.ID_CUENTAAASI AND CEC.ID_RESTRICCION=CPC.ID_RESTRICCION AND CEC.ID_TIPOPLAN=CPC.ID_TIPOPLAN'))
        ->join('CONTA_CTA_EMPRESARIAL as CCE', 'CCE.ID_CUENTAEMPRESARIAL', '=', 'CEC.ID_CUENTAEMPRESARIAL')
        ->join('TIPO_CONCEPTO_PLANILLA as TCP', 'TCP.ID_TIPOCONCEPTOAPS', '=', 'CP.ID_TIPOCONCEPTOAPS');
        if($id_tipoconceptoaps && $id_tipoconceptoaps!=null && $id_tipoconceptoaps!='null'){
            $query=$query->where('CP.ID_TIPOCONCEPTOAPS','=',$id_tipoconceptoaps);
        }
        $query=$query->orderBY('TCP.NOMBRE','ASC')
        ->orderBY('CP.NOMBRE','ASC')
        ->distinct()
        ->get();
        return $query;
    }
    public static function getBalanceConcepts($request)
    {
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $acumulate = $request->query('acumulate');
        $month_init = $request->query('month_init');
        $month = $request->query('month');
        $id_conceptoaps = $request->query('id_conceptoaps');
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
       
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $query = DB::table('VW_APS_PLANILLA as PL')
        ->select(
            'TCP.ID_TIPOCONCEPTOAPS',
            'TCP.NOMBRE as TIPOCONCEPTO',
            'E.NUM_DOCUMENTO',
            'E.NOM_PERSONA',
            'CP.NOMBRE as CONCEPTO',
            DB::raw("
                SUM(
                    CASE WHEN CP.TIPO='D' OR CP.TIPO='R' THEN -PL.COS_VALOR
                    ELSE PL.COS_VALOR END) as VALOR"),
            DB::raw("
                SUM(
                    CASE WHEN CP.TIPO='D' OR CP.TIPO='R' THEN -PL.COS_REFERENCIA1
                    ELSE PL.COS_REFERENCIA1 END) as REFERENCIA")
        )
        ->join('VW_APS_EMPLEADO as E', 'E.ID_PERSONA', '=',
            DB::raw('PL.ID_PERSONA AND E.ID_ENTIDAD=PL.ID_ENTIDAD AND E.ID_CONTRATO=PL.ID_CONTRATO'))
        ->join('APS_CONCEPTO_PLANILLA as CP', 'CP.ID_CONCEPTOAPS', '=', 'PL.ID_CONCEPTOAPS')
        ->join('TIPO_CONCEPTO_PLANILLA as TCP', 'TCP.ID_TIPOCONCEPTOAPS', '=', 'CP.ID_TIPOCONCEPTOAPS');
        if($id_conceptoaps && $id_conceptoaps!=null && $id_conceptoaps!='null'){
            $query=$query->where('PL.ID_CONCEPTOAPS','=',$id_conceptoaps);
        }
        $query=$query->where('PL.ID_ANHO','=',$id_anho)
        ->where('PL.ID_ENTIDAD','=',$id_entidad)
        ->whereRaw("PL.ID_MES IN (".$months.") AND E.ID_TIPODOCUMENTO IN (1,4) AND
         (UPPER(E.NUM_DOCUMENTO) LIKE UPPER('%{$search}%') OR UPPER(E.PATERNO) LIKE UPPER('%{$search}%') 
            OR UPPER(E.MATERNO) like UPPER('%{$search}%') OR UPPER(E.NOMBRE) LIKE UPPER('%{$search}%'))")
        ->groupBy('TCP.ID_TIPOCONCEPTOAPS')
        ->groupBy('TCP.NOMBRE')
        ->groupBy('E.NUM_DOCUMENTO')
        ->groupBy('E.NOM_PERSONA')
        ->groupBy('CP.NOMBRE')
        ->orderBY('TCP.NOMBRE','ASC')
        ->orderBY('E.NOM_PERSONA','ASC')
        ->distinct()
        ->get();
        return $query;
    }

    public static function getBalanceEquityAccounts($request)
    {
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $acumulate = $request->query('acumulate');
        $month_init = $request->query('month_init');
        $month = $request->query('month');
        $orderColumn = $request->query('orderColumn');
        $order = $request->query('order');
        if(!$orderColumn){
            $orderColumn='nombre';
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
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $query = DB::select(
            'select "CCD5"."ID_CUENTAAASI" as "PARENT", "CCD5"."NOMBRE" as "PARENTNOMBRE", "CCD1"."ID_CUENTAAASI" as "CUENTA", "CCD1"."NOMBRE", SUM(CD.DEBE) as DEBE, SUM(CD.HABER) as HABER, 
            SUM(CD.COS_VALOR) as SALDO 
            from (SELECT ID_ANHO,ID_MES, ID_ENTIDAD,ID_CUENTAAASI,DEBE,HABER,COS_VALOR FROM  "VW_CONTA_DIARIO" WHERE ID_ANHO ='. $id_anho.' and ID_ENTIDAD = '.$id_entidad.' and ID_MES IN ('.$months.')
            and SUBSTR(ID_CUENTAAASI,0,4) IN (1135,2135)) CD 
            inner join (SELECT ID_CUENTAAASI, ID_PARENT,NOMBRE FROM "CONTA_CTA_DENOMINACIONAL" WHERE SUBSTR(ID_CUENTAAASI,0,4)IN (1135,2135)) CCD1 on "CD"."ID_CUENTAAASI" = "CCD1"."ID_CUENTAAASI" 
            inner join (SELECT ID_CUENTAAASI, ID_PARENT FROM "CONTA_CTA_DENOMINACIONAL" WHERE SUBSTR(ID_CUENTAAASI,0,4)IN (1135,2135)) CCD2 on "CCD1"."ID_PARENT" = "CCD2"."ID_CUENTAAASI" 
            inner join "CONTA_CTA_DENOMINACIONAL" CCD3 on "CCD2"."ID_PARENT" = "CCD3"."ID_CUENTAAASI" 
            inner join "CONTA_CTA_DENOMINACIONAL" CCD4 on "CCD3"."ID_PARENT" = "CCD4"."ID_CUENTAAASI" 
            inner join "CONTA_CTA_DENOMINACIONAL" CCD5 on "CCD4"."ID_PARENT" = "CCD5"."ID_CUENTAAASI" 
            group by "CCD5"."ID_CUENTAAASI", "CCD5"."NOMBRE", "CCD1"."ID_CUENTAAASI", "CCD1"."NOMBRE" 
            order by "CCD5"."ID_CUENTAAASI" asc, "CCD5"."NOMBRE" asc, "CCD1"."ID_CUENTAAASI" asc, "CCD1"."NOMBRE" asc');
        return $query;
    }

    public static function getAccountBalancesSpending($request)
    {
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $acumulate = $request->query('acumulate');
        $month_init = $request->query('month_init');
        $month = $request->query('month');
        $orderColumn = $request->query('orderColumn');
        $order = $request->query('order');
        if(!$orderColumn){
            $orderColumn='nombre';
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
       
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $query = DB::table(DB::raw("(SELECT ID_CUENTAAASI,DEBE,HABER,COS_VALOR FROM VW_CONTA_DIARIO WHERE ID_ANHO =".$id_anho." AND ID_ENTIDAD=".$id_entidad." AND ID_MES IN (".$months.")) CD"))
        ->select(
            'CCD5.ID_CUENTAAASI as PARENT',
            'CCD5.NOMBRE as PARENTNOMBRE',
            'CCD1.ID_CUENTAAASI as CUENTA',
            'CCD1.NOMBRE',
            DB::raw("SUM(CD.DEBE) as DEBE"),
            DB::raw("SUM(CD.HABER) as HABER"),
            DB::raw("SUM(CD.COS_VALOR) as SALDO")
        )
        ->join('CONTA_CTA_DENOMINACIONAL as CCD1', 'CD.ID_CUENTAAASI', '=', 'CCD1.ID_CUENTAAASI')
        ->join('CONTA_CTA_DENOMINACIONAL as CCD2', 'CCD1.ID_PARENT', '=', 'CCD2.ID_CUENTAAASI')
        ->join(DB::raw('(SELECT ID_CUENTAAASI,ID_PARENT FROM CONTA_CTA_DENOMINACIONAL WHERE ID_CUENTAAASI=4110000)CCD3'), 'CCD2.ID_PARENT', '=', 'CCD3.ID_CUENTAAASI')
        ->join('CONTA_CTA_DENOMINACIONAL as CCD4', 'CCD3.ID_PARENT', '=', 'CCD4.ID_CUENTAAASI')
        ->join('CONTA_CTA_DENOMINACIONAL as CCD5', 'CCD4.ID_PARENT', '=', 'CCD5.ID_CUENTAAASI')
        ->groupBy('CCD5.ID_CUENTAAASI')
        ->groupBy('CCD5.NOMBRE')
        ->groupBy('CCD1.ID_CUENTAAASI')
        ->groupBy('CCD1.NOMBRE')
        ->orderBY('CCD5.ID_CUENTAAASI','ASC')
        ->orderBY('CCD5.NOMBRE','ASC')
        ->orderBY('CCD1.ID_CUENTAAASI','ASC')
        ->orderBY('CCD1.NOMBRE','ASC')
        ->get();
        return $query;
    }
    public static function getDetailEquityAccounts($request)
    {
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('year');
        $acumulate = $request->query('acumulate');
        $month_init = $request->query('month_init');
        $month = $request->query('month');
        $orderColumn = $request->query('orderColumn');
        $order = $request->query('order');
        $estado = $request->query('estado');
        if(!$orderColumn){
            $orderColumn='nombre';
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
       
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $query = DB::table('VW_APS_EMPLEADO')
        ->select(
            'NOM_PERSONA',
            'NUM_DOCUMENTO',
            'ID_PERSONA'
        )
        ->where('ID_ENTIDAD','=',$id_entidad)
        ->where('ESTADO','=',$estado)
        ->whereRaw("ID_TIPODOCUMENTO IN (1,4) AND (UPPER(NUM_DOCUMENTO) LIKE UPPER('%{$search}%') OR UPPER(PATERNO) LIKE UPPER('%{$search}%') 
        OR UPPER(MATERNO) like UPPER('%{$search}%') OR UPPER(NOMBRE) LIKE UPPER('%{$search}%'))")
        ->orderBY('NOM_PERSONA','ASC')
        ->distinct()
        ->paginate($pageSize);
        foreach($query as $item){
            $cuentas=DB::select(
                'select "CCD1"."ID_CUENTAAASI", "CCD1"."NOMBRE", NVL(SUM(PD.COS_VALOR),0) as VALOR 
                from (SELECT ID_CUENTAAASI, ID_PARENT,NOMBRE FROM "CONTA_CTA_DENOMINACIONAL" WHERE SUBSTR(ID_CUENTAAASI,0,4) IN (2135,1135)) CCD1 
                left join (SELECT ID_ENTIDAD,ID_ANHO,ID_MES,ID_CUENTAAASI,COS_VALOR FROM "VW_CONTA_DIARIO" WHERE ID_ENTIDAD='.$id_entidad.' AND ID_ANHO='.$id_anho.' AND ID_MES IN ('.$months.') AND ID_CTACTE='.$item->num_documento.' AND SUBSTR(ID_CUENTAAASI,0,4) IN (2135,1135))PD on "PD"."ID_CUENTAAASI" = CCD1.ID_CUENTAAASI
                group by "CCD1"."ID_CUENTAAASI", "CCD1"."NOMBRE" 
                order by "CCD1"."ID_CUENTAAASI" asc');
            $item->cuentas=$cuentas;
        }
        return $query;
    }
    public static function getTypePayroll()
    {
        $query = DB::table('ELISEO.TIPO_PLANILLA')
            ->select('NOMBRE','ID_TIPOPLANILLA')
            ->get();
        return $query;
    }
    public static function getTypeContract()
    {
        $query = DB::table('ELISEO.TIPO_CONTRATO')
            ->select('NOMBRE','NOMBRE_CORTO','ID_TIPOCONTRATO')
            ->get();
        return $query;
    }
    public static function getTypePlan()
    {
        $query = DB::table('ELISEO.TIPO_PLAN')
            ->select('NOMBRE','ID_TIPOPLAN')
            ->get();
        return $query;
    }
    public static function getConceptPayrollAccount($request)
    {
        $id_tipoplanilla = $request->query('id_tipoplanilla');
        $id_tipocontrato = $request->query('id_tipocontrato');
        $id_tipoplan = $request->query('id_tipoplan');
        $id_cuentaaasi = $request->query('id_cuentaaasi');
        $id_restriccion = $request->query('id_restriccion');
        $id_fondo = $request->query('id_fondo');
        $query = DB::table('ELISEO.APS_CONCEPTO_PLANILLA_CUENTA as CPC')
            ->join('ELISEO.APS_CONCEPTO_PLANILLA as CP', 'CP.ID_CONCEPTOAPS', '=', 'CPC.ID_CONCEPTOAPS')
            ->join('ELISEO.CONTA_CTA_DENOMINACIONAL as CCD', 'CCD.ID_CUENTAAASI', '=', 'CPC.ID_CUENTAAASI')
            ->join('ELISEO.TIPO_PLAN as TP', 'TP.ID_TIPOPLAN', '=', 'CPC.ID_TIPOPLAN')
            ->join('ELISEO.TIPO_PLANILLA as TPP', 'TPP.ID_TIPOPLANILLA', '=', 'CPC.ID_TIPOPLANILLA')
            ->join('ELISEO.TIPO_CONTRATO as TC', 'TC.ID_TIPOCONTRATO', '=', 'CPC.ID_TIPOCONTRATO')
            ->select(
                'CPC.*',
                'CP.NOMBRE as NOMBRE_CONCEPTO',
                'CCD.NOMBRE as NOMBRE_CUENTA',
                'TP.NOMBRE as NOMBRE_TIPOPLAN',
                'TPP.NOMBRE as NOMBRE_TIPOPLANILLA',
                'TC.NOMBRE as NOMBRE_TIPOCONTRATO'
            );
            if($id_tipoplan){
                $query=$query->where('CPC.ID_TIPOPLAN','=',$id_tipoplan);
            }
            if($id_tipoplanilla){
            $query=$query->where('CPC.ID_TIPOPLANILLA','=',$id_tipoplanilla);
            }
            if($id_tipocontrato){
                $query=$query->where('CPC.ID_TIPOCONTRATO','=',$id_tipocontrato);
            }
            if($id_cuentaaasi && $id_cuentaaasi!='null'){
                $query=$query->where('CPC.ID_CUENTAAASI','=',$id_cuentaaasi);
            }
            if($id_restriccion){
                $query=$query->where('CPC.ID_RESTRICCION','=',$id_restriccion);
            }
            if($id_fondo){
                $query=$query->where('CPC.ID_FONDO','=',$id_fondo);
            }
            $query=$query->orderBY('CPC.ID_CUENTAAASI','ASC')
            ->orderBY('CPC.ID_CONCEPTOAPS','ASC')
            ->distinct()
            ->get();
        return $query;
    }
    
    public static function getRestriccion()
    {
        $query = DB::table('ELISEO.CONTA_RESTRICCION')
        ->select('ID_RESTRICCION','NOMBRE')
        ->where('ES_GRUPO','=','0')
        ->orderBY('NOMBRE','ASC')
        ->get();
    return $query;
    }

    public static function listPayrollConceptAccountTab($request) {
        // $search = $request->query('search');
        $id_tipoplan = $request->query('id_tipoplan');
        $id_cuentaaasi = $request->query('id_cuentaaasi');
        $id_restriccion = $request->query('id_restriccion');
        $id_fondo = $request->query('id_fondo');
        $id_plan_fichafinanciera = $request->query('id_plan_fichafinanciera');
        $id_entidad = $request->query('id_entidad');
        $query = DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA as CPCF')
            ->leftjoin('ELISEO.APS_CONCEPTO_PLANILLA as CP', 'CP.ID_CONCEPTOAPS', '=', 'CPCF.ID_CONCEPTOAPS')
            ->join('ELISEO.CONTA_CTA_DENOMINACIONAL as CCD', 'CCD.ID_CUENTAAASI', '=', 'CPCF.ID_CUENTAAASI')
            ->join('ELISEO.TIPO_PLAN as TP', 'TP.ID_TIPOPLAN', '=', 'CPCF.ID_TIPOPLAN')
            ->select(
                'CPCF.*',
                DB::raw('nvl(CP.NOMBRE,CCD.NOMBRE) as NOMBRE_CONCEPTO'),
                'CCD.NOMBRE as NOMBRE_CUENTA',
                'TP.NOMBRE as NOMBRE_TIPOPLAN'
            );
            if($id_tipoplan){
                $query=$query->where('CPCF.ID_TIPOPLAN','=',$id_tipoplan);
            }
            if($id_cuentaaasi && $id_cuentaaasi!='null'){
                $query=$query->where('CPCF.ID_CUENTAAASI','=',$id_cuentaaasi);
            }
            if($id_restriccion){
                $query=$query->where('CPCF.ID_RESTRICCION','=',$id_restriccion);
            }
            if($id_fondo){
                $query=$query->where('CPCF.ID_FONDO','=',$id_fondo);
            }
            $query=$query->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera);
            $query=$query->where('ID_ENTIDAD','=',$id_entidad);
            $query=$query->orderBY('CPCF.ID_CUENTAAASI','ASC')
            ->orderBY('CPCF.ID_CONCEPTOAPS','ASC')
            ->distinct()
            ->get();
        return $query;
    }

    public static function getDataToEditPayrollConceptAccount($request)
    {
        $id_cuentaaasi = $request->id_cuentaaasi;
        $id_tipoplan = $request->id_tipoplan;
        $id_restriccion = $request->id_restriccion;
        $sistema = $request->sistema;
        $id_plan_fichafinanciera = $request->id_plan_fichafinanciera;
        $id_entidad = $request->id_entidad;

        $query_concepts = DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA')
            ->select('ID_CONCEPTOAPS')
            ->where('ID_CUENTAAASI', '=', $id_cuentaaasi)
            ->where('ID_TIPOPLAN', '=', $id_tipoplan)
            ->where('ID_RESTRICCION', '=', $id_restriccion)
            ->where('ID_PLAN_FICHAFINANCIERA', '=', $id_plan_fichafinanciera)
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->distinct()
            ->get();

        $query_fondos = DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA')
            ->select('ID_FONDO')
            ->where('ID_CUENTAAASI', '=', $id_cuentaaasi)
            ->where('ID_TIPOPLAN', '=', $id_tipoplan)
            ->where('ID_RESTRICCION', '=', $id_restriccion)
            ->where('ID_PLAN_FICHAFINANCIERA', '=', $id_plan_fichafinanciera)
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->distinct()
            ->get();

        $concepts = [];
        $fondos = [];

        foreach($query_concepts as $item){
            $concepts [] = $item->id_conceptoaps;
        }

        foreach($query_fondos as $item){
            $fondos [] = $item->id_fondo;
        }
        return array('concepts' => $concepts, 'fondos' => $fondos);
    }

    public static function addPayrollConceptAccountTab($request) {
        $ret='OK';
        $id_cuentaaasi = $request->id_cuentaaasi;
        $id_tipoplan = $request->id_tipoplan;
        $id_restriccion = $request->id_restriccion;
        $fondos = $request->fondos;
        $concepts = $request->conceptos;
        $id_plan_fichafinanciera = $request->id_plan_fichafinanciera;
        $id_entidad = $request->id_entidad;
        $sistema = $request->sistema;

        DB::table('APS_CONCEPTO_PLANI_CUENTAFICHA')
        ->where('ID_CUENTAAASI','=',$id_cuentaaasi)
        ->where('ID_TIPOPLAN','=',$id_tipoplan)
        ->where('ID_RESTRICCION','=',$id_restriccion)
        ->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera)
        ->where('ID_ENTIDAD','=',$id_entidad)
        ->delete();

            foreach($fondos as $fondo){
                $count = DB::table('APS_CONCEPTO_PLANI_CUENTAFICHA')
                ->where('ID_CUENTAAASI','=',$id_cuentaaasi)
                ->where('ID_TIPOPLAN','=',$id_tipoplan)
                ->where('ID_RESTRICCION','=',$id_restriccion)
                ->where('ID_FONDO','=',$fondo)
                ->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera)
                ->where('ID_ENTIDAD','=',$id_entidad)
                ->where('SISTEMA','=',$sistema)
                ->count();
                if($count == 0){
                    count($concepts);
                    if (count($concepts)) {
                        foreach($concepts as $conaps){
                            DB::table('APS_CONCEPTO_PLANI_CUENTAFICHA')->insert(
                                array('ID_CUENTAAASI' => $id_cuentaaasi,
                                    'ID_TIPOPLAN' => $id_tipoplan,
                                    'ID_RESTRICCION' => $id_restriccion,
                                    'ID_FONDO' => $fondo,
                                    'SISTEMA' => $sistema,
                                    'ID_CONCEPTOAPS' => $conaps['id_conceptoaps'],
                                    'ID_PLAN_FICHAFINANCIERA' => $id_plan_fichafinanciera,
                                    'ID_ENTIDAD' => $id_entidad,
                                    )
                            );
                        }
                    } else {
                        DB::table('APS_CONCEPTO_PLANI_CUENTAFICHA')->insert(
                            array('ID_CUENTAAASI' => $id_cuentaaasi,
                                'ID_TIPOPLAN' => $id_tipoplan,
                                'ID_RESTRICCION' => $id_restriccion,
                                'ID_FONDO' => $fondo,
                                'SISTEMA' => $sistema,
                                'ID_PLAN_FICHAFINANCIERA' => $id_plan_fichafinanciera,
                                'ID_ENTIDAD' => $id_entidad,
                                )
                        );
                    }

                }else{
                        $ret="La cuenta con ese tipo plan y fondo ya se encuentra registrado";
                }
            }

        return $ret;

    }

    public static function deletePayrollConceptAccountTab($id_cuentaaasi, $request){
        $ret='OK';
        $id_tipoplan = $request->id_tipoplan;
        $id_restriccion = $request->id_restriccion;
        $sistema = $request->sistema;
        $id_plan_fichafinanciera = $request->id_plan_fichafinanciera;
        $id_entidad = $request->id_entidad;

        DB::table('APS_CONCEPTO_PLANI_CUENTAFICHA')
        ->where('ID_CUENTAAASI','=',$id_cuentaaasi)
        ->where('ID_TIPOPLAN','=',$id_tipoplan)
        ->where('ID_RESTRICCION','=',$id_restriccion)
        ->where('SISTEMA','=',$sistema)
        ->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera)
        ->where('ID_ENTIDAD','=',$id_entidad)
        ->delete();
        return $ret;
    }
    public static function getFinancialStatementPlan($request)
    {
        $id_entidad = $request->query('id_entidad');
        $search = $request->query('search');
        $pageSize = $request->query('pageSize');
        $query = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
            ->select('ID_PLAN_FICHAFINANCIERA','DESCRIPCION','MES_INICIO','ANHO_INICIO', 'MES_FIN', 'ANHO_FIN', 'ID_ENTIDAD')
            ->where('ID_ENTIDAD','=',$id_entidad)
            ->whereRaw("(UPPER(DESCRIPCION) LIKE UPPER('%{$search}%'))")
            ->OrderBy('ID_PLAN_FICHAFINANCIERA','DESC');
            if($pageSize > 0) {
                $query = $query->paginate($pageSize);
            } else {
                $query = $query->get();
            }
            
            foreach($query as $item){
                $quantities = DB::table('APS_CONCEPTO_PLANI_CUENTAFICHA')
                ->select(
                    DB::raw("NVL(count(ID_PLAN_FICHAFINANCIERA), 0) AS CANT_CONCEPTO")
                )
                ->where('ID_PLAN_FICHAFINANCIERA','=',$item->id_plan_fichafinanciera)
                ->get();
                foreach($quantities as $data){
                    $item->cant_concepto = (int)$data->cant_concepto;
                }
            }

        
        return $query;
    }
    public static function copyFinancialStatement($request) {
        $ret='OK';
        $id_entidad_origen = $request->id_entidad_origen;
        $id_entidad_destino = $request->id_entidad_destino;
        $id_plan_fichafinanciera_origen = $request->id_plan_fichafinanciera_origen;
        $id_plan_fichafinanciera_destino = $request->id_plan_fichafinanciera_destino;
        DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA')
        ->where('id_entidad','=', $id_entidad_destino)
            ->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera_destino)
            ->delete();
        $result = DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA')
        ->where('id_entidad','=', $id_entidad_origen)
        ->where('id_plan_fichafinanciera','=', $id_plan_fichafinanciera_origen)
        ->get();
        foreach($result as $item){
            DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA')->insert(
                array('ID_CUENTAAASI' => $item->id_cuentaaasi,
                    'ID_TIPOPLAN' => $item->id_tipoplan,
                    'ID_RESTRICCION' => $item->id_restriccion,
                    'ID_FONDO' => $item->id_fondo,
                    'ID_CONCEPTOAPS' => $item->id_conceptoaps,
                    'ID_PLAN_FICHAFINANCIERA' => $id_plan_fichafinanciera_destino,
                    'ID_ENTIDAD' => $id_entidad_destino,
                    'SISTEMA' => $item->sistema
                    )
            );
        }
        
        return $ret;
    }
    public static function addFinancialStatementPlan($request) {
        $ret='OK';
        $descripcion = $request->descripcion;
        $id_entidad = $request->id_entidad;
        $mes_inicio = $request->mes_inicio;
        $anho_inicio = $request->anho_inicio;
        $mes_fin = $request->mes_fin;
        $anho_fin = $request->anho_fin;
        $id_plan_fichafinanciera = 0;
        $count=0;
        if($anho_fin and $mes_fin){
            $count = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
            ->where('ID_ENTIDAD','=', $id_entidad)
            ->whereRaw("((TO_NUMBER($anho_inicio||LPAD($mes_inicio,2,0)) BETWEEN TO_NUMBER(ANHO_INICIO||LPAD(MES_INICIO,2,0)) AND TO_NUMBER(ANHO_FIN||LPAD(MES_FIN,2,0))) 
            OR (TO_NUMBER($anho_fin||LPAD($mes_fin,2,0)) BETWEEN TO_NUMBER(ANHO_INICIO||LPAD(MES_INICIO,2,0)) AND TO_NUMBER(ANHO_FIN||LPAD(MES_FIN,2,0))))")
            ->count();
        }else{
            $count = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
            ->where('ID_ENTIDAD','=', $id_entidad)
            ->whereRaw("TO_NUMBER($anho_inicio||LPAD($mes_inicio,2,0)) BETWEEN TO_NUMBER(ANHO_INICIO||LPAD(MES_INICIO,2,0)) AND TO_NUMBER(ANHO_FIN||LPAD(MES_FIN,2,0))")
            ->count();
        }
       
        if($count<=0){
            $query = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
                ->where('ID_ENTIDAD','=',$id_entidad)
                ->max('ID_PLAN_FICHAFINANCIERA');
            if ($query) {
                $table = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
                ->where('ID_PLAN_FICHAFINANCIERA','=', $query)
                ->first();
                if ($table and is_null($table->mes_fin) and is_null($table->anho_fin)) {
                    DB::table('APS_PLAN_FICHAFINANCIERA')->where('ID_PLAN_FICHAFINANCIERA','=', $query)->update(
                        array('MES_FIN' => $mes_inicio==12?1:$mes_inicio-1,
                            'ANHO_FIN' => $mes_inicio==12?$anho_inicio-1:$anho_inicio,
                            )
                    );
                }
            }

            DB::table('APS_PLAN_FICHAFINANCIERA')->insert(
                array('DESCRIPCION' => $descripcion,
                    'MES_INICIO' => $mes_inicio,
                    'ANHO_INICIO' => $anho_inicio,
                    'MES_FIN' => $mes_fin,
                    'ANHO_FIN' => $anho_fin,
                    'ID_ENTIDAD' => $id_entidad,
                    )
            );
        }else{
            $ret="Ya existe un plan de ficha financiera dentro de éste rango de fechas.";
        }
        
        return $ret;
    }
    public static function editFinancialStatementPlan($id_plan_fichafinanciera, $request) {
        $ret='OK';
        $descripcion = $request->descripcion;
        $id_entidad = $request->id_entidad;
        $mes_inicio = $request->mes_inicio;
        $anho_inicio = $request->anho_inicio;
        $mes_fin = $request->mes_fin;
        $anho_fin = $request->anho_fin;
        $count=0;
        if($anho_fin and $mes_fin){
            $count = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
            ->where('ID_ENTIDAD','=', $id_entidad)
            ->where('ID_PLAN_FICHAFINANCIERA','<>', $id_plan_fichafinanciera)
            ->whereRaw("((TO_NUMBER($anho_inicio||LPAD($mes_inicio,2,0)) BETWEEN TO_NUMBER(ANHO_INICIO||LPAD(MES_INICIO,2,0)) AND TO_NUMBER(ANHO_FIN||LPAD(MES_FIN,2,0))) 
            OR (TO_NUMBER($anho_fin||LPAD($mes_fin,2,0)) BETWEEN TO_NUMBER(ANHO_INICIO||LPAD(MES_INICIO,2,0)) AND TO_NUMBER(ANHO_FIN||LPAD(MES_FIN,2,0))))")
            ->count();
        }else{
            $count = DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
            ->where('ID_PLAN_FICHAFINANCIERA','<>', $id_plan_fichafinanciera)
            ->where('ID_ENTIDAD','=', $id_entidad)
            ->whereRaw("TO_NUMBER($anho_inicio||LPAD($mes_inicio,2,0)) BETWEEN TO_NUMBER(ANHO_INICIO||LPAD(MES_INICIO,2,0)) AND TO_NUMBER(ANHO_FIN||LPAD(MES_FIN,2,0))")
            ->count();
        }
        if($count<=0){
            DB::table('APS_PLAN_FICHAFINANCIERA')->where('ID_PLAN_FICHAFINANCIERA','=', $id_plan_fichafinanciera)->update(
                array(
                    'DESCRIPCION' => $descripcion,
                    'MES_INICIO' => $mes_inicio,
                    'ANHO_INICIO' => $anho_inicio,
                    'MES_FIN' => $mes_fin,
                    'ANHO_FIN' => $anho_fin,
                    'ID_ENTIDAD' => $id_entidad,
                    )
            );
        }else{
            $ret="Ya existe un plan de ficha financiera dentro de éste rango de fechas.";
        }
        
        return $ret;
    }
    public static function deleteFinancialStatementPlan($id_plan_fichafinanciera){
        $msg='Se eliminó correctamente.';
        $count = DB::table('ELISEO.APS_CONCEPTO_PLANI_CUENTAFICHA')
                ->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera)
                ->count();
        if($count<=0){
            DB::table('ELISEO.APS_PLAN_FICHAFINANCIERA')
            ->where('ID_PLAN_FICHAFINANCIERA','=',$id_plan_fichafinanciera)
            ->delete();
        }else{
            $msg='No se puede eliminar, primero tiene que eliminar la ficha financiera que utilize este plan.';
        }
        return $msg;
    }
}


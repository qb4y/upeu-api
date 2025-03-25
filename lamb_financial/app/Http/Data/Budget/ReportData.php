<?php
namespace App\Http\Data\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class ReportData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listPregradoProyeccion($id_entidad,$id_depto,$id_auxiliar,$id_area,$id_anho){
        $query = "SELECT
                    M.TIPO,
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO ORDER BY M.CICLO)=1 OR M.TIPO='T' THEN M.ID_DEPTO ELSE '' END AS ID_DEPTO,
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO ORDER BY M.CICLO)=1 OR M.TIPO='T' THEN M.NOMBRE ELSE '' END AS NOMBRE,
                    M.CICLO,
                    M.CANTIDAD_I,
                    M.HORAS_I,
                    M.CREDITO_I,
                    M.TOTAL_CREDITO_I,
                    M.CANTIDAD_II,
                    M.HORAS_II,
                    M.CREDITO_II,
                    M.TOTAL_CREDITO_II
                FROM(
                    SELECT 
                        'R' AS TIPO,
                        P.ID_DEPTO,
                        N.DEPARTAMENTO AS NOMBRE,
                        P.CICLO,
                        P.CANTIDAD_I,
                        P.HORAS_I,
                        P.CREDITO_I,
                        P.CANTIDAD_I*P.CREDITO_I AS TOTAL_CREDITO_I,
                        P.CANTIDAD_II,
                        P.HORAS_II,
                        P.CREDITO_II,
                        P.CANTIDAD_II*P.CREDITO_II AS TOTAL_CREDITO_II
                    FROM PSTO_PREGRADO_PROYECCCION P,VW_AREA_DEPTO N
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND N.ID_AREA_PADRE= ".$id_area."
                    AND P.ID_DEPTO_PADRE='".$id_depto."'
                    UNION ALL
                    SELECT
                        'T' AS TIPO,
                        P.ID_DEPTO,
                        'TOTAL: ' AS NOMBRE,
                        20 AS CICLO,
                        SUM(P.CANTIDAD_I) AS CANTIDAD_I,
                        SUM(P.HORAS_I) AS HORAS_I,
                        0 AS CREDITO_I,
                        SUM(P.CANTIDAD_I*P.CREDITO_I) AS TOTAL_CREDITO_I,
                        SUM(P.CANTIDAD_II) AS CANTIDAD_II,
                        SUM(P.HORAS_II) AS HORAS_II ,
                        0 AS CREDITO_II,
                        SUM(P.CANTIDAD_II*P.CREDITO_II) AS TOTAL_CREDITO_II
                    FROM PSTO_PREGRADO_PROYECCCION P,VW_AREA_DEPTO N
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND N.ID_AREA_PADRE= ".$id_area."
                    AND P.ID_DEPTO_PADRE='".$id_depto."'
                    GROUP BY P.ID_DEPTO,
                    N.DEPARTAMENTO
                    UNION ALL
                    SELECT 
                        'T' AS TIPO,
                        '999' AS ID_DEPTO,
                        'TOTAL GENERAL:' AS NOMBRE,
                        25 AS CICLO,
                        SUM(P.CANTIDAD_I) AS CANTIDAD_I,
                        SUM(P.HORAS_I) AS HORAS_I,
                        0 AS CREDITO_I,
                        SUM(P.CANTIDAD_I*P.CREDITO_I) AS TOTAL_CREDITO_I,
                        SUM(P.CANTIDAD_II) AS CANTIDAD_II,
                        SUM(P.HORAS_II) AS HORAS_II ,
                        0 AS CREDITO_II,
                        SUM(P.CANTIDAD_II*P.CREDITO_II) AS TOTAL_CREDITO_II
                    FROM PSTO_PREGRADO_PROYECCCION P,VW_AREA_DEPTO N
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND N.ID_AREA_PADRE= ".$id_area."
                    AND P.ID_DEPTO_PADRE='".$id_depto."'
                )M
                ORDER BY M.ID_DEPTO,M.CICLO ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPregradoProceso($id_entidad,$id_auxiliar,$id_depto,$id_anho,$id_area){
        $where="";
        if(strlen($id_area)>0){
            $where=" AND N.ID_AREA_PADRE=".$id_area." ";
        }
        $query="SELECT 
                    M.ID_PREGRADO_PROCESO,
                    M.ID_DEPTO,
                    M.NOMBRE||' '||M.CONVOCA AS NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.CREDITO_2_5,
                    M.TOTALCREDITO_1_I,
                    M.TOTALCREDITO_2_5_I,
                    M.TOTAL_ALUMNO_I,
                    M.TOTALCREDITO_1_II,
                    M.TOTALCREDITO_2_5_II,
                    M.TOTAL_ALUMNO_II,
                    M.MAT_I,
                    M.ENSENANZA_I,
                    M.MAT_II,
                    M.ENSENANZA_II,
                    M.MAT_I+M.MAT_II AS MAT,
                    M.ENSENANZA_I+M.ENSENANZA_II AS ENSENANZA,
                    M.MAT_I+M.MAT_II+M.ENSENANZA_I+M.ENSENANZA_II AS TOTAL
                FROM(
                  SELECT
                    P.ID_PREGRADO_PROCESO,
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.CREDITO_2_5,
                    P.TOTALCREDITO_1_I,
                    P.TOTALCREDITO_1_II,
                    P.TOTALCREDITO_2_5_I,
                    P.TOTALCREDITO_2_5_II,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.CONVOCA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS MAT_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS MAT_II,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_I ELSE 0 END) AS ENSENANZA_I,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_II ELSE 0 END) AS ENSENANZA_II
                  FROM PSTO_PREGRADO_PROCESO P,VW_AREA_DEPTO N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  AND P.ID_DEPTO_PADRE='".$id_depto."'
                  ".$where."
                  GROUP BY P.ID_DEPTO,N.DEPARTAMENTO,P.ARMADA,P.IMP_MAT,P.CREDITO_1,P.TOTAL_ALUMNO_I,P.TOTAL_ALUMNO_II,P.CONVOCA,P.CREDITO_2_5,P.TOTALCREDITO_1_I,
                  P.TOTALCREDITO_1_II,
                  P.TOTALCREDITO_2_5_I,
                  P.TOTALCREDITO_2_5_II,
                  P.ID_PREGRADO_PROCESO
                )M
              ORDER BY M.ID_DEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listProesadProyeccion($id_entidad,$id_auxiliar,$id_depto,$id_anho){
        $query = "SELECT
                    M.TIPO,
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA ORDER BY M.ID_EAP_DEPTO_AREA,M.CICLO)=1 OR M.TIPO='T' THEN M.ID_DEPTO ELSE '' END AS ID_DEPTO,
                    CASE WHEN ROW_NUMBER() OVER (PARTITION BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA ORDER BY M.ID_EAP_DEPTO_AREA,M.CICLO)=1 OR M.TIPO='T' THEN M.NOMBRE ELSE '' END AS NOMBRE,
                    M.CICLO,
                    M.ID_EAP_DEPTO_AREA,
                    M.CANTIDAD_I,
                    M.HORAS_I,
                    M.CANTIDAD_II,
                    M.HORAS_II
                FROM(
                    SELECT 
                        'R' AS TIPO,
                        P.ID_DEPTO,
                        E.ID_EAP_DEPTO_AREA,
                        E.NOMBRE,
                        P.CICLO,
                        P.CANTIDAD_I,
                        P.CANTIDAD_II,
                        P.HORAS_I,
                        P.HORAS_II
                    FROM PSTO_PREGRADO_PROYECCCION P,CONTA_ENTIDAD_DEPTO N,VW_DPTO_EAP E
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_EAP_DEPTO_AREA=E.ID_EAP_DEPTO_AREA
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND P.ID_DEPTO='".$id_depto."'
                    UNION ALL
                    SELECT 
                        'T' AS TIPO,
                        P.ID_DEPTO,
                        E.ID_EAP_DEPTO_AREA,
                        'TOTAL:' AS NOMBRE,
                        20 AS CICLO,
                        SUM(P.CANTIDAD_I) AS CANTIDAD_I ,
                        SUM(P.CANTIDAD_II) AS CANTIDAD_II,
                        SUM(P.HORAS_I) AS HORAS_I,
                        SUM(P.HORAS_II) AS HORAS_II
                    FROM PSTO_PREGRADO_PROYECCCION P,CONTA_ENTIDAD_DEPTO N,VW_DPTO_EAP E
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_EAP_DEPTO_AREA=E.ID_EAP_DEPTO_AREA
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND P.ID_DEPTO='".$id_depto."'
                    GROUP BY P.ID_DEPTO,E.ID_EAP_DEPTO_AREA
                    UNION ALL
                    SELECT 
                        'T' AS TIPO,
                        '999' AS ID_DEPTO,
                        99 AS ID_EAP_DEPTO_AREA,
                        'TOTAL GENERAL:' AS NOMBRE,
                        25 AS CICLO,
                        SUM(P.CANTIDAD_I) AS CANTIDAD_I ,
                        SUM(P.CANTIDAD_II) AS CANTIDAD_II,
                        SUM(P.HORAS_I) AS HORAS_I,
                        SUM(P.HORAS_II) AS HORAS_II
                    FROM PSTO_PREGRADO_PROYECCCION P,CONTA_ENTIDAD_DEPTO N,VW_DPTO_EAP E
                    WHERE P.ID_ENTIDAD=N.ID_ENTIDAD
                    AND P.ID_DEPTO=N.ID_DEPTO
                    AND P.ID_EAP_DEPTO_AREA=E.ID_EAP_DEPTO_AREA
                    AND P.ID_AUXILIAR=".$id_auxiliar."
                    AND P.ID_ENTIDAD=".$id_entidad."
                    AND P.ID_ANHO=".$id_anho."
                    AND P.ID_DEPTO='".$id_depto."'
                    ORDER BY ID_DEPTO,ID_EAP_DEPTO_AREA,CICLO
                )M
                ORDER BY M.ID_DEPTO,M.ID_EAP_DEPTO_AREA,M.CICLO ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listProesadProceso($id_entidad,$id_auxiliar,$id_depto,$id_anho){
        $query="SELECT 
                    M.ID_DEPTO,
                    M.NOMBRE,
                    M.ARMADA,
                    M.IMP_MAT,
                    M.CREDITO_1,
                    M.TOTAL_ALUMNO_I,
                    M.TOTAL_ALUMNO_II,
                    M.MAT_I,
                    M.ENSENANZA_I,
                    M.MAT_II,
                    M.ENSENANZA_II,
                    M.MAT_I+M.MAT_II AS MAT,
                    M.ENSENANZA_I+M.ENSENANZA_II AS ENSENANZA,
                    M.MAT_I+M.MAT_II+M.ENSENANZA_I+M.ENSENANZA_II AS TOTAL
                FROM(
                  SELECT 
                    P.ID_DEPTO,
                    N.DEPARTAMENTO AS NOMBRE,
                    P.ARMADA,
                    P.IMP_MAT,
                    P.CREDITO_1,
                    P.TOTAL_ALUMNO_I,
                    P.TOTAL_ALUMNO_II,
                    P.CONVOCA,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_I ELSE 0 END) AS MAT_I,
                    SUM(CASE WHEN C.TIPO='M' THEN D.TOTAL_II ELSE 0 END) AS MAT_II,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_I ELSE 0 END) AS ENSENANZA_I,
                    SUM(CASE WHEN C.TIPO='E' THEN D.TOTAL_II ELSE 0 END) AS ENSENANZA_II
                  FROM PSTO_PREGRADO_PROCESO P,VW_AREA_DEPTO N,PSTO_PREGRADO_PROCESO_CONCEPTO D,PSTO_PREGRADO_CONCEPTO_PRECIO C
                  WHERE P.ID_PREGRADO_PROCESO=D.ID_PREGRADO_PROCESO
                  AND  D.ID_CONCEPTO_PRECIO=C.ID_CONCEPTO_PRECIO
                  AND P.ID_ENTIDAD=N.ID_ENTIDAD
                  AND P.ID_DEPTO=N.ID_DEPTO
                  AND P.ID_AUXILIAR=".$id_auxiliar."
                  AND P.ID_ENTIDAD=".$id_entidad."
                  AND P.ID_ANHO=".$id_anho."
                  AND P.ID_DEPTO_PADRE='".$id_depto."'
                  GROUP BY P.ID_DEPTO,N.DEPARTAMENTO,P.ARMADA,P.IMP_MAT,P.CREDITO_1,P.TOTAL_ALUMNO_I,P.TOTAL_ALUMNO_II,P.CONVOCA
                )M
              ORDER BY M.CONVOCA DESC,M.ID_DEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listResultado($id_entidad,$id_anho,$id_area,$id_proyecto ,$id_evento,$id_pstonegocio,$id_eje,$estado,$id_depto,$id_depto_padre,$id_area_padre,$mes_ini,$mes_fin){
        
        $where="";
        
        if(strlen($id_depto)>0){
            $where.=" and b.id_depto='".$id_depto."' ";
                            
        }else{
            if(strlen($id_area)>0){
                $where.=" and f.id_area=".$id_area." ";
            }else{
                if(strlen($id_area_padre)>0){
                    $where.=" and f.id_area_padre=".$id_area_padre." ";
                }
            }
        }
        if(strlen($id_proyecto)>0){
            $where.=" and e.id_proyecto=".$id_proyecto;
        }
        if(strlen($id_pstonegocio)>0){
            $where.=" and d.id_pstonegocio=".$id_pstonegocio;
        }
        if(strlen($id_eje)>0){
            $where.=" and d.id_eje=".$id_eje;
        }
        if(strlen($estado)>0){
           
            $where.=" and d.estado='".$estado."' ";
        }
        if(strlen($id_evento)>0){
            $where.=" and d.id_evento=".$id_evento;
        }
        
        if((strlen($mes_ini)>0) and (strlen($mes_fin)>0)) {
            $where.=" and b.id_mes>=".$mes_ini." and b.id_mes<=".$mes_fin;
        }else{
            if(strlen($mes_ini)>0){
                $where.=" and b.id_mes=".$mes_ini;
            }
            if(strlen($mes_fin)>0){
                $where.=" and b.id_mes<=".$mes_fin;
            }
        }
        
        if(strlen($id_evento)>0){
            $where.=" and d.id_evento=".$id_evento;
        }
        
        $query="select 
                    b.id_depto
                from psto_asiento a, psto_asiento_det b,conta_cta_denominacional c,psto_presupuesto d,psto_evento e,vw_area_depto f
                where a.id_asiento=b.id_asiento
                and b.id_tipoplan=c.id_tipoplan
                and b.id_cuentaaasi=c.id_cuentaaasi
                and b.id_restriccion=c.id_restriccion
                and a.id_presupuesto=d.id_presupuesto
                and d.id_evento=e.id_evento
                and b.id_entidad=f.id_entidad
                and b.id_depto=f.id_depto
                and a.id_entidad=".$id_entidad."  
                and a.id_anho=".$id_anho." 
                and f.id_depto_area='".$id_depto_padre."'
                and d.estado in('1','2')
                ".$where."
                group by b.id_depto
                order by b.id_depto";
        $dta = DB::select($query);
        $id_depto="''";
        $j=0;
        foreach($dta as $row){
            if($j==0){
                $id_depto="'".$row->id_depto."'";
            }else{
                $id_depto.=",'".$row->id_depto."'";
            }
            
            $j++;
        }

        $query="SELECT *  FROM(
                    select 
                        'A' as tipo,
                        b.id_tipoplan,
                        b.id_cuentaaasi,
                        b.id_restriccion,
                        b.id_ctacte,
                        b.id_depto,
                        b.glosa,
                        c.nombre as rubro,
                        case when b.id_ctacte is null then b.id_cuentaaasi else b.id_cuentaaasi||'/'||b.id_ctacte end as cuentaaasictacte,
                        sum(b.importe) as importe   
                    from psto_asiento a, psto_asiento_det b,conta_cta_denominacional c,psto_presupuesto d,psto_evento e,vw_area_depto f
                    where a.id_asiento=b.id_asiento
                    and b.id_tipoplan=c.id_tipoplan
                    and b.id_cuentaaasi=c.id_cuentaaasi
                    and b.id_restriccion=c.id_restriccion
                    and a.id_presupuesto=d.id_presupuesto
                    and d.id_evento=e.id_evento
                    and b.id_entidad=f.id_entidad
                    and b.id_depto=f.id_depto
                    and d.estado in('1','2')
                    and a.id_entidad=".$id_entidad."  
                    and a.id_anho=".$id_anho." 
                    and f.id_depto_area='".$id_depto_padre."' 
                    ".$where." 
                    and b.id_depto in(".$id_depto.")
                    group by b.id_tipoplan,b.id_cuentaaasi,b.id_restriccion,b.id_ctacte,b.glosa,c.nombre,b.id_depto
                    order by b.id_tipoplan,b.id_cuentaaasi,b.id_restriccion,b.id_ctacte,b.id_depto,
                    case when b.id_ctacte is null then b.id_cuentaaasi else b.id_cuentaaasi||'/'||b.id_ctacte end
                    )PIVOT  (
                    sum(importe) 
                    FOR id_depto IN(".$id_depto.")
                    )
                union all
                SELECT *  FROM(
                    select 
                        'B' as tipo,
                        0 as id_tipoplan,
                        '' as id_cuentaaasi,
                        '' as id_restriccion,
                        '' as id_ctacte,
                        b.id_depto,
                        '' as glosa, 
                        'INGRESO' as rubro,
                        '' as cuentaaasictacte,
                        sum(case when b.tipo='I' then  b.importe else 0 end ) as importe   
                    from psto_asiento a, psto_asiento_det b,conta_cta_denominacional c,psto_presupuesto d,psto_evento e,vw_area_depto f
                    where a.id_asiento=b.id_asiento
                    and b.id_tipoplan=c.id_tipoplan
                    and b.id_cuentaaasi=c.id_cuentaaasi
                    and b.id_restriccion=c.id_restriccion
                    and a.id_presupuesto=d.id_presupuesto
                    and d.id_evento=e.id_evento
                    and b.id_entidad=f.id_entidad
                    and b.id_depto=f.id_depto
                    and d.estado in('1','2')
                    and a.id_entidad=".$id_entidad."  
                    and a.id_anho=".$id_anho." 
                    and f.id_depto_area='".$id_depto_padre."' 
                    ".$where." 
                    and b.id_depto in(".$id_depto.")
                    group by b.id_depto
                    order by b.id_depto
                    )PIVOT (
                    sum(importe)
                    FOR id_depto IN(".$id_depto.")
                    )
                union all
                SELECT * FROM(
                    select 
                        'C' as tipo,
                        0 as id_tipoplan,
                        '' as id_cuentaaasi,
                        '' as id_restriccion,
                        '' as id_ctacte,
                        b.id_depto,
                        '' as glosa, 
                        'EGRESOS' as rubro,
                        '' as cuentaaasictacte,
                        sum(case when b.tipo='G' then  b.importe else 0 end ) as importe   
                    from psto_asiento a, psto_asiento_det b,conta_cta_denominacional c,psto_presupuesto d,psto_evento e,vw_area_depto f
                    where a.id_asiento=b.id_asiento
                    and b.id_tipoplan=c.id_tipoplan
                    and b.id_cuentaaasi=c.id_cuentaaasi
                    and b.id_restriccion=c.id_restriccion
                    and a.id_presupuesto=d.id_presupuesto
                    and d.id_evento=e.id_evento
                    and b.id_entidad=f.id_entidad
                    and b.id_depto=f.id_depto
                    and d.estado in('1','2')
                    and a.id_entidad=".$id_entidad."  
                    and a.id_anho=".$id_anho." 
                    and f.id_depto_area='".$id_depto_padre."' 
                    ".$where." 
                    and b.id_depto in(".$id_depto.")
                    group by b.id_depto
                    order by b.id_depto
                    )PIVOT (
                    sum(importe)
                    FOR id_depto IN(".$id_depto.")
                    )
                    union all
                SELECT * FROM(
                    select 
                        'D' as tipo,
                        0 as id_tipoplan,
                        '' as id_cuentaaasi,
                        '' as id_restriccion,
                        '' as id_ctacte,
                        b.id_depto,
                        '' as glosa, 
                        'RESULTADO' as rubro,
                        '' as cuentaaasictacte,
                        sum(case when b.tipo='I' then  b.importe else 0 end )-sum(case when b.tipo='G' then  b.importe else 0 end ) as importe   
                    from psto_asiento a, psto_asiento_det b,conta_cta_denominacional c,psto_presupuesto d,psto_evento e,vw_area_depto f
                    where a.id_asiento=b.id_asiento
                    and b.id_tipoplan=c.id_tipoplan
                    and b.id_cuentaaasi=c.id_cuentaaasi
                    and b.id_restriccion=c.id_restriccion
                    and a.id_presupuesto=d.id_presupuesto
                    and d.id_evento=e.id_evento
                    and b.id_entidad=f.id_entidad
                    and b.id_depto=f.id_depto
                    and d.estado in('1','2')
                    and a.id_entidad=".$id_entidad."  
                    and a.id_anho=".$id_anho." 
                    and f.id_depto_area='".$id_depto_padre."' 
                    ".$where." 
                    and b.id_depto in(".$id_depto.")
                    group by b.id_depto
                    order by b.id_depto
                    )PIVOT (
                    sum(importe)
                    FOR id_depto IN(".$id_depto.")
                    )";
        $oQuery = DB::select($query); 
        
        $datos=array();
        foreach($oQuery as  $key =>  $item){
            $dep = (array)$item;
            
            $fila=array();
            $fila["tipo"]=$item->tipo;
            $fila["id_tipoplan"]=$item->id_tipoplan;
            $fila["id_cuentaaasi"]=$item->id_cuentaaasi;
            $fila["id_restriccion"]=$item->id_restriccion;
            $fila["id_ctacte"]=$item->id_ctacte;
            $fila["cuentaaasictacte"]=$item->cuentaaasictacte;
            $fila["glosa"]=$item->glosa;
            $fila["rubro"]=$item->rubro;
            $child=array();
            $total=0;
            foreach($dta as $row){
                $child[$row->id_depto]=$dep["'".$row->id_depto."'"];
                $total=$total + $dep["'".$row->id_depto."'"];
            }
            $fila["total"]=$total;
            $fila["children"]=$child;
                    
            $datos[]=$fila;
        }
        
        return $datos;
    }
    
    public static function listResultadoCab($id_entidad,$id_anho,$id_area,$id_proyecto ,$id_evento,$id_pstonegocio,$id_eje,$estado,$id_depto,$id_depto_padre,$id_area_padre,$mes_ini,$mes_fin){
        
        $where="";
        
        if(strlen($id_depto)>0){
            $where.=" and b.id_depto='".$id_depto."' ";
                            
        }else{
            if(strlen($id_area)>0){
                $where.=" and f.id_area=".$id_area." ";
            }else{
                if(strlen($id_area_padre)>0){
                    $where.=" and f.id_area_padre=".$id_area_padre." ";
                }
            }
        }
        if(strlen($id_proyecto)>0){
            $where.=" and e.id_proyecto=".$id_proyecto;
        }
        if(strlen($id_pstonegocio)>0){
            $where.=" and d.id_pstonegocio=".$id_pstonegocio;
        }
        if(strlen($id_eje)>0){
            $where.=" and d.id_eje=".$id_eje;
        }
        if(strlen($estado)>0){
           
            $where.=" and d.estado='".$estado."' ";
        }
        if(strlen($id_evento)>0){
            $where.=" and d.id_evento=".$id_evento;
        }
        if((strlen($mes_ini)>0) and (strlen($mes_fin)>0)) {
            $where.=" and b.id_mes>=".$mes_ini." and b.id_mes<=".$mes_fin;
        }else{
            if(strlen($mes_ini)>0){
                $where.=" and b.id_mes=".$mes_ini;
            }
            if(strlen($mes_fin)>0){
                $where.=" and b.id_mes<=".$mes_fin;
            }
        }
        $query="select 
                    b.id_depto,
                    f.departamento as nombre
                from psto_asiento a, psto_asiento_det b,conta_cta_denominacional c,psto_presupuesto d,psto_evento e,vw_area_depto f
                where a.id_asiento=b.id_asiento
                and b.id_tipoplan=c.id_tipoplan
                and b.id_cuentaaasi=c.id_cuentaaasi
                and b.id_restriccion=c.id_restriccion
                and a.id_presupuesto=d.id_presupuesto
                and d.id_evento=e.id_evento
                and b.id_entidad=f.id_entidad
                and b.id_depto=f.id_depto
                and a.id_entidad=".$id_entidad."  
                and a.id_anho=".$id_anho." 
                and f.id_depto_area='".$id_depto_padre."'
                and d.estado in('1','2')
                ".$where."
                group by b.id_depto,f.departamento
                order by b.id_depto";
        $oQuery = DB::select($query);        
        return $oQuery;
        
        
    }
    public static function listSummaryPayroll($id_entidad,$id_depto,$id_anho){
        $query="SELECT 
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE*(-1) ELSE DI.IMPORTE END) AS RESULTADO
                FROM PSTO_PRESUPUESTO A,
                PSTO_PRESUPUESTO_DET D,
                PSTO_PRESUPUESTO_DET_DIST DI,
                PSTO_EVENTO E, 
                PSTO_PROYECTO P
                WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                AND A.ID_EVENTO=E.ID_EVENTO
                AND E.ID_PROYECTO=P.ID_PROYECTO
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO='".$id_depto."'
                AND A.ESTADO IN('1','2')";
        $datatotales = DB::select($query);  
        
        $query="SELECT 
                    A.ID_ANHO,
                    DI.ID_MES,
                    M.SIGLAS,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO 
                FROM PSTO_PRESUPUESTO A,
                PSTO_PRESUPUESTO_DET D,
                PSTO_PRESUPUESTO_DET_DIST DI,
                PSTO_EVENTO E, 
                PSTO_PROYECTO P,
                CONTA_MES M
                WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                AND A.ID_EVENTO=E.ID_EVENTO
                AND E.ID_PROYECTO=P.ID_PROYECTO
                AND DI.ID_MES=M.ID_MES
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO='".$id_depto."'
                AND A.ESTADO IN('1','2')
                GROUP BY A.ID_ANHO,DI.ID_MES,M.SIGLAS
                ORDER BY DI.ID_MES";
        $datmeses = DB::select($query);
        
        $datameses=array();
        $dataset=array();
        
        $serieingreso=array();
        $seriedescuento=array();
        $seriegasto=array();
        
        $labels=array();
        
        $ingreso["label"]="Ingreso";
        $ingreso["fill"]=false;
        $ingreso["lineTension"]=0.2;
        $ingreso["borderColor"]='#2E8B57';
        $ingreso["borderWidth"]=1;
        
        $descuento["label"]="Descuento";
        $descuento["fill"]=false;
        $descuento["lineTension"]=0.2;
        $descuento["borderColor"]='#9370DB';
        $descuento["borderWidth"]=1;
        
        $gasto["label"]="Gasto";
        $gasto["fill"]=false;
        $gasto["lineTension"]=0.2;
        $gasto["borderColor"]='#FF0000';
        $gasto["borderWidth"]=1;

        foreach($datmeses as $row){
            $labels[]=$row->siglas;
            $serieingreso[]=$row->ingreso*1;
            $seriedescuento[]=$row->descuento*1;
            $seriegasto[]=$row->gasto*1;
            
        }
        $ingreso["data"]=$serieingreso;
        $descuento["data"]=$seriedescuento;
        $gasto["data"]=$seriegasto;
        
        $dataset[]=$ingreso;
        $dataset[]=$descuento;
        $dataset[]=$gasto;
        
        $datameses["labels"]=$labels;
        $datameses["datasets"]=$dataset;
        
        $query="SELECT 
                    P.ID_PROYECTO,
                    P.NOMBRE,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE*(-1) ELSE DI.IMPORTE END) AS RESULTADO
                FROM PSTO_PRESUPUESTO A,
                PSTO_PRESUPUESTO_DET D,
                PSTO_PRESUPUESTO_DET_DIST DI,
                PSTO_EVENTO E, 
                PSTO_PROYECTO P
                WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                AND A.ID_EVENTO=E.ID_EVENTO
                AND E.ID_PROYECTO=P.ID_PROYECTO
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO='".$id_depto."'
                AND A.ESTADO IN('1','2')
                GROUP BY  P.ID_PROYECTO,P.NOMBRE
                ORDER BY P.ID_PROYECTO";
        $dataproyecto = DB::select($query);
        
        $query="SELECT 
                    DP.ID_AREA_PADRE,
                    DP.AREA_PADRE,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO ,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE*(-1) ELSE DI.IMPORTE END) AS RESULTADO
                FROM PSTO_PRESUPUESTO A,
                PSTO_PRESUPUESTO_DET D,
                PSTO_PRESUPUESTO_DET_DIST DI,
                PSTO_EVENTO E, 
                PSTO_PROYECTO P,
                VW_AREA_DEPTO DP
                WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                AND A.ID_EVENTO=E.ID_EVENTO
                AND DP.ID_DEPTO=D.ID_DEPTO_ASIENTO
                AND DP.ID_ENTIDAD=D.ID_ENTIDAD
                AND E.ID_PROYECTO=P.ID_PROYECTO
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO='".$id_depto."'
                AND A.ESTADO IN('1','2')
                GROUP BY  DP.ID_AREA_PADRE,DP.AREA_PADRE
                ORDER BY DP.ID_AREA_PADRE";
        $dataareapadre = DB::select($query);
        
        return $return=[
            'total'=>$datatotales,
            'month'=>$datameses,
            'proyect'=>$dataproyecto,
            'area'=>$dataareapadre
        ];
    }
    public static function listSummaryEvent($id_entidad,$id_depto,$id_anho,$id_proyecto){
        $query="SELECT 
                    E.ID_EVENTO,
                    E.NOMBRE,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                    SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO,
                    SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE*(-1) ELSE DI.IMPORTE END) AS RESULTADO
                FROM PSTO_PRESUPUESTO A,
                PSTO_PRESUPUESTO_DET D,
                PSTO_PRESUPUESTO_DET_DIST DI,
                PSTO_EVENTO E, 
                PSTO_PROYECTO P
                WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                AND A.ID_EVENTO=E.ID_EVENTO
                AND E.ID_PROYECTO=P.ID_PROYECTO
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO='".$id_depto."'
                AND P.ID_PROYECTO=".$id_proyecto."
                AND A.ESTADO IN('1','2')
                GROUP BY  E.ID_EVENTO,E.NOMBRE
                ORDER BY E.ID_EVENTO";
        $data = DB::select($query);  
        return $data;
    }
    public static function listSummarySubArea($id_entidad,$id_depto,$id_anho,$id_area_padre){
        $query="SELECT DP.ID_AREA,DP.AREA ,SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO ,
                SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE*(-1) ELSE DI.IMPORTE END) AS RESULTADO
                FROM PSTO_PRESUPUESTO A,
                PSTO_PRESUPUESTO_DET D,
                PSTO_PRESUPUESTO_DET_DIST DI,
                PSTO_EVENTO E, 
                PSTO_PROYECTO P,
                VW_AREA_DEPTO DP
                WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                AND A.ID_EVENTO=E.ID_EVENTO
                AND DP.ID_DEPTO=D.ID_DEPTO_ASIENTO
                AND DP.ID_ENTIDAD=D.ID_ENTIDAD
                AND E.ID_PROYECTO=P.ID_PROYECTO
                AND A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND P.ID_DEPTO='".$id_depto."'
                AND DP.ID_AREA_PADRE=".$id_area_padre."
                AND A.ESTADO IN('1','2')
                GROUP BY  DP.ID_AREA,DP.AREA
                ORDER BY DP.ID_AREA";
        $data = DB::select($query);  
        return $data;
    }
    public static function listSummaryDepartament($id_entidad,$id_depto,$id_anho,$id_area){
        $query="SELECT 
                        DP.ID_AREA,
                        DP.AREA,
                        DP.ID_DEPTO,
                        DP.DEPARTAMENTO ,
                        SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE>0 THEN DI.IMPORTE ELSE 0 END   ELSE 0 END) AS INGRESO ,
                        SUM(CASE WHEN D.TIPO='I' THEN CASE WHEN DI.IMPORTE<0 THEN DI.IMPORTE*(-1) ELSE 0 END   ELSE 0 END) AS DESCUENTO,
                        SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE ELSE 0 END) AS GASTO,
                        SUM(CASE WHEN D.TIPO='G' THEN DI.IMPORTE*(-1) ELSE DI.IMPORTE END) AS RESULTADO
                    FROM PSTO_PRESUPUESTO A,
                    PSTO_PRESUPUESTO_DET D,
                    PSTO_PRESUPUESTO_DET_DIST DI,
                    PSTO_EVENTO E, 
                    PSTO_PROYECTO P,
                    VW_AREA_DEPTO DP
                    WHERE A.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                    AND D.ID_PRESUPUESTO_DET=DI.ID_PRESUPUESTO_DET
                    AND D.ID_PRESUPUESTO=DI.ID_PRESUPUESTO
                    AND A.ID_EVENTO=E.ID_EVENTO
                    AND DP.ID_DEPTO=D.ID_DEPTO_ASIENTO
                    AND DP.ID_ENTIDAD=D.ID_ENTIDAD
                    AND E.ID_PROYECTO=P.ID_PROYECTO
                    AND A.ID_ENTIDAD=".$id_entidad."
                    AND A.ID_ANHO=".$id_anho."
                    AND P.ID_DEPTO='".$id_depto."'
                    AND DP.ID_AREA=".$id_area."
                    AND A.ESTADO IN('1','2')
                    GROUP BY  DP.ID_AREA_PADRE,DP.ID_AREA,DP.AREA,DP.ID_DEPTO,DP.DEPARTAMENTO
                    ORDER BY DP.ID_AREA_PADRE,DP.ID_AREA,DP.ID_DEPTO";
        $data = DB::select($query);  
        return $data;
    }
}
?>


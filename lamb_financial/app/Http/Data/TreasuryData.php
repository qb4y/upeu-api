<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/$second_year
 * Time: 4:12 PM
 */

namespace App\Http\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreasuryData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function user($person){
        $query = "SELECT
                    COALESCE(MAX(ID_TYPEADMIN),1) ID_TYPEADMIN,
                    COALESCE(MAX(ID_ENTIDAD),1) ID_ENTIDAD
                    FROM USERS_DETAILS
                    WHERE ID IN ($person)";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    
    public static function budget($year, $month, $entity, $department)
    {
        $query = "Select
        X.Ano,
        X.mes,
        case when sum(X.Previsto) > 0 or sum(X.Previsto) < 0 then to_char(sum(X.Previsto) ,'999,999,999.99') 
             when sum(X.Previsto) = 0 then ' ' else to_char(sum(X.Previsto) ,'999,999,999.99') end Previsto_mes,
        case when sum(X.Ingreso) > 0 or sum(X.Ingreso) < 0 then to_char(sum(X.Ingreso),'999,999,999.99') 
             when sum(X.Ingreso) = 0 then ' ' else to_char(sum(X.Ingreso) ,'999,999,999.99') end Ingreso_mes,
        case when sum(X.Realizado) > 0 or sum(X.Realizado) < 0 then to_char(sum(X.Realizado),'999,999,999.99') 
             when sum(X.Realizado) = 0 then ' ' else to_char(sum(X.Realizado),'999,999,999.99') end Realizado_mes
        from (
        select
            id_ANHO Ano,
            ID_MES mes,
            sum(COS_VALOR) Previsto,
            0 Ingreso,
            0 Realizado
        FROM VW_CONTA_PRESUPUESTO p
        where ID_DEPTO = $department
        and ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_CUENTAAASI like '6%'
        group by p.ID_ANHO,p.ID_MES
        Union
        select
            ID_ANHO Ano,
            ID_MES mes,
            0 Previsto,
             sum(COS_VALOR)*-1 Ingreso,
            0 Realizado
        from VW_CONTA_DIARIO
        where ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_DEPTO = $department
        and ID_CUENTAAASI like '3%'
        and ID_CUENTAAASI not like '3196%'
        group by ID_ANHO,ID_MES
        Union
        select
            ID_ANHO Ano,
            ID_MES mes,
            0 Previsto,
            0 Ingreso,
            sum(COS_VALOR) Realizado
        from VW_CONTA_DIARIO
        WHERE ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_DEPTO = $department
        and ID_CUENTAAASI like ('4%')
        group by ID_ANHO,ID_MES
        ) X
        group by X.Ano,X.Mes
        order by X.mes";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function totalBudget($year, $month, $entity, $department)
    {

        $query = "Select
                    ABS(sum(X.Saldo_Inicial)) Saldo_Inicial,
                    sum(X.Previsto) Previsto,
                    sum(X.Ingreso) Ingreso,
                    sum(X.Realizado) Realizado,
                    (sum(X.Saldo_Inicial)+sum(X.Previsto)+sum(X.Ingreso))-sum(X.Realizado) Saldo
        from (
        Select
            case when ID_CUENTAAASI = '2317005' and ID_DEPTO = '910111' then sum(COS_VALOR) else sum(COS_VALOR) end Saldo_Inicial,
            0 Previsto,
            0 Ingreso,
            0 Realizado
        from VW_CONTA_DIARIO
        where ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_DEPTO =  $department
        and ID_CUENTAAASI in ('2317001','2317005')
        GROUP BY ID_CUENTAAASI,ID_DEPTO
        Union
        Select
                0 Saldo_Inicial,
                sum(COS_VALOR) Previsto,
                0 Ingreso,
                0 Realizado
        FROM VW_CONTA_PRESUPUESTO p
        where ID_DEPTO = $department
        and ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_CUENTAAASI like '6%'
        Union
        select
            0 Saldo_Inicial,
            0 Previsto,
             sum(COS_VALOR)*-1 Ingreso,
            0 Realizado
        from VW_CONTA_DIARIO
        where ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_DEPTO = $department
        and ID_CUENTAAASI like '3%'
        and ID_CUENTAAASI not like '3196%'
        Union
        select
            0 Saldo_Inicial,
            0 Previsto,
            0 Ingreso,
            sum(COS_VALOR) Realizado
        from VW_CONTA_DIARIO
        WHERE ID_ENTIDAD = $entity
        and ID_ANHO = $year
        and ID_MES between 1 and $month
        and ID_DEPTO = $department
        and ID_CUENTAAASI like ('4%')
        ) X";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function detail($year, $month, $entity, $department)
    {

        $query = "SELECT
        ID_ANHO AS ANHO,
        ID_MES AS MES,
        TO_CHAR(FEC_ASIENTO,'DD/MM/YYYY') AS FECHA_ASIENTO,
        TO_CHAR(FEC_DIGITADO,'DD/MM/YYYY') AS FECHA_DIGITADO,
        TO_CHAR(FEC_CONTABILIZADO,'DD/MM/YYYY') AS FECHA_CONTABILIZADO,
        ID_DEPTO AS DPTO,
        ID_CUENTAAASI AS CTA,
        ID_CTACTE AS CTA_CORRENTE,
        ID_TIPOASIENTO AS TIPO,
        COD_AASI AS LOTE,
        NUM_AASI AS ITEM,
        CASE
            WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
            ELSE 0
        END AS DEBITO,
        CASE
            WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
            ELSE 0
        END AS CREDITO,
        CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
        COMENTARIO AS HISTORICO
        FROM VW_CONTA_DIARIO
        WHERE ID_ANHO = $year
        AND ID_MES BETWEEN NVL(1,0) and NVL($month,0)
        and ID_CUENTAAASI like ('4%')
        AND ID_ENTIDAD = $entity
        AND ID_DEPTO IN ($department) ";
        
        $getDetail = DB::select($query);
        
        return $getDetail;
    }
    
    public static function totalDetail($year, $month, $entity, $department)
    {
       $query = "SELECT
                    ced.ID_DEPTO as depto,
                    ced.NOM_DEPARTAMENTO as Nombre_depto,
                    case when sum(di.COS_VALOR) > 0 or sum(di.COS_VALOR) < 0 then to_char(sum(di.COS_VALOR),'999,999,999.99') 
                         when sum(di.COS_VALOR) = 0 then ' ' else to_char(sum(di.COS_VALOR),'999,999,999.99') end COS_VALOR_TOTAL,
                    CASE
                        WHEN sum(di.COS_VALOR) > 0 THEN ABS( CAST(-sum(di.COS_VALOR) AS DECIMAL( 9, 2 )))
                    ELSE 0 END AS DEBITO,
                    CASE
                        WHEN sum(di.COS_VALOR) < 0 THEN ABS( CAST(sum(di.COS_VALOR) AS DECIMAL( 9, 2 )))
                    ELSE 0 END AS CREDITO
                from VW_CONTA_DIARIO di,VW_CONTA_ENTIDAD_DEPTO ced
                where di.ID_ENTIDAD = ced.ID_ENTIDAD
                AND di.ID_DEPTO = ced.ID_DEPTO
                and di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_MES BETWEEN NVL(1,0) and NVL($month,0)
                and di.ID_DEPTO in ('$department')
                and (di.ID_CUENTAAASI like '3%' OR di.ID_CUENTAAASI like '4%')
                GROUP BY ced.ID_DEPTO,ced.NOM_DEPARTAMENTO";
        
        $oQuery = DB::select($query);

        return $oQuery[0];
    }
    
    public static function budgetUPN_summary($year, $month, $entity, $person)
    {
        $query = "Select               
                X.Dpto as departamento,
                ed.NOMBRE as descricao,
                vwpn.paterno||' '||
                case when nvl(substr(vwpn.nombre,0,instr(vwpn.nombre,' ',1,1)),'X') = 'X' then 
                substr(vwpn.nombre,instr(vwpn.nombre,' ',1,3) +1,length(vwpn.nombre) - instr(vwpn.nombre,' ',1,3)) else 
                substr(vwpn.nombre,0,instr(vwpn.nombre,' ',1,1)) end as responsable,
                X.Entidad,
                case when ABS(sum(X.Previsto_mes)) > 0 or ABS(sum(X.Previsto_mes)) < 0 then to_char(sum(X.Previsto_mes),'999,999,999.99') 
                     when ABS(sum(X.Previsto_mes)) = 0 then ' ' else to_char(ABS(sum(X.Previsto_mes)),'999,999,999.99') end Previsto_otro,
                ABS(sum(X.Previsto_mes)) Previsto_mes,
                ABS(sum(X.Realizado_mes)) Realizado_mes,
                ABS(sum(X.Previsto_mes-X.Realizado_mes)) Total_mes,
                sum(X.Saldo_Inicial) Saldo_Inicial,
                sum(X.Previsto) Previsto,
                sum(X.Ingreso) Ingreso,
                sum(X.Realizado) Realizado,
                sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))- ABS(sum(X.Realizado)) Total,
                round((ABS(sum(X.Realizado))/
                decode((sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))), 0 ,1,(sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))))
                )*100,2) porc
            from (
            select
                    ID_ENTIDAD Entidad,
                    ID_DEPTO Dpto,
                    sum(COS_VALOR) Previsto_mes,
                    0 Realizado_mes,
                    0 Saldo_Inicial,
                    0 Previsto,
                    0 Ingreso,
                    0 Realizado
            from VW_CONTA_PRESUPUESTO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and $month
            and ID_CUENTAAASI like '6%'
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                sum(COS_VALOR) Realizado_mes,
                0 Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and $month
            and (ID_CUENTAAASI like '3%' OR ID_CUENTAAASI like '4%')
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            Select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                0 Realizado_mes,
                case when ID_CUENTAAASI = '2317005' and ID_DEPTO = '910111' then sum(COS_VALOR*-1) else sum(COS_VALOR*-1) end Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI in ('2317001','2317005','2317010')
            group by ID_DEPTO,ID_ENTIDAD,ID_CUENTAAASI
            UNION
            select
                    ID_ENTIDAD Entidad,
                    ID_DEPTO Dpto,
                    0 Previsto_mes,
                    0 Realizado_mes,
                    0 Saldo_Inicial,
                    sum(COS_VALOR) Previsto,
                    0 Ingreso,
                    0 Realizado
            from VW_CONTA_PRESUPUESTO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI like '6%'
            group by ID_DEPTO,ID_ENTIDAD
            Union
            select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                0 Realizado_mes,
                0 Saldo_Inicial,
                0 Previsto,
                sum(COS_VALOR) Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and (
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '3%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 1
              )
              or 
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '4%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 1
              )
            )
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                0 Realizado_mes,
                0 Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                sum(COS_VALOR) Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and $month
            and (
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '3%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 0
              )
              or 
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '4%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 0
              )
            )
            group by ID_DEPTO,ID_ENTIDAD
            ) X
        INNER JOIN Conta_Entidad_Depto ed ON 
        X.Entidad = ed.ID_ENTIDAD
        and X.Dpto = ed.ID_DEPTO
        LEFT JOIN CONTA_ENTIDAD_DEPTO_RESP cedr ON
        X.Entidad = cedr.ID_ENTIDAD
        AND X.Dpto = cedr.ID_DEPTO
        LEFT JOIN MOISES.VW_PERSONA_NATURAL vwpn ON
        cedr.ID_PERSONA = vwpn.ID_PERSONA
        where cedr.ID_PERSONA = $person
        group by X.Dpto,X.Entidad,ed.NOMBRE,vwpn.paterno,vwpn.nombre
        order by ed.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function budgetUPN($year, $month, $entity)
    {
        $query = "Select               
                X.Dpto as Codigo,
                ed.NOMBRE as Nombre,
                vwpn.paterno||' '||
                case when nvl(substr(vwpn.nombre,0,instr(vwpn.nombre,' ',1,1)),'X') = 'X' then 
                substr(vwpn.nombre,instr(vwpn.nombre,' ',1,3) +1,length(vwpn.nombre) - instr(vwpn.nombre,' ',1,3)) else 
                substr(vwpn.nombre,0,instr(vwpn.nombre,' ',1,1)) end as Responsable,
                X.Entidad,
                ABS(sum(X.Previsto_mes)) Previsto_mes,
                sum(X.Realizado_mes) Realizado_mes,
                ABS(sum(X.Previsto_mes))-sum(X.Realizado_mes) Total_mes,
                sum(X.Saldo_Inicial) Saldo_Inicial,
                ABS(sum(X.Previsto)) Previsto,
                ABS(sum(X.Ingreso)) Ingreso,
                sum(X.Realizado) Realizado,
                (sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso)))- sum(X.Realizado) Total,
                round((sum(X.Realizado)/
                decode((sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))), 0 ,1,(sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))))
                )*100,2) Porcentaje
            from (
            select
                    ID_ENTIDAD Entidad,
                    ID_DEPTO Dpto,
                    sum(COS_VALOR) Previsto_mes,
                    0 Realizado_mes,
                    0 Saldo_Inicial,
                    0 Previsto,
                    0 Ingreso,
                    0 Realizado
            from VW_CONTA_PRESUPUESTO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and $month
            and ID_CUENTAAASI like '6%'
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                sum(COS_VALOR) Realizado_mes,
                0 Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and $month
            and (ID_CUENTAAASI like '3%' OR ID_CUENTAAASI like '4%')
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            Select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                0 Realizado_mes,
                case when ID_CUENTAAASI = '2317005' and ID_DEPTO = '910111' then sum(COS_VALOR*-1) else sum(COS_VALOR*-1) end Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI in ('2317001','2317005','2317010')
            group by ID_DEPTO,ID_ENTIDAD,ID_CUENTAAASI
            UNION
            select
                    ID_ENTIDAD Entidad,
                    ID_DEPTO Dpto,
                    0 Previsto_mes,
                    0 Realizado_mes,
                    0 Saldo_Inicial,
                    sum(COS_VALOR) Previsto,
                    0 Ingreso,
                    0 Realizado
            from VW_CONTA_PRESUPUESTO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI like '6%'
            group by ID_DEPTO,ID_ENTIDAD
            Union
            select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                0 Realizado_mes,
                0 Saldo_Inicial,
                0 Previsto,
                sum(COS_VALOR) Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and (
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '3%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 1
              )
              or 
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '4%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 1
              )
            )
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            select
                ID_ENTIDAD Entidad,
                ID_DEPTO Dpto,
                0 Previsto_mes,
                0 Realizado_mes,
                0 Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                sum(COS_VALOR) Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_ANHO = $year
            and ID_MES between 1 and $month
            and (
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '3%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 0
              )
              or 
              id_cuentaaasi in (
                select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                  where id_cuentaaasi like '4%' 
                  and es_grupo = 0
                  and ES_ACREEDORA = 0
              )
            )
            group by ID_DEPTO,ID_ENTIDAD
            ) X
        INNER JOIN Conta_Entidad_Depto ed ON 
        X.Entidad = ed.ID_ENTIDAD
        and X.Dpto = ed.ID_DEPTO
        LEFT JOIN CONTA_ENTIDAD_DEPTO_RESP cedr ON
        X.Entidad = cedr.ID_ENTIDAD
        AND X.Dpto = cedr.ID_DEPTO
        LEFT JOIN MOISES.VW_PERSONA_NATURAL vwpn ON
        cedr.ID_PERSONA = vwpn.ID_PERSONA
        group by X.Dpto,X.Entidad,ed.NOMBRE,vwpn.paterno,vwpn.nombre
        order by ed.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function budgetMain($year, $month, $entity, $group)
    {
        $query = "SELECT 
                        NOMBRE,        
                        DECODE(GROUPING(INSA),1,'TOTAL',DECODE(INSA,'I','A','B')) INSA,
                        NVL(NOMBRE,DECODE(GROUPING(INSA),1,'TOTAL',DECODE(INSA,'I','INGRESOS','GASTOS'))) GLOSA,
                        case when SUM(IMP) > 0 or SUM(IMP) < 0 then to_char(SUM(IMP),'999,999,999.99') 
                             when SUM(IMP) = 0 then ' ' else to_char(SUM(IMP),'999,999,999.99') end EJECUTADO,
                       case when SUM(MES) > 0 or SUM(MES) < 0 then to_char(SUM(MES),'999,999,999.99') 
                             when SUM(MES) = 0 then ' ' else to_char(SUM(MES),'999,999,999.99') end PRESUPUESTO_MES,
                        case when SUM(ACUM) > 0 or SUM(ACUM) < 0 then to_char(SUM(ACUM),'999,999,999.99') 
                             when SUM(ACUM) = 0 then ' ' else to_char(SUM(ACUM),'999,999,999.99') end ACUMULADO
                    FROM (
                        SELECT
                                NOMBRE,ORDEN,INSA,
                                (CASE WHEN ID_MES BETWEEN 1 AND $month THEN (PPTO)  ELSE  0 END) MES,
                                (PPTO) ACUM ,
                                (CASE WHEN ID_MES BETWEEN 1 AND $month THEN (IMP)  ELSE  0 END) IMP
                        FROM (
                                --DATA CON CTA CTE
                                SELECT
                                        X.ID_ENTIDAD,
                                        X.ID_ANHO,
                                        X.ID_MES,
                                        A.NOMBRE,A.ORDEN,A.INSA,
                                        X.ID_DEPTO,
                                        X.ID_CUENTAAASI,
                                        X.COS_VALOR PPTO,
                                        0 IMP
                                FROM CONTA_ENTIDAD_GRUPO A
                                LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                                ON A.ID_ENTIDAD = B.ID_ENTIDAD
                                AND A.ID_GRUPO = B.ID_GRUPO
                                LEFT JOIN CONTA_PRESUPUESTO X
                                ON B.ID_ENTIDAD = X.ID_ENTIDAD
                                AND B.ID_CUENTAAASI = X. ID_CUENTAAASI
                                WHERE B.TIENE_CTACTE = '0'
                                UNION ALL
                                --DATA CON CTA CTE
                                SELECT
                                        X.ID_ENTIDAD,
                                        X.ID_ANHO,
                                        X.ID_MES,
                                        A.NOMBRE,A.ORDEN,A.INSA,
                                        X.ID_DEPTO,
                                        X.ID_CUENTAAASI,
                                        X.COS_VALOR PPTO,
                                        0 IMP
                                FROM CONTA_ENTIDAD_GRUPO A
                                LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                                ON A.ID_ENTIDAD = B.ID_ENTIDAD
                                AND A.ID_GRUPO = B.ID_GRUPO
                                LEFT JOIN CONTA_PRESUPUESTO X
                                ON B.ID_ENTIDAD = X.ID_ENTIDAD
                                AND B.ID_PARENT = X. ID_CUENTAAASI
                                AND B.ID_CUENTAAASI = X.ID_CTACTE
                                UNION ALL
                                --DATA CON CTA CTE
                                SELECT
                                        X.ID_ENTIDAD,
                                        X.ID_ANHO,
                                        X.ID_MES,
                                        A.NOMBRE,A.ORDEN,A.INSA,
                                        X.ID_DEPTO,
                                        X.ID_CUENTAAASI,
                                        0 PPTO,
                                        X.COS_VALOR IMP
                                FROM CONTA_ENTIDAD_GRUPO A
                                LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                                ON A.ID_ENTIDAD = B.ID_ENTIDAD
                                AND A.ID_GRUPO = B.ID_GRUPO
                                LEFT JOIN VW_CONTA_DIARIO X
                                ON B.ID_ENTIDAD = X.ID_ENTIDAD
                                AND B.ID_CUENTAAASI = X. ID_CUENTAAASI
                                WHERE B.TIENE_CTACTE = '0'
                                UNION ALL
                                --DATA CON CTA CTE
                                SELECT
                                        X.ID_ENTIDAD,
                                        X.ID_ANHO,
                                        X.ID_MES,
                                        A.NOMBRE,A.ORDEN,A.INSA,
                                        X.ID_DEPTO,
                                        X.ID_CUENTAAASI,
                                        0 PPTO,
                                        X.COS_VALOR IMP
                                FROM CONTA_ENTIDAD_GRUPO A
                                LEFT JOIN CONTA_ENTIDAD_CTA_GRUPO B
                                ON A.ID_ENTIDAD = B.ID_ENTIDAD
                                AND A.ID_GRUPO = B.ID_GRUPO
                                LEFT JOIN VW_CONTA_DIARIO X
                                ON B.ID_ENTIDAD = X.ID_ENTIDAD
                                AND B.ID_PARENT = X. ID_CUENTAAASI
                                AND B.ID_CUENTAAASI = X.ID_CTACTE
                        )
                        WHERE ID_ENTIDAD = $entity
                        AND ID_ANHO = $year
                        AND ID_MES BETWEEN 1 AND 12
                        AND ID_DEPTO IN ( SELECT ID_DEPTO FROM CONTA_ENTIDAD_DEPTO_GRUPO  WHERE ID_ENTIDAD = $entity AND ID_GRUPO = $group )
                        ORDER BY INSA,ORDEN
                    )
                    GROUP BY ROLLUP (INSA,NOMBRE)
                    ORDER BY INSA, NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function travels($year, $month, $entity){
        if($entity == 7124){
            $depto = "";
        }else{
            $depto = "and di.ID_DEPTO = 396119 ";
        }
        $query = "SELECT
            X.CtaCte,
            X.Nombre_ctacte,
            ABS(sum(X.Previsto_mes)) Previsto_mes, 
            sum(X.Realizado_mes) Realizado_mes,    
            ABS(sum(X.Previsto_mes))-sum(X.Realizado_mes) total_mes,
            sum(X.Saldo_inicial) Saldo_Inicial,
            ABS(sum(X.Previsto)) Previsto,
            sum(X.Realizado) Realizado,
            (sum(X.Saldo_inicial)+ABS(sum(X.Previsto))) - sum(X.Realizado) total,
            round((sum(X.Realizado)/
                decode((sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))), 0 ,1,(sum(X.Saldo_Inicial)+ABS(sum(X.Previsto))))
                )*100,2) Porcentaje
        from (
        SELECT
        ecc.ID_CTACTE CtaCte,
        ecc.NOMBRE as Nombre_ctacte,
        sum(di.COS_VALOR) Previsto_mes,
        0 Realizado_mes,
        0 saldo_inicial,
        0 Previsto,
        0 Realizado
        from vw_conta_Presupuesto di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = $entity
        and di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE=ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ANHO IN ($year)
        and di.ID_MES <= $month
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and p.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,ecc.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTE
        UNION
        select
            ecc.ID_CTACTE CtaCte,
            ecc.NOMBRE as Nombre_ctacte,
            0 Previsto_mes,
            sum(di.COS_VALOR) Realizado_mes,
            0 saldo_inicial,
            0 Previsto,
            0 Realizado
        from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE = ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ENTIDAD = $entity
        and di.ID_ANHO = $year
        and di.ID_MES <= $month
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,di.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTe
        UNION
        SELECT
        CtaCte,
        Nombre_ctacte,
        0 Previsto_mes,
        0 Realizado_mes,
        sum(saldo_inicial)-sum(Previsto) saldo_inicial,
        0 Previsto,
        0 Realizado
        FROM (
        select
            ecc.ID_CTACTE CtaCte,
            ecc.NOMBRE as Nombre_ctacte,    
            sum(di.COS_VALOR) saldo_inicial,
            0 Previsto
        from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc--,DBContabilidad..Entidad_Cuenta_Corriente_Func dcc
        where di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE = ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ENTIDAD = $entity
        and di.ID_ANHO in ($year)-1
        and di.ID_MES = 12
        AND ID_TIPOASIENTO = 'EA'
        and di.ID_CUENTAAASI in (4113023,4121029,4111070,4111022,4111023,4111073,4111071)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,di.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTe    
        UNION
        select
        ecc.ID_CTACTE CtaCte,
        ecc.NOMBRE as Nombre_ctacte,
        0 saldo_inicial,
        sum(di.COS_VALOR) Previsto
        from vw_conta_Presupuesto di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = $entity
        and di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE=ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ANHO IN ($year)-1
        and di.ID_MES <= 12
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and p.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,ecc.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTE)
        group by CtaCte, Nombre_ctacte
        UNION
        select
        ecc.ID_CTACTE CtaCte,
        ecc.NOMBRE as Nombre_ctacte,
        0 Previsto_mes,
        0 Realizado_mes,
        0 saldo_inicial,
        sum(di.COS_VALOR) Previsto,
        0 Realizado
        from vw_conta_Presupuesto di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = $entity
        and di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE=ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ANHO IN ($year)
        and di.ID_MES <= 12
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,ecc.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTE
        UNION
        select
            ecc.ID_CTACTE CtaCte,
            ecc.NOMBRE as Nombre_ctacte,
            0 Previsto_mes,
            0 Realizado_mes,
            0 saldo_inicial,
            0 Previsto,
            sum(di.COS_VALOR) Realizado
        from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE = ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ENTIDAD = $entity
        and di.ID_ANHO = $year
        and di.ID_MES <= $month
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,di.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTe
        ) X
        group by X.Nombre_ctacte,X.CtaCte
        order by X.Nombre_ctacte ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function travels_summary($year, $month, $entity,$person){
        if($entity == 7124){
            $depto = "";
        }else{
            $depto = "and p.ID_DEPTO = 396119 ";
        }
        $query = "SELECT
            X.CtaCte,
            X.Nombre_ctacte,
            ABS(sum(X.Previsto_mes)) Previsto_mes, 
            sum(X.Realizado_mes) Realizado_mes,    
            ABS(sum(X.Previsto_mes))-sum(X.Realizado_mes) total_mes,
            sum(X.Saldo_inicial) Saldo_Inicial,
            ABS(sum(X.Previsto)) Previsto,
            sum(X.Realizado) Realizado,
            (sum(X.Saldo_inicial)+ABS(sum(X.Previsto))) - sum(X.Realizado) total
        from (
        SELECT
        ecc.ID_CTACTE CtaCte,
        ecc.NOMBRE as Nombre_ctacte,
        sum(p.COS_VALOR) Previsto_mes,
        0 Realizado_mes,
        0 saldo_inicial,
        0 Previsto,
        0 Realizado
        from vw_conta_Presupuesto p,VW_CONTA_CTACTE ecc
        where p.ID_ENTIDAD = $entity
        and p.ID_ENTIDAD = ecc.ID_ENTIDAD
        and p.ID_CTACTE=ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and p.ID_ANHO IN ($year)
        and p.ID_MES <= $month
        and p.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and p.ID_DEPTO = 396119
        ".$depto."
        and p.ID_CTACTE <> 0
        group by p.ID_ENTIDAD,ecc.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTE
        UNION
        select
            ecc.ID_CTACTE CtaCte,
            ecc.NOMBRE as Nombre_ctacte,
            0 Previsto_mes,
            sum(di.COS_VALOR) Realizado_mes,
            0 saldo_inicial,
            0 Previsto,
            0 Realizado
        from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE = ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ENTIDAD = $entity
        and di.ID_ANHO = $year
        and di.ID_MES <= $month
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,di.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTe
        UNION
        SELECT
        CtaCte,
        Nombre_ctacte,
        0 Previsto_mes,
        0 Realizado_mes,
        sum(saldo_inicial)-sum(Previsto) saldo_inicial,
        0 Previsto,
        0 Realizado
        FROM (
        select
            ecc.ID_CTACTE CtaCte,
            ecc.NOMBRE as Nombre_ctacte,    
            sum(di.COS_VALOR) saldo_inicial,
            0 Previsto
        from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE = ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ENTIDAD = $entity
        and di.ID_ANHO in ($year)-1
        and di.ID_MES = 12
        AND ID_TIPOASIENTO = 'EA'
        and di.ID_CUENTAAASI in (4113023,4121029,4111070,4111022,4111023,4111073,4111071)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,di.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTe    
        UNION
        select
        ecc.ID_CTACTE CtaCte,
        ecc.NOMBRE as Nombre_ctacte,
        0 saldo_inicial,
        sum(p.COS_VALOR) Previsto
        from vw_conta_Presupuesto p,VW_CONTA_CTACTE ecc
        where p.ID_ENTIDAD = $entity
        and p.ID_ENTIDAD = ecc.ID_ENTIDAD
        and p.ID_CTACTE=ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and p.ID_ANHO IN ($year)-1
        and p.ID_MES <= 12
        and p.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and p.ID_DEPTO = 396119
        ".$depto."
        and p.ID_CTACTE <> 0
        group by p.ID_ENTIDAD,ecc.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTE)
        group by CtaCte, Nombre_ctacte
        UNION
        select
        ecc.ID_CTACTE CtaCte,
        ecc.NOMBRE as Nombre_ctacte,
        0 Previsto_mes,
        0 Realizado_mes,
        0 saldo_inicial,
        sum(p.COS_VALOR) Previsto,
        0 Realizado
        from vw_conta_Presupuesto p,VW_CONTA_CTACTE ecc
        where p.ID_ENTIDAD = $entity
        and p.ID_ENTIDAD = ecc.ID_ENTIDAD
        and p.ID_CTACTE=ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and p.ID_ANHO IN ($year)
        and p.ID_MES <= 12
        and p.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and p.ID_DEPTO = 396119
        ".$depto."
        and p.ID_CTACTE <> 0
        group by p.ID_ENTIDAD,ecc.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTE
        UNION
        select
            ecc.ID_CTACTE CtaCte,
            ecc.NOMBRE as Nombre_ctacte,
            0 Previsto_mes,
            0 Realizado_mes,
            0 saldo_inicial,
            0 Previsto,
            sum(di.COS_VALOR) Realizado
        from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
        where di.ID_ENTIDAD = ecc.ID_ENTIDAD
        and di.ID_CTACTE = ecc.ID_CTACTE
        and ecc.ID_TIPOCTACTE = 'FUNC'
        and di.ID_ENTIDAD = $entity
        and di.ID_ANHO = $year
        and di.ID_MES <= $month
        and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
        --and di.ID_DEPTO = 396119
        ".$depto."
        and di.ID_CTACTE <> 0
        group by di.ID_ENTIDAD,di.ID_CTACTE,ecc.NOMBRE,ecc.ID_CTACTe
        ) X , MOISES.VW_PERSONA_NATURAL vwpn 
        where vwpn.ID_PERSONA= $person
        and X.CtaCte = vwpn.num_documento
        group by X.Nombre_ctacte,X.CtaCte
        order by X.Nombre_ctacte ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function travels_detail($year, $month, $entity, $currentAccount){
        if($entity == 7124){
            $depto = "";
        }else{
            $depto = "and di.ID_DEPTO = 396119 ";
        }
        $query = "SELECT
                    ecc.ID_CTACTE as CtaCte,
                    ecc.NOMBRE as Nombre_ctacte,
                    to_char(di.FEC_ASIENTO,'dd/mm/yyyy') AS Fecha,
                    di.CODIGO AS CODIGO,
                    di.COMENTARIO AS COMENTARIO, 
                    di.COS_VALOR AS COS_VALOR
                from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
                where di.ID_ENTIDAD = ecc.ID_ENTIDAD
                and di.ID_CTACTE = ecc.ID_CTACTE
                and ecc.ID_TIPOCTACTE = 'FUNC'
                and di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_MES <= $month
                and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
                --and di.ID_DEPTO = 396119
                ".$depto."
                and di.ID_CTACTE = '".$currentAccount."'";
                   
        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function travels_detail_total($year, $month, $entity, $currentAccounts)
    {

        $query = "SELECT
                    ecc.ID_CTACTE as CtaCte,
                    ecc.NOMBRE as Nombre_ctacte,
                    sum(di.COS_VALOR) AS COS_VALOR_TOTAL
                from VW_CONTA_DIARIO di,VW_CONTA_CTACTE ecc
                where di.ID_ENTIDAD = ecc.ID_ENTIDAD
                and di.ID_CTACTE = ecc.ID_CTACTE
                and ecc.ID_TIPOCTACTE = 'FUNC'
                and di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_MES <= $month
                and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
                and di.ID_DEPTO = 396119
                and di.ID_CTACTE = '$currentAccounts'
                GROUP BY ecc.ID_CTACTE, ecc.NOMBRE";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function budget_detail($year, $month, $entity, $departamento)
    {

        $query = "SELECT
                    to_char(FEC_ASIENTO,'dd/mm/yyyy') AS FECHA,
                    CODIGO,
                    COMENTARIO,
                    COS_VALOR COS_VALOR
                from VW_CONTA_DIARIO
                where ID_ENTIDAD = $entity
                and ID_ANHO = $year
                and ID_MES <= $month
                and ID_DEPTO in ('$departamento')
                and (
                  id_cuentaaasi in (
                    select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                      where id_cuentaaasi like '3%' 
                      and es_grupo = 0
                      and ES_ACREEDORA = 0
                  )
                  or 
                  id_cuentaaasi in (
                    select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                      where id_cuentaaasi like '4%' 
                      and es_grupo = 0
                      and ES_ACREEDORA = 0
                  )
                ) order by FEC_ASIENTO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function budget_detail_total($year, $month, $entity, $departamento)
    {

        $query = "SELECT
                    ced.ID_DEPTO as depto,
                    ced.NOM_DEPARTAMENTO as Nombre_depto,
                    sum(di.COS_VALOR)  AS COS_VALOR_TOTAL
                from VW_CONTA_DIARIO di,VW_CONTA_ENTIDAD_DEPTO ced
                where di.ID_ENTIDAD = ced.ID_ENTIDAD
                AND di.ID_DEPTO = ced.ID_DEPTO
                and di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_MES <= $month
                and di.ID_DEPTO in ('$departamento')
                and (
                  id_cuentaaasi in (
                    select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                      where id_cuentaaasi like '3%' 
                      and es_grupo = 0
                      and ES_ACREEDORA = 0
                  )
                  or 
                  id_cuentaaasi in (
                    select id_cuentaaasi from CONTA_CTA_DENOMINACIONAL
                      where id_cuentaaasi like '4%' 
                      and es_grupo = 0
                      and ES_ACREEDORA = 0
                  )
                )
                GROUP BY ced.ID_DEPTO,ced.NOM_DEPARTAMENTO";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }

}
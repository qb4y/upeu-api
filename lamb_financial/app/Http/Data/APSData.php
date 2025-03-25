<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class APSData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function payroll()
    {
        $query = "SELECT
            APS_PLANILLA_DETALLE.ID_PERSONA,
            APS_PLANILLA_DETALLE.ID_TIPOPLANILLA,
            APS_PLANILLA_DETALLE.ID_CONCEPTOAPS,
            APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI,
            CAST(SUM(APS_PLANILLA_DETALLE.COS_VALOR) AS decimal(19, 2)) AS COS_VALOR
          FROM APS_PLANILLA_DETALLE
          INNER JOIN APS_CONCEPTO_PLANILLA_CUENTA ON
            APS_PLANILLA_DETALLE.ID_CONCEPTOAPS = APS_CONCEPTO_PLANILLA_CUENTA.ID_CONCEPTOAPS
          WHERE APS_PLANILLA_DETALLE.ID_ENTIDAD = 7124
            AND APS_PLANILLA_DETALLE.ID_ANHO = 2017
            AND ((APS_PLANILLA_DETALLE.ID_MES <= 1 AND -1 = 1) OR (APS_PLANILLA_DETALLE.ID_MES = 1 /* AND @Acumulado = 0*/))
           -- AND APS_PLANILLA_DETALLE.ID_TIPOPLANILLA IN (1, 9, 12)
            AND APS_PLANILLA_DETALLE.ID_TIPOPLANILLA IN (98626,106791,98634)
            AND APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI LIKE '411%'
            AND CAST(APS_PLANILLA_DETALLE.ID_TIPOPLANILLA AS varchar(10)) || '.' || CAST(APS_PLANILLA_DETALLE.ID_CONCEPTOAPS AS varchar(10)) <> '9.7080'
          GROUP BY APS_PLANILLA_DETALLE.ID_PERSONA, APS_PLANILLA_DETALLE.ID_TIPOPLANILLA, APS_PLANILLA_DETALLE.ID_CONCEPTOAPS, APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI";

        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function detailFinancialStatement($year, $month, $entity)
    {
        $query = "SELECT
                    VAE.NUM_DOCUMENTO DNI,
                    VAE.PATERNO,
                    VAE.MATERNO,
                    VAE.NOMBRE,
                    VAP.FMR,
                    nvl(FC_TIPO_ESTATUS(VAP.ID_TIPOESTATUS),'-') TIPOESTATUS,
                    nvl(FC_TIPO_CONTRATO(VAE.ID_TIPOCONTRATO),0) TIPOCONTRATO,
                    VAE.FEC_INICIO,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 1 THEN VAP.COS_VALOR ELSE 0 END ) SUELDOS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 2 THEN VAP.COS_VALOR ELSE 0 END ) REMVAR,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 3 THEN VAP.COS_VALOR ELSE 0 END ) GRATI,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 4 THEN VAP.COS_VALOR ELSE 0 END ) ASIGFAM,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 5 THEN VAP.COS_VALOR ELSE 0 END ) REMESP,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 6 THEN VAP.COS_VALOR ELSE 0 END ) VLD,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 7 THEN VAP.COS_VALOR ELSE 0 END ) VCP,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 8 THEN VAP.COS_VALOR ELSE 0 END ) MODFORM,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 9 THEN VAP.COS_VALOR ELSE 0 END ) OTROSREM,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 10 THEN VAP.COS_VALOR ELSE 0 END ) MUDANZA,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 11 THEN VAP.COS_VALOR ELSE 0 END ) EDUCACION,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 12 THEN VAP.COS_VALOR ELSE 0 END ) HOSPALOJ,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 13 THEN VAP.COS_VALOR ELSE 0 END ) IMPVIAJES,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 14 THEN VAP.COS_VALOR ELSE 0 END ) REMEXT,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 15 THEN VAP.COS_VALOR ELSE 0 END ) PPG,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 16 THEN VAP.COS_VALOR ELSE 0 END ) CTS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 17 THEN VAP.COS_VALOR ELSE 0 END ) ESSALUD,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN = 18 THEN VAP.COS_VALOR ELSE 0 END ) EPS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 7 AND CCG.NUM_ORDEN BETWEEN 1 AND 18 THEN VAP.COS_VALOR ELSE 0 END ) TOTALREM,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 1 THEN VAP.COS_VALOR ELSE 0 END ) SEGVEH,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 2 THEN VAP.COS_VALOR ELSE 0 END ) SEGONCO,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 3 THEN VAP.COS_VALOR ELSE 0 END ) EDPRISEC,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 4 THEN VAP.COS_VALOR ELSE 0 END ) EDSUP,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 5 THEN VAP.COS_VALOR ELSE 0 END ) MUSICA,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 6 THEN VAP.COS_VALOR ELSE 0 END ) AGUA,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 7 THEN VAP.COS_VALOR ELSE 0 END ) MEDIC,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 8 THEN VAP.COS_VALOR ELSE 0 END ) CONSULTAS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 9 THEN VAP.COS_VALOR ELSE 0 END ) EXAMENES,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 10 THEN VAP.COS_VALOR ELSE 0 END ) CHEQUEO,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 11 THEN VAP.COS_VALOR ELSE 0 END ) OFTLUNA,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 12 THEN VAP.COS_VALOR ELSE 0 END ) OFTMONTURA,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 13 THEN VAP.COS_VALOR ELSE 0 END ) ODONT,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 14 THEN VAP.COS_VALOR ELSE 0 END ) ORTOD,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 15 THEN VAP.COS_VALOR ELSE 0 END ) LIBROS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 16 THEN VAP.COS_VALOR ELSE 0 END ) COMP,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 17 THEN VAP.COS_VALOR ELSE 0 END ) TELEF,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 18 THEN VAP.COS_VALOR ELSE 0 END ) TABLET,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN = 19 THEN VAP.COS_VALOR ELSE 0 END ) IMPRESORAS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 8 AND CCG.NUM_ORDEN BETWEEN 1 AND 19 THEN VAP.COS_VALOR ELSE 0 END ) TOTALAYUDAS,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN = 1 THEN VAP.COS_VALOR ELSE 0 END ) REEMMOVLOCAL,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN = 2 THEN VAP.COS_VALOR ELSE 0 END ) CAPPERSONAL,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN = 3 THEN VAP.COS_VALOR ELSE 0 END ) SVL,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN = 4 THEN VAP.COS_VALOR ELSE 0 END ) SVP,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN = 5 THEN VAP.COS_VALOR ELSE 0 END ) SEGVIAJES,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN = 6 THEN VAP.COS_VALOR ELSE 0 END ) VALEALIM,
                    SUM( CASE WHEN CCG.ID_TIPOGRUPOCUENTA = 9 AND CCG.NUM_ORDEN BETWEEN 1 AND 6 THEN VAP.COS_VALOR ELSE 0 END ) TOTALSEG
            FROM VW_APS_PLANILLA VAP
            INNER JOIN VW_APS_EMPLEADO VAE
            ON  VAP.ID_EMPRESA = VAE.ID_EMPRESA
            AND VAP.ID_ENTIDAD = VAE.ID_ENTIDAD
            AND VAP.ID_PERSONA = VAE.ID_PERSONA
            AND VAP.ID_CONTRATO = VAE.ID_CONTRATO
            AND VAP.ID_TIPOCONTRATO = VAE.ID_TIPOCONTRATO
            INNER JOIN CONTA_CUENTA_GRUPO CCG
            ON CCG.ID_TIPOGRUPOCUENTA in (7,8,9)
            AND '+' || CCG.CUENTAS || '+' LIKE '%+' || CAST(VAP.ID_CONCEPTOAPS AS varchar(20)) || '+%'
            WHERE VAP.ID_ENTIDAD = $entity
            AND VAP.ID_ANHO = $year
            AND VAP.ID_MES <= $month
            GROUP BY VAE.NUM_DOCUMENTO,VAE.PATERNO,VAE.MATERNO,VAE.NOMBRE,VAP.FMR,VAP.ID_TIPOESTATUS,VAE.ID_TIPOCONTRATO,VAE.FEC_INICIO
            ORDER BY VAE.PATERNO,VAE.MATERNO,VAE.NOMBRE
    ";

        $oQuery = DB::select($query);
        return $oQuery;

    }

    public static function summaryFinancialStatement($year, $month, $entity)
    {
        $query = "SELECT
        CTA,
        NOMBRE_CTA,
        SUM( COS_VALOR_AASINET ) AS AASINET,
        SUM( COS_VALOR_APS ) AS APS,
        SUM( COS_VALOR_APS )- SUM( COS_VALOR_AASINET ) AS DIFERENCIA
    FROM
        (
         SELECT
                APS_CONCEPTO_PLANILLA_CTAFICHA.ID_CUENTAAASI AS CTA,
                FC_CUENTA_DENOMINACIONAL(APS_CONCEPTO_PLANILLA_CTAFICHA.ID_CUENTAAASI) AS NOMBRE_CTA,
                CAST(SUM( APS_PLANILLA_DETALLE.COS_VALOR ) AS DECIMAL(19,2)) AS COS_VALOR_APS,
                0 AS COS_VALOR_AASINET
            FROM APS_PLANILLA_DETALLE
            INNER JOIN APS_CONCEPTO_PLANILLA_CTAFICHA ON
            APS_PLANILLA_DETALLE.ID_CONCEPTOAPS = APS_CONCEPTO_PLANILLA_CTAFICHA.ID_CONCEPTOAPS
            WHERE APS_PLANILLA_DETALLE.ID_ENTIDAD = $entity
            AND APS_PLANILLA_DETALLE.ID_ANHO = $year
            AND((APS_PLANILLA_DETALLE.ID_MES <= $month /*AND -1 = 1) OR (APS_PLANILLA_DETALLE.ID_MES = 1  AND @Acumulado = 0*/))
            AND APS_PLANILLA_DETALLE.ID_TIPOPLANILLA IN (98626,98634)
            AND APS_CONCEPTO_PLANILLA_CTAFICHA.ID_CUENTAAASI LIKE '411%'
           -- AND CAST(APS_PLANILLA_DETALLE.ID_TIPOPLANILLA AS VARCHAR(10))|| '.' || 
           --     CAST(APS_PLANILLA_DETALLE.ID_CONCEPTOAPS  AS VARCHAR(10)) <> '9.7080'
            GROUP BY APS_CONCEPTO_PLANILLA_CTAFICHA.ID_CUENTAAASI     
        UNION
        SELECT
                ID_CUENTAAASI AS CTA,
                FC_CUENTA_DENOMINACIONAL(ID_CUENTAAASI) AS NOMBRE_CTA,
                0 AS COS_VALOR_APS,
                SUM( SALDO ) AS COS_VALOR_AASINET
            FROM VW_CONTA_SALDOS
            WHERE ID_ANHO = $year
            AND ID_MES <= $month
            AND ID_ENTIDAD = $entity
            AND ID_CUENTAAASI IN(
                    4111001,4111003,4111004,4111008,
                    4111019,4111022,4111023,4111026,
                    4111027,4111032,4111050,4111070,
                    4111071,4111076,4112025,4112047,
                    4112048,4112090,4113023,4114003,
                    4114034,4114035,4114037,4114040,
                    4114045
            )
            GROUP BY ID_CUENTAAASI
            ORDER BY 1
    ) GROUP BY CTA,	NOMBRE_CTA
    ORDER BY 1
    ";

        $oQuery = DB::select($query);
        return $oQuery;

    }

    public static function totalFinancialStatement($year, $month, $entity)
    {
        $query = "SELECT
	SUM( COS_VALOR_AASINET ) AS TOTAL_AASINET,
	SUM( COS_VALOR_APS ) AS TOTAL_APS,
	SUM( COS_VALOR_AASINET )- SUM( COS_VALOR_APS ) AS TOTAL_DIFERENCIA
FROM
	(
		SELECT
			APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI AS CTA,
			FC_CUENTA_DENOMINACIONAL(APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI) AS NOMBRE_CTA,
			CAST(SUM( APS_PLANILLA_DETALLE.COS_VALOR ) AS DECIMAL(19,2)) AS COS_VALOR_APS,
			0 AS COS_VALOR_AASINET
		FROM APS_PLANILLA_DETALLE
		INNER JOIN APS_CONCEPTO_PLANILLA_CUENTA ON
		APS_PLANILLA_DETALLE.ID_CONCEPTOAPS = APS_CONCEPTO_PLANILLA_CUENTA.ID_CONCEPTOAPS
		WHERE APS_PLANILLA_DETALLE.ID_ENTIDAD = $entity
		AND APS_PLANILLA_DETALLE.ID_ANHO = $year
		AND((APS_PLANILLA_DETALLE.ID_MES <= $month /*AND -1 = 1) OR (APS_PLANILLA_DETALLE.ID_MES = 1  AND @Acumulado = 0*/))
			AND APS_PLANILLA_DETALLE.ID_TIPOPLANILLA IN(98626,106791,98634)
			AND APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI LIKE '411%'
			AND CAST(APS_PLANILLA_DETALLE.ID_TIPOPLANILLA AS VARCHAR(10))|| '.' || CAST(APS_PLANILLA_DETALLE.ID_CONCEPTOAPS AS VARCHAR(10)) <> '9.7080'
		GROUP BY APS_CONCEPTO_PLANILLA_CUENTA.ID_CUENTAAASI
	UNION
	SELECT
			ID_CUENTAAASI AS CTA,
			FC_CUENTA_DENOMINACIONAL(ID_CUENTAAASI) AS NOMBRE_CTA,
			0 AS COS_VALOR_APS,
			SUM( SALDO ) AS COS_VALOR_AASINET
		FROM VW_CONTA_SALDOS
		WHERE ID_ANHO = $year
		AND ID_MES = $month
		AND ID_ENTIDAD = $entity
		AND ID_CUENTAAASI IN(
				4111001,4111003,4111004,4111008,
				4111019,4111022,4111023,4111026,
				4111027,4111032,4111050,4111070,
				4111071,4112025,4112047,4112048,
				4112090,4113023,4114003,4114034,
				4114035,4114037,4114040,4114045
			)
		GROUP BY ID_CUENTAAASI
		ORDER BY 1
	)";

        $oQuery = DB::select($query);

        return $oQuery[0];
    }

    public static function pas_header($entity)
    {
        $query = "
            select NOM_EMPRESA, ID_RUC,NOM_ENTIDAD
            from VW_CONTA_ENTIDAD
            where id_entidad=$entity
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function pas_personal_data($entity, $year, $month, $id_persona)
    {
        $query = "
            select distinct e.NOM_PERSONA, e.NUM_DOCUMENTO, p.NOM_CARGO from VW_APS_PLANILLA p, vw_aps_empleado e
            where p.id_entidad = e.id_entidad
            and p.id_contrato = e.id_contrato
            and p.id_persona = e.id_persona
            and p.id_entidad = $entity
            and p.id_persona = $id_persona
            and p.id_anho = $year
            and p.id_mes = $month
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function pas_payroll_earnings($entity, $year, $month, $id_persona)
    {
        $query = "
            select 
                a.NOMBRE, p.COS_VALOR
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                AND P.ID_CONCEPTOAPS IN(1000,1079,1126,1212,1145,1530,1532,3000,7030)
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function pas_payroll_contribute($entity, $year, $month, $id_persona)
    {
        $query = "
            select 
                a.NOMBRE, p.COS_VALOR
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                AND P.ID_CONCEPTOAPS IN(1500,1501,1502,1508,1522)
                and p.ID_ENTIDAD=$entity
                and p.ID_PERSONA=$id_persona
                and p.ID_ANHO=$year
                and p.ID_MES=$month
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function pas_payroll_discount($entity, $year, $month, $id_persona)
    {
        $query = "
            select 
                a.NOMBRE, p.COS_VALOR
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                AND P.ID_CONCEPTOAPS IN(1545,7600,1147,1530)
                and p.ID_ENTIDAD=$entity
                and p.ID_PERSONA=$id_persona
                and p.ID_ANHO=$year
                and p.ID_MES=$month
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function pas_account_movement($entity, $year, $month, $dni, $id_cuentaaasi)
    {
        $query = "
            select glosa, debito, credito
            ,sum(valor) OVER ( 
                order by fecha_orden,codigo
                rows unbounded preceding
            ) as saldo
            from (
            select                      
                    to_date('01010001','ddmmyyyy') as fecha_orden,
                    '' as fecha,
                    '' as codigo,
                    'Saldo Inicial' as glosa,
                    0 Debito, 
                    0 Credito,
                    sum(d.COS_VALOR) AS Valor
            from VW_CONTA_DIARIO d
            where  d.ID_ANHO = $year
            and d.ID_MES < $month
            and d.ID_ENTIDAD =$entity
            and d.ID_CTACTE ='$dni'
            and d.ID_CUENTAAASI=$id_cuentaaasi
            group by d.ID_ANHO 
            union all
            Select 
                    d.FEC_ASIENTO as fecha_orden,
                    TO_CHAR(d.FEC_ASIENTO,'DD/MM/YYYY') as fecha,
                    CAST(d.id_entidad as varchar(100))||'-'||cast(d.id_tipoasiento as varchar(100))||' '||cast(d.cod_aasi as varchar(100)) as codigo,
                    d.COMENTARIO as glosa,
                    case when d.COS_VALOR > 0 then ABS(CAST(-d.COS_VALOR AS DECIMAL(9,2))) else 0 end Debito, 
                    case when d.COS_VALOR < 0 then ABS(CAST(d.COS_VALOR AS DECIMAL(9,2))) else 0 end Credito,
                    D.COS_VALOR AS Valor                         
            from VW_CONTA_DIARIO d
            where d.ID_ANHO = $year
            and d.ID_MES = $month 
            and d.ID_ENTIDAD =$entity
            and d.ID_CTACTE ='$dni' 
            and d.ID_CUENTAAASI = $id_cuentaaasi
            ) a
            order by fecha_orden , codigo
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function pas_list_personal($entity, $year, $month)
    {
        $query = "
            select distinct e.id_persona as id, e.NUM_DOCUMENTO||' '||e.NOM_PERSONA as name,
            e.NUM_DOCUMENTO as doc_number
            from VW_APS_PLANILLA p, vw_aps_empleado e
            where p.id_entidad = e.id_entidad
            and p.id_contrato = e.id_contrato
            and p.id_persona = e.id_persona
            and p.id_entidad = $entity
            and p.id_anho = $year
            and p.id_mes = $month
            order by name 
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function summaryHelp($entity, $year, $month)
    {
        $query = "SELECT    DECODE(ID_ENTIDAD,7124,'UPeU','UPN') GLOSA,
                            SUBSTR(ID_DEPTO,1,1) NIVEL,
                            DECODE(SUBSTR(ID_DEPTO,1,1),1,'1. UPeU',2,'2. PU',3,'3. IU',4,'4. CU',5,'5. FJ',6,'6. FT',7,'7. CAT') SEDE,
                            ID_DEPTO,
                            ID_PERSONA,
                            (SELECT NOMBRE||' '||PATERNO||' '||PATERNO FROM MOISES.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = VW_APS_PLANILLA.ID_PERSONA) NOMBRE,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7002,7036,7037) THEN COS_VALOR ELSE 0 END) MEDICA,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7000,7001,7009) THEN COS_VALOR ELSE 0 END) ODONTO,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7050,7051) THEN COS_VALOR ELSE 0 END) OFTAL,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7056,7062,7060,7058,7064) THEN COS_VALOR ELSE 0 END) EDUC,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7030) THEN COS_VALOR ELSE 0 END) CASA,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7017) THEN COS_VALOR ELSE 0 END) PROFE,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7080) THEN COS_VALOR ELSE 0 END) DEPREC,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7003,7015,7037,7046,7020,7034) THEN COS_VALOR ELSE 0 END) OTROS,
                            SUM(CASE WHEN ID_CONCEPTOAPS IN (7002,7036,7037,7000,7001,7009,7050,7051,7056,7062,7060,7058,7064,7030,7017,7080,7015,7037,7046,7020,7034) THEN COS_VALOR ELSE 0 END) TOTAL
                    FROM VW_APS_PLANILLA
                    WHERE ID_ENTIDAD = $entity
                    AND ID_ANHO = $year
                    AND ID_MES = $month       
                    AND ID_CONCEPTOAPS BETWEEN 7000 AND 7200
                    GROUP BY ID_ENTIDAD,ID_PERSONA,ID_DEPTO
                    ORDER BY SEDE,NIVEL,ID_DEPTO,NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function summaryHelpTotal($entity, $year, $month)
    {
        $query = "SELECT    DECODE(ID_ENTIDAD,7124,'UPeU','UPN') GLOSA,        
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7002,7036,7037) THEN COS_VALOR ELSE 0 END) MEDICA,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7000,7001,7009) THEN COS_VALOR ELSE 0 END) ODONTO,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7050,7051) THEN COS_VALOR ELSE 0 END) OFTAL,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7056,7062,7060,7058,7064) THEN COS_VALOR ELSE 0 END) EDUC,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7030) THEN COS_VALOR ELSE 0 END) CASA,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7017) THEN COS_VALOR ELSE 0 END) PROFE,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7080) THEN COS_VALOR ELSE 0 END) DEPREC,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7003,7015,7037,7046,7020,7034) THEN COS_VALOR ELSE 0 END) OTROS,
                        SUM(CASE WHEN ID_CONCEPTOAPS IN (7002,7036,7037,7000,7001,7009,7050,7051,7056,7062,7060,7058,7064,7030,7017,7080,7015,7037,7046,7020,7034) THEN COS_VALOR ELSE 0 END) TOTAL
                FROM VW_APS_PLANILLA
                WHERE ID_ENTIDAD = $entity
                AND ID_ANHO = $year
                AND ID_MES = $month       
                AND ID_CONCEPTOAPS BETWEEN 7000 AND 7200
                GROUP BY ID_ENTIDAD ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function previsionAFP($entity, $year, $month)
    {
        $query = "SELECT          
                        A.ID_PERSONA,
                        A.ID_DEPTO,
                        A.ID_SISTEMAPENSION,
                        --B.NOMBRE AFP,
                        (SELECT NOMBRE FROM APS_SISTEMA_PENSION WHERE APS_SISTEMA_PENSION.ID_SISTEMAPENSION = A.ID_SISTEMAPENSION) AFP,        
                        (SELECT PATERNO||' '||MATERNO||' '||NOMBRE FROM MOISES.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = A.ID_PERSONA) NOMBRE,
                        (SELECT NUM_DOCUMENTO FROM MOISES.PERSONA_DOCUMENTO WHERE MOISES.PERSONA_DOCUMENTO.ID_PERSONA = A.ID_PERSONA) DNI,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1500) THEN A.COS_REFERENCIA2 ELSE 0 END) BASE,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1500) THEN A.COS_VALOR ELSE 0 END) APORTE,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1501) THEN A.COS_VALOR ELSE 0 END) SEGURO,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1508) THEN A.COS_VALOR ELSE 0 END) COMISION,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1500,1501,1508) THEN A.COS_VALOR ELSE 0 END) TOTAL
                FROM VW_APS_PLANILLA A
                WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                AND A.ID_MES = $month
                AND A.ID_CONCEPTOAPS IN (1500, 1501, 1508)
                GROUP BY A.ID_PERSONA,A.ID_DEPTO,A.ID_SISTEMAPENSION--,AFP
                ORDER BY AFP,NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function previsionONP($entity, $year, $month)
    {
        $query = "SELECT          
                        A.ID_PERSONA,
                        A.ID_DEPTO,
                        A.ID_SISTEMAPENSION,        
                        (SELECT NOMBRE FROM APS_SISTEMA_PENSION WHERE APS_SISTEMA_PENSION.ID_SISTEMAPENSION = A.ID_SISTEMAPENSION) AFP,        
                        (SELECT PATERNO||' '||MATERNO||' '||NOMBRE FROM MOISES.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = A.ID_PERSONA) NOMBRE,
                        (SELECT NUM_DOCUMENTO FROM MOISES.PERSONA_DOCUMENTO WHERE MOISES.PERSONA_DOCUMENTO.ID_PERSONA = A.ID_PERSONA) DNI,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1522) THEN A.COS_REFERENCIA2 ELSE 0 END) BASE,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1522) THEN A.COS_VALOR ELSE 0 END) APORTE
                FROM VW_APS_PLANILLA A
                WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                AND A.ID_MES = $month
                AND A.ID_CONCEPTOAPS IN (1522)
                GROUP BY A.ID_PERSONA,A.ID_DEPTO,A.ID_SISTEMAPENSION
                ORDER BY AFP,NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function previsionAFPNET($entity, $year, $month)
    {
        $query = "SELECT          
                        A.ID_PERSONA,
                        A.ID_DEPTO,
                        A.ID_SISTEMAPENSION,        
                        (SELECT NOMBRE FROM APS_SISTEMA_PENSION WHERE APS_SISTEMA_PENSION.ID_SISTEMAPENSION = A.ID_SISTEMAPENSION) AFP,        
                        (SELECT PATERNO||' '||MATERNO||' '||NOMBRE FROM MOISES.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = A.ID_PERSONA) NOMBRE,
                        (SELECT NUM_DOCUMENTO FROM MOISES.PERSONA_DOCUMENTO WHERE MOISES.PERSONA_DOCUMENTO.ID_PERSONA = A.ID_PERSONA) DNI,
                        DECODE(TO_CHAR(A.FEC_TERMINO,'MM'),$month,2,'') TIPO,
                        TO_CHAR(A.FEC_TERMINO,'DD/MM/YYYY') FEC_TERMINO,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1500) THEN A.COS_REFERENCIA2 ELSE 0 END) BASE,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1500) THEN A.COS_VALOR ELSE 0 END) APORTE,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1501) THEN A.COS_VALOR ELSE 0 END) SEGURO,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1508) THEN A.COS_VALOR ELSE 0 END) COMISION,
                        SUM(CASE WHEN A.ID_CONCEPTOAPS IN (1500,1501,1508) THEN A.COS_VALOR ELSE 0 END) TOTAL
                FROM VW_APS_PLANILLA A
                WHERE A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                AND A.ID_MES = $month
                AND A.ID_CONCEPTOAPS IN (1500,1501,1508)
                GROUP BY A.ID_PERSONA,A.ID_DEPTO,A.ID_SISTEMAPENSION,A.FEC_TERMINO
                ORDER BY NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function entidadPersona($entity)
    {
        $query = "SELECT ID_PERSONA,ID_EMPRESA 
                    FROM CONTA_ENTIDAD
                    WHERE ID_ENTIDAD = $entity ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function entidadEmpresa($id_persona)
    {
        // $query = "SELECT
        //         ID_PERSONA,ID_RUC,NOM_COMERCIAL, NOMBRE 
        //         FROM MOISES.VW_PERSONA_JURIDICA
        //         WHERE ID_PERSONA = $id_persona ";

        $query = "SELECT
            ID_PERSONA,ID_RUC,NOM_COMERCIAL, NOMBRE 
        FROM MOISES.VW_PERSONA_JURIDICA
        WHERE ID_RUC IN (
                	SELECT cem.ID_RUC 
                    FROM CONTA_ENTIDAD ce, CONTA_EMPRESA cem
                    WHERE ce.ID_EMPRESA = cem.ID_EMPRESA 
                    AND ce.ID_PERSONA = $id_persona
                ) ";


        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function entidadPlanilla($entity, $id_depto, $year, $month, $id_persona)
    {


        $query = DB::table("aps_planilla as a");
        $query->join("moises.persona as b", 'b.ID_PERSONA', '=', 'A.ID_PERSONA');
        $query->select(
            'A.ID_PERSONA', 'B.PATERNO', 'B.MATERNO', 'B.NOMBRE', 'A.ID_DEPTO', 'A.ID_CONTRATO',
            DB::raw("substr(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE")
        );
        $query->groupBy('A.ID_PERSONA', 'B.PATERNO', 'B.MATERNO', 'B.NOMBRE', 'A.ID_DEPTO', 'A.ID_CONTRATO');
        $query->where("A.ID_ENTIDAD", $entity);
        $query->where("A.ID_ANHO", $year);
        $query->where("A.ID_MES", $month);
        $query->whereraw("SUBSTR(A.ID_DEPTO,1,1)='" . $id_depto . "' 
                    AND A.ID_PERSONA NOT IN(
                    SELECT X.ID_PERSONA FROM APS_PLANILLA_BOLETA X
                    WHERE X.ID_ENTIDAD= A.ID_ENTIDAD
                    AND X.ID_ANHO= A.ID_ANHO
                    AND X.ID_MES= A.ID_MES
                    AND X.ID_CONTRATO= A.ID_CONTRATO
                )");

        if ($id_persona > 0) {
            $query->where("A.ID_PERSONA", $id_persona);
        }

        $query->orderBy('A.ID_DEPTO', 'asc');
        $query->orderBy('B.PATERNO', 'asc');
        $query->orderBy('B.MATERNO', 'asc');
        $query->orderBy('B.NOMBRE', 'asc');

        if ($id_persona > 0) {
            $data = $query->paginate(1);
        }else{
            $data = $query->paginate(100); 
        }
        return $data;
    }

    /*public static function entidadPlanilla($entity,$id_depto,$year,$month,$id_persona){
        $where="";
        
        if ($id_persona>0){
            $where=" and A.ID_PERSONA=".$id_persona." ";
        }
        $query = "SELECT  A.ID_PERSONA,B.PATERNO,B.MATERNO,B.NOMBRE, A.ID_DEPTO,substr(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE,A.ID_CONTRATO
                FROM APS_PLANILLA A, MOISES.PERSONA B
                WHERE A.ID_PERSONA = B.ID_PERSONA
                AND A.ID_ENTIDAD = ".$entity." 
                AND A.ID_ANHO = ".$year."
                AND A.ID_MES = ".$month."
                AND substr(A.ID_DEPTO,1,1) ='".$id_depto."'
                ".$where."
                AND A.ID_PERSONA NOT IN(
                    SELECT X.ID_PERSONA FROM APS_PLANILLA_BOLETA X
                    WHERE X.ID_ENTIDAD= A.ID_ENTIDAD
                    AND X.ID_ANHO= A.ID_ANHO
                    AND X.ID_MES= A.ID_MES
                    AND X.ID_CONTRATO= A.ID_CONTRATO
                )
                GROUP BY A.ID_PERSONA,B.PATERNO,B.MATERNO,B.NOMBRE,A.ID_DEPTO,A.ID_CONTRATO
                ORDER BY ID_DEPTO,B.PATERNO,B.MATERNO,B.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }*/

    public static function personPlanilla($entity, $year, $month, $id_persona)
    {
        $query = "SELECT  
                    A.ID_PERSONA,
                    B.PATERNO,
                    B.MATERNO,
                    B.NOMBRE, 
                    A.ID_DEPTO,
                    substr(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE
                FROM APS_PLANILLA A, MOISES.PERSONA B
                WHERE A.ID_PERSONA = B.ID_PERSONA
                AND A.ID_ENTIDAD = " . $entity . "
                AND A.ID_ANHO = " . $year . "
                AND A.ID_MES = " . $month . "
                AND A.ID_PERSONA= " . $id_persona;
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function person($dni)
    {
        $query = "SELECT ID_PERSONA 
                    FROM VW_APS_EMPLEADO
                    WHERE NUM_DOCUMENTO = '" . $dni . "'
                    GROUP BY ID_PERSONA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function employee($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        $query = "SELECT  
                        C.NOM_PERSONA,
                        A.NOM_CARGO,
                        --'' ESSALUD,
                        --'' CUSS,
                        (SELECT COALESCE(max(NUM_DOCUMENTO ),'') FROM moises.PERSONA_DOCUMENTO pd 
                            WHERE ID_PERSONA = $id_persona
                            AND ID_TIPODOCUMENTO = 97) as ESSALUD,
                            (SELECT COALESCE(max(NUM_DOCUMENTO ),'') FROM moises.PERSONA_DOCUMENTO pd 
                            WHERE ID_PERSONA = $id_persona
                            AND ID_TIPODOCUMENTO = 98) as CUSS,
                        TO_CHAR(C.FEC_NACIMIENTO,'DD/MM/YYYY') AS FEC_NACIMIENTO,
                        C.NUM_DOCUMENTO,
                        FC_MES_NAME(LPAD($mes,2,0))||' del '||$anho AS MES,
                        TO_CHAR(B.FEC_INICIO,'DD/MM/YYYY') AS FEC_INICIO,
                        TO_CHAR(B.FEC_TERMINO,'DD/MM/YYYY') AS FEC_TERMINO,
                        A.NUM_DIAS||' / '||A.NUM_HORAS DH,
                        TO_CHAR(A.INI_VACACIONES,'DD/MM/YYYY')||' al '||TO_CHAR(A.FIN_VACACIONES,'DD/MM/YYYY') VACACIONES,
                        (SELECT X.NOMBRE FROM APS_SISTEMA_PENSION X WHERE X.ID_SISTEMAPENSION = A.ID_SISTEMAPENSION) AFP,
                        TO_CHAR((LAST_DAY(TO_DATE('$mes/$anho','MM/YYYY'))),'DD')||' '||FC_MES_NAME(LPAD($mes,2,0))||' del '||$anho AS MES_NAME,
                        A.ID_ENTIDAD,A.ID_ANHO,A.ID_MES,A.ID_PERSONA,A.ID_CONTRATO,substr(a.id_depto,0,1) as ID_DEPTO_PADRE
                FROM APS_PLANILLA A, APS_EMPLEADO B , MOISES.VW_PERSONA_NATURAL_LIGHT C
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_PERSONA = B.ID_PERSONA 
                AND A.ID_CONTRATO = B.ID_CONTRATO 
                AND A.ID_PERSONA = C.ID_PERSONA
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_CONTRATO = " . $id_contrato . "
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes;

        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function remuneration($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND A.ID_TIPOPLANILLA = 98626 
                AND (A.ID_CONCEPTOAPS BETWEEN 1000 AND 1416 OR A.ID_CONCEPTOAPS IN (7030)
                OR A.ID_CONCEPTOAPS LIKE '3%'
                OR A.ID_CONCEPTOAPS LIKE '2%')
                ORDER BY A.ID_CONCEPTOAPS ";*/
        
        $query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND A.ID_TIPOPLANILLA = 98626
                AND B.TIPO='I'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function retention($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND (A.ID_CONCEPTOAPS BETWEEN 1500 AND 1530)
                AND  A.ID_CONCEPTOAPS NOT IN( 1526,1503,1504,1519,1530,1520)
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND B.TIPO='R'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function contribution($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND A.ID_CONCEPTOAPS IN (9000,9030,9035)
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND B.TIPO='A'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function descuentos($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND A.ID_CONCEPTOAPS IN (1104,1105,1150,1503,1504,1519,1520,1526,1530,1533,1535,1543,1544,1545,1556,1557,1558,5001,7600,7601,9055)
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        A.ID_CONCEPTOAPS, 
                        B.NOMBRE, 
                        A.COS_REFERENCIA1, 
                        A.COS_REFERENCIA2, 
                        A.COS_REFERENCIA3, 
                        TO_CHAR(A.COS_VALOR,'999,999.99') AS COS_VALOR
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND (B.TIPO='D' or B.TIPO1='D')
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function tRemuneration($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "   
                AND A.ID_TIPOPLANILLA = 98626
                AND (A.ID_CONCEPTOAPS BETWEEN 1000 AND 1416 OR A.ID_CONCEPTOAPS IN (7030)
                OR A.ID_CONCEPTOAPS LIKE '3%'
                OR A.ID_CONCEPTOAPS LIKE '2%')
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        TO_CHAR(coalesce(SUM(A.COS_VALOR),0),'999,999.99') IMP,coalesce(SUM(A.COS_VALOR),0) as sueldo
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "   
                AND A.ID_TIPOPLANILLA = 98626
                AND B.TIPO='I'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function tdescuentos($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "                
                AND A.ID_CONCEPTOAPS IN (1104,1105,1150,1503,1504,1519,1520,1526,1530,1533,1535,1543,1544,1545,1556,1557,1558,5001,7600,7601,9055)
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "                
                AND (B.TIPO='D' or B.TIPO1='D')
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function tRetention($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND (A.ID_CONCEPTOAPS BETWEEN 1500 AND 1530)
                AND  A.ID_CONCEPTOAPS NOT IN( 1526,1503,1504,1519,1530,1520)
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . " 
                AND B.TIPO='R'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function tContribution($entity, $id_persona, $anho, $mes, $id_contrato)
    {
       /* $query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "
                AND A.ID_CONCEPTOAPS IN (9000,9030,9035)
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        TO_CHAR(SUM(A.COS_VALOR),'999,999.99') IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "
                AND B.TIPO='A'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function Neto($entity, $id_persona, $anho, $mes, $id_contrato)
    {
        /*$query = "SELECT 
                        TO_CHAR(A.COS_VALOR,'999,999.99') as IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "
                AND A.ID_CONCEPTOAPS = 1552
                ORDER BY A.ID_CONCEPTOAPS ";*/
        $query = "SELECT 
                        TO_CHAR(A.COS_VALOR,'999,999.99') as IMP
                FROM APS_PLANILLA_DETALLE A, APS_CONCEPTO_PLANILLA B
                WHERE A.ID_CONCEPTOAPS = B.ID_CONCEPTOAPS
                AND A.ID_ENTIDAD = " . $entity . " 
                AND A.ID_PERSONA = " . $id_persona . " 
                AND A.ID_ANHO = " . $anho . " 
                AND A.ID_MES = " . $mes . " 
                AND A.ID_CONTRATO=" . $id_contrato . "
                AND B.TIPO = 'N'
                ORDER BY A.ID_CONCEPTOAPS ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    /*Por DNI*/
    public static function quintaCategoria($entity, $anho, $id_empresa, $opc)
    {
        $query = "SELECT
                        moises.VW_PERSONA_NATURAL_LIGHT.NUM_DOCUMENTO,
                        moises.VW_PERSONA_NATURAL_LIGHT.NOM_PERSONA,
                        CASE
                                WHEN Certificado.Renta_Neta > 0 THEN 'Afecto'
                                ELSE 'Inafecto'
                        END estado,
                        Certificado.*,
                        Certificado.Imp_Renta -(Certificado.Retenciones) saldo
                FROM
                        (
                                SELECT
                                        DISTINCT ID_PERSONA
                                FROM
                                        APs_Planilla
                                WHERE
                                        ID_ENTIDAD = $entity
                                        AND ID_ANHO = $anho
                        ) Personas
                INNER JOIN moises.VW_PERSONA_NATURAL_LIGHT ON
                        moises.VW_PERSONA_NATURAL_LIGHT.ID_PERSONA = Personas.ID_PERSONA
                INNER JOIN(
                                SELECT
                                        Datos.*,
                                        ROUND( CASE WHEN $anho >= 2015 THEN CASE WHEN RENTA_NETA <= (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 5 THEN 
                                                    CASE WHEN RENTA_NETA > 0 THEN RENTA_NETA ELSE 0 END ELSE (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 5 END ELSE 0 END * 8 / 100, 2 )+
                                        ROUND( CASE WHEN $anho >= 2015 THEN CASE WHEN RENTA_NETA <= (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 20 THEN CASE WHEN RENTA_NETA - 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 5 > 0 THEN RENTA_NETA - (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 5 ELSE 0 END ELSE 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) *( 20 - 5 ) END ELSE CASE WHEN RENTA_NETA <= (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 20 THEN CASE WHEN RENTA_NETA > 0 THEN RENTA_NETA ELSE 0 END ELSE 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 20 END END * CASE WHEN $anho >= 2015 THEN 14 ELSE 15 END / 100, 2 )+ ROUND( CASE WHEN $anho >= 2015 THEN CASE WHEN RENTA_NETA <= 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 35 THEN CASE WHEN RENTA_NETA - (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 20 > 0 THEN RENTA_NETA - 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 20 ELSE 0 END ELSE (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) *( 35 - 20 ) END ELSE 0 END * 17 / 100, 2 )+ ROUND( CASE WHEN $anho >= 2015 THEN CASE WHEN RENTA_NETA <= 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 45 THEN CASE WHEN RENTA_NETA - (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 35 > 0 THEN RENTA_NETA - 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 35 ELSE 0 END ELSE (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) *( 45 - 35 ) END ELSE 0 END * 20 / 100, 2 )+ ROUND( CASE WHEN $anho >= 2015 THEN CASE WHEN RENTA_NETA > 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 45 THEN CASE WHEN RENTA_NETA - (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 45 > 0 THEN RENTA_NETA - 
                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 45 ELSE 0 END ELSE 0 END ELSE 0 END * 30 / 100, 2 ) Imp_Renta
                                FROM
                                        (
                                                SELECT
                                                        Datos.*,
                                                        (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 7 UIT_7,
                                                        Datos.A + Datos.B + Datos.C + Datos.D + Datos.E + Datos.F + Datos.G + Datos.H + Datos.I + Datos.J AS Renta_Bruta,
                                                        (Datos.A + Datos.B + Datos.C + Datos.D + Datos.E + Datos.F + Datos.G + Datos.H + Datos.I + Datos.J
                                                        )- (SELECT uit from CONTA_PERU_VALOR WHERE ID_ANHO = $anho) * 7 AS Renta_Neta
                                                FROM
                                                        (					
                                                                SELECT
                                                                        APS_Planilla_Detalle.ID_PERSONA,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 1 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) A,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 2 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) B,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 3 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) C,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 4 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) D,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 5 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) E,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 6 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) F,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 7 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) G,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 8 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) H,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 9 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) I,
                                                                        SUM( CASE WHEN Conta_Cuenta_Grupo.NUM_ORDEN = 10 THEN APS_Planilla_Detalle.COS_VALOR ELSE 0 END ) J,
                                                                        SUM( CASE WHEN APS_Planilla_Detalle.ID_CONCEPTOAPS IN( 1502, 9010 ) THEN APS_Planilla_Detalle.COS_VALOR ELSE CASE 
                                                                        WHEN APS_Planilla_Detalle.ID_CONCEPTOAPS IN( 1350 ) THEN - APS_Planilla_Detalle.COS_VALOR ELSE 0 END END ) Retenciones
                                                                FROM APS_Planilla_Detalle INNER JOIN CONTA_Entidad 
                                                                ON APS_Planilla_Detalle.ID_ENTIDAD = Conta_Entidad.ID_ENTIDAD	
                                                                INNER JOIN Conta_Cuenta_Grupo 
                                                                ON Conta_Cuenta_Grupo.ID_TIPOGRUPOCUENTA = 5
                                                                AND '+' || Conta_Cuenta_Grupo.CUENTAS || '+' LIKE '%+' || CAST(APS_Planilla_Detalle.ID_CONCEPTOAPS AS varchar(20)) || '+%'
                                                                WHERE Conta_Entidad.ID_EMPRESA = $id_empresa
                                                                AND APS_Planilla_Detalle.ID_ANHO = $anho 
                                                                AND APS_Planilla_Detalle.ID_TIPOPLANILLA = 98626
                                                                $opc
                                                                GROUP BY APS_Planilla_Detalle.ID_PERSONA						
                                                        ) Datos
                                        ) Datos			
                        ) Certificado ON
                        Personas.ID_PERSONA = Certificado.ID_PERSONA
                ORDER BY MOISES.VW_PERSONA_NATURAL_LIGHT.NOM_PERSONA ";
        //dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameJOR($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? "and  a.id_entidad = " . $id_entidad : "";

        $query = "select distinct 
                    fc_nameentity(a.id_entidad) AS entidad,
                    b.nom_persona as persona,
                    id_entidad, lpad(b.ID_TIPODOCUMENTO,2,'0') as tipo_documento, 
                    case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end as numero_documento, 
                    round(a.NUM_HORAS) as num_horas,
                    round(a.NUM_HORASEXTRA) as num_horasextra,
                    lpad(b.ID_TIPODOCUMENTO,2,'0')||'|'||
                    case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end||'|'|| 
                    round(a.NUM_HORAS)||'|0|'||
                    round(a.NUM_HORASEXTRA)||'|0|' as txt
                    from VW_APS_PLANILLA a, MOISES.VW_PERSONA_NATURAL_LIGHT b
                    where a.ID_PERSONA = b.ID_PERSONA
                    and a.id_empresa = " . $id_empresa . "
                    " . $entidad . "
                    and a.id_anho = " . $id_anho . "  
                    and a.id_mes = " . $id_mes . " 
                    AND a.ID_TIPOCONTRATO NOT IN (81)  -- NO practicantes
                    ORDER BY id_entidad  
                    --and a.NUM_DIASVAC = 0 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameSNL($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? "and  a.id_entidad = " . $id_entidad : "";

        $query = "select distinct 
                    fc_nameentity(ID_Entidad) AS entidad,
                    id_entidad, lpad(b.ID_TIPODOCUMENTO,2,'0') as tipo_documento, 
                    case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end as numero_documento, 
                    23 as dias, 
                    case when a.NUM_DIASVAC = 30 then 31 else a.NUM_DIASVAC end as dias_vacaciones, b.NOM_PERSONA,
                    lpad(b.ID_TIPODOCUMENTO,2,'0')||'|'||case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end||'|'|| '23'||'|'||case when a.NUM_DIASVAC = 30 then 31 else a.NUM_DIASVAC end||'|' as txt
                    from VW_APS_PLANILLA a, MOISES.VW_PERSONA_NATURAL_LIGHT b
                    where a.ID_PERSONA = b.ID_PERSONA
                    and a.id_empresa = " . $id_empresa . "
                    " . $entidad . "
                    and a.id_anho = " . $id_anho . "  
                    and a.id_mes = " . $id_mes . " 
                    and a.NUM_DIASVAC > 0
                    ORDER BY id_entidad
                    --and a.NUM_DIASVAC = 0  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameREM($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? "and  a.id_entidad = " . $id_entidad : "";

        $query = "select 
                fc_nameentity(ID_Entidad) AS entidad,
                ID_Entidad,
                ID_DEPTO,
                tipo,
                doc,
                cod_sunat,
                trim(to_char(cos_valor,'9999990.00')) IMPORTE,
                trim(to_char(cos_valor,'9999990.00')) IMP,
                NOM_PERSONA,
                tipo||'|'||doc||'|'|| cod_sunat||'|'||trim(to_char(cos_valor,'9999990.00')) ||'|'||trim(to_char(cos_valor,'9999990.00')) ||'|' as txt
                from (
                select  
                a.ID_Entidad,
                a.ID_DEPTO,
                lpad(b.ID_TIPODOCUMENTO,2,'0') as tipo, 
                case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end as doc, 
                case when c.COD_SUNAT = '0000' then '0703' else c.COD_SUNAT end as cod_sunat,
                sum(a.COS_VALOR) as cos_valor, 
                b.NOM_PERSONA
                from VW_APS_PLANILLA a, MOISES.VW_PERSONA_NATURAL_LIGHT b, APS_CONCEPTO_PLANILLA c
                where a.ID_PERSONA = b.ID_PERSONA
                and a.ID_CONCEPTOAPS = c.ID_CONCEPTOAPS
                and a.id_empresa = " . $id_empresa . "
                " . $entidad . "
                and a.id_anho = " . $id_anho . "  
                and a.id_mes = " . $id_mes . "
                and a.ID_TIPOPLANILLA in (98626)
                and c.cod_sunat <> '0000'
                AND a.ID_TIPOCONTRATO NOT IN (81)  -- NO practicantes
                group by
                a.ID_Entidad,
                a.ID_DEPTO,
                lpad(b.ID_TIPODOCUMENTO,2,'0'), 
                case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end , 
                case when c.COD_SUNAT = '0000' then '0703' else c.COD_SUNAT end ,
                b.NOM_PERSONA
                union all 
                select  
                a.ID_Entidad,
                a.ID_DEPTO,
                lpad(b.ID_TIPODOCUMENTO,2,'0') as tipo, 
                case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end as doc, 
                '0601' as cod_sunat,
                0 as cos_valor, 
                b.NOM_PERSONA
                from VW_APS_PLANILLA a, MOISES.VW_PERSONA_NATURAL_LIGHT b, APS_CONCEPTO_PLANILLA c
                where a.ID_PERSONA = b.ID_PERSONA
                and a.ID_CONCEPTOAPS = c.ID_CONCEPTOAPS
                and a.id_empresa = " . $id_empresa . "
                " . $entidad . "
                and a.id_anho = " . $id_anho . "  
                and a.id_mes = " . $id_mes . "
                and a.ID_TIPOPLANILLA in (98626)
                and c.cod_sunat <> '0000'
                and a.ID_SISTEMAPENSION = 11
                AND a.ID_TIPOCONTRATO NOT IN (81)  -- NO practicantes
                group by
                a.ID_Entidad,
                a.ID_DEPTO,
                lpad(b.ID_TIPODOCUMENTO,2,'0'), 
                case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end , 
                b.NOM_PERSONA
                ) a
                order by id_entidad, NOM_PERSONA,doc, cod_sunat  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameHonorarios($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? "a.id_entidad = " . $id_entidad . " AND " : "";

        $query = "SELECT
                        c.email as usuario,
                        fc_nameentity(a.id_entidad) AS entidad,
                        a.id_compra,
                        b.id_ruc            AS ruc,
                        fc_nombre_cliente(a.id_proveedor) as proveedor,
                        a.id_comprobante    AS tipo_comp,
                        a.serie             AS serie,
                        a.numero            AS numero,
                        round(a.importe, 2) AS importe,
                        a.fecha_doc         AS fecha_emi,
                        a.fecha_doc   AS fecha_pago,
                        CASE
                            WHEN nvl(a.importe_renta, 0) <> 0 THEN
                                1
                            ELSE
                                0
                        END AS ind_retencion,
                        nvl(a.importe_renta, 0)         AS importe_retencion,
                        '06'|| '|' || b.id_ruc || '|R|' || a.serie || '|'
                        || a.numero || '|' || round(a.importe, 2) || '|' || to_char(a.fecha_doc, 'dd/MM/YYYY')
                        || '|' || to_char(a.fecha_doc, 'dd/MM/YYYY')
                        || '|'|| CASE WHEN nvl(a.importe_renta, 0) <> 0 THEN 1 ELSE 0 END
                        || '|||' AS txt
                    FROM
                        compra a
                        INNER JOIN CONTA_ENTIDAD ce ON ce.ID_ENTIDAD = a.ID_ENTIDAD
                        INNER JOIN MOISES.VW_PERSONA_NATURAL_LEGAL   b ON b.id_persona = a.id_proveedor
                        LEFT JOIN users c ON c.id = a.id_persona
                    WHERE
                        " . $entidad . "
                        a.id_anho = " . $id_anho . "
                        AND a.id_mes = " . $id_mes . "
                        AND a.id_comprobante = '02'
                        AND ce.ID_EMPRESA = " . $id_empresa . "
                    ORDER BY a.id_entidad, a.fecha_doc, a.id_proveedor";
        // print($query);
        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function plamePS4($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? "a.id_entidad = " . $id_entidad . " AND " : "";

        $query = "SELECT
                    fc_nameentity(a.id_entidad) AS entidad,
                    b.id_ruc AS ruc,
                    c.paterno,
                    c.materno,
                    c.nombre,
                    '06'
                    || '|'
                    || b.id_ruc
                    || '|'
                    || upper(c.paterno)
                    || '|'
                    || upper(c.materno)
                    || '|'
                    || upper(c.nombre)
                    || '|1|0|' AS txt
                FROM
                    compra                a
                    INNER JOIN CONTA_ENTIDAD ce ON ce.ID_ENTIDAD = a.ID_ENTIDAD
                    INNER JOIN MOISES.VW_PERSONA_NATURAL_LEGAL   b ON b.id_persona = a.id_proveedor
                    INNER JOIN moises.persona c ON c.id_persona = b.id_persona
                WHERE
                    " . $entidad . "
                    a.id_anho = " . $id_anho . "
                    AND a.id_mes = " . $id_mes . "
                    AND ce.ID_EMPRESA = " . $id_empresa . "
                    AND a.id_comprobante = '02'
                GROUP BY
                    a.id_entidad,
                    b.id_ruc,
                    c.paterno,
                    c.materno,
                    c.nombre
                ORDER BY
                    a.id_entidad,
                    c.paterno,
                    c.materno,
                    c.nombre";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameFOR($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? " AND a.id_entidad = " . $id_entidad : "";

        $query = "SELECT DISTINCT 
                    fc_nameentity(a.id_entidad) AS entidad,
                    b.nom_persona as persona,
                    id_entidad, lpad(b.ID_TIPODOCUMENTO,2,'0') as tipo_documento, 
                    case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end as numero_documento, 
                    round(a.COS_VALOR, 2) as monto_pagado,
                    lpad(b.ID_TIPODOCUMENTO,2,'0')||'|'||
                    case when b.ID_TIPODOCUMENTO = 4 then lpad(b.NUM_DOCUMENTO,9,'0') else b.NUM_DOCUMENTO end||'|'|| 
                    round(a.COS_VALOR, 2)||'|' as txt
                    FROM VW_APS_PLANILLA a, MOISES.VW_PERSONA_NATURAL_LIGHT b
                    WHERE a.ID_PERSONA = b.ID_PERSONA
                    AND a.id_empresa = " . $id_empresa . "
                    " . $entidad . "
                    and a.id_anho = " . $id_anho . "  
                    and a.id_mes = " . $id_mes . " 
                    and a.id_conceptoaps=1020 AND b.ID_TIPODOCUMENTO NOT IN (97,98)
                    ORDER BY persona";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameTOC($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? " AND a.id_entidad = " . $id_entidad : "";

        $query = "SELECT DISTINCT
                    a.nom_cargo,
                    b.nom_persona,
                    b.num_documento,
                    fc_nameentity(a.id_entidad) AS entidad,
                    lpad(b.id_tipodocumento, 2, '0')
                    || '|'
                    || b.num_documento
                    || '|0|1||1|' AS txt
                FROM
                    vw_aps_planilla      a
                    INNER JOIN moises.VW_PERSONA_NATURAL_LIGHT   b ON a.id_persona = b.id_persona
                WHERE
                    a.ID_CONCEPTOAPS = 1517
                    " . $entidad . "
                    AND a.id_anho = " . $id_anho . "
                    AND a.id_mes = " . $id_mes . "
                    AND a.id_empresa = " . $id_empresa . "
                ORDER BY
                    a.nom_cargo";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function plameTOC_OLD($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? " AND a.id_entidad = " . $id_entidad : "";

        $query = "SELECT DISTINCT
                    a.nom_cargo,
                    b.nom_persona,
                    b.num_documento,
                    fc_nameentity(a.id_entidad) AS entidad,
                    lpad(b.id_tipodocumento, 2, '0')
                    || '|'
                    || b.num_documento
                    || '|0|0|0|1|' AS txt
                FROM
                    vw_aps_planilla      a
                    INNER JOIN moises.VW_PERSONA_NATURAL_LIGHT   b ON a.id_persona = b.id_persona
                WHERE
                    a.id_tipocontrato = 81
                    " . $entidad . "
                    AND a.id_anho = " . $id_anho . "
                    AND a.id_mes = " . $id_mes . "
                    AND a.id_empresa = " . $id_empresa . "
                ORDER BY
                    a.nom_cargo";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    


}
<?php
namespace App\Http\Data\HumanTalent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialBenefitsData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function yearPayrollRegistry($request)
    {
        $entity = $request->query('entity');

        $getYear = DB::table('APS_PLANILLA_DETALLE')->select('ID_ANHO')->where('ID_ENTIDAD', $entity)->groupBy('ID_ANHO')->orderBy('ID_ANHO', 'DESC')->get();
        return $getYear;
/*
$queryy = "SELECT ID_ANHO From APS_PLANILLA_DETALLE where ID_ENTIDAD = " .$entity. " GROUP BY ID_ANHO  ORDER BY ID_ANHO DESC";
$oQuery = DB::select($queryy);
return $oQuery; */

    }

    public static function listTypeBankAccount()
    {

        $query = "SELECT * FROM  ELISEO.TIPO_CTA_BANCO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listBanks()
    {

        $query = "SELECT * FROM  ELISEO.CAJA_ENTIDAD_FINANCIERA WHERE ESTADO='1'";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function ctsTotal($id_entidad, $id_anho, $id_tramo)
    {
        $date_start = null;
        $date_finish = null;

        if ($id_tramo == '1') {
            $date_start = ($id_anho - 1) . "-11-01";
            $date_finish = $id_anho . "-04-30";
        } else {
            $date_start = $id_anho . "-05-01";
            $date_finish = $id_anho . "-10-31";
        }

        $query = "SELECT
                        COUNT(DISTINCT A.ID_PERSONA) AS TOTAL
                FROM (SELECT * FROM APS_EMPLEADO A
                INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                        A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                WHERE (A.ID_TIPOCONTRATO IN (1,4) OR (A.ID_TIPOCONTRATO = 2 AND (FC_OBTENER_NUM_HORAS(A.ID_CONTRATO, A.ID_PERSONA, A.ID_ENTIDAD))/4 >=20)) AND
                        (A.FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR A.FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (A.FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                        A.ID_ENTIDAD = " . $id_entidad . " AND A.ESTADO IS NOT NULL AND
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) > 0";
                #print_r($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function ctsCalculation($id_entidad, $id_anho, $id_tramo, $pageSize)
    {
        $date_start = null;
        $date_finish = null;

        if ($id_tramo == '1') {
            $date_start = ($id_anho - 1) . "-11-01";
            $date_finish = $id_anho . "-04-30";
        } else {
            $date_start = $id_anho . "-05-01";
            $date_finish = $id_anho . "-10-31";
        }
        $result=DB::table(
                DB::raw("(
                SELECT
                        RECORDS.*,
                        TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                        ROUND(RECORDS.GRATI/6,2) AS D_GRATI,
                        RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD + (RECORDS.GRATI/6) AS RC_CTS,
                        ROUND((RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD)*4,2) AS ULT_CUATRO_SUELDOS
                FROM (
                        SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                SELECT DISTINCT
                B.NOM_PERSONA,
                B.NUM_DOCUMENTO,
                FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
				A.ID_PERSONA,
				TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
				TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                (CASE WHEN A.FEC_ENTIDAD > TO_DATE('" . $date_start . "', 'YYYY-MM-DD') THEN a.FEC_ENTIDAD ELSE TO_DATE('" . $date_start . "', 'YYYY-MM-DD') END ) AS INGRESO,
                (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                        WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                        ELSE A.FEC_TERMINO END ) AS SALIDA,
                FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS BASICO,
                FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL,
                FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_VARIABLE,
                FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE,
                FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_CARGO,
                FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISIONES,
                FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS VIATICOS_LD,
                FC_FALTAS(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS FALTAS,
                ROUND(NVL((SELECT CAST(SUM(COS_VALOR) AS FLOAT)
                        FROM APS_PLANILLA_DETALLE
                        WHERE ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONCEPTOAPS IN (3000, 3079, 3086, 3121, 3122, 3126, 3145, 3151, 3157, 3171, 3212, 3222) AND ID_PERSONA = A.ID_PERSONA
                        AND ID_MES = (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) = 11 THEN 12 ELSE 7 END)
                        AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                ),0),2) AS GRATI
                FROM APS_EMPLEADO A
                INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                        A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                WHERE
                        (A.ID_TIPOCONTRATO IN (1,4) OR (A.ID_TIPOCONTRATO = 2 AND (FC_OBTENER_NUM_HORAS(A.ID_CONTRATO, A.ID_PERSONA, A.ID_ENTIDAD))/4 >= 20)) AND
                        (A.FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR A.FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (A.FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                        A.ID_ENTIDAD = " . $id_entidad . " AND A.ESTADO IS NOT NULL
                )T) RECORDS) FILAS"))
                ->select(
                        DB::raw("FILAS.*"),
                        DB::raw("ROUND((FILAS.RC_CTS/12)*FILAS.MESES,2) AS X_MESES"),
                        DB::raw("ROUND((FILAS.RC_CTS/360)*FILAS.DIAS,2) AS X_DIAS"),
                        DB::raw(" ROUND((FILAS.RC_CTS/12)*FILAS.MESES + (FILAS.RC_CTS/360)*FILAS.DIAS,2) AS TOTAL")
                );
                if($pageSize){
                       $result=$result->paginate($pageSize);
                }else{
                        $result=$result->get();
                }
        //print_r($query);
        return $result;
    }

    public static function ctsProvision($id_entidad, $id_anho, $id_tramo,$pageSize)
    {
        $date_start = null;
        $date_finish = null;

        if ($id_tramo == '1') {
                $date_start = ($id_anho - 1) . "-11-01";
                $date_finish = $id_anho . "-04-30";
        } else {
                $date_start = $id_anho . "-05-01";
                $date_finish = $id_anho . "-10-31";
        }


        $result=DB::table(
                DB::raw("(
                        SELECT
                                LISTA.ID_ENTIDAD,
                                LISTA.ID_PERSONA,
                                LISTA.NOM_PERSONA,
                                LISTA.NUM_DOCUMENTO,
                                LISTA.MESES,
                                LISTA.TOTAL,
                                FC_PROVISION_CTS(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO) AS M1,
                                FC_PROVISION_CTS(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO + 1) AS M2,
                                FC_PROVISION_CTS(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, EXTRACT(YEAR FROM ADD_MONTHS(TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),2)), EXTRACT(MONTH FROM ADD_MONTHS(TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),2))) AS M3,
                                FC_PROVISION_CTS(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, EXTRACT(YEAR FROM ADD_MONTHS(TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),3)), EXTRACT(MONTH FROM ADD_MONTHS(TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),3))) AS M4,
                                FC_PROVISION_CTS(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, EXTRACT(YEAR FROM ADD_MONTHS(TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),4)), EXTRACT(MONTH FROM ADD_MONTHS(TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),4))) AS M5,
                                LISTA.ASSINET
                        FROM (
                        SELECT
                                FILAS.NOM_PERSONA,
                                FILAS.NUM_DOCUMENTO,
                                FILAS.MESES,
                                FILAS.DIAS,
                                FILAS.ID_ENTIDAD,
                                FILAS.ID_PERSONA,
                                ROUND(FILAS.ASSINET,2) AS ASSINET,
                                ROUND((FILAS.RC_CTS/12)*FILAS.MESES,2) AS X_MESES,
                                ROUND((FILAS.RC_CTS/360)*FILAS.DIAS,2) AS X_DIAS,
                                EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) ANHO,
                                EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) MES_UNO,
                                ROUND((FILAS.RC_CTS/12)*FILAS.MESES + (FILAS.RC_CTS/360)*FILAS.DIAS,2) AS TOTAL
                        FROM (
                        SELECT
                                RECORDS.*,
                                TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                                ROUND(RECORDS.GRATI/6,2) AS D_GRATI,
                                RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD + (RECORDS.GRATI/6) AS RC_CTS,
                                ROUND((RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD)*4,2) AS ULT_CUATRO_SUELDOS
                        FROM (
                                SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                                SELECT DISTINCT
                                A.ID_ENTIDAD,
                                A.ID_PERSONA,
                                B.NOM_PERSONA,
                                B.NUM_DOCUMENTO,
                                FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
				A.ID_PERSONA,
				TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
				TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                                FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                                (CASE WHEN A.FEC_ENTIDAD > TO_DATE('" . $date_start . "', 'YYYY-MM-DD') THEN a.FEC_ENTIDAD ELSE TO_DATE('" . $date_start . "', 'YYYY-MM-DD') END ) AS INGRESO,
                                (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                        WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                        ELSE A.FEC_TERMINO END ) AS SALIDA,
                                FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS BASICO,
                                FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL,
                                FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_VARIABLE,
                                FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE,
                                FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_CARGO,
                                FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                                FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISIONES,
                                FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS VIATICOS_LD,
                                FC_FALTAS(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS FALTAS,
                                ROUND(NVL((SELECT CAST(SUM(COS_VALOR) AS FLOAT)
                                        FROM APS_PLANILLA_DETALLE
                                        WHERE ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONCEPTOAPS IN (3000, 3079, 3086, 3121, 3122, 3126, 3145, 3151, 3157, 3171, 3212, 3222) AND ID_PERSONA = A.ID_PERSONA
                                        AND ID_MES = (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) = 11 THEN 12 ELSE 7 END)
                                        AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                                ),0),2) AS GRATI,
                                (SELECT SUM(HABER) FROM VW_CONTA_DIARIO
                                        WHERE ID_ENTIDAD = A.ID_ENTIDAD
                                        AND CONCAT(ID_ANHO,LPAD(ID_MES,2,0))>=CONCAT(EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')),LPAD(EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')),2,0))
                                        AND CONCAT(ID_ANHO,LPAD(ID_MES,2,0))<=CONCAT(EXTRACT(YEAR FROM TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')),LPAD((EXTRACT(MONTH FROM TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))-1),2,0))
                                        AND ID_CUENTAAASI = 2135008 AND ID_CTACTE = B.NUM_DOCUMENTO  AND ID_TIPOASIENTO NOT IN ('BB')
                                        ) AS ASSINET
                                FROM APS_EMPLEADO A
                                INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                                        A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                                WHERE
                                        (A.ID_TIPOCONTRATO IN (1,4) OR (A.ID_TIPOCONTRATO = 2 AND (FC_OBTENER_NUM_HORAS(A.ID_CONTRATO, A.ID_PERSONA, A.ID_ENTIDAD))/4 >= 20)) AND
                                        (A.FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR A.FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                                        (A.FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                                        A.ID_ENTIDAD = " . $id_entidad . " AND A.ESTADO IS NOT NULL
                        )T) RECORDS WHERE RECORDS.MONTHS_TOTAL > 0  ORDER BY RECORDS.line_number) FILAS
                        ) LISTA
                        ) DATOS"))
                ->select(
                        'DATOS.NOM_PERSONA',
                        'DATOS.NUM_DOCUMENTO',
                        'DATOS.MESES',
                        'DATOS.TOTAL',
                        'DATOS.M1',
                        'DATOS.M2',
                        'DATOS.M3',
                        'DATOS.M4',
                        'DATOS.M5',
                        DB::raw("ROUND(DATOS.M1 + DATOS.M2 + DATOS.M3 + DATOS.M4 + DATOS.M5,2) AS TOTAL_APS"),
                        'DATOS.ASSINET',
                        DB::raw("ROUND(DATOS.TOTAL - DATOS.ASSINET, 2) AS PROV_RESTANTE")
                );
                if($pageSize){
                       $result=$result->paginate($pageSize);
                }else{
                        $result=$result->get();
                }
        
        return $result;
    }

    public static function ctsSummary($id_entidad, $id_anho, $id_tramo, $pageSize)
    {
        $date_start = null;
        $date_finish = null;
        $paginate = '';

        if ($id_tramo == '1') {
            $date_start = ($id_anho - 1) . "-11-01";
            $date_finish = $id_anho . "-04-30";
        } else {
            $date_start = $id_anho . "-05-01";
            $date_finish = $id_anho . "-10-31";
        }
        $result=DB::table(
                DB::raw("(
                        SELECT
                                RECORDS.ID_PERSONA,
                                RECORDS.NOM_PERSONA,
                                RECORDS.NUM_DOCUMENTO,
                                RECORDS.ENTIDAD_BANCARIA,
                                RECORDS.CTA_BANCARIA,
                                RECORDS.DIAS,
                                TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                                RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD + (RECORDS.GRATI/6) AS RC_CTS,
                                ROUND((RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD)*4,2) AS SUELDOS
                        FROM (
                                SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                        SELECT DISTINCT
                        A.ID_PERSONA,
                        B.NOM_PERSONA,
                        B.NUM_DOCUMENTO,
                        PCB.NOMBRE_BANCO AS ENTIDAD_BANCARIA,
                        PCB.CUENTA AS CTA_BANCARIA,
                        FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
				A.ID_PERSONA,
				TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
				TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISIONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS VIATICOS_LD,
                        FC_FALTAS(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS FALTAS,
                        ROUND(NVL((SELECT CAST(SUM(COS_VALOR) AS FLOAT)
                                FROM ELISEO.APS_PLANILLA_DETALLE
                                WHERE ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONCEPTOAPS IN (3000, 3079, 3086, 3121, 3122, 3126, 3145, 3151, 3157, 3171, 3212, 3222) AND ID_PERSONA = A.ID_PERSONA
                                AND ID_MES = (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) = 11 THEN 12 ELSE 7 END)
                                AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                        ),0),2) AS GRATI
                        FROM (SELECT * FROM ELISEO.APS_EMPLEADO WHERE
                        (ID_TIPOCONTRATO IN (1,4) OR (ID_TIPOCONTRATO = 2 AND (FC_OBTENER_NUM_HORAS(ID_CONTRATO, ID_PERSONA, ID_ENTIDAD))/4 >= 20)) AND
                        (FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR FEC_TERMINO IS NULL) AND
                        ID_ENTIDAD = " . $id_entidad . " AND ESTADO IS NOT NULL)A
                        INNER JOIN (SELECT * FROM MOISES.VW_PERSONA_NATURAL_LIGHT WHERE ID_TIPODOCUMENTO NOT IN (97,98)) B ON
                                A.ID_PERSONA = B.ID_PERSONA
                        LEFT JOIN (
                                SELECT PB.ID_PERSONA, PB.CUENTA, CEF.NOMBRE AS NOMBRE_BANCO
                                FROM MOISES.PERSONA_CUENTA_BANCARIA PB
                                INNER JOIN (SELECT ID_PERSONA, MAX(FEC_INICIO) FEC_INICIO FROM MOISES.PERSONA_CUENTA_BANCARIA  WHERE (FEC_FIN <= TO_DATE('2019-04-30', 'YYYY-MM-DD') OR FEC_FIN IS NULL)
                                AND ID_TIPOCTABANCO = 4 AND ACTIVO=1 
                                GROUP BY ID_PERSONA) PB1 ON (PB.ID_PERSONA = PB1.ID_PERSONA AND PB.FEC_INICIO = PB1.FEC_INICIO)
                                INNER JOIN ELISEO.CAJA_ENTIDAD_FINANCIERA CEF ON (PB.ID_BANCO = CEF.ID_BANCO)
                                ) PCB ON PCB.ID_PERSONA = A.ID_PERSONA
                                )T
                        ) RECORDS)"))
                ->select(
                        DB::raw("ID_PERSONA"),
                        DB::raw("NOM_PERSONA"),
                        DB::raw("NUM_DOCUMENTO"),
                        DB::raw("ENTIDAD_BANCARIA"),
                        DB::raw("CTA_BANCARIA"),
                        DB::raw("ROUND((RC_CTS/12)*MESES + (RC_CTS/360)*DIAS,2) AS TOTAL"),
                        DB::raw("SUELDOS")
                );
                $result=$result->orderBy('ENTIDAD_BANCARIA')
                ->orderBy('NOM_PERSONA');
                #print_r($result->toSql());
                if($pageSize){
                       $result=$result->paginate($pageSize);
                }else{
                        $result=$result->get();
                }
                
        return $result;
    }

    public static function getConstanciaDepCtsPerson($id_entidad, $id_persona, $date_start, $date_finish)
    {

        $query = "SELECT
                        FILAS.*,
                        ROUND((FILAS.RC_CTS/12)*FILAS.MESES,2) AS X_MESES,
                        ROUND((FILAS.RC_CTS/360)*FILAS.DIAS,2) AS X_DIAS,
                        ROUND((FILAS.RC_CTS/12)*FILAS.MESES + (FILAS.RC_CTS/360)*FILAS.DIAS,2) AS TOTAL
                FROM (
                        SELECT
                                RECORDS.*,
                                TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                                ROUND(RECORDS.GRATI/6,2) AS D_GRATI,
                                RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD + (RECORDS.GRATI/6) AS RC_CTS,
                                ROUND((RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD)*4,2) AS ULT_CUATRO_SUELDOS
                        FROM (
                        SELECT
                        B.NOMBRE,
                        B.PATERNO,
                        B.MATERNO,
                        B.NUM_DOCUMENTO,
                        FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
                        A.ID_PERSONA,
                        TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
                        TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                        (CASE WHEN A.FEC_ENTIDAD > TO_DATE('" . $date_start . "', 'YYYY-MM-DD') THEN a.FEC_ENTIDAD ELSE TO_DATE('" . $date_start . "', 'YYYY-MM-DD') END ) AS INGRESO,
                        (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                ELSE A.FEC_TERMINO END ) AS SALIDA,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISIONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS VIATICOS_LD,
                        FC_FALTAS(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AS FALTAS,
                        ROUND(NVL((SELECT CAST(SUM(COS_VALOR) AS FLOAT)
                                FROM ELISEO.APS_PLANILLA_DETALLE
                                WHERE ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONCEPTOAPS IN (3000, 3079, 3086, 3121, 3122, 3126, 3145, 3151, 3157, 3171, 3212, 3222) AND ID_PERSONA = A.ID_PERSONA
                                AND ID_MES = (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) = 11 THEN 12 ELSE 7 END)
                                AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                        ),0),2) AS GRATI
                        FROM (SELECT * FROM ELISEO.APS_EMPLEADO WHERE  (ID_TIPOCONTRATO IN (1,4) OR (ID_TIPOCONTRATO = 2 AND (FC_OBTENER_NUM_HORAS(ID_CONTRATO, ID_PERSONA, ID_ENTIDAD))/4 >= 20)) AND
                                (FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                                (FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR FEC_TERMINO IS NULL) AND
                                ID_ENTIDAD = " . $id_entidad . " AND ID_PERSONA= " . $id_persona . " AND ESTADO IS NOT NULL) A
                        INNER JOIN (SELECT * FROM  MOISES.VW_PERSONA_NATURAL_LIGHT WHERE ID_TIPODOCUMENTO NOT IN (97,98))B ON
                                A.ID_PERSONA = B.ID_PERSONA
                               
                        ) RECORDS
                        ) FILAS";
                        //print_r($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getIdCalcuCts($request)
    {

        $entity = $request->query('entity');
        $person = $request->query('person');
        $date_ini = $request->query('date_ini');
        $date_finaly = $request->query('date_finaly');
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');

        $queryy = "SELECT
                        FILAS.*,
                        ROUND((FILAS.RC_CTS/12)*FILAS.MESES,2) AS T_MESES,
                        ROUND((FILAS.RC_CTS/360)*FILAS.DIAS,2) AS T_DIAS,
                        ROUND((FILAS.RC_CTS/12)*FILAS.MESES + (FILAS.RC_CTS/360)*FILAS.DIAS,2) AS TOTAL
                        FROM (
                        SELECT
                                RECORDS.*,
                                TRUNC(MONTHS_BETWEEN(RECORDS.SALIDA + 1, RECORDS.INGRESO)) AS MESES,
                                ROUND((MONTHS_BETWEEN(RECORDS.SALIDA + 1, RECORDS.INGRESO)-TRUNC(MONTHS_BETWEEN(RECORDS.SALIDA + 1, RECORDS.INGRESO)))*30, 0) AS DIAS,
                                ROUND(RECORDS.GRATI/6,2) AS D_GRATI,
                                ROUND(RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_ESPECIE + RECORDS.REMUN_VARIABLE + RECORDS.VIATICOS_LD + (RECORDS.GRATI/6),2) AS RC_CTS
                        FROM (
                        SELECT
                                --fc_nameentity(A.ID_ENTIDAD) AS ENTIDAD,
                                A.ID_ENTIDAD,
                                A.ID_PERSONA,
                                B.NOM_PERSONA,
                                B.NUM_DOCUMENTO,
                                B.PATERNO,
                                B.MATERNO,
                                B.NOMBRE AS NOM_COMPLETO,
                                --A.FEC_INICIO,
                                --A.FEC_TERMINO,
                                (CASE WHEN A.FEC_INICIO > TO_DATE('" . $date_ini . "', 'YYYY-MM-DD') THEN a.FEC_INICIO ELSE TO_DATE('" . $date_ini . "', 'YYYY-MM-DD') END ) AS INGRESO,
                                (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD') ELSE A.FEC_TERMINO END ) AS SALIDA,
                                FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_ini . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD'))  AS BASICO,
                                FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_ini . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD'))  AS PRIMA_INFANTIL,
                                FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_ini . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD'))  AS REMUN_VARIABLE,
                                FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_ini . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD'))  AS REMUN_ESPECIE,
                                FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_ini . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD'))  AS VIATICOS_LD,
                                ROUND(NVL((SELECT CAST(COS_VALOR AS FLOAT)
                                    FROM APS_PLANILLA_DETALLE
                                    WHERE ID_ENTIDAD = A.ID_ENTIDAD
                                    AND ID_CONCEPTOAPS = '3000'
                                    AND ID_PERSONA = A.ID_PERSONA
                                    AND ID_MES = (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $date_ini . "', 'YYYY-MM-DD')) = 11 THEN 12 ELSE 6 END)
                                    AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_ini . "', 'YYYY-MM-DD'))
                                ),0),2) AS GRATI
                        FROM APS_EMPLEADO A
                        INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                                A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                        WHERE
                                A.FEC_INICIO <= TO_DATE('" . $date_finaly . "', 'YYYY-MM-DD') AND
                                (A.FEC_TERMINO > TO_DATE('" . $date_ini . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                                A.ID_ENTIDAD = " . $entity . " AND A.ID_PERSONA = " . $person . " AND
                                (UPPER(B.NOM_PERSONA) LIKE UPPER('%" . $search . "%') OR B.NUM_DOCUMENTO LIKE '%" . $search . "%')
                        ) RECORDS
                        ) FILAS";
        $oQuery = DB::select(DB::raw($queryy));
        $result = [
            'data' => $oQuery,
        ];
        return $result;
    }

    public static function getPersonBankAccount($id_persona, $date_start, $date_finish, $id_tipoctabanco)
    {
        $queryy = "SELECT P.ID_PERSONA, P.NOMBRE, P.PATERNO, P.MATERNO, B.NUM_DOCUMENTO, B.SEXO, B.ID_TIPODOCUMENTO,
                        PCB.ID_PBANCARIA, PCB.ID_BANCO, CEF.NOMBRE AS NAME_BANK, PCB.ID_TIPOCTABANCO, PCB.CUENTA,
                        PCB.CCI, PCB.ACTIVO, PCB.FEC_INICIO, PCB.FEC_FIN
                        FROM MOISES.PERSONA P
                LEFT OUTER JOIN (
                SELECT *
                FROM MOISES.PERSONA_CUENTA_BANCARIA
                WHERE ID_PERSONA = " . $id_persona . " AND FEC_INICIO = (SELECT MAX(FEC_INICIO) FROM MOISES.PERSONA_CUENTA_BANCARIA
                        WHERE (FEC_FIN <= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR FEC_FIN IS NULL)
                        AND ID_PERSONA = " . $id_persona . " AND ID_TIPOCTABANCO = " . $id_tipoctabanco . " AND ACTIVO=1)
                ) PCB ON
                        PCB.ID_PERSONA = P.ID_PERSONA
                LEFT OUTER JOIN CAJA_ENTIDAD_FINANCIERA CEF ON (PCB.ID_BANCO = CEF.ID_BANCO)
                LEFT OUTER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                                P.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO IN (1,4,7)
                WHERE P.ID_PERSONA = " . $id_persona;

        $oQuery = DB::select(DB::raw($queryy));
        return $oQuery;

    }

    public static function getPdfHolidayRecord($entity, $person, $fec_termino, $fec_pago, $id_tipo_cese,$fec_vac)
    {
        $query="SELECT T1.*,(T1.VAC_TRUNC-(T1.SIST_PEN_TOTAL+T1.IR_5TA_CAT+T1.DIEZMO)) AS TOTAL FROM (SELECT
                T.*,
                ROUND(T.VAC_TRUNC*0.10,2) AS DIEZMO,
                ROUND(T.VAC_TRUNC*0.07,2) AS IR_5TA_CAT,
                ROUND(CASE
                        WHEN T.SISTEMA_PENSION_TAZA IS NOT NULL
                        AND T.SISTEMA_PENSION_TAZA != 0 THEN T.VAC_TRUNC*(T.SISTEMA_PENSION_TAZA / 100)
                        ELSE 0
                END,2) AS SIST_PEN_TOTAL
        FROM
                (
                SELECT
                        E.ID_PERSONA, E.NOM_PERSONA, E.NUM_DOCUMENTO, FC_OBTENER_CARGO(E.ID_CONTRATO,
                        E.ID_PERSONA,
                        E.ID_ENTIDAD) CARGO, TO_DATE('$fec_termino', 'YYYY-MM-DD') AS FECHA_CESE, 
                        (
                        SELECT
                        SUM(ROUND(CASE WHEN DATOS.VAC>0 THEN ((DATOS.VAC / 12)* DATOS.MESES_VAC_PLN)+(((DATOS.VAC / 12)/ 30)* DATOS.DIAS_VAC_PLN) ELSE 0 END, 2))
                        FROM
                        (
                        SELECT
                                ITEMS2.VAC, TRUNC((ITEMS2.NETO_DIAS_VAC / 30)-(12 * ANHOS_VAC_PLN)) AS MESES_VAC_PLN, 
                                ROUND(((ITEMS2.NETO_DIAS_VAC / 30)-TRUNC(ITEMS2.NETO_DIAS_VAC / 30))* 30,0) AS DIAS_VAC_PLN
                                FROM (
                                        SELECT
                                        ITEMS1.VAC, ITEMS1.NETO_DIAS_VAC,
                                        CASE
                                                WHEN ITEMS1.NETO_DIAS_VAC>360 THEN TRUNC(ITEMS1.NETO_DIAS_VAC / 360)
                                                ELSE 0
                                        END AS ANHOS_VAC_PLN
                                        FROM
                                        (
                                        SELECT
                                                ITEMS.VAC,
                                                CASE
                                                        WHEN ITEMS.TOTAL_DIAS_VAC>0
                                                        AND ITEMS.TOTAL_DIAS_VAC>ITEMS.FALTAS_VAC THEN ITEMS.TOTAL_DIAS_VAC-ITEMS.FALTAS_VAC
                                                        ELSE 0
                                                END AS NETO_DIAS_VAC
                                                FROM
                                                (
                                                SELECT
                                                        RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_VARIABLE + RECORDS.REMUN_ESPECIE + RECORDS.BONIFICACION_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISONES + RECORDS.VIATICOS_LD AS VAC, 
                                                        ELISEO.DAYS360(RECORDS.VAC_FECHA,
                                                        RECORDS.FECHA_CESE + 1) AS TOTAL_DIAS_VAC, 0 AS FALTAS_VAC
                                                FROM
                                                        (
                                                        SELECT
                                                                FC_SUELDO_BASICO(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS BASICO, FC_PRIMA_INFANTIL(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS PRIMA_INFANTIL, FC_REMUN_VARIABLE(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS REMUN_VARIABLE, FC_RESUMEN_ESPECIE(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS REMUN_ESPECIE, FC_BONIFICACION_CARGO(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS BONIFICACION_CARGO, FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS BON_ESP_VOLUNTARIA, FC_COMISIONES(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS COMISONES, FC_VIATICOS_LD(A.ID_ENTIDAD,
                                                                A.ID_PERSONA,
                                                                A.FEC_INICIO,
                                                                TO_DATE('$fec_termino', 'YYYY-MM-DD')) AS VIATICOS_LD, TO_DATE('$fec_termino', 'YYYY-MM-DD') AS FECHA_CESE, 
                                                                /*(
                                                                                        CASE WHEN EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) = EXTRACT(YEAR FROM A.FEC_INICIO) THEN A.FEC_INICIO
                                                                                        ELSE TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| '01-01', 'YYYY-MM-DD')
                                                                                END) */
                                                                TO_DATE('".$fec_vac."', 'YYYY-MM-DD') AS VAC_FECHA
                                                                FROM
                                                                (
                                                                SELECT
                                                                        *
                                                                FROM
                                                                        VW_APS_EMPLEADO
                                                                WHERE
                                                                        ID_ENTIDAD = $entity
                                                                        AND ID_PERSONA =  $person
                                                                        AND ID_TIPODOCUMENTO NOT IN (97,98)
                                                                        AND (TO_DATE('$fec_termino', 'YYYY-MM-DD') BETWEEN FEC_INICIO AND NVL(FEC_TERMINO, TO_DATE(TO_CHAR(SYSDATE, 'YYYY-MM-DD'), 'YYYY-MM-DD'))))A) RECORDS ) ITEMS )ITEMS1)ITEMS2 ) DATOS
                                
                        ) AS VAC_TRUNC, 
                        NVL(FC_OBTENER_SP_TASA(NVL(FC_OBTENER_SP(E.ID_CONTRATO, E.ID_PERSONA, E.ID_ENTIDAD), NULL), 
                        EXTRACT(YEAR FROM TO_DATE('$fec_termino', 'YYYY-MM-DD')), EXTRACT(MONTH FROM TO_DATE('$fec_termino', 'YYYY-MM-DD'))), 0) AS SISTEMA_PENSION_TAZA
                FROM
                        VW_APS_EMPLEADO E
                WHERE
                        E.ID_ENTIDAD = $entity
                        AND E.ID_PERSONA = $person
                        AND E.ID_TIPODOCUMENTO NOT IN (97,98)
                        AND (TO_DATE('$fec_termino', 'YYYY-MM-DD') BETWEEN E.FEC_INICIO AND NVL(E.FEC_TERMINO,TO_DATE(TO_CHAR(SYSDATE, 'YYYY-MM-DD'), 'YYYY-MM-DD'))))T)T1";
        $oQuery = DB::select($query);
        return $oQuery;

    }

    public static function listBank($request)
    {
        $person = $request->query('person');
        $date_ini = $request->query('date_ini');
        $date_finaly = $request->query('date_finaly');
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');

        $queryy = "SELECT cef.* FROM CAJA_ENTIDAD_FINANCIERA cef WHERE cef.ESTADO = 1 AND
                        UPPER(cef.NOMBRE) LIKE UPPER('%" . $search . "%')";

        $oQuery = DB::select(DB::raw($queryy));
        return $oQuery;

    }

    public static function addUpPersonBankAccount($request)
    {
        $person = $request->query('person');
        $bank = $request->query('bank');
        $tipoctabanco = $request->query('tipoctabanco');
        $account = $request->query('account');
        $activo = $request->query('activo');
        $bindings = [
            'P_ID_PERSONA' => $person,
            'P_ID_BANCO' => $bank,
            'P_ID_TIPOCTABANCO' => $tipoctabanco,
            'P_CUENTA' => $account,
            'P_ACTIVO' => $activo,
        ];

        DB::executeProcedure('IUDP_PERSONA_CUENTA_BANCARIA', $bindings);
        $result = ['mensaje' => 'Insert ok'];
        return $result;

    }

    public static function gratificacionSummary($id_entidad, $id_anho, $id_tramo, $limit, $offset)
    {
        $date_start = null;
        $date_finish = null;
        $paginate = '';

        if ($id_tramo == '1') {
            $date_start = $id_anho . "-01-01";
            $date_finish = $id_anho . "-06-30";
        } else {
            $date_start = $id_anho . "-07-01";
            $date_finish = $id_anho . "-12-31";
        }

        if ($offset != 0) {
            $paginate = " AND RECORDS.line_number BETWEEN " . $limit . " AND " . $offset . " ";
        }

        $query = "SELECT
                LISTA.ID_PERSONA,
                LISTA.NOM_PERSONA,
                LISTA.NUM_DOCUMENTO,
                LISTA.ENTIDAD_BANCARIA,
                LISTA.CTA_BANCARIA,
                ROUND((LISTA.TOTAL - LISTA.TOTAL*(10/100) - (CASE WHEN LISTA.BASICO > 2151 THEN LISTA.TOTAL*(8/100) ELSE 0 END) - LISTA.ADELANTOS),2) AS TOTAL
        FROM (
                SELECT
                        FILAS.*,
                        ROUND((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS + ((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS)*(9/100),2) AS TOTAL
                FROM (
                SELECT
                        RECORDS.*,
                        TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                        (RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_VARIABLE + RECORDS.REMUN_ESPECIE + RECORDS.BONIFICACION_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISONES + RECORDS.VIATICOS_LD) AS SUMATORIA
                FROM (
                        SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                SELECT
                        DISTINCT
                        A.ID_ENTIDAD,
                        A.ID_PERSONA,
                        B.NOM_PERSONA,
                        B.NUM_DOCUMENTO,
                        PCB.NOMBRE_BANCO AS ENTIDAD_BANCARIA,
                        PCB.CUENTA AS CTA_BANCARIA,
                        FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
				A.ID_PERSONA,
				TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
				TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BONIFICACION_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS VIATICOS_LD,
                        (SELECT CAST(NVL(SUM(COS_VALOR),0) AS FLOAT) FROM VW_CONTA_DIARIO
                                WHERE ID_ENTIDAD = A.ID_ENTIDAD
                                AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                                AND ID_MES BETWEEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) AND (EXTRACT(MONTH FROM TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))-1)
                                AND ID_CUENTAAASI = 1135016 AND ID_CTACTE = B.NUM_DOCUMENTO) AS ADELANTOS
                FROM APS_EMPLEADO A
                INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                        A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                LEFT OUTER JOIN (
                        SELECT PB.ID_PERSONA, PB.CUENTA, CEF.NOMBRE AS NOMBRE_BANCO
                                FROM MOISES.PERSONA_CUENTA_BANCARIA PB
                                INNER JOIN (SELECT ID_PERSONA, MAX(FEC_INICIO) FEC_INICIO FROM MOISES.PERSONA_CUENTA_BANCARIA  WHERE (FEC_FIN <= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR FEC_FIN IS NULL)
                                AND ID_TIPOCTABANCO = 3 AND ACTIVO=1 GROUP BY ID_PERSONA
                                ) PB1 ON (PB.ID_PERSONA = PB1.ID_PERSONA AND PB.FEC_INICIO = PB1.FEC_INICIO)
                                INNER JOIN CAJA_ENTIDAD_FINANCIERA CEF ON (PB.ID_BANCO = CEF.ID_BANCO)
                                ) PCB ON PCB.ID_PERSONA = A.ID_PERSONA
                WHERE A.ID_TIPOCONTRATO IN (1,4, 2) AND A.ESTADO IS NOT NULL AND
                        (A.FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR A.FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (A.FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                        A.ID_ENTIDAD = " . $id_entidad . "
                )T) RECORDS
                WHERE RECORDS.MONTHS_TOTAL > 0 " . $paginate . "  ORDER BY RECORDS.line_number) FILAS)LISTA";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function gratificationProvision($id_entidad, $id_anho, $id_tramo, $limit, $offset)
    {
        $date_start = null;
        $date_finish = null;
        $paginate = '';

        if ($id_tramo == '1') {
            $date_start = $id_anho . "-01-01";
            $date_finish = $id_anho . "-06-30";
        } else {
            $date_start = $id_anho . "-07-01";
            $date_finish = $id_anho . "-12-31";
        }

        if ($offset != 0) {
            $paginate = " AND RECORDS.line_number BETWEEN " . $limit . " AND " . $offset . " ";
        }

        $query = "SELECT DATOS.NOM_PERSONA, DATOS.NUM_DOCUMENTO, DATOS.MESES, DATOS.TOTAL,
                        DATOS.M1, DATOS.M2, DATOS.M3, DATOS.M4, DATOS.M5, ROUND(DATOS.M1 + DATOS.M2 + DATOS.M3 + DATOS.M4 + DATOS.M5,2) AS TOTAL_APS,
                        DATOS.ASSINET, ROUND(DATOS.TOTAL - (DATOS.M1 + DATOS.M2 + DATOS.M3 + DATOS.M4 + DATOS.M5), 2) AS PROV_RESTANTE
                FROM (
                SELECT
                        LISTA.NOM_PERSONA,
                        LISTA.NUM_DOCUMENTO,
                        LISTA.MESES,
                        LISTA.TOTAL,
                        FC_PROVISION_GRATI(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO) AS M1,
                        FC_PROVISION_GRATI(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO+1) AS M2,
                        FC_PROVISION_GRATI(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO+2) AS M3,
                        FC_PROVISION_GRATI(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO+3) AS M4,
                        FC_PROVISION_GRATI(LISTA.ID_ENTIDAD, LISTA.ID_PERSONA, LISTA.ANHO, LISTA.MES_UNO+4) AS M5,
                        LISTA.ASSINET
                FROM (
                SELECT
                        FILAS.NOM_PERSONA,
                        FILAS.NUM_DOCUMENTO,
                        FILAS.MESES,
                        FILAS.BASICO,
                        FILAS.ADELANTOS,
                        ROUND(FILAS.ASSINET,2) AS ASSINET,
                        FILAS.ID_ENTIDAD,
                        FILAS.ID_PERSONA,
                        EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) ANHO,
                        EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) MES_UNO,
                        ROUND((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS + ((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS)*(9/100),2) AS TOTAL
                FROM (
                SELECT
                        RECORDS.*,
                        TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                        (RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_VARIABLE + RECORDS.REMUN_ESPECIE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD) AS SUMATORIA
                FROM (
                        SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                SELECT
                        DISTINCT
                        A.ID_ENTIDAD,
                        A.ID_PERSONA,
                        B.NOM_PERSONA,
                        B.NUM_DOCUMENTO,
                        FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
				A.ID_PERSONA,
				TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
				TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                        (CASE WHEN A.FEC_ENTIDAD > TO_DATE('" . $date_start . "', 'YYYY-MM-DD') THEN a.FEC_ENTIDAD ELSE TO_DATE('" . $date_start . "', 'YYYY-MM-DD') END ) AS INGRESO,
                        (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                ELSE A.FEC_TERMINO END ) AS SALIDA,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISIONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS VIATICOS_LD,
                        (SELECT CAST(NVL(SUM(COS_VALOR),0) AS FLOAT) FROM VW_CONTA_DIARIO
                                WHERE ID_ENTIDAD = A.ID_ENTIDAD
                                AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                                AND ID_MES BETWEEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) AND (EXTRACT(MONTH FROM TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))-1)
                                AND ID_CUENTAAASI = 1135016 AND ID_CTACTE = B.NUM_DOCUMENTO) AS ADELANTOS,
                        (SELECT SUM(HABER) FROM VW_CONTA_DIARIO
                                WHERE ID_ENTIDAD = A.ID_ENTIDAD
                                AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                                AND ID_MES BETWEEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) AND (EXTRACT(MONTH FROM TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))-1)
                                AND ID_CUENTAAASI = 2135011 AND ID_CTACTE = B.NUM_DOCUMENTO) AS ASSINET
                FROM APS_EMPLEADO A
                INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                        A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                WHERE A.ID_TIPOCONTRATO IN (1,4, 2) AND A.ESTADO IS NOT NULL AND
                        (A.FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR A.FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (A.FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                        A.ID_ENTIDAD = " . $id_entidad . "
                )T) RECORDS WHERE RECORDS.MONTHS_TOTAL > 0 " . $paginate . "  ORDER BY RECORDS.line_number) FILAS
                ) LISTA
                ) DATOS";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function gratificationCalculation($id_entidad, $id_anho, $id_tramo, $limit, $offset)
    {
        $date_start = null;
        $date_finish = null;
        $paginate = '';

        if ($id_tramo == '1') {
            $date_start = $id_anho . "-01-01";
            $date_finish = $id_anho . "-06-30";
        } else {
            $date_start = $id_anho . "-07-01";
            $date_finish = $id_anho . "-12-31";
        }

        $query = "SELECT
                        LISTA.*,
                        ROUND(LISTA.TOTAL*(10/100), 2) AS DIEZMO,
                        ROUND((CASE WHEN LISTA.BASICO > 2151 THEN LISTA.TOTAL*(8/100) ELSE 0 END),2) AS QUINTA,
                        ROUND((LISTA.TOTAL - LISTA.TOTAL*(10/100) - (CASE WHEN LISTA.BASICO > 2151 THEN LISTA.TOTAL*(8/100) ELSE 0 END) - LISTA.ADELANTOS),2) AS GRAT_NETA
                FROM (
                SELECT
                        FILAS.*,
                        ROUND((FILAS.SUMATORIA/6)*FILAS.MESES,2) AS X_MESES,
                        ROUND((FILAS.SUMATORIA/180)*FILAS.DIAS,2) AS X_DIAS,
                        ROUND(((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS)*(9/100),2) AS B_ESSALUD,
                        ROUND((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS + ((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS)*(9/100),2) AS TOTAL
                FROM (
                SELECT
                        RECORDS.*,
                        TRUNC(RECORDS.MONTHS_TOTAL) AS MESES,
                        (RECORDS.BASICO + RECORDS.PRIMA_INFANTIL + RECORDS.REMUN_VARIABLE + RECORDS.REMUN_ESPECIE + RECORDS.BON_CARGO + RECORDS.BON_ESP_VOLUNTARIA + RECORDS.COMISIONES + RECORDS.VIATICOS_LD) AS SUMATORIA
                FROM (
                        SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                SELECT
                        DISTINCT 
                        B.NOM_PERSONA,
                        B.NUM_DOCUMENTO,
                        FC_OBT_DIAS_MISMO_RUC(A.ID_ENTIDAD,
				A.ID_PERSONA,
				TO_DATE('" . $date_start . "', 'YYYY-MM-DD'),
				TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) DIAS,
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) MONTHS_TOTAL,
                        (CASE WHEN A.FEC_ENTIDAD > TO_DATE('" . $date_start . "', 'YYYY-MM-DD') THEN a.FEC_ENTIDAD ELSE TO_DATE('" . $date_start . "', 'YYYY-MM-DD') END ) AS INGRESO,
                        (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') THEN TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')
                                ELSE A.FEC_TERMINO END ) AS SALIDA,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS COMISIONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))  AS VIATICOS_LD,
                        (SELECT CAST(NVL(SUM(COS_VALOR),0) AS FLOAT) FROM VW_CONTA_DIARIO
                                WHERE ID_ENTIDAD = A.ID_ENTIDAD
                                AND ID_ANHO = EXTRACT(YEAR FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD'))
                                AND ID_MES BETWEEN EXTRACT(MONTH FROM TO_DATE('" . $date_start . "', 'YYYY-MM-DD')) AND (EXTRACT(MONTH FROM TO_DATE('" . $date_finish . "', 'YYYY-MM-DD'))-1)
                                AND ID_CUENTAAASI = 1135016 AND ID_CTACTE = B.NUM_DOCUMENTO) AS ADELANTOS
                FROM APS_EMPLEADO A
                INNER JOIN MOISES.VW_PERSONA_NATURAL_LIGHT B ON
                        A.ID_PERSONA = B.ID_PERSONA AND B.ID_TIPODOCUMENTO NOT IN (97,98)
                WHERE A.ID_TIPOCONTRATO IN (1,4, 2) AND A.ESTADO IS NOT NULL AND
                        (A.FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR A.FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (A.FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND
                        A.ID_ENTIDAD = " . $id_entidad . "
                )T) RECORDS WHERE RECORDS.line_number BETWEEN " . $limit . " AND " . $offset . ") FILAS
                ) LISTA";

        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function gratificationTotal($id_entidad, $id_anho, $id_tramo)
    {
        $date_start = null;
        $date_finish = null;

        if ($id_tramo == '1') {
            $date_start = $id_anho . "-01-01";
            $date_finish = $id_anho . "-06-30";
        } else {
            $date_start = $id_anho . "-07-01";
            $date_finish = $id_anho . "-12-31";
        }

        $query = "SELECT COUNT(DISTINCT A.ID_PERSONA) AS TOTAL
                FROM (SELECT * FROM APS_EMPLEADO WHERE ID_TIPOCONTRATO IN (1,4, 2) AND ESTADO IS NOT NULL AND
                        (FEC_ENTIDAD <= TO_DATE('" . $date_start . "', 'YYYY-MM-DD') OR FEC_ENTIDAD < TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) AND
                        (FEC_TERMINO >= TO_DATE('" . $date_finish . "', 'YYYY-MM-DD') OR FEC_TERMINO IS NULL) AND
                        ID_ENTIDAD = " . $id_entidad . " AND
                        FC_OBT_MESES_MISMO_RUC(ID_ENTIDAD, ID_PERSONA, TO_DATE('" . $date_start . "', 'YYYY-MM-DD'), TO_DATE('" . $date_finish . "', 'YYYY-MM-DD')) > 0) A
                INNER JOIN (SELECT * FROM MOISES.VW_PERSONA_NATURAL_LIGHT WHERE ID_TIPODOCUMENTO NOT IN (97,98)) B ON
                        A.ID_PERSONA = B.ID_PERSONA";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function importPersonBankAccount($request)
    {
        $ret = 'OK';
        //print_r($request->params);
        $data = $request->params;

        foreach ($request->params as $row) {
                $sqlNumDocument ="  SELECT LPAD('".$row['A']."',8,'0') as num_documento FROM DUAL";
                $query = DB::select($sqlNumDocument);
                $sql = "SELECT  ID_PERSONA From MOISES.PERSONA_DOCUMENTO
                        where NUM_DOCUMENTO = '" . $query[0]->num_documento . "'";
            $oQuery = DB::select($sql);
            foreach ($oQuery as $item) {
                $bindings = [
                    'P_ID_PERSONA' => $item->id_persona,
                    'P_ID_BANCO' => $row['B'],
                    'P_ID_TIPOCTABANCO' => $row['D'],
                    'P_CUENTA' => $row['C'],
                    'P_ACTIVO' => 1,
                ];

                DB::executeProcedure('IUDP_PERSONA_CUENTA_BANCARIA', $bindings);
            }

        }
        $ret = ['mensaje' => 'Insert ok'];
        return $ret;

    }

    public static function liquidationCalculation($entity, $person, $fec_termino, $fec_pago, $id_tipo_cese,$id_sistemapension,$fec_vac,$cant_vac_pend,$cant_cal_grat_trunc)
    {
        $query = "SELECT
	FILAS.*,
	ROUND(FILAS.CTS_TOTAL + FILAS.GRAT_TRUNC_TOTAL + FILAS.VAC_TRUNC_TOTAL + FILAS.VAC_PEND_TOTAL - NVL(FILAS.SIST_PENS_TOTAL, 0), 2) AS TOTAL_PAGAR,
	ELISEO.FC_NUMERO_TEXTO(ROUND(FILAS.CTS_TOTAL + FILAS.GRAT_TRUNC_TOTAL + FILAS.VAC_TRUNC_TOTAL + FILAS.VAC_PEND_TOTAL - NVL(FILAS.SIST_PENS_TOTAL, 0), 2)) TEXT_TOTAL_PAGAR
FROM
	(
	SELECT
		LISTA.*, NVL(ROUND((LISTA.VAC_TRUNC_TOTAL + LISTA.VAC_PEND_TOTAL)*(LISTA.SISTEMA_PENSION_TAZA / 100), 2), 0) AS SIST_PENS_TOTAL
	FROM
		(
		SELECT
			DATOS.*, ROUND(CASE WHEN DATOS.CTS>0 THEN ((DATOS.CTS / 12)* DATOS.MESES_CTS_PLN)+(((DATOS.CTS / 12)/ 30)* DATOS.DIAS_CTS_PLN) ELSE 0 END, 2) AS CTS_TOTAL, ROUND(CASE WHEN DATOS.GRAT_VAC>0 THEN ((DATOS.GRAT_VAC / 6)* DATOS.MESES_GRAT_TRUNC_PLR)+(((DATOS.GRAT_VAC / 6)* DATOS.MESES_GRAT_TRUNC_PLR)* 0.09) ELSE 0 END, 2) AS GRAT_TRUNC_TOTAL, ROUND(((DATOS.GRAT_VAC / 6)* DATOS.MESES_GRAT_TRUNC_PLR)* 0.09, 2) AS BONIF_EXTRA, ROUND(CASE WHEN DATOS.VAC>0 THEN ((DATOS.VAC / 12)* DATOS.MESES_VAC_PLN)+(((DATOS.VAC / 12)/ 30)* DATOS.DIAS_VAC_PLN) ELSE 0 END, 2) AS VAC_TRUNC_TOTAL, ROUND(CASE WHEN DATOS.VAC>0 THEN (DATOS.VAC / 30)* DATOS.NETO_DIAS_PEND ELSE 0 END, 2) AS VAC_PEND_TOTAL
		FROM
			(
			SELECT
				ITEMS2.*, /*PERIODO A LIQUIDAR */
				TRUNC((ITEMS2.TOTAL_DIAS_CTS / 30)-(12 * ANHOS_CTS_PLR)) AS MESES_CTS_PLR, TRUNC((ITEMS2.TOTAL_DIAS_GRAT_TRUNC / 30)-(12 * ANHOS_GRAT_TRUNC_PLR)) AS MESES_GRAT_TRUNC_PLR, TRUNC((ITEMS2.TOTAL_DIAS_VAC / 30)-(12 * ANHOS_VAC_PLR)) AS MESES_VAC_PLR, TRUNC((ITEMS2.TOTALS_DIAS_PEND / 30)-(12 * ANHOS_PEND_PLR)) AS MESES_PEND_PLR, /*-- */
				/*PERIODO DE LIQUIDACIÓN */
				TRUNC((ITEMS2.NETO_DIAS_CTS / 30)-(12 * ANHOS_CTS_PLN)) AS MESES_CTS_PLN, TRUNC((ITEMS2.NETO_DIAS_GRAT_TRUNC / 30)-(12 * ANHOS_GRAT_TRUNC_PLN)) AS MESES_GRAT_TRUNC_PLN, TRUNC((ITEMS2.NETO_DIAS_VAC / 30)-(12 * ANHOS_VAC_PLN)) AS MESES_VAC_PLN, TRUNC((ITEMS2.NETO_DIAS_PEND / 30)-(12 * ANHOS_PEND_PLN)) AS MESES_PEND_PLN, /*--*/
				/*PERIODO A LIQUIDAR */
				ROUND(((ITEMS2.TOTAL_DIAS_CTS / 30)-TRUNC(ITEMS2.TOTAL_DIAS_CTS / 30))* 30, 0) AS DIAS_CTS_PLR, ROUND(((ITEMS2.TOTAL_DIAS_GRAT_TRUNC / 30)-TRUNC(ITEMS2.TOTAL_DIAS_GRAT_TRUNC / 30))* 30, 0) AS DIAS_GRAT_TRUNC_PLR, ROUND(((ITEMS2.TOTAL_DIAS_VAC / 30)-TRUNC(ITEMS2.TOTAL_DIAS_VAC / 30))* 30, 0) AS DIAS_VAC_PLR, ROUND(((ITEMS2.TOTALS_DIAS_PEND / 30)-TRUNC(ITEMS2.TOTALS_DIAS_PEND / 30))* 30, 0) AS DIAS_PEND_PLR, /*-- */
				/*PERIODO DE LIQUIDACIÓN */
				ROUND(((ITEMS2.NETO_DIAS_CTS / 30)-TRUNC(ITEMS2.NETO_DIAS_CTS / 30))* 30, 0) AS DIAS_CTS_PLN, ROUND(((ITEMS2.NETO_DIAS_GRAT_TRUNC / 30)-TRUNC(ITEMS2.NETO_DIAS_GRAT_TRUNC / 30))* 30, 0) AS DIAS_GRAT_TRUNC_PLN, ROUND(((ITEMS2.NETO_DIAS_VAC / 30)-TRUNC(ITEMS2.NETO_DIAS_VAC / 30))* 30, 0) AS DIAS_VAC_PLN, ROUND(((ITEMS2.NETO_DIAS_PEND / 30)-TRUNC(ITEMS2.NETO_DIAS_PEND / 30))* 30, 0) AS DIAS_PEND_PLN /*	-- */
			FROM
				(
				SELECT
					ITEMS1.*, /*PERIODO A LIQUIDAR */
				CASE
						WHEN ITEMS1.TOTAL_DIAS_CTS>360 THEN TRUNC(ITEMS1.TOTAL_DIAS_CTS / 360)
						ELSE 0
					END AS ANHOS_CTS_PLR,
				CASE
						WHEN ITEMS1.TOTAL_DIAS_GRAT_TRUNC>360 THEN TRUNC(ITEMS1.TOTAL_DIAS_GRAT_TRUNC / 360)
						ELSE 0
					END AS ANHOS_GRAT_TRUNC_PLR,
				CASE
						WHEN ITEMS1.TOTAL_DIAS_VAC>360 THEN TRUNC(ITEMS1.TOTAL_DIAS_VAC / 360)
						ELSE 0
					END AS ANHOS_VAC_PLR,
				CASE
						WHEN ITEMS1.TOTALS_DIAS_PEND>360 THEN TRUNC(ITEMS1.TOTALS_DIAS_PEND / 360)
						ELSE 0
					END AS ANHOS_PEND_PLR, /*-- */
					/*PERIODO DE LIQUIDACIÓN */
				CASE
						WHEN ITEMS1.NETO_DIAS_CTS>360 THEN TRUNC(ITEMS1.NETO_DIAS_CTS / 360)
						ELSE 0
					END AS ANHOS_CTS_PLN,
				CASE
						WHEN ITEMS1.NETO_DIAS_GRAT_TRUNC>360 THEN TRUNC(ITEMS1.NETO_DIAS_GRAT_TRUNC / 360)
						ELSE 0
					END AS ANHOS_GRAT_TRUNC_PLN,
				CASE
						WHEN ITEMS1.NETO_DIAS_VAC>360 THEN TRUNC(ITEMS1.NETO_DIAS_VAC / 360)
						ELSE 0
					END AS ANHOS_VAC_PLN,
				CASE
						WHEN ITEMS1.NETO_DIAS_PEND>360 THEN TRUNC(ITEMS1.NETO_DIAS_PEND / 360)
						ELSE 0
					END AS ANHOS_PEND_PLN /**/
				FROM
					(
					SELECT
						ITEMS.*,
					CASE
							WHEN ITEMS.TOTAL_DIAS_CTS>0
							AND ITEMS.TOTAL_DIAS_CTS>ITEMS.FALTAS_CTS THEN ITEMS.TOTAL_DIAS_CTS-ITEMS.FALTAS_CTS
							ELSE 0
						END AS NETO_DIAS_CTS,
					CASE
							WHEN ITEMS.TOTAL_DIAS_GRAT_TRUNC>0
							AND ITEMS.TOTAL_DIAS_GRAT_TRUNC>ITEMS.FALTAS_GRAT_TRUNC THEN ITEMS.TOTAL_DIAS_GRAT_TRUNC-ITEMS.FALTAS_GRAT_TRUNC-ITEMS.RECAL_DIAS_GRAT_TRUNC
							ELSE 0
						END AS NETO_DIAS_GRAT_TRUNC,
					CASE
							WHEN ITEMS.TOTAL_DIAS_VAC>0
							AND ITEMS.TOTAL_DIAS_VAC>ITEMS.FALTAS_VAC THEN ITEMS.TOTAL_DIAS_VAC-ITEMS.FALTAS_VAC
							ELSE 0
						END AS NETO_DIAS_VAC,
					CASE
							WHEN ITEMS.TOTALS_DIAS_PEND>0
							AND ITEMS.TOTALS_DIAS_PEND>ITEMS.VAC_ACUMUL THEN ITEMS.TOTALS_DIAS_PEND-ITEMS.VAC_ACUMUL
							ELSE 0
						END AS NETO_DIAS_PEND
					FROM
						(
						SELECT
							RECORDS.*, FC_NAMESISTEMAPENSION($id_sistemapension) SISTEMA_PENSION, FC_OBTENER_SP_TASA($id_sistemapension,
							EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')),
							EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))) SISTEMA_PENSION_TAZA, 
							NVL(RECORDS.BASICO_CTS,0) + NVL(RECORDS.PRIMA_INFANTIL_CTS,0) + NVL(RECORDS.REMUN_VARIABLE_CTS,0) + NVL(RECORDS.REMUN_ESPECIE_CTS,0) + NVL(RECORDS.BONIFICACION_CARGO,0) + 
							NVL(RECORDS.BON_ESP_VOLUNTARIA,0) + NVL(RECORDS.COMISONES,0) + NVL(RECORDS.VIATICOS_LD_CTS,0) + NVL(RECORDS.ULT_GRATI_SEXTO,0) AS CTS, 
							NVL(RECORDS.BASICO_GRAT,0) + NVL(RECORDS.PRIMA_INFANTIL_GRAT,0) + NVL(RECORDS.REMUN_VARIABLE_GRAT,0) + NVL(RECORDS.REMUN_ESPECIE_GRAT,0) + NVL(RECORDS.BONIFICACION_CARGO,0) + 
							NVL(RECORDS.BON_ESP_VOLUNTARIA,0) + NVL(RECORDS.COMISONES,0) + NVL(RECORDS.VIATICOS_LD_GRAT,0) AS GRAT_VAC, 
							NVL(RECORDS.BASICO_VAC,0) + NVL(RECORDS.PRIMA_INFANTIL_VAC,0) + NVL(RECORDS.REMUN_VARIABLE_VAC,0) + NVL(RECORDS.REMUN_ESPECIE_VAC,0) + NVL(RECORDS.BONIFICACION_CARGO,0) + 
							NVL(RECORDS.BON_ESP_VOLUNTARIA,0) + NVL(RECORDS.COMISONES,0) + NVL(RECORDS.VIATICOS_LD_VAC,0) AS VAC, 
							ELISEO.DAYS360(RECORDS.CTS_FECHA,
							RECORDS.FECHA_CESE + 1) AS TOTAL_DIAS_CTS, 0 AS FALTAS_CTS, ELISEO.DAYS360(RECORDS.GRAT_VAC_FECHA,
							RECORDS.FECHA_CESE + 1) AS TOTAL_DIAS_GRAT_TRUNC, 0 AS FALTAS_GRAT_TRUNC,
                                                        $cant_cal_grat_trunc AS RECAL_DIAS_GRAT_TRUNC,
							ELISEO.DAYS360(RECORDS.VAC_FECHA,RECORDS.FECHA_CESE+1) AS TOTAL_DIAS_VAC,
							0 AS FALTAS_VAC,
							$cant_vac_pend AS TOTALS_DIAS_PEND, 0 AS VAC_ACUMUL
							FROM (SELECT 
								TB.*,
								FC_SUELDO_BASICO(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.CTS_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS BASICO_CTS,
								FC_SUELDO_BASICO(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.GRAT_VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS BASICO_GRAT,
								FC_SUELDO_BASICO(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS BASICO_VAC,
								FC_PRIMA_INFANTIL(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.CTS_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL_CTS,
								FC_PRIMA_INFANTIL(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.GRAT_VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL_GRAT, 
								FC_PRIMA_INFANTIL(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS PRIMA_INFANTIL_VAC, 
								(SELECT ROUND(SUM(COS_VALOR)/6,2) FROM  APS_PLANILLA_DETALLE 
								WHERE (TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0))  BETWEEN 
										TO_NUMBER(EXTRACT(YEAR FROM TB.CTS_FECHA)||LPAD(EXTRACT(MONTH FROM TB.CTS_FECHA),2,0))
										AND TO_NUMBER(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))||LPAD(EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')),2,0)))
									AND ID_ENTIDAD = TB.ID_ENTIDAD AND ID_CONCEPTOAPS = 1086 AND ID_PERSONA = TB.ID_PERSONA)AS REMUN_VARIABLE_CTS,
								(SELECT ROUND(SUM(COS_VALOR)/6,2) FROM  APS_PLANILLA_DETALLE 
								WHERE (TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0))  BETWEEN 
										TO_NUMBER(EXTRACT(YEAR FROM TB.GRAT_VAC_FECHA)||LPAD(EXTRACT(MONTH FROM TB.GRAT_VAC_FECHA),2,0))
										AND TO_NUMBER(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))||LPAD(EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')),2,0)))
									AND ID_ENTIDAD = TB.ID_ENTIDAD AND ID_CONCEPTOAPS = 1086 AND ID_PERSONA = TB.ID_PERSONA)AS REMUN_VARIABLE_GRAT,
								(SELECT ROUND(SUM(COS_VALOR)/6,2) FROM  APS_PLANILLA_DETALLE 
								WHERE (TO_NUMBER(ID_ANHO||LPAD(ID_MES,2,0))  BETWEEN 
										TO_NUMBER(EXTRACT(YEAR FROM TB.VAC_FECHA)||LPAD(EXTRACT(MONTH FROM TB.VAC_FECHA),2,0))
										AND TO_NUMBER(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))||LPAD(EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')),2,0)))
									AND ID_ENTIDAD = TB.ID_ENTIDAD AND ID_CONCEPTOAPS = 1086 AND ID_PERSONA = TB.ID_PERSONA)AS REMUN_VARIABLE_VAC,
								FC_RESUMEN_ESPECIE(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.CTS_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE_CTS,
								FC_RESUMEN_ESPECIE(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.GRAT_VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE_GRAT,
								FC_RESUMEN_ESPECIE(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS REMUN_ESPECIE_VAC,
								FC_BONIFICACION_CARGO(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.FEC_INICIO,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS BONIFICACION_CARGO, 
								FC_BON_ESP_VOLUNTARIA(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.FEC_INICIO,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS BON_ESP_VOLUNTARIA, 
								FC_COMISIONES(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.FEC_INICIO,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS COMISONES, 
								FC_VIATICOS_LD(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.CTS_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS VIATICOS_LD_CTS,
								FC_VIATICOS_LD(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.GRAT_VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS VIATICOS_LD_GRAT,
								FC_VIATICOS_LD(TB.ID_ENTIDAD,TB.ID_PERSONA,TB.VAC_FECHA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS VIATICOS_LD_VAC,
								FC_ULTIMA_GRATI(TB.ID_ENTIDAD,TB.ID_PERSONA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) AS ULT_GRATI, 
								FC_ULTIMA_GRATI(TB.ID_ENTIDAD,TB.ID_PERSONA,TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))/ 6 AS ULT_GRATI_SEXTO
						FROM
							(
							SELECT
								A.ID_ENTIDAD,
								A.FEC_INICIO,
								A.ID_PERSONA, A.NOM_PERSONA, A.NUM_DOCUMENTO, A.FEC_ENTIDAD, A.FEC_TERMINO, B.NOMBRE AS CATEGORIA, C.ID_TIPOCESE, C.NOMBRE AS TIPO_CESE, 
								/*( SELECT NVL(SUM(COS_VALOR), 0) FROM APS_PLANILLA_DETALLE WHERE ID_PERSONA = A.ID_PERSONA AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONTRATO = A.ID_CONTRATO AND ID_CONCEPTOAPS IN (1104)) AS CTS_TOTAL, ( SELECT NVL(SUM(COS_VALOR), 0) FROM APS_PLANILLA_DETALLE WHERE ID_PERSONA = A.ID_PERSONA AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONTRATO = A.ID_CONTRATO AND ID_CONCEPTOAPS IN (1405)) AS GRAT_TRUNC_TOTAL,( SELECT NVL(SUM(COS_VALOR), 0) FROM APS_PLANILLA_DETALLE WHERE ID_PERSONA = A.ID_PERSONA AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONTRATO = A.ID_CONTRATO AND ID_CONCEPTOAPS IN (1404)) AS VAC_PEND_TOTAL, ( SELECT NVL(SUM(COS_VALOR), 0) FROM APS_PLANILLA_DETALLE WHERE ID_PERSONA = A.ID_PERSONA AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_CONTRATO = A.ID_CONTRATO AND ID_CONCEPTOAPS IN (1402)) AS VAC_TRUNC_TOTAL,*/
								(
								SELECT
									TRUNC(SUM(NUM_DIASVAC)/ 30)
								FROM
									APS_PLANILLA
								WHERE
									ID_PERSONA = A.ID_PERSONA
									AND ID_ENTIDAD = A.ID_ENTIDAD
									AND TO_NUMBER(ID_ANHO || LPAD(ID_MES, 2, 0)) BETWEEN TO_NUMBER(TO_CHAR(A.FEC_INICIO, 'YYYY')|| LPAD(TO_CHAR(A.FEC_INICIO, 'MM'), 2, 0)) AND TO_NUMBER(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| LPAD(EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')), 2, 0)) ) AS VAC_GAN_NOGOZ_MESES, 
									(SELECT
										ROUND(TRUNC(SUM(NUM_DIASVAC)/ 30)-(SUM(NUM_DIASVAC)/ 30), 0)
									FROM
										APS_PLANILLA
									WHERE
										ID_PERSONA = A.ID_PERSONA
										AND ID_ENTIDAD = A.ID_ENTIDAD
										AND TO_NUMBER(ID_ANHO || LPAD(ID_MES, 2, 0)) BETWEEN TO_NUMBER(TO_CHAR(A.FEC_INICIO, 'YYYY')|| LPAD(TO_CHAR(A.FEC_INICIO, 'MM'), 2, 0)) AND TO_NUMBER(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| LPAD(EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')), 2, 0))) AS VAC_GAN_NOGOZ_DIAS, 
									FC_OBTENER_CARGO(A.ID_CONTRATO,A.ID_PERSONA,A.ID_ENTIDAD) CARGO, 
								FC_OBTENER_SP(A.ID_CONTRATO,A.ID_PERSONA,A.ID_ENTIDAD) ID_SISTEMAPENSION, 
								 
								TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD') AS FECHA_CESE,
								(
								CASE
									WHEN EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) > 4 THEN TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| '05-01', 'YYYY-MM-DD')
									ELSE TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))-1 || '11-01', 'YYYY-MM-DD')
								END) AS CTS_FECHA,
								(
								CASE
									WHEN EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) <= 6 THEN TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| '01-01', 'YYYY-MM-DD')
									ELSE TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| '07-01', 'YYYY-MM-DD')
								END) AS GRAT_VAC_FECHA,
								/*( CASE WHEN EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) = EXTRACT(YEAR FROM A.FEC_INICIO) THEN A.FEC_INICIO ELSE TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))|| '01-01', 'YYYY-MM-DD') END) */
								TO_DATE('".$fec_vac."', 'YYYY-MM-DD') AS VAC_FECHA
								FROM
								(
								SELECT
									*
								FROM
									VW_APS_EMPLEADO
								WHERE
									ID_ENTIDAD = $entity
									AND ID_PERSONA = $person
									AND ID_TIPODOCUMENTO NOT IN (97, 98)
									AND (TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD') BETWEEN FEC_INICIO AND NVL(FEC_TERMINO, TO_DATE(TO_CHAR(SYSDATE, 'YYYY-MM-DD'), 'YYYY-MM-DD')))) A
								LEFT JOIN APS_CATEGORIA_OCUPACIONAL B ON
								B.ID_CATEGORIAOCUPACIONAL = A.ID_CATEGORIAOCUPACIONAL
								LEFT JOIN TIPO_CESE C ON
								(C.ID_TIPOCESE = A.ID_TIPOCESE
								OR C.ID_TIPOCESE = $id_tipo_cese)
								ORDER BY
								3 ) TB )RECORDS) ITEMS )ITEMS1)ITEMS2 ) DATOS)LISTA ) FILAS";
        #print_r($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function dataPersonLiquidation($entity, $person, $fec_termino)
    {

        $fec_fin="(CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')
        WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD') THEN TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')
        ELSE A.FEC_TERMINO END ) ";
        $sql = "SELECT
                        A.ID_PERSONA,
                        A.NOM_PERSONA,
                        A.NUM_DOCUMENTO,
                        A.FEC_ENTIDAD,
                        A.FEC_TERMINO,
                        B.NOMBRE AS CATEGORIA,
                        C.ID_TIPOCESE,
                        C.NOMBRE AS TIPO_CESE,
                        FC_OBTENER_CARGO(A.ID_CONTRATO, A.ID_PERSONA, A.ID_ENTIDAD) CARGO,
                        FC_OBTENER_SP(A.ID_CONTRATO, A.ID_PERSONA, A.ID_ENTIDAD) ID_SISTEMAPENSION,
                        (CASE WHEN A.FEC_ENTIDAD > A.FEC_INICIO THEN a.FEC_ENTIDAD ELSE A.FEC_INICIO END ) AS INGRESO,
                        (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')
                                WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD') THEN TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')
                                ELSE A.FEC_TERMINO END ) AS SALIDA,
                        $fec_fin AS FECHA_CESE,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS BONIFICACION_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS COMISONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, A.FEC_INICIO, $fec_fin)  AS VIATICOS_LD,
                        FC_ULTIMA_GRATI(A.ID_ENTIDAD, A.ID_PERSONA, $fec_fin) AS ULT_GRATI,
                        FC_ULTIMA_GRATI(A.ID_ENTIDAD, A.ID_PERSONA, $fec_fin)/6  AS ULT_GRATI_SEXTO,
                        (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) > 4 THEN
                                TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))||'05-01','YYYY-MM-DD')
                        ELSE
                                TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))-1||'11-01','YYYY-MM-DD')
                        END) CTS_FECHA,
                        (CASE WHEN EXTRACT(MONTH FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD')) <= 6 THEN
                                TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))||'01-01','YYYY-MM-DD')
                        ELSE
                                TO_DATE(EXTRACT(YEAR FROM TO_DATE('" . $fec_termino . "', 'YYYY-MM-DD'))||'07-01','YYYY-MM-DD')
                        END) GRAT_VAC_FECHA

                FROM (SELECT * FROM VW_APS_EMPLEADO WHERE ID_ENTIDAD = " . $entity . " 
                        AND ID_PERSONA = " . $person . " 
                        AND ID_TIPODOCUMENTO NOT IN (97,98)
                        ORDER BY FEC_TERMINO DESC) A
                LEFT JOIN APS_CATEGORIA_OCUPACIONAL B ON
                        B.ID_CATEGORIAOCUPACIONAL = A.ID_CATEGORIAOCUPACIONAL
                LEFT JOIN TIPO_CESE C ON
                        C.ID_TIPOCESE = A.ID_TIPOCESE
                WHERE rownum=1 ";

        $oQuery = DB::select(DB::raw($sql));
        return $oQuery;
    }

    public static function listCessationType()
    {
        $sql = "Select * from TIPO_CESE";
        $oQuery = DB::select(DB::raw($sql));
        return $oQuery;
    }

    public static function listPensionSystem()
    {
        $sql = "Select * from APS_SISTEMA_PENSION";
        $oQuery = DB::select(DB::raw($sql));
        return $oQuery;
    }

    public static function importDirectory($request)
    {
        $ret = 'OK';
        $data = $request->params;

        foreach ($request->params as $row) {

            $sql = "SELECT  ID_PERSONA From MOISES.PERSONA_DOCUMENTO
                        where NUM_DOCUMENTO = '" . $row['D'] . "'";
            $oQuery = DB::select($sql);
            foreach ($oQuery as $item) {
                    
                $bindings = [
                    'P_ID_PERSONA' => $item->id_persona,
                    'P_ID_BANCO' => $row['B'],
                    'P_ID_TIPOCTABANCO' => $row['D'],
                    'P_CUENTA' => $row['C'],
                    'P_ACTIVO' => 1,
                ];

                DB::executeProcedure('IUDP_PERSONA_CUENTA_BANCARIA', $bindings);
            }

        }
        $ret = ['mensaje' => 'Insert ok'];
        return $ret;

    }

    public static function getNaturalPersonforId($id_persona)
    {
        $sql = "SELECT * FROM MOISES.VW_PERSONA_NATURAL_LIGHT 
        WHERE ID_PERSONA = ".$id_persona." AND ID_TIPODOCUMENTO IN (1,4,7)";
        $oQuery = DB::select($sql);
        return $oQuery;
    }

}

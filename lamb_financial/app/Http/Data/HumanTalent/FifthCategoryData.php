<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

use Illuminate\Support\Facades\Input;
use Carbon\Carbon;

class FifthCategoryData extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function fifthCategoryTotal($id_entidad, $id_anho)
    {
        $query = "SELECT
                        COUNT(*) AS TOTAL
                FROM APS_EMPLEADO A
                INNER JOIN (SELECT DISTINCT ID_PERSONA FROM  MOISES.VW_PERSONA_NATURAL_LIGHT WHERE ID_TIPODOCUMENTO IN (1,4,7)) B ON	
                        A.ID_PERSONA = B.ID_PERSONA
                WHERE A.ID_TIPOCONTRATO IN (1,4,2) AND A.ESTADO IS NOT NULL AND
                        (A.FEC_INICIO <= TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD') OR A.FEC_INICIO < TO_DATE('".$id_anho."-06-30', 'YYYY-MM-DD')) AND 
                        (A.FEC_TERMINO >= TO_DATE('".$id_anho."-06-30', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND  
                        A.ID_ENTIDAD = ".$id_entidad;
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function fifthCategoryProjection($uit, $id_entidad, $id_anho, $limit, $offset)
    {

        $query = "SELECT LIST_DATA.*,
                        ROUND(LIST_DATA.HASTA_5UIT + LIST_DATA.HASTA_20UIT + LIST_DATA.HASTA_35UIT,2) AS TOTAL_IR
                FROM (
                SELECT DATOS.*,
                        (CASE WHEN DATOS.RENTA_NETA >= 147000 THEN (15*$uit*0.17) ELSE (DATOS.RENTA_NETA-((DATOS.HASTA_5UIT*100)/8) - ((DATOS.HASTA_20UIT*100)/14))*0.17 END) AS HASTA_35UIT
                FROM (
                SELECT RESULTS.*,
                        (CASE WHEN RESULTS.RENTA_NETA >= 84000 THEN (15*$uit*0.14) ELSE (RESULTS.RENTA_NETA-((RESULTS.HASTA_5UIT*100)/8))*0.14 END) AS HASTA_20UIT
                FROM (
                SELECT ITEMS.*,
                        (CASE WHEN ITEMS.RENTA_NETA >= 21000 THEN 21000 ELSE ITEMS.RENTA_NETA END)*0.08 AS HASTA_5UIT
                FROM (
                SELECT LISTA.*,
                        (CASE WHEN LISTA.RENTA_BRUTA_ANUAL - LISTA.DEDUCCION_7UIT>=0 THEN LISTA.RENTA_BRUTA_ANUAL - LISTA.DEDUCCION_7UIT ELSE 0 END) RENTA_NETA
                FROM (
                SELECT 
                        FILAS.ID_PERSONA, 
                        FILAS.NOM_PERSONA, 
                        FILAS.NUM_DOCUMENTO, 
                        FILAS.INGRESO,
                        FILAS.SALIDA,
                        FILAS.TOTAL_MESES,
                        FILAS.BASICO_ANUAL,
                        FILAS.PRIMAINF_ANUAL,
                        FILAS.REMUNESP_ANUAL,
                        FILAS.REMUNVAR_ANUAL,
                        ROUND(((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS + ((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS)*(9/100))*2,2) AS GRAT_ANUAL,
                        FILAS.BEXTRAORD_ANUAL,
                        FILAS.ASIGED_ANUAL,
                        FILAS.BASIGED_ANUAL,
                        FILAS.COMISIONES_ANUAL,
                        FILAS.BDESTAQUE_ANUAL,
                        FILAS.BONPRESTALIM_ANUAL,
                        FILAS.ASIGUPEU_ANUAL,
                        FILAS.VLD_ANUAL,
                        ROUND(FILAS.BASICO_ANUAL + FILAS.PRIMAINF_ANUAL + FILAS.REMUNESP_ANUAL + FILAS.REMUNVAR_ANUAL + ((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS + ((FILAS.SUMATORIA/6)*FILAS.MESES + (FILAS.SUMATORIA/180)*FILAS.DIAS)*(9/100)) +
                        FILAS.BEXTRAORD_ANUAL + FILAS.ASIGED_ANUAL + FILAS.BASIGED_ANUAL + FILAS.COMISIONES_ANUAL + FILAS.BDESTAQUE_ANUAL + FILAS.BONPRESTALIM_ANUAL + FILAS.ASIGUPEU_ANUAL + FILAS.VLD_ANUAL,2) AS RENTA_BRUTA_ANUAL,
                        $uit*7 AS DEDUCCION_7UIT
                FROM (
                SELECT 
                        LINES.ID_PERSONA, 
                        LINES.NOM_PERSONA, 
                        LINES.NUM_DOCUMENTO, 
                        LINES.INGRESO,
                        LINES.SALIDA,
                        LINES.TOTAL_MESES,
                        LINES.BASICO  * LINES.TOTAL_MESES  AS BASICO_ANUAL,
                        LINES.PRIMA_INFANTIL * LINES.TOTAL_MESES AS PRIMAINF_ANUAL,
                        LINES.REMUN_ESPECIE * LINES.TOTAL_MESES AS REMUNESP_ANUAL,
                        LINES.REMUNVAR_ANUAL,
                        LINES.BEXTRAORD_ANUAL,
                        LINES.ASIGED_ANUAL,
                        LINES.BASIGED_ANUAL,
                        LINES.COMISIONES_ANUAL,
                        LINES.BDESTAQUE_ANUAL,
                        LINES.BONPRESTALIM_ANUAL,
                        LINES.ASIGUPEU_ANUAL,
                        LINES.VLD_ANUAL,
                        TRUNC(LINES.MONTHS_TOTAL) AS MESES,
                        CEIL((LINES.MONTHS_TOTAL - TRUNC(LINES.MONTHS_TOTAL))*30) AS DIAS,
                        ROUND((LINES.BASICO + LINES.PRIMA_INFANTIL + LINES.REMUN_VARIABLE + LINES.REMUN_ESPECIE + LINES.BONIFICACION_CARGO + LINES.BON_ESP_VOLUNTARIA + LINES.COMISONES + LINES.VIATICOS_LD),2) AS SUMATORIA
                FROM (
                SELECT RECORDS.*,
                        TRUNC(MONTHS_BETWEEN(RECORDS.SALIDA + 1, RECORDS.INGRESO)) AS TOTAL_MESES
                FROM (
                    SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                SELECT
                        DISTINCT
                        A.ID_PERSONA,
                        B.NOM_PERSONA,
                        B.NUM_DOCUMENTO,
                        FC_OBT_MESES_MISMO_RUC(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) MONTHS_TOTAL,
                        (CASE WHEN A.FEC_ENTIDAD > TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD') THEN a.FEC_ENTIDAD ELSE TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD') END ) AS INGRESO,
                        (CASE WHEN A.FEC_TERMINO IS NULL THEN TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD') 
                                                WHEN A.FEC_TERMINO IS NOT NULL AND A.FEC_TERMINO > TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD') THEN TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')
                                                ELSE A.FEC_TERMINO END ) AS SALIDA,
                        FC_SUELDO_BASICO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AS BASICO,
                        FC_PRIMA_INFANTIL(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AS PRIMA_INFANTIL,
                        FC_REMUN_VARIABLE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AS REMUN_VARIABLE,
                        FC_RESUMEN_ESPECIE(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AS REMUN_ESPECIE,
                        FC_BONIFICACION_CARGO(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AS BONIFICACION_CARGO,
                        FC_BON_ESP_VOLUNTARIA(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AS BON_ESP_VOLUNTARIA,
                        FC_COMISIONES(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD'))  AS COMISONES,
                        FC_VIATICOS_LD(A.ID_ENTIDAD, A.ID_PERSONA, TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD'), TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD'))  AS VIATICOS_LD,
                        FC_BDV_PROYECTADO(A.ID_ENTIDAD, A.ID_PERSONA, ".$id_anho.") AS REMUNVAR_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1215 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS BDESTAQUE_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 3100 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS BEXTRAORD_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1118
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS ASIGED_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1119 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS BASIGED_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1147 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS BONPRESTALIM_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1151 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS COMISIONES_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1138 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS ASIGUPEU_ANUAL, 
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1145 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS VLD_ANUAL
                FROM APS_EMPLEADO A
                INNER JOIN (SELECT * FROM  MOISES.VW_PERSONA_NATURAL_LIGHT WHERE ID_TIPODOCUMENTO IN (1,4,7))B ON	
                        A.ID_PERSONA = B.ID_PERSONA
                WHERE A.ID_TIPOCONTRATO IN (1,4,2) AND A.ESTADO IS NOT NULL AND
                        (A.FEC_INICIO <= TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD') OR A.FEC_INICIO < TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AND 
                        (A.FEC_TERMINO >= TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND  
                        A.ID_ENTIDAD = ".$id_entidad."
                )T) RECORDS WHERE RECORDS.line_number BETWEEN ".$limit." AND ".$offset."
                ) LINES
                ) FILAS
                ) LISTA
                ) ITEMS
                ) RESULTS
                ) DATOS
                ) LIST_DATA";  
                //print_r($query);         
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function fifthCategoryAdjustment($uit, $id_entidad, $id_anho, $limit, $offset, $id_persona)
    {   
        $condition = "";
        $paginate = "";
        if($id_persona !== ''){
                $condition = "AND A.ID_PERSONA =".$id_persona;
        }else{
               
                $paginate = " WHERE RECORDS.line_number BETWEEN ".$limit." AND ".$offset." ";
        }

        
        $query = "SELECT FILAS.*,
                        (CASE WHEN FILAS.DIF_AJUST_DIC > 0 THEN FILAS.DIF_AJUST_DIC ELSE 0 END) QUINTA_1502,
                        (CASE WHEN FILAS.DIF_AJUST_DIC < 0 THEN -1*(FILAS.DIF_AJUST_DIC) ELSE 0 END) QUINTA_1350
                FROM (
                SELECT LISTA.*,
                        HASTA_5UIT + HASTA_20UIT + HASTA_35UIT AS RENTA_ANUAL_TOTAL,
                        (HASTA_5UIT + HASTA_20UIT + HASTA_35UIT) - LISTA.RT_RETENCIONES_ANUAL AS DIF_AJUST_DIC
                FROM (
                SELECT DATOS.*,
                        (CASE WHEN DATOS.DESCTO_LIMITE >= 147000 THEN (15*$uit*0.17) ELSE (DATOS.DESCTO_LIMITE-((DATOS.HASTA_5UIT*100)/8) - ((DATOS.HASTA_20UIT*100)/14))*0.17 END) AS HASTA_35UIT
                FROM (
                SELECT RESULTS.*,
                        (CASE WHEN RESULTS.DESCTO_LIMITE >= 84000 THEN (15*$uit*0.14) ELSE (RESULTS.DESCTO_LIMITE-((RESULTS.HASTA_5UIT*100)/8))*0.14 END) AS HASTA_20UIT
                FROM (
                SELECT ITEMS.*,
                        (CASE WHEN ITEMS.DESCTO_LIMITE >= 21000 THEN 21000 ELSE ITEMS.DESCTO_LIMITE END)*0.08 AS HASTA_5UIT
                FROM (
                SELECT LINES.*,
                        (CASE WHEN LINES.TOTAL - LINES.DEDUCCION_7UIT>=0 THEN LINES.TOTAL - LINES.DEDUCCION_7UIT ELSE 0 END) DESCTO_LIMITE
                FROM (
                SELECT RECORDS.*,
                $uit*7 AS DEDUCCION_7UIT,
                        ROUND(RECORDS.A_BASICO_ANUAL + RECORDS.B_GREATIF_ANUAL + RECORDS.C_GREATIFEXTRA_ANUAL + RECORDS.D_BONIFASIGN_ANUAL + RECORDS.E_OTRCONCEPREMUN_ANUAL + RECORDS.F_ASIGNFAM_ANUAL + 
                        RECORDS.G_HORASEXTRAS_ANUAL + RECORDS.H_REMEMPRESASANT_ANUAL + RECORDS.I_VACACIONES_ANUAL + RECORDS.J_PRESALIM_ANUAL,2) AS TOTAL
                FROM (
                    SELECT row_number() over (ORDER BY T.NOM_PERSONA ASC) line_number,T.* FROM(
                SELECT
                        DISTINCT
                        A.ID_ENTIDAD,
                        A.ID_PERSONA,
                        B.NOM_PERSONA,
                        B.ID_TIPODOCUMENTO,
                        B.NUM_DOCUMENTO,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1000,1022,1079,1097,1212,1220,1224,1228) 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS A_BASICO_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1405,1406,3000,3079,3080,3082,3090,3095,3097,3100,3102,3121,3122,3126,3145,3151,3156,3157,3170,3171,3200,3212,3216,3220,3222,3224,3228,3246,3086)
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS B_GREATIF_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1080 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS C_GREATIFEXTRA_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1076,1082,7080,1086,1090,1092,1094,1095,1096,1110,1112,1113,1114,1116,1117,1118,1119,1120,1124,1126,1128,1146,1153,1210,1214,1215,1216,1218,1246,1226,1134,1138) 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS D_BONIFASIGN_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1145,1151,1130,7030,1222)
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS E_OTRCONCEPREMUN_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1121,1122) 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS F_ASIGNFAM_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1156,1157,1170,1171) 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS G_HORASEXTRAS_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 9011 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS H_REMEMPRESASANT_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS IN (1402,1401,2000,2079,2080,2090,2095,2097,2121,2122,2126,2145,2151,2156,2157,2170,2171,2212,2216,2220,2222,2224,2228,2246) 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0)/2 AS I_VACACIONES_ANUAL, 
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_CONCEPTOAPS = 1147 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS J_PRESALIM_ANUAL,
                        NVL((SELECT SUM(COS_VALOR) 
                                FROM  APS_PLANILLA_DETALLE 
                                WHERE ID_ANHO = ".$id_anho." AND ID_MES <12 AND ID_CONCEPTOAPS IN (1502,9010,1350) 
                                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_PERSONA = A.ID_PERSONA),0) AS RT_RETENCIONES_ANUAL
                FROM APS_EMPLEADO A
                INNER JOIN (SELECT * FROM  MOISES.VW_PERSONA_NATURAL_LIGHT WHERE ID_TIPODOCUMENTO IN (1,4,7)) B ON	
                        A.ID_PERSONA = B.ID_PERSONA
                WHERE A.ID_TIPOCONTRATO IN (1,4,2) AND A.ESTADO IS NOT NULL AND
                        (A.FEC_INICIO <= TO_DATE('".$id_anho."-01-01', 'YYYY-MM-DD') OR A.FEC_INICIO < TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD')) AND 
                        (A.FEC_TERMINO >= TO_DATE('".$id_anho."-12-31', 'YYYY-MM-DD') OR A.FEC_TERMINO IS NULL) AND  
                        A.ID_ENTIDAD = ".$id_entidad." ".$condition."
                )T) RECORDS  ".$paginate."
                ) LINES
                ) ITEMS
                ) RESULTS
                ) DATOS
                ) LISTA
                ) FILAS"; 
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getUit($request)
    {
        $search = $request->query('search');
        $pageSize = $request->query('pageSize');
        $id_anho = $request->query('year');
        $query = DB::table('ELISEO.APS_UIT')
            ->select('ID_UIT','UIT','ID_ANHO')
            ->whereRaw("(UPPER(uit) LIKE UPPER('%{$search}%'))");
            if($id_anho){
                $query=$query->where('ID_ANHO','=', $id_anho);
            }
            $query = $query->OrderBy('ID_UIT','DESC');
            if($pageSize > 0) {
                $query = $query->paginate($pageSize);
            } else {
                $query = $query->get();
            }      
        return $query;
    }
    public static function addUit($request) {
        $ret='OK';
        $uit = $request->uit;
        $id_anho = $request->id_anho;
        
        DB::table('ELISEO.APS_UIT')->insert(
            array('UIT' => $uit,
                'ID_ANHO' => $id_anho,
                )
        );
        return $ret;
    }
    public static function editUit($id_uit, $request) {
        $ret='OK';
        $uit = $request->uit;
        $id_anho = $request->id_anho;
    
 
        DB::table('ELISEO.APS_UIT')->where('ID_UIT','=', $id_uit)->update(
            array(
                'UIT' => $uit,
                'ID_ANHO' => $id_anho,
                )
        );
        return $ret;
    }
    public static function deleteUit($id_uit){
        DB::table('ELISEO.APS_UIT')
        ->where('ID_UIT','=',$id_uit)
        ->delete();
    }
}

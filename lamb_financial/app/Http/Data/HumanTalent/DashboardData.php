<?php
/**
 * Created by PhpStorm.
 * User: ulices.julca
 * Date: 07/10/2020
 * Time: 7:12 AM
 */
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
class DashboardData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function report($request)
    {
        $id_entidad=$request->id_entidad;
        $id_depto=$request->id_depto;
        $estado = $request->estado ? $request->estado : 'A';
        if($id_entidad or $id_entidad==='*'){
            $id_entidad=" IN ($id_entidad)";
        }else{
            $id_entidad=" IS NOT NULL";
        }

        if($id_depto and $id_depto!=='*'){
            $id_depto=" IN ($id_depto)";
        }else{
            $id_depto=" IS NOT NULL";
        }
        $where_estado="";
        if($estado){
            $where_estado=" AND ESTADO = '$estado'";
        }
        $result_entities=DB::select("SELECT ID_ENTIDAD,NOMBRE,COUNT(ID_PERSONA) AS TOTAL
                        FROM (SELECT E.ID_ENTIDAD,CE.NOMBRE ,E.ID_PERSONA
                        FROM (SELECT DISTINCT ID_PERSONA,ID_ENTIDAD FROM ELISEO.APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado)  E
                        INNER JOIN ELISEO.VW_CONTA_ENTIDAD CE ON E.ID_ENTIDAD=CE.ID_ENTIDAD)
                        GROUP BY ID_ENTIDAD,NOMBRE");
        $result_total_entities=DB::select("SELECT COUNT(ID_PERSONA) AS TOTAL
                        FROM (SELECT E.ID_ENTIDAD,CE.NOMBRE ,E.ID_PERSONA
                        FROM (SELECT DISTINCT ID_PERSONA,ID_ENTIDAD FROM ELISEO.APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado)  E
                        INNER JOIN ELISEO.VW_CONTA_ENTIDAD CE ON E.ID_ENTIDAD=CE.ID_ENTIDAD)");
        $result_gender=DB::select("SELECT 'MASCULINO' AS NAME,COUNT(1) AS TOTAL FROM (
                        SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                        INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE SEXO='1') PN ON E.ID_PERSONA=PN.ID_PERSONA
                        )
                        UNION ALL
                        SELECT 'FEMENINO' AS NAME,COUNT(1) AS TOTAL FROM (
                                    SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                                    INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE SEXO='2') PN ON E.ID_PERSONA=PN.ID_PERSONA)");

        $total_gender=DB::select("SELECT COUNT(1) AS TOTAL FROM (SELECT DISTINCT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado)");

        $result_civil_status=DB::select("SELECT TEC.ID_TIPOESTADOCIVIL,TEC.NOMBRE AS NAME,COUNT(TEC.ID_TIPOESTADOCIVIL) AS TOTAL
                        FROM MOISES.TIPO_ESTADO_CIVIL TEC 
                        INNER JOIN (SELECT DISTINCT ID_PERSONA,ID_TIPOESTADOCIVIL FROM MOISES.PERSONA_NATURAL) PN ON TEC.ID_TIPOESTADOCIVIL=PN.ID_TIPOESTADOCIVIL
                        INNER JOIN (SELECT DISTINCT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E ON E.ID_PERSONA=PN.ID_PERSONA
                        GROUP BY TEC.ID_TIPOESTADOCIVIL,TEC.NOMBRE
                        ORDER BY TEC.NOMBRE ASC");

        $result_degree_instruction=DB::select("SELECT SE.ID_SITUACION_EDUCATIVO ,SE.NOMBRE AS NAME,COUNT(SE.ID_SITUACION_EDUCATIVO) AS TOTAL
                        FROM MOISES.SITUACION_EDUCATIVA SE 
                        INNER JOIN (SELECT DISTINCT ID_PERSONA,ID_SITUACION_EDUCATIVO FROM MOISES.PERSONA_NATURAL) PN ON SE.ID_SITUACION_EDUCATIVO=PN.ID_SITUACION_EDUCATIVO
                        INNER JOIN (SELECT DISTINCT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E ON E.ID_PERSONA=PN.ID_PERSONA
                        GROUP BY SE.ID_SITUACION_EDUCATIVO,SE.NOMBRE
                        ORDER BY SE.NOMBRE ASC");

        $result_age_range=DB::select("SELECT '-18 - 25' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                            INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE trunc(months_between(sysdate,FEC_NACIMIENTO)/12)<=25) PN ON E.ID_PERSONA=PN.ID_PERSONA)
                        UNION ALL 	
                        SELECT '26 - 35' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                            INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE trunc(months_between(sysdate,FEC_NACIMIENTO)/12)>=26 AND trunc(months_between(sysdate,FEC_NACIMIENTO)/12)<=35) PN ON E.ID_PERSONA=PN.ID_PERSONA)
                        UNION ALL 	
                        SELECT '36 - 45' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                            INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE trunc(months_between(sysdate,FEC_NACIMIENTO)/12)>=36 AND trunc(months_between(sysdate,FEC_NACIMIENTO)/12)<=45) PN ON E.ID_PERSONA=PN.ID_PERSONA)
                        UNION ALL 	
                        SELECT '46 - 55' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                            INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE trunc(months_between(sysdate,FEC_NACIMIENTO)/12)>=46 AND trunc(months_between(sysdate,FEC_NACIMIENTO)/12)<=55) PN ON E.ID_PERSONA=PN.ID_PERSONA)
                        UNION ALL 
                        SELECT '56 - 65+' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT DISTINCT E.ID_PERSONA FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                            INNER JOIN (SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL WHERE trunc(months_between(sysdate,FEC_NACIMIENTO)/12)>=56) PN ON E.ID_PERSONA=PN.ID_PERSONA)");

        $promedio_age_range=DB::select("SELECT ROUND(SUM(AGE)/COUNT(ID_PERSONA),1) AS PROMEDIO  FROM (SELECT DISTINCT E.ID_PERSONA,trunc(months_between(sysdate,PN.FEC_NACIMIENTO)/12) AS AGE FROM (SELECT ID_PERSONA FROM ELISEO.VW_APS_EMPLEADO WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado) E
                        INNER JOIN MOISES.PERSONA_NATURAL PN ON E.ID_PERSONA=PN.ID_PERSONA)");

        $result_antique=DB::select("SELECT '0 meses a 6 meses' AS NAME,COUNT(1) AS TOTAL
                        FROM (
                            SELECT ID_PERSONA, SUM(trunc(months_between(CASE WHEN FEC_TERMINO IS NULL THEN SYSDATE ELSE FEC_TERMINO END,FEC_INICIO ))) AS TOTAL 
                            FROM ELISEO.VW_APS_EMPLEADO 
                            WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado
                            GROUP BY ID_PERSONA)
                        WHERE TOTAL<=6
                        UNION ALL
                        SELECT '6 meses a 1 año' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT ID_PERSONA, SUM(trunc(months_between(CASE WHEN FEC_TERMINO IS NULL THEN SYSDATE ELSE FEC_TERMINO END,FEC_INICIO ))) AS TOTAL 
                            FROM ELISEO.APS_EMPLEADO 
                            WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado
                            GROUP BY ID_PERSONA)
                        WHERE TOTAL>6 AND TOTAL<=12
                        UNION ALL
                        SELECT '1 años a 2 años' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT ID_PERSONA, SUM(trunc(months_between(CASE WHEN FEC_TERMINO IS NULL THEN SYSDATE ELSE FEC_TERMINO END,FEC_INICIO ))) AS TOTAL 
                            FROM ELISEO.VW_APS_EMPLEADO 
                            WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado
                            GROUP BY ID_PERSONA)
                        WHERE TOTAL>12 AND TOTAL<=24
                        UNION ALL
                        SELECT '2 años a más' AS NAME,COUNT(1)  AS TOTAL
                        FROM (
                            SELECT ID_PERSONA, SUM(trunc(months_between(CASE WHEN FEC_TERMINO IS NULL THEN SYSDATE ELSE FEC_TERMINO END,FEC_INICIO ))) AS TOTAL 
                            FROM ELISEO.VW_APS_EMPLEADO 
                            WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado
                            GROUP BY ID_PERSONA)
                        WHERE TOTAL>24");

        $promedio_antique=DB::select("SELECT ROUND(SUM(TOTAL)/COUNT(ID_PERSONA),1) AS PROMEDIO 
                        FROM (SELECT ID_PERSONA, SUM(trunc(months_between(CASE WHEN FEC_TERMINO IS NULL THEN SYSDATE ELSE FEC_TERMINO END,FEC_INICIO)/12)) AS TOTAL 
                            FROM ELISEO.VW_APS_EMPLEADO 
                            WHERE ID_ENTIDAD $id_entidad AND ID_DEPTO $id_depto $where_estado
                            GROUP BY ID_PERSONA)");
        
        $result=[];
        $series=[];
        $series_data=[];
        foreach ($result_gender as $value){
            $series_data[]=(object)array('name'=>$value->name,'y'=>floatval($value->total));
        }
        $series[]=(object)array('name'=>'Género','colorByPoint'=>true,'data'=>$series_data);
        $result[]=(object)array('title'=>'Género','series'=>$series);
        $series=[];
        $series_data=[];
        foreach ($result_civil_status as $value){
            $series_data[]=(object)array('name'=>$value->name,'y'=>floatval($value->total));
        }
        $series[]=(object)array('name'=>'Estado civil','colorByPoint'=>true,'data'=>$series_data);
        $result[]=(object)array('title'=>'Estado civil','series'=>$series);

        $series=[];
        $series_data=[];
        foreach ($result_degree_instruction as $value){
            $series_data[]=(object)array('name'=>$value->name,'y'=>floatval($value->total));
        }
        $series[]=(object)array('name'=>'Grado de instrucción','colorByPoint'=>true,'data'=>$series_data);
        $result[]=(object)array('title'=>'Grado de instrucción','series'=>$series);

        $series=[];
        $series_data=[];
        foreach ($result_age_range as $value){
            $series_data[]=(object)array('name'=>$value->name,'y'=>floatval($value->total));
        }
        $series[]=(object)array('name'=>'Rango etario','colorByPoint'=>true,'data'=>$series_data);
        $promedio=0;
        foreach ($promedio_age_range as $value){
            $promedio=$value->promedio;
        }
        $result[]=(object)array('promedio'=>$promedio,'title'=>'Rango etario','series'=>$series);

        $series=[];
        $series_data=[];
        foreach ($result_antique as $value){
            $series_data[]=(object)array('name'=>$value->name,'y'=>floatval($value->total));
        }
        $series[]=(object)array('name'=>'Gráfico de antiguedad','colorByPoint'=>true,'data'=>$series_data);
        $promedio=0;
        foreach ($promedio_antique as $value){
            $promedio=$value->promedio;
        }
        $result[]=(object)array('promedio'=>$promedio,'title'=>'Gráfico de antiguedad','series'=>$series); 
        $total=0;
        foreach ($result_total_entities as $value){
            $total=$value->total;
        }
        $data=[];
        $data['graphics']=$result;
        $data['entities']=$result_entities;
        $data['total']=$total;
        return $data;
    }
}
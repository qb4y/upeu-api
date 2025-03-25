<?php
namespace App\Http\Data\ProviderServices;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class ProviderData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listEntidades(){
        $query = "SELECT 
                B.NOMBRE TIPO, A.ID_ENTIDAD ID, A.NOMBRE NAME, A.ID_TIPOENTIDAD
                FROM conta_entidad A, tipo_entidad B
                WHERE A. ID_TIPOENTIDAD = B.ID_TIPOENTIDAD  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listEntidadesDepartments($id_entidad){
        $query = "SELECT ID_ENTIDAD,ID_DEPTO,NOMBRE 
                FROM CONTA_ENTIDAD_DEPTO
                WHERE ID_ENTIDAD = ".$id_entidad."
                AND ID_PARENT = 0
                ORDER BY ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function datosPlanilla($id_entidad,$id_anho, $id_mes,$id_depto){
        $sql = "SELECT 
                A.ID_ENTIDAD,A.ID_ANHO,A.ID_MES,
                B.ID_PERSONA,B.NOMBRE,B.PATERNO,B.MATERNO,DECODE(B.SEXO,1,'M','F') AS SEXO,
                D.ID_TIPODOCUMENTO,D.NOMBRE AS MOISES.TIPO_DOCUMENTO,B.NUM_DOCUMENTO,
                C.FEC_NACIMIENTO,C.ID_TIPOESTADOCIVIL,F.NOMBRE AS ESTADO_CIVIL, E.NOMBRE AS NOMBRE_DEPTO,A.NOM_CARGO
                FROM APS_PLANILLA A, MOISES.VW_PERSONA_NATURAL B, MOISES.PERSONA_NATURAL C, MOISES.TIPO_DOCUMENTO D, CONTA_ENTIDAD_DEPTO E,TIPO_ESTADO_CIVIL F
                WHERE A.ID_PERSONA = B.ID_PERSONA
                AND A.ID_PERSONA = C.ID_PERSONA
                AND B.ID_TIPODOCUMENTO = D.ID_TIPODOCUMENTO
                AND A.ID_ENTIDAD = E.ID_ENTIDAD
                AND A.ID_DEPTO = E.ID_DEPTO
                AND C.ID_TIPOESTADOCIVIL = F.ID_TIPOESTADOCIVIL
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO LIKE '".$id_depto."%'
                AND A.ID_ANHO = ".$id_anho."
                AND A.ID_MES = ".$id_mes."
                ORDER BY B.PATERNO,B.MATERNO,B.NOMBRE,A.ID_DEPTO  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showPersonalInformation($id_tipodocumento,$nro_documento,$nombre,$paterno,$materno) {
        $id_persona = "";
        $sql = "SELECT ID_PERSONA FROM MOISES.PERSONA_DOCUMENTO
        WHERE ID_TIPODOCUMENTO = ".$id_tipodocumento."
        AND NUM_DOCUMENTO = '".$nro_documento."' ";
        $oQuery = DB::select($sql);
        if(count($oQuery)>0){
            foreach ($oQuery as $key => $item){
                $id_persona = $item->id_persona;                
            }
        }else{
            if (strlen($nombre.$paterno.$materno) > 0){
                $sql = "SELECT ID_PERSONA FROM MOISES.PERSONA
                WHERE UPPER(NOMBRE) LIKE UPPER('%".$nombre."%')
                AND UPPER(PATERNO) LIKE UPPER('%".$paterno."%')
                AND UPPER(PATERNO) LIKE UPPER('%".$materno."%')
                AND ROWNUM = 1 ";
                $oQuery = DB::select($sql);
                foreach ($oQuery as $key => $item){
                    $id_persona = $item->id_persona;                
                }
            }
        }

        $sql = "SELECT 
        A.ID_PERSONA,A.NOMBRE,A.PATERNO,A.MATERNO,B.NUM_DOCUMENTO AS DNI,B.CORREO,C.CODIGO
        FROM MOISES.PERSONA A LEFT JOIN MOISES.PERSONA_NATURAL B ON A.ID_PERSONA = B.ID_PERSONA
        LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO C
        ON C.ID_PERSONA = A.ID_PERSONA
        WHERE A.ID_PERSONA = ".$id_persona." ";
        $oQuery = DB::select($sql);
        return $oQuery;
    }
}
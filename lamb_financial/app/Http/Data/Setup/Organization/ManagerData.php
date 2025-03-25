<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/11/19
 * Time: 11:34 AM
 */

namespace App\Http\Data\Setup\Organization;

use Illuminate\Support\Facades\DB;

class ManagerData
{

    public static function getResponsables($idOrganization)
    {
//        $query = DB::table('ORG_AREA')->select('')->where('id_area', $idOrganization)->get();
        $querys = "SELECT
          ORG_SEDE_AREA.ID_AREA,
          ORG_SEDE_AREA.ID_SEDEAREA,
          CONTA_ANHO.ID_ANHO,
          CONTA_ENTIDAD_DEPTO.ID_DEPTO,
          CONTA_ENTIDAD_DEPTO.NOMBRE                                         departamento,
          ORG_NIVEL_GESTION.NOMBRE                                           nivel,
          ORG_SEDE.NOMBRE                                                    sede,
          ORG_AREA_RESPONSABLE.ID_RESPONSABLE,
          PE.NOMBRE || ' ' || PE.PATERNO || ' ' || PE.MATERNO responsable,
          ORG_AREA_RESPONSABLE.FECHA,
          ORG_AREA_RESPONSABLE.FECHA,ORG_SEDE_AREA.ESTADO,
          ORG_AREA_RESPONSABLE.ACTIVO
        FROM ORG_AREA
          INNER JOIN ORG_SEDE_AREA ON ORG_AREA.ID_AREA = ORG_SEDE_AREA.ID_AREA
          INNER JOIN ORG_SEDE ON ORG_SEDE.ID_SEDE = ORG_SEDE_AREA.ID_SEDE
          INNER JOIN CONTA_ENTIDAD_DEPTO ON CONTA_ENTIDAD_DEPTO.ID_DEPTO = ORG_SEDE_AREA.ID_DEPTO
          AND ORG_SEDE_AREA.ID_ENTIDAD = CONTA_ENTIDAD_DEPTO.ID_ENTIDAD
          AND ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO
          LEFT OUTER JOIN ORG_AREA_RESPONSABLE ON ORG_AREA_RESPONSABLE.ID_SEDEAREA = ORG_SEDE_AREA.ID_SEDEAREA
          LEFT OUTER JOIN ORG_NIVEL_GESTION ON ORG_AREA_RESPONSABLE.ID_NIVEL = ORG_NIVEL_GESTION.ID_NIVEL
          LEFT OUTER JOIN CONTA_ANHO ON ORG_AREA_RESPONSABLE.ID_ANHO = CONTA_ANHO.ID_ANHO
          LEFT OUTER JOIN MOISES.PERSONA PE ON ORG_AREA_RESPONSABLE.ID_PERSONA = PE.ID_PERSONA
        where ORG_SEDE_AREA.ID_AREA = $idOrganization ORDER BY  ORG_SEDE.NOMBRE";
        $oQuery = DB::connection('oracle')->select($querys);
        return $oQuery;
    }

    public static function generateIdValue($tabla, $campo)
    {
        $valor = DB::table($tabla)->max($campo);
        return $valor + 1;
    }

    public static function createResponsable($idSedeArea, $dataAreaRespo)
    {

//        if (!self::validateExistResponsable($idSedeArea, $dataAreaRespo)) {
//            dd('is valid');
//            $resultSedAre = DB::table('ORG_SEDE_AREA')->insert($dataAreaRespo);
//            if ($resultSedAre) {
//            dd('resssucurs', $resultSedAre, '->', $dataAreaRespo);
//        array_push($dataAreaRespo, 'id_sedearea'=>$idSedeArea);
        $results = DB::table('ORG_AREA_RESPONSABLE')->insert($dataAreaRespo);
//        dd($dataAreaRespo, $resultOrResp);
//            }
//            if ($resultOrResp) {
//        $results = ManagerData::getResponsable($dataAreaRespo['id_responsable']);
//            }
        return $results;
//        } else {
////            dd('ya existe peersona!!');
//            return false;
//        }
    }

    public static function validateExistResponsable($idSedeArea, $dataAreaRespo)
    {
        /*$id_sede = $dataSedeArea['id_sede'];
        $id_depto = $dataSedeArea['id_depto'];
        $id_persona = $dataAreaRespo['id_persona'];
        $id_nivel = $dataAreaRespo['id_nivel'];
        $query = "SELECT orsa.ID_SEDEAREA
                    FROM ORG_SEDE_AREA orsa, ORG_AREA_RESPONSABLE orar
                    WHERE orsa.ID_SEDEAREA = orar.ID_SEDEAREA
                          AND orsa.ID_DEPTO = $id_depto
                          AND orsa.ID_SEDE = $id_sede
                          AND orar.ID_PERSONA = $id_persona
                          AND orar.ID_NIVEL = $id_nivel";
        $result = DB::connection('oracle')->select($query);
        return $result;*/
    }
    public static function validateExistResponsableAnho($idsedearea, $id_anho,$id_persona){
        $query = "SELECT ID_PERSONA 
                FROM ORG_AREA_RESPONSABLE
                WHERE ID_SEDEAREA = ".$idsedearea."
                AND ID_PERSONA = ".$id_persona." ";
        $result = DB::connection('oracle')->select($query);
        return $result;
    }

    public static function getResponsable($idAreaResponsable)
    {
        $querys = "SELECT
                      orsa.ID_DEPTO,
                      orsa.ID_AREA,
                      orsa.ID_SEDE,
                      orsa.ID_SEDEAREA,
                      orar.ID_RESPONSABLE,
                      orar.ID_NIVEL,
                      orar.ID_ANHO,
                      orar.ID_PERSONA,
                      coed.NOMBRE departamento,
                      orar.ACTIVO,
                      pers.NOMBRE || ' ' || pers.MATERNO || ' ' || pers.MATERNO persona
                    FROM CONTA_ENTIDAD_DEPTO coed, ORG_SEDE_AREA orsa, ORG_AREA_RESPONSABLE orar, MOISES.PERSONA pers
                    WHERE coed.ID_DEPTO = orsa.ID_DEPTO AND orsa.ID_SEDEAREA = orar.ID_SEDEAREA AND orar.ID_PERSONA = pers.ID_PERSONA AND
                          orar.ID_RESPONSABLE =$idAreaResponsable";
        $oQuery = DB::connection('oracle')->select($querys);
//        dd('->',end($oQuery));
//        $query = DB::table('ORG_AREA_RESPONSABLE')->select()->where('id_responsable', $idAreaResponsable)->get()->toArray();
//        dd($query);
        return $oQuery;
    }

    public static function updateResponsable($idAreaResponsable, $data)
    {
//        dd('data update', $idAreaResponsable, $data);
        $result = DB::table('ORG_AREA_RESPONSABLE')->where('id_responsable', $idAreaResponsable)->update($data);
        return $result;
    }

    public static function deleteResponsables($idResponsable)
    {
//        $responsable = self::getResponsable($idResponsable);
//        $id_sedearea = $responsable[0]->id_sedearea;
        $result = DB::table('ORG_AREA_RESPONSABLE')->where('id_responsable', $idResponsable)->delete();
//        $queryFin = DB::table('ORG_SEDE_AREA')->where('id_sedearea', $id_sedearea)->delete();
//        dd($result ,'->', $queryFin);
        return $result;
    }

    public static function stripAccents($str)
    {
        return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïññòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiin?ooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public static function findWorker($idEntidad, $textSearch, $estado)
    {
        $ntext_search = str_replace(' ', '', ManagerData::stripAccents($textSearch));
        $utext_search = strtoupper($ntext_search);
        $and_anho = "";
        if ($idEntidad) {
            $and_anho = "AND vitra.ID_ENTIDAD = $idEntidad";
        }
        if ($estado && $estado === 'A') {
//            dd('getting',$estado);
            $and_state = "AND vitra.ESTADO = UPPER('A')";
        } else {
//            dd('getting....',$estado);
            $and_state = "";
        }
        $querys = "SELECT
                      vitra.ID_ENTIDAD,
                      vitra.ID_PERSONA,
                      vitra.NOM_PERSONA NOMBRES,
                      vitra.NUM_DOCUMENTO,
                      vitra.CARGO,
                      vitra.ESTADO,
                      '0' SELECTED
                    FROM MOISES.VW_PERSONA_NATURAL_TRABAJADOR vitra
                    WHERE
                      CONVERT(replace(upper(vitra.NUM_DOCUMENTO||vitra.nom_persona||vitra.nombre||vitra.paterno||vitra.materno), ' ', ''), 'US7ASCII') like '%$utext_search%'
                      $and_anho         
                      $and_state         
                      ";
        $oQuery = DB::connection('oracle')->select($querys);
        // vitra.ID_ENTIDAD = 7124

        return $oQuery;
    }


    public static function findPersonaNatural($textSearch)
    {
        $ntext_search = str_replace(' ', '', ManagerData::stripAccents($textSearch));
        $utext_search = strtoupper($ntext_search);
        $querys = "SELECT * FROM (SELECT 
                    0 AS ID_ENTIDAD,
                    vitra.ID_PERSONA,
                    upper(vitra.PATERNO||' '||vitra.MATERNO)||', '||initcap(lower(vitra.NOMBRE))||', ('||vitra.NUM_DOCUMENTO||')' AS NOMBRES,
                    vitra.NUM_DOCUMENTO,
                    '' AS CARGO,
                    '' AS ESTADO,
                    '0' SELECTED
                FROM moises.VW_PERSONA_NATURAL vitra where
                CONVERT(replace(upper(vitra.NUM_DOCUMENTO||vitra.paterno||vitra.materno||vitra.NOMBRE||vitra.paterno), ' ', ''), 'US7ASCII') like '%$utext_search%'
                ) x WHERE rownum <= 50
                      ";
        $oQuery = DB::connection('oracle')->select($querys);
        return $oQuery;
    }

}
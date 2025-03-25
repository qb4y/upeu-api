<?php

/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/4/19
 * Time: 10:21 AM
 */

namespace App\Http\Data\Setup;

use App\Custom\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use PDO;

class OrganizationData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getAreaParentEntidad($id_etidad)
    {
        $oQuery = DB::table('ORG_AREA')->select('id_area')->whereNull('id_parent')->where('id_entidad', $id_etidad)->first();
        return $oQuery;
    }

    public static function listOrganization($id_area, $id_entidad)
    {
        $query = "SELECT
          orar.id_area,
          orar.ID_PARENT,
          orar.NOMBRE,
          orar.ESTADO,
          orar.ORDEN,
          orta.NOMBRE tipo_area,
          orta.CODIGO tipo_area_codigo,
          (SELECT count(CONNECT_BY_ROOT ID_AREA) - 1
            FROM ORG_AREA arr
            WHERE arr.ID_AREA = orar.id_area
            CONNECT BY arr.ID_AREA = PRIOR arr.ID_PARENT) num_child,
            (SELECT count(sa.ID_AREA)
            FROM ORG_SEDE_AREA sa
            WHERE sa.ID_AREA = orar.id_area) num_dep,
          (SELECT (SELECT count(ORG_AREA_RESPONSABLE.ID_SEDEAREA)
                   FROM ORG_AREA_RESPONSABLE
                   WHERE ID_SEDEAREA = ORG_SEDE_AREA.ID_SEDEAREA AND ORG_SEDE_AREA.ESTADO = 1)
           FROM ORG_SEDE_AREA
           WHERE ORG_SEDE_AREA.ID_AREA = orar.ID_AREA AND ORG_SEDE_AREA.ID_ENTIDAD = orar.ID_ENTIDAD AND
                 ORG_SEDE_AREA.ESTADO = 1 AND ROWNUM = 1) num_respon
        FROM ORG_AREA orar, TIPO_AREA orta
        WHERE orar.ID_TIPOAREA = orta.ID_TIPOAREA AND orar.ID_ENTIDAD = $id_entidad AND orar.ID_AREA = $id_area";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    public static function listChildrenOrganization($id_area, $id_entidad)
    {
        $query = "SELECT
          orar.id_area,
          orar.ID_PARENT,
          orar.NOMBRE,
          orar.ESTADO,
          orar.ORDEN,
          orta.NOMBRE tipo_area,
          orta.CODIGO tipo_area_codigo,
          (SELECT count(CONNECT_BY_ROOT ID_AREA) - 1
            FROM ORG_AREA arr
            WHERE arr.ID_AREA = orar.id_area
            CONNECT BY arr.ID_AREA = PRIOR arr.ID_PARENT) num_child,
            (SELECT count(sa.ID_AREA)
            FROM ORG_SEDE_AREA sa
            WHERE sa.ID_AREA = orar.id_area) num_dep,
          (SELECT (SELECT count(ORG_AREA_RESPONSABLE.ID_SEDEAREA)
                   FROM ORG_AREA_RESPONSABLE
                   WHERE ID_SEDEAREA = ORG_SEDE_AREA.ID_SEDEAREA AND ORG_SEDE_AREA.ESTADO = 1)
           FROM ORG_SEDE_AREA
           WHERE ORG_SEDE_AREA.ID_AREA = orar.ID_AREA AND ORG_SEDE_AREA.ID_ENTIDAD = orar.ID_ENTIDAD AND
                 ORG_SEDE_AREA.ESTADO = 1 AND ROWNUM = 1) num_respon
        FROM ORG_AREA orar, TIPO_AREA orta
        WHERE orar.ID_TIPOAREA = orta.ID_TIPOAREA AND orar.ID_ENTIDAD = $id_entidad AND orar.id_parent = $id_area";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    public static function getInfoOrganization($request)
    {
        $obj = DB::table('org_area')
            ->where('id_area', $request->id_area)
            ->select(DB::raw('coalesce(nivelhijo,0) as nivelhijo'))
            ->first();

        $num_nivel = $obj->nivelhijo;
        $querynivel = '';
        if ($num_nivel > 0) {
            $dato = array();
            for ($j = 1; $j <= $num_nivel; $j++) {
                $dato[] = $j;
            }
            $niveles = implode(",", $dato);
            $querynivel = " and level in(" . $niveles . ") ";
        }
        $query = "SELECT nombre, id_area,id_parent,level,id_tipoarea,tipo_area,tipo_area_codigo,num_dep,num_respon,nivel,nivelhijo,estado 
        from vw_area 
        where id_entidad=:p_id_entidad
        " . $querynivel . "
        start with id_area =:p_id_area
        connect by prior id_area = id_parent
        order siblings by nombre";

        $params = array();
        $params['p_id_entidad'] = $request->id_entidad;
        $params['p_id_area'] = $request->id_area;

        $result = DB::select($query, $params);

        return $result;
    }

    public static function addOrganization($data)
    {
        $result = DB::table('ORG_AREA')->insert($data);
        return $result;
    }

    public static function getMax($tabla, $campo)
    {
        $valor = DB::table($tabla)->max($campo);
        return $valor + 1;
    }

    public static function getOrganization($idOrganization)
    {
        $query = DB::table('ORG_AREA')->where('id_area', $idOrganization)->get();
        return $query;
    }

    public static function updateOrganization($id_org, $id_parent, $id_tipoarea, $estado, $nombre, $nivelhijo, $gth,$codigo)
    {
        $query = DB::table('ORG_AREA')
            ->where('id_area', $id_org)
            ->update([
                'ID_PARENT' => $id_parent,
                'ID_TIPOAREA' => $id_tipoarea,
                'ESTADO' => $estado,
                'NOMBRE' => $nombre,
                'NIVELHIJO' => $nivelhijo,
                'GTH' => $gth,
                'CODIGO' => $codigo
            ]);
        return $query;
    }

    public static function deleteOrganizations($idOrganization)
    {
        //        dd('.sQQQQQ');
        $query = DB::table('ORG_AREA')->where('id_area', $idOrganization)->delete();
        //        $query = DB::table('ORG_AREA')->foreign('id_parent')->references('id_area')->on('ORG_AREA')->where('id_area', $idOrganization)->onDelete('cascade');
        return $query;
    }

    public static function listTypeArea()
    {
        $oQuery = DB::table('TIPO_AREA')->select('ID_TIPOAREA', 'NOMBRE', 'CODIGO')->orderBy('ID_TIPOAREA')->get();
        return $oQuery;
    }

    public static function findDepartmentByName($name, $entidad)
    {
        //   AND REGEXP_LIKE(coed.ID_PARENT, '^[[:digit:]]{6}$')
        $query = "SELECT
                  coed.ID_DEPTO,
                  coed.NOMBRE
                FROM CONTA_ENTIDAD coe, CONTA_ENTIDAD_DEPTO coed
                WHERE
                  coe.ID_ENTIDAD = coed.ID_ENTIDAD
                    AND coe.ES_ACTIVO = 1
                    AND coed.ES_ACTIVO = 1
                    AND coe.ID_ENTIDAD = $entidad
                  AND (upper(coed.NOMBRE) LIKE upper('%$name%')
                    OR upper(coed.id_depto) like upper('%$name%'))
                  ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    public static function findPeople($txtSearch)
    {
        //        dd('PEOPLEFIND', $txtSearch);
        $query = "SELECT
          ID_PERSONA,
          pers.nombre || ' ' || pers.PATERNO || ' ' || pers.MATERNO persona
        FROM MOISES.PERSONA pers
        WHERE
          upper(pers.nombre || ' ' || pers.PATERNO || ' ' || pers.MATERNO) LIKE upper('%$txtSearch%')";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    ////////////////////////////////  fumad...  //////// Que cara.....s 
    public static function listAreasOrders($id_entidad, $id_depto)
    {
        $pu = "";
        $sql = OrganizationData::returnQDeptoSelect($id_entidad, $id_depto,$pu);
        if ($id_depto == '1') {
            $pu = " AND B.ID_SEDE = 1 ";
            $ids = ['2', '3', '4'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }
        if ($id_depto == '5') {
            $pu = " AND B.ID_SEDE = 2 ";
            $ids = ['7','2'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }
        if ($id_depto == '7') {
            $pu = " AND B.ID_SEDE = 2 ";
            $ids = ['5','2'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }
        if ($id_depto == '2') {
            $ids = ['1', '3', '4'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }
        if ($id_depto == '3') {
            $pu = " AND B.ID_SEDE = 1 ";
            $ids = ['1', '2', '4'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }
        if ($id_depto == '4') {
            $pu = " AND B.ID_SEDE = 1 ";
            $ids = ['1', '2', '3'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }
        if ($id_depto == '8') {
            $pu = " AND B.ID_SEDE = 2 ";
            $ids = ['5','3'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSelect($id_entidad, $ids[$i],$pu);
            }
        }

        $sql = $sql . " order by nombre ";
        $query = DB::select($sql);
        //dd($query);
        return $query;
    }

    public static function returnQDeptoSelect($id_entidad, $idDepto,$pu)
    {
        return "SELECT B.ID_SEDEAREA,
    B.ID_DEPTO,
    A.NOMBRE,
    D.NOMBRE                AS NOMBRE_DEPTO,
    C.ACTIVO,
    NVL(C.ID_FORMULARIO, 1) AS ID_FORMULARIO
    FROM ORG_AREA A,
         ORG_SEDE_AREA B,
         PEDIDO_AREA C,
         CONTA_ENTIDAD_DEPTO D
    WHERE A.ID_AREA = B.ID_AREA
      AND B.ID_SEDEAREA = C.ID_SEDEAREA
      AND B.ID_ENTIDAD = D.ID_ENTIDAD
      AND B.ID_DEPTO = D.ID_DEPTO
      AND B.ID_ENTIDAD = " . $id_entidad . "
      AND C.ACTIVO = '1'
      ".$pu."
      AND B.ID_DEPTO like '" . $idDepto . "%'";
    }
    ///////
    public static function listAreasOrdersTo($id_entidad, $id_depto, $search)
    {
        $pu = "";
        $sql = OrganizationData::returnQDeptoSearcht($id_entidad, $id_depto, $search,$pu);
        if ($id_depto == '1') {
            $pu = " AND B.ID_SEDE = 1 ";
            $ids = ['2', '3', '4'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSearcht($id_entidad, $ids[$i], $search,$pu);
            }
        }
        if ($id_depto == '5') {
            $pu = " AND B.ID_SEDE = 2 ";
            $ids = ['7'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSearcht($id_entidad, $ids[$i], $search,$pu);
            }
        }
        if ($id_depto == '2') {
            $ids = ['1', '3', '4'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSearcht($id_entidad, $ids[$i], $search,$pu);
            }
        }
        if ($id_depto == '3') {
            $pu = " AND B.ID_SEDE = 1 ";
            $ids = ['1', '2', '4'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSearcht($id_entidad, $ids[$i], $search,$pu);
            }
        }
        if ($id_depto == '4') {
            $pu = " AND B.ID_SEDE = 1 ";
            $ids = ['1', '2', '3'];
            for ($i = 0; $i < count($ids); $i++) {
                $sql = $sql . " union " . OrganizationData::returnQDeptoSearcht($id_entidad, $ids[$i], $search,$pu);
            }
        }


        $sql = $sql . " order by nombre ";

        $query = DB::select($sql);
        // dd($query);
        return $query;
    }
    public static function returnQDeptoSearcht($id_entidad, $idDepto,  $search,$pu)
    {
        return "SELECT B.ID_SEDEAREA,
    B.ID_DEPTO,
    A.NOMBRE,
    D.NOMBRE                AS NOMBRE_DEPTO,
    C.ACTIVO,
    NVL(C.ID_FORMULARIO, 1) AS ID_FORMULARIO
    FROM ORG_AREA A,
         ORG_SEDE_AREA B,
         PEDIDO_AREA C,
         CONTA_ENTIDAD_DEPTO D
    WHERE A.ID_AREA = B.ID_AREA
      AND B.ID_SEDEAREA = C.ID_SEDEAREA
      AND B.ID_ENTIDAD = D.ID_ENTIDAD
      AND B.ID_DEPTO = D.ID_DEPTO
      AND B.ID_ENTIDAD = " . $id_entidad . "
      AND (B.ID_DEPTO LIKE ('%$search%') OR UPPER(D.NOMBRE) LIKE UPPER('%$search%') OR UPPER(A.NOMBRE) LIKE UPPER('%$search%'))
      AND C.ACTIVO = '1'
      ".$pu."
      AND B.ID_DEPTO like '" . $idDepto . "%'";
    }
    //////////////////////////////////////
    public static function addOrUpdateAreaOrder($data)
    {
        $areaOrder = DB::table('PEDIDO_AREA')->where('id_sedearea', $data['id_sedearea'])->get();
        if ($areaOrder->isEmpty()) {
            $result = DB::table('PEDIDO_AREA')->insert($data);
        } else {
            $result = DB::table('PEDIDO_AREA')->where('id_sedearea', $data['id_sedearea'])->update($data);
        }
        return $result;
    }

    public static function getInventorySedeArea($idSedeArea)
    {
        $inventory = DB::table('INVENTARIO_ALMACEN')->select('id_sedearea')->where('id_sedearea', $idSedeArea)->get();
        //        dd('====', count($inventory));
        return $inventory;
    }

    public static function deleteAreaOrder($idAreaPedido)
    {
        $query = DB::table('PEDIDO_AREA')->where('id_sedearea', $idAreaPedido)->delete();
        return $query;
    }

    public static function updateAreaOrder($idAreaPedido, $data)
    {
        $query = DB::table('PEDIDO_AREA')->where('id_sedearea', $idAreaPedido)->update($data);
        return $query;
    }

    public static function listAreas($id_entidad)
    {
        $sql = "SELECT
                      B.ID_SEDEAREA,B.ID_DEPTO,A.NOMBRE, D.NOMBRE AS NOMBRE_DEPTO -- ,C.ACTIVO
              FROM ORG_AREA A, ORG_SEDE_AREA B, CONTA_ENTIDAD_DEPTO D
              WHERE A.ID_AREA = B.ID_AREA
              -- AND B.ID_SEDEAREA = C.ID_SEDEAREA
              AND B.ID_ENTIDAD = D.ID_ENTIDAD
              AND B.ID_DEPTO = D.ID_DEPTO
              AND B.ID_ENTIDAD = " . $id_entidad . "
              -- AND C.ACTIVO = '1'
              ORDER BY A.NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getArea($id_entidad, $id_persona)
    {
        $sql = "SELECT
                B.ID_SEDEAREA,B.ID_DEPTO,A.NOMBRE, D.NOMBRE AS NOMBRE_DEPTO,C.ACTIVO
                FROM ORG_AREA A, ORG_SEDE_AREA B, ORG_AREA_RESPONSABLE C, CONTA_ENTIDAD_DEPTO D
                WHERE A.ID_AREA = B.ID_AREA
                AND B.ID_SEDEAREA = C.ID_SEDEAREA
                AND B.ID_ENTIDAD = D.ID_ENTIDAD
                AND B.ID_DEPTO = D.ID_DEPTO
                AND B.ID_ENTIDAD = " . $id_entidad . "
                AND C.ID_PERSONA = " . $id_persona . "
                AND C.ACTIVO = '1'
                ORDER BY A.NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }

    public static function getAreaParentSedeArea($id_etidad)
    {

        $oQuery = DB::table('ORG_SEDE_AREA')
            ->join('ORG_AREA', 'ORG_SEDE_AREA.id_area', '=', 'ORG_AREA.id_area')
            ->select('ORG_AREA.id_area')
            ->whereNull('ORG_AREA.id_parent')
            ->where('ORG_AREA.id_entidad', $id_etidad)->first();
        return $oQuery;
    }

    public static function listSedeAreaPersona($id_entidad, $id_persona)
    {
        $query = "SELECT
                      --ORG_AREA.*,
                      ORG_AREA.nombre || ' - ' ||CONTA_ENTIDAD_DEPTO.nombre area_departamento,
                      ORG_SEDE_AREA.ID_SEDEAREA,
                      ORG_SEDE_AREA.ID_DEPTO,
                      (SELECT (CASE WHEN (count(peaa.ID_PAUTORIZA)) = 0
                        THEN 0
                               WHEN (count(peaa.ID_PAUTORIZA)) = 1
                                 THEN 1
                               ELSE 0 END)
                       FROM PEDIDO_AUTORIZA_AREA peaa
                       WHERE
                         peaa.ID_ENTIDAD = $id_entidad AND peaa.ID_PERSONA = $id_persona AND peaa.ID_SEDEAREA = ORG_SEDE_AREA.ID_SEDEAREA) SELECTED
                    FROM ORG_AREA
                      JOIN ORG_SEDE_AREA ON ORG_AREA.ID_AREA = ORG_SEDE_AREA.ID_AREA
                      JOIN CONTA_ENTIDAD_DEPTO ON ORG_SEDE_AREA.ID_DEPTO = CONTA_ENTIDAD_DEPTO.ID_DEPTO
                    WHERE ORG_AREA.ID_ENTIDAD = $id_entidad
                    --START WITH ORG_AREA.ID_PARENT IS NULL
                    --CONNECT BY PRIOR ORG_AREA.ID_AREA = ORG_AREA.ID_PARENT 
                    ORDER BY ORG_AREA.ID_AREA, ORG_AREA.NOMBRE asc";
        $oQuery = Collect(DB::connection('oracle')->select($query));
        $oQuery->map(function ($item) {
            if ($item->selected == '0') {
                $item->selected = false;
            } else if ($item->selected == '1') {
                $item->selected = true;
            }
        });
        return $oQuery;
    }

    public static function listAreaSedeArea($id_area, $id_entidad, $parentOrChild, $idPersona, $idDepartamento)
    {
        if ($parentOrChild) {
            $andQ = "AND orar.ID_AREA = $id_area";
        } else {
            $andQ = "AND orar.id_parent = $id_area";
        }

        /*
        $query = "SELECT
                      orar.id_area,
                      orar.ID_PARENT,
                      orar.NOMBRE,
                      orar.ESTADO,
                      orar.ORDEN,
                      orsa.ID_SEDEAREA,
                      (select coed.NOMBRE from CONTA_ENTIDAD_DEPTO coed where coed.ID_DEPTO = orsa.ID_DEPTO) departamento,
                      UPPER(orar.nombre||' - '||(SELECT coed.NOMBRE
                        FROM CONTA_ENTIDAD_DEPTO coed
                        WHERE coed.ID_DEPTO = orsa.ID_DEPTO) 
                      ) area_dep,
                      (SELECT (CASE WHEN (count(peaa.ID_PAUTORIZA)) = 0
                        THEN 0
                               WHEN (count(peaa.ID_PAUTORIZA)) = 1
                                 THEN 1
                               ELSE 0 END)
                       FROM PEDIDO_AUTORIZA_AREA peaa
                       WHERE peaa.ID_ENTIDAD = $id_entidad AND peaa.ID_PERSONA = $idPersona AND peaa.ID_SEDEAREA = orsa.ID_SEDEAREA)           SELECTED
                    FROM
                      ORG_AREA orar, ORG_SEDE_AREA orsa
                    WHERE
                      orar.id_area = orsa.id_area
                      and orsa.id_sede = $idDepartamento
                    $andQ
                    ";
            */


        $query = "SELECT
                            orar.id_area,orar.ID_PARENT,orar.NOMBRE, orar.ESTADO,
                            orar.ORDEN,orsa.ID_SEDEAREA,
                            orsa.id_sede
                            ,(select coed.NOMBRE from CONTA_ENTIDAD_DEPTO coed 
                            where coed.id_entidad=$id_entidad and coed.ID_DEPTO = orsa.ID_DEPTO) departamento
                            
                            ,UPPER(orar.nombre||' - '||(SELECT coed.NOMBRE
                            FROM CONTA_ENTIDAD_DEPTO coed
                            WHERE coed.id_entidad=$id_entidad and coed.ID_DEPTO = orsa.ID_DEPTO) 
                            ) area_dep
                        
                            , (SELECT (CASE WHEN (count(peaa.ID_PAUTORIZA)) = 0
                            THEN 0
                                    WHEN (count(peaa.ID_PAUTORIZA)) = 1
                                    THEN 1
                                    ELSE 0 END)
                            FROM PEDIDO_AUTORIZA_AREA peaa
                            WHERE peaa.ID_ENTIDAD = $id_entidad AND peaa.ID_PERSONA = $idPersona 
                            AND peaa.ID_SEDEAREA = orsa.ID_SEDEAREA) SELECTED
                            
                        FROM
                            ORG_AREA orar, ORG_SEDE_AREA orsa
                        WHERE
                            orar.id_area = orsa.id_area
                            -- and orsa.id_depto = '$idDepartamento'
                            $andQ
                        ";


        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    public static function addAuthorizeAreas($data, $whereData)
    {
        $result = false;
        if ($data) {
            $exist_p = DB::table('PEDIDO_AUTORIZA_AREA')->where($whereData)->pluck('id_pautoriza');
            if ($exist_p->count()) {

                DB::table('PEDIDO_AUTORIZA_AREA')->whereIn('ID_PAUTORIZA', $exist_p->toArray())->delete();
                $result = DB::table('PEDIDO_AUTORIZA_AREA')->insert($data);
                //            dd('adddeed');
            } else {
                $result = DB::table('PEDIDO_AUTORIZA_AREA')->insert($data);
            }
        } else {
            $result = DB::table('PEDIDO_AUTORIZA_AREA')
                ->where('id_persona', $whereData['id_persona'])
                ->where('id_depto', $whereData['id_depto'])
                ->where('id_entidad', $whereData['id_entidad'])
                ->delete();
        }
        return $result;
    }
    
    public static function copyOrganization($request, $id_user)
    {
        $result = [];
        $result['success'] = true;
        $result['message'] = '';
        $result['data'] = [];
        try{
           
            $id_entidad_from = $request->id_entidad_from;
            $id_area_from = $request->id_area_from ? $request->id_area_from : 0;
            $id_entidad_to = $request->id_entidad_to;
            $id_area_to = $request->id_area_to ? $request->id_area_to : 0;
            $in_depto = $request->in_depto ? $request->in_depto : 0;
            $in_area_parent = $request->in_area_parent ? $request->in_area_parent : 0;

            $paso = $request->paso ? $request->paso : 1;
            $eliminar_org = $request->eliminar_org ? $request->eliminar_org : 0;

            if($paso == 1 and (!$id_area_to or $id_area_to == 0)){
                $count = DB::table('ELISEO.ORG_AREA OA')
                ->leftjoin('ELISEO.ORG_SEDE_AREA OSA', 'OA.ID_AREA', '=', DB::raw(' OSA.ID_AREA AND OA.ID_ENTIDAD = OSA.ID_ENTIDAD'))
                ->where('OA.ID_ENTIDAD', $id_entidad_to)
                ->where('OA.ESTADO', '1')
                ->count();
                if($count > 0){
                    $sql = "SELECT T.ID_AREA,T.ID_PARENT,T.ID_ENTIDAD,T.ID_TIPOAREA,T.NOMBRE,T.IZQUIERDA,T.DERECHA,
                    T.ORDEN,T.ESTADO,T.CODIGO,T.TIENEHIJO,T.TIPO_AREA,T.NUM_DEP,T.NUM_RESPON,T.NIVELHIJO,MAX(T.NIVEL_COPIA) AS NIVEL  
                    FROM (SELECT A.*, LEVEL AS NIVEL_COPIA
                    from ELISEO.VW_AREA  A
                    where A.ID_ENTIDAD = $id_entidad_to
                    connect by prior A.ID_AREA = A.ID_PARENT) T
                    GROUP BY T.ID_AREA,T.ID_PARENT,T.ID_ENTIDAD,T.ID_TIPOAREA,T.NOMBRE,T.IZQUIERDA,T.DERECHA,
                    T.ORDEN,T.ESTADO,T.CODIGO,T.TIENEHIJO,T.TIPO_AREA,T.NUM_DEP,T.NUM_RESPON,T.NIVELHIJO
                    ORDER BY NIVEL ASC";
                    $org = DB::select($sql);
                    $result['success'] = false;
                    $result['message'] = 'Se encontró áreas registradas.';
                    $result['data'] = $org;
                    return $result;
                }
            }

            $pdo = DB::getPdo();
            DB::beginTransaction();

            $error = 0;
            $msgerror = "";
          
            $stmt = $pdo->prepare("begin ELISEO.PKG_SETUP.SP_COPIAR_ORG_AREAS(
                :P_ID_USER, 
                :P_ID_ENTIDAD_FROM, 
                :P_ID_AREA_FROM, 
                :P_ID_ENTIDAD_TO, 
                :P_ID_AREA_TO, 
                :P_IN_DEPTO, 
                :P_IN_AREA_PARENT, 
                :P_ELIMINAR_ORG, 
                :P_ERROR, 
                :P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD_FROM', $id_entidad_from, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_AREA_FROM', $id_area_from, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD_TO', $id_entidad_to, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_AREA_TO', $id_area_to, PDO::PARAM_INT);
            $stmt->bindParam(':P_IN_DEPTO', $in_depto, PDO::PARAM_INT);
            $stmt->bindParam(':P_IN_AREA_PARENT', $in_area_parent, PDO::PARAM_INT);
            $stmt->bindParam(':P_ELIMINAR_ORG', $eliminar_org, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR,5000);
            $stmt->execute();
            if($error === 0){
                DB::commit();
                $result['success'] = true;
                $result['message'] = $msgerror;
                $result['data'] = [];
            }else{
                DB::rollback();
                $result['success'] = false;
                $result['message'] = $msgerror;
                $result['data'] = $error;
            }
            
        }catch(Exception $e){
            $result['success'] = false;
            $result['message'] = '';
            $result['data'] = [];
        }
        return $result;
    }
}

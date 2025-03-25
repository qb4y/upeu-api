<?php
namespace App\Http\Data\Schools\Setup;
use Exception;
use Illuminate\Support\Facades\DB;
use PDO;

class SetupData
{
    public function __construct()
    {
    }
    public static function getSysdate()
    {
        return DB::raw('SYSDATE');
    }
    public static function getIdMax($tabla,$campo)
    {
        $valor = DB::table($tabla)->max($campo);
        return $valor;
    }
    /* SCHOOL_CONFIG */
    public static function listConfigs($id_entidad, $id_depto)
    {
        $rows = DB::table('SCHOOL_CONFIG')
            ->join('SCHOOL_NIVEL', 'SCHOOL_CONFIG.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
            ->select(
                'SCHOOL_CONFIG.ID_CONFIG',
                'SCHOOL_CONFIG.ID_ENTIDAD',
                'SCHOOL_CONFIG.ID_DEPTO',
                'SCHOOL_CONFIG.ID_NIVEL',
                'SCHOOL_CONFIG.CODIGO_UGEL',
                DB::raw(
                    "
                    SCHOOL_NIVEL.NOMBRE NOMBRE_NIVEL
                    "
                )
            )
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->get();
        return $rows;
    }
    public static function showConfigs($id_entidad, $id_depto, $id_config)
    {
        $rows = DB::table('SCHOOL_CONFIG')
            ->join('SCHOOL_NIVEL', 'SCHOOL_CONFIG.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
            ->select(
                'SCHOOL_CONFIG.ID_CONFIG',
                'SCHOOL_CONFIG.ID_ENTIDAD',
                'SCHOOL_CONFIG.ID_DEPTO',
                'SCHOOL_CONFIG.ID_NIVEL',
                'SCHOOL_CONFIG.CODIGO_UGEL',
                DB::raw(
                    "
                    SCHOOL_NIVEL.NOMBRE NOMBRE_NIVEL
                    "
                )
            )
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->where('ID_CONFIG',$id_config)
            ->first();
        return $rows;
    }
    public static function addConfigs($data)
    {
        $id_config = self::getIdMax("SCHOOL_CONFIG","ID_CONFIG")+1;
        $data["id_config"] = $id_config;
        $result = DB::table('SCHOOL_CONFIG')->insert($data);
        if($result)
        {
            return self::showConfigs($data['id_entidad'], $data['id_depto'], $id_config);
        }
        return $result;
    }
    public static function updateConfigs($data, $id_config, $id_entidad, $id_depto)
    {
        $result = DB::table('SCHOOL_CONFIG')
        ->where('ID_CONFIG', $id_config)
        ->update($data);
        if($result)
        {
            return self::showConfigs($id_entidad, $id_depto, $id_config);
        }
        return $result;
    }
    /* SCHOOL_NIVEL */
    public static function listStages()
    {
        $rows = DB::table('SCHOOL_NIVEL')
            ->select(
                'ID_NIVEL',
                'CODIGO',
                'NOMBRE',
                'ESTADO'
            )
            ->get();
        return $rows;
    }
    public static function showStages($id_nivel)
    {
        $rows = DB::table('SCHOOL_NIVEL')
            ->select(
                'ID_NIVEL',
                'CODIGO',
                'NOMBRE',
                'ESTADO'
            )
            ->where('ID_NIVEL',$id_nivel)
            ->first();
        return $rows;
    }
    public static function addStages($data)
    {
        $id_nivel = self::getIdMax("SCHOOL_NIVEL","ID_NIVEL")+1;
        $data["id_nivel"] = $id_nivel;
        $result = DB::table('SCHOOL_NIVEL')->insert($data);
        if($result)
        {
            return self::showStages($id_nivel);
        }
        return $result;
    }
    public static function updateStages($data, $id_nivel)
    {
        $result = DB::table('SCHOOL_NIVEL')
        ->where('ID_NIVEL', $id_nivel)
        ->update($data);
        if($result)
        {
            return self::showStages($id_nivel);
        }
        return $result;
    }
    /* SCHOOL_GRADO */
    public static function listGrades()
    {
        $rows = DB::table('SCHOOL_GRADO')
            ->select(
                'ID_GRADO',
                'CODIGO',
                'NOMBRE',
                'ESTADO'
            )
            ->get();
        return $rows;
    }
    public static function showGrades($id_grado)
    {
        $rows = DB::table('SCHOOL_GRADO')
            ->select(
                'ID_GRADO',
                'CODIGO',
                'NOMBRE',
                'ESTADO'
            )
            ->where('ID_GRADO',$id_grado)
            ->first();
        return $rows;
    }
    public static function addGrades($data)
    {
        $id_grado = self::getIdMax("SCHOOL_GRADO","ID_GRADO")+1;
        $data["id_grado"] = $id_grado;
        $result = DB::table('SCHOOL_GRADO')->insert($data);
        if($result)
        {
            return self::showGrades($id_grado);
        }
        return $result;
    }
    public static function updateGrades($data, $id_grado)
    {
        $result = DB::table('SCHOOL_GRADO')
        ->where('ID_GRADO', $id_grado)
        ->update($data);
        if($result)
        {
            return self::showGrades($id_grado);
        }
        return $result;
    }
    /* SCHOOL_TIPODESCUENTO */
    public static function listTypeDiscounts()
    {
        $rows = DB::table('SCHOOL_TIPODESCUENTO')
            ->select(
                'ID_TIPODESCUENTO',
                'NOMBRE',
                'ESTADO'
            )
            ->get();
        return $rows;
    }
    public static function showTypeDiscounts($id_tipodescuento)
    {
        $rows = DB::table('SCHOOL_TIPODESCUENTO')
            ->select(
                'ID_TIPODESCUENTO',
                'NOMBRE',
                'ESTADO'
            )
            ->where('ID_TIPODESCUENTO',$id_tipodescuento)
            ->first();
        return $rows;
    }
    public static function addTypeDiscounts($data)
    {
        $id_tipodescuento = self::getIdMax("SCHOOL_TIPODESCUENTO","ID_TIPODESCUENTO")+1;
        $data["id_tipodescuento"] = $id_tipodescuento;
        $result = DB::table('SCHOOL_TIPODESCUENTO')->insert($data);
        if($result)
        {
            return self::showTypeDiscounts($id_tipodescuento);
        }
        return $result;
    }
    public static function updateTypeDiscounts($data, $id_tipodescuento)
    {
        $result = DB::table('SCHOOL_TIPODESCUENTO')
        ->where('ID_TIPODESCUENTO', $id_tipodescuento)
        ->update($data);
        if($result)
        {
            return self::showTypeDiscounts($id_tipodescuento);
        }
        return $result;
    }
    /* SCHOOL_TIPOPAGO */
    public static function listTypePayments()
    {
        $rows = DB::table('SCHOOL_TIPOPAGO')
            ->select(
                'ID_TIPOPAGO',
                'NOMBRE',
                'ESTADO'
            )
            ->get();
        return $rows;
    }
    public static function showTypePayments($id_tipopago)
    {
        $rows = DB::table('SCHOOL_TIPOPAGO')
            ->select(
                'ID_TIPOPAGO',
                'NOMBRE',
                'ESTADO'
            )
            ->where('ID_TIPOPAGO',$id_tipopago)
            ->first();
        return $rows;
    }
    public static function addTypePayments($data)
    {
        $id_tipopago = self::getIdMax("SCHOOL_TIPOPAGO","ID_TIPOPAGO")+1;
        $data["id_tipopago"] = $id_tipopago;
        $result = DB::table('SCHOOL_TIPOPAGO')->insert($data);
        if($result)
        {
            return self::showTypePayments($id_tipopago);
        }
        return $result;
    }
    public static function updateTypePayments($data, $id_tipopago)
    {
        $result = DB::table('SCHOOL_TIPOPAGO')
        ->where('ID_TIPOPAGO', $id_tipopago)
        ->update($data);
        if($result)
        {
            return self::showTypePayments($id_tipopago);
        }
        return $result;
    }
    /* SCHOOL_NIVEL_GRADO */
    public static function listStagesGrades($id_entidad, $id_depto, $id_config)
    {
        $rows = DB::table('SCHOOL_NIVEL_GRADO')
            ->join('SCHOOL_CONFIG', 'SCHOOL_NIVEL_GRADO.ID_CONFIG', '=', 'SCHOOL_CONFIG.ID_CONFIG')
            ->join('SCHOOL_NIVEL', 'SCHOOL_CONFIG.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
            ->join('SCHOOL_GRADO', 'SCHOOL_NIVEL_GRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
            ->select(
                'SCHOOL_NIVEL_GRADO.ID_NGRADO',
                'SCHOOL_NIVEL_GRADO.ID_CONFIG',
                'SCHOOL_NIVEL_GRADO.ID_GRADO',
                'SCHOOL_CONFIG.ID_NIVEL',
                'SCHOOL_CONFIG.CODIGO_UGEL',
                DB::raw(
                    "
                    SCHOOL_NIVEL.NOMBRE NOMBRE_NIVEL,
                    SCHOOL_GRADO.NOMBRE NOMBRE_GRADO
                    "
                )
            )
            ->where('SCHOOL_CONFIG.ID_ENTIDAD', $id_entidad)
            ->where('SCHOOL_CONFIG.ID_DEPTO', $id_depto)
            ->where('SCHOOL_CONFIG.ID_CONFIG', $id_config)
            ->get();
        return $rows;
    }
    public static function showStagesGrades($id_entidad, $id_depto, $id_ngrado)
    {
        $rows = DB::table('SCHOOL_NIVEL_GRADO')
            ->join('SCHOOL_CONFIG', 'SCHOOL_NIVEL_GRADO.ID_CONFIG', '=', 'SCHOOL_CONFIG.ID_CONFIG')
            ->join('SCHOOL_NIVEL', 'SCHOOL_CONFIG.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
            ->join('SCHOOL_GRADO', 'SCHOOL_NIVEL_GRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
            ->select(
                'SCHOOL_NIVEL_GRADO.ID_NGRADO',
                'SCHOOL_NIVEL_GRADO.ID_CONFIG',
                'SCHOOL_NIVEL_GRADO.ID_GRADO',
                'SCHOOL_CONFIG.ID_NIVEL',
                'SCHOOL_CONFIG.CODIGO_UGEL',
                DB::raw(
                    "
                    SCHOOL_NIVEL.NOMBRE NOMBRE_NIVEL,
                    SCHOOL_GRADO.NOMBRE NOMBRE_GRADO
                    "
                )
            )
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_DEPTO', $id_depto)
            ->where('ID_NGRADO',$id_ngrado)
            ->first();
        return $rows;
    }
    public static function addStagesGrades($data, $id_entidad, $id_depto)
    {
        $id_ngrado = self::getIdMax("SCHOOL_NIVEL_GRADO","ID_NGRADO")+1;
        $data["id_ngrado"] = $id_ngrado;
        $result = DB::table('SCHOOL_NIVEL_GRADO')->insert($data);
        if($result)
        {
            return self::showStagesGrades($id_entidad, $id_depto, $id_ngrado);
        }
        return $result;
    }
    public static function updateStagesGrades($data, $id_ngrado, $id_entidad, $id_depto)
    {
        $result = DB::table('SCHOOL_NIVEL_GRADO')
        ->where('ID_NGRADO', $id_ngrado)
        ->update($data);
        if($result)
        {
            return self::showStagesGrades($id_entidad, $id_depto, $id_ngrado);
        }
        return $result;
    }
    /* SCHOOL_VACANTE */
    public static function listVacants($id_periodo, $id_config)
    {
        $rows = DB::table('SCHOOL_VACANTE')
            ->join('SCHOOL_PERIODO_ESCOLAR', 'SCHOOL_VACANTE.ID_PERIODO', '=', 'SCHOOL_PERIODO_ESCOLAR.ID_PERIODO')
            ->join('SCHOOL_NIVEL_GRADO', 'SCHOOL_VACANTE.ID_NGRADO', '=', 'SCHOOL_NIVEL_GRADO.ID_NGRADO')
            ->join('SCHOOL_CONFIG', 'SCHOOL_NIVEL_GRADO.ID_CONFIG', '=', 'SCHOOL_CONFIG.ID_CONFIG')
            ->select(
                'SCHOOL_VACANTE.ID_VACANTE',
                'SCHOOL_VACANTE.ID_PERIODO',
                'SCHOOL_VACANTE.ID_NGRADO',
                'SCHOOL_VACANTE.NRO_VACANTE',
                'SCHOOL_VACANTE.ESTADO',
                'SCHOOL_CONFIG.ID_CONFIG',
                'SCHOOL_CONFIG.ID_NIVEL',
                'SCHOOL_NIVEL_GRADO.ID_GRADO',
                DB::raw(
                    "
                    (SELECT NOMBRE FROM SCHOOL_NIVEL WHERE SCHOOL_NIVEL.ID_NIVEL = SCHOOL_CONFIG.ID_NIVEL) NOMBRE_NIVEL,
                    (SELECT NOMBRE FROM SCHOOL_GRADO WHERE SCHOOL_GRADO.ID_GRADO = SCHOOL_NIVEL_GRADO.ID_GRADO) NOMBRE_GRADO
                    "
                )
            )
            ->where('SCHOOL_VACANTE.ID_PERIODO', $id_periodo)
            ->where('SCHOOL_NIVEL_GRADO.ID_CONFIG', $id_config)
            ->get();
        return $rows;
    }
    public static function showVacants($id_vacante, $id_periodo)
    {
        $rows = DB::table('SCHOOL_VACANTE')
            ->join('SCHOOL_PERIODO_ESCOLAR', 'SCHOOL_VACANTE.ID_PERIODO', '=', 'SCHOOL_PERIODO_ESCOLAR.ID_PERIODO')
            ->join('SCHOOL_NIVEL_GRADO', 'SCHOOL_VACANTE.ID_NGRADO', '=', 'SCHOOL_NIVEL_GRADO.ID_NGRADO')
            ->join('SCHOOL_CONFIG', 'SCHOOL_NIVEL_GRADO.ID_CONFIG', '=', 'SCHOOL_CONFIG.ID_CONFIG')
            ->select(
                'SCHOOL_VACANTE.ID_VACANTE',
                'SCHOOL_VACANTE.ID_PERIODO',
                'SCHOOL_VACANTE.ID_NGRADO',
                'SCHOOL_VACANTE.NRO_VACANTE',
                'SCHOOL_VACANTE.ESTADO',
                'SCHOOL_CONFIG.ID_CONFIG',
                'SCHOOL_CONFIG.ID_NIVEL',
                'SCHOOL_NIVEL_GRADO.ID_GRADO',
                DB::raw(
                    "
                    (SELECT NOMBRE FROM SCHOOL_NIVEL WHERE SCHOOL_NIVEL.ID_NIVEL = SCHOOL_CONFIG.ID_NIVEL) NOMBRE_NIVEL,
                    (SELECT NOMBRE FROM SCHOOL_GRADO WHERE SCHOOL_GRADO.ID_GRADO = SCHOOL_NIVEL_GRADO.ID_GRADO) NOMBRE_GRADO
                    "
                )
            )
            ->where('SCHOOL_VACANTE.ID_PERIODO', $id_periodo)
            ->where('SCHOOL_VACANTE.ID_VACANTE', $id_vacante)
            ->first();
        return $rows;
    }
    public static function addVacants($data)
    {
        $id_vacante = self::getIdMax("SCHOOL_VACANTE","ID_VACANTE")+1;
        $data["id_vacante"] = $id_vacante;
        $result = DB::table('SCHOOL_VACANTE')->insert($data);
        if($result)
        {
            return self::showVacants($id_vacante, $data["id_periodo"]);
        }
        return $result;
    }
    public static function updateVacants($data, $id_vacante, $id_periodo)
    {
        $result = DB::table('SCHOOL_VACANTE')
        ->where('ID_VACANTE', $id_vacante)
        ->update($data);
        if($result)
        {
            return self::showVacants($id_vacante, $id_periodo);
        }
        return $result;
    }
    /* SCHOOL_CRITERIO */
    public static function listCriterions($id_periodo, $id_config)
    {
        $rows = DB::table('SCHOOL_CRITERIO')
            ->join('SCHOOL_NIVEL_GRADO', 'SCHOOL_CRITERIO.ID_NGRADO', '=', 'SCHOOL_NIVEL_GRADO.ID_NGRADO')
            ->join('SCHOOL_CONFIG', 'SCHOOL_NIVEL_GRADO.ID_CONFIG', '=', 'SCHOOL_CONFIG.ID_CONFIG')
            ->leftJoin('SCHOOL_TIPODESCUENTO', 'SCHOOL_CRITERIO.ID_TIPODESCUENTO', '=', 'SCHOOL_TIPODESCUENTO.ID_TIPODESCUENTO')
            ->leftJoin('SCHOOL_TIPOPAGO', 'SCHOOL_CRITERIO.ID_TIPOPAGO', '=', 'SCHOOL_TIPOPAGO.ID_TIPOPAGO')
            ->select(
                'SCHOOL_CRITERIO.ID_CRITERIO',
                'SCHOOL_CRITERIO.ID_NGRADO',
                'SCHOOL_CRITERIO.ID_TIPODESCUENTO',
                'SCHOOL_CRITERIO.ID_TIPOPAGO',
                'SCHOOL_CRITERIO.DETALLE',
                'SCHOOL_CRITERIO.IMPORTE',
                'SCHOOL_CRITERIO.TIPO',
                DB::raw(
                    "
                    (SELECT NOMBRE FROM SCHOOL_NIVEL WHERE SCHOOL_NIVEL.ID_NIVEL = SCHOOL_CONFIG.ID_NIVEL) NOMBRE_NIVEL,
                    (SELECT NOMBRE FROM SCHOOL_GRADO WHERE SCHOOL_GRADO.ID_GRADO = SCHOOL_NIVEL_GRADO.ID_GRADO) NOMBRE_GRADO
                    "
                )
            )
            ->where('SCHOOL_CRITERIO.ID_PERIODO', $id_periodo)
            ->where('SCHOOL_NIVEL_GRADO.ID_CONFIG', $id_config)
            ->get();
        return $rows;
    }
    public static function showCriterions($id_criterio, $id_periodo)
    {
        $rows = DB::table('SCHOOL_CRITERIO')
            ->join('SCHOOL_NIVEL_GRADO', 'SCHOOL_CRITERIO.ID_NGRADO', '=', 'SCHOOL_NIVEL_GRADO.ID_NGRADO')
            ->join('SCHOOL_CONFIG', 'SCHOOL_NIVEL_GRADO.ID_CONFIG', '=', 'SCHOOL_CONFIG.ID_CONFIG')
            ->leftJoin('SCHOOL_TIPODESCUENTO', 'SCHOOL_CRITERIO.ID_TIPODESCUENTO', '=', 'SCHOOL_TIPODESCUENTO.ID_TIPODESCUENTO')
            ->leftJoin('SCHOOL_TIPOPAGO', 'SCHOOL_CRITERIO.ID_TIPOPAGO', '=', 'SCHOOL_TIPOPAGO.ID_TIPOPAGO')
            ->select(
                'SCHOOL_CRITERIO.ID_CRITERIO',
                'SCHOOL_CRITERIO.ID_NGRADO',
                'SCHOOL_CRITERIO.ID_TIPODESCUENTO',
                'SCHOOL_CRITERIO.ID_TIPOPAGO',
                'SCHOOL_CRITERIO.DETALLE',
                'SCHOOL_CRITERIO.IMPORTE',
                'SCHOOL_CRITERIO.TIPO',
                DB::raw(
                    "
                    (SELECT NOMBRE FROM SCHOOL_NIVEL WHERE SCHOOL_NIVEL.ID_NIVEL = SCHOOL_CONFIG.ID_NIVEL) NOMBRE_NIVEL,
                    (SELECT NOMBRE FROM SCHOOL_GRADO WHERE SCHOOL_GRADO.ID_GRADO = SCHOOL_NIVEL_GRADO.ID_GRADO) NOMBRE_GRADO
                    "
                )
            )
            ->where('SCHOOL_CRITERIO.ID_PERIODO', $id_periodo)
            ->where('SCHOOL_CRITERIO.ID_CRITERIO', $id_criterio)
            ->first();
        return $rows;
    }
    public static function addCriterions($data)
    {
        $id_vacante = self::getIdMax("SCHOOL_VACANTE","ID_VACANTE")+1;
        $data["id_vacante"] = $id_vacante;
        $result = DB::table('SCHOOL_VACANTE')->insert($data);
        if($result)
        {
            return self::showVacants($id_vacante, $data["id_periodo"]);
        }
        return $result;
    }
    public static function updateCriterions($data, $id_vacante, $id_periodo)
    {
        $result = DB::table('SCHOOL_VACANTE')
        ->where('ID_VACANTE', $id_vacante)
        ->update($data);
        if($result)
        {
            return self::showVacants($id_vacante, $id_periodo);
        }
        return $result;
    }
}

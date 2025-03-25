<?php
namespace App\Http\Data\Schools;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Schools\Util\SchoolsUtil;
use PDO;

class SchoolsData
{
    private static $id_persona;
    private static $id_direccion;
    private static $id_virtual;
    private static $id_pdetalle;
    private static $id_reserva;
    private static $id_hcita;
    private static $id_cita;
    private static $id_acuerdo;
    private static $id_telefono;
    private static $numero;
    private static $id_responsable;
    public function __construct()
    {
    }
    public static function getSysdate()
    {
        return DB::connection('jose')->raw('SYSDATE');
    }
    public static function getIdMax($conexion,$tabla,$campo)
    {
        $valor = $conexion->table($tabla)->max($campo);
        return $valor;
    }
    public static function generaCodigoTexto($conexion,$tabla,$campo){ // 001
        $codigo_secundario="";
        $contador = 0;
        $contador = $conexion->table($tabla)->max($campo)+1;
        if($contador < 10){
            $codigo_secundario = "00".$contador;
        }else if($contador < 100){
            $codigo_secundario = "0".$contador;  
        }
        return $codigo_secundario;
    }
    public static function getIdPersona()
    {
        return static::$id_persona;
    }
    public static function getIdDireccion()
    {
        return static::$id_direccion;
    }
    public static function getIdVirtual()
    {
        return static::$id_virtual;
    }
    public static function getIdPdetalle()
    {
        return static::$id_pdetalle;
    }
    public static function getIdReserva()
    {
        return static::$id_reserva;
    }
    public static function getIdHcita()
    {
        return static::$id_hcita;
    }
    public static function getIdCita()
    {
        return static::$id_cita;
    }
    public static function getIdAcuerdo()
    {
        return static::$id_acuerdo;
    }
    public static function getIdTelefono()
    {
        return static::$id_telefono;
    }
    public static function getNumero()
    {
        return static::$numero;
    }
    public static function getIdResponsable()
    {
        return static::$id_responsable;
    }
    public static function listPeriodsCheck($data)
    {
        $saldo = 0;
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_SHOW_VERIFICACION (
            :P_ID_ALUMNO,
            :P_ID_PERIODO,
            :P_SALDO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_ALUMNO', $data['id_alumno'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERIODO', $data['id_periodo'], PDO::PARAM_INT);
        $stmt->bindParam(':P_SALDO', $saldo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "saldo" => $saldo,
            "error" => $error,
            "message" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function listPeriodsPO($plan_confirmado)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_PERIODO_DE_PO (
            :P_PLAN_CONFIRMADO,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_PLAN_CONFIRMADO', $plan_confirmado, PDO::PARAM_STR);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listPeriodsOfEnrollments()
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_PERIODO_DE_MATRICULA (
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listPeriodsAreasMissing($id_periodo, $nombre)
    {
        $rows = DB::connection('jose')->table('SCHOOL_CURSO')
        ->leftJoin('SCHOOL_PERIODO_CURSO', function($join) use($id_periodo) {
            $join->on('SCHOOL_CURSO.ID_CURSO', '=', 'SCHOOL_PERIODO_CURSO.ID_CURSO')
            ->where('SCHOOL_PERIODO_CURSO.ID_PERIODO', $id_periodo);
        })
        ->select(
            'SCHOOL_PERIODO_CURSO.ID_PCURSO',
            'SCHOOL_PERIODO_CURSO.ID_PERIODO',
            'SCHOOL_CURSO.ID_CURSO',
            'SCHOOL_CURSO.NOMBRE',
            'SCHOOL_CURSO.ABREVIATURA',
            DB::raw(
                "
                (CASE WHEN UPPER(SCHOOL_CURSO.NOMBRE) = UPPER('$nombre') THEN 'SI' ELSE 'NO' END) AS EQUAL
                "
            )
        )
        ->whereNull('SCHOOL_PERIODO_CURSO.ID_PCURSO')
        ->whereRaw("UPPER(SCHOOL_CURSO.NOMBRE) LIKE UPPER('%".($nombre)."%')")
        ->get();
        return $rows;
    }
    public static function listAreas($nombre, $nombre_eq)
    {
        $rows = DB::connection('jose')->table('SCHOOL_CURSO')
        ->select(
            'SCHOOL_CURSO.ID_CURSO',
            'SCHOOL_CURSO.NOMBRE',
            'SCHOOL_CURSO.ABREVIATURA'
        );
        if($nombre)
        {
            $rows->whereRaw("UPPER(SCHOOL_CURSO.NOMBRE) LIKE UPPER('%".($nombre)."%')");
        }
        if($nombre_eq)
        {
            $rows->whereRaw("UPPER(SCHOOL_CURSO.NOMBRE) = UPPER('".($nombre_eq)."')");
        }
        return $rows->get();
    }
    public static function addAreas($data)
    {
        $id_curso = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_CURSO (
            :P_NOMBRE,
            :P_ABREVIATURA,
            :P_ID_CURSO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NOMBRE', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ABREVIATURA', $data['abreviatura'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_CURSO', $id_curso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_curso" => $id_curso,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function updateAreas($data, $id_curso)
    {
        $result = DB::connection('jose')->table('SCHOOL_CURSO')
        ->where('id_curso', $id_curso)
        ->update($data);
        return $result;
    }
    public static function listMyPeriodsStages($id_user, $id_periodo, $id_entidad, $id_depto)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_MY_PERIODO_NIVEL (
            :P_ID_USER,
            :P_ID_PERIODO,
            :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERIODO', $id_periodo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listMyPeriodsStagesGrades($id_pnivel)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_MY_PERIODO_NIVEL_GRADO (
            :P_ID_PNIVEL,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNIVEL', $id_pnivel, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listMyPeriodsStagesGradesSections($id_pngrado)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_MY_PER_NIV_GRA_SECCION (
            :P_ID_PNGRADO,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGRADO', $id_pngrado, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listMyPeriodsStagesGradesAreas($id_pngrado, $id_pngseccion)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_MY_PER_NIV_GRA_CURSO (
            :P_ID_PNGRADO,
            :P_ID_PNGSECCION,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGRADO', $id_pngrado, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGSECCION', $id_pngseccion, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function addPeriodsStagesGradesAreas($data)
    {
        $id_pngcurso = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_CREATE_PERIODO_NG_CURSO (
            :P_NOMBRE,
            :P_ID_PNGRADO,
            :P_PARENT,
            :P_ID_USER,
            :P_ID_PNGCURSO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NOMBRE', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PNGRADO', $data['id_pngrado'], PDO::PARAM_INT);
        $stmt->bindParam(':P_PARENT', $data['parent'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_USER', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGCURSO', $id_pngcurso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "id_pngcurso" => $id_pngcurso,
            "error" => $error,
            "message" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function patchPeriodsStagesGradesAreasNroCupo($data)
    {
        $id_pngcurso = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_EDIT_PERIODO_NG_CURSO_NC (
            :P_NRO_CUPO,
            :P_ID_PNGCURSO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NRO_CUPO', $data['nro_cupo'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGCURSO', $data['id_pngcurso'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "message" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function patchPeriodsStagesGradesSectionsSave($data)
    {
        $id_periodo = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_SAVE_SECCION_IN_PNGS (
            :P_ID_PNGSECCION,
            :P_NOMBRE,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGSECCION', $data['id_pngseccion'], PDO::PARAM_INT);
        $stmt->bindParam(':P_NOMBRE', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function listPeriodsStudentsAreasBoth($id_reserva)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_PAC_BY_RESERVA (
            :P_ID_RESERVA,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_RESERVA', $id_reserva, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function addPeriodsStudentsAreas($data)
    {
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_SAVE_PERIODO_ALUM_CURSO (
            :P_ID_PNGCURSO,
            :P_ID_ALUMNO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGCURSO', $data['id_pngcurso'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ALUMNO', $data['id_alumno'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "message" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function listPmesAreasTeachers($id_pngseccion, $id_pngcurso)
    {
        $result = DB::connection('jose')->table('SCHOOL_PMES_CURSO_PROFESOR')
        ->join('SCHOOL_PERIODOMES', 'SCHOOL_PMES_CURSO_PROFESOR.ID_PMES', '=', 'SCHOOL_PERIODOMES.ID_PMES')
        ->select(
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PMCPROFESOR',
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PMES',
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PNGCURSO',
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PNGSECCION',
            'SCHOOL_PMES_CURSO_PROFESOR.TIPO',
            'SCHOOL_PMES_CURSO_PROFESOR.ID_INSTITUCION',
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PROFESOR',
            'SCHOOL_PERIODOMES.NOMBRE AS NOMBRE_PMES'
        )
        ->where('ID_PNGSECCION', $id_pngseccion)
        ->where('ID_PNGCURSO', $id_pngcurso)
        ->get();
        return $result;
    }
    public static function listUnits($id_pngcurso, $id_pmes, $parent)
    {
        $result = DB::connection('jose')->table('SCHOOL_UNIDAD')
        ->join('SCHOOL_PERIODOMES', 'SCHOOL_UNIDAD.ID_PMES', '=', 'SCHOOL_PERIODOMES.ID_PMES')
        ->select(
            'SCHOOL_UNIDAD.ID_UNIDAD',
            'SCHOOL_UNIDAD.ID_PNGCURSO',
            'SCHOOL_UNIDAD.FECHA_INI',
            'SCHOOL_UNIDAD.FECHA_FIN',
            'SCHOOL_UNIDAD.TITULO',
            'SCHOOL_UNIDAD.ID_PMES'
        )
        ->where('ID_PNGCURSO', $id_pngcurso);
        if($id_pmes)
        {
            $result->where('SCHOOL_UNIDAD.ID_PMES', $id_pmes);
        }
        if($parent)
        {
            $result->where('SCHOOL_PERIODOMES.PARENT', $parent);
        }
        return $result->get();
    }
    public static function listSessions($id_unidad)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION')
        ->select(
            'ID_SESION',
            'ID_UNIDAD',
            'FECHA',
            'HORA_INI',
            'HORA_FIN',
            'TITULO'
        )
        ->where('ID_UNIDAD', $id_unidad)
        ->orderBy('FECHA', 'ASC')
        ->orderBy('HORA_INI', 'ASC')
        ->get();
        return $result;
    }
    public static function addSessions($data)
    {
        $result = false;
        $id_sesion = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_SESION (
            :P_ID_UNIDAD,
            :P_FECHA,
            :P_HORA_INI,
            :P_HORA_FIN,
            :P_TITULO,
            :P_TEMA,
            :P_ID_USER,
            :P_ID_SESION,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_UNIDAD', $data['id_unidad'], PDO::PARAM_INT);
        $stmt->bindParam(':P_FECHA', $data['fecha'], PDO::PARAM_STR);
        $stmt->bindParam(':P_HORA_INI',$data['hora_ini'], PDO::PARAM_STR);
        $stmt->bindParam(':P_HORA_FIN',$data['hora_fin'], PDO::PARAM_STR);
        $stmt->bindParam(':P_TITULO',$data['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':P_TEMA',$data['tema'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER',$data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_SESION', $id_sesion, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_sesion" => $id_sesion,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function listSessionsItems($id_sesion)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_ITEM')
        ->select(
            'ID_SITEM',
            'DESCRIPCION',
            'PARENT',
            'TIPO',
            'ID_SESION'
        )
        ->where('ID_SESION', $id_sesion)
        ->orderBy('ID_SITEM', 'ASC')
        ->get();
        return $result;
    }
    public static function addSessionsItems($data)
    {
        $result = false;
        $id_sitem = 0;
        $tipo = '2';
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_SESION_ITEM (
            :P_DESCRIPCION,
            :P_PARENT,
            :P_TIPO,
            :P_ID_SESION,
            :P_ID_USER,
            :P_ID_SITEM,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_DESCRIPCION', $data['descripcion'], PDO::PARAM_STR);
        $stmt->bindParam(':P_PARENT', $data['parent'], PDO::PARAM_INT);
        $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_SESION',$data['id_sesion'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_USER',$data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_SITEM', $id_sitem, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_sitem" => $id_sitem,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function updateSessionsItems($data, $id_sitem)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_ITEM')
        ->where('ID_SITEM', $id_sitem)
        ->update($data);
        return $result;
    }
    public static function deleteSessionsItems($id_sitem)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_ITEM')
        ->where('ID_SITEM', '=', $id_sitem)
        ->delete();
        return $result;
    }
    public static function listSessionsInstruments($id_sesion)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_INSTRUMENTO')
        ->join('SCHOOL_INSTRUMENTO', 'SCHOOL_SESION_INSTRUMENTO.ID_INSTRUMENTO', '=', 'SCHOOL_INSTRUMENTO.ID_INSTRUMENTO')
        ->select(
            'SCHOOL_SESION_INSTRUMENTO.ID_INSTRUMENTO',
            'SCHOOL_SESION_INSTRUMENTO.ID_SESION',
            'SCHOOL_INSTRUMENTO.NOMBRE AS NOMBRE_INSTRUMENTO'
        )
        ->where('SCHOOL_SESION_INSTRUMENTO.ID_SESION', $id_sesion)
        ->get();
        return $result;
    }
    public static function listSessionsInstrumentsMissing($id_sesion)
    {
        $result = DB::connection('jose')->table('SCHOOL_INSTRUMENTO')
        ->leftJoin('SCHOOL_SESION_INSTRUMENTO', function($join) use($id_sesion) {
            $join->on('SCHOOL_SESION_INSTRUMENTO.ID_INSTRUMENTO', '=', 'SCHOOL_INSTRUMENTO.ID_INSTRUMENTO')
            ->where('ID_SESION', $id_sesion);
        })
        ->select(
            'SCHOOL_INSTRUMENTO.ID_INSTRUMENTO',
            'SCHOOL_SESION_INSTRUMENTO.ID_SESION',
            'SCHOOL_INSTRUMENTO.NOMBRE AS NOMBRE_INSTRUMENTO'
        )
        ->whereNull('SCHOOL_SESION_INSTRUMENTO.ID_SESION')
        ->get();
        return $result;
    }
    public static function addSessionsInstruments($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_INSTRUMENTO')->insert($data);
        return $result;
    }
    public static function deleteSessionsInstruments($id_sesion, $id_instrumento)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_INSTRUMENTO')
        ->where('ID_SESION', '=', $id_sesion)
        ->where('ID_INSTRUMENTO', '=', $id_instrumento)
        ->delete();
        return $result;
    }
    public static function listSessionsCriteria($id_sesion)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_CRITERIO')
        ->join('SCHOOL_UNIDAD_CRITERIO', 'SCHOOL_SESION_CRITERIO.ID_UCRITERIO', '=', 'SCHOOL_UNIDAD_CRITERIO.ID_UCRITERIO')
        ->select(
            'SCHOOL_UNIDAD_CRITERIO.ID_UCRITERIO',
            'SCHOOL_SESION_CRITERIO.ID_SESION',
            'SCHOOL_UNIDAD_CRITERIO.DESCRIPCION'
        )
        ->where('SCHOOL_SESION_CRITERIO.ID_SESION', $id_sesion)
        ->get();
        return $result;
    }
    public static function listSessionsCriteriaMissing($id_sesion)
    {
        $result = DB::connection('jose')->table('SCHOOL_UNIDAD_CRITERIO')
        ->join('SCHOOL_UNIDAD_APRENDIZAJE_PREC', 'SCHOOL_UNIDAD_CRITERIO.ID_UAPRECISADO', '=', 'SCHOOL_UNIDAD_APRENDIZAJE_PREC.ID_UAPRECISADO')
        ->join('SCHOOL_UNIDAD_APRENDIZAJE', 'SCHOOL_UNIDAD_APRENDIZAJE_PREC.ID_UAPRENDIZAJE', '=', 'SCHOOL_UNIDAD_APRENDIZAJE.ID_UAPRENDIZAJE')
        ->join('SCHOOL_UNIDAD_CAPACIDAD', 'SCHOOL_UNIDAD_APRENDIZAJE.ID_UCAPACIDAD', '=', 'SCHOOL_UNIDAD_CAPACIDAD.ID_UCAPACIDAD')
        ->join('SCHOOL_UNIDAD_COMPETENCIA', 'SCHOOL_UNIDAD_CAPACIDAD.ID_UCOMPETENCIA', '=', 'SCHOOL_UNIDAD_COMPETENCIA.ID_UCOMPETENCIA')
        ->join('SCHOOL_SESION', function($join) use($id_sesion) {
            $join->on('SCHOOL_UNIDAD_COMPETENCIA.ID_UNIDAD', '=', 'SCHOOL_SESION.ID_UNIDAD')
            ->where('SCHOOL_SESION.ID_SESION', '=', $id_sesion);
        })
        ->leftJoin('SCHOOL_SESION_CRITERIO', function($join) use($id_sesion) {
            $join->on('SCHOOL_SESION_CRITERIO.ID_UCRITERIO', '=', 'SCHOOL_UNIDAD_CRITERIO.ID_UCRITERIO')
            ->where('SCHOOL_SESION_CRITERIO.ID_SESION', $id_sesion);
        })
        ->select(
            'SCHOOL_UNIDAD_CRITERIO.ID_UCRITERIO',
            'SCHOOL_SESION_CRITERIO.ID_SESION',
            'SCHOOL_UNIDAD_CRITERIO.DESCRIPCION'
        )
        ->whereNull('SCHOOL_SESION_CRITERIO.ID_SESION')
        ->get();
        return $result;
    }
    public static function addSessionsCriteria($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_CRITERIO')->insert($data);
        return $result;
    }
    public static function deleteSessionsCriteria($id_sesion, $id_ucriterio)
    {
        $result = DB::connection('jose')->table('SCHOOL_SESION_CRITERIO')
        ->where('ID_SESION', '=', $id_sesion)
        ->where('ID_UCRITERIO', '=', $id_ucriterio)
        ->delete();
        return $result;
    }
    public static function listUnitsCompetencysEvaluations($id_unidad)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_UNIDAD_COMPET_EVAL (
            :P_ID_UNIDAD,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_UNIDAD', $id_unidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listUnitsCompetencysByEvalsPeriods($id_pngseccion, $id_pngcurso, $id_pmes)
    {
        $list_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_UNIDAD_COMPET_BY_EVALP (
            :P_ID_PNGSECCION,
            :P_ID_PNGCURSO,
            :P_ID_PMES,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGSECCION', $id_pngseccion, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGCURSO', $id_pngcurso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PMES', $id_pmes, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return $result;
    }
    public static function listUnitsLearningsPrecisesEvaluations($id_ucompetencia, $id_pngseccion)
    {
        $list_cursor = [];
        $list_alumno_cursor = [];
        $list_aprecisado_cursor = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_UNIDAD_APREN_PREC_EVAL (
            :P_ID_UCOMPETENCIA,
            :P_ID_PNGSECCION,
            :P_LIST_CURSOR,
            :P_LIST_ALUMNO_CURSOR,
            :P_LIST_APRECISADO_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_UCOMPETENCIA', $id_ucompetencia, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGSECCION', $id_pngseccion, PDO::PARAM_INT);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->bindParam(':P_LIST_ALUMNO_CURSOR', $list_alumno_cursor, PDO::PARAM_STMT);
        $stmt->bindParam(':P_LIST_APRECISADO_CURSOR', $list_aprecisado_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $lista, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($lista as $key => $value)
        {
            $lista[$key] = array_change_key_case($value, CASE_LOWER);
        }

        oci_execute($list_alumno_cursor, OCI_DEFAULT);
        oci_fetch_all($list_alumno_cursor, $alumnos, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_alumno_cursor);
        foreach($alumnos as $key => $value)
        {
            $alumnos[$key] = array_change_key_case($value, CASE_LOWER);
        }

        oci_execute($list_aprecisado_cursor, OCI_DEFAULT);
        oci_fetch_all($list_aprecisado_cursor, $aprecisados, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_aprecisado_cursor);
        foreach($aprecisados as $key => $value)
        {
            $aprecisados[$key] = array_change_key_case($value, CASE_LOWER);
        }
        $result = [
            'lista'=> $lista,
            'alumnos'=> $alumnos,
            'aprecisados'=> $aprecisados
        ];
        return $result;
    }
    public static function addUnitsLearningsPrecisesEvaluations($data)
    {
        $result = false;
        $id_eunidad = 0;
        $evaluado = '';
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_EVAL_UNIDAD (
            :P_NOTA,
            :P_COMENTARIO,
            :P_ID_UAPRECISADO,
            :P_TIPO,
            :P_ID_UCOMPETENCIA,
            :P_ID_ALUMNO,
            :P_ID_USER,
            :P_ID_EUNIDAD,
            :P_EVALUADO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NOTA', $data['nota'], PDO::PARAM_STR);
        $stmt->bindParam(':P_COMENTARIO', $data['comentario'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_UAPRECISADO',$data['id_uaprecisado'], PDO::PARAM_INT);
        $stmt->bindParam(':P_TIPO', $data['tipo'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_UCOMPETENCIA', $data['id_ucompetencia'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ALUMNO', $data['id_alumno'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_USER', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_EUNIDAD', $id_eunidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_EVALUADO', $evaluado, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_eunidad" => $id_eunidad,
            "evaluado" => $evaluado,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function updateUnitsLearningsPrecisesEvaluations($data)
    {
        $result = false;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_UPDATE_EVAL_UNIDAD (
            :P_NOTA,
            :P_COMENTARIO,
            :P_ID_EUNIDAD,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NOTA', $data['nota'], PDO::PARAM_STR);
        $stmt->bindParam(':P_COMENTARIO', $data['comentario'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_EUNIDAD', $data['id_eunidad'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function addFile($data)
    {
        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_FILE", "ID_FILE") + 1;
        $data["id_file"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_FILE')->insert($data);
        if($result) return $id;
        else return false;
    }
    public static function uploadFile($data)
    {
        $result = false;
        $id_file = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_UPDATE_EVAL_UNIDAD (
            :P_NOMBRE,
            :P_FORMATO,
            :P_TAMANHO,
            :P_RUTA,
            :P_ID_USER,
            :P_ID_FILE,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NOMBRE', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':P_FORMATO', $data['formato'], PDO::PARAM_STR);
        $stmt->bindParam(':P_TAMANHO', $data['tamanho'], PDO::PARAM_STR);
        $stmt->bindParam(':P_RUTA', $data['ruta'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER', $data['id_user'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_FILE', $id_file, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_file" => $id_file,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function getIdInstitucionByEntyDepto($id_entidad, $id_depto)
    {
        $one = DB::connection('jose')->table('SCHOOL_INSTITUCION')
        ->select(
            'SCHOOL_INSTITUCION.ID_INSTITUCION'
        )
        ->where('SCHOOL_INSTITUCION.ID_CAMPO', $id_entidad)
        ->where('SCHOOL_INSTITUCION.ID_DEPTO', $id_depto)
        ->first();
        return $one;
    }

    public static function getPeriodPostulant()
    {
        $one = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->select(
            DB::raw(
                "
                ID_PERIODO
                "
            )
        )
        ->where('ESTADO', 'O')
        ->first();
        return $one;
    }
    public static function listReservations($id_persona)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_RESERVA')
        ->join('JOSE.SCHOOL_PERIODO', 'SCHOOL_RESERVA.ID_PERIODO', '=', 'SCHOOL_PERIODO.ID_PERIODO')
        ->join('MOISES.PERSONA', 'SCHOOL_RESERVA.ID_ALUMNO', '=', 'PERSONA.ID_PERSONA')
        ->select(
            'SCHOOL_RESERVA.ID_RESERVA',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO',
            DB::raw(
                "
                (
                    SELECT NUM_DOCUMENTO
                    FROM MOISES.PERSONA_DOCUMENTO
                    WHERE PERSONA_DOCUMENTO.ID_PERSONA = PERSONA.ID_PERSONA
                ) NUM_DOCUMENTO,
                (
                    SELECT FEC_NACIMIENTO
                    FROM MOISES.PERSONA_NATURAL
                    WHERE PERSONA_NATURAL.ID_PERSONA = PERSONA.ID_PERSONA
                ) FEC_NACIMIENTO
                "
            )
        )
        ->where('SCHOOL_PERIODO.ESTADO', '2')
        ->where('SCHOOL_RESERVA.ID_PERSONA', $id_persona)
        ->orderBy('SCHOOL_RESERVA.ID_RESERVA', 'DESC')
        ->get();
        return $rows;
    }
    public static function listReservationsMyStudents($id_persona, $id_periodo)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_ALUMNO')
        ->join('JOSE.SCHOOL_PERSONA_PARENTESCO', function($join) use($id_persona) {
            $join->on('SCHOOL_ALUMNO.ID_ALUMNO', '=', 'SCHOOL_PERSONA_PARENTESCO.ID_PERSONA')
            ->where('PARENT', '=', $id_persona);
        })
        ->leftJoin('JOSE.SCHOOL_RESERVA', function($join) use($id_periodo) {
            $join->on('SCHOOL_ALUMNO.ID_ALUMNO', '=', 'SCHOOL_RESERVA.ID_ALUMNO')
            ->where('ID_PERIODO', '=', $id_periodo);
        })
        ->select(
            DB::raw(
                "
                NVL(SCHOOL_RESERVA.ID_RESERVA, 0) ID_RESERVA,
                $id_periodo ID_PERIODO,
                SCHOOL_ALUMNO.ID_GRADO,
                SCHOOL_ALUMNO.ID_NIVEL,
                SCHOOL_ALUMNO.ID_ALUMNO,
                $id_persona ID_PERSONA,
                (
                    SELECT SCHOOL_GRADO.NOMBRE
                    FROM JOSE.SCHOOL_GRADO
                    WHERE SCHOOL_ALUMNO.ID_GRADO = SCHOOL_GRADO.ID_GRADO
                ) NOMBRE_GRADO,
                (
                    SELECT SCHOOL_NIVEL.NOMBRE
                    FROM JOSE.SCHOOL_NIVEL
                    WHERE SCHOOL_ALUMNO.ID_NIVEL = SCHOOL_NIVEL.ID_NIVEL
                ) NOMBRE_NIVEL,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE SCHOOL_ALUMNO.ID_ALUMNO = PERSONA.ID_PERSONA
                ) NOMBRE_ALUMNO,
                NVL(SCHOOL_RESERVA.ESTADO, '0') ESTADO_RESERVA
                "
            )
        )
        ->get();
        return $rows;
    }
    public static function listReservationsStudentsBoth($id_entidad, $id_depto, $texto)
    {
        $list_cursor = [];
        $estado = '0000000000';
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_RESERVA_ALUMNO_BOTH (
            :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_TEXTO,
            :P_ESTADO,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_TEXTO', $texto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ESTADO', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return ['reservas' => $result, 'estado' => $estado];
    }
    public static function listReservationsStudentsParent($id_entidad, $id_depto, $texto, $id_user)
    {
        $list_cursor = [];
        $estado = '0000000000';
        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_LIST_RESERVA_ALUMNO_PARENT (
            :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_TEXTO,
            :P_ID_USER,
            :P_ESTADO,
            :P_LIST_CURSOR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_TEXTO', $texto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_STR);
        $stmt->bindParam(':P_ESTADO', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':P_LIST_CURSOR', $list_cursor, PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($list_cursor, OCI_DEFAULT);
        oci_fetch_all($list_cursor, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC );
        oci_free_cursor($list_cursor);
        foreach($result as $key => $value)
        {
            $result[$key] = array_change_key_case($value, CASE_LOWER);
        }
        return ['reservas' => $result, 'estado' => $estado];
    }
    public static function showReservations($id_reserva)
    {
        $row = DB::connection('eliseo')->table('JOSE.SCHOOL_RESERVA')
        ->join('JOSE.SCHOOL_PERIODO', 'SCHOOL_RESERVA.ID_PERIODO', '=', 'SCHOOL_PERIODO.ID_PERIODO')
        ->join('MOISES.PERSONA', 'SCHOOL_RESERVA.ID_ALUMNO', '=', 'PERSONA.ID_PERSONA')
        ->select(
            'SCHOOL_RESERVA.ID_RESERVA',
            'SCHOOL_RESERVA.ID_ALUMNO',
            'SCHOOL_RESERVA.ID_PNGRADO',
            'SCHOOL_RESERVA.ID_PERIODO',
            'SCHOOL_RESERVA.ID_PERSONA'
        )
        ->where('SCHOOL_RESERVA.ID_RESERVA', $id_reserva)
        ->first();
        return $row;
    }
    public static function showReservations2($id_reserva)
    {
        $row = DB::connection('eliseo')->table('JOSE.SCHOOL_RESERVA')
        ->join('JOSE.SCHOOL_PERIODO', 'SCHOOL_RESERVA.ID_PERIODO', '=', 'SCHOOL_PERIODO.ID_PERIODO')
        ->join('JOSE.SCHOOL_ALUMNO', 'SCHOOL_RESERVA.ID_ALUMNO', '=', 'SCHOOL_ALUMNO.ID_ALUMNO')
        ->join('MOISES.PERSONA', 'SCHOOL_RESERVA.ID_ALUMNO', '=', 'PERSONA.ID_PERSONA')
        ->select(
            'SCHOOL_RESERVA.ID_RESERVA',
            'SCHOOL_RESERVA.ID_ALUMNO',
            'SCHOOL_RESERVA.ID_PNGRADO',
            'SCHOOL_RESERVA.ID_PERSONA',
            'SCHOOL_RESERVA.CASO_MOVILIDAD',
            'SCHOOL_RESERVA.AUTORIZA_FOTO',
            'SCHOOL_PERIODO.ID_PERIODO',
            'SCHOOL_PERIODO.ANHO_PERIODO',
            'SCHOOL_ALUMNO.ID_GRADO',
            'SCHOOL_ALUMNO.ID_NIVEL',
            DB::raw(
                "
                (
                SELECT SCHOOL_GRADO.NOMBRE
                FROM JOSE.SCHOOL_GRADO
                WHERE SCHOOL_ALUMNO.ID_GRADO = SCHOOL_GRADO.ID_GRADO
                ) NOMBRE_GRADO,
                (
                SELECT SCHOOL_NIVEL.NOMBRE
                FROM JOSE.SCHOOL_NIVEL
                WHERE SCHOOL_ALUMNO.ID_NIVEL = SCHOOL_NIVEL.ID_NIVEL
                ) NOMBRE_NIVEL,
                (
                SELECT SCHOOL_FICHA_MEDICA.ID_FMEDICA
                FROM JOSE.SCHOOL_FICHA_MEDICA
                WHERE SCHOOL_FICHA_MEDICA.ID_ALUMNO = SCHOOL_ALUMNO.ID_ALUMNO AND ROWNUM <= 1
                ) ID_FMEDICA,
                PERSONA.NOMBRE||' '||PERSONA.paterno||' '||PERSONA.materno as NOMBRE_ALUMNO,
                SCHOOL_RESERVA.ESTADO ESTADO_RESERVA,
                NULL NOMBRE
                "
            )
        )
        ->where('SCHOOL_RESERVA.ID_RESERVA', $id_reserva)
        ->first();
        return $row;
    }
    public static function addReservations($data)
    {
        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_RESERVA", "ID_RESERVA")+1;
        $data["id_reserva"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_RESERVA')->insert($data);
        if($result) return $id;
        else return false;
    }
    public static function updateReservations($data, $id_reserva)
    {
        $result = DB::connection('jose')->table('SCHOOL_RESERVA')
        ->where('ID_RESERVA', $id_reserva)
        ->update($data);
        return $result;
    }
    public static function patchReservationsPasoNext($id_reserva)
    {
        $estado = '000';
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_SAVE_RESERVA_PASO_NEXT (
            :P_ID_RESERVA,
            :P_ESTADO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_RESERVA', $id_reserva, PDO::PARAM_INT);
        $stmt->bindParam(':P_ESTADO', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "estado" => $estado,
            "error" => $error,
            "message" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function addMatricula($data)
    {
        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_MATRICULA", "ID_MATRICULA")+1;
        $data["id_matricula"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_MATRICULA')->insert($data);
        if($result) return $id;
        else return false;
    }
    public static function listHospitalEssalud()
    {
        $rows = DB::connection('jose')->table('SCHOOL_HOSPITAL_ESSALUD')
        ->select(
        )
        ->get();
        return $rows;
    }
    public static function listAllergys()
    {
        $rows = DB::connection('jose')->table('SCHOOL_ALERGIA')
        ->select(
        )
        ->get();
        return $rows;
    }
    public static function listPersonsParentesco($id_persona, $opc)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA');
        if($opc == 'P')
        {
            $rows->join('JOSE.SCHOOL_PERSONA_PARENTESCO', function($join) use($id_persona) {
                $join->on('PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_PARENTESCO.ID_PERSONA')
                ->where('PARENT', '=', $id_persona);
            });
        }
        if($opc == 'H')
        {
            $rows->join('JOSE.SCHOOL_PERSONA_PARENTESCO', function($join) use($id_persona) {
                $join->on('PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_PARENTESCO.PARENT')
                ->where('SCHOOL_PERSONA_PARENTESCO.ID_PERSONA', '=', $id_persona);
            });
        }
        $rows->select(
            DB::raw(
                "
                PERSONA.ID_PERSONA ID_ALUMNO,
                NOMBRE || ' ' || PATERNO || ' ' || MATERNO NOMBREC
                "
            )
        );
        return $rows->get();
    }
    public static function deleteMeets($id_cita)
    {
        $result = DB::connection('jose')->table('SCHOOL_CITA')
            ->where('ID_CITA', '=', $id_cita)
            ->delete();
        return $result;
    }
    // ***** HOY UP
    public static function listEmployeesSearch($text)
    {
        $rows =  DB::connection('moises')->table('PERSONA_NATURAL')
        ->join('PERSONA', 'PERSONA_NATURAL.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
        ->join('PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->leftJoin('TIPO_DOCUMENTO', 'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO', '=', 'TIPO_DOCUMENTO.ID_TIPODOCUMENTO')
        ->select(
            'PERSONA.ID_PERSONA',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO',
            'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
            'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
            'PERSONA_NATURAL.ID_TIPOTRATAMIENTO',
            'PERSONA_NATURAL.ID_TIPOESTADOCIVIL',
            'PERSONA_NATURAL.SEXO',
            'PERSONA_NATURAL.FEC_NACIMIENTO',
            DB::RAW(
                'TIPO_DOCUMENTO.NOMBRE NOMBRE_TIPODOCUMENTO'
            )
        )
        ->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO', '=', $text)
        ->first();
        return $first;
    }

    public static function addPersons($data)
    {
        /*$result = false;
        $idpersona = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_PERSONA_SAVE (
            :P_NOMBRE,
            :P_PATERNO,
            :P_MATERNO,
            :P_ID_PERSONA,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_NOMBRE', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':P_PATERNO', $data['paterno'], PDO::PARAM_STR);
        $stmt->bindParam(':P_MATERNO',$data['materno'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PERSONA', $idpersona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "id_persona" => $idpersona,
            "msg_error" => $msg_error,
            "msg_error" => $msg_error
        ];*/

        $id = self::getIdMax(DB::connection('eliseo'),"MOISES.PERSONA","ID_PERSONA")+1;
        $data['id_persona']=$id;
        $result = DB::connection('moises')->table('PERSONA')->insert($data);
        static::$id_persona = $id;
        return $result;
    }
    public static function addPersonsDocument($data)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_DOCUMENTO')->insert($data);
        return $result;
    }
    public static function addPersonsNatural($data)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL')->insert($data);
        return $result;
    }
    public static function existPersonsVirtualByEmail($email)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_VIRTUAL')
        ->where('PERSONA_VIRTUAL.DIRECCION',$email)
        ->exists();
  
        return $result;
    }
    public static function addPersonsVirtual($data)
    {
        $id = self::getIdMax(DB::connection('eliseo'),"MOISES.PERSONA_VIRTUAL","ID_VIRTUAL")+1;
        $data["id_virtual"] = $id;
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_VIRTUAL')->insert($data);
        static::$id_virtual = $id;
        return $result;
    }
    public static function updatePersonsVirtual($data,$id_persona)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_VIRTUAL')
        ->where('PERSONA_VIRTUAL.ID_VIRTUAL',$data["id_virtual"])
        ->where('PERSONA_VIRTUAL.ID_PERSONA',$id_persona)
        ->update($data);
        return $result;
    }
    public static function deletePersonsVirtual($id_persona)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_VIRTUAL')
        ->where('PERSONA_VIRTUAL.ID_PERSONA',$id_persona)
        ->delete();
        return $result;
    }
    public static function updatePersons($data, $id_persona)
    {
        $result =DB::connection('moises')->table('PERSONA')
        ->where('ID_PERSONA', $id_persona)
        ->update($data);
        return $result;
    }
    public static function updatePersonsNatural($data, $id_persona)
    {
        $result =DB::connection('moises')->table('PERSONA_NATURAL')
        ->where('ID_PERSONA', $id_persona)
        ->update($data);
        return $result;
    }
    //DATOS DE PERSONA NADA DE SCHOOL
    public static function showPersonsManagerNotSchool($id_persona)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL')
        ->join('MOISES.PERSONA', 'PERSONA_NATURAL.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
        ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->leftJoin('MOISES.PERSONA_DIRECCION', function($join) { $join->on('PERSONA.ID_PERSONA', '=', 'PERSONA_DIRECCION.ID_PERSONA')->where('PERSONA_DIRECCION.ES_ACTIVO','=',1); })
        ->select(
            'PERSONA.ID_PERSONA',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO',
            'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
            'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
            'PERSONA_NATURAL.ID_TIPOTRATAMIENTO',
            'PERSONA_NATURAL.ID_TIPOESTADOCIVIL',
            'PERSONA_NATURAL.SEXO',
            'PERSONA_NATURAL.VIVE',
            'PERSONA_NATURAL.ID_TIPOPAIS',
            'PERSONA_NATURAL.ID_NACIONALIDAD',
            'PERSONA_NATURAL.ID_DEPARTAMENTO',
            'PERSONA_NATURAL.ID_PROVINCIA',
            'PERSONA_NATURAL.ID_DISTRITO',
            'PERSONA_DIRECCION.DIRECCION',
            DB::RAW(
                " '' AS NUMERO"
            ),
            DB::RAW(
                "to_char(PERSONA_NATURAL.FEC_NACIMIENTO,'YYYY-MM-DD') AS FEC_NACIMIENTO"
            )
        )
        ->where('PERSONA.ID_PERSONA', $id_persona)
        ->first();
        return $rows;
    }
     //DATOS DE PERSONA CON DATOS DE SCHOOL
    public static function showPersonsManager($id_persona,$tipo_parentesco)
    {
        if($tipo_parentesco=='03'){
            $rows = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL')
            ->join('MOISES.PERSONA', 'PERSONA_NATURAL.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
            ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
            ->leftJoin('MOISES.PERSONA_DIRECCION', function($join) { $join->on('PERSONA.ID_PERSONA', '=', 'PERSONA_DIRECCION.ID_PERSONA')->where('PERSONA_DIRECCION.ES_ACTIVO','=',1); })
            ->leftJoin('JOSE.SCHOOL_DATO_ADICIONAL', function($join) { $join->on('PERSONA.ID_PERSONA', '=', 'SCHOOL_DATO_ADICIONAL.ID_HIJO_HIJA')->whereNull('SCHOOL_DATO_ADICIONAL.ID_PMO'); })
            ->leftJoin('JOSE.SCHOOL_PERSONA_RELIGION', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_RELIGION.ID_PERSONA')
            ->leftJoin('JOSE.SCHOOL_PERSONA_VIVIENDA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_VIVIENDA.ID_PERSONA')
            ->leftJoin('JOSE.SCHOOL_PERSONA_LABORAL', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_LABORAL.ID_PERSONA')
            ->select(
                'PERSONA.ID_PERSONA',
                'PERSONA.NOMBRE',
                'PERSONA.PATERNO',
                'PERSONA.MATERNO',
                'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
                'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
                'PERSONA_NATURAL.ID_TIPOTRATAMIENTO',
                'PERSONA_NATURAL.ID_TIPOESTADOCIVIL',
                'PERSONA_NATURAL.SEXO',
                'PERSONA_NATURAL.VIVE',
                'PERSONA_NATURAL.ID_TIPOPAIS',
                'PERSONA_NATURAL.ID_NACIONALIDAD',
                'PERSONA_NATURAL.ID_DEPARTAMENTO',
                'PERSONA_NATURAL.ID_PROVINCIA',
                'PERSONA_NATURAL.ID_DISTRITO',
                'PERSONA_DIRECCION.DIRECCION',
                'SCHOOL_DATO_ADICIONAL.ID_RESP_PAGO',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_PADRE',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_MADRE',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_APODERADO',
                'SCHOOL_DATO_ADICIONAL.EX_ALUMNO',
                'SCHOOL_DATO_ADICIONAL.Ex_ALUMNO_ANHO',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_ESTUDIANTE',
                'SCHOOL_PERSONA_RELIGION.ID_RELIGION',
                'SCHOOL_PERSONA_VIVIENDA.ID_PAIS AS VIVIENDA_ID_PAIS',
                'SCHOOL_PERSONA_VIVIENDA.DIRECCION AS VIVIENDA_DIRECCION',
                'SCHOOL_PERSONA_VIVIENDA.DEPARTAMENTO AS VIVIENDA_DEPARTAMENTO',
                'SCHOOL_PERSONA_VIVIENDA.PROVINCIA AS VIVIENDA_PROVINCIA',
                'SCHOOL_PERSONA_VIVIENDA.DISTRITO AS VIVIENDA_DISTRITO',
                'SCHOOL_PERSONA_VIVIENDA.MOVIL AS VIVIENDA_MOVIL',
                'SCHOOL_PERSONA_VIVIENDA.TELEFONO AS VIVIENDA_TELEFONO',
                'SCHOOL_PERSONA_VIVIENDA.LOCALIZACION AS VIVIENDA_LOCALIZACION',
                'SCHOOL_PERSONA_VIVIENDA.LOCALIZACION_DETALLE AS VIVIENDA_LOCALIZACION_DETALLE',
                'SCHOOL_PERSONA_LABORAL.NOMBRE AS LABORAL_NOMBRE',
                'SCHOOL_PERSONA_LABORAL.CARGO AS LABORAL_CARGO',
                'SCHOOL_PERSONA_LABORAL.DIRECCION AS LABORAL_DIRECCION',
                'SCHOOL_PERSONA_LABORAL.PROFESION AS LABORAL_PROFESION',
                'SCHOOL_PERSONA_LABORAL.OCUPACION AS LABORAL_OCUPACION',
                'SCHOOL_PERSONA_LABORAL.ESPECIALIDAD AS LABORAL_ESPECIALIDAD',
                'SCHOOL_PERSONA_LABORAL.ID_TIPOPAIS AS LABORAL_ID_TIPOPAIS',
                'SCHOOL_PERSONA_LABORAL.ID_DEPARTAMENTO AS LABORAL_ID_DEPARTAMENTO',
                'SCHOOL_PERSONA_LABORAL.ID_PROVINCIA AS LABORAL_ID_PROVINCIA',
                'SCHOOL_PERSONA_LABORAL.ID_DISTRITO AS LABORAL_ID_DISTRITO',
                'SCHOOL_PERSONA_LABORAL.TELEFONO1 AS LABORAL_TELEFONO1',
                'SCHOOL_PERSONA_LABORAL.TELEFONO2 AS LABORAL_TELEFONO2',
                'SCHOOL_PERSONA_LABORAL.ANEXO AS LABORAL_ANEXO',
                DB::RAW(
                    "to_char(PERSONA_NATURAL.FEC_NACIMIENTO,'YYYY-MM-DD') AS FEC_NACIMIENTO"
                )
            )
            ->where('PERSONA.ID_PERSONA', $id_persona)
            ->first();
        }else{
            $rows = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL')
            ->join('MOISES.PERSONA', 'PERSONA_NATURAL.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
            ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
            ->leftJoin('MOISES.PERSONA_DIRECCION', function($join) { $join->on('PERSONA.ID_PERSONA', '=', 'PERSONA_DIRECCION.ID_PERSONA')->where('PERSONA_DIRECCION.ES_ACTIVO','=',1); })
            ->leftJoin('JOSE.SCHOOL_DATO_ADICIONAL', function($join) use($id_persona) { $join->on('PERSONA.ID_PERSONA', '=', 'SCHOOL_DATO_ADICIONAL.ID_PMO')->where('SCHOOL_DATO_ADICIONAL.ID_PMO',$id_persona); })
            ->leftJoin('JOSE.SCHOOL_PERSONA_RELIGION', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_RELIGION.ID_PERSONA')
            ->leftJoin('JOSE.SCHOOL_PERSONA_VIVIENDA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_VIVIENDA.ID_PERSONA')
            ->leftJoin('JOSE.SCHOOL_PERSONA_LABORAL', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_LABORAL.ID_PERSONA')
            ->select(
                'PERSONA.ID_PERSONA',
                'PERSONA.NOMBRE',
                'PERSONA.PATERNO',
                'PERSONA.MATERNO',
                'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
                'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
                'PERSONA_NATURAL.ID_TIPOTRATAMIENTO',
                'PERSONA_NATURAL.ID_TIPOESTADOCIVIL',
                'PERSONA_NATURAL.SEXO',
                'PERSONA_NATURAL.VIVE',
                'PERSONA_NATURAL.ID_TIPOPAIS',
                'PERSONA_NATURAL.ID_NACIONALIDAD',
                'PERSONA_NATURAL.ID_DEPARTAMENTO',
                'PERSONA_NATURAL.ID_PROVINCIA',
                'PERSONA_NATURAL.ID_DISTRITO',
                'PERSONA_DIRECCION.DIRECCION',
                'SCHOOL_DATO_ADICIONAL.ID_RESP_PAGO',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_PADRE',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_MADRE',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_APODERADO',
                'SCHOOL_DATO_ADICIONAL.EX_ALUMNO',
                'SCHOOL_DATO_ADICIONAL.Ex_ALUMNO_ANHO',
                'SCHOOL_DATO_ADICIONAL.VIVE_CON_ESTUDIANTE',
                'SCHOOL_DATO_ADICIONAL.ID_NIVEL_INSTRUCCION',
                'SCHOOL_DATO_ADICIONAL.TIPO_PARENTESCO',
                'SCHOOL_PERSONA_RELIGION.ID_RELIGION',
                'SCHOOL_PERSONA_VIVIENDA.ID_PAIS AS VIVIENDA_ID_PAIS',
                'SCHOOL_PERSONA_VIVIENDA.DIRECCION AS VIVIENDA_DIRECCION',
                'SCHOOL_PERSONA_VIVIENDA.DEPARTAMENTO AS VIVIENDA_DEPARTAMENTO',
                'SCHOOL_PERSONA_VIVIENDA.PROVINCIA AS VIVIENDA_PROVINCIA',
                'SCHOOL_PERSONA_VIVIENDA.DISTRITO AS VIVIENDA_DISTRITO',
                'SCHOOL_PERSONA_VIVIENDA.MOVIL AS VIVIENDA_MOVIL',
                'SCHOOL_PERSONA_VIVIENDA.TELEFONO AS VIVIENDA_TELEFONO',
                'SCHOOL_PERSONA_VIVIENDA.LOCALIZACION AS VIVIENDA_LOCALIZACION',
                'SCHOOL_PERSONA_VIVIENDA.LOCALIZACION_DETALLE AS VIVIENDA_LOCALIZACION_DETALLE',
                'SCHOOL_PERSONA_LABORAL.NOMBRE AS LABORAL_NOMBRE',
                'SCHOOL_PERSONA_LABORAL.CARGO AS LABORAL_CARGO',
                'SCHOOL_PERSONA_LABORAL.DIRECCION AS LABORAL_DIRECCION',
                'SCHOOL_PERSONA_LABORAL.PROFESION AS LABORAL_PROFESION',
                'SCHOOL_PERSONA_LABORAL.OCUPACION AS LABORAL_OCUPACION',
                'SCHOOL_PERSONA_LABORAL.ESPECIALIDAD AS LABORAL_ESPECIALIDAD',
                'SCHOOL_PERSONA_LABORAL.ID_TIPOPAIS AS LABORAL_ID_TIPOPAIS',
                'SCHOOL_PERSONA_LABORAL.ID_DEPARTAMENTO AS LABORAL_ID_DEPARTAMENTO',
                'SCHOOL_PERSONA_LABORAL.ID_PROVINCIA AS LABORAL_ID_PROVINCIA',
                'SCHOOL_PERSONA_LABORAL.ID_DISTRITO AS LABORAL_ID_DISTRITO',
                'SCHOOL_PERSONA_LABORAL.TELEFONO1 AS LABORAL_TELEFONO1',
                'SCHOOL_PERSONA_LABORAL.TELEFONO2 AS LABORAL_TELEFONO2',
                'SCHOOL_PERSONA_LABORAL.ANEXO AS LABORAL_ANEXO',
                DB::RAW(
                    "to_char(PERSONA_NATURAL.FEC_NACIMIENTO,'YYYY-MM-DD') AS FEC_NACIMIENTO"
                )
            )
            ->where('PERSONA.ID_PERSONA', $id_persona)
            ->first();
        }
        return $rows;
    }
    public static function listPersonsManager()
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL')
            ->join('MOISES.PERSONA', 'PERSONA_NATURAL.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
            ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
            ->select(
                'PERSONA.ID_PERSONA',
                'PERSONA.NOMBRE',
                'PERSONA.PATERNO',
                'PERSONA.MATERNO',
                'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
                'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
                'PERSONA_NATURAL.ID_TIPOTRATAMIENTO',
                'PERSONA_NATURAL.ID_TIPOESTADOCIVIL',
                'PERSONA_NATURAL.SEXO',
                'PERSONA_NATURAL.FEC_NACIMIENTO'
            )
            ->orderBy('PERSONA_NATURAL.ID_PERSONA','DESC')
            ->paginate(20);
        return $rows;
    }
    public static function listPersonsManagerSearch($text)
    {
        $rows = DB::connection('moises')->table('PERSONA_NATURAL')
            ->join('PERSONA', 'PERSONA_NATURAL.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
            ->join('PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
            ->select(
                'PERSONA.ID_PERSONA',
                'PERSONA.NOMBRE',
                'PERSONA.PATERNO',
                'PERSONA.MATERNO',
                'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
                'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
                'PERSONA_NATURAL.ID_TIPOTRATAMIENTO',
                'PERSONA_NATURAL.ID_TIPOESTADOCIVIL',
                'PERSONA_NATURAL.SEXO',
                'PERSONA_NATURAL.FEC_NACIMIENTO'
            )
            ->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO', 'like', '%'.$text.'%')
            ->orderBy('PERSONA_NATURAL.ID_PERSONA','DESC')
            ->get();
        return $rows;
    }
    /* SCHOOL_PERSONA_FAMILIA */
    public static function listPersonsFamily()
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_FAMILIA')
            ->join('MOISES.PERSONA', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
            ->select(
                'PERSONA.ID_PERSONA',
                DB::raw(
                    "
                    PERSONA.NOMBRE || ' ' || PERSONA.PATERNO || ' ' || PERSONA.MATERNO NOMBRE
                    "
                )
            )
            ->get();
        return $rows;
    }
    public static function addProformas($data)
    {
        $id_proforma = self::getIdMax(DB::connection('jose'),"SCHOOL_PROFORMA","ID_PROFORMA")+1;
        $data["id_proforma"] = $id_proforma;
        $result = DB::connection('jose')->table('SCHOOL_PROFORMA')->insert($data);
        if($result)
        {
            return self::showProformas($id_proforma);
        }
        return $result;
    }
    public static function updateProformas($data, $id_proforma)
    {
        $result = DB::connection('jose')->table('SCHOOL_PROFORMA')
        ->where('ID_PROFORMA', $id_proforma)
        ->update($data);
        if($result)
        {
            return self::showProformas($id_proforma);
        }
        return $result;
    }
    public static function showProformas($id_proforma)
    {
        $rows = DB::table('SCHOOL_PROFORMA')
            ->select(
                DB::raw(
                "
                SCHOOL_PROFORMA.ID_PROFORMA,
                SCHOOL_PROFORMA.NOMBRES,
                SCHOOL_PROFORMA.DNI,
                SCHOOL_PROFORMA.TELEFONO,
                SCHOOL_PROFORMA.CORREO,
                SCHOOL_NIVEL.NOMBRE AS NOMBRE_NIVEL,
                SCHOOL_GRADO.NOMBRE AS NOMBRE_GRADO,
                SCHOOL_TIPOPAGO.NOMBRE AS NOMBRE_TIPOPAGO
                "
                )
            )
            ->join('SCHOOL_PERIODO_NIVEL','SCHOOL_PROFORMA.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
            ->join('SCHOOL_PERIODO_NGRADO','SCHOOL_PROFORMA.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
            ->join('SCHOOL_NIVEL', 'SCHOOL_PERIODO_NIVEL.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
            ->join('SCHOOL_GRADO', 'SCHOOL_PERIODO_NGRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
            ->join('SCHOOL_TIPOPAGO', 'SCHOOL_PROFORMA.ID_TIPOPAGO', '=', 'SCHOOL_TIPOPAGO.ID_TIPOPAGO')
            ->where('SCHOOL_PROFORMA.ESTADO', '1')
            ->where('SCHOOL_PROFORMA.ID_PROFORMA', $id_proforma)
            ->first();
        return $rows;
    }
    public static function existSchedulesMeet($id_personal,$fecha)
    {
        return false;
    }
    public static function listSchedulesMeet($id_personal,$fecha)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_HORARIO_CITA')
        // ->join('JOSE.SCHOOL_PERIODO', 'SCHOOL_HORARIO_CITA.ID_PERIODO', '=', 'SCHOOL_PERIODO.ID_PERIODO')
        ->join('MOISES.PERSONA', 'SCHOOL_HORARIO_CITA.ID_PERSONAL', '=', 'PERSONA.ID_PERSONA')
        ->select(
            DB::raw(
                "
                SCHOOL_HORARIO_CITA.ID_HCITA,
                PERSONA.NOMBRE,
                PERSONA.PATERNO,
                PERSONA.MATERNO,
                TO_CHAR(SCHOOL_HORARIO_CITA.FECHA,'YYYY/MM/DD') FECHA,
                SCHOOL_HORARIO_CITA.HORA_INICIO,
                SCHOOL_HORARIO_CITA.HORA_FIN,
                SCHOOL_HORARIO_CITA.HORA,
                (SCHOOL_HORARIO_CITA.HORA-MOD(SCHOOL_HORARIO_CITA.HORA,100))/100 || ':' || MOD(SCHOOL_HORARIO_CITA.HORA,100) HORA_FORMAT
                "
            )
        )
        // ->where('SCHOOL_PERIODO.ESTADO', 'O')
        ;
        if($id_personal)
            $rows->where('SCHOOL_HORARIO_CITA.ID_PERSONAL', $id_personal);
        if($fecha)
            $rows->where('SCHOOL_HORARIO_CITA.FECHA', $fecha);
        $rows->orderBy('SCHOOL_HORARIO_CITA.FECHA', 'DESC');
        $rows->orderBy('SCHOOL_HORARIO_CITA.HORA_INICIO', 'DESC');
        return $rows->get();
    }
    public static function showRetirements($id_retiro)
    {
        $row = DB::connection('jose')->table('SCHOOL_RETIRO')
        ->select(
            'ID_ALUMNO',
            'ID_RETIRO'
        )
        ->where('ID_RETIRO', $id_retiro)
        ->first();
        return $row;
    }
    public static function listRetirements($id_alumno)
    {
        $rows = DB::connection('jose')->table('VSCH_RETIROS')
        ->select(
            'NOMBRE',
            'PATERNO',
            'MATERNO',
            'ID_ALUMNO',
            'ID_RETIRO',
            'BLOQUEO',
            'ESTADO'
        )
        ->where('ID_ALUMNO', $id_alumno)
        ->get();
        return $rows;
    }
    public static function addRetirements($data)
    {
        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_RETIRO", "ID_RETIRO")+1;
        $data["id_retiro"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_RETIRO')->insert($data);
        if($result) return $id;
        return $result;
    }
    public static function updateRetirements($data, $id_retiro)
    {
        $result = DB::connection('jose')->table('SCHOOL_RETIRO')
        ->where('ID_RETIRO', $id_retiro)
        ->update($data);
        return $result;
    }

    public static function showStudents($id_alumno)
    {
        $row = DB::connection('jose')->table('SCHOOL_ALUMNO')
        ->where('ID_ALUMNO', $id_alumno)
        ->first();
        return $row;
    }
    public static function addStudents($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_ALUMNO')
        ->insert($data);
        return $result;
    }
    public static function updateStudents($data, $id_alumno)
    {
        $result = DB::connection('jose')->table('SCHOOL_ALUMNO')
        ->where('ID_ALUMNO', $id_alumno)
        ->update($data);
        return $result;
    }
    public static function listTransfers($id_alumno, $ocp)
    {
        $rows = DB::connection('jose')->table('VSCH_TRASLADOS')
        ->select(
            'NOMBRE',
            'PATERNO',
            'MATERNO',
            'ID_ALUMNO',
            'ID_TRASLADO',
            'BLOQUEO',
            'CODIGO',
            'ESTADO_ALUMNO',
            'PROCEDENCIA',
            'ESTADO',
            'ID_NIVEL',
            'ID_GRADO',
            'ID_INSTITUCION',
            'NOMBRE_NIVEL',
            'NOMBRE_GRADO',
            'NOMBRE_INSTITUTO'
        )
        ->where('ID_ALUMNO', $id_alumno);
        if($ocp == 'T') $rows->whereRaw("((ESTADO = 'I' OR ESTADO IS NULL) AND ID_INSTITUCION IS NOT NULL)");
        else if($ocp == 'R') $rows->whereRaw("(ESTADO = 'S' OR ID_INSTITUCION IS NULL)");
        return $rows->get();;
    }
    public static function addTransfers($data)
    {
        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_TRASLADO", "ID_TRASLADO")+1;
        $data["id_traslado"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_TRASLADO')->insert($data);
        if($result) return $id;
        return $result;
    }
    public static function updateTransfers($data, $id_traslado)
    {
        $result = DB::connection('jose')->table('SCHOOL_TRASLADO')
        ->where('ID_TRASLADO', $id_traslado)
        ->update($data);
        return $result;
    }
    public static function listStudentsSearch($text)
    {
        $rows = DB::connection('jose')->table('VSCH_ALUMNOS')
        ->select(
            'ID_PERSONA',
            'NOMBRE',
            'PATERNO',
            'MATERNO',
            'ID_TIPODOCUMENTO',
            'NUM_DOCUMENTO',
            'ID_TIPOTRATAMIENTO',
            'ID_TIPOESTADOCIVIL',
            'SEXO',
            'FEC_NACIMIENTO'
        )
        ->where('NUM_DOCUMENTO', 'like', '%'.$text.'%')
        ->orWhereRaw("UPPER(NOMBRE || ' ' || PATERNO || ' ' || MATERNO) LIKE UPPER('%".$text."%')")
        ->get();
        return $rows;
    }
    public static function showQuestionnaires($id_cuestionario)
    {
        $row = DB::connection('jose')->table('SCHOOL_CUESTIONARIO')
        ->select(
            'ID_CUESTIONARIO',
            'TITULO',
            'DESCRIPCION',
            'ID_NIVEL',
            DB::raw(
                "
                (
                    SELECT SCHOOL_NIVEL.NOMBRE
                    FROM SCHOOL_NIVEL
                    WHERE SCHOOL_CUESTIONARIO.ID_NIVEL = SCHOOL_NIVEL.ID_NIVEL
                ) NOMBRE_NIVEL
                "
            )
        )
        ->where('ID_CUESTIONARIO', $id_cuestionario)
        ->first();
        return $row;
    }
    public static function listQuestionnairesQuestionsEvaluations($id_persona, $id_nivel) // ($id_persona, $id_cuestionario)
    {
        $rows = DB::connection('jose')->table('SCHOOL_CUESTIONARIO_PREGUNTA')
        // ->join('SCHOOL_CUESTIONARIO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->join('SCHOOL_CUESTIONARIO', function($join) use($id_nivel) {
            $join->on('SCHOOL_CUESTIONARIO_PREGUNTA.ID_CUESTIONARIO', '=', 'SCHOOL_CUESTIONARIO.ID_CUESTIONARIO')
            ->where('SCHOOL_CUESTIONARIO.ID_NIVEL', '=', $id_nivel);
        })
        ->leftJoin('SCHOOL_CUESTIONARIO_EVALUACION', function($join) use($id_persona) {
            $join->on('SCHOOL_CUESTIONARIO_EVALUACION.ID_CPREGUNTA', '=', 'SCHOOL_CUESTIONARIO_PREGUNTA.ID_CPREGUNTA')
            ->where('SCHOOL_CUESTIONARIO_EVALUACION.ID_PERSONA', '=', $id_persona);
        })
        ->select(
                'SCHOOL_CUESTIONARIO_PREGUNTA.ID_CPREGUNTA',
                'SCHOOL_CUESTIONARIO_PREGUNTA.ORDEN',
                'SCHOOL_CUESTIONARIO_PREGUNTA.DESCRIPCION',
                'SCHOOL_CUESTIONARIO_PREGUNTA.GRUPO',
                'SCHOOL_CUESTIONARIO_PREGUNTA.ID_CUESTIONARIO',
                'SCHOOL_CUESTIONARIO_EVALUACION.ID_CEVALUACION',
                'SCHOOL_CUESTIONARIO_EVALUACION.DESCRIPCION AS DESCRIPCION_EVAL',
                'SCHOOL_CUESTIONARIO_EVALUACION.ESTADO',
                'SCHOOL_CUESTIONARIO_EVALUACION.ID_PERSONA'
        )
        // ->where('SCHOOL_CUESTIONARIO_PREGUNTA.ID_CUESTIONARIO', $id_cuestionario)
        ->orderBy('SCHOOL_CUESTIONARIO_PREGUNTA.ORDEN', 'ASC')
        ->get();
        return $rows;
    }
    public static function showPeriodsSGrades_($id_periodo, $id_nivel, $id_grado)
    {
        $row = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->join('SCHOOL_PERIODO_NIVEL', 'SCHOOL_PERIODO_NGRADO.ID_PNIVEL', '=', 'SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->select(
                'SCHOOL_PERIODO_NGRADO.ID_PNGRADO',
                'SCHOOL_PERIODO_NGRADO.ID_PNIVEL',
                'SCHOOL_PERIODO_NGRADO.ID_GRADO',
                'SCHOOL_PERIODO_NGRADO.TIPO_NOTA',
                'SCHOOL_PERIODO_NGRADO.THORAS',
                'SCHOOL_PERIODO_NGRADO.NOTA_MIN_APROB',
                'SCHOOL_PERIODO_NGRADO.TNRO_CUPO',
                'SCHOOL_PERIODO_NGRADO.SINCRONIZADO',
                'SCHOOL_PERIODO_NGRADO.TURNO'
        )
        ->where('ID_PERIODO', $id_periodo)
        ->where('SCHOOL_PERIODO_NIVEL.ID_NIVEL', $id_nivel)
        ->where('SCHOOL_PERIODO_NGRADO.ID_GRADO', $id_grado)
        ->first();
        return $row;
    }
    public static function existsVacantGrade($id_pngrado)
    {
        $total_cupos = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->where('ID_PNGRADO', $id_pngrado)
        ->sum('TNRO_CUPO');
        if(!$total_cupos) return false;

        $count_matriculados = DB::connection('jose')->table('SCHOOL_MATRICULA')
        ->where('ID_PNGRADO', $id_pngrado)
        ->count();
        $total_ocupados = 0;
        if($count_matriculados) $total_ocupados = $count_matriculados;

        $count_reservas = DB::connection('jose')->table('SCHOOL_RESERVA')
        ->where('ID_PNGRADO', $id_pngrado)
        ->count();
        if($count_reservas) $total_ocupados = $total_ocupados + $count_reservas;

        if($total_cupos > $total_ocupados) return true;
        return false;
    }

    public static function updatePersonsDocument($data, $id_persona)
    {
        $result = DB::connection('moises')->table('PERSONA_DOCUMENTO')
        ->where('PERSONA_DOCUMENTO.ID_PERSONA', $id_persona)
        ->where('PERSONA_DOCUMENTO.ID_TIPODOCUMENTO', $data['id_tipodocumento'])
        ->update($data);
        return $result;
    }
    public static function addSchedulesMeet($data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_HORARIO_CITA","ID_HCITA")+1;
        $data["id_hcita"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_HORARIO_CITA')->insert($data);
        if($result) return $id;
        return false;
    }
    public static function deleteSchedulesMeet($id_hcita)
    {
        $result = DB::connection('jose')->table('SCHOOL_HORARIO_CITA')
            ->where('ID_HCITA', '=', $id_hcita)
            ->delete();
        return $result;
    }
    public static function listMeetS($ids)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_CITA')
        ->join('JOSE.SCHOOL_HORARIO_CITA', 'SCHOOL_CITA.ID_HCITA', '=', 'SCHOOL_HORARIO_CITA.ID_HCITA')
        // ->join('JOSE.SCHOOL_PERIODO', 'SCHOOL_HORARIO_CITA.ID_PERIODO', '=', 'SCHOOL_PERIODO.ID_PERIODO')
        ->join('MOISES.PERSONA', 'SCHOOL_CITA.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
        ->select(
            DB::raw(
                "
                SCHOOL_CITA.ID_CITA,
                TO_CHAR(SCHOOL_HORARIO_CITA.FECHA,'YYYY/MM/DD') FECHA,
                SCHOOL_CITA.TIPO,
                SCHOOL_HORARIO_CITA.HORA_INICIO,
                SCHOOL_HORARIO_CITA.HORA_FIN,
                PERSONA.NOMBRE || ' ' || PERSONA.PATERNO || ' ' || PERSONA.MATERNO NOMBRE
                "
            )
        )
        // ->where('SCHOOL_PERIODO.ESTADO','O')
        ->whereIn('SCHOOL_CITA.ID_PERSONA', $ids)
        ->orderBy('FECHA', 'DESC')
        ->orderBy('HORA_INICIO', 'DESC')
        ->get();
        return $rows;
    }
    public static function addMeets($data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_CITA","ID_CITA")+1;
        $data["id_cita"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_CITA')->insert($data);
        static::$id_cita = $id;
        return $result;
    }
    public static function listAgreements()
    {
        $rows = DB::connection('jose')->table('SCHOOL_ACUERDO')
            ->select(
                DB::raw(
                    "
                    SCHOOL_CITA.ID_ACUERDO,
                    SCHOOL_CITA.IMPORTE,
                    SCHOOL_CITA.DETALLE
                    "
                )
            )
            ->orderBy('SCHOOL_ACUERDO.ID_ACUERDO', 'DESC')
            ->get();
        return $rows;
    }
    public static function addAgreements($data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_ACUERDO","ID_ACUERDO")+1;
        $data["id_acuerdo"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_ACUERDO')->insert($data);
        static::$id_acuerdo = $id;
        return $result;
    }
    public static function existsTypesPayInPeriod($id_entidad, $id_depto, $id_tipopago)
    {
        $result = DB::connection('jose')->table('SCHOOL_TIPOPAGO')
            ->select(
                DB::raw(
                    "
                    ID_TIPOPAGO,
                    NOMBRE,
                    ESTADO
                    "
                )
            )
            ->whereIn('ID_TIPOPAGO', function($query) use ($id_entidad, $id_depto){
                $query->select('ID_TIPOPAGO')
                ->from('SCHOOL_CRITERIO')
                ->join('SCHOOL_PERIODO','SCHOOL_CRITERIO.ID_PERIODO','=','SCHOOL_PERIODO.ID_PERIODO')
                ->where('SCHOOL_PERIODO.ID_ENTIDAD', $id_entidad)
                ->where('SCHOOL_PERIODO.ID_DEPTO', $id_depto)
                ->where('SCHOOL_PERIODO.ESTADO', '2');
            })
            ->where('ID_TIPOPAGO', $id_tipopago)
            ->exists();
        return $result;
    }
    public static function existsVacantInPeriod($id_entidad, $id_depto, $id_ngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_VACANTE')
        ->join('SCHOOL_PERIODO','SCHOOL_VACANTE.ID_PERIODO','=','SCHOOL_PERIODO.ID_PERIODO')
        ->where('SCHOOL_PERIODO.ID_ENTIDAD', $id_entidad)
        ->where('SCHOOL_PERIODO.ID_DEPTO', $id_depto)
        ->where('SCHOOL_PERIODO.ESTADO', '2')
        ->where('SCHOOL_VACANTE.ID_NGRADO', $id_ngrado)
        ->exists();
        return $rows;
    }
    public static function listTypesPhone()
    {
        $rows = DB::connection('moises')->table('TIPO_TELEFONO')
            ->select(
                'TIPO_TELEFONO.ID_TIPOTELEFONO',
                'TIPO_TELEFONO.NOMBRE'
            )
            ->orderBy('TIPO_TELEFONO.NOMBRE')
            ->get();
        return $rows;
    }
    public static function listTypesAddress()
    {
        $rows = DB::connection('moises')->table('TIPO_DIRECCION')
            ->select(
                'TIPO_DIRECCION.ID_TIPODIRECCION',
                'TIPO_DIRECCION.NOMBRE'
            )
            ->orderBy('TIPO_DIRECCION.NOMBRE')
            ->get();
        return $rows;
    }
    public static function listTypesVirtual()
    {
        $rows = DB::connection('moises')->table('TIPO_VIRTUAL')
            ->select(
                'TIPO_VIRTUAL.ID_TIPOVIRTUAL',
                'TIPO_VIRTUAL.NOMBRE',
                'TIPO_VIRTUAL.SIGLAS'
            )
            ->orderBy('TIPO_VIRTUAL.NOMBRE')
            ->get();
        return $rows;
    }
    public static function existsPersonsDocumentNum($id_tipodocumento,$num_documento,$id_persona)
    {
        if(!empty($id_persona)){
             $exists = DB::connection('moises')->table('PERSONA_DOCUMENTO')
            ->where('ID_TIPODOCUMENTO','=',$id_tipodocumento)
            ->where('NUM_DOCUMENTO','=',$num_documento)
            ->where('ID_PERSONA','!=',$id_persona)
            ->exists();
        }else{
            $exists = DB::connection('moises')->table('PERSONA_DOCUMENTO')
            ->where('ID_TIPODOCUMENTO','=',$id_tipodocumento)
            ->where('NUM_DOCUMENTO','=',$num_documento)
            ->exists();
        }
       
        return $exists;
    }
    public static function idPersonByDocumentNum($id_tipodocumento,$num_documento)
    {
        $rows = DB::connection('moises')->table('PERSONA_DOCUMENTO')
        ->select('ID_PERSONA')
        ->where('ID_TIPODOCUMENTO','=',$id_tipodocumento)
        ->where('NUM_DOCUMENTO','=',$num_documento)
        ->first();
        return $rows;
    }
    public static function listPais()
    {
        $rows = DB::connection('moises')->table('TIPO_PAIS')
            ->select(
                DB::raw(
                    "
                    ID_TIPOPAIS,
                    NOMBRE
                    "
                )
            )
            ->orderBy('TIPO_PAIS.ID_TIPOPAIS', 'DESC')
            ->get();
        return $rows;
    }
    public static function listDepartamento($id_pais)
    {
        $rows = DB::connection('jose')->table('SCHOOL_DEPARTAMENTO')
            ->select(
                DB::raw(
                    "
                    DEP_ID,
                    PAI_ID,
                    DEP_NOMBRE,
                    DEP_CODIGO
                    "
                )
            )
            ->where('PAI_ID',$id_pais)
            ->orderBy('DEP_ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function listProvincia($id_dep)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PROVINCIA')
            ->select(
                DB::raw(
                    "
                    PRO_ID,
                    DEP_ID,
                    PRO_NOMBRE,
                    PRO_CODIGO
                    "
                )
            )
            ->where('DEP_ID',$id_dep)
            ->orderBy('PRO_ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function listDistrito($id_prov)
    {
        $rows = DB::connection('jose')->table('SCHOOL_DISTRITO')
            ->select(
                DB::raw(
                    "
                    DIS_ID,
                    PRO_ID,
                    DIS_NOMBRE,
                    DIS_CODIGO
                    "
                )
            )
            ->where('PRO_ID',$id_prov)
            ->orderBy('DIS_ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function listTypesPay()
    {
        $rows = DB::connection('jose')->table('SCHOOL_TIPOPAGO')
            ->select(
                DB::raw(
                    "
                    ID_TIPOPAGO,
                    NOMBRE,
                    ESTADO
                    "
                )
            )
            ->where('estado','1')
            ->orderBy('ID_TIPOPAGO', 'DESC')
            ->get();
        return $rows;
    }
    public static function listPeriodoEscolar($id_entidad, $id_depto)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO')
            ->select(
                DB::raw(
                    "
                    ID_PERIODO,
                    ANHO_PERIODO,
                    NOMBRE
                    "
                )
            )
            ->where('ID_ENTIDAD',$id_entidad)
            ->where('ID_DEPTO',$id_depto)
            ->where('ESTADO','O')
            ->orderBy('ID_PERIODO', 'DESC')
            ->get();
        return $rows;
    }  
    public static function listReligion(){
        $rows = DB::connection('jose')->table('SCHOOL_RELIGION')
            ->select(
                DB::raw(
                    "
                    ID,
                    RELIGION_NOMBRE
                    "
                )
            )
            ->orderBy('ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function listTipoIdioma(){
        $rows = DB::connection('moises')->table('TIPO_IDIOMA')
            ->select(
                DB::raw(
                    "
                    ID_TIPOIDIOMA,
                    NOMBRE
                    "
                )
            )
            ->orderBy('ID_TIPOIDIOMA', 'DESC')
            ->get();
        return $rows;
    }
    public static function listLevelInstruction(){
        $rows = DB::connection('jose')->table('SCHOOL_NIVEL_INSTRUCCION')
            ->select(
                DB::raw(
                    "
                    ID,
                    NIVELINSTRUCCION_NOMBRE
                    "
                )
            )
            ->orderBy('ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function listStatusCivil(){
        $rows = DB::connection('moises')->table('TIPO_ESTADO_CIVIL')
            ->select(
                DB::raw(
                    "
                    ID_TIPOESTADOCIVIL,
                    NOMBRE
                    "
                )
            )
            ->orderBy('ID_TIPOESTADOCIVIL', 'DESC')
            ->get();
        return $rows;
    }
    public static function listOperatorMovil(){
        $rows = DB::connection('jose')->table('SCHOOL_OPERADOR_MOVIL')
            ->select(
                DB::raw(
                    "
                    ID,
                    OPERADORMOVIL_NOMBRE
                    "
                )
            )
            ->orderBy('ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function listLocalization(){
        $rows = DB::connection('jose')->table('SCHOOL_LOCALIZACION')
            ->select(
                DB::raw(
                    "
                    ID,
                    LOCALIZACION_NOMBRE
                    "
                )
            )
            ->orderBy('ID', 'DESC')
            ->get();
        return $rows;
    }
    public static function proformaByDni($dni)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PROFORMA')
            ->select('ID_PROFORMA','NOMBRES')
            ->where('DNI',$dni)
            ->first();
        return $rows;
    }
    public static function listFamilySon($numero)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')
            ->select('ID_PERSONA','TIPOPARENTESCO_ID')
            ->where('NUMERO',$numero)
            ->where('TIPOPARENTESCO_ID','03')
            ->get();
        return $rows;
    }
    public static function addPersonsTelefono($data)
    {
        $id = self::getIdMax(DB::connection('eliseo'),"MOISES.PERSONA_TELEFONO","ID_TELEFONO")+1;
        $data["id_telefono"] = $id;
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_TELEFONO')->insert($data);
        static::$id_telefono = $id;
        return $result;
    }
    public static function personsTelefonoDeleteByIdPersona($id_persona)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_TELEFONO')
        ->where('PERSONA_TELEFONO.ID_PERSONA',$id_persona)
        ->delete();
        return $result;
    }
    public static function telefonosByIdPersona($id_persona)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_TELEFONO')
        ->select('PERSONA_TELEFONO.NUM_TELEFONO','PERSONA_TELEFONO.OPERADOR_MOVIL')
        ->where('PERSONA_TELEFONO.ID_PERSONA',$id_persona)
        ->get();
        return $result;
    }
    public static function addPersonsNaturalIdioma($data)
    {
        $id = self::getIdMax(DB::connection('eliseo'),"MOISES.PERSONA_NATURAL_IDIOMA","ID_IDIOMA")+1;
        $data['ID_IDIOMA'] = $id;
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL_IDIOMA')->insert($data);
        return $result;
    }
    public static function personsNaturalIdiomaDeleteByIdPersona($id_persona)
    {
        $result = DB::connection('eliseo')->table('MOISES.PERSONA_NATURAL_IDIOMA')
        ->where('PERSONA_NATURAL_IDIOMA.ID_PERSONA',$id_persona)
        ->delete();
        return $result;
    }
    public static function existSchoolDatoAdicionalHijo($id_hijo)
    {
        $result = DB::connection('jose')->table('SCHOOL_DATO_ADICIONAL')
        ->where('ID_HIJO_HIJA',$id_hijo)
        ->whereNull('ID_PMO')
        ->exists();
        return $result;
    }
    public static function firstSchoolDatoAdicionalIdTipoParentesco($id_hijo,$tipo)
    {
        $result = DB::connection('jose')
        ->table('SCHOOL_DATO_ADICIONAL')
        ->select('ID_PMO','ID_RESP_PAGO')
        ->where('ID_HIJO_HIJA',$id_hijo)
        ->where('TIPO_PARENTESCO',$tipo)
        ->first();
        return $result;
    }
    public static function existSchoolDatoAdicionalHijoPadre($id_hijo,$id_padre)
    {
        $result = DB::connection('jose')->table('SCHOOL_DATO_ADICIONAL')
        ->where('ID_HIJO_HIJA',$id_hijo)
        ->where('ID_PMO',$id_padre)
        ->exists();
        return $result;
    }
    public static function addSchoolDatoAdicional($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_DATO_ADICIONAL')->insert($data);
        return $result;
    }
    public static function updateSchoolDatoAdicional($data,$id_hijo,$id_padre)
    {
        if(!empty($id_hijo) && !empty($id_padre)){
             $result = DB::connection('moises')->table('SCHOOL_DATO_ADICIONAL')
            ->where('id_hijo_hija',$id_hijo)
            ->where('id_pmo',$id_padre)
            ->update($data);
        }else{
            $result = DB::connection('moises')->table('SCHOOL_DATO_ADICIONAL')
            ->where('id_hijo_hija',$id_hijo)
            ->whereNull('id_pmo')
            ->update($data); 
        }
        return $result;
    }
    public static function addSchoolPersonsFamily($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')->insert($data);
        return $result;
    }
    public static function updateSchoolPersonsFamily($data,$id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')
        ->update($data)
        ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',$id_persona);
        return $result;
    }
    public static function addSchoolPersonsFamilySon($data,$numero)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_PERSONA_FAMILIA","NUMERO")+1;
       
        if(!empty($numero)){
            $data["numero"] = $numero;
        }else{
            $data["numero"] = $id;
        }
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')->insert($data);
        static::$numero = $id;
        return $result;
    }
    public static function updateSchoolPersonsFamilySon($data,$id_persona)
    {
     
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')
        ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',$id_persona)
        ->update($data);
        
        return $result;
    }
    public static function addSchoolPersonsReligion($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_RELIGION')->insert($data);
        return $result;
    }
    public static function updateSchoolPersonsReligion($data,$id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_RELIGION')
        ->where('SCHOOL_PERSONA_RELIGION.ID_PERSONA',$id_persona)
        ->update($data);
        return $result;
    }
    public static function schoolPersonsReligionByIdPersona($id_persona,$id_religion)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_RELIGION')
        ->where('SCHOOL_PERSONA_RELIGION.ID_PERSONA',$id_persona)
        ->where('SCHOOL_PERSONA_RELIGION.ID_RELIGION',$id_religion)
        ->exists();
        return $result;
    }
    public static function addSchoolPersonsLaboral($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_LABORAL')->insert($data);
        return $result;
    }
    public static function updateSchoolPersonsLaboral($data,$id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_LABORAL')
        ->where('SCHOOL_PERSONA_LABORAL.ID_PERSONA',$id_persona)
        ->update($data);
        return $result;
    }
    public static function existSchoolPersonsLaboral($id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_LABORAL')
        ->where('SCHOOL_PERSONA_LABORAL.ID_PERSONA',$id_persona)
        ->exists();
        return $result;
    }
    public static function addSchoolPersonsVivienda($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_VIVIENDA')->insert($data);
        return $result;
    }
    public static function updateSchoolPersonsVivienda($data,$id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_VIVIENDA')
        ->where('SCHOOL_PERSONA_VIVIENDA.ID_PERSONA',$id_persona)
        ->update($data);
        
        return $result;
    }
    public static function existSchoolPersonsVivienda($id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_VIVIENDA')
        ->where('SCHOOL_PERSONA_VIVIENDA.ID_PERSONA',$id_persona)
        ->exists();
        
        return $result;
    }
    public static function addSchoolPersonsParentesco($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_PARENTESCO')->insert($data);
        return $result;
    }
    public static function listTipoParentesco()
    {
        $rows = DB::connection('jose')->table('SCHOOL_TIPO_PARENTESCO')
            ->select('TIPOPARENTESCO_ID','NOMBRE')
            ->get();
        return $rows;
    }
    public static function numeroFamiliaByDni($dni, $id_persona)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
        ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->join('JOSE.SCHOOL_PERSONA_FAMILIA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA')
        ->select(
            'SCHOOL_PERSONA_FAMILIA.NUMERO'
        );
        if($dni)
        {
            $rows->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO',$dni);
        }
        if($id_persona)
        {
            $rows->where('PERSONA.ID_PERSONA',$id_persona);
        }
        return $rows->first();
    }
    public static function numeroFamilia($numero)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
            ->join('JOSE.SCHOOL_PERSONA_FAMILIA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA')
            ->join('JOSE.SCHOOL_TIPO_PARENTESCO', 'SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID', '=', 'SCHOOL_TIPO_PARENTESCO.TIPOPARENTESCO_ID')
            ->select(
                'SCHOOL_TIPO_PARENTESCO.NOMBRE AS TIPO','SCHOOL_PERSONA_FAMILIA.ID_PERSONA','SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','SCHOOL_PERSONA_FAMILIA.NOMBRE_FAMILIA AS FAMILIA','SCHOOL_PERSONA_FAMILIA.NUMERO','PERSONA.NOMBRE','PERSONA.PATERNO','PERSONA.MATERNO'
            )
            ->where('SCHOOL_PERSONA_FAMILIA.NUMERO', $numero)
            ->get();
        return $rows;
    }
    public static function numeroFamiliaAndHijo($numero)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
            ->join('JOSE.SCHOOL_PERSONA_FAMILIA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA')
            ->select(
                'SCHOOL_PERSONA_FAMILIA.ID_PERSONA','SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','SCHOOL_PERSONA_FAMILIA.NOMBRE_FAMILIA AS FAMILIA','SCHOOL_PERSONA_FAMILIA.NUMERO','PERSONA.NOMBRE','PERSONA.PATERNO','PERSONA.MATERNO'
            )
            ->where('SCHOOL_PERSONA_FAMILIA.NUMERO', $numero)
            ->where('SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','03')
            ->first();
        return $rows;
    }
    public static function familiaByIdPersonExist($id_persona)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_FAMILIA')
            ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',$id_persona)
            ->exists();
        return $rows;
    }
    public static function familiaHijoByIdPersonExist($id_persona)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_FAMILIA')
            ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',$id_persona)
            ->where('SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','03')
            ->exists();
        return $rows;
    }
    public static function familiaByIdPerson($id_persona)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
            ->join('MOISES.PERSONA_NATURAL', 'PERSONA.ID_PERSONA', '=', 'PERSONA_NATURAL.ID_PERSONA')
            ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
            ->join('JOSE.SCHOOL_PERSONA_FAMILIA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA')
            ->select(
                DB::RAW(
                    "to_char(PERSONA_NATURAL.FEC_NACIMIENTO,'YYYY-MM-DD') AS FEC_NACIMIENTO"
                ),
                'PERSONA_NATURAL.SEXO',
                'PERSONA_NATURAL.VIVE',
                'PERSONA_NATURAL.ID_TIPOPAIS',
                'PERSONA_NATURAL.ID_NACIONALIDAD',
                'PERSONA_NATURAL.ID_DEPARTAMENTO',
                'PERSONA_NATURAL.ID_PROVINCIA',
                'PERSONA_NATURAL.ID_DISTRITO',
                'SCHOOL_PERSONA_FAMILIA.ID_PERSONA',
                'SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID',
                'SCHOOL_PERSONA_FAMILIA.NOMBRE_FAMILIA AS FAMILIA',
                'SCHOOL_PERSONA_FAMILIA.NUMERO',
                'PERSONA.NOMBRE','PERSONA.PATERNO',
                'PERSONA.MATERNO',
                'PERSONA_DOCUMENTO.ID_TIPODOCUMENTO',
                'PERSONA_DOCUMENTO.NUM_DOCUMENTO'
            )
            ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',$id_persona)
            ->get();
        return $rows;
    }
    public static function datosByCodFamAndIdPerson($numero_familia,$id_persona)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
            ->join('JOSE.SCHOOL_PERSONA_FAMILIA', 'PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA')
            ->select(
                'SCHOOL_PERSONA_FAMILIA.ID_PERSONA','SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','SCHOOL_PERSONA_FAMILIA.NOMBRE_FAMILIA AS FAMILIA','SCHOOL_PERSONA_FAMILIA.NUMERO','PERSONA.NOMBRE','PERSONA.PATERNO','PERSONA.MATERNO'
            )
            ->where('SCHOOL_PERSONA_FAMILIA.NUMERO', $numero_familia)
            ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',$id_persona)
            ->first();
        return $rows;
    }
    public static function datosByCodFamAndIdHijo($numero_familia,$tipoparentesco)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_FAMILIA')
            ->join('MOISES.PERSONA', 'SCHOOL_PERSONA_FAMILIA.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
            ->select(
                'SCHOOL_PERSONA_FAMILIA.ID_PERSONA','SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','SCHOOL_PERSONA_FAMILIA.NOMBRE_FAMILIA AS FAMILIA','SCHOOL_PERSONA_FAMILIA.NUMERO','PERSONA.NOMBRE','PERSONA.PATERNO','PERSONA.MATERNO'
            )
            ->where('SCHOOL_PERSONA_FAMILIA.NUMERO', $numero_familia)
            ->where('SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID',$tipoparentesco)
            ->first();
        return $rows;
    }
    public static function familiaByIdPersonaAndNumero($id_persona,$numero)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')
            ->where('SCHOOL_PERSONA_FAMILIA.NUMERO', $numero)
            ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA', $id_persona)
            ->exists();
        return $rows;
    }
    public static function idiomasByIdPersona($id_persona)
    {
        $rows = DB::connection('moises')->table('PERSONA_NATURAL_IDIOMA')
            ->select(
                'PERSONA_NATURAL_IDIOMA.ID_PERSONA','PERSONA_NATURAL_IDIOMA.ID_TIPOIDIOMA','PERSONA_NATURAL_IDIOMA.ES_MATERNO'
            )
            ->where('PERSONA_NATURAL_IDIOMA.ID_PERSONA', $id_persona)
            ->get();
        return $rows;
    }
    public static function correosByIdPersona($id_persona)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA_VIRTUAL')
            ->select(
                'PERSONA_VIRTUAL.ID_VIRTUAL','PERSONA_VIRTUAL.ID_PERSONA','PERSONA_VIRTUAL.DIRECCION AS CORREO'
            )
            ->where('PERSONA_VIRTUAL.ID_PERSONA', $id_persona)
            ->orderBy('ID_VIRTUAL','ASC')
            ->get();
        return $rows;
    }
    public static function datoRespFinanByIdPerson($id_persona)
    {
        $rows = DB::connection('jose')->table('SCHOOL_DATO_ADICIONAL')
            ->select(
                'ID_RESP_PAGO'
            )
            ->where('ID_HIJO_HIJA',$id_persona)
            ->whereNull('ID_PMO')
            ->first();
        return $rows;
    }
    public static function listPersonsAdmisionSearch($texto)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_ADMISION')
        ->join('MOISES.PERSONA', 'SCHOOL_PERSONA_ADMISION.ID_PERSONA', '=', 'PERSONA.ID_PERSONA')
        ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->select(
            'PERSONA.ID_PERSONA',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO',
            'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
            DB::raw(
                "
                PERSONA.NOMBRE || ' ' || PERSONA.PATERNO || ' ' || PERSONA.MATERNO NOMBREC,
                (
                    SELECT NOMBRE 
                    FROM JOSE.SCHOOL_NIVEL 
                    INNER JOIN JOSE.SCHOOL_PERIODO_NIVEL
                    ON SCHOOL_PERIODO_NIVEL.ID_NIVEL = SCHOOL_NIVEL.ID_NIVEL
                    WHERE SCHOOL_PERIODO_NIVEL.ID_PNIVEL = SCHOOL_PERSONA_ADMISION.ID_PNIVEL
                ) NOMBRE_NIVEL,
                (
                    SELECT NOMBRE 
                    FROM JOSE.SCHOOL_GRADO
                    INNER JOIN JOSE.SCHOOL_PERIODO_NGRADO
                    ON SCHOOL_PERIODO_NGRADO.ID_GRADO = SCHOOL_GRADO.ID_GRADO
                    WHERE SCHOOL_PERIODO_NGRADO.ID_PNGRADO = SCHOOL_PERSONA_ADMISION.ID_PNGRADO
                ) NOMBRE_GRADO
                "
            )
        )
        // // ->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO', 'LIKE', '%'.$texto.'%')
        ->where('SCHOOL_PERSONA_ADMISION.ESTADO', '1')
        // ->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO', $texto)
        // ->orWhereRaw("LOWER(NOMBRE || ' ' || PATERNO || ' ' || MATERNO) LIKE LOWER('%".$texto."%')")
        ->where(function ($query) use($texto) {
            // $query->where('votes', '>', 100)
            // ->orWhere('title', '=', 'Admin');
            $query->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO', $texto)
            ->orWhereRaw("LOWER(NOMBRE || ' ' || PATERNO || ' ' || MATERNO) LIKE LOWER('%".$texto."%')");
        })
        ->limit(15)
        ->get();
        return $rows;
    }
    public static function showPersonAdmision($id_persona)
    {
        $row = DB::connection('jose')->table('SCHOOL_PERSONA_ADMISION')
        ->select(
            'ID_PERSONA',
            'ID_PERIODO',
            'ID_PNIVEL',
            'ID_PNGRADO',
            'ID_USER',
            'FECHA_REGISTRO',
            'FECHA_UPDATE',
            'ID_PNGSECCION',
            'ID_RESP_FINANCIERO',
            DB::raw(
                "
                (
                    SELECT ID_NIVEL 
                    FROM SCHOOL_PERIODO_NIVEL
                    WHERE SCHOOL_PERIODO_NIVEL.ID_PNIVEL = SCHOOL_PERSONA_ADMISION.ID_PNIVEL
                ) ID_NIVEL,
                (
                    SELECT ID_GRADO 
                    FROM SCHOOL_PERIODO_NGRADO
                    WHERE SCHOOL_PERIODO_NGRADO.ID_PNGRADO = SCHOOL_PERSONA_ADMISION.ID_PNGRADO
                ) ID_GRADO
                "
            )
        )
        ->where('ID_PERSONA', $id_persona)
        ->first();
        return $row;
    }
    public static function addPersonAdmision($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_ADMISION')->insert($data);
        return $result;
    }
    public static function editPersonAdmision($data, $id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_ADMISION')
        ->where('ID_PERSONA',$id_persona)
        ->update($data);
        return $result;
    }
    public static function existPersonAdmision($id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_ADMISION')
        ->where('ID_PERSONA',$id_persona)
        ->exists();
        return $result;
    }
    public static function admisionRealizoPago($id_persona)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_ADMISION')
        ->select('SCHOOL_PERSONA_ADMISION.REALIZO_PAGO')
        ->where('SCHOOL_PERSONA_ADMISION.ID_PERSONA',$id_persona)
        ->first();
        return $result;
    }
    public static function listUnion()
    {
        $rows = DB::connection('eliseo')->table('CONTA_CORPORACION')
            ->select(
                'CONTA_CORPORACION.ID_CORPORACION','CONTA_CORPORACION.SIGLAS'
            )
            ->where('CONTA_CORPORACION.ID_CORPORACION',2) // 2=UPN
            ->get();
        return $rows;
    }
    public static function listCampo($id_union)
    {

        $rows = DB::connection('eliseo')->table('CONTA_ENTIDAD')
            ->join('CONTA_EMPRESA','CONTA_ENTIDAD.ID_EMPRESA','=','CONTA_EMPRESA.ID_EMPRESA')
            ->select(
                'CONTA_ENTIDAD.ID_ENTIDAD','CONTA_ENTIDAD.NOMBRE'
            )
            ->where('CONTA_EMPRESA.ID_CORPORACION',$id_union)
            ->whereIn('CONTA_ENTIDAD.ID_TIPOENTIDAD',[5, 12])
            ->get();
        return $rows;
    }
    public static function listDeparment($id_campo)
    {
        $rows = DB::connection('eliseo')->table('CONTA_ENTIDAD_DEPTO')
            ->select(
                'CONTA_ENTIDAD_DEPTO.ID_DEPTO','CONTA_ENTIDAD_DEPTO.NOMBRE'
            )
            ->where('CONTA_ENTIDAD_DEPTO.ID_ENTIDAD',$id_campo)
            ->where('CONTA_ENTIDAD_DEPTO.ES_EMPRESA',1)
            ->get();
        return $rows;
    }
    public static function listInstitucionParametros($union,$campo,$departamento,$institucion){
        if(!empty($institucion)){$sql_institucion="WHERE A.ID_INSTITUCION=$institucion";}else{$sql_institucion="WHERE A.ID_INSTITUCION >0";}
        if(!empty($union)){$sql_union=" AND A.ID_UNION=$union";}else{$sql_union="";}
        if(!empty($campo)){$sql_campo=" AND A.ID_CAMPO=$campo";}else{$sql_campo="";}
        if(!empty($departamento)){$sql_depto=" AND A.ID_DEPTO=$departamento";}else{$sql_depto="";}
       

        $query = "SELECT
                    A.ID_INSTITUCION,
                    A.ID_CAMPO,
                    A.CODIGO,
                    A.NOMBRE,
                    A.CODIGO_UGEL,
                    A.UGEL,
                    A.DIRECCION,
                    A.TELEFONO,
                    A.ESTADO,
                    B.NOMBRE AS CAMPO
                    FROM JOSE.SCHOOL_INSTITUCION A 
                    INNER JOIN ELISEO.CONTA_ENTIDAD B ON A.ID_CAMPO=B.ID_ENTIDAD 
                    ".$sql_institucion.$sql_union.$sql_campo.$sql_depto;
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listInstitucion()
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_INSTITUCION')
            ->join('ELISEO.CONTA_ENTIDAD','SCHOOL_INSTITUCION.ID_CAMPO','=','CONTA_ENTIDAD.ID_ENTIDAD')
            ->select(
                'SCHOOL_INSTITUCION.ID_INSTITUCION',
                'SCHOOL_INSTITUCION.ID_UNION',
                'SCHOOL_INSTITUCION.ID_CAMPO',
                'SCHOOL_INSTITUCION.ID_DEPTO',
                'SCHOOL_INSTITUCION.CODIGO',
                'SCHOOL_INSTITUCION.NOMBRE',
                'CONTA_ENTIDAD.NOMBRE AS CAMPO',
                'SCHOOL_INSTITUCION.CODIGO_UGEL',
                'SCHOOL_INSTITUCION.UGEL',
                'SCHOOL_INSTITUCION.DIRECCION',
                'SCHOOL_INSTITUCION.TELEFONO',
                'SCHOOL_INSTITUCION.ESTADO',
                'SCHOOL_INSTITUCION.ID_PAIS',
                'SCHOOL_INSTITUCION.ID_DEPARTAMENTO',
                'SCHOOL_INSTITUCION.ID_PROVINCIA',
                'SCHOOL_INSTITUCION.ID_DISTRITO'
            )
            ->get();
        return $rows;
    }
    public static function institucionById($id_institucion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_INSTITUCION')
            ->select(
                'SCHOOL_INSTITUCION.ID_INSTITUCION',
                'SCHOOL_INSTITUCION.ID_UNION',
                'SCHOOL_INSTITUCION.ID_CAMPO',
                'SCHOOL_INSTITUCION.ID_DEPTO',
                'SCHOOL_INSTITUCION.CODIGO',
                'SCHOOL_INSTITUCION.NOMBRE',
                'SCHOOL_INSTITUCION.CODIGO_UGEL',
                'SCHOOL_INSTITUCION.UGEL',
                'SCHOOL_INSTITUCION.DIRECCION',
                'SCHOOL_INSTITUCION.TELEFONO',
                'SCHOOL_INSTITUCION.ESTADO',
                'SCHOOL_INSTITUCION.ID_PAIS',
                'SCHOOL_INSTITUCION.ID_DEPARTAMENTO',
                'SCHOOL_INSTITUCION.ID_PROVINCIA',
                'SCHOOL_INSTITUCION.ID_DISTRITO'
            )
            ->where('ID_INSTITUCION',$id_institucion)
            ->first();
        return $rows;
    }
    public static function updateSchoolInstitucion($data,$id_institucion)
    {
        $result = DB::connection('jose')->table('SCHOOL_INSTITUCION')
        ->where('SCHOOL_INSTITUCION.ID_INSTITUCION',$id_institucion)
        ->update($data);
        return $result;
    }
    public static function updateSchoolInstitucionEstado($data,$id_institucion)
    {
        $result = DB::connection('jose')->table('SCHOOL_INSTITUCION')
        ->where('SCHOOL_INSTITUCION.ID_INSTITUCION',$id_institucion)
        ->update($data);
        return $result;
    }
    
    public static function addSchoolInstitucion($data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_INSTITUCION","CODIGO")+1;
        $data["ID_INSTITUCION"] = $id;
        $data["CODIGO"] = self::generaCodigoTexto(DB::connection('jose'), "SCHOOL_INSTITUCION","CODIGO");
        $result = DB::connection('jose')->table('SCHOOL_INSTITUCION')->insert($data);
        return $result;
    }

    public static function addSchoolInstitucionEntrada($data)
    {
        $result = DB::connection('JOSE')->table('SCHOOL_INSTITUCION_ENTRADA')->insert($data);
        return $result;
    }

    public static function updateSchoolInstitucionEntrada($id_ientrada, $data)
    {
        $result = DB::connection('JOSE')->table('SCHOOL_INSTITUCION_ENTRADA')
        ->where('SCHOOL_INSTITUCION_ENTRADA.ID_IENTRADA',$id_ientrada)
        ->update($data);
        return $result;
    }

    public static function existeSchoolInstitucion($union,$campo,$depto)
    {
        $result = DB::connection('jose')->table('SCHOOL_INSTITUCION')
        ->select('CODIGO')
        ->where('id_union', $union)
        ->where('id_campo', $campo)
        ->where('id_depto', $depto)
        ->exists();
    return $result;
    }
    public static function deleteSchoolInstitucion($id_institucion)
    {
        $result = DB::connection('eliseo')->table('SCHOOL_INSTITUCION')->where('SCHOOL_INSTITUCION.ID_INSTITUCION',$id_institucion)->delete();
        return $result;
    }
    public static function listPersonasCampo($id_entidad)
    {
        $query = "   SELECT A.ID_ENTIDAD,A.ID_PERSONA,P.NOMBRE,P.PATERNO,P.MATERNO,PD.NUM_DOCUMENTO 
        FROM ELISEO.CONTA_ENTIDAD_USUARIO A
                    INNER JOIN MOISES.PERSONA P ON A.ID_PERSONA=P.ID_PERSONA
                    INNER JOIN MOISES.PERSONA_DOCUMENTO PD ON A.ID_PERSONA=PD.ID_PERSONA
                    WHERE A.ESTADO=1 
                    AND A.ID_ENTIDAD=$id_entidad 
                    --AND A.ID_ENTIDAD IN(
                       -- SELECT I.ID_CAMPO FROM  JOSE.SCHOOL_INSTITUCION I
                   -- )
                    AND A.ID_PERSONA NOT IN(SELECT T.ID_PERSONA FROM  JOSE.SCHOOL_TRABAJADOR T 
                            INNER JOIN JOSE.SCHOOL_INSTITUCION I ON T.ID_INSTITUCION=I.ID_INSTITUCION
                            WHERE i.id_campo = A.ID_ENTIDAD
                            ) 
                    AND PD.ID_TIPODOCUMENTO=1 
                    GROUP BY A.ID_ENTIDAD,A.ID_PERSONA,P.NOMBRE,P.PATERNO,P.MATERNO,PD.NUM_DOCUMENTO ORDER BY P.NOMBRE,P.PATERNO,P.MATERNO";
        $oQuery = DB::select($query);  
        return $oQuery;
    }
    public static function addCategoriaTrabajador($data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_CATEGORIA_TRABAJADOR","ID_CATEGORIA")+1;
        $data['ID_CATEGORIA']=$id;
        $result = DB::connection('jose')->table('SCHOOL_CATEGORIA_TRABAJADOR')->insert($data);
        return $data;
    }
    public static function deleteTrabajador($id_trabajador,$id_categoria)
    {
        $result = DB::connection('jose')->table('SCHOOL_TRABAJADOR')
            ->where('ID_PERSONA', '=', $id_trabajador)
            ->where('ID_CATEGORIA', '=', $id_categoria)
            ->delete();
        return $result;
    }
    public static function deleteTrabajadores($lista)
    {
        foreach($lista as $dato){
            $result = DB::connection('jose')->table('SCHOOL_TRABAJADOR')
                        ->where('ID_PERSONA', '=', $dato)
                        ->delete();
        }
        return true;
    }
    public static function listCategoriaTrabajador()
    {
        $rows = DB::connection('jose')->table('SCHOOL_CATEGORIA_TRABAJADOR')
        ->select(
            'SCHOOL_CATEGORIA_TRABAJADOR.ID_CATEGORIA',
            'SCHOOL_CATEGORIA_TRABAJADOR.NOMBRE'
        )
        ->get();
    return $rows;
    }
     public static function addTrabajador($data,$lista)
    {
        foreach($lista as $dato){
            $data['id_persona']=$dato;
            DB::connection('jose')->table('SCHOOL_TRABAJADOR')->insert($data);
        }
        
        return true;
    }
    public static function listTrabajador($id_categoria)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_TRABAJADOR')
        ->join('moises.VW_PERSONA_NATURAL','SCHOOL_TRABAJADOR.ID_PERSONA','=','VW_PERSONA_NATURAL.ID_PERSONA')
        ->join('JOSE.SCHOOL_CATEGORIA_TRABAJADOR','SCHOOL_TRABAJADOR.ID_CATEGORIA','=','SCHOOL_CATEGORIA_TRABAJADOR.ID_CATEGORIA')
        ->select(
            'VW_PERSONA_NATURAL.ID_PERSONA',
            'VW_PERSONA_NATURAL.PATERNO',
            'VW_PERSONA_NATURAL.MATERNO',
            'VW_PERSONA_NATURAL.NOMBRE',
            'VW_PERSONA_NATURAL.NUM_DOCUMENTO',
            'SCHOOL_TRABAJADOR.ID_CATEGORIA',
            'SCHOOL_CATEGORIA_TRABAJADOR.NOMBRE AS CATEGORIA'
        )
        ->where('SCHOOL_TRABAJADOR.ID_CATEGORIA',$id_categoria)
        ->where('VW_PERSONA_NATURAL.ID_TIPODOCUMENTO',1)
        ->get();
        return $rows;
    }
    public static function listBimestreOpenClose($id_periodo)
    {
        /*$rows = DB::table('SCHOOL_PERIODO')
        ->select(
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC',
            'SCHOOL_PERIODO.ID_PERIODO',
            'SCHOOL_BIMESTRE.ID_BIMESTRE',
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE AS ID_BIMESTRE2',
            'SCHOOL_BIMESTRE.NOMBRE AS BIMESTRE',
            DB::raw("TO_CHAR(SCHOOL_PERIODO.FECHA_OPEN, 'DD-MM-YYYY') AS FECHA_OPEN"),
            DB::raw("TO_CHAR(SCHOOL_PERIODO.FECHA_CLOSE, 'DD-MM-YYYY') AS FECHA_CLOSE"),
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_OPEN',
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ESTADO_AUTOMATICO',
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ESTADO_OPCION',
            DB::raw("TO_CHAR(SYSDATE,'DD-MM-YYYY') AS FECHA_SISTEMA"),
            DB::raw("to_number(to_char(SCHOOL_PERIODO.FECHA_CLOSE, 'yyyymmddhh24miss')) AS FECHA1") ,
            DB::raw("to_number(to_char(sysdate, 'yyyymmddhh24miss')) AS FECHA2")
        )
        ->join('SCHOOL_PERIODO_CALENDARIO','SCHOOL_PERIODO.ID_PERIODO','=','SCHOOL_PERIODO_CALENDARIO.ID_PERIODO')
        ->join('SCHOOL_BIMESTRE','SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE','=','SCHOOL_BIMESTRE.ID_BIMESTRE')
        ->leftJoin('SCHOOL_BIMESTRE_OPEN_CLOSE','SCHOOL_BIMESTRE.ID_BIMESTRE','=','SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE')
        ->leftJoin('SCHOOL_BIMESTRE_OPEN_CLOSE', function($leftJoin) {
            $leftJoin->on('SCHOOL_PERIODO.ID_PERIODO', '=', 'SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERIODO')
           ->where('SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE', '=', 1);
        })
        ->where('SCHOOL_PERIODO.ID_PERIODO',$id_periodo)
        ->orderBy('SCHOOL_BIMESTRE.ID_BIMESTRE','ASC')
        ->get();
        return $rows;*/
        $query = "SELECT SCHOOL_BIMESTRE_OPEN_CLOSE.ITERACION,SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC, SCHOOL_PERIODO.ID_PERIODO, 
                SCHOOL_BIMESTRE.ID_BIMESTRE,SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE as ID_BIMESTRE2, 
                SCHOOL_BIMESTRE.NOMBRE as BIMESTRE,TO_CHAR(SCHOOL_BIMESTRE_OPEN_CLOSE.FECHA_OPEN, 'DD-MM-YYYY') AS FECHA_OPEN_OC,
                TO_CHAR(SCHOOL_PERIODO.FECHA_OPEN, 'DD-MM-YYYY') AS FECHA_OPEN, 
                TO_CHAR(SCHOOL_BIMESTRE_OPEN_CLOSE.FECHA_CLOSE, 'DD-MM-YYYY') AS FECHA_CLOSE_OC,
                TO_CHAR(SCHOOL_PERIODO.FECHA_CLOSE, 'DD-MM-YYYY') AS FECHA_CLOSE,
                SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_OPEN,
                FC_NOMBRE_PERSONA(SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_OPEN)PERSONA_OPEN,
                FC_NOMBRE_PERSONA(SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_CLOSE)PERSONA_CLOSE,
                FC_NOMBRE_PERSONA(SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_REABIERTO)PERSONA_REABIERTO,
                SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_CLOSE,
                SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERSONA_REABIERTO,
                SCHOOL_BIMESTRE_OPEN_CLOSE.ESTADO_AUTOMATICO, SCHOOL_BIMESTRE_OPEN_CLOSE.ESTADO_OPCION,
                TO_CHAR(SYSDATE,'DD-MM-YYYY') AS FECHA_SISTEMA, to_number(to_char(SCHOOL_PERIODO.FECHA_CLOSE, 'yyyymmddhh24miss')) AS FECHA1,
                to_number(to_char(sysdate, 'yyyymmddhh24miss')) AS FECHA2 
                from JOSE.SCHOOL_PERIODO 
                inner join JOSE.SCHOOL_PERIODO_CALENDARIO on SCHOOL_PERIODO.ID_PERIODO = SCHOOL_PERIODO_CALENDARIO.ID_PERIODO 
                inner join JOSE.SCHOOL_BIMESTRE on SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE = SCHOOL_BIMESTRE.ID_BIMESTRE 
                left join JOSE.SCHOOL_BIMESTRE_OPEN_CLOSE on SCHOOL_BIMESTRE.ID_BIMESTRE = SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE 
                left join JOSE.SCHOOL_BIMESTRE_OPEN_CLOSE on SCHOOL_PERIODO.ID_PERIODO = SCHOOL_BIMESTRE_OPEN_CLOSE.ID_PERIODO 
                        and SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE = SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE 
                where SCHOOL_PERIODO.ID_PERIODO = '".$id_periodo."' order by SCHOOL_BIMESTRE.ID_BIMESTRE asc";
        $oQuery = DB::select($query);        
        return $oQuery;
        
    }
    public static function listBimestreByEstadoAutomatico()
    {
        $rows = DB::connection('jose')->table('SCHOOL_BIMESTRE_OPEN_CLOSE')
        ->select(
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC',
            'SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE',
            'SCHOOL_BIMESTRE.NOMBRE AS BIMESTRE'
        )
        ->join('SCHOOL_BIMESTRE','SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE','=','SCHOOL_BIMESTRE.ID_BIMESTRE')
        ->where('SCHOOL_BIMESTRE_OPEN_CLOSE.ESTADO_AUTOMATICO',1)
        ->get();
        return $rows;
    }
    public static function addBimestreOpenClose($data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_BIMESTRE_OPEN_CLOSE","ID_BIMESTRE_OC")+1;
        $data['ID_BIMESTRE_OC']=$id;
        $result = DB::connection('jose')->table('SCHOOL_BIMESTRE_OPEN_CLOSE')->insert($data);
        return $data;
    }
    public static function updateBimestreOpenClose($data,$id_bimestre_oc,$id_bimestre)
    {
        $iteracion=DB::connection('jose')->table("SCHOOL_BIMESTRE_OPEN_CLOSE")
        ->where('SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC',$id_bimestre_oc)
        ->where('SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE',$id_bimestre)->max("ITERACION")+1;
        $data['ITERACION']=$iteracion;
        $result = DB::connection('jose')->table('SCHOOL_BIMESTRE_OPEN_CLOSE')
        ->where('SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC',$id_bimestre_oc)
        ->update($data);
        return $result;
    }
    public static function existBimestreOpenClose($id_bimestre_oc)
    {
        $result = DB::connection('jose')->table('SCHOOL_BIMESTRE_OPEN_CLOSE')
        ->select('SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC')
        ->where('SCHOOL_BIMESTRE_OPEN_CLOSE.ID_BIMESTRE_OC',$id_bimestre_oc)
        ->exists();
        return $result;
    }
    public static function listPeriodoOpenClose()
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->select(
            'SCHOOL_PERIODO.ID_PERIODO',
            'SCHOOL_PERIODO.ANHO_PERIODO',
            DB::raw("CASE ESTADO 
            WHEN 'C' THEN 'CERRADO'
            WHEN 'O' THEN 'ABIERTO' 
            END AS ESTADO")
        )
        ->where('ESTADO','O')
        ->get();
        return $rows;
    }
    public static function listPeriodoNivel($id_periodo,$id_institucion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NIVEL')
        ->select(
            'SCHOOL_PERIODO_NIVEL.ID_PNIVEL',
            'SCHOOL_PERIODO_NIVEL.ID_PERIODO',
            'SCHOOL_PERIODO_NIVEL.ID_NIVEL',
            'SCHOOL_NIVEL.NOMBRE' 
        )
        ->join('SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
        ->where('SCHOOL_PERIODO_NIVEL.ID_PERIODO',$id_periodo)
        ->where('SCHOOL_PERIODO_NIVEL.ID_INSTITUCION',$id_institucion)
        ->get();
        return $rows;
    }
    public static function listPeriodoNGrado($id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->select(
            'SCHOOL_PERIODO_NGRADO.ID_PNGRADO',
            'SCHOOL_PERIODO_NGRADO.ID_PNIVEL',
            'SCHOOL_PERIODO_NGRADO.ID_GRADO',
            'SCHOOL_GRADO.NOMBRE' 
        )
        ->join('SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->where('SCHOOL_PERIODO_NGRADO.ID_PNIVEL',$id_pnivel)
        ->get();
        return $rows; 
    }
    public static function listPeriodoNGradoByPeriodo($id_periodo,$id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->select(
            'SCHOOL_NIVEL.NOMBRE AS NIVEL',
            'SCHOOL_GRADO.NOMBRE AS GRADO',
            'SCHOOL_GRADO.CORTO AS CORTO',
            'SCHOOL_PERIODO_NGRADO.ID_PNGRADO'
        )
        ->join('SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->join('SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->join('SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
        ->where('SCHOOL_PERIODO_NIVEL.ID_PERIODO',$id_periodo)
        ->orderBy('SCHOOL_GRADO.ORDEN')
        ->get();
        return $rows; 
    }
    public static function periodoNGrado($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->select(
            'SCHOOL_PERIODO_NGRADO.ID_PNGRADO',
            'SCHOOL_PERIODO_NGRADO.ID_PMES'
        )
        ->where('SCHOOL_PERIODO_NGRADO.ID_PNGRADO',$id_pngrado)
        ->first();
        return $rows; 
    }
    public static function listPeriodoNGSeccion($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->select(
            'SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION',
            'SCHOOL_PERIODO_NGSECCION.ID_PNGRADO',
            'SCHOOL_PERIODO_NGSECCION.ID_SECCION',
            'SCHOOL_SECCION.NOMBRE' 
        )
        ->join('SCHOOL_SECCION','SCHOOL_PERIODO_NGSECCION.ID_SECCION','=','SCHOOL_SECCION.ID_SECCION')
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGRADO',$id_pngrado)
        ->get();
        return $rows; 
    }
    public static function listPeriodoNGNotSeccion($id_pngrado,$id_pngseccion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->select(
            'SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION',
            'SCHOOL_PERIODO_NGSECCION.ID_PNGRADO',
            'SCHOOL_PERIODO_NGSECCION.ID_SECCION',
            'SCHOOL_SECCION.NOMBRE' 
        )
        ->join('SCHOOL_SECCION','SCHOOL_PERIODO_NGSECCION.ID_SECCION','=','SCHOOL_SECCION.ID_SECCION')
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGRADO',$id_pngrado)
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION','!=',$id_pngseccion)
        ->get();
        return $rows; 
    }
    public static function searchEmployee($person,$categoria)
    { 
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_TRABAJADOR')
        ->select(
            'JOSE.SCHOOL_TRABAJADOR.ID_PERSONA',
            'MOISES.PERSONA.NOMBRE',
            'MOISES.PERSONA.PATERNO',
            'MOISES.PERSONA.MATERNO'
        )
        ->join('MOISES.PERSONA','SCHOOL_TRABAJADOR.ID_PERSONA','=','PERSONA.ID_PERSONA')
        ->where('SCHOOL_TRABAJADOR.ID_CATEGORIA',$categoria)
        ->where(function($query) use($person){
            $query->where(DB::raw("lower(PERSONA.NOMBRE)"), 'LIKE',"%$person%")
            ->orWhere(DB::raw("lower(PERSONA.PATERNO)"), 'LIKE',"%$person%")
            ->orWhere(DB::raw("lower(PERSONA.MATERNO)"), 'LIKE',"%$person%");
        })->get();
        return $rows;
    }
    public static function addSeccionPersonal($data)
    { 
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_SPERSONAL","ID_SPERSONAL")+1;
        $data["ID_SPERSONAL"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_SPERSONAL')->insert($data);
        return $id;
    }
    public static function editSeccionPersonal($data,$id_spersonal)
    { 
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_SPERSONAL')->where('SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',$id_spersonal)->update($data);
        return $id_spersonal;
    }
    public static function listSPersonal()
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERIODO_SPERSONAL')
        ->select(
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',
            'SCHOOL_PERIODO_SPERSONAL.ID_PERIODO',
            'SCHOOL_PERIODO_SPERSONAL.ID_PNIVEL',
            'SCHOOL_PERIODO_SPERSONAL.ID_PNGRADO',
            'SCHOOL_PERIODO_SPERSONAL.ID_PNGSECCION',
            'SCHOOL_PERIODO_SPERSONAL.ID_PERSONA',
            DB::raw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRES"),
            'SCHOOL_PERIODO.ANHO_PERIODO AS PERIODO',
            'SCHOOL_NIVEL.NOMBRE AS NIVEL',
            'SCHOOL_GRADO.NOMBRE AS GRADO',
            'SCHOOL_SECCION.NOMBRE AS SECCION'
        )
        ->join('MOISES.PERSONA','SCHOOL_PERIODO_SPERSONAL.ID_PERSONA','=','PERSONA.ID_PERSONA')
        ->join('JOSE.SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_SPERSONAL.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->join('JOSE.SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
        ->join('JOSE.SCHOOL_PERIODO','SCHOOL_PERIODO_SPERSONAL.ID_PERIODO','=','SCHOOL_PERIODO.ID_PERIODO')
        ->join('JOSE.SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_SPERSONAL.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('JOSE.SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->join('JOSE.SCHOOL_PERIODO_NGSECCION','SCHOOL_PERIODO_SPERSONAL.ID_PNGSECCION','=','SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION')
        ->join('JOSE.SCHOOL_SECCION','SCHOOL_PERIODO_NGSECCION.ID_SECCION','=','SCHOOL_SECCION.ID_SECCION')
        ->get();
        return $rows;
    }
    public static function PersonalSeccionById($id_spersonal)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERIODO_SPERSONAL')
        ->select(
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',
            DB::raw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRES")
        )
        ->join('MOISES.PERSONA','SCHOOL_PERIODO_SPERSONAL.ID_PERSONA','=','PERSONA.ID_PERSONA')
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',$id_spersonal)
        ->first();
        return $rows;
    }
    public static function PersonalSeccionTipoById($id_pngseccion,$categoria,$tipo)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERIODO_SPERSONAL')
        ->select(
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',
            'SCHOOL_PERIODO_SPERSONAL.ID_PERSONA',
            DB::raw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRES")
        )
        ->join('MOISES.PERSONA','SCHOOL_PERIODO_SPERSONAL.ID_PERSONA','=','PERSONA.ID_PERSONA')
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PNGSECCION',$id_pngseccion)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_CAT_TRABAJADOR',$categoria)
        ->where('SCHOOL_PERIODO_SPERSONAL.TIPO',$tipo)
        ->first();
        return $rows;
    }
    public static function existPersonalSeccionFirst($id_periodo, $id_pnivel, $id_pngrado, $id_pngseccion, $id_persona, $tipo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_SPERSONAL')
        ->select(
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL'
        )
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PERIODO',$id_periodo)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PNIVEL',$id_pnivel)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PNGRADO',$id_pngrado)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PNGSECCION',$id_pngseccion)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PERSONA',$id_persona)
        ->where('SCHOOL_PERIODO_SPERSONAL.TIPO',$tipo)
        ->first();
        if(empty($rows)){
            return 0;
        }else{
            return $rows;
        }
    }
    public static function existPersonalGradoFirst($id_periodo, $id_pnivel, $id_pngrado, $id_persona, $tipo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_SPERSONAL')
        ->select(
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL'
        )
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PERIODO',$id_periodo)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PNIVEL',$id_pnivel)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PNGRADO',$id_pngrado)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PERSONA',$id_persona)
        ->where('SCHOOL_PERIODO_SPERSONAL.TIPO',$tipo)
        ->first();
        if(empty($rows)){
            return 0;
        }else{
            return $rows;
        }
    }
    public static function listCursosBySDocente($id_sdocente, $id_institucion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')
        ->select(
            'SCHOOL_PERIODO_DCURSO.ID_DCURSO',
            'SCHOOL_PERIODO_DCURSO.ID_PNGCURSO',
            'SCHOOL_PERIODO_DCURSO.ID_SPERSONAL',
            'SCHOOL_CURSO.NOMBRE AS CURSO',
            'SCHOOL_PERIODO_NGCURSO.HORAS'
        )
        ->join('SCHOOL_PERIODO_NGCURSO','SCHOOL_PERIODO_DCURSO.ID_PNGCURSO','=','SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO')
        ->join('SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_NGCURSO.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->join('SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        ->where('SCHOOL_PERIODO_DCURSO.ID_SPERSONAL',$id_sdocente)
        ->where('SCHOOL_PERIODO_NIVEL.ID_INSTITUCION',$id_institucion)
        ->get();
        return $rows; 
    }
    public static function listCursosByPngseccion($id_pngseccion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGCURSO')
        ->select(
            'SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO',
            'SCHOOL_CURSO.NOMBRE AS CURSO',
            'SCHOOL_PERIODO_NGCURSO.HORAS'
        )
        ->join('SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        ->join('SCHOOL_PERIODO_NGSECCION','SCHOOL_PERIODO_NGCURSO.ID_PNGRADO','=','SCHOOL_PERIODO_NGSECCION.ID_PNGRADO')
        ->whereNotIn('SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO',function($query) use ($id_pngseccion) {
            $query->select('SCHOOL_PERIODO_DCURSO.ID_PNGCURSO')->from('SCHOOL_PERIODO_DCURSO')
            ->where('SCHOOL_PERIODO_DCURSO.ID_PNGSECCION',$id_pngseccion);
         })
        ->whereNull('SCHOOL_PERIODO_NGCURSO.ID_CPARENT')
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION',$id_pngseccion)
        ->where('SCHOOL_PERIODO_NGCURSO.ES_TALLER_ELECTIVO','N')
        ->get();
        return $rows; 
    }
    public static function listCursosByPNGradoElectivo($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGCURSO')
        ->select(
            'SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO',
            'SCHOOL_CURSO.NOMBRE AS CURSO',
            'SCHOOL_PERIODO_NGCURSO.HORAS'
        )
        ->join('SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        ->whereNotIn('SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO',function($query) {
            $query->select('SCHOOL_PERIODO_DCURSO.ID_PNGCURSO')->from('SCHOOL_PERIODO_DCURSO');
         })
        ->whereNull('SCHOOL_PERIODO_NGCURSO.ID_CPARENT')
        ->where('SCHOOL_PERIODO_NGCURSO.ID_PNGRADO',$id_pngrado)
        ->where('SCHOOL_PERIODO_NGCURSO.ES_TALLER_ELECTIVO','S')
        ->get();
        return $rows; 
    }
    public static function listCursosByIdCParent($id_cparent)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGCURSO')
        ->select(
            'SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO',
            'SCHOOL_CURSO.NOMBRE AS CURSO',
            'SCHOOL_PERIODO_NGCURSO.HORAS',
            'SCHOOL_PERIODO_DCURSO.ID_DCURSO',
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',
            'SCHOOL_PERIODO_SPERSONAL.ID_PERSONA'
        )
        ->leftJoin('SCHOOL_PERIODO_DCURSO','SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO','=','SCHOOL_PERIODO_DCURSO.ID_PNGCURSO')
        ->leftJoin('SCHOOL_PERIODO_SPERSONAL','SCHOOL_PERIODO_DCURSO.ID_SPERSONAL','=','SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL')
        ->join('SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        // ->where('SCHOOL_PERIODO_NGCURSO.ID_CPARENT',$id_cparent)
        ->where('SCHOOL_PERIODO_NGCURSO.PARENT',$id_cparent)
        ->get();
        return $rows; 
    }
    public static function addCursoDocente($data,$lista)
    { 
        foreach($lista as $dato){
            $id = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_DCURSO","ID_DCURSO")+1;
            $data["ID_PNGCURSO"] =$dato;
            $data["ID_DCURSO"] = $id;
            $result = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')->insert($data);
        }
        return true;
    }
    public static function addSubCursoDocente($data)
    { 
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_DCURSO","ID_DCURSO")+1;
        $data["ID_DCURSO"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')->insert($data);
        return $result;
    }
    public static function editSubCursoDocente($data,$id_dcurso)
    { 
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')
        ->where('SCHOOL_PERIODO_DCURSO.ID_DCURSO',$id_dcurso)
        ->update($data);
        return $result;
    }
    public static function existSubCursoByIdPersIdPNGCurso($id_dcurso)
    { 
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')
        ->where('SCHOOL_PERIODO_DCURSO.ID_DCURSO',$id_dcurso)
        ->exists();
        return $result;
    }
    public static function deleteCursosByIdSDocente($lista,$id_sdocente)
    {
        foreach($lista as $dato){
            $exist = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')
            ->where('ID_DCPARENT', '=', $dato)
            ->exists();
            if($exist){
                $result = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')
                ->where('ID_DCPARENT', '=', $dato)
                ->delete();
            }else{
                $result = DB::connection('jose')->table('SCHOOL_PERIODO_DCURSO')
                ->where('ID_DCURSO', '=', $dato)
                ->where('SCHOOL_PERIODO_DCURSO.ID_SPERSONAL',$id_sdocente)
                ->delete();
            } 
        }
        return $result;
    }
    public static function gradoSeccionById($id_pngseccion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->select(
            'SCHOOL_GRADO.NOMBRE AS GRADO',
            'SCHOOL_SECCION.NOMBRE AS SECCION'
        )
        ->join('SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_NGSECCION.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->join('SCHOOL_SECCION','SCHOOL_PERIODO_NGSECCION.ID_SECCION','=','SCHOOL_SECCION.ID_SECCION')
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION',$id_pngseccion)
        ->first();
        return $rows; 
    }
    public static function ngSeccionById($id_pngseccion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->leftJoin('SCHOOL_MATRICULA','SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION','=','SCHOOL_MATRICULA.ID_PNGSECCION')
        ->select(
            'SCHOOL_PERIODO_NGSECCION.NRO_CUPO AS CUPO',
            DB::raw("COUNT(SCHOOL_MATRICULA.ID_PNGSECCION) AS NVACANTE")
        )
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION',$id_pngseccion)
        ->groupBy('SCHOOL_PERIODO_NGSECCION.NRO_CUPO')
        ->first();
        return $rows; 
    }
    public static function listAlumnosBySeccion($id_pngseccion)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_MATRICULA')
        ->select(
            'SCHOOL_MATRICULA.ID_MATRICULA',
            'SCHOOL_MATRICULA.ID_ALUMNO',
            DB::raw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRES")
        )
        ->join('JOSE.SCHOOL_ALUMNO','SCHOOL_MATRICULA.ID_ALUMNO','=','SCHOOL_ALUMNO.ID_ALUMNO')
        ->join('MOISES.PERSONA','SCHOOL_ALUMNO.ID_ALUMNO','=','PERSONA.ID_PERSONA')
        ->where('JOSE.SCHOOL_MATRICULA.ID_PNGSECCION',$id_pngseccion)
        ->get();
        return $rows; 
    }
    public static function updateAlumnoSeccion($data,$idsMatricula){
        $res= implode(',',$idsMatricula);
        DB::update(DB::RAW("update JOSE.school_matricula set id_pngseccion =".$data['id_pngseccion']." where id_matricula in(".$res.")"));
             
        return true;
    }
    public static function matriculaByIdPersonaPeriodo($id_persona,$periodo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_MATRICULA')
            ->join('SCHOOL_PERIODO_NGSECCION','SCHOOL_MATRICULA.ID_PNGSECCION','=','SCHOOL_PERIODO_NGSECCION.ID_PNGSECCION')
            ->join('SCHOOL_SECCION','SCHOOL_PERIODO_NGSECCION.ID_SECCION','=','SCHOOL_SECCION.ID_SECCION')
            ->join('SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_NGSECCION.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
            ->join('SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
            ->join('SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
            ->join('SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
            ->join('SCHOOL_ALUMNO','SCHOOL_MATRICULA.ID_ALUMNO','=','SCHOOL_ALUMNO.ID_ALUMNO')
            ->select(
                'SCHOOL_MATRICULA.ID_ALUMNO',
                'SCHOOL_NIVEL.NOMBRE AS NIVEL',
                'SCHOOL_GRADO.NOMBRE AS GRADO',
                'SCHOOL_SECCION.NOMBRE AS SECCION'
                )
            ->where('SCHOOL_ALUMNO.ID_ALUMNO',$id_persona)
            ->where('SCHOOL_MATRICULA.ID_PERIODO',$periodo)
            ->first();
        return $rows;
    }
    public static function searchAlumnoByPeriodo($person,$id_periodo)
    { 
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_ALUMNO')
        ->select(
            'SCHOOL_ALUMNO.ID_ALUMNO',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO'
        )
        ->join('JOSE.SCHOOL_MATRICULA','SCHOOL_ALUMNO.ID_ALUMNO','=','SCHOOL_MATRICULA.ID_ALUMNO')
        ->join('MOISES.PERSONA','SCHOOL_ALUMNO.ID_ALUMNO','=','PERSONA.ID_PERSONA')
        ->where(function($query) use($person){
            $query->where(DB::raw("lower(PERSONA.NOMBRE)"), 'LIKE',"%$person%")
            ->orWhere(DB::raw("lower(PERSONA.PATERNO)"), 'LIKE',"%$person%")
            ->orWhere(DB::raw("lower(PERSONA.MATERNO)"), 'LIKE',"%$person%");
        })
        ->where('SCHOOL_MATRICULA.ID_PERIODO',$id_periodo)
        ->get();
        return $rows;
    }
    public static function searchAlumnoReserva($id_persona)
    { 
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_RESERVA')
        ->select(
            'SCHOOL_RESERVA.ID_RESERVA',
            'SCHOOL_RESERVA.ID_ALUMNO',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO',
            'SCHOOL_PERIODO.ANHO_PERIODO AS PERIODO',
            'SCHOOL_NIVEL.NOMBRE AS NIVEL',
            'SCHOOL_GRADO.NOMBRE AS GRADO',
            'SCHOOL_RESERVA.CONFIRMADO_CH',
            'SCHOOL_RESERVA.CONFIRMADO_FM',
            'SCHOOL_RESERVA.CONFIRMADO_MOV',
            'SCHOOL_RESERVA.CONFIRMADO_AF'

        )
        ->join('JOSE.SCHOOL_PERIODO','SCHOOL_RESERVA.ID_PERIODO','=','SCHOOL_PERIODO.ID_PERIODO')
        ->join('JOSE.SCHOOL_PERIODO_NGRADO','SCHOOL_RESERVA.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('JOSE.SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->join('JOSE.SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->join('JOSE.SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
        ->join('MOISES.PERSONA','SCHOOL_RESERVA.ID_ALUMNO','=','PERSONA.ID_PERSONA')
        ->where('SCHOOL_RESERVA.ID_ALUMNO',$id_persona)
        ->where('SCHOOL_PERIODO.ESTADO','O')
        ->first();
        return $rows;
    }
    public static function incidenciaJustificacion($id_incidencia,$tipo_evidencia,$imagenes,$data, $id_alumno)
    {
        $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA')->where('ID_INCIDENCIA',$id_incidencia)->update($data);

        $destinationPath = 'school/evidencias';
        $evidencias = array();
        if($imagenes && $result){
            foreach($imagenes as $file){
                $nombreDinamico = $id_alumno."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
                $formato = strtoupper($file->getClientOriginalExtension());
                $size =  $file->getSize();
                $url = $destinationPath."/".$nombreDinamico;
                $file->move($destinationPath, $nombreDinamico);
                $dataEvidencia = array(
                    "id_incidencia" => $id_incidencia,
                    "nombre" =>$nombreDinamico,
                    "tipo" =>$tipo_evidencia
                );
                if($file){
                    $id2 = self::getIdMax(DB::connection('jose'),"SCHOOL_INCIDENCIA_EVIDENCIA","ID_EVIDENCIA")+1;
                    $dataEvidencia["ID_EVIDENCIA"] = $id2;
                    $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')->insert($dataEvidencia);
                    $evidencias[] =$dataEvidencia;
                }
            }
            $data["envidencias"]=$evidencias;
        }
        
        return $data;
    }
    public static function addIncidencia($tipo_evidencia,$imagenes,$data)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_INCIDENCIA","ID_INCIDENCIA")+1;
        $data["ID_INCIDENCIA"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA')->insert($data);

        $destinationPath = 'school/evidencias';
        $evidencias = array();
        if($imagenes && $result){
            foreach($imagenes as $file){
                $nombreDinamico = $data['id_alumno']."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
                $formato = $file->getClientOriginalExtension();
                $size =  $file->getSize();
                $url = $destinationPath."/".$nombreDinamico;
                $file->move($destinationPath, $nombreDinamico);
                $dataEvidencia = array(
                    "id_incidencia" => $id,
                    "nombre" =>$nombreDinamico,
                    "tipo" =>$tipo_evidencia,
                    "img_size" =>$size,
                    "img_type" =>"image/".$formato
                );
                if($file){
                    $id2 = self::getIdMax(DB::connection('jose'),"SCHOOL_INCIDENCIA_EVIDENCIA","ID_EVIDENCIA")+1;
                    $dataEvidencia["ID_EVIDENCIA"] = $id2;
                    $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')->insert($dataEvidencia);
                    $evidencias[] =$dataEvidencia;
                }
            }
            $data["envidencias"]=$evidencias;
        }
        return $data;
    }
    public static function editIncidencia($tipo_evidencia,$imagenes,$data,$id_incidencia)
    {
        $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA')->where('ID_INCIDENCIA',$id_incidencia)->update($data);
        //SE BORRA LAS IMAGENES QUE EXISTEN PARA LUEGO REGISTRAR LA NUEVA IMAGENES
        $evidencias = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')
                    ->select('SCHOOL_INCIDENCIA_EVIDENCIA.ID_EVIDENCIA','SCHOOL_INCIDENCIA_EVIDENCIA.NOMBRE')
                    ->where('ID_INCIDENCIA',$id_incidencia)
                    ->get();        
        if(!empty($evidencias)){
            foreach($evidencias as $file){
                
                $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')
                            ->where('SCHOOL_INCIDENCIA_EVIDENCIA.ID_EVIDENCIA',$file->id_evidencia)
                            ->delete();
                $file_path = public_path('school/evidencias/') . $file->nombre;
                if($file_path)
                    unlink($file_path);
            }
        }
        //SE REGISTRAN A NUEVAS IMAGENES
        $destinationPath = 'school/evidencias';
        $evidencias = array();
        if($imagenes && $result){
            foreach($imagenes as $file){
                $nombreDinamico = $data['id_alumno']."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
                $formato = $file->getClientOriginalExtension();
                $size =  $file->getSize();
                $url = $destinationPath."/".$nombreDinamico;
                $file->move($destinationPath, $nombreDinamico);
                $dataEvidencia = array(
                    "id_incidencia" => $id_incidencia,
                    "nombre" =>$nombreDinamico,
                    "tipo" =>$tipo_evidencia,
                    "img_size" =>$size,
                    "img_type" =>"image/".$formato
                );
                if($file){
                    $id2 = self::getIdMax(DB::connection('jose'),"SCHOOL_INCIDENCIA_EVIDENCIA","ID_EVIDENCIA")+1;
                    $dataEvidencia["ID_EVIDENCIA"] = $id2;
                    $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')->insert($dataEvidencia);
                    $evidencias[] =$dataEvidencia;
                }
            }
            $data["envidencias"]=$evidencias;
        }
        return $data;
    }
    public static function deleteIncidenciaEvidencia($id_incidencia)
    {
        $evidencias = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')
                    ->select('SCHOOL_INCIDENCIA_EVIDENCIA.ID_EVIDENCIA','SCHOOL_INCIDENCIA_EVIDENCIA.NOMBRE')
                    ->where('ID_INCIDENCIA',$id_incidencia)
                    ->get(); 
        $lista = array();         
        if(!empty($evidencias)){
            foreach($evidencias as $file){
                
                $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')
                            ->where('SCHOOL_INCIDENCIA_EVIDENCIA.ID_EVIDENCIA',$file->id_evidencia)
                            ->delete();
                $file_path = public_path('school/evidencias/') . $file->nombre;
                unlink($file_path);
            }
        }
        $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA')->where('SCHOOL_INCIDENCIA.ID_INCIDENCIA',$id_incidencia)->delete();
        return true;
    }
    public static function listIncidenciaAlumno($id_alumno,$id_periodo,$nivel)
    {
        $rows = "";
        if($nivel=='null'){
            $rows = DB::connection('jose')->table('SCHOOL_INCIDENCIA')
            ->select(
                'SCHOOL_INCIDENCIA.ID_INCIDENCIA',
                'SCHOOL_INCIDENCIA.DESCRIPCION',
                DB::raw("TO_CHAR(SCHOOL_INCIDENCIA.HORA_REGISTRO, 'HH:MI AM') AS HORA_REGISTRO"),
                DB::raw("TO_CHAR(SCHOOL_INCIDENCIA.HORA_OCURRIO, 'HH:MI AM') AS HORA_OCURRIO"),
                'SCHOOL_INCIDENCIA.NIVEL',
                'SCHOOL_INCIDENCIA.ESTADO'
            )
            ->where('SCHOOL_INCIDENCIA.ID_ALUMNO',$id_alumno)
            ->where('SCHOOL_INCIDENCIA.ID_PERIODO',$id_periodo)
            ->get();
        }else{
            if($nivel=='J'){
                $rows =DB::connection('jose')->table('SCHOOL_INCIDENCIA')
                ->select(
                'SCHOOL_INCIDENCIA.ID_INCIDENCIA',
                'SCHOOL_INCIDENCIA.DESCRIPCION',
                DB::raw("TO_CHAR(SCHOOL_INCIDENCIA.HORA_REGISTRO, 'HH:MI AM') AS HORA_REGISTRO"),
                DB::raw("TO_CHAR(SCHOOL_INCIDENCIA.HORA_OCURRIO, 'HH:MI AM') AS HORA_OCURRIO"),
                'SCHOOL_INCIDENCIA.NIVEL',
                'SCHOOL_INCIDENCIA.ESTADO'
                 )
                ->where('SCHOOL_INCIDENCIA.ID_ALUMNO',$id_alumno)
                ->where('SCHOOL_INCIDENCIA.ID_PERIODO',$id_periodo)
                ->where('SCHOOL_INCIDENCIA.ESTADO','J')
                ->get();
            }else{
                $rows = DB::connection('jose')->table('SCHOOL_INCIDENCIA')
                ->select(
                'SCHOOL_INCIDENCIA.ID_INCIDENCIA',
                'SCHOOL_INCIDENCIA.DESCRIPCION',
                DB::raw("TO_CHAR(SCHOOL_INCIDENCIA.HORA_REGISTRO, 'HH:MI AM') AS HORA_REGISTRO"),
                DB::raw("TO_CHAR(SCHOOL_INCIDENCIA.HORA_OCURRIO, 'HH:MI AM') AS HORA_OCURRIO"),
                'SCHOOL_INCIDENCIA.NIVEL',
                'SCHOOL_INCIDENCIA.ESTADO'
                 )
                ->where('SCHOOL_INCIDENCIA.ID_ALUMNO',$id_alumno)
                ->where('SCHOOL_INCIDENCIA.ID_PERIODO',$id_periodo)
                ->where('SCHOOL_INCIDENCIA.NIVEL',$nivel)
                ->get();

            }

        }
        
        return $rows;
    }
    public static function evidenciaByIdIncidencia($id_evidencia)
    {
        $rows = DB::connection('jose')->table('SCHOOL_INCIDENCIA_EVIDENCIA')
        ->select(
            'SCHOOL_INCIDENCIA_EVIDENCIA.ID_EVIDENCIA',
            'SCHOOL_INCIDENCIA_EVIDENCIA.NOMBRE',
            'SCHOOL_INCIDENCIA_EVIDENCIA.IMG_SIZE',
            'SCHOOL_INCIDENCIA_EVIDENCIA.IMG_TYPE'
        )
        ->where('SCHOOL_INCIDENCIA_EVIDENCIA.ID_INCIDENCIA',$id_evidencia)
        ->get();
        return $rows;
    }
    public static function retirarAlumnoFaltasAltas($id_alumno, $id_periodo)
    {
        $result = DB::connection('jose')->table('SCHOOL_INCIDENCIA')
        ->where('ID_ALUMNO',$id_alumno)
        ->where('ID_PERIODO',$id_periodo)
        ->update(array(
            "estado" => "R"
        ));
        
        return $result;
    }
    public static function exoneracionAddEstudiantes($data,$datosAlumnos)//ADD
    {
        foreach($datosAlumnos as $dato){
            $id = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_ECURSO","ID_ECURSO")+1;
            $data["ID_ECURSO"] = $id;
            $data["ID_ALUMNO"] = $dato['id_alumno'];
            $result = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSO')
            ->insert($data);
         }
        return true;
    }
    public static function exoneracionListCursosG($id_pngrado)//lista de cursos por grado
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGCURSO')
        ->select(
            'SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO',
            'SCHOOL_PERIODO_NGCURSO.ID_PNGRADO',
            'SCHOOL_CURSO.ID_CURSO',
            'SCHOOL_CURSO.NOMBRE'
            )
        ->join('SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_NGCURSO.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        ->where('SCHOOL_PERIODO_NGCURSO.ID_PNGRADO',$id_pngrado)
        ->get();
        
        return $result;
    }
    public static function exoneracionListEstudiantesCursoPG($periodo,$id_pngcurso)
    {
        $result = DB::connection('eliseo')->table('JOSE.SCHOOL_PERIODO_ECURSO')
        ->selectRaw(
            "SCHOOL_PERIODO_ECURSO.ID_ECURSO,
            SCHOOL_PERIODO_ECURSO.ID_ALUMNO,
            SCHOOL_CURSO.NOMBRE AS CURSO,
            SCHOOL_GRADO.NOMBRE AS GRADO,
            SCHOOL_NIVEL.NOMBRE AS NIVEL,
            COUNT(SCHOOL_PERIODO_ECURSOE.ID_ECURSOE)AS EVIDENCIA_COUNT,
            PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRES"
            )
        ->join('MOISES.PERSONA','SCHOOL_PERIODO_ECURSO.ID_ALUMNO','=','PERSONA.ID_PERSONA')
        ->join('JOSE.SCHOOL_PERIODO_NGCURSO','SCHOOL_PERIODO_ECURSO.ID_PNGCURSO','=','SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO')
        ->join('JOSE.SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        ->join('JOSE.SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_NGCURSO.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('JOSE.SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->join('JOSE.SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->join('JOSE.SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
        ->leftJoin('JOSE.SCHOOL_PERIODO_ECURSOE', function($join){
            $join->on('SCHOOL_PERIODO_ECURSOE.ID_ECURSO', '=', 'SCHOOL_PERIODO_ECURSO.ID_ECURSO');
            })
        ->where('SCHOOL_PERIODO_ECURSO.ID_PERIODO',$periodo)
        ->where('SCHOOL_PERIODO_ECURSO.ID_PNGCURSO',$id_pngcurso)
        ->groupBy('SCHOOL_PERIODO_ECURSO.ID_ECURSO')
        ->groupBy('SCHOOL_PERIODO_ECURSO.ID_ALUMNO')
        ->groupBy('SCHOOL_CURSO.NOMBRE')
        ->groupBy('SCHOOL_GRADO.NOMBRE')
        ->groupBy('SCHOOL_NIVEL.NOMBRE')
        ->groupBy('PERSONA.NOMBRE')
        ->groupBy('PERSONA.PATERNO')
        ->groupBy('PERSONA.MATERNO')
        ->get();
        
        return $result;
    }
    public static function exoneracionDelEstudiantesCursoPGA($ids_ecurso)
    {
        foreach($ids_ecurso as $dato){
            $result = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSO')
            ->where('ID_ECURSO',$dato['id_ecurso'])
            ->delete();
         }
        
        return true;
    }
    public static function exoneracionEstudiantesBySeccionCurso($id_pngseccion,$id_pngcurso)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_MATRICULA')
        ->select(
            'SCHOOL_MATRICULA.ID_MATRICULA',
            'SCHOOL_MATRICULA.ID_ALUMNO',
            DB::raw("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRES")
        )
        ->join('JOSE.SCHOOL_ALUMNO','SCHOOL_MATRICULA.ID_ALUMNO','=','SCHOOL_ALUMNO.ID_ALUMNO')
        ->join('MOISES.PERSONA','SCHOOL_ALUMNO.ID_ALUMNO','=','PERSONA.ID_PERSONA')
        ->whereNotIn('SCHOOL_MATRICULA.ID_ALUMNO',function($query)use ($id_pngcurso) {
            $query->select('SCHOOL_PERIODO_ECURSO.ID_ALUMNO')->from('JOSE.SCHOOL_PERIODO_ECURSO')
            ->where('SCHOOL_PERIODO_ECURSO.ID_PNGCURSO',$id_pngcurso);
         })
        ->where('SCHOOL_MATRICULA.ID_PNGSECCION',$id_pngseccion)
        ->get();
        return $rows; 
    }
    public static function exoneracionAddDescripcionEvidencia($data_ecursodescripcion,$data_ecursoevidencia,$imagenes,$id_alumno)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_ECURSOD","ID_ECURSOD")+1;
        $data_ecursodescripcion["ID_ECURSOD"] = $id;
        $data_ecursodescripcion["ID_ALUMNO"] = $id_alumno;
        $result1 = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOD')->insert($data_ecursodescripcion);

        $destinationPath = 'school/evidencias';
        $evidencias = array();
        if($imagenes){
            foreach($imagenes as $file){
                $nombreDinamico = $id_alumno."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
                $formato = $file->getClientOriginalExtension();
                $size =  $file->getSize();
                $url = $destinationPath."/".$nombreDinamico;
                $file->move($destinationPath, $nombreDinamico);
                $data_ecursoevidencia["ID_ALUMNO"]=$id_alumno;
                $data_ecursoevidencia["nombre"]=$nombreDinamico;
                $data_ecursoevidencia["img_size"]=$size;
                $data_ecursoevidencia["img_type"]="image/".$formato;
                if($file){
                    $id2 = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_ECURSOE","ID_ECURSOE")+1;
                    $data_ecursoevidencia["ID_ECURSOE"] = $id2;
                    $result2 = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOE')->insert($data_ecursoevidencia);
                    $evidencias[] =$data_ecursoevidencia;
                }
            }
            $data["envidencias"]=$evidencias;
        }
        
        return $data;
    }
    public static function exoneracionEditDescripcionEvidencia($data_ecursodescripcion,$data_ecursoevidencia,$imagenes,$id_alumno)
    {
       
        $data_ecursodescripcion["ID_ALUMNO"] = $id_alumno;
        $result1 = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOD')
                    ->where('id_ecurso',$data_ecursodescripcion['id_ecurso'])
                    ->update($data_ecursodescripcion);

        $evidencias = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOE')
        ->select('SCHOOL_PERIODO_ECURSOE.ID_ECURSOE','SCHOOL_PERIODO_ECURSOE.ID_ECURSO','SCHOOL_PERIODO_ECURSOE.NOMBRE')
        ->where('ID_ECURSOE',$data_ecursoevidencia['id_ecurso'])
        ->get(); 
        if(!empty($evidencias)){
            foreach($evidencias as $file){
                
                $result = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOE')
                            ->where('SCHOOL_PERIODO_ECURSOE.id_ecurso',$file->id_ecurso)
                            ->delete();
                $file_path = public_path('school/evidencias/') . $file->nombre;
                if($file_path)
                    unlink($file_path);
            }
        }

        $destinationPath = 'school/evidencias';
        $evidencias = array();
        if($imagenes){
            foreach($imagenes as $file){
                $nombreDinamico = $id_alumno."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
                $formato = $file->getClientOriginalExtension();
                $size =  $file->getSize();
                $url = $destinationPath."/".$nombreDinamico;
                $file->move($destinationPath, $nombreDinamico);
                $data_ecursoevidencia["id_alumno"]=$id_alumno;
                $data_ecursoevidencia["nombre"]=$nombreDinamico;
                $data_ecursoevidencia["img_size"]=$size;
                $data_ecursoevidencia["img_type"]="image/".$formato;
                if($file){
                    $id2 = self::getIdMax(DB::connection('jose'),"SCHOOL_PERIODO_ECURSOE","ID_ECURSOE")+1;
                    $data_ecursoevidencia["ID_ECURSOE"] = $id2;
                    $result2 = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOE')->insert($data_ecursoevidencia);
                    $evidencias[] =$data_ecursoevidencia;
                }
            }
            $data["envidencias"]=$evidencias;
        }
        
        return $data;
    }
    public static function exoneracionDescripcionById($id_ecurso)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOD')
                ->select(
                    'SCHOOL_PERIODO_ECURSOD.ID_ECURSOD',
                    'SCHOOL_PERIODO_ECURSOD.DESCRIPCION'
                    )
                ->where('ID_ECURSO',$id_ecurso)
                ->first();
        return $result;
    }
    public static function exoneracionIvidenciaById($id_ecurso)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_ECURSOE')
        ->select(
            'SCHOOL_PERIODO_ECURSOE.ID_ECURSOE',
            'SCHOOL_PERIODO_ECURSOE.NOMBRE',
            'SCHOOL_PERIODO_ECURSOE.IMG_SIZE',
            'SCHOOL_PERIODO_ECURSOE.IMG_TYPE'
        )
        ->where('SCHOOL_PERIODO_ECURSOE.ID_ECURSO',$id_ecurso)
        ->get();
        return $rows;
    }
    public static function cambioDocenteCursolist($id_periodo,$id_persona)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_SPERSONAL')
        ->select(
            'SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL',
            'SCHOOL_PERIODO_SPERSONAL.ID_PERSONA',
            'SCHOOL_PERIODO_DCURSO.ID_DCURSO',
            'SCHOOL_PERIODO_DCURSO.ID_PNGCURSO',
            'SCHOOL_CURSO.NOMBRE AS CURSO',
            'SCHOOL_GRADO.NOMBRE AS GRADO',
            'SCHOOL_NIVEL.NOMBRE AS NIVEL'
        )
        ->join('SCHOOL_PERIODO_DCURSO','SCHOOL_PERIODO_SPERSONAL.ID_SPERSONAL','=','SCHOOL_PERIODO_DCURSO.ID_SPERSONAL')
        ->join('SCHOOL_PERIODO_NGRADO','SCHOOL_PERIODO_SPERSONAL.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
        ->join('SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
        ->join('SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
        ->join('SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
        ->join('SCHOOL_PERIODO_NGCURSO','SCHOOL_PERIODO_DCURSO.ID_PNGCURSO','=','SCHOOL_PERIODO_NGCURSO.ID_PNGCURSO')
        ->join('SCHOOL_CURSO','SCHOOL_PERIODO_NGCURSO.ID_CURSO','=','SCHOOL_CURSO.ID_CURSO')
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PERSONA',$id_persona)
        ->where('SCHOOL_PERIODO_SPERSONAL.ID_PERIODO',$id_periodo)
        ->get();
        return $rows; 
    }

    public static function cambioDocenteCursoUpdate($cursosdatos,$id_user)
    { 
        foreach($cursosdatos as $data){
            DB::connection('jose')->table('SCHOOL_PERIODO_SPERSONAL')
                    ->where('id_spersonal',$data['id_spersonal'])
                    ->update(array(
                        "id_persona" =>$data['id_persona']
                    ));
        }
        return true;
    }
    public static function sexoTipo(){
        $rows = DB::connection('moises')->table('TIPO_SEXO')
            ->select(
                DB::raw(
                    "
                    SEXO,
                    NOMBRE
                    "
                )
            )
            ->get();
        return $rows;
    } 
    public static function listPersonsSearch($texto)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
        ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->select(
            'PERSONA.ID_PERSONA',
            'PERSONA.NOMBRE',
            'PERSONA.PATERNO',
            'PERSONA.MATERNO',
            'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
            DB::raw(
                "
                PERSONA.NOMBRE || ' ' || PERSONA.PATERNO || ' ' || PERSONA.MATERNO NOMBREC
                "
            )
        )
        ->where('PERSONA_DOCUMENTO.NUM_DOCUMENTO', 'LIKE', '%'.$texto.'%')
        ->orWhereRaw("LOWER(NOMBRE || ' ' || PATERNO || ' ' || MATERNO) LIKE LOWER('%".$texto."%')")
        ->get();
        return $rows;
    }
    public static function listBimestresPMesCursoProf($id_pngseccion,$id_pngcurso)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PMES_CURSO_PROFESOR')
        ->join('JOSE.SCHOOL_PERIODOMES', 'SCHOOL_PMES_CURSO_PROFESOR.ID_PMES', '=', 'SCHOOL_PERIODOMES.ID_PMES')
        ->select(
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PMCPROFESOR',
            'SCHOOL_PMES_CURSO_PROFESOR.ID_PROFESOR',
            'SCHOOL_PERIODOMES.NOMBRE'
        )
        ->where('SCHOOL_PMES_CURSO_PROFESOR.ID_PNGCURSO',$id_pngcurso)
        ->where('SCHOOL_PMES_CURSO_PROFESOR.ID_PNGSECCION',$id_pngseccion)
        ->get();
        return $rows;
    }
    public static function updateBimestresPMesCursoProf($id_sdocente,$id_institucion,$bimestres)
    {
        $data_bimestre = array();
       
        foreach($bimestres as $bimestre){
            if($bimestre['isSelected']==true){
                $data_bimestre['id_profesor'] = $id_sdocente;
                $data_bimestre['id_institucion'] = $id_institucion;
                $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PMES_CURSO_PROFESOR')
                ->where('SCHOOL_PMES_CURSO_PROFESOR.ID_PMCPROFESOR',$bimestre['id_pmcprofesor'])
                ->update($data_bimestre);
            }else{
                $data_bimestre['id_profesor'] = "";
                $data_bimestre['id_institucion'] = "";
                $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PMES_CURSO_PROFESOR')
                ->where('SCHOOL_PMES_CURSO_PROFESOR.ID_PMCPROFESOR',$bimestre['id_pmcprofesor'])
                ->update($data_bimestre);
                
            }
        }
        return true;
    }
    public static function periodoMesHijos($id_parent)
    {
        
        if(!empty($id_parent)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERIODOMES')
            ->select(
            'SCHOOL_PERIODOMES.ID_PMES',
            'SCHOOL_PERIODOMES.NOMBRE',
            'SCHOOL_PERIODOMES.PARENT'
            )
            ->where('SCHOOL_PERIODOMES.PARENT',$id_parent)
            ->orderBy('SCHOOL_PERIODOMES.ORDEN')
            ->get();
            
        }
        
        return $rows;
    }
    public static function unitsAdd($unidades,$id_periodo,$id_pngrado)
    {
        $dataUnidad= array(
            "id_pmes_bimestre" =>null,
            "id_pmes" =>null,
            "fecha_ini"=>null,
            "fecha_fin" => null,
            "duracion_semanal" => null,
            "id_pngrado" =>null,
            "id_periodo" =>null,
            "tipo" =>null
        );
        if(!self::unitsByParamExist($id_pngrado,$id_periodo,'P')){
            foreach($unidades as $dato){
                if(!empty($dato)){
                    if(empty((int)$dato['id_unidad'])){
                        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_UNIDAD", "ID_UNIDAD")+1;
                        $dataUnidad['id_unidad'] = $id;
                        $dataUnidad['id_pmes_bimestre'] = (int)$dato['id_pmes_bimestre'];
                        $dataUnidad['id_pmes'] = (int)$dato['id_pmes_unidad'];
                        $dataUnidad['id_pngrado'] = $id_pngrado;
                        $dataUnidad['fecha_ini'] =$dato['fecha_ini'];
                        $dataUnidad['fecha_fin'] =$dato['fecha_fin'];
                        $dataUnidad['duracion_semanal'] =$dato['duracion_semanal'];
                        $dataUnidad['id_periodo'] =$id_periodo;
                        $dataUnidad['tipo'] ='R';
                        DB::connection('jose')->table('SCHOOL_UNIDAD')->insert($dataUnidad);
                    }else{
                        $dataUnidad['fecha_ini'] =$dato['fecha_ini'];
                        $dataUnidad['fecha_fin'] =$dato['fecha_fin'];
                        $dataUnidad['duracion_semanal'] =$dato['duracion_semanal'];
                        DB::connection('jose')->table('SCHOOL_UNIDAD')
                        ->where('SCHOOL_UNIDAD.ID_UNIDAD',(int)$dato['id_unidad'])
                        ->update($dataUnidad);
                    }
                } 
            }
            foreach($unidades as $dato){
                if(!empty($dato)){
                    if(empty((int)$dato['id_unidad'])){
                        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_UNIDAD", "ID_UNIDAD")+1;
                        $dataUnidad['id_unidad'] = $id;
                        $dataUnidad['id_pmes_bimestre'] = (int)$dato['id_pmes_bimestre'];
                        $dataUnidad['id_pmes'] = (int)$dato['id_pmes_unidad'];
                        $dataUnidad['id_pngrado'] = null;
                        $dataUnidad['fecha_ini'] =$dato['fecha_ini'];
                        $dataUnidad['fecha_fin'] =$dato['fecha_fin'];
                        $dataUnidad['duracion_semanal'] =$dato['duracion_semanal'];
                        $dataUnidad['id_periodo'] =$id_periodo;
                        $dataUnidad['tipo'] ='P';
                        DB::connection('jose')->table('SCHOOL_UNIDAD')->insert($dataUnidad);
                    }else{
                        $dataUnidad['fecha_ini'] =$dato['fecha_ini'];
                        $dataUnidad['fecha_fin'] =$dato['fecha_fin'];
                        $dataUnidad['duracion_semanal'] =$dato['duracion_semanal'];
                        DB::connection('jose')->table('SCHOOL_UNIDAD')
                        ->where('SCHOOL_UNIDAD.ID_UNIDAD',(int)$dato['id_unidad'])
                        ->update($dataUnidad);
                    }
                } 
            }
        }else if(self::unitsByParamExist(null,$id_periodo,'P')){
            foreach($unidades as $dato){
                if(!empty($dato)){
                        $id = self::getIdMax(DB::connection('jose'), "SCHOOL_UNIDAD", "ID_UNIDAD")+1;
                        $dataUnidad['id_unidad'] = $id;
                        $dataUnidad['id_pmes_bimestre'] = (int)$dato['id_pmes_bimestre'];
                        $dataUnidad['id_pmes'] = (int)$dato['id_pmes_unidad'];
                        $dataUnidad['id_pngrado'] = $id_pngrado;
                        $dataUnidad['fecha_ini'] =$dato['fecha_ini'];
                        $dataUnidad['fecha_fin'] =$dato['fecha_fin'];
                        $dataUnidad['duracion_semanal'] =$dato['duracion_semanal'];
                        $dataUnidad['id_periodo'] =$id_periodo;
                        $dataUnidad['tipo'] = 'R';
                        DB::connection('jose')->table('SCHOOL_UNIDAD')->insert($dataUnidad);
                } 
            }
        }
        
        return true;
    }
    public static function unitsByIdPNGrado($id_pngrado,$id_periodo)
    { 
        /*
select 
c.id_unidad,
to_char(C.FECHA_INI,'YYYY-MM-DD') AS U_FECHA_DESDE,
to_char(C.FECHA_FIN,'YYYY-MM-DD') AS U_FECHA_HASTA,
c.duracion_semanal,
c.id_pmes_bimestre,
c.id_pmes,
c.id_pngrado,
c.tipo,
a.id_pmes as id_pmes_calendario,
b.id_pmes,
to_char(a.FECHA_DESDE,'YYYY-MM-DD') AS PC_FECHA_DESDE,
to_char(a.FECHA_HASTA,'YYYY-MM-DD') AS PC_FECHA_HASTA
from jose.school_periodo_calendario a
inner join jose.school_periodomes b on a.id_pmes = b.parent
left join jose.school_unidad c on b.id_pmes = c.id_pmes and c.id_pmes_bimestre=a.id_pmes and c.id_pngrado=3 and  c.tipo='R'
where a.id_periodo = 1 and a.id_pmes=4
 order by  b.id_pmes;

        */
        $rows ="";
        if(self::unitsByParamExist($id_pngrado,null,null)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_PERIODO_CALENDARIO')
            ->select(
            'SCHOOL_UNIDAD.ID_UNIDAD',
            'SCHOOL_UNIDAD.FECHA_INI',
            'SCHOOL_UNIDAD.FECHA_FIN',
            'SCHOOL_UNIDAD.DURACION_SEMANAL',
            'SCHOOL_UNIDAD.ID_PMES_BIMESTRE',
            'SCHOOL_UNIDAD.ID_PMES',
            'SCHOOL_UNIDAD.ID_PNGRADO',
            'SCHOOL_UNIDAD.TIPO',
            'SCHOOL_PERIODO_CALENDARIO.ID_PMES AS ID_PMES_CALENDARIO',
            DB::RAW(
                "to_char(SCHOOL_PERIODO_CALENDARIO.FECHA_DESDE,'YYYY-MM-DD') AS FECHA_DESDE"
            ),
            DB::RAW(
                "to_char(SCHOOL_PERIODO_CALENDARIO.FECHA_HASTA,'YYYY-MM-DD') AS FECHA_HASTA"
            )
            )
            ->join('JOSE.SCHOOL_PERIODOMES','SCHOOL_PERIODO_CALENDARIO.ID_PMES','=','SCHOOL_PERIODOMES.PARENT')
            ->leftJoin('JOSE.SCHOOL_UNIDAD', function($join) use($id_periodo, $id_pngrado) {
                $join->on('JOSE.SCHOOL_UNIDAD.ID_PMES', '=', 'SCHOOL_PERIODOMES.ID_PMES')
                ->where('SCHOOL_UNIDAD.ID_PERIODO', '=', $id_periodo)
                ->where('SCHOOL_UNIDAD.ID_PNGRADO', '=',$id_pngrado)
                ->where('SCHOOL_UNIDAD.TIPO', '=','R');
            })
            ->get(); 
        }else if(self::unitsByParamExist(null,$id_periodo,'P')){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD')
            ->select(
            'SCHOOL_UNIDAD.ID_UNIDAD',
            'SCHOOL_UNIDAD.FECHA_INI',
            'SCHOOL_UNIDAD.FECHA_FIN',
            'SCHOOL_UNIDAD.DURACION_SEMANAL',
            'SCHOOL_UNIDAD.ID_PMES_BIMESTRE',
            'SCHOOL_UNIDAD.ID_PMES',
            'SCHOOL_UNIDAD.ID_PNGRADO',
            'SCHOOL_UNIDAD.TIPO',
            'SCHOOL_PERIODO_CALENDARIO.ID_PMES AS ID_PMES_CALENDARIO',
            DB::RAW(
                "to_char(SCHOOL_PERIODO_CALENDARIO.FECHA_DESDE,'YYYY-MM-DD') AS FECHA_DESDE"
            ),
            DB::RAW(
                "to_char(SCHOOL_PERIODO_CALENDARIO.FECHA_HASTA,'YYYY-MM-DD') AS FECHA_HASTA"
            )
            )
            ->leftJoin('JOSE.SCHOOL_PERIODO_CALENDARIO', function($join) use($id_periodo) {
                $join->on('JOSE.SCHOOL_UNIDAD.ID_PMES_BIMESTRE', '=', 'SCHOOL_PERIODO_CALENDARIO.ID_PMES')
                ->where('SCHOOL_PERIODO_CALENDARIO.ID_PERIODO', '=', $id_periodo);
            })
            ->Where('SCHOOL_UNIDAD.ID_PERIODO',$id_periodo)
            ->Where('SCHOOL_UNIDAD.TIPO','P')
            ->get();
        }
        return $rows;
    }
    public static function unitsLista($id_pngrado,$id_periodo)
    { 
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD')
            ->select(
            'SCHOOL_UNIDAD.ID_UNIDAD',
            'SCHOOL_UNIDAD.FECHA_INI',
            'SCHOOL_UNIDAD.FECHA_FIN',
            'SCHOOL_UNIDAD.DURACION_SEMANAL',
            'SCHOOL_UNIDAD.ID_PMES_BIMESTRE',
            'SCHOOL_UNIDAD.ID_PMES',
            'SCHOOL_UNIDAD.ID_PNGRADO',
            'SCHOOL_UNIDAD.TIPO'
            )
            ->Where('SCHOOL_UNIDAD.ID_PNGRADO',$id_pngrado)
            ->Where('SCHOOL_UNIDAD.ID_PERIODO',$id_periodo)
            ->Where('SCHOOL_UNIDAD.TIPO','R')
            ->get(); 
        return $rows;
    }
    public static function unitsByParamExist($id_pngrado,$id_periodo,$tipo)
    { 
        $rows = false;
        if(!empty($id_pngrado) && empty($id_periodo) && empty($tipo)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD')
            ->where('SCHOOL_UNIDAD.ID_PNGRADO',$id_pngrado)
            ->exists();  
        }else if(empty($id_pngrado) && !empty($id_periodo) && !empty($tipo)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD')
            ->where('SCHOOL_UNIDAD.ID_PERIODO',$id_periodo)
            ->where('SCHOOL_UNIDAD.TIPO',$tipo)
            ->exists();  
        }
        return $rows;
    }
    public static function unitsByIdPNGradoGroupHijo($id_pngrado)
    { 
        $rows ="";
        if(!empty($id_pngrado)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD')
            ->leftJoin('JOSE.SCHOOL_PERIODOMES',"JOSE.SCHOOL_UNIDAD.ID_PMES_BIMESTRE","=","JOSE.SCHOOL_PERIODOMES.ID_PMES")
            ->select(
            'SCHOOL_UNIDAD.ID_PMES_BIMESTRE',
            'SCHOOL_PERIODOMES.NOMBRE'
            )
            ->where('SCHOOL_UNIDAD.ID_PNGRADO',$id_pngrado)
            ->groupBy('SCHOOL_UNIDAD.ID_PMES_BIMESTRE')
            ->groupBy('SCHOOL_PERIODOMES.NOMBRE')
            ->orderBy('SCHOOL_UNIDAD.ID_PMES_BIMESTRE')
            ->get(); 
        }
        return $rows;
    }
    public static function unitsByIdPmesHijo($id_pmes_hijo)
    { 
        $rows ="";
        if(!empty($id_pmes_hijo)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD')
            ->select(
            'SCHOOL_UNIDAD.ID_UNIDAD',
            'SCHOOL_UNIDAD.FECHA_INI',
            'SCHOOL_UNIDAD.FECHA_FIN',
            'SCHOOL_UNIDAD.DURACION_SEMANAL',
            'SCHOOL_UNIDAD.ID_PMES_BIMESTRE',
            'SCHOOL_UNIDAD.ID_PMES',
            'SCHOOL_PERIODOMES.NOMBRE AS UNIDAD'
            )
            ->leftJoin('JOSE.SCHOOL_PERIODOMES',"JOSE.SCHOOL_UNIDAD.ID_PMES","=","JOSE.SCHOOL_PERIODOMES.ID_PMES")
            ->where('SCHOOL_UNIDAD.ID_PMES_BIMESTRE',$id_pmes_hijo)
            ->where('SCHOOL_UNIDAD.TIPO','R')
            ->get(); 
        }
        return $rows;
    }
    public static function thematicAdd($data,$id_unidad)
    {
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_TEMATICA","ID_TEMATICA")+1;
            $data["ID_TEMATICA"] = $id;
            $result = DB::connection('jose')->table('SCHOOL_TEMATICA')
            ->insert($data);
            if($result){
                $result = DB::connection('jose')->table('SCHOOL_UNIDAD')
                ->where('ID_UNIDAD',$id_unidad)
                ->update(array('ID_TEMATICA'=>$id));
            }
            
        return $result;
    }
    public static function thematicEdit($id_tematica,$data,$id_unidad)
    {
       
            $result = DB::connection('jose')->table('SCHOOL_TEMATICA')
            ->where('ID_TEMATICA',$id_tematica)
            ->update($data);
            if($result){
                $result = DB::connection('jose')->table('SCHOOL_UNIDAD')
                ->where('ID_UNIDAD',$id_unidad)
                ->update(array('ID_TEMATICA'=>$id_tematica));
            }
            
        return $result;
    }
    public static function thematicById($id_tematica)
    {
        $result = DB::connection('jose')->table('SCHOOL_TEMATICA')
        ->join('JOSE.SCHOOL_UNIDAD',"SCHOOL_TEMATICA.ID_TEMATICA","=","SCHOOL_UNIDAD.ID_TEMATICA")
        ->select(
            'SCHOOL_TEMATICA.ID_TEMATICA',
            'SCHOOL_TEMATICA.DESCRIPCION',
            'SCHOOL_UNIDAD.ID_PMES_BIMESTRE',
            'SCHOOL_UNIDAD.ID_UNIDAD'
            )
        ->where('SCHOOL_TEMATICA.ID_TEMATICA',$id_tematica)
        ->first();
        return $result;
    }
    public static function thematicGrado($id_pngrado)
    {
        
        $result = DB::connection('jose')->table('SCHOOL_TEMATICA')
        ->join('JOSE.SCHOOL_UNIDAD',"SCHOOL_TEMATICA.ID_TEMATICA","=","SCHOOL_UNIDAD.ID_TEMATICA")
        ->join('JOSE.SCHOOL_PERIODOMES',"SCHOOL_UNIDAD.ID_PMES","=","SCHOOL_PERIODOMES.ID_PMES")
        ->select(
            'SCHOOL_TEMATICA.ID_TEMATICA',
            'SCHOOL_TEMATICA.DESCRIPCION',
            'SCHOOL_TEMATICA.SSIG_DESC_CONTEXTO_REALIDAD',
            'SCHOOL_TEMATICA.SSIG_PLAN_RETO_DESAFIO',
            'SCHOOL_TEMATICA.SSIG_INTE_PEDAGOGICA',
            'SCHOOL_PERIODOMES.NOMBRE AS UNIDAD',
            'SCHOOL_UNIDAD.ID_UNIDAD'
        )
        ->where('SCHOOL_UNIDAD.ID_PNGRADO',$id_pngrado)
        ->get();
        return $result;
    }
    public static function thematicDelete($id_tematica,$id_unidad)
    {
        if($id_unidad){
            $result = DB::connection('jose')->table('SCHOOL_UNIDAD')
            ->where('ID_UNIDAD',$id_unidad)
            ->update(array('ID_TEMATICA'=>''));
        }
        $result = DB::connection('jose')->table('SCHOOL_TEMATICA')
                ->where('ID_TEMATICA',$id_tematica)
                ->delete();
        return $result;
    }
    public static function pmdeList()
    { 
        $result = DB::connection('jose')->table('SCHOOL_PMDE')
        ->select(
            'SCHOOL_PMDE.ID_PMDE',
            'SCHOOL_PMDE.DESCRIPCION'
        )
        ->get();
      
        return $result;
    }
    public static function pmdeListPeriodo($id_periodo,$id_unidad)
    { 
        $result = DB::connection('jose')->table('SCHOOL_UNIDAD')
                ->join('JOSE.SCHOOL_UNIDAD_PMDE',"SCHOOL_UNIDAD.ID_UNIDAD","=","SCHOOL_UNIDAD_PMDE.ID_UNIDAD")
                ->join('JOSE.SCHOOL_PMDE',"SCHOOL_UNIDAD_PMDE.ID_PMDE","=","SCHOOL_PMDE.ID_PMDE")
                ->select(
                    'SCHOOL_UNIDAD.ID_UNIDAD',
                    'SCHOOL_PMDE.ID_PMDE',
                    'SCHOOL_PMDE.DESCRIPCION',
                    'SCHOOL_UNIDAD_PMDE.CHECKED')
                ->where('SCHOOL_UNIDAD.ID_PERIODO',$id_periodo)
                ->where('SCHOOL_UNIDAD.ID_UNIDAD',$id_unidad)
                ->where('SCHOOL_UNIDAD.TIPO','R')
                ->orderBy('SCHOOL_PMDE.ID_PMDE')
                ->get();

        return $result;
    }
    public static function pmdeAdd($pmdes, $id_periodo)
    {
        $data= array(
            "id_unidad" =>null,
            "id_pmde" =>null,
            "id_periodo" =>null,
            "checked" =>null
        );
        if(self::pmdesByParamExist($id_periodo)){
            $result = DB::connection('jose')->table('SCHOOL_UNIDAD_PMDE')
            ->where('SCHOOL_UNIDAD_PMDE.ID_PERIODO',$id_periodo)
            ->delete();
        }
        foreach($pmdes as $dato){
            if(!empty($dato)){
                $data['id_unidad'] =(int) $dato['id_unidad'];
                $data['id_pmde'] = (int)$dato['id_pmde'];
                $data['checked'] = $dato['checked'];
                $data['id_periodo'] = $id_periodo;
                DB::connection('jose')->table('SCHOOL_UNIDAD_PMDE')->insert($data);  
            } 
        }
        
        return true;
    }
    public static function pmdesByParamExist($id_periodo)
    { 
        $rows = false;
        if(!empty($id_periodo)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_UNIDAD_PMDE')
            ->where('SCHOOL_UNIDAD_PMDE.ID_PERIODO',$id_periodo)
            ->exists();  
        }
        return $rows;
    }
    public static function agendaList()
    { 
        $result = DB::connection('jose')->table('SCHOOL_AGENDA')
                ->select(
                    'SCHOOL_AGENDA.ID_AGENDA',
                    'SCHOOL_AGENDA.ID_CATEGORIA',
                    'SCHOOL_AGENDA.ID_PERIODO',
                    'SCHOOL_AGENDA.DESCRIPCION',
                    'SCHOOL_AGENDA.FECHA_INICIO',
                    'SCHOOL_AGENDA.FECHA_FINAL')
                ->get();

        return $result;
    }
    public static function agendaAdd($data, $id_agenda)
    { 
        $result = null;
        if(empty((int)$id_agenda)){
            $id = self::getIdMax(DB::connection('jose'),"SCHOOL_AGENDA","ID_AGENDA")+1;
            $data['id_agenda'] = $id;
            $result = $id;
            DB::connection('jose')->table('SCHOOL_AGENDA')->insert($data); 
        }else{
            $result= self::agendaEdit($data,(int)$id_agenda);
            $result = $id_agenda;
        }
       

        return $result;
    }
    public static function agendaEdit($data, $id_agenda)
    { 
        $result = DB::connection('jose')->table('SCHOOL_AGENDA')
        ->where('SCHOOL_AGENDA.ID_AGENDA',$id_agenda)
        ->update($data); 

        return $result;
    }
    public static function deleteAgenda($id_agenda)
    { 
        $result = DB::connection('jose')->table('SCHOOL_AGENDA')
        ->where('SCHOOL_AGENDA.ID_AGENDA', $id_agenda)
        ->delete(); 

        return $result;
    }
    public static function confirmationDocumentEdit($id_alumno,$data,$id_user)
    {
        $result = false;
        // $destinationPath = 'school/documentos_confirmados';
        if($data) {
            $result= self::confirmationDocumentOperation($id_alumno,$data,$data['file_ch'],$id_user);
            $result= self::confirmationDocumentOperation($id_alumno,$data,$data['file_fm'],$id_user);
            $result= self::confirmationDocumentOperation($id_alumno,$data,$data['file_cm'],$id_user);
            $result= self::confirmationDocumentOperation($id_alumno,$data,$data['file_af'],$id_user);
        }
        return $result;
    }
    public static function confirmationDocumentOperation($id_alumno,$data,$file,$id_user)
    { 
        $result = false;
        $destinationPath = 'school/evidencias';
        $nombreDinamico = $id_alumno."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
        $formato = $file->getClientOriginalExtension();
        $size =  $file->getSize();
        $url = $destinationPath."/".$nombreDinamico;
        $file->move($destinationPath, $nombreDinamico); 
        /////////////////INERTAR A LA TABLA SCHOOL_FILE//////////////////////////
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_FILE","ID_FILE")+1;
        $dataFile = array();
        $dataFile['id_file'] = $id;
        $dataFile['nombre'] = $nombreDinamico;
        $dataFile['formato'] = $file->getClientOriginalExtension();
        $dataFile['tamanho'] = $size;
        $dataFile['ruta'] = $destinationPath.'/'.$nombreDinamico;
        $dataFile['id_user'] = $id_user;
      
        $resp = DB::connection('jose')->table('SCHOOL_FILE')->insert($dataFile); 
    
        /////////////////ACTUALIZAR LOS CAMPOS  A LA TABLA SCHOOL_RESERVA////////////////////////// 
        
        if($data['file_ch']==$file){
            $dataReserva = array(
                "CONFIRMADO_CH" => $data['compromiso_honor_check'],
                "ID_FILE_CH"=>$id
            );
            if($resp){
                $result = DB::connection('jose')->table('SCHOOL_RESERVA')
                ->where('SCHOOL_RESERVA.ID_ALUMNO',$id_alumno)
                ->update($dataReserva);
            }
        }else if($data['file_fm']==$file){
            $dataReserva = array(
                "CONFIRMADO_FM" => $data['ficha_medica_check'],
                "IF_FILE_FM"=>$id
            );
            if($resp){
                $result = DB::connection('jose')->table('SCHOOL_RESERVA')
                ->where('SCHOOL_RESERVA.ID_ALUMNO',$id_alumno)
                ->update($dataReserva);
            }
        }else if($data['file_cm']==$file){
            $dataReserva = array(
                "CONFIRMADO_MOV"=> $data['confirmacion_movilidad_check'],
                "ID_FILE_MOV"=>$id
            );
            if($resp){
                $result = DB::connection('jose')->table('SCHOOL_RESERVA')
                ->where('SCHOOL_RESERVA.ID_ALUMNO',$id_alumno)
                ->update($dataReserva);
            }
        }else if($data['file_af']==$file){
            $dataReserva = array(
                "CONFIRMADO_AF"=> $data['autorizacion_foto_check'],
                "ID_FILE_AF"=>$id
            );
            if($resp){
                $result = DB::connection('jose')->table('SCHOOL_RESERVA')
                ->where('SCHOOL_RESERVA.ID_ALUMNO',$id_alumno)
                ->update($dataReserva);
            }
        }
        return $result;
    }
    public static function feligresiaListByPeriodOperativo(){
        $result = DB::connection('jose')->table('SCHOOL_RESERVA')
                ->select('SCHOOL_RESERVA.ID_RESERVA','SCHOOL_RESERVA.FELIGRESIA_VOB','SCHOOL_RESERVA.ID_FILE','SCHOOL_RESERVA.ID_ALUMNO',
                DB::RAW("PERSONA.NOMBRE||' '||PERSONA.PATERNO||' '||PERSONA.MATERNO AS NOMBRE"),
                'SCHOOL_GRADO.NOMBRE AS GRADO','SCHOOL_FILE.NOMBRE AS FILE')
                ->join('JOSE.SCHOOL_PERIODO',"SCHOOL_RESERVA.ID_PERIODO","=","SCHOOL_PERIODO.ID_PERIODO")
                ->join('MOISES.PERSONA',"SCHOOL_RESERVA.ID_ALUMNO","=","PERSONA.ID_PERSONA")
                ->join('JOSE.SCHOOL_PERIODO_NGRADO',"SCHOOL_RESERVA.ID_PNGRADO","=","SCHOOL_PERIODO_NGRADO.ID_PNGRADO")
                ->join('JOSE.SCHOOL_GRADO',"SCHOOL_PERIODO_NGRADO.ID_GRADO","=","SCHOOL_GRADO.ID_GRADO")
                ->leftJoin('JOSE.SCHOOL_FILE',"SCHOOL_RESERVA.ID_FILE","=","SCHOOL_FILE.ID_FILE")
                ->join('JOSE.SCHOOL_PERSONA_RELIGION',"SCHOOL_RESERVA.ID_ALUMNO","=","SCHOOL_PERSONA_RELIGION.ID_PERSONA")
                ->join('JOSE.SCHOOL_RELIGION', function($join) { 
                    $join->on('SCHOOL_RESERVA.ID_ALUMNO', '=', 'SCHOOL_PERSONA_RELIGION.ID_PERSONA')->where('SCHOOL_RELIGION.ID','=','01'); 
                })
                ->where('SCHOOL_PERIODO.ESTADO','O')
                ->whereNull('SCHOOL_RESERVA.FELIGRESIA_VOB')
                //->whereNotNull('SCHOOL_RESERVA.ID_FILE')
                ->get();
        return $result;
    }
    
    public static function feligresiaConfirmar($data, $id_reserva)
    { 
        $result = DB::connection('jose')->table('SCHOOL_RESERVA')
        ->where('SCHOOL_RESERVA.ID_RESERVA',$id_reserva)
        ->update($data); 

        return $result;
    }
    public static function feligresiaSearchFamiliaHijosByIdUser($id_persona)
    { 
        $rows="";
        $familia = DB::connection('jose')->table('SCHOOL_PERSONA_FAMILIA')
        ->select('NUMERO')
        ->where('SCHOOL_PERSONA_FAMILIA.ID_PERSONA',(int)$id_persona)
        ->first();
     
        if(!empty($familia->numero)){
            $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_RESERVA')
            ->select(
                'SCHOOL_RESERVA.ID_RESERVA',
                'SCHOOL_RESERVA.ID_ALUMNO',
                'SCHOOL_RESERVA.ID_FILE',
                'PERSONA.NOMBRE',
                'PERSONA.PATERNO',
                'PERSONA.MATERNO',
                'SCHOOL_PERIODO.ANHO_PERIODO AS PERIODO',
                'SCHOOL_NIVEL.NOMBRE AS NIVEL',
                'SCHOOL_GRADO.NOMBRE AS GRADO'
            )
            ->join('JOSE.SCHOOL_PERIODO','SCHOOL_RESERVA.ID_PERIODO','=','SCHOOL_PERIODO.ID_PERIODO')
            ->join('JOSE.SCHOOL_PERIODO_NGRADO','SCHOOL_RESERVA.ID_PNGRADO','=','SCHOOL_PERIODO_NGRADO.ID_PNGRADO')
            ->join('JOSE.SCHOOL_GRADO','SCHOOL_PERIODO_NGRADO.ID_GRADO','=','SCHOOL_GRADO.ID_GRADO')
            ->join('JOSE.SCHOOL_PERIODO_NIVEL','SCHOOL_PERIODO_NGRADO.ID_PNIVEL','=','SCHOOL_PERIODO_NIVEL.ID_PNIVEL')
            ->join('JOSE.SCHOOL_NIVEL','SCHOOL_PERIODO_NIVEL.ID_NIVEL','=','SCHOOL_NIVEL.ID_NIVEL')
            ->join('MOISES.PERSONA','SCHOOL_RESERVA.ID_ALUMNO','=','PERSONA.ID_PERSONA')
            ->join('JOSE.SCHOOL_PERSONA_FAMILIA','SCHOOL_RESERVA.ID_ALUMNO','=','SCHOOL_PERSONA_FAMILIA.ID_PERSONA')
            ->join('JOSE.SCHOOL_PERSONA_RELIGION',"SCHOOL_RESERVA.ID_ALUMNO","=","SCHOOL_PERSONA_RELIGION.ID_PERSONA")
                ->join('JOSE.SCHOOL_RELIGION', function($join) { 
                    $join->on('SCHOOL_RESERVA.ID_ALUMNO', '=', 'SCHOOL_PERSONA_RELIGION.ID_PERSONA')->where('SCHOOL_RELIGION.ID','=','01'); 
            })
            ->where('SCHOOL_PERIODO.ESTADO','O')
            ->where('SCHOOL_PERSONA_FAMILIA.NUMERO',$familia->numero)
            ->where('SCHOOL_PERSONA_FAMILIA.TIPOPARENTESCO_ID','03')
            ->whereNull('SCHOOL_RESERVA.FELIGRESIA_VOB')
            ->get();
         }
        return $rows;
    }
    public static function uploadFileReservaFeligresia($id_alumno,$file,$id_user)
    { 
        $result = false;
        $destinationPath = 'school/evidencias/';
        $nombreDinamico = $id_alumno."_".SchoolsUtil::getGenereNameRandom(17).".".$file->getClientOriginalExtension();
        $formato = $file->getClientOriginalExtension();
        $size =  $file->getSize();
        $url = $destinationPath."/".$nombreDinamico;
        $file->move($destinationPath, $nombreDinamico); 
        /////////////////INERTAR A LA TABLA SCHOOL_FILE//////////////////////////
        $id = self::getIdMax(DB::connection('jose'),"SCHOOL_FILE","ID_FILE")+1;
        $dataFile = array();
        $dataFile['id_file'] = $id;
        $dataFile['nombre'] = $nombreDinamico;
        $dataFile['formato'] = $file->getClientOriginalExtension();
        $dataFile['tamanho'] = $size;
        $dataFile['ruta'] = $destinationPath.'/'.$nombreDinamico;
        $dataFile['id_user'] = $id_user;
      
        $resp = DB::connection('jose')->table('SCHOOL_FILE')->insert($dataFile); 
    
        /////////////////ACTUALIZAR LOS CAMPOS  A LA TABLA SCHOOL_RESERVA////////////////////////// 
        $dataReserva = array(
            "ID_FILE"=> $id
        );
        if($resp){
            $result = DB::connection('jose')->table('SCHOOL_RESERVA')
            ->where('SCHOOL_RESERVA.ID_ALUMNO',$id_alumno)
            ->update($dataReserva);
        }
        
        return $result;
    }
    
    
}

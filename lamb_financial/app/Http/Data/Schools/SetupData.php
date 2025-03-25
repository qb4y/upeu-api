<?php
namespace App\Http\Data\Schools;
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
    public static function getIdMax($connection, $tabla, $campo)
    {
        $valor = DB::connection($connection)->table($tabla)->max($campo);
        return $valor;
    }

    public static function exCommit()
    {
        DB::commit();
    }
    public static function listPersonsSearch($texto)
    {
        $rows = DB::connection('moises')->table('PERSONA')
        ->join('PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
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
        ->limit(15)
        ->get();
        return $rows;
    }

    public static function listPersonsParentesco($id_persona)
    {
        $rows = DB::connection('eliseo')->table('JOSE.SCHOOL_ALUMNO')
        ->join('JOSE.SCHOOL_PERSONA_PARENTESCO', function($join) use($id_persona) {
            $join->on('SCHOOL_ALUMNO.ID_ALUMNO', '=', 'SCHOOL_PERSONA_PARENTESCO.ID_PERSONA')
            ->whereNotNull('PARENT');
        })
        ->select(
            DB::raw(
                "
                SCHOOL_PERSONA_PARENTESCO.PARENT ID_PERSONA,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE SCHOOL_PERSONA_PARENTESCO.PARENT = PERSONA.ID_PERSONA
                ) NOMBRE_PARENT,
                (
                    SELECT NUM_DOCUMENTO
                    FROM MOISES.PERSONA_DOCUMENTO
                    WHERE PERSONA_DOCUMENTO.ID_PERSONA = SCHOOL_PERSONA_PARENTESCO.PARENT
                    AND ROWNUM <= 1
                ) NUM_DOCUMENTO,
                (
                    SELECT NUM_TELEFONO
                    FROM MOISES.PERSONA_TELEFONO
                    WHERE PERSONA_TELEFONO.ID_PERSONA = SCHOOL_PERSONA_PARENTESCO.PARENT
                    AND ROWNUM <= 1
                ) NUM_TELEFONO,
                (
                    SELECT DIRECCION
                    FROM MOISES.PERSONA_DIRECCION
                    WHERE PERSONA_DIRECCION.ID_PERSONA = SCHOOL_PERSONA_PARENTESCO.PARENT
                    AND ROWNUM <= 1
                ) DIRECCION
                "
            )
        )
        ->where('SCHOOL_ALUMNO.ID_ALUMNO', $id_persona)
        ->get();
        return $rows;
    }

    public static function listPersonsEmergency($id_persona)
    {
        $list = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_EMERGENCIA')
        ->select(
            'ID_PEMERGENCIA',
            'ID_PERSONA',
            'TIPOPARENTESCO_ID',
            'ID_ENCARGADO',
            DB::raw(
                "
                (
                    SELECT NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE PERSONA.ID_PERSONA = SCHOOL_PERSONA_EMERGENCIA.ID_ENCARGADO
                ) NOMBRE_ENCARGADO,
                (
                    SELECT NUM_DOCUMENTO
                    FROM MOISES.PERSONA_DOCUMENTO
                    WHERE PERSONA_DOCUMENTO.ID_PERSONA = SCHOOL_PERSONA_EMERGENCIA.ID_ENCARGADO
                    AND ROWNUM <= 1
                ) NUM_DOCUMENTO,
                (
                    SELECT NUM_TELEFONO
                    FROM MOISES.PERSONA_TELEFONO
                    WHERE PERSONA_TELEFONO.ID_PERSONA = SCHOOL_PERSONA_EMERGENCIA.ID_ENCARGADO
                    AND ROWNUM <= 1
                ) NUM_TELEFONO,
                (
                    SELECT DIRECCION
                    FROM MOISES.PERSONA_DIRECCION
                    WHERE PERSONA_DIRECCION.ID_PERSONA = SCHOOL_PERSONA_EMERGENCIA.ID_ENCARGADO
                    AND ROWNUM <= 1
                ) DIRECCION
                "
            )
        )
        ->where('ID_PERSONA',$id_persona)
        ->get();
        return $list;
    }
    public static function listPersonsEmergencyNone($id_persona, $texto)
    {
        $rows = DB::connection('eliseo')->table('MOISES.PERSONA')
        ->join('MOISES.PERSONA_DOCUMENTO', 'PERSONA.ID_PERSONA', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->leftJoin('JOSE.SCHOOL_PERSONA_EMERGENCIA', function($join) use($id_persona) {
            $join->on('PERSONA.ID_PERSONA', '=', 'SCHOOL_PERSONA_EMERGENCIA.ID_ENCARGADO')
            ->where('SCHOOL_PERSONA_EMERGENCIA.ID_PERSONA', '=', $id_persona);
        })
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
        ->whereNull('SCHOOL_PERSONA_EMERGENCIA.ID_ENCARGADO')
        ->whereRaw(
            "(
                PERSONA_DOCUMENTO.NUM_DOCUMENTO LIKE '%$texto%'
                OR LOWER(NOMBRE || ' ' || PATERNO || ' ' || MATERNO) LIKE LOWER('%".$texto."%')
            )"
        )
        ->limit(15)
        ->get();
        return $rows;
    }
    public static function addPersonsEmergency($data)
    {
        $id_pemergencia = self::getIdMax('jose', "SCHOOL_PERSONA_EMERGENCIA", "ID_PEMERGENCIA")+1;
        $data["id_pemergencia"] = $id_pemergencia;
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_EMERGENCIA')->insert($data);
        if($result)
        {
            return $id_pemergencia;
        }
        return false;
    }
    public static function deletePersonsEmergency($id_pemergencia)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_EMERGENCIA')
        ->where('id_pemergencia', $id_pemergencia)
        ->delete();
        return $result;
    }

    public static function listPersonsMobility($id_persona)
    {
        $list = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_MOVILIDAD')
        ->join('JOSE.SCHOOL_PERSONA_CONDUCTOR', 'SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO', '=', 'SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR')
        ->select(
            'SCHOOL_PERSONA_MOVILIDAD.ID_PMOVILIDAD',
            'SCHOOL_PERSONA_MOVILIDAD.ID_PERSONA',
            'SCHOOL_PERSONA_MOVILIDAD.TIPOPARENTESCO_ID',
            'SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO',
            DB::raw(
                "
                (
                    SELECT NOMBRE || ' ' || PATERNO || ' ' || MATERNO 
                    FROM MOISES.PERSONA
                    WHERE PERSONA.ID_PERSONA = SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO
                ) NOMBRE_ENCARGADO,
                (
                    SELECT NUM_DOCUMENTO
                    FROM MOISES.PERSONA_DOCUMENTO
                    WHERE PERSONA_DOCUMENTO.ID_PERSONA = SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO
                    AND ROWNUM <= 1
                ) NUM_DOCUMENTO,
                (
                    SELECT NUM_TELEFONO
                    FROM MOISES.PERSONA_TELEFONO
                    WHERE PERSONA_TELEFONO.ID_PERSONA = SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO
                    AND ROWNUM <= 1
                ) NUM_TELEFONO,
                (
                    SELECT DIRECCION
                    FROM MOISES.PERSONA_DIRECCION
                    WHERE PERSONA_DIRECCION.ID_PERSONA = SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO
                    AND ROWNUM <= 1
                ) DIRECCION 
                "
            )
        )
        ->where('SCHOOL_PERSONA_MOVILIDAD.ID_PERSONA',$id_persona)
        ->get();
        return $list;
    }
    public static function existPersonsMobilityByPersons($id_persona, $id_encargado)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_MOVILIDAD')
        ->where('ID_PERSONA',$id_persona)
        ->where('ID_ENCARGADO',$id_encargado)
        ->exists();
        return $result;
    }
    public static function addPersonsMobility($data)
    {
        $id_pmovilidad = self::getIdMax('jose', "SCHOOL_PERSONA_MOVILIDAD","ID_PMOVILIDAD")+1;
        $data["id_pmovilidad"] = $id_pmovilidad;
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_MOVILIDAD')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function deletePersonsMobility($id_pmovilidad)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_MOVILIDAD')
        ->where('id_pmovilidad', $id_pmovilidad)
        ->delete();
        return $result;
    }
    public static function listPersonsdriversNone($id_persona, $texto)
    {
        $list = DB::connection('eliseo')->table('JOSE.SCHOOL_PERSONA_CONDUCTOR')
        ->join('MOISES.PERSONA', 'SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR', '=', 'PERSONA.ID_PERSONA')
        ->leftJoin('MOISES.PERSONA_DOCUMENTO', 'SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR', '=', 'PERSONA_DOCUMENTO.ID_PERSONA')
        ->leftJoin('JOSE.SCHOOL_PERSONA_MOVILIDAD', function($join) use($id_persona) {
            $join->on('SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR', '=', 'SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO')
            ->where('SCHOOL_PERSONA_MOVILIDAD.ID_PERSONA', '=', $id_persona);
        })
        ->select(
            'SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR',
            'SCHOOL_PERSONA_CONDUCTOR.NRO_LICENCIA',
            'SCHOOL_PERSONA_CONDUCTOR.PLACA',
            'PERSONA_DOCUMENTO.NUM_DOCUMENTO',
            DB::raw(
                "
                PERSONA.NOMBRE || ' ' || PERSONA.PATERNO || ' ' || PERSONA.MATERNO NOMBREC,
                (
                    SELECT NUM_TELEFONO
                    FROM MOISES.PERSONA_TELEFONO
                    WHERE PERSONA_TELEFONO.ID_PERSONA = SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR
                    AND ROWNUM <= 1
                ) NUM_TELEFONO,
                (
                    SELECT DIRECCION
                    FROM MOISES.PERSONA_DIRECCION
                    WHERE PERSONA_DIRECCION.ID_PERSONA = SCHOOL_PERSONA_CONDUCTOR.ID_PCONDUCTOR
                    AND ROWNUM <= 1
                ) DIRECCION 
                "
            )
        )
        ->whereRaw(
            "(
                UPPER(PERSONA.NOMBRE || ' ' || PERSONA.PATERNO || ' ' || PERSONA.MATERNO) LIKE UPPER('%$texto%')
                OR PERSONA_DOCUMENTO.NUM_DOCUMENTO LIKE '%$texto%'
            )"
        )
        ->whereNull('SCHOOL_PERSONA_MOVILIDAD.ID_ENCARGADO')
        ->get();
        return $list;
    }
    public static function addPersonsDrivers($data)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERSONA_CONDUCTOR')->insert($data);
        return $result;
    }
    public static function showRecordMedical($id_fmedica)
    {
        $first = DB::connection('jose')->table('SCHOOL_FICHA_MEDICA')
        ->select(
            'ID_FMEDICA',
            'ID_ALUMNO',
            'SEGURO_ACCIDENTE',
            'ESSALUD',
            'HOSPITAL_ESSALUD',
            'TIPO_SANGRE',
            'TABIQUE_DESVIADO',
            'SANGRADO_NASAL',
            'USA_BRAQUET',
            'USA_LENTES',
            'ALERGIAS',
            'ID_ALERGIA',
            'MEDICINA_ALERGIA',
            'CUENTA_VAC_REFUERZO',
            'ENFERMEDADES',
            'MEDICAMENTOS',
            'OBSERVACION_GENERAL',
            'OPERACIONES',
            'LUGAR_ATENC_EMERG',
            'MEDICAMENTOS_CAS_EMER',
            'USA_INHALADORES',
            'CASO_PRESEN_37G_RECIBE',
            'CASO_PRESEN_38G_RECIBE',
            'PRESENTA_CONVUL_FEBRIL',
            'PESO',
            'TALLA',
            'TOMA_LECHE',
            'TIPO_PREPARA_RECIBE',
            'FRUTAS'
        )
        ->where('ID_FMEDICA', $id_fmedica)
        ->first();
        return $first;
    }
    public static function addRecordMedical($data)
    {
        $id_fmedica = self::getIdMax('jose', "SCHOOL_FICHA_MEDICA","ID_FMEDICA")+1;
        $data["id_fmedica"] = $id_fmedica;
        $result = DB::connection('jose')->table('SCHOOL_FICHA_MEDICA')->insert($data);
        if($result)
        {
            return $id_fmedica;
        }
        return false;
    }
    public static function updateRecordMedical($data, $id_fmedica)
    {
        $result = DB::connection('jose')->table('SCHOOL_FICHA_MEDICA')
        ->where('ID_FMEDICA', $id_fmedica)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function showPersonsAll($id_persona)
    {
        $first = DB::connection('moises')->table('PERSONA')
        ->select(
            'ID_PERSONA',
            'NOMBRE',
            'PATERNO',
            'MATERNO'
        )
        ->where('ID_PERSONA',$id_persona)
        ->first();
        return $first;
    }
    public static function addPersons($data)
    {
        $id_persona = self::getIdMax('moises', "PERSONA","ID_PERSONA")+1;
        $data["id_persona"] = $id_persona;
        $result = DB::connection('moises')->table('PERSONA')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersons($data, $id_persona)
    {
        $result = DB::connection('moises')->table('PERSONA')
        ->where('ID_PERSONA', $id_persona)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function listPersonsAddress($id_persona)
    {
        $rows = DB::connection('moises')->table('PERSONA_DIRECCION')
        ->select(
            'ID_DIRECCION',
            'ID_PERSONA',
            'ID_TIPODIRECCION',
            'ID_UBIGUEO',
            'DIRECCION',
            'ES_ACTIVO',
            'MAP_LATITUD',
            'MAP_LONGITUD',
            'COMENTARIO'
        )
        ->where('ID_PERSONA', $id_persona)
        ->get();
        return $rows;
    }
    public static function addPersonsAddress($data)
    {
        $id_direccion = self::getIdMax('moises', "PERSONA_DIRECCION","ID_DIRECCION")+1;
        $data["id_direccion"] = $id_direccion;
        $result = DB::connection('moises')->table('PERSONA_DIRECCION')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsAddress($data, $id_direccion)
    {
        $result = DB::connection('moises')->table('PERSONA_DIRECCION')
        ->where('id_direccion', $id_direccion)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function listPersonsDocument($id_persona)
    {
        $rows = DB::connection('moises')->table('PERSONA_DOCUMENTO')
        ->select(
            'ID_PERSONA',
            'ID_TIPODOCUMENTO',
            'NUM_DOCUMENTO'
        )
        ->where('ID_PERSONA', $id_persona)
        ->get();
        return $rows;
    }
    public static function addPersonsDocument($data)
    {
        $result = DB::connection('moises')->table('PERSONA_DOCUMENTO')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsDocument($data, $num_documento)
    {
        $result = DB::connection('moises')->table('PERSONA_DOCUMENTO')
        ->where('num_documento', $num_documento)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function showPersonsNatural($id_persona)
    {
        $first = DB::connection('moises')->table('PERSONA_NATURAL')
        ->select(
            'ID_PERSONA',
            'ID_TIPOTRATAMIENTO',
            'ID_TIPOESTADOCIVIL',
            'ID_TIPOPAIS',
            'ID_NACIONALIDAD',
            'ID_TIPOSANGRE',
            'SEXO',
            'FEC_NACIMIENTO',
            'FEC_DEFUNCION'
        )
        ->where('ID_PERSONA', $id_persona)
        ->first();
        return $first;
    }
    public static function addPersonsNatural($data)
    {
        $result = DB::connection('moises')->table('PERSONA_NATURAL')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsNatural($data, $id_persona)
    {
        $result = DB::connection('moises')->table('PERSONA_NATURAL')
        ->where('id_persona', $id_persona)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function showPersonsNaturalReligion($id_persona)
    {
        $first = DB::connection('moises')->table('PERSONA_NATURAL_RELIGION')
        ->select(
            'ID_PERSONA',
            'ID_IGLESIA',
            'ID_TIPORELIGION',
            'FEC_BAUSTIMO',
            'COMENTARIO'
        )
        ->where('ID_PERSONA', $id_persona)
        ->first();
        return $first;
    }
    public static function addPersonsNaturalReligion($data)
    {
        $result = DB::connection('moises')->table('PERSONA_NATURAL_RELIGION')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsNaturalReligion($data, $id_persona)
    {
        $result = DB::connection('moises')->table('PERSONA_NATURAL_RELIGION')
        ->where('id_persona', $id_persona)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function listPersonsTelephone($id_persona)
    {
        $rows = DB::connection('moises')->table('PERSONA_TELEFONO')
        ->select(
            'ID_TELEFONO',
            'ID_PERSONA',
            'ID_TIPOTELEFONO',
            'NUM_TELEFONO',
            'ES_ACTIVO',
            'ES_PRIVADO',
            'COMENTARIO',
            'GTH'
        )
        ->where('ID_PERSONA', $id_persona)
        ->get();
        return $rows;
    }
    public static function addPersonsPhone($data)
    {
        $id_telefono = self::getIdMax('moises', "PERSONA_TELEFONO","ID_TELEFONO")+1;
        $data["id_telefono"] = $id_telefono;
        $result = DB::connection('moises')->table('PERSONA_TELEFONO')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsPhone($data, $id_telefono)
    {
        $result = DB::connection('moises')->table('PERSONA_TELEFONO')
        ->where('id_telefono', $id_telefono)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function listPersonsVirtual($id_persona)
    {
        $rows = DB::connection('moises')->table('PERSONA_VIRTUAL')
        ->select(
            'ID_VIRTUAL',
            'ID_PERSONA',
            'ID_TIPOVIRTUAL',
            'DIRECCION',
            'COMENTARIO',
            'GTH'
        )
        ->where('ID_PERSONA', $id_persona)
        ->get();
        return $rows;
    }
    public static function addPersonsVirtual($data)
    {
        $id_virtual = self::getIdMax('moises', "PERSONA_VIRTUAL","ID_VIRTUAL")+1;
        $data["id_virtual"] = $id_virtual;
        $result = DB::connection('moises')->table('PERSONA_VIRTUAL')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsVirtual($data, $id_virtual)
    {
        $result = DB::connection('moises')->table('PERSONA_VIRTUAL')
        ->where('id_virtual', $id_virtual)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function showPersonsResponsible($id_persona) // -+
    {
        $first = DB::connection('jose')->table('SCHOOL_RESPONSABLE')
        ->where('ID_PERSONA', $id_persona)
        ->first();
        return $first;
    }
    public static function showPersonsNaturalSchool($id_persona) // -+
    {
        $first = DB::connection('moises')->table('PERSONA_NATURAL_SCHOOL')
        ->select(
            'ID_PERSONA',
            'NRO_HERMANOS',
            'UBICA_CENTRO_MED',
            'ID_RESP_PAGO',
            'CON_QUIEN_VIVE',
            'ID_RESP_MATRICULA'
        )
        ->where('ID_PERSONA', $id_persona)
        ->first();
        return $first;
    }
    public static function addPersonsNaturalSchool($data) // -+
    {
        $result = DB::connection('moises')->table('PERSONA_NATURAL_SCHOOL')->insert($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function updatePersonsNaturalSchool($data, $id_persona) // -+
    {
        $result = DB::connection('moises')->table('PERSONA_NATURAL_SCHOOL')
        ->where('id_persona', $id_persona)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function showPeriodsOpen()
    {
        $result = DB::connection('jose')
        ->table('SCHOOL_PERIODO')
        ->where('ESTADO', 'O')
        ->max('ID_PERIODO');
        return $result;
    }
    public static function listPeriods($id_empresa, $estado, $plan_confirmado)
    {
        $rows = DB::connection('eliseo')
        ->table('JOSE.SCHOOL_PERIODO')
        ->select(
            'ID_PERIODO',
            'ANHO_PERIODO',
            'NOMBRE',
            'ESTADO',
            'PLAN_CONFIRMADO',
            'ES_MATRICULA',
            DB::raw(
                "
                TO_CHAR(FECHA_CLOSE,'YYYY/MM/DD') FECHA_CLOSE,
                TO_CHAR(FECHA_OPEN,'YYYY/MM/DD') FECHA_OPEN,
                TO_CHAR(FECHA_CLOSE,'YYYY/MM/DD') FECHA_CLOSE_MAT,
                TO_CHAR(FECHA_OPEN,'YYYY/MM/DD') FECHA_OPEN_MAT,
                CASE ESTADO
                    WHEN 'P' THEN 'PLANIFICACION'
                    WHEN 'O' THEN 'OPERATIVO'
                    WHEN 'C' THEN 'CERRADO'
                    ELSE 'NON'
                END NOMBRE_ESTADO,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE ID_PERSONA_OPEN = PERSONA.ID_PERSONA
                ) NOMBRE_OPEN,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE ID_PERSONA_REOPEN = PERSONA.ID_PERSONA
                ) NOMBRE_REOPEN,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE ID_PERSONA_CLOSE = PERSONA.ID_PERSONA
                ) NOMBRE_CLOSE,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE ID_PERSONA_OPEN_MAT = PERSONA.ID_PERSONA
                ) NOMBRE_OPEN_MAT,
                (
                    SELECT  NOMBRE || ' ' || PATERNO || ' ' || MATERNO
                    FROM MOISES.PERSONA
                    WHERE ID_PERSONA_CLOSE_MAT = PERSONA.ID_PERSONA
                ) NOMBRE_CLOSE_MAT,
                (
                    SELECT
                        Persona.MATERNO AS NOM_EMPRESA
                    FROM
                        ELISEO.CONTA_EMPRESA
                        INNER JOIN ELISEO.Tipo_Empresa
                        ON CONTA_EMPRESA.ID_TIPOEMPRESA = Tipo_Empresa.ID_TIPOEMPRESA
                        INNER JOIN MOISES.Persona_Juridica
                        ON CONTA_EMPRESA.ID_RUC = Persona_Juridica.ID_RUC
                        INNER JOIN MOISES.Persona
                        ON Persona_Juridica.ID_PERSONA = Persona.ID_PERSONA
                    WHERE CONTA_EMPRESA.ID_EMPRESA = SCHOOL_PERIODO.ID_EMPRESA
                ) NOMBRE_EMPRESA
                "
            )
        );
        if($id_empresa)
        {
            $rows->where('ID_EMPRESA', $id_empresa);
        }
        if($estado)
        {
            $rows->where('ESTADO', $estado);
        }
        if($plan_confirmado)
        {
            $rows->where('PLAN_CONFIRMADO', $plan_confirmado);
        }
        $rows->orderBy('ANHO_PERIODO', 'DESC');
        return $rows->get();
    }
    public static function showPeriods($id_periodo)
    {
        $first = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->select(
            'ID_PERIODO',
            'ANHO_PERIODO',
            'NOMBRE',
            'ESTADO',
            'ID_EMPRESA',
            DB::raw(
                "
                CASE ESTADO
                    WHEN '0' THEN 'INACTIVO'
                    WHEN '1' THEN 'EN MARCHA'
                    WHEN '2' THEN 'RESERVACION'
                    ELSE 'NON'
                END NOMBRE_ESTADO
                "
            )
        )
        ->where('ID_PERIODO', $id_periodo)
        ->first();
        return $first;
    }
    public static function showBeforePeriods($id_periodo)
    {
        $periodoActual = self::showPeriods($id_periodo);
        if(!$periodoActual)
        {
            return null;
        }
        $anho_anterior = $periodoActual->anho_periodo - 1;
        $id_empresa = $periodoActual->id_empresa;
        $first = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->select(
            'ID_PERIODO',
            'ANHO_PERIODO',
            'NOMBRE',
            'ESTADO'
        )
        ->where('ANHO_PERIODO', $anho_anterior)
        ->where('ID_EMPRESA', $id_empresa)
        ->first();
        return $first;
    }
    public static function addPeriods($data)
    {
        $id_periodo = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_PERIODO (
            :P_ID_USER,
            :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_ID_EMPRESA,
            :P_ID_PERIODO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_USER', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ENTIDAD', $data['id_entidad'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $data['id_depto'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_EMPRESA', $data['id_empresa'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERIODO', $id_periodo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error,
            "id_periodo" => $id_periodo
        ];
        RETURN $objReturn;
    }
    public static function updatePeriods($data, $id_periodo)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->where('ID_PERIODO', $id_periodo)
        ->update($data);
        return $result;
    }
    public static function updatePeriodsMatricula($data, $id_periodo)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->where('ID_PERIODO', $id_periodo)
        ->update($data);
        return $result;
    }
    public static function deletePeriods($id_periodo)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->where('ID_PERIODO', $id_periodo)
        ->delete();
        return $result;
    }
    public static function periodsConfirm($id_periodo)
    {
        $id_pngcurso = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_CONFIRM_PLAN_PERIODO (
            :P_ID_PERIODO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PERIODO', $id_periodo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function getNextYearPeriod($id_empresa)
    {
        $valor = DB::connection('jose')->table('SCHOOL_PERIODO')
        ->where('ID_EMPRESA', $id_empresa)
        ->max('ANHO_PERIODO');
        if($valor)
        {
            return $valor + 1;
        }
        else
        {
            $valor = DB::raw("TO_CHAR(SYSDATE, 'YYYY')");
            return $valor;
        }
    }
    public static function listPeriodsCalendar($id_periodo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_CALENDARIO')
        ->select(
            'ID_PCALENDARIO',
            'NRO_SEMANAS',
            'TIPO',
            'ID_BIMESTRE',
            'ORDEN',
            DB::raw(
                "
                TO_CHAR(FECHA_DESDE,'YYYY-MM-DD') FECHA_DESDE,
                TO_CHAR(FECHA_HASTA,'YYYY-MM-DD') FECHA_HASTA,
                ORDEN,
                CASE ORDEN
                    WHEN 1 THEN (SELECT  NOMBRE FROM SCHOOL_BIMESTRE WHERE SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE = SCHOOL_BIMESTRE.ID_BIMESTRE)
                    WHEN 3 THEN (SELECT  NOMBRE FROM SCHOOL_BIMESTRE WHERE SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE = SCHOOL_BIMESTRE.ID_BIMESTRE)
                    WHEN 5 THEN (SELECT  NOMBRE FROM SCHOOL_BIMESTRE WHERE SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE = SCHOOL_BIMESTRE.ID_BIMESTRE)
                    WHEN 7 THEN (SELECT  NOMBRE FROM SCHOOL_BIMESTRE WHERE SCHOOL_PERIODO_CALENDARIO.ID_BIMESTRE = SCHOOL_BIMESTRE.ID_BIMESTRE)
                    WHEN 2 THEN 'RECESO'
                    WHEN 4 THEN 'RECESO'
                    WHEN 6 THEN 'RECESO'
                    WHEN 8 THEN 'FINAL'
                    ELSE 'NON'
                END NOMBRE_BIMESTRE
                "
            )
        )
        ->where('ID_PERIODO', $id_periodo)
        ->orderBy('SCHOOL_PERIODO_CALENDARIO.ORDEN', 'ASC')
        ->get();
        return $rows;
    }
    public static function addPeriodsPeriodsCalendar($data)
    {
        $id_pcalendario = self::getIdMax('jose', "SCHOOL_PERIODO_CALENDARIO","ID_PCALENDARIO")+1;
        $data["id_pcalendario"] = $id_pcalendario;
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_CALENDARIO')->insert($data);
        if($result)
        {
            return $id_pcalendario;
        }
        return $result;
    }
    public static function updatePeriodsCalendar($data, $id_pcalendario)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_CALENDARIO')
        ->where('ID_PCALENDARIO', $id_pcalendario)
        ->update($data);
        return $result;
    }
    public static function listPeriodsArea($id_periodo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_CURSO')
        ->join('SCHOOL_CURSO', 'SCHOOL_PERIODO_CURSO.ID_CURSO', '=', 'SCHOOL_CURSO.ID_CURSO')
        ->select(
            'SCHOOL_PERIODO_CURSO.ID_PCURSO',
            'SCHOOL_PERIODO_CURSO.ID_CURSO',
            'SCHOOL_PERIODO_CURSO.ID_CPARENT',
            DB::raw(
                "
                SCHOOL_CURSO.NOMBRE NOMBRE_CURSO,
                SCHOOL_CURSO.ABREVIATURA ABREVIATURA_CURSO
                "
            )
        )
        ->where('ID_PERIODO', $id_periodo)
        ->get();
        return $rows;
    }
    public static function addPeriodsArea($data)
    {
        $id_pcurso = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_PERIODO_CURSO (
            :P_ID_CURSO,
            :P_ID_PERIODO,
            :P_ID_CPARENT,
            :P_ID_PCURSO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_CURSO', $data['id_curso'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERIODO', $data['id_periodo'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CPARENT', $data['id_cparent'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PCURSO', $id_pcurso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_pcurso" => $id_pcurso,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function deletePeriodsArea($id_pcurso)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_CURSO')
        ->where('ID_PCURSO', $id_pcurso)
        ->delete();
        return $result;
    }

    public static function showPlansStageGradeArea($id_pngcurso)
    {
        $row = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_GRADO_CURSO')
        ->where('ID_PNGCURSO', $id_pngcurso)
        ->first();
        return $row;
    }
    public static function addPlansStageGradeArea($data)
    {
        $id_pngcurso = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_CREATE_PLAN_NGAREA (
            :P_ID_PNGRADO,
            :P_ID_CURSO,
            :P_ID_CPARENT,
            :P_HORAS,
            :P_ID_USER,
            :P_ID_PNGCURSO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGRADO', $data['id_pngrado'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CURSO', $data['id_curso'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CPARENT', $data['id_cparent'], PDO::PARAM_INT);
        $stmt->bindParam(':P_HORAS', $data['horas'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_USER', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGCURSO', $id_pngcurso, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            'id_pngcurso' => $id_pngcurso,
            "error" => $error,
            "msg_error" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function updatePlansStageGradeArea($data)
    {
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_EDIT_PLAN_NGAREA (
            :P_ID_PNGCURSO,
            :P_HORAS,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGCURSO', $data['id_pngcurso'], PDO::PARAM_INT);
        $stmt->bindParam(':P_HORAS', $data['horas'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function deletePlansStageGradeArea($id_pngcurso)
    {
        $result = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_GRADO_CURSO')
        ->where('ID_PNGCURSO', $id_pngcurso)
        ->delete();
        return $result;
    }
    public static function deletePlansStageGradeArea_($id_pngrado, $id_curso)
    {
        $result = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_GRADO_CURSO')
        ->where('ID_PNGRADO', $id_pngrado)
        ->where('ID_CURSO', $id_curso)
        ->delete();
        return $result;
    }

    public static function listPlansStageConfigEval($id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CONFIG_NOTA')
        ->select(
            'ID_PNCNOTA',
            'ID_PNIVEL',
            'TIPO_NOTA',
            'DESDE',
            'HASTA',
            'ESTADO_NOTA',
            'DETALLE'
        )
        ->where('ID_PNIVEL', $id_pnivel)
        ->orderBy('DESDE','DESC')
        ->get();
        return $rows;
    }
    public static function addPlansStageConfigEval($data)
    {
        $id = self::getIdMax('jose', "SCHOOL_PLAN_NIVEL_CONFIG_NOTA", "ID_PNCNOTA")+1;
        $data["id_pncnota"] = $id;
        $result = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CONFIG_NOTA')->insert($data);
        if($result)
        {
            return $id;
        }
        return $result;
    }
    public static function updatePlansStageConfigEval($data, $id_pncnota)
    {
        $result = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CONFIG_NOTA')
        ->where('ID_PNCNOTA', $id_pncnota)
        ->update($data);
        if($result)
        {
            return $result;
        }
        return false;
    }
    public static function showPlansStages($id_pnivel)
    {
        $first = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL')
        ->select(
            'ID_PNIVEL',
            'ID_PERIODO',
            'ID_NIVEL',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_NIVEL
                    WHERE SCHOOL_NIVEL.ID_NIVEL = SCHOOL_PLAN_NIVEL.ID_NIVEL
                ) NOMBRE_NIVEL
                "
            )
        )
        ->where('ID_PNIVEL', $id_pnivel)
        ->first();
        return $first;
    }
    public static function listPlansStages($id_periodo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL')
        ->join('SCHOOL_NIVEL', 'SCHOOL_PLAN_NIVEL.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
        ->select(
            'SCHOOL_PLAN_NIVEL.ID_PNIVEL',
            'SCHOOL_PLAN_NIVEL.ID_PERIODO',
            'SCHOOL_PLAN_NIVEL.ID_NIVEL',
            'SCHOOL_NIVEL.NOMBRE AS NOMBRE_NIVEL'
        )
        ->where('ID_PERIODO', $id_periodo)
        ->orderBy('SCHOOL_NIVEL.ORDEN','ASC')
        ->get();
        return $rows;
    }
    public static function listPlansSANone($id_pnivel, $id_periodo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_CURSO')
        ->leftJoin('SCHOOL_PLAN_NIVEL_CURSO', function($join) use($id_pnivel) {
            $join->on('SCHOOL_PLAN_NIVEL_CURSO.ID_CURSO', '=', 'SCHOOL_PERIODO_CURSO.ID_CURSO')
            ->where('SCHOOL_PLAN_NIVEL_CURSO.ID_PNIVEL', '=', $id_pnivel);
        })
        ->select(
            'SCHOOL_PLAN_NIVEL_CURSO.ID_PNCURSO',
            'SCHOOL_PLAN_NIVEL_CURSO.ID_CPARENT',
            'SCHOOL_PLAN_NIVEL_CURSO.PARENT',
            'SCHOOL_PERIODO_CURSO.ID_CURSO',
            DB::raw(
                "
                (
                    SELECT SCHOOL_CURSO.NOMBRE
                    FROM SCHOOL_CURSO
                    WHERE SCHOOL_CURSO.ID_CURSO = SCHOOL_PERIODO_CURSO.ID_CURSO
                ) NOMBRE_CURSO
                "
            )
        )
        ->where('SCHOOL_PERIODO_CURSO.ID_PERIODO', $id_periodo)
        ->whereNull('SCHOOL_PLAN_NIVEL_CURSO.ID_PNCURSO')
        ->orderBy('NOMBRE_CURSO','ASC')
        ->get();
        return $rows;
    }
    public static function listPlansSAreas($id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CURSO')
        ->join('SCHOOL_CURSO', 'SCHOOL_PLAN_NIVEL_CURSO.ID_CURSO', '=', 'SCHOOL_CURSO.ID_CURSO')
        ->select(
            'SCHOOL_PLAN_NIVEL_CURSO.PARENT',
            'SCHOOL_PLAN_NIVEL_CURSO.ID_PNCURSO',
            'SCHOOL_PLAN_NIVEL_CURSO.ID_CURSO',
            'SCHOOL_PLAN_NIVEL_CURSO.ID_CPARENT',
            'SCHOOL_CURSO.NOMBRE AS NOMBRE_CURSO',
            DB::raw(
                "
                NVL(SCHOOL_PLAN_NIVEL_CURSO.PARENT, SCHOOL_PLAN_NIVEL_CURSO.ID_PNCURSO) ORDEN1,
                CASE WHEN SCHOOL_PLAN_NIVEL_CURSO.PARENT IS NULL THEN 1 ELSE 2 END AS ORDEN2
                "
            )
        )
        ->where('SCHOOL_PLAN_NIVEL_CURSO.ID_PNIVEL', $id_pnivel)
        ->orderBy('ORDEN1','ASC')
        ->orderBy('ORDEN2','ASC')
        ->get();
        return $rows;
    }
    public static function listPlansSGAreas_($id_pnivel, $id_curso)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_GRADO')
        ->join('SCHOOL_GRADO', 'SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
        ->join('SCHOOL_PLAN_NIVEL_CURSO', 'SCHOOL_PLAN_NIVEL_GRADO.ID_PNIVEL', '=', 'SCHOOL_PLAN_NIVEL_CURSO.ID_PNIVEL')
        ->leftJoin('SCHOOL_PLAN_NIVEL_GRADO_CURSO', function($join) use($id_curso) {
            $join->on('SCHOOL_PLAN_NIVEL_GRADO.ID_PNGRADO', '=', 'SCHOOL_PLAN_NIVEL_GRADO_CURSO.ID_PNGRADO')
            ->where('SCHOOL_PLAN_NIVEL_GRADO_CURSO.ID_CURSO', '=', $id_curso);
        })
        ->select(
            'SCHOOL_PLAN_NIVEL_CURSO.PARENT',
            'SCHOOL_PLAN_NIVEL_GRADO_CURSO.HORAS',
            'SCHOOL_PLAN_NIVEL_GRADO_CURSO.ID_PNGCURSO',
            'SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO',
            'SCHOOL_PLAN_NIVEL_GRADO.ID_PNGRADO',
            'SCHOOL_GRADO.ORDEN'
        )
        ->where('SCHOOL_PLAN_NIVEL_GRADO.ID_PNIVEL', $id_pnivel)
        ->where('SCHOOL_PLAN_NIVEL_CURSO.ID_CURSO', $id_curso)
        ->orderBy('SCHOOL_GRADO.ORDEN','ASC')
        ->get();
        return $rows;
    }
    public static function showPlansSAreas($id_pncurso)
    {
        $row = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CURSO')
        ->where('SCHOOL_PLAN_NIVEL_CURSO.ID_PNCURSO', $id_pncurso)
        ->first();
        return $row;
    }
    public static function addPlansSAreas($data)
    {
        $id_pncurso = self::getIdMax('jose', "SCHOOL_PLAN_NIVEL_CURSO", "ID_PNCURSO")+1;
        $data["id_pncurso"] = $id_pncurso;
        $result = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CURSO')->insert($data);
        if($result)
        {
            return $id_pncurso;
        }
        return $result;
    }
    public static function deletePlansSAreas($id_pncurso)
    {
        $result = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_CURSO')
        ->where('ID_PNCURSO', $id_pncurso)
        ->delete();
        return $result;
    }
    public static function listPlansNGrade($id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_GRADO')
        ->join('SCHOOL_GRADO', 'SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
        ->select(
            'SCHOOL_PLAN_NIVEL_GRADO.ID_PNGRADO',
            'SCHOOL_PLAN_NIVEL_GRADO.ID_PNIVEL',
            'SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO',
            'SCHOOL_PLAN_NIVEL_GRADO.TIPO_NOTA',
            'SCHOOL_PLAN_NIVEL_GRADO.THORAS',
            'SCHOOL_PLAN_NIVEL_GRADO.NOTA_MIN_APROB',
            'SCHOOL_GRADO.NOMBRE AS NOMBRE_GRADO',
            'SCHOOL_GRADO.CORTO'
        )
        ->where('ID_PNIVEL', $id_pnivel)
        ->orderBy('SCHOOL_GRADO.ORDEN','ASC')
        ->get();
        return $rows;
    }
    public static function listPlansSGArea($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL_GRADO_CURSO')
        ->select(
            'ID_PNGCURSO',
            'ID_PNGRADO',
            'ID_CURSO',
            'ID_CPARENT',
            'HORAS',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_CURSO
                    WHERE SCHOOL_CURSO.ID_CURSO = SCHOOL_PLAN_NIVEL_GRADO_CURSO.ID_CURSO
                ) NOMBRE_CURSO
                "
            )
        )
        ->where('ID_PNGRADO', $id_pngrado)
        ->get();
        return $rows;
    }
    public static function updatePlansStageGrade($data, $id_pngrado)
    {
        $result = false;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_UPDATE_FORMATO_EVAL (
            :P_ID_PNGRADO,
            :P_TIPO_NOTA,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGRADO', $id_pngrado, PDO::PARAM_INT);
        $stmt->bindParam(':P_TIPO_NOTA', $data['tipo_nota'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error
        ];
        return $objReturn;
    }
    public static function updateSumPlansStageGrade($id_pngrado) // -+
    {
        $result = DB::connection('eliseo')->statement("UPDATE JOSE.SCHOOL_PLAN_NIVEL_GRADO 
            SET THORAS = NVL((SELECT SUM(HORAS) FROM JOSE.SCHOOL_PLAN_NIVEL_GRADO_CURSO WHERE SCHOOL_PLAN_NIVEL_GRADO.ID_PNGRADO = SCHOOL_PLAN_NIVEL_GRADO_CURSO.ID_PNGRADO ),0) 
            WHERE ID_PNGRADO = ".$id_pngrado);
        return $result;
    }
    public static function showPeriodsStages($id_pnivel)
    {
        $first = DB::connection('jose')->table('SCHOOL_PERIODO_NIVEL')
        ->select(
            'ID_PNIVEL',
            'ID_PERIODO',
            'ID_NIVEL',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_NIVEL
                    WHERE SCHOOL_NIVEL.ID_NIVEL = SCHOOL_PERIODO_NIVEL.ID_NIVEL
                ) NOMBRE_NIVEL
                "
            )
        )
        ->where('ID_PNIVEL', $id_pnivel)
        ->first();
        return $first;
    }
    public static function listPeriodsStages($id_periodo, $id_institucion)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NIVEL')
        ->join('SCHOOL_PERIODO', 'SCHOOL_PERIODO_NIVEL.ID_PERIODO', '=', 'SCHOOL_PERIODO.ID_PERIODO')
        ->join('SCHOOL_NIVEL', 'SCHOOL_PERIODO_NIVEL.ID_NIVEL', '=', 'SCHOOL_NIVEL.ID_NIVEL')
        ->select(
            'SCHOOL_PERIODO_NIVEL.ID_PNIVEL',
            'SCHOOL_PERIODO_NIVEL.ID_PERIODO',
            'SCHOOL_PERIODO_NIVEL.ID_NIVEL',
            'SCHOOL_PERIODO.ANHO_PERIODO',
            'SCHOOL_NIVEL.ORDEN',
            DB::raw(
                "
                SCHOOL_NIVEL.NOMBRE NOMBRE_NIVEL
                "
            )
        )
        ->where('SCHOOL_PERIODO_NIVEL.ID_PERIODO', $id_periodo)
        ->where('SCHOOL_PERIODO_NIVEL.ID_INSTITUCION', $id_institucion)
        ->orderBy('SCHOOL_NIVEL.ORDEN', 'ASC')
        ->get();
        return $rows;
    }
    public static function listPeriodsNGrade($id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->join('SCHOOL_GRADO', 'SCHOOL_PERIODO_NGRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
        ->select(
            'SCHOOL_PERIODO_NGRADO.ID_PNGRADO',
            'SCHOOL_PERIODO_NGRADO.ID_PNIVEL',
            'SCHOOL_PERIODO_NGRADO.ID_GRADO',
            'SCHOOL_PERIODO_NGRADO.TIPO_NOTA',
            'SCHOOL_PERIODO_NGRADO.THORAS',
            'SCHOOL_PERIODO_NGRADO.NOTA_MIN_APROB',
            'SCHOOL_PERIODO_NGRADO.TNRO_CUPO',
            'SCHOOL_PERIODO_NGRADO.SINCRONIZADO',
            'SCHOOL_PERIODO_NGRADO.TURNO',
            'SCHOOL_GRADO.NOMBRE AS NOMBRE_GRADO',
            'SCHOOL_GRADO.ORDEN'
        )
        ->where('SCHOOL_PERIODO_NGRADO.ID_PNIVEL', $id_pnivel)
        ->orderBy('SCHOOL_GRADO.ORDEN', 'ASC')
        ->get();
        return $rows;
    }
    public static function listPeriodsSGrades($id_pnivel)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->select(
            'SCHOOL_PERIODO_NGRADO.ID_PNGRADO',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_GRADO
                    WHERE SCHOOL_GRADO.ID_GRADO = SCHOOL_PERIODO_NGRADO.ID_GRADO
                ) NOMBRE_GRADO
                "
            )
        )
        ->where('SCHOOL_PERIODO_NGRADO.ID_PNIVEL', $id_pnivel)
        ->get();
        return $rows;
    }
    public static function listPeriodsSGSections($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->select(
            'SCHOOL_PERIODO_NGSECCION.ID_PNGRADO',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_SECCION
                    WHERE SCHOOL_SECCION.ID_SECCION = SCHOOL_PERIODO_NGSECCION.ID_SECCION
                ) NOMBRE_SECCION
                "
            )
        )
        ->where('SCHOOL_PERIODO_NGSECCION.ID_PNGRADO', $id_pngrado)
        ->get();
        return $rows;
    }
    public static function listPeriodsNGNone($id_nivel, $id_periodo)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PLAN_NIVEL')
        ->join('SCHOOL_PLAN_NIVEL_GRADO', 'SCHOOL_PLAN_NIVEL.ID_PNIVEL', '=', 'SCHOOL_PLAN_NIVEL_GRADO.ID_PNIVEL')
        ->join('SCHOOL_GRADO', 'SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO', '=', 'SCHOOL_GRADO.ID_GRADO')
        ->select(
            'SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO',
            'SCHOOL_PLAN_NIVEL_GRADO.TIPO_NOTA',
            'SCHOOL_PLAN_NIVEL_GRADO.THORAS',
            'SCHOOL_PLAN_NIVEL_GRADO.NOTA_MIN_APROB',
            'SCHOOL_GRADO.ORDEN',
            DB::raw(
                "
                /* (
                    SELECT NOMBRE
                    FROM SCHOOL_GRADO
                    WHERE SCHOOL_GRADO.ID_GRADO = SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO
                ) NOMBRE_GRADO */
                SCHOOL_GRADO.NOMBRE NOMBRE_GRADO
                "
            )
        )
        ->where('SCHOOL_PLAN_NIVEL.ID_NIVEL', $id_nivel)
        ->where('SCHOOL_PLAN_NIVEL.ID_PERIODO', $id_periodo)
        ->whereRaw("SCHOOL_PLAN_NIVEL_GRADO.ID_GRADO NOT IN (
            SELECT PER_NGRA.ID_GRADO 
            FROM 
                SCHOOL_PERIODO_NIVEL PER_NIV
                INNER JOIN
                SCHOOL_PERIODO_NGRADO PER_NGRA
                ON
                PER_NGRA.ID_PNIVEL = PER_NIV.ID_PNIVEL
            WHERE
            PER_NIV.ID_NIVEL = $id_nivel AND PER_NIV.ID_PERIODO = $id_periodo
            )
            "
        )
        ->orderBy('SCHOOL_GRADO.ORDEN', 'ASC')
        ->get();
        return $rows;
    }
    public static function listPeriodsNGAreas($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGCURSO')
        ->select(
            'ID_PNGCURSO',
            'ID_PNGRADO',
            'ID_CURSO',
            'ID_CPARENT',
            'PARENT',
            'HORAS',
            'ES_TALLER_ELECTIVO',
            'NRO_CUPO',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_CURSO
                    WHERE SCHOOL_CURSO.ID_CURSO = SCHOOL_PERIODO_NGCURSO.ID_CURSO
                ) NOMBRE_CURSO,
                NVL(PARENT, ID_PNGCURSO) ORDEN1,
                CASE WHEN PARENT IS NULL THEN 1 ELSE 2 END ORDEN2
                "
            )
        )
        ->where('ID_PNGRADO', $id_pngrado)
        ->orderBy('ORDEN1', 'ASC')
        ->orderBy('ORDEN2', 'ASC')
        ->orderBy('ID_PNGCURSO', 'DESC')
        ->get();
        return $rows;
    }
    public static function addPeriodsNGAreaSync($data)
    {
        $id_periodo = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('eliseo')->getPdo();
        $stmt = $pdo->prepare("BEGIN JOSE.PKG_SCHOOLS.SP_SYNC_PERIODO_NGCURSO (
            :P_ID_USER,
            :P_ID_PNGRADO,
            :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_USER', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGRADO', $data['id_pngrado'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ENTIDAD', $data['id_entidad'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $data['id_depto'], PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

        $stmt->execute();
        $objReturn = [
            "error" => $error,
            "msg_error" => $msg_error
        ];
        RETURN $objReturn;
    }
    public static function listPeriodsNGSection($id_pngrado)
    {
        $rows = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->select(
            'ID_PNGSECCION',
            'ID_PNGRADO',
            'ID_SECCION',
            'TIPO_NOTA',
            'HORAS',
            'NOTA_MIN_APROB',
            'NRO_CUPO',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_SECCION
                    WHERE SCHOOL_SECCION.ID_SECCION = SCHOOL_PERIODO_NGSECCION.ID_SECCION
                ) NOMBRE_SECCION
                "
            )
        )
        ->where('ID_PNGRADO', $id_pngrado)
        ->get();
        return $rows;
    }
    public static function showPeriodsNGSection($id_pngseccion)
    {
        $first = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->select(
            'ID_PNGSECCION',
            'ID_PNGRADO',
            'ID_SECCION',
            'TIPO_NOTA',
            'HORAS',
            'NOTA_MIN_APROB',
            'NRO_CUPO',
            DB::raw(
                "
                (
                    SELECT NOMBRE
                    FROM SCHOOL_SECCION
                    WHERE SCHOOL_SECCION.ID_SECCION = SCHOOL_PERIODO_NGSECCION.ID_SECCION
                ) NOMBRE_SECCION
                "
            )
        )
        ->where('ID_PNGSECCION', $id_pngseccion)
        ->first();
        return $first;
    }
    public static function addPeriodsStagesGradesSections($data)
    {
        $id_pngseccion = 0;
        $error = 0;
        $msg_error = str_repeat("0", 200);
        $objReturn = [];

        $pdo = DB::connection('jose')->getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SCHOOLS.SP_CREATE_PERIODO_NG_SECCION (
            :P_ID_PNGRADO,
            :P_ID_SECCION,
            :P_NRO_CUPO,
            :ID_USER,
            :P_ID_PNGSECCION,
            :P_ERROR,
            :P_MSGERROR
            );
            END;"
        );
        $stmt->bindParam(':P_ID_PNGRADO', $data['id_pngrado'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_SECCION', $data['id_seccion'], PDO::PARAM_INT);
        $stmt->bindParam(':P_NRO_CUPO', $data['nro_cupo'], PDO::PARAM_INT);
        $stmt->bindParam(':ID_USER', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PNGSECCION', $id_pngseccion, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        $objReturn = [
            "id_pngseccion" => $id_pngseccion,
            "error" => $error,
            "message" => $msg_error
        ];
        return $objReturn;
    }
    public static function updatePeriodsNGSections($data, $id_pngseccion)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->where('ID_PNGSECCION', $id_pngseccion)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function deletePeriodsNGSections($id_pngseccion)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->where('id_pngseccion', $id_pngseccion)
        ->delete();
        return $result;
    }
    public static function deletePeriodsNGSectionsByGrade($id_pngrado)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->where('ID_PNGRADO', $id_pngrado)
        ->delete();
        return $result;
    }
    public static function updatePeriodsNGrades($data, $id_pngrado)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->where('ID_PNGRADO', $id_pngrado)
        ->update($data);
        if($result)
        {
            return $data;
        }
        return $result;
    }
    public static function addPeriodsSGrades($data)
    {
        $id_pngrado = self::getIdMax('jose', "SCHOOL_PERIODO_NGRADO","ID_PNGRADO")+1;
        $data["id_pngrado"] = $id_pngrado;
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')->insert($data);
        if($result)
        {
            return $id_pngrado;
        }
        return $result;
    }
    public static function deletePeriodsSGrades($id_pngrado)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGRADO')
        ->where('ID_PNGRADO', $id_pngrado)
        ->delete();
        return $result;
    }
    public static function sumPeriodsNGSectionsCupos($id_pngrado)
    {
        $result = DB::connection('jose')->table('SCHOOL_PERIODO_NGSECCION')
        ->where('ID_PNGRADO', $id_pngrado)
        ->sum('NRO_CUPO');
        return $result;
    }

}

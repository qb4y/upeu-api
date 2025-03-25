<?php
namespace App\Http\Controllers\Schools\Util;
use Exception;
use App\Http\Controllers\Schools\Validation\SetupValidation;
use Illuminate\Support\Facades\Input;

class SetupUtil
{
    private static $message;
    private static $data;
    public function __construct()
    {
    }
    public static function getData()
    {
        return self::$data;
    }
    public static function getMessage()
    {
        return self::$message;
    }

    public static function validPersonsEmergency()
    {
        SetupValidation::rulesPersonsEmergency();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_persona' => Input::get('id_persona'),
            'tipoparentesco_id' => Input::get('tipoparentesco_id'),
            'id_encargado' => Input::get('id_encargado')
        ];
        return true;
    }
    public static function validPersonsMobility()
    {
        SetupValidation::rulesPersonsMobility();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_persona' => Input::get('id_persona'),
            'tipoparentesco_id' => Input::get('tipoparentesco_id'),
            'id_encargado' => Input::get('id_encargado'),
            'tipo' => Input::get('tipo'),
            'placa' => Input::get('placa')
        ];
        return true;
    }
    public static function validRecordMedical()
    {
        SetupValidation::rulesRecordMedical();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_alumno' => input::get('id_alumno'),
            'seguro_accidente' => input::get('seguro_accidente'),
            'essalud' => input::get('essalud'),
            'hospital_essalud' => input::get('hospital_essalud'),
            'tipo_sangre' => input::get('tipo_sangre'),
            'tabique_desviado' => input::get('tabique_desviado'),
            'sangrado_nasal' => input::get('sangrado_nasal'),
            'usa_braquet' => input::get('usa_braquet'),
            'usa_lentes' => input::get('usa_lentes'),
            'alergias' => input::get('alergias'),
            'id_alergia' => input::get('id_alergia'),
            'medicina_alergia' => input::get('medicina_alergia'),
            'cuenta_vac_refuerzo' => input::get('cuenta_vac_refuerzo'),
            'enfermedades' => input::get('enfermedades'),
            'medicamentos' => input::get('medicamentos'),
            'observacion_general' => input::get('observacion_general'),
            'operaciones' => input::get('operaciones'),
            'lugar_atenc_emerg' => input::get('lugar_atenc_emerg'),
            'medicamentos_cas_emer' => input::get('medicamentos_cas_emer'),
            'usa_inhaladores' => input::get('usa_inhaladores'),
            // 'caso_presen_37g_recibe' => input::get('caso_presen_37g_recibe'),
            // 'caso_presen_38g_recibe' => input::get('caso_presen_38g_recibe'),
            // 'presenta_convul_febril' => input::get('presenta_convul_febril'),
            'peso' => input::get('peso'),
            'talla' => input::get('talla'),
            // 'toma_leche' => input::get('toma_leche'),
            // 'tipo_prepara_recibe' => input::get('tipo_prepara_recibe'),
            // 'frutas' => input::get('frutas')
        ];
        return true;
    }

    public static function validPersons()
    {
        SetupValidation::rulesPersons();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'nombre' => Input::get('nombre'),
            'paterno' => Input::get('paterno'),
            'materno' => Input::get('materno')
        ];
        return true;
    }
    public static function validPersonsAddress()
    {
        SetupValidation::rulesPersonsAddress();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_persona' => Input::get('id_persona'),
            'id_tipodireccion' => Input::get('id_tipodireccion'),
            'id_ubigueo' => Input::get('id_ubigueo'),
            'direccion' => Input::get('direccion'),
            'es_activo' => Input::get('es_activo'),
            'map_latitud' => Input::get('map_latitud'),
            'map_longitud' => Input::get('map_longitud'),
            'comentario' => Input::get('comentario')
        ];
        return true;
    }
    public static function validPersonsDocument()
    {
        SetupValidation::rulesPersonsDocument();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_persona' => Input::get('id_persona'),
            'id_tipodocumento' => Input::get('id_tipodocumento'),
            'num_documento' => Input::get('num_documento')
        ];
        return true;
    }
    public static function validPersonsNatural()
    {
        SetupValidation::rulesPersonsNatural();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            // 'id_persona' => Input::get('id_persona'),
            'id_tipotratamiento' => Input::get('id_tipotratamiento'),
            'id_tipoestadocivil' => Input::get('id_tipoestadocivil'),
            'id_tipopais' => Input::get('id_tipopais'),
            'id_nacionalidad' => Input::get('id_nacionalidad'),
            'id_tiposangre' => Input::get('id_tiposangre'),
            'sexo' => Input::get('sexo'),
            'fec_nacimiento' => Input::get('fec_nacimiento'),
            'fec_defuncion' => Input::get('fec_defuncion')
        ];
        return true;
    }
    public static function validPersonsNaturalReligion()
    {
        SetupValidation::rulesPersonsNaturalReligion();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_iglesia' => Input::get('id_iglesia'),
            'id_tiporeligion' => Input::get('id_tiporeligion'),
            'fec_baustimo' => Input::get('fec_baustimo'),
            'comentario' => Input::get('comentario')
        ];
        return true;
    }
    public static function validPersonsPhone()
    {
        SetupValidation::rulesPersonsPhone();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_persona' => Input::get('id_persona'),
            'id_tipotelefono' => Input::get('id_tipotelefono'),
            'num_telefono' => Input::get('num_telefono'),
            'es_activo' => Input::get('es_activo'),
            'es_privado' => Input::get('es_privado'),
            'comentario' => Input::get('comentario'),
            'gth' => Input::get('gth'),
            'operador_movil' => Input::get('operador_movil')
        ];
        return true;
    }
    public static function validPersonsVirtual()
    {
        SetupValidation::rulesPersonsVirtual();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_persona' => Input::get('id_persona'),
            'id_tipovirtual' => Input::get('id_tipovirtual'),
            'direccion' => Input::get('direccion'),
            'comentario' => Input::get('comentario'),
            'gth' => Input::get('gth')
        ];
        return true;
    }
    public static function validPersonsNaturalSchool()
    {
        SetupValidation::rulesPersonsNaturalSchool();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'nro_hermanos' => Input::get('nro_hermanos'),
            'ubica_centro_med' => Input::get('ubica_centro_med'),
            'id_resp_pago' => Input::get('id_resp_pago'),
            'con_quien_vive' => Input::get('con_quien_vive'),
            'id_resp_matricula' => Input::get('id_resp_matricula')
        ];
        return true;
    }
    public static function validConfigs()
    {
        SetupValidation::rulesConfigs();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_nivel' => Input::get('id_nivel'),
            'codigo_ugel' => Input::get('codigo_ugel')
        ];
        return true;
    }
    public static function validGrades()
    {
        SetupValidation::rulesGrades();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'codigo' => Input::get('codigo'),
            'nombre' => Input::get('nombre'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
    public static function validStages()
    {
        SetupValidation::rulesStages();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'codigo' => Input::get('codigo'),
            'nombre' => Input::get('nombre'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
    public static function validSections()
    {
        SetupValidation::rulesSections();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'nombre' => Input::get('nombre'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
    public static function validTypeDiscounts()
    {
        SetupValidation::rulesTypeDiscounts();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'nombre' => Input::get('nombre'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
    public static function validTypePayments()
    {
        SetupValidation::rulesTypePayments();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'nombre' => Input::get('nombre'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
    public static function validStagesGrades()
    {
        SetupValidation::rulesStagesGrades();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_config' => Input::get('id_config'),
            'id_grado' => Input::get('id_grado')
        ];
        return true;
    }
    public static function validVacants()
    {
        SetupValidation::rulesVacants();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'nro_vacante' => Input::get('nro_vacante'),
            'estado' => Input::get('estado'),
            'id_nivel' => Input::get('id_nivel'),
            'id_grado' => Input::get('id_grado'),
            'id_seccion' => Input::get('id_seccion')
        ];
        return true;
    }
    public static function validCriterions()
    {
        SetupValidation::rulesCriterions();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_ngrado' => Input::get('id_ngrado'),
            'detalle' => Input::get('detalle'),
            'importe' => Input::get('importe'),
            'tipo' => Input::get('tipo')
        ];
        return true;
    }
    public static function validPeriods()
    {
        SetupValidation::rulesPeriods();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'anho_periodo' => Input::get('anho_periodo'),
            'nombre' => Input::get('nombre'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
}

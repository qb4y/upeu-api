<?php
namespace App\Http\Controllers\Schools\Validation;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class SetupValidation
{
    private static $message;
    private static $rules;
    public function __construct()
    {
    }
    public static function getMessage()
    {
        return self::$message;
    }
    public static function isValid()
    {
        $validator = Validator::make(Input::all(), self::$rules);
        if ($validator->fails())
        {
            self::$message = implode(" ",$validator->messages()->all());
            return false;
        }
        return true;
    }

    public static function rulesPersonsEmergency()
    {
        self::$rules = [
            'id_persona' => ['required'],
            'tipoparentesco_id' => ['nullable'],
            'id_encargado' => ['required']
        ];
    }
    public static function rulesPersonsMobility()
    {
        self::$rules = [
            'id_persona' => ['required'],
            'tipoparentesco_id' => ['nullable'],
            'id_encargado' => ['required'],
            'tipo' => ['nullable'],
            'placa' => ['nullable']
        ];
    }
    public static function rulesRecordMedical()
    {
        self::$rules = [
            'id_alumno' => ['required'],
            'seguro_accidente' => ['required'],
            'essalud' => ['nullable'],
            'hospital_essalud' => ['nullable'],
            'tipo_sangre' => ['nullable'],
            'tabique_desviado' => ['required'],
            'sangrado_nasal' => ['required'],
            'usa_braquet' => ['required'],
            'usa_lentes' => ['required'],
            'alergias' => ['nullable'],
            'id_alergia' => ['nullable'],
            'medicina_alergia' => ['nullable'],
            'cuenta_vac_refuerzo' => ['required'],
            'enfermedades' => ['nullable'],
            'medicamentos' => ['nullable'],
            'observacion_general' => ['nullable'],
            'operaciones' => ['nullable'],
            'lugar_atenc_emerg' => ['required'],
            'medicamentos_cas_emer' => ['required'],
            'usa_inhaladores' => ['required'],
            // 'caso_presen_37g_recibe' => ['nullable'],
            // 'caso_presen_38g_recibe' => ['nullable'],
            // 'presenta_convul_febril' => ['nullable'],
            'peso' => ['required'],
            'talla' => ['required'],
            // 'toma_leche' => ['nullable'],
            // 'tipo_prepara_recibe' => ['nullable'],
            // 'frutas' => ['nullable']
        ];
    }

    public static function rulesPersons()
    {
        self::$rules = [
            'nombre' => ['required', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'paterno' => ['required', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'materno' => ['required', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/']
        ];
    }
    public static function rulesPersonsAddress()
    {
        self::$rules = [
            'id_persona' => ['required', 'regex:/^[0-9\s]+$/'],
            'id_tipodireccion' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_ubigueo' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'direccion' => ['required', 'max:1000', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'es_activo' => ['required', 'max:1', 'regex:/^[0-1\s]+$/'],
            'map_latitud' => ['nullable', 'max:126', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'map_longitud' => ['nullable', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'comentario' => ['nullable', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/']
        ];
    }
    public static function rulesPersonsDocument()
    {
        self::$rules = [
            'id_persona' => ['required', 'regex:/^[0-9\s]+$/'],
            'id_tipodocumento' => ['required', 'regex:/^[0-9\s]+$/'],
            'num_documento' => ['required', 'max:20', 'regex:/^[0-9\s]+$/']
        ];
    }
    public static function rulesPersonsNatural()
    {
        self::$rules = [
            'id_tipotratamiento' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_tipoestadocivil' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_tipopais' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_nacionalidad' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_tiposangre' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'sexo' => ['nullable', 'max:10', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'fec_nacimiento' => ['nullable', 'date'],
            'fec_defuncion' => ['nullable', 'date']
        ];
    }
    public static function rulesPersonsNaturalReligion()
    {
        self::$rules = [
            'id_iglesia' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_tiporeligion' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'fec_baustimo' => ['nullable', 'date'],
            'comentario' => ['nullable', 'max:50', 'regex:/^[a-zA-Z0-9\s]+$/']
        ];
    }
    public static function rulesPersonsPhone()
    {
        self::$rules = [
            'id_persona' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_tipotelefono' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'num_telefono' => ['nullable', 'max:100','regex:/^[0-9\s]+$/'],
            'es_activo' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'es_privado' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'comentario' => ['nullable', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'gth' => ['nullable', 'max:5', 'regex:/^[0-9\s]+$/'],
            'operador_movil' => ['nullable', 'max:2', 'regex:/^[a-zA-Z0-9\s]+$/']
        ];
    }
    public static function rulesPersonsVirtual()
    {
        self::$rules = [
            'id_persona' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'id_tipovirtual' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'direccion' => ['nullable', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'comentario' => ['nullable', 'max:255', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'gth' => ['nullable', 'max:5', 'regex:/^[0-9\s]+$/']
        ];
    }
    public static function rulesPersonsNaturalSchool()
    {
        self::$rules = [
            'nro_hermanos' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'ubica_centro_med' => ['nullable', 'max:100', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'id_resp_pago' => ['nullable', 'regex:/^[0-9\s]+$/'],
            'con_quien_vive' => ['nullable', 'max:100', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'id_resp_matricula' => ['nullable', 'regex:/^[0-9\s]+$/']
        ];
    }
    public static function rulesConfigs()
    {
        self::$rules = [
            'id_nivel' => ['required', 'regex:/^[0-9\s]+$/'],
            'codigo_ugel' => ['required', 'max:40']
        ];
    }
    public static function rulesGrades()
    {
        self::$rules = [
            'codigo' => ['required', 'max:20', 'regex:/^[0-9\s]+$/'],
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesStages()
    {
        self::$rules = [
            'codigo' => ['required', 'max:20', 'regex:/^[0-9\s]+$/'],
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesTypeDiscounts()
    {
        self::$rules = [
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesTypePayments()
    {
        self::$rules = [
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesStagesGrades()
    {
        self::$rules = [
            'id_config' => ['required', 'numeric'],
            'id_grado' => ['required', 'numeric']
        ];
    }
    public static function rulesSections()
    {
        self::$rules = [
            'nombre' => ['required', 'max:100', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesVacants()
    {
        self::$rules = [
            'nro_vacante' => ['required', 'numeric'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/'],
            'id_nivel' => ['required', 'numeric'],
            'id_grado' => ['required', 'numeric'],
            'id_seccion' => ['required', 'numeric']
        ];
    }
    public static function rulesCriterions()
    {
        self::$rules = [
            'id_ngrado' => ['required', 'numeric'],
            'detalle' => ['required', 'max:100'],
            'importe' => ['required', 'numeric'],
            'tipo' => ['required', 'max:1', 'regex:/^((D)|(P))\d{0}$/i']
        ];
    }
    public static function rulesPeriods()
    {
        self::$rules = [
            'anho_periodo' => ['required', 'numeric'],
            'nombre' => ['required', 'max:100', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'estado' => ['required', 'max:1', 'regex:/^[0-2\s]+$/']
        ];
    }
}

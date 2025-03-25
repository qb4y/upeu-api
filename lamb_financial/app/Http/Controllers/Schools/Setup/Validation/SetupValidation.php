<?php
namespace App\Http\Controllers\Schools\Setup\Validation;
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
    public static function rulesConfigs() // OK
    {
        self::$rules = [
            'id_nivel' => ['required', 'regex:/^[0-9\s]+$/'],
            'codigo_ugel' => ['required', 'max:40']
        ];
    }
    public static function rulesGrades() // ok
    {
        self::$rules = [
            'codigo' => ['required', 'max:20', 'regex:/^[0-9\s]+$/'],
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesStages() // ok
    {
        self::$rules = [
            'codigo' => ['required', 'max:20', 'regex:/^[0-9\s]+$/'],
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesTypeDiscounts() // OK
    {
        self::$rules = [
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesTypePayments() // ok
    {
        self::$rules = [
            'nombre' => ['required', 'max:100'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesStagesGrades() // ok
    {
        self::$rules = [
            'id_config' => ['required', 'numeric'],
            'id_grado' => ['required', 'numeric']
        ];
    }
    public static function rulesVacants() // ok
    {
        self::$rules = [
            'id_ngrado' => ['required', 'numeric'],
            'nro_vacante' => ['required', 'numeric'],
            'estado' => ['required', 'max:1', 'regex:/^[0-1\s]+$/']
        ];
    }
    public static function rulesCriterions() // ok
    {
        self::$rules = [
            'id_ngrado' => ['required', 'numeric'],
            'detalle' => ['required', 'max:100'],
            'importe' => ['required', 'numeric'],
            'tipo' => ['required', 'max:1', 'regex:/^((D)|(P))\d{0}$/i'] // 'regex:/^[D-P\s]+$/'
        ];
    }
}

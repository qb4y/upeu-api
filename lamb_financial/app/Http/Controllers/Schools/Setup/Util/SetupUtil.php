<?php
namespace App\Http\Controllers\Schools\Setup\Util;
use Exception;
use App\Http\Controllers\Schools\Setup\Validation\SetupValidation;
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
    public static function validConfigs() // ok
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
    public static function validGrades() // ok
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
    public static function validStages() // ok
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
    public static function validTypeDiscounts() // OK
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
    public static function validTypePayments() // ok
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
    public static function validStagesGrades() // ok
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
    public static function validVacants() // ok
    {
        SetupValidation::rulesVacants();
        if(!SetupValidation::isValid())
        {
            self::$message = SetupValidation::getMessage();
            return false;
        }
        self::$data = [
            'id_ngrado' => Input::get('id_ngrado'),
            'nro_vacante' => Input::get('nro_vacante'),
            'estado' => Input::get('estado')
        ];
        return true;
    }
    public static function validCriterions() // ok
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
}

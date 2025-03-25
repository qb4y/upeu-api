<?php
namespace App\Http\Controllers\Schools\Validation;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class SchoolsValidation
{
    public function __construct()
    {
    }
    public static function validationByCallRules($function)
    {
        $result = new class{};
        $rules = self::$function();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid = false;
            $result->message = $errorString;
            return $result;
        }
        $result->valid = true;
        $result->message = "";
        return $result;
    }

    private static function rulesPersonsNatural()
    {
        return [
            'nombre' => 'required',
            'paterno' => 'required',
            'materno' => 'required',
            'id_tipodocumento' => 'required',
            'num_documento' => 'required|numeric'
        ];
    }
    private static function rulesPersonDocumentDNI()
    {
        return [
            'num_documento' => 'min:8|max:8'
        ];
    }
    private static function rulesPersonDocumentPasaporteCarnetExt()
    {
        return [
            'num_documento' => 'min:9|max:9'
        ];
    }
    private static function rulesProformas()
    {
        return [
            'id_periodo' => 'required',
            'id_pnivel' => 'required',
            'id_pngrado' => 'required',
            'id_tipopago' => 'required'
        ];
    }
    private static function rulesResponsibles()
    {
        return [
            'id_alumno' => 'required',
            'id_persona' => 'required',
            'tipo_parentesco' => 'required',
            'resp_financiero' => 'required'
        ];
    }
}

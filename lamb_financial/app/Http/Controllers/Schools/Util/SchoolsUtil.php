<?php
namespace App\Http\Controllers\Schools\Util;
use Exception;
use App\Http\Controllers\Schools\Validation\SchoolsValidation;
use Illuminate\Support\Facades\Input;

class SchoolsUtil
{
    public function __construct()
    {
    }
    public static function dataPersonsNatural()
    {
        $validator = SchoolsValidation::validationByCallRules("rulesPersonsNatural");
        if(!$validator->valid)
        {
            return $validator;
        }
        $result = new class{};
        $dataPersona = [];
        $dataPNatural = [];
        $dataPDocumento = [];
        $dataPDireccion = [];
        $dataPVirtual = [];
        $dataPTelefono = [];
        $dataPNaturalIdioma = [];
        $dataPNaturalIdioma2 = [];
        $dataPNaturalSchool = [];
        $dataPSchoolFamily = [];
        $dataPSchoolReligion = [];
        $dataPSchoolLaboral = [];
        $dataPSchoolVivienda = [];
        

        $dataPersona["nombre"] = Input::get('nombre');
        $dataPersona["paterno"] = Input::get('paterno');
        $dataPersona["materno"] = Input::get('materno');

        $dataPNatural["sexo"] = Input::get('sexo');
        $dataPNatural["fec_nacimiento"] = Input::get('fec_nacimiento');
        $dataPNatural["id_tipoestadocivil"] = Input::get('id_tipoestadocivil');
        $dataPNatural["id_tipotratamiento"] = 1;
        $dataPNatural["vive"] = Input::get('vive');
        $dataPNatural["id_tipopais"] = Input::get('id_tipopais');
        $dataPNatural["id_nacionalidad"] = Input::get('id_nacionalidad');
        $dataPNatural["id_departamento"] = Input::get('id_departamento');
        $dataPNatural["id_provincia"] = Input::get('id_provincia');
        $dataPNatural["id_distrito"] = Input::get('id_distrito');

        $dataPDocumento["id_tipodocumento"] = Input::get('id_tipodocumento');
        $dataPDocumento["num_documento"] = Input::get('num_documento');

        $dataPDireccion["id_tipodireccion"] = Input::get('id_tipodireccion');
        $dataPDireccion["direccion"] = Input::get('direccion');
        

        //$dataPVirtual["id_tipovirtual"] = Input::get('id_tipovirtual');
        

        //$dataPTelefono["id_tipotelefono"] = Input::get('tipotelefono');
        
        if(!empty(Input::get('id_tipoidioma'))){ $dataPNaturalIdioma["id_tipoidioma"] = Input::get('id_tipoidioma');}else{ $dataPNaturalIdioma["id_tipoidioma"] = null;}
        $dataPNaturalIdioma["es_materno"] = Input::get('es_materna');
        if(!empty(Input::get('id_tipoidioma2'))){ $dataPNaturalIdioma2["id_tipoidioma"] = Input::get('id_tipoidioma2');}else{$dataPNaturalIdioma2["id_tipoidioma"] = null;}
        $dataPNaturalIdioma2["es_materno"] = Input::get('es_materna2');
        
        
        
        
        
        if(!empty(Input::get('nombre_familia'))){$dataPSchoolFamily["nombre_familia"] = Input::get('nombre_familia');}else{$dataPSchoolFamily["nombre_familia"] = null;}
        
        if(!empty(Input::get('id_religion'))){$dataPSchoolReligion["id_religion"] = Input::get('id_religion');}else{$dataPSchoolReligion["id_religion"] = "";}

        $dataPSchoolLaboral["nombre"] = Input::get('l_nombre');
        $dataPSchoolLaboral["cargo"] = Input::get('l_cargo');
        $dataPSchoolLaboral["direccion"] = Input::get('l_direccion');
        $dataPSchoolLaboral["id_tipopais"] = Input::get('l_pais');
        $dataPSchoolLaboral["id_departamento"] = Input::get('l_departamento');
        $dataPSchoolLaboral["id_provincia"] = Input::get('l_provincia');
        $dataPSchoolLaboral["id_distrito"] = Input::get('l_distrito');
        $dataPSchoolLaboral["telefono1"] = Input::get('l_telefono1');
        $dataPSchoolLaboral["telefono2"] = Input::get('l_telefono2');
        $dataPSchoolLaboral["anexo"] = Input::get('l_anexo');
        $dataPSchoolLaboral["profesion"] = Input::get('l_profesion');
        $dataPSchoolLaboral["ocupacion"] = Input::get('l_ocupacion');
        $dataPSchoolLaboral["especialidad"] = Input::get('l_especialidad');

        $dataPSchoolVivienda["direccion"] = Input::get('v_direccion');
        $dataPSchoolVivienda["departamento"] = Input::get('v_departamento');
        $dataPSchoolVivienda["provincia"] = Input::get('v_provincia');
        $dataPSchoolVivienda["distrito"] = Input::get('v_distrito');
        $dataPSchoolVivienda["telefono"] = Input::get('v_telefono');
        $dataPSchoolVivienda["movil"] = Input::get('v_movil');
        $dataPSchoolVivienda["localizacion"] = Input::get('v_localizacion');
        $dataPSchoolVivienda["localizacion_detalle"] = Input::get('v_localizacion_detalle');

        $result->valid = true;
        $result->message = "";
        $result->dataPersona = $dataPersona;
        $result->dataPNatural = $dataPNatural;
        $result->dataPDocumento = $dataPDocumento;
        $result->dataPDireccion = $dataPDireccion;
        //$result->dataPVirtual = $dataPVirtual;
        //$result->dataPTelefono = $dataPTelefono;
        $result->dataPNaturalIdioma = $dataPNaturalIdioma;
        $result->dataPNaturalIdioma2 = $dataPNaturalIdioma2;
        //$result->dataPNaturalSchool = $dataPNaturalSchool;
        $result->dataPSchoolFamily = $dataPSchoolFamily;
        $result->dataPSchoolReligion = $dataPSchoolReligion;
        $result->dataPSchoolLaboral = $dataPSchoolLaboral;
        $result->dataPSchoolVivienda = $dataPSchoolVivienda;
        return $result;
    }
    public static function dataProformas()
    {
        $validator = SchoolsValidation::validationByCallRules("rulesProformas");
        if(!$validator->valid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["nombres"] = Input::get('nombres');
        $data["dni"] = Input::get('dni');
        $data["telefono"] = Input::get('telefono');
        $data["direccion"] = Input::get('direccion');

        $data["id_periodo"] = Input::get('id_periodo');
        $data["id_pnivel"] = Input::get('id_pnivel');
        $data["id_pngrado"] = Input::get('id_pngrado');
        $data["id_tipopago"] = Input::get('id_tipopago');
        
        $result->valid = true;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataResponsibles()
    {
        $validator = SchoolsValidation::validationByCallRules("rulesResponsibles");
        if(!$validator->valid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["id_alumno"] = Input::get('id_alumno');
        $data["id_persona"] = Input::get('id_persona');
        $data["tipo_parentesco"] = Input::get('tipo_parentesco');
        $data["tipo"] = Input::get('tipo');
        $data["resp_financiero"] = Input::get('resp_financiero');
        $result->valid = true;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function getGenereNameRandom($length)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $name = substr(str_shuffle($characters), 0, $length);
        return $name; 
    }
    public static function getSquema()
    {
        $esquema = "JOSE";
        return $esquema; 
    }
    public static function validationPersonDocument($tipoDocumento)
    {
        if($tipoDocumento=='1'){
            $validator = SchoolsValidation::validationByCallRules("rulesPersonDocumentDNI"); 
        }else{
            $validator = SchoolsValidation::validationByCallRules("rulesPersonDocumentPasaporteCarnetExt");
        } 
        return $validator->valid;
    }
}

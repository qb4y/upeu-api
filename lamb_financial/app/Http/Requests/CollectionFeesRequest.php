<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 22/02/21
 * Time: 17:13
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class CollectionFeesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            /*'id_entidad' => 'required',
            'id_depto' => 'required',
            'id_persona' => 'required',*/
            'id_semestre' => 'required',
            'alumnos' => 'required',
            'id_programa_estudio' => 'required',
            'id_modo_contrato' => 'required',
            'id_nivel_ensenanza' => 'required',
            'id_modalidad_estudio' => 'required',
            'id_sede' => 'required',
            'cuota' => 'required',
        ];
    }
}
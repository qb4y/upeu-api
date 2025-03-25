<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 12/04/21
 * Time: 08:57
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ExchangeRateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // passport laravel -> configurar roles permission al nivel de models, security
    }

    public function rules() // regla de valiadaciones
    {
        return [
            'compra' => 'required|numeric|regex:/^\d+(\.\d{1,3})?$/', // implementar valiacion minima de 2 decimales para campo BUY
            'venta' => 'required|numeric|regex:/^\d+(\.\d{1,3})?$/', // implementar valiacion minima de 2 decimales para campo BUY
            'denominacional' => 'required|numeric|regex:/^\d+(\.\d{1,3})?$/', // implementar valiacion minima de 2 decimales para campo BUY
        ];
    }

    public function messages()
    {
        return [
            'compra.regex' => 'El número de decimales permitido es: 3',
            'venta.regex' => 'El número de decimales permitido es: 3',
            'denominacional.regex' => 'El número de decimales permitido es: 3',
        ];
    }
}
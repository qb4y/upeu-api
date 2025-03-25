<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 9/9/21
 * Time: 8:20 PM
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PedidoRegistroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'fecha_pago' => 'nullable|date',
        ];
    }
}
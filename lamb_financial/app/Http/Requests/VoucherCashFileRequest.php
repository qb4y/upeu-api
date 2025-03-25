<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 22/02/21
 * Time: 17:13
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class VoucherCashFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'motivo' => 'required|min:2',
        ];
    }
}
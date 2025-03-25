<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 12/04/21
 * Time: 16:09
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class AccountingVoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'lote' => 'required',
            'fecha' => 'required',
        ];
    }

}
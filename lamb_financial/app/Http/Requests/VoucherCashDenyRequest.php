<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 16/02/21
 * Time: 15:50
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class VoucherCashDenyRequest extends FormRequest
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
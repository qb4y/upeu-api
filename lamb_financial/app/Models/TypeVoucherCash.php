<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 15:11
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TypeVoucherCash extends Model
{
    protected $table = 'CAJA_TIPOVALE';
    protected $primaryKey = 'ID_TIPOVALE';


    protected $fillable = [
        'ID_TIPOVALE',
        'NOMBRE',
    ];

    //const CREATED_AT = null;
    //const UPDATED_AT = null;
}
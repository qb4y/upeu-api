<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 11:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class VoucherCashFile extends Model
{
    protected $table = 'CAJA_VALE_FILE';
    protected $primaryKey = 'ID_VFILE';


    protected $fillable = [
        "ID_VALE",
        "NOMBRE",
        "FORMATO",
        "URL",
        "FECHA",
        "TIPO",
        "TAMANHO",
        "ESTADO",
    ];

    public function getUrlAttribute()
    {
        //dd($this->attributes);
            return $this->attributes['url'];
    }

    //const CREATED_AT = null;
    //const UPDATED_AT = null;
}
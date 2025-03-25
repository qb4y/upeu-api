<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaPagoFile extends Model
{

    protected $table = 'eliseo.caja_pago_file';
    protected $primaryKey ='id_cfile';
    public $timestamps = false;

    protected $fillable = [
        'id_cfile','id_pago',
        'nombre','formato','url','fecha','tipo',
        'tamanho',
        'estado','url_old'
    ];

}

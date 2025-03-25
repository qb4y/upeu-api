<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoFile extends Model
{

    protected $table = 'eliseo.pedido_file';
    protected $primaryKey ='id_pfile';
    public $timestamps = false;

    protected $fillable = [
        'id_pfile',
        'id_pedido',
        'id_pcompra',
        'nombre','formato','url','fecha','tipo',
        'seleccionado',
        'tamanho',
        'estado',
        'id_detalle',
        'name_file',
        'url_old'
    ];

}

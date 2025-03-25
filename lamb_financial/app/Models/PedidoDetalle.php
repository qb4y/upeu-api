<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{

    protected $table = 'eliseo.pedido_detalle';
    protected $primaryKey ='id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_detalle','id_pedido','id_almacen','id_articulo','detalle','cantidad','precio',
        'importe','fecha_inicio','fecha_fin','cantidad_reg',
    ];

}

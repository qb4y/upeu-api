<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoRegistro extends Model
{

    protected $table = 'eliseo.pedido_registro';
    protected $primaryKey ='id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido','id_entidad','id_depto','id_anho','id_mes','id_persona','id_tipopedido',
        // 'id_gasto',
        'id_tipotransaccion',
        'id_actividad',
        'id_areaorigen',
        'id_areadestino',
        // 'id_deptoorigen',
        // 'id_areagasto','id_pbancaria','id_tipoactividadeconomica',
        'numero','acuerdo','fecha','fecha_pedido',
        'fecha_entrega',
        'fecha_pago',
        'motivo','estado','comentario','id_almacen_origen',
        'id_almacen_destino'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{

    protected $table = 'eliseo.pedido_compra';
    protected $primaryKey ='id_pcompra';
    public $timestamps = false;

    protected $fillable = [
        'id_pcompra','id_pedido',
        'id_compra','id_moneda','id_proveedor','importe','fecha',
        'numero',
        'serie',
        'es_contrato',
        'estado',
        'tramite_pago',
        'id_persona',
        'id_vale',
        'importe_me',
        'id_comprobante',
        'id_actividad',
        'igv',
        'igv_me',
        'tipo_cambio',
        'id_mediopago',
    ];

}

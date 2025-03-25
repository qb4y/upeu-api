<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use EloquentFilter\Filterable;

class CompraDetalle extends Model
{
    // use Filterable;
    protected $table = 'eliseo.compra_detalle';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_detalle',
        'id_compra',
        'id_dinamica',
        'id_ctipoigv',
        'id_almacen',
        'id_articulo',
        'id_tipoigv',
        'detalle',
        'cantidad',
        'precio',
        'base',
        'igv',
        'importe',
        'orden',
        'estado',
        'es_costo_vinculado',
        'costo_vinculado',
        'fecha_vencimiento',
    ];

}

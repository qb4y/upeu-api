<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use EloquentFilter\Filterable;

class Compra extends Model
{
    // use Filterable;
    protected $table = 'eliseo.compra';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_parent',
        'id_entidad',
        'id_anho',
        'id_depto',
        'id_mes',
        'id_persona',
        'id_proveedor',
        'id_comprobante',
        'id_moneda',
        'id_voucher',
        'id_tiponota',
        'id_tipotransaccion',
        'tipocambio',
        'fecha_almacen',
        'fecha_provision',
        'fecha_doc',
        'serie',
        'numero',
        'importe',
        'importe_me',
        'igv',
        'id_igv',
        'base_gravada',
        'base_nogravada',
        'base_mixta',
        'base_sincredito',
        'base_inafecta',
        'igv_gravado',
        'igv_nogravado',
        'igv_mixto',
        'igv_sincredito',
        'estado',
        // 'detraccion_numero',
        // 'detraccion_fecha',
        // 'detraccion_importe',
        // 'detraccion_banco',
        'otros',
        // 'retencion_importe',
        // 'retencion_serie',
        // 'retencion_numero',
        // 'retencion_fecha',
        'es_ret_det',
        'es_activo',
        'tiene_kardex',
        'es_electronica',
        'importe_renta', // RH
        'tiene_suspencion', // RH
        'base',
        'correlativo',
        'es_credito',
        'id_tipoorigen',
        'es_transporte_carga',
        'taxs',
        'fecha_vencimiento',
        'id_dinamica',
    ];

    // public function pedido()
    // {
    //     return $this->hasOne('App\Models\Eliseo\PedidoRegistro', 'id_pedido', 'id_pedido');
    // }
    // public function persona()
    // {
    //     return $this->hasOne('App\Models\Moises\Persona', 'id_persona', 'id_persona');
    // }
    // public function user()
    // {
    //     return $this->hasOne('App\Models\Eliseo\Users', 'id', 'id_persona');
    // }
}

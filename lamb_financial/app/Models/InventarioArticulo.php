<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioArticulo extends Model
{
    protected $table = 'eliseo.inventario_articulo';
    protected $primaryKey ='id_articulo';
    public $timestamps = false;

    protected $fillable = [
        'id_articulo','nombre','codigo','id_clase'
    ];

}

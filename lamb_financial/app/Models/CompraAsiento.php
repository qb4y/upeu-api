<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use EloquentFilter\Filterable;

class CompraAsiento extends Model
{
    // use Filterable;
    protected $table = 'eliseo.compra_asiento';
    protected $primaryKey = 'id_casiento';
    public $timestamps = false;

    protected $fillable = [
        'id_casiento',
        'id_compra',
        'id_cuentaaasi',
        'id_restriccion',
        'id_ctacte',
        'id_fondo',
        'id_depto',
        'importe',
        'descripcion',
        'editable',
        'id_parent',
        'id_tiporegistro',
        'dc',
        'importe_me',
        'fecha_actualizacion',
        'agrupa',
        'nro_asiento',
        'orden',
    ];

    public function compraIipoigv()
    {
        return $this->hasOne('App\Models\Eliseo\CompraTipoigv', 'id_ctipoigv', 'id_ctipoigv');
    }
    // public function persona()
    // {
    //     return $this->hasOne('App\Models\Moises\Persona', 'id_persona', 'id_persona');
    // }
    // public function user()
    // {
    //     return $this->hasOne('App\Models\Eliseo\Users', 'id', 'id_persona');
    // }
}

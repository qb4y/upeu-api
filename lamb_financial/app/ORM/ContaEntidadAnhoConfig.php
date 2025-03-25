<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 8:47 PM
 */

namespace App\ORM;

use Illuminate\Database\Eloquent\Model;
use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class ContaEntidadAnhoConfig extends Eloquent {

    protected table='CONTA_ENTIDAD_ANHO_CONFIG'
    protected $guarded = ['ID_ENTIDAD','ID_ANHO'];
    public $timestamps = false;
    protected $fillable = ['ID_TIPOPLAN','NOMBRE','FECHA_INICIO','FECHA_FIN','ACTIVO','ID_USER_INICIO','ID_USER_FIN']

    public function ComprasSuspencion(){
        return $this->hasMany('App\ORM\CompraSuspencion');
    }

}
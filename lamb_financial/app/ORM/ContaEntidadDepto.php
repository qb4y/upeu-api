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

class ContaEntidadDepto extends Eloquent {
    protected table='CONTA_ENTIDAD_DEPTO';
    protected $guarded = ['ID_ENTIDAD','ID_DEPTO'];
    public $timestamps = false;
    protected $fillable = ['ID_DEPTO','ID_PARENT','NOMBRE','ES_GRUPO','ES_ACTIVO'];

    public function ComprasSuspencion(){
        return $this->hasMany('App\ORM\CompraSuspencion');
    }


}
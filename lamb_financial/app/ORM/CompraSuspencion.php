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

class CompraSuspencion extends Eloquent {
    protected table='COMPRA_SUSPENCION';
    protected $guarded = ['ID_SUSPENSION'];
    public $timestamps = false;
    protected $fillable = ['ID_SUSPENCION','ID_ENTIDAD','ID_DEPTO','ID_ANHO','ID_PROVEEDOR','FECHA_EMISION','FECHA_PRESENTACION'];

    public function conta_entidad_anho_conf(){
        return $this->belongsTo('App\ORM\ContaEntidadAnhoConfig','config');
    }
    public function conta_entidad_depto(){
        return $this->belongsTo('App\ORM\ContaEntidadAnhoDepto','depto');
    }

    public function persona(){
        return $this->belongsTo('App\ORM\Persona','persona');
    }


}
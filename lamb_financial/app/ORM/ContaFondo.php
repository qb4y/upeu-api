<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 8:47 PM
 */

namespace App\ORM;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class ContaFondo extends Eloquent {

    protected $guarded = ['ID_FONDO'];
    public $timestamps = false;

}
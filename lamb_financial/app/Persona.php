<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:37 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class Persona extends Eloquent {

    protected $guarded = ['id_persona'];
    public $timestamps = false;

}
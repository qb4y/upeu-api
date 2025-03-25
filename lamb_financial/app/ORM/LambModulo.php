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

class LambModulo extends Eloquent
{

    protected $guarded = ['id_modulo'];
    public $timestamps = false;
}
<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 12:02 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    protected $table = 'ELISEO.PROCESS_PASO';
    protected $primaryKey ='id_paso';
    protected $fillable = ['id_paso','id_proceso','id_tipopaso','nombre','orden','estado','route','icono'];
    public $timestamps = false;

}